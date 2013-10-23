<?php
namespace Tpg\ExtjsBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Tools\Export\ExportException;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Finder\Finder;
use Tpg\ExtjsBundle\Annotation\Direct;
use Tpg\ExtjsBundle\Annotation\Model;
use Tpg\ExtjsBundle\Annotation\ModelProxy;

class GeneratorService {

    /** @var AnnotationReader */
    protected $annoReader;

    /** @var $twig TwigEngine */
    protected $twig;

    protected $remotingBundles = array();
    protected $fieldsParams = array();

    public function setAnnotationReader($reader) {
        $this->annoReader = $reader;
    }

    public function setTwigEngine($engine) {
        $this->twig = $engine;
    }

    public function setRemotingBundles($bundles) {
        $this->remotingBundles = $bundles;
    }
    public function setModelFieldsParameters($fieldsParams) {
        $this->fieldsParams = $fieldsParams;
    }

    /**
     * Generate Remote API from a list of controllers
     */
    public function generateRemotingApi() {
        $list = array();
        foreach($this->remotingBundles as $bundle) {
            $controllers = array();
            $bundleRef = new \ReflectionClass($bundle);
            $controllerDir = new Finder();
            $controllerDir->files()->in(dirname($bundleRef->getFileName()).'/Controller/')->name('/.*Controller\.php$/');
            foreach($controllerDir as $controllerFile) {
                $controller = $bundleRef->getNamespaceName() . "\\Controller\\" . substr($controllerFile->getFilename(), 0, -4);
                $controllerRef = new \ReflectionClass($controller);
                foreach ($controllerRef->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    /** @var $methodDirectAnnotation Direct */
                    $methodDirectAnnotation = $this->annoReader->getMethodAnnotation($method, 'Tpg\ExtjsBundle\Annotation\Direct');
                    if ($methodDirectAnnotation !== null) {
                        $nameSpace = str_replace("\\", ".", $bundleRef->getNamespaceName());
                        $className = str_replace("Controller", "", $controllerRef->getShortName());
                        $methodName = str_replace("Action", "", $method->getName());
                        $list[$nameSpace][$className][] = array(
                            'name'=>$methodName,
                            'len' => count($method->getParameters())
                        );
                    }
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
            if (empty($modelName)) {
                $modelName = str_replace("\\", ".", $classRef->getName());
            }
            if ($classModelAnnotation->generateAsBase === true) {
                $modelName = substr($modelName, 0, strrpos($modelName, '.')+1) . 'Base' . substr($modelName, strrpos($modelName, '.')+1);
            }
            $structure = array(
                'name' => $modelName,
                'extend' => $classModelAnnotation->extend,
                'fields' => array(),
                'associations' => array(),
                'validators' => array(),
                'idProperty' => 'id'
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
            $this->removeDuplicate($structure['validators']);
            return $this->twig->render('TpgExtjsBundle:ExtjsMarkup:model.js.twig', $structure);
        } else {
            return "";
        }
    }

    protected function convertNaming($name) {
        return $name;
    }

    /**
     * @param \ReflectionProperty $property
     * @param $structure
     *
     * @return array
     */
    protected function buildPropertyAnnotation($property, &$structure) {
        $field = array(
            'name' => $this->convertNaming($property->getName()),
            'type' => 'string',
        );
        $association = array();
        $validators = array();
        $skipValidator = false;
        $saveField = false;
        $fieldIsId = false;
        $annotations = $this->annoReader->getPropertyAnnotations($property);
        foreach ($annotations as $annotation) {
            $className = get_class($annotation);
            /** Get Constraints from Symfony Validator */
            if (strpos(get_class($annotation), 'Symfony\Component\Validator\Constraints') === 0) {
                $validators[] = array_merge(
                    array('field'=>$this->convertNaming($property->getName())),
                    $this->getValidator(substr($className, 40),$annotation)
                );
            }
            switch(get_class($annotation)) {
                case 'Doctrine\ORM\Mapping\Id':
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Id':
                    $field['useNull'] = true;
                    $field['persist'] = false;
                    $skipValidator = true;
                    $fieldIsId = true;
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Timestamp':
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Date':
                    $field['type'] = "date";
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Float':
                    $field['type'] = "float";
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Boolean':
                    $field['type'] = "boolean";
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Hash':
                    $field['type'] = "auto";
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Int':
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Increment':
                    $field['type'] = "int";
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\String':
                    $field['type'] = "string";
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\Field':
                    $field['type'] = $this->getColumnType($annotation->type);
                    break;
                case 'Doctrine\ORM\Mapping\Column':
                    $field['type'] = $this->getColumnType($annotation->type);
                    $validators[] = array('type'=>'presence', 'field'=>$this->convertNaming($property->getName()));
                    break;
                case 'JMS\Serializer\Annotation\SerializedName':
                    $field['name'] = $annotation->name;
                    break;
                case 'Doctrine\ORM\Mapping\OneToMany':
                case 'Doctrine\ORM\Mapping\OneToOne':
                    $association['type'] = substr(get_class($annotation), 21);
                    $association['name'] = $property->getName();
                    $association['model'] = $this->getModelName($annotation->targetEntity);
                    $association['entity'] = $annotation->targetEntity;
                    $association['key'] = $this->getAnnotation(
                        $annotation->targetEntity,
                        $annotation->mappedBy,
                        'Doctrine\ORM\Mapping\JoinColumn'
                    )->name;
                    break;
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany':
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne':
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany':
                case 'Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne':
                    if ($annotation->targetDocument) {
                        $association['type'] = substr(get_class($annotation), 41);
                        $association['name'] = $property->getName();
                        $association['model'] = $this->getModelName($annotation->targetDocument);
                        $association['entity'] = $annotation->targetDocument;
                    } else {
                        $field['type'] = "auto";
                    }
                    break;
                case 'Doctrine\ORM\Mapping\ManyToOne':
                    $association['type'] = substr(get_class($annotation), 21);
                    $association['name'] = $property->getName();
                    $association['model'] = $this->getModelName($annotation->targetEntity);
                    $association['entity'] = $annotation->targetEntity;
                    break;
                case 'Doctrine\ORM\Mapping\ManyToMany':
                    $association['type'] = 'ManyToMany';
                    $association['name'] = $property->getName();
                    $association['model'] = $this->getModelName($annotation->targetEntity);
                    $association['entity'] = $annotation->targetEntity;
                    break;
                case 'Doctrine\ORM\Mapping\JoinColumn':
                    $saveField = true;
                    $field['name'] = $this->convertNaming($annotation->name);
                    $field['type'] = $this->getEntityColumnType($association['entity'], $annotation->referencedColumnName);
                    $field['useNull'] = true;
                    $association['key'] = $this->convertNaming($annotation->name);
                    break;
            }
        }
        if($fieldIsId){
            $structure['idProperty'] = $field['name'];
        }

        if($field['type'] === 'date') {
            $field['format'] = $this->fieldsParams['date']['format'];
            $field['useNull'] = true;
        }
        if (!empty($association)) {
            $structure['associations'][] = $association;
        }
        if ($saveField || empty($association)) {
            $structure['fields'][$field['name']] = $field;
        }
        if (!empty($validators) && !$skipValidator) {
            $structure['validators'] = array_merge($structure['validators'], $validators);
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
     * Translate Mongo Field Type to ExtJs
     *
     * @param $type
     * @return string
     */
    protected function getFieldType($type) {
        switch ($type) {
            case 'hash':
                return "auto";
            case 'timestamp':
            case 'date':
                return "date";
            default:
                return $type;
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
            case 'decimal':
                return 'float';
            case 'integer':
            case 'smallint':
            case 'bigint':
                return 'int';
            case 'datetime':
            case 'time':
            case 'date':
            case 'datetimetz':
                return 'date';
            case 'text':
            case 'guid':
                return 'string';
            case 'object':
            case 'array':
            case 'simple_array':
            case 'json_array':
            case 'blob':
                return 'auto';
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
                $validate['type'] = "presence";
                break;
            case 'Email':
                $validate['type'] = "email";
                break;
            case 'Length':
                $validate['type'] = "length";
                $validate['max'] = (int)$annotation->max;
                $validate['min'] = (int)$annotation->min;
                break;
            case 'Regex':
                if ($annotation->match) {
                    $validate['type'] = "format";
                    $validate['matcher']['skipEncode'] = true;
                    $validate['matcher']['value'] = $annotation->pattern;
                }
                break;
            case 'MaxLength':
            case 'MinLength':
                $validate['type'] = "length";
                if ($name == "MaxLength") {
                    $validate['max'] = (int)$annotation->limit;
                } else {
                    $validate['min'] = (int)$annotation->limit;
                }
                break;
            case 'Choice':
                $validate['type'] = "inclusion";
                $validate['list'] = $annotation->choices;
                break;
            default:
                $validate['type'] = strtolower($name);
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
            if ($classModelAnnotation->name) {
                return $classModelAnnotation->name;
            } else {
                return str_replace("\\", ".", $classRef->getName());
            }
        }
        return null;
    }

    protected function removeDuplicate(&$list) {
        $secondList = $list;
        $duplicateList = array();
        foreach ($list as $index=>$row) {
            if (in_array($index, $duplicateList)) continue;
            foreach ($secondList as $index2 => $row2) {
                if ($index === $index2) continue;
                if ($row == $row2) {
                    $duplicateList[] = $index2;
                }
            }
        }
        foreach(array_reverse($duplicateList) as $index) {
            unset($list[$index]);
        }
    }
}
