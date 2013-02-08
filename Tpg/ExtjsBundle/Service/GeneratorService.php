<?php
namespace Tpg\ExtjsBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Tools\Export\ExportException;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Tpg\ExtjsBundle\Annotation\Direct;
use Tpg\ExtjsBundle\Annotation\Model;
use Tpg\ExtjsBundle\Annotation\ModelProxy;

class GeneratorService {

    /** @var AnnotationReader */
    protected $annoReader;

    /** @var $twig TwigEngine */
    protected $twig;

    protected $remotingBundles = array();

    public function setAnnotationReader($reader) {
        $this->annoReader = $reader;
    }

    public function setTwigEngine($engine) {
        $this->twig = $engine;
    }

    public function setRemotingBundles($bundles) {
        $this->remotingBundles = $bundles;
    }

    /**
     * Generate Remote API from a list of controllers
     *
     * @param array $controllers
     */
    public function generateRemotingApi($controllers) {
        $list = array();
        if ($controllers === false) {

        }
        foreach ($controllers as $controller) {
            $controllerRef = new \ReflectionClass($controller);
            foreach ($controllerRef->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                /** @var $methodDirectAnnotation Direct */
                $methodDirectAnnotation = $this->annoReader->getMethodAnnotation($method, 'Tpg\ExtjsBundle\Annotation\Direct');
                if ($methodDirectAnnotation !== null) {
                    $apiName = $methodDirectAnnotation->name;
                    $className = substr($apiName, 0, strrpos($apiName, '.'));
                    $methodName = substr($apiName, strrpos($apiName, '.')+1);
                    $list[$className]['methods'][$methodName] = array('len' => count($method->getParameters()));
                }
            }
        }
        return $list;
    }

    public function generateMarkupForEntity($entity) {
        $classRef = new \ReflectionClass($entity);
        /** @var $classModelAnnotation Model */
        $classModelAnnotation = $this->annoReader->getClassAnnotation($classRef, 'Tpg\ExtjsBundle\Annotation\Model');
        /** @var $classModelProxyAnnotation ModelProxy */
        $classModelProxyAnnotation = $this->annoReader->getClassAnnotation($classRef, 'Tpg\ExtjsBundle\Annotation\ModelProxy');
        if ($classModelAnnotation !== null) {
            $modelName = $classModelAnnotation->name;
            if ($classModelAnnotation->generateAsBase === true) {
                $modelName = substr($modelName, 0, strrpos($modelName, '.')+1) . 'Base' . substr($modelName, strrpos($modelName, '.')+1);
            }
            $structure = array(
                'name' => $modelName,
                'extend' => $classModelAnnotation->extend,
                'fields' => array(),
                'associations' => array(),
            );
            if ($classModelProxyAnnotation !== null) {
                $structure['proxy'] = array(
                    'type'=>$classModelProxyAnnotation->name,
                ) + $classModelProxyAnnotation->option;
                if ($classModelProxyAnnotation->reader != array()) $structure['proxy']['reader'] = $classModelProxyAnnotation->reader;
                if ($classModelProxyAnnotation->writer != array()) $structure['proxy']['writer'] = $classModelProxyAnnotation->writer;
            }
            /** @var $classExclusionPolicy ExclusionPolicy */
            $classExclusionPolicy = $this->annoReader->getClassAnnotation($classRef, 'JMS\Serializer\Annotation\ExclusionPolicy');
            foreach ($classRef->getProperties() as $property) {
                /** @var $propertyExclude Exclude */
                $propertyExclude = $this->annoReader->getPropertyAnnotation($property, 'JMS\Serializer\Annotation\Exclude');
                /** @var $propertyExpose Expose */
                $propertyExpose = $this->annoReader->getPropertyAnnotation($property, 'JMS\Serializer\Annotation\Expose');
                if ($classExclusionPolicy === null || $classExclusionPolicy->policy == "none") {
                    if ($propertyExclude !== null) {
                        continue;
                    }
                } else if ($classExclusionPolicy->policy == "all") {
                    if ($propertyExpose === null) {
                        continue;
                    }
                }
                $this->buildPropertyAnnotation($property, $structure);
            }
            return $this->twig->render('TpgExtjsBundle:ExtjsMarkup:model.js.twig', $structure);
        } else {
            return "";
        }
    }

    protected function convertNaming($name) {
        $separator = "_";
        $name = preg_replace('/[A-Z]/', $separator.'\\0', $name);
        return strtolower($name);
    }

