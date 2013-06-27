<?php
namespace Tpg\ExtjsBundle\Generator;

use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Sensio\Bundle\GeneratorBundle\Generator\ControllerGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

class RestControllerGenerator extends ControllerGenerator {

    protected $entityName;

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


    public function generate(BundleInterface $bundle, $controller, $routeFormat, $templateFormat, array $actions = array())
    {
        $dir = $bundle->getPath();
        $controllerFile = $dir.'/Controller/'.$controller.'Controller.php';
        if (file_exists($controllerFile)) {
            throw new \RuntimeException(sprintf('Controller "%s" already exists', $controller));
        }

        $entityClass = $this->entityBundle->getNamespace().'\\Entity\\'.$this->entityName;
        $tmpEntity = explode('/', $this->entityName);

        $parameters = array(
            'namespace'  => $bundle->getNamespace(),
            'bundle'     => $bundle->getName(),
            'format'     => array(
                'routing'    => $routeFormat,
                'templating' => $templateFormat,
            ),
            'controller'        => $controller,
            'entity_class'      => $entityClass,
            'entity_name'       => str_replace(array("/","\\"), "_", $this->entityName),
            'entity_bundle'     => $this->entityBundle->getName(),
            'entity'            => array_pop($tmpEntity),
            'entity_type_class' => $bundle->getNamespace().'\\Form\\Type\\'.$this->entityName.'Type',
            'entity_type'       => $this->entityName.'Type',
            'route_name_prefix' => strtolower(preg_replace('/([A-Z])/', '_\\1', $bundle->getName().'_api_'))
        );

        $this->generateRestRouting($bundle, $controller);

        $parameters['actions'] = $actions;

        $this->renderFile('controller/Controller.php', $controllerFile, $parameters);
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