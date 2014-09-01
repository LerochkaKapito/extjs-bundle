<?php

namespace {{ namespace }}\Controller
{%- if trait %}
\Generated
{%- endif %}
;

{% block use_statements %}
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Route;
use \JMS\Serializer\SerializationContext;
use \Doctrine\DBAL\DBALException;
use \JMS\Serializer\DeserializationContext;
use {{ entity_class }};
{% endblock use_statements %}

{% block class_definition %}
/**
 * {%- if trait %} Trait{%- else %} Class{%- endif %} {{ controller }}Controller
 */

{%- if trait %}

trait
{%- else %}

class
{%- endif %}
 {{ controller }}Controller
{%- if not trait %}
 extends FOSRestController
{%- endif %}
{% endblock class_definition %}
 {
{% block class_body %}
    /**
     * Get detail of a {{ entity_name }} record
     * @param              $id
	 *
{% if phpcr %}     * @Route(requirements={"id"=".+"}) {% endif %}
     * @QueryParam(name="group", description="The JMS Serializer group", default="")
     * @QueryParam(name="depth", description="The depth to use for serialization", default="1")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get{{ controller|capitalize }}Action($id) {
        $manager = $this->get("{{ manager }}");
        $entity = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        $view = View::create($entity, 200)->setSerializationContext($this->getSerializerContext(array("get")));;
        return $this->handleView($view);
    }

    /**
     * Get list of {{ entity_name }} record
     * @param ParamFetcherInterface $paramFetcher
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the list.")
     * @QueryParam(name="start", requirements="\d+", default="0", description="Offset of the list")
     * @QueryParam(name="limit", requirements="\d+", default="25", description="Number of record per fetch.")
     * @QueryParam(name="sort", description="Sort result by field in URL encoded JSON format", default="[]")
     * @QueryParam(name="filter", description="Search filter in URL encoded JSON format", default="[]")
     * @QueryParam(name="group", description="The JMS Serializer group", default="")
     * @QueryParam(name="depth", description="The depth to use for serialization", default="1")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get{{ controller|capitalize }}sAction(ParamFetcherInterface $paramFetcher) {
        $manager = $this->get('{{ manager }}');
        $rawSorters = json_decode($paramFetcher->get("sort"), true);
        $sorters = array();
        foreach ($rawSorters as $s) {
            $sorters[$s['property']] = $s['direction'];
        }
        $rawFilters = json_decode($paramFetcher->get("filter"), true);
        $filters = array();
        foreach ($rawFilters as $f) {
            $filters[$f['property']] = $f['value'];
        }
        $start = 0;
        if ($paramFetcher->get("start") === "0") {
            if ($paramFetcher->get("page") > 1) {
                $start = ($paramFetcher->get("page")-1) * $paramFetcher->get("limit");
            }
        } else {
            $start = $paramFetcher->get("start");
        }
        $list = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->findBy(
            $filters,
            $sorters,
            $paramFetcher->get("limit"),
            $start
        );
        {%- if (mongo or phpcr) %}

        $list = array_values($list->toArray());
        {%- endif %}

        $context = $this->getSerializerContext(array('list'));
        $view = View::create($list, 200)->setSerializationContext($context);
        return $this->handleView($view);
    }

    /**
     * Create a new {{ entity_name }} record
     *
{% if phpcr %}     * @Route(requirements={"id"=".+"}) {% endif %}
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function post{{ controller|capitalize }}sAction() {
        $serializer = $this->get("{{ serializer }}");
        $entity = $serializer->deserialize(
            $this->getRequest()->getContent(),
            '{{ entity_class }}',
            'json',
            DeserializationContext::create()->setGroups(array("Default", "post"))
        );
        $validator = $this->get('validator');
        $validations = $validator->validate($entity, array('Default', 'post'));
        if ($validations->count() === 0) {
            $manager = $this->get('{{ manager }}');
            $manager->persist($entity);
            try {
                $manager->flush();
            } catch (DBALException $e) {
                return $this->handleView(
                    View::create(array('errors'=>array($e->getMessage())), 400)
                );
            }
            return $this->handleView(
                View::create($entity, 201, array('Location'=>$this->generateUrl(
                    "{{route_name_prefix}}get_{{ controller|lower }}",
                    array('id'=>$entity->getId()),
                    true
                )))->setSerializationContext($this->getSerializerContext())
            );
        } else {
            return $this->handleView(
                View::create(array('errors'=>$validations), 400)
            );
        }
    }

    /**
     * Update an existing {{ entity_name }} record
     * @param $id
     *
{% if phpcr %}     * @Route(requirements={"id"=".+"}) {% endif %}
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function put{{ controller|capitalize }}Action($id) {
        $manager = $this->get('{{ manager }}');
        $entity = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        if ($entity === null) {
            return $this->handleView(View::create('', 404));
        }
        $serializer = $this->get("{{ serializer }}");
        $entity = $serializer->deserialize(
            $this->getRequest()->getContent(),
            '{{ entity_class }}',
            'json',
            DeserializationContext::create()->setGroups(array("Default", "put"))
        );
        $entity->setId($id);
        $validator = $this->get('validator', array('Default', 'put'));
        $validations = $validator->validate($entity);
        if ($validations->count() === 0) {
            try {
                $manager->merge($entity);
                $manager->flush();
            } catch (DBALException $e) {
                return $this->handleView(
                    View::create(array('errors'=>array($e->getMessage())), 400)
                );
            }
            return $this->handleView(
                View::create($entity, 200)->setSerializationContext($this->getSerializerContext(array("get")))
            );
        } else {
            return $this->handleView(
                View::create(array('errors'=>$validations), 400)
            );
        }
    }

    /**
     * Patch an existing {{ entity_name }} record
     * @param $id
     *
{% if phpcr %}     * @Route(requirements={"id"=".+"}) {% endif %}
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patch{{ controller|capitalize }}Action($id) {
        $manager = $this->get('{{ manager }}');
        $entity = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        if ($entity === null) {
            return $this->handleView(View::create('', 404));
        }
        $content = json_decode($this->getRequest()->getContent(), true);
        $content['id'] = $id;
        $serializer = $this->get("{{ serializer }}");
        $dContext = DeserializationContext::create()->setGroups(array("Default", "patch"));
        $dContext->attributes->set('related_action', 'merge');
        $entity = $serializer->deserialize(
            json_encode($content),
            '{{ entity_class }}',
            'json',
            $dContext
        );
        $validator = $this->get('validator');
        $validations = $validator->validate($entity, array('Default', 'patch'));
        if ($validations->count() === 0) {
            try {
                $manager->flush();
            } catch (DBALException $e) {
                return $this->handleView(
                    View::create(array('errors'=>array($e->getMessage())), 400)
                );
            }
            return $this->handleView(
                View::create($entity, 200)->setSerializationContext($this->getSerializerContext(array("get")))
            );
        } else {
            return $this->handleView(
                View::create(array('errors'=>$validations), 400)
            );
        }
    }

    /**
     * Delete an existing {{ entity_name }} record
     * @param $id
     *
{% if phpcr %}     * @Route(requirements={"id"=".+"}) {% endif %}
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete{{ controller|capitalize }}Action($id) {
        $manager = $this->get('{{ manager }}');
        $entity = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        $manager->remove($entity);
        $manager->flush();
        return $this->handleView(View::create(null, 204));
    }

{% endblock class_body %}

    protected function getSerializerContext($groups = array(), $version = null) {
        $serializeContext = SerializationContext::create();
        $serializeContext->enableMaxDepthChecks();
        $serializeContext->setGroups(array_merge(
            array(\JMS\Serializer\Exclusion\GroupsExclusionStrategy::DEFAULT_GROUP),
            $groups
        ));
        if ($version !== null) $serializeContext->setVersion($version);
        return $serializeContext;
    }
}
