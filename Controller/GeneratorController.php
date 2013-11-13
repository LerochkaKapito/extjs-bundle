<?php
namespace Tpg\ExtjsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\AppKernel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tpg\ExtjsBundle\Service\GeneratorService;

class GeneratorController extends Controller {
    public function generateModelAction() {
        $models = $this->getRequest()->get("model");
        /** @var $generator GeneratorService */
        $generator = $this->get("tpg_extjs.generator");
        /** @var $kernel AppKernel */
        $kernel = $this->get('kernel');
        if ($models === null) {
            $list = $this->container->getParameter("tpg_extjs.entities");
            return new StreamedResponse(function () use($list, $generator, $kernel) {
                foreach ($list as $configEntityLine) {
                    list($bundleName, $path) = explode("/", substr($configEntityLine, 1), 2);
                    /** @var Bundle $bundle */
                    $bundle = $kernel->getBundle($bundleName, true);

                    /** Entity end with backslash, it is a directory */
                    $loadAllEntitiesFromDir = ($configEntityLine[strlen($configEntityLine)-1] == "/");

                    if ( $loadAllEntitiesFromDir ) {
                        $bundleRef = new \ReflectionClass($bundle);
                        $dir = new Finder();
                        $dir->files()->depth('== 0')->in(dirname($bundleRef->getFileName()).'/'.$path)->name('/.*\.php$/');
                        foreach($dir as $file) {
                            $entityClassname = $bundleRef->getNamespaceName() . "\\" . str_replace("/", "\\", $path) . substr($file->getFilename(), 0, -4);
                            echo $generator->generateMarkupForEntity($entityClassname);
                        }
                    } else  {
                        $entityClassname = $bundle->getNamespace() . "\\" . str_replace("/", "\\", $path);
                        echo $generator->generateMarkupForEntity($entityClassname);
                    }
                }
                flush();
            }, 200, array(
                'Content-Type'=>'application/javascript'
            ));
        } else {
            if (!is_array($models)) {
                $models = array($models);
            }
            return new StreamedResponse(function () use($models, $generator) {
                foreach ($models as $model) {
                    $model = str_replace(".", "\\", $model);
                    echo $generator->generateMarkupForEntity($model);
                }
                flush();
            }, 200, array(
                'Content-Type'=>'application/javascript'
            ));
        }
    }

    public function generateRemoteApiAction() {
        /** @var $generator GeneratorService */
        $generator = $this->get("tpg_extjs.generator");
        $apis = $generator->generateRemotingApi();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/javascript');
        return $this->render(
            "TpgExtjsBundle:ExtjsMarkup:remoteapi.js.twig",
            array(
                "apis"=>$apis,
                "route"=>'extjs_remoting',
            ),
            $response
        );
    }

    public function remotingAction($bundle) {
        $bundleName = str_replace('.', "", $bundle);
        $request = json_decode($this->getRequest()->getContent(), true);
        if (!isset($request['data'])) {
            $data = array();
        } else {
            $data = $request['data'];
        }
        $controller = str_replace('.', "\\", $bundle)."\\Controller\\".$request['action'].'Controller';
        $ref = (new \ReflectionClass($controller));
        $actionMethod = $ref->getMethod($request['method'].'Action');
        $actionparams = $actionMethod->getParameters();
        if (count($actionparams) == 1
            && is_object($actionparams[0]->getClass())
            && (
                $actionparams[0]->getClass()->name == 'FOS\RestBundle\Request\ParamFetcherInterface'
                || $actionparams[0]->getClass()->name == 'Symfony\Component\HttpFoundation\Request'
            )) {
            // if the action expects only a ParamFetcher or Request param just give it the first member of $data
            $requestData = $data[0];
        } else {
            $requestData = array();
            $i = 0;
            foreach($actionparams as $parameter) {
                $requestData[$parameter->getName()] = $data[$i++];
            }
        }

        /* TODO:
            figure out which request params need to be "path params" and which are "query params".
            We would need to check the route for the action to figure this out.
            Meanwhile we just give the complete request param array as both pathParams and queryParams
            so the action can fetch the params from where it expects them to be.
        */
        $pathParams = $requestData;
        $queryParams = $requestData;

        /** @var JsonResponse $response */
        $response = $this->forward($bundleName.':'.$request['action'].':'.$request['method'], $pathParams, $queryParams);
        return new JsonResponse(array(
            'type'=>$request['type'],
            'tid'=>$request['tid'],
            'action'=>$request['action'],
            'method'=>$request['method'],
            'result'=>json_decode($response->getContent())
        ));
    }
}