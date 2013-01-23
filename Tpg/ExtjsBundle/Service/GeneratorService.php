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
use Tpg\ExtjsBundle\Annotation\Model;

class GeneratorService {

    /** @var AnnotationReader */
    protected $annoReader;

    /** @var $twig TwigEngine */
    protected $twig;

    public function setAnnotationReader($reader) {
        $this->annoReader = $reader;
    }

    public function setTwigEngine($engine) {
        $this->twig = $engine;
    }

    public function generateMarkupForEntity($entity) {
        $classRef = new \ReflectionClass($entity);
        /** @var $classModelAnnotation Model */
        $classModelAnnotation = $this->annoReader->getClassAnnotation($classRef, 'Tpg\ExtjsBundle\Annotation\Model');
        if ($classModelAnnotation !== null) {
            $structure['name'] = $classModelAnnotation->name;
            $structure['extend'] = $classModelAnnotation->extend;
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
                $structure['fields'][] = $this->buildPropertyAnnotation($property);
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

    protected function buildPropertyAnnotation($property) {
        $field = array(
            'name' => $this->convertNaming($property->name),
            'type' => 'string',
            'validators' => array (),
        );
        $annotations = $this->annoReader->getPropertyAnnotations($property);
        foreach ($annotations as $annotation) {
            $className = get_class($annotation);
            if (strpos(get_class($annotation), 'Symfony\Component\Validator\Constraints') === 0) {
                $field['validators'][] = array(
                    'name' => strtolower(substr($className, 40)),
                ) + get_object_vars($annotation);
            }
            switch(get_class($annotation)) {
                case 'Doctrine\ORM\Mapping\Column':
                    $field['type'] = $annotation->type;
                    break;
                case 'JMS\Serializer\Annotation\SerializedName':
                    $field['name'] = $annotation->name;
                    break;
            }
        }
        return $field;
    }
}