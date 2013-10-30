<?php
namespace Tpg\ExtjsBundle\Twig;

class ExtjsExtension extends \Twig_Extension {

    protected $generator;
    protected $router;

    /**
     * DI for ExtJS Generator
     * @param $generator
     */
    public function setGenerator($generator) {
        $this->generator = $generator;
    }

    /**
     * DI for Router
     * @param $router
     */
    public function setRouter($router) {
        $this->router = $router;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'extjs';
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('ucfirst', array($this, 'ucfirst')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('extjs_model', array($this, 'injectModel'), array('is_safe' => array('all'))),
        );
    }

    /**
     * Inject ExtJs Model into the DOM.
     *
     * @param boolean $injection Produce ExtJS model code on the page directly?
     * @param string $model Model Name.
     */
    public function injectModel() {
        $params = func_get_args();
        $injection = array_shift($params);
        if ($injection == false) {
            $url = $this->router->generate('extjs_generate_model', array('model'=>$params), false);
            return "<script type='text/javascript' src='$url'></script>";
        } else {
            $code = '';
            foreach ($params as $model) {
                $model = str_replace(".", "\\", $model);
                $code .= $this->generator->generateMarkupForEntity($model);
            }
            return $code;
        }
    }

    public function ucfirst($string) {
        return ucfirst($string);

    }
}