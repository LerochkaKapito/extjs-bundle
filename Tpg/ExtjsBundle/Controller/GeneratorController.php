<?php
namespace Tpg\ExtjsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
                "route"=>'/'
            ),
            $response
        );
    }
}