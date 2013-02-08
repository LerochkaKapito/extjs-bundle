<?php
namespace Tpg\ExtjsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
    }
}