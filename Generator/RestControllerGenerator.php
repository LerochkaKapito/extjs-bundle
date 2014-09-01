<?php
namespace Tpg\ExtjsBundle\Generator;

use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Sensio\Bundle\GeneratorBundle\Generator\ControllerGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

class RestControllerGenerator extends ControllerGenerator {

    protected $entityName;
    protected $trait = false;
    protected $mongo = false;
    protected $phpcr = false;
    protected $template = 'Controller.php';

    /**
     * @var BundleInterface $entityBundle
     */
    protected $entityBundle;

    public function setEntityName($name) {
        $this->entityName = $name;
    }
    public function setEntityBundle(BundleInterface $bundle) {
        $this->entityBundle = $bundle;
    }

    public function setUseTrait($flag) {
        $this->trait = $flag;
    }

    public function setTemplateFile($file) {
        $this->template = $file;
    }

    public function setMongo($mongo) {
        $this->mongo = $mongo;
    }
    
    public function setPhpcr($phpcr) {
    	$this->phpcr = $phpcr;
    }
    
    public function generate(BundleInterface $bundle, $controller, $routeFormat, $templateFormat, array $actions = array())
    {
        $dir = $bundle->getPath();
        $controllerFile = $dir.'/Controller/';
        if ($this->trait) {
            $controllerFile .= 'Generated/';
            @mkdir($controllerFile);
        }
        $controllerFile .= $controller.'Controller.php';

        if (file_exists($controllerFile) && !$this->trait) {
            throw new \RuntimeException(sprintf('Controller "%s" already exists', $controllerFile));
        }

        $manager = "doctrine.orm.default_entity_manager";
        $entityClass = $this->entityBundle->getNamespace().'\\Entity\\'.$this->entityName;
        $serializer = "tpg_extjs.orm_serializer";
        
        if ($this->mongo) {
            $entityClass = $this->entityBundle->getNamespace().'\\Document\\'.$this->entityName;
            $manager = "doctrine_mongodb.odm.default_document_manager";
            $serializer = "tpg_extjs.odm_serializer";
        } elseif ($this->phpcr) {
            $entityClass = $this->entityBundle->getNamespace().'\\Document\\'.$this->entityName;
            $manager = "doctrine_phpcr.odm.default_document_manager";
            $serializer = "tpg_extjs.phpcr_serializer";
        }

        $tmpEntity = explode('/', $this->entityName);
        
        $parameters = array(
            'mongo'      => $this->mongo,
            'phpcr'      => $this->phpcr,
        	'trait'      => $this->trait,
            'namespace'  => $bundle->getNamespace(),
            'bundle'     => $bundle->getName(),
            "manager"    => $manager,
            'serializer' => $serializer,
            'controller'        => $controller,
            'entity_class'      => $entityClass,
            'entity_name'       => str_replace(array("/","\\"), "_", $this->entityName),
            'entity_bundle'     => $this->entityBundle->getName(),
            'entity'            => array_pop($tmpEntity),
            'route_name_prefix' => strtolower(preg_replace('/([A-Z])/', '_\\1', $bundle->getName().'_api_'))
        );

        $this->generateRestRouting($bundle, $controller);

        $this->renderFile('controller/'.$this->template, $controllerFile, $parameters);
    }

    public function generateRestRouting(BundleInterface $bundle, $controller)
    {
        $file = $bundle->getPath().'/Resources/config/routing.rest.yml';
        if (file_exists($file)) {
            $content = file_get_contents($file);
        } elseif (!is_dir($dir = $bundle->getPath().'/Resources/config')) {
            mkdir($dir);
        }

        $resource = $bundle->getNamespace()."\\Controller\\".$controller.'Controller';
        $name = strtolower(preg_replace('/([A-Z])/', '_\\1', $bundle->getName().$controller.'_rest'));
        $name_prefix = strtolower(preg_replace('/([A-Z])/', '_\\1', $bundle->getName().'_api_'));


        if (!isset($content)) {
            $content = '';
        } else {
            $yml = new Yaml();
            $route = $yml->parse($content);
            if (isset($route[$name])) {
                return false;
            }
        }

        $content .= sprintf(
            "\n%s:\n    type: rest\n    resource: %s\n    name_prefix: %s\n",
            $name,
            $resource,
            $name_prefix
        );

        $flink = fopen($file, 'w');
        if ($flink) {
            $write = fwrite($flink, $content);

            if ($write) {
                fclose($flink);
            } else {
                throw new \RunTimeException(sprintf('We cannot write into file "%s", has that file the correct access level?', $file));
            }
        } else {
            throw new \RunTimeException(sprintf('Problems with generating file "%s", did you gave write access to that directory?', $file));
        }
    }
}