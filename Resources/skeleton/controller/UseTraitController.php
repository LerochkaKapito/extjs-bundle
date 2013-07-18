<?php

namespace {{ namespace }}\Controller;

{% block use_statements %}
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\QueryParam;
{% endblock use_statements %}

{% block class_definition %}
/**
 * Class {{ controller }}Controller
 * @package {{ namespace }}\Controller
 */
class {{ controller }}Controller extends FOSRestController
{% endblock class_definition %}
{
{% block class_body %}
    use Generated\{{ controller }}Controller;
{% endblock class_body %}
}
