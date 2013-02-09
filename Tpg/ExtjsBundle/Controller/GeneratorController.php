<?php
namespace Tpg\ExtjsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Tpg\ExtjsBundle\Service\GeneratorService;

class GeneratorController extends Controller {
    public function generateModelAction() {
        $models = $this->getRequest()->get("model");
        if (!is_array($models)) {
            $models = array($models);
        }
        /** @var $generator GeneratorService */
        $generator = $this->get("tpg_extjs.generator");
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
        $bundleName = str_replace('.', "", $bundle) . 'Bundle';
        $request = json_decode($this->getRequest()->getContent(), true);
        if (!isset($request['data'])) {
            $data = array();
        } else {
            $data = $request['data'];
        }
        $controller = str_replace('.', "\\", $bundle)."\\Controller\\".$request['action'].'Controller';
        $actionMethod = (new \ReflectionClass($controller))->getMethod($request['method'].'Action');
        $requestData = array();
        $i = 0;
        foreach($actionMethod->getParameters() as $paramter) {
            $requestData[$paramter->getName()] = $data[$i++];
        }
        /** @var $response JsonResponse */
        $response = $this->forward($bundleName.':'.$request['action'].':'.$request['method'], $requestData);
        return new JsonResponse(array(
            'type'=>$request['type'],
            'tid'=>$request['tid'],
            'action'=>$request['action'],
            'method'=>$request['method'],
            'result'=>json_decode($response->getContent())
        ));
    }
}