<?php
namespace Test\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tpg\ExtjsBundle\Annotation as Extjs;

class TestController extends Controller {

    /**
     * @Extjs\Direct("Test.Remote.test")
     */
    public function testAction() {
        return new JsonResponse(array('result'=>'test'));
    }

    public function notApiAction() {
        return new JsonResponse(array('result'=>false));
    }

    /**
     * @Extjs\Direct("Test.Remote.test2")
     */
    public function testParameterAction($id) {
        return new JsonResponse(array('result'=>$id));
    }
}