    protected function buildPropertyAnnotation($property, &$structure) {
        $field = array(
            'name' => $this->convertNaming($property->name),
            'type' => 'string',
            'validators' => array(),
        );
        $association = array();
        $saveField = false;
        $annotations = $this->annoReader->getPropertyAnnotations($property);
        foreach ($annotations as $annotation) {
            $className = get_class($annotation);
            if (strpos(get_class($annotation), 'Symfony\Component\Validator\Constraints') === 0) {
                $field['validators'][] = $this->getValidator(
                    substr($className, 40),
                    $annotation
                );
            }
            switch(get_class($annotation)) {
                case 'Doctrine\ORM\Mapping\Column':
                    $field['type'] = $this->getColumnType($annotation->type);
                    break;
                case 'JMS\Serializer\Annotation\SerializedName':
                    $field['name'] = $annotation->name;
                    break;
                case 'Doctrine\ORM\Mapping\OneToMany':
                case 'Doctrine\ORM\Mapping\OneToOne':
                    $association['type'] = substr(get_class($annotation), 21);
                    $association['name'] = $this->convertNaming($property->name);
                    $association['model'] = $this->getModelName($annotation->targetEntity);
                    $association['entity'] = $annotation->targetEntity;
                    $association['key'] = $this->getAnnotation(
                        $annotation->targetEntity,
                        $annotation->mappedBy,
                        'Doctrine\ORM\Mapping\JoinColumn'
                    )->name;
                    break;
                case 'Doctrine\ORM\Mapping\ManyToOne':
                    $association['type'] = substr(get_class($annotation), 21);
                    $association['name'] = $this->convertNaming($property->name);
                    $association['model'] = $this->getModelName($annotation->targetEntity);
                    $association['entity'] = $annotation->targetEntity;
                    break;
                case 'Doctrine\ORM\Mapping\JoinColumn':
                    $saveField = true;
                    $field['name'] = $this->convertNaming($annotation->name);
                    $field['type'] = $this->getEntityColumnType($association['entity'], $annotation->referencedColumnName);
                    $association['key'] = $this->convertNaming($annotation->name);
                    break;
            }
        }
        if (!empty($association)) {
            $structure['associations'][] = $association;
        }
        if ($saveField || empty($association)) {
            $structure['fields'][] = $field;
        }
        return $field;
    }

    protected function getAnnotation($entity, $property, $annotation) {
        $classRef = new \ReflectionClass($entity);
        $propertyRef = $classRef->getProperty($property);
        if ($propertyRef) return $this->annoReader->getPropertyAnnotation($propertyRef, $annotation);
        else return false;
    }

    /**
     * Get Column Type of a model.
     *
     * @param $entity string Class name of the entity
     * @param $property string
     */
    public function getEntityColumnType($entity, $property) {
        $classRef = new \ReflectionClass($entity);
        $propertyRef = $classRef->getProperty($property);
        $columnRef = $this->annoReader->getPropertyAnnotation($propertyRef, 'Doctrine\ORM\Mapping\Column');
        if ($columnRef === null) {
            $idRef = $this->annoReader->getPropertyAnnotation($propertyRef, 'Doctrine\ORM\Mapping\Id');
            if ($idRef !== null) {
                return "int";
            } else {
                return "string";
            }
        } else {
            return $this->getColumnType($columnRef->type);
        }
    }

    /**
     * Translate Column Type from PHP to ExtJS
     *
     * @param $type
     * @return string
     */
    protected function getColumnType($type) {
        switch ($type) {
            case 'integer':
                return 'int';
            case 'datetime':
                return 'date';
            default:
                return $type;
        }
    }

    /**
     * Get the Ext JS Validator
     *
     * @param string $name
     * @param array $annotation
     * @return array
     */
    protected function getValidator($name, $annotation) {
        $validate = array();
        switch($name) {
            case 'NotBlank':
            case 'NotNull':
                $validate['name'] = "presence";
                break;
            case 'Email':
                $validate['name'] = "email";
                break;
            case 'MaxLength':
            case 'MinLength':
                $validate['name'] = "length";
                if ($name == "MaxLength") {
                    $validate['max'] = $annotation->limit;
                } else {
                    $validate['min'] = $annotation->limit;
                }
                break;
            default:
                $validate['name'] = strtolower($name);
                $validate += get_object_vars($annotation);
                break;
        }
        return $validate;
    }

    /**
     * Get model name of an entity
     * @param $entity string Class name of the entity
     */
    public function getModelName($entity) {
        $classRef = new \ReflectionClass($entity);
        $classModelAnnotation = $this->annoReader->getClassAnnotation($classRef, 'Tpg\ExtjsBundle\Annotation\Model');
        if ($classModelAnnotation !== null) {
            return $classModelAnnotation->name;
        }
        return null;
    }
}