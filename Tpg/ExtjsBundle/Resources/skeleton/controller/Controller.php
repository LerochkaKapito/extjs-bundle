<?php

namespace {{ namespace }}\Controller;

{% block use_statements %}
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\View\View;
{% if 'annotation' == format.routing -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
{% endif %}
{% endblock use_statements %}

{% block class_definition %}
class {{ controller }}Controller extends FOSRestController
{% endblock class_definition %}
{
{% block class_body %}
    /**
     * Get detail of a {{ entity_name }} record
     * @param              $id
     *
     * @QueryParam(name="id", requirements="\d+")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get{{ entity_name|capitalize }}Action($id) {
        $view = View::create();
        return $this->handleView($view);
    }

    /**
     * Get list of {{ entity_name }} record
     * @param ParamFetcher $param
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="count", requirements="\d+", default="10")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get{{ entity_name|capitalize }}sAction(ParamFetcher $param) {
        $view = View::create();
        return $this->handleView($view);
    }

    /**
     * Create a new {{ entity_name }} record
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function post{{ entity_name|capitalize }}Action() {
        $view = View::create();
        return $this->handleView($view);
    }

    /**
     * Update an existing {{ entity_name }} record
     * @param $id
     *
     * @QueryParam(name="id", requirements="\d+")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function put{{ entity_name|capitalize }}Action($id) {
        $view = View::create();
        return $this->handleView($view);
    }

    /**
     * Delete an existing {{ entity_name }} record
     * @param $id
     *
     * @QueryParam(name="id", requirements="\d+")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete{{ entity_name|capitalize }}Action($id) {
        $view = View::create();
        return $this->handleView($view);
    }

{% for action in actions %}
    {% if 'annotation' == format.routing -%}
    /**
     * @Route("{{ action.route }}")
    {% if 'default' == action.template -%}
     * @Template()
    {% else -%}
     * @Template("{{ action.template }}")
    {% endif -%}
     */
    {% endif -%}
    public function {{ action.name }}(
        {%- if action.placeholders|length > 0 -%}
            ${{- action.placeholders|join(', $') -}}
        {%- endif -%})
    {
    }

{% endfor -%}
{% endblock class_body %}
}
