<?php

namespace {{ namespace }}\Controller;

{% block use_statements %}
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use \JMS\Serializer\SerializationContext;
use \JMS\Serializer\SerializerBuilder;
use \Tpg\ExtjsBundle\Component\JMSCamelCaseNamingStrategy;
use \Doctrine\DBAL\DBALException;
use Tpg\ExtjsBundle\Component\FailedObjectConstructor;
use JMS\Serializer\Construction\DoctrineObjectConstructor;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use \JMS\Serializer\DeserializationContext;
use {{ entity_class }};
use {{ entity_type_class }};
{% if 'annotation' == format.routing -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
{% endif %}
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
    /**
     * Get detail of a {{ entity_name }} record
     * @param              $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get{{ entity_name|capitalize }}Action($id) {
        /** @var $manager EntityManager */
        $manager = $this->get('doctrine.orm.default_entity_manager');
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get{{ entity_name|capitalize }}sAction(ParamFetcherInterface $paramFetcher) {
        /** @var $manager EntityManager */
        $manager = $this->get('doctrine.orm.default_entity_manager');
        $rawSorters = json_decode($paramFetcher->get("sort"), true);
        $sorters = [];
        foreach ($rawSorters as $s) {
            $sorters[$s['property']] = $s['direction'];
        }
        $rawFilters = json_decode($paramFetcher->get("filter"), true);
        $filters = [];
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
        $view = View::create($list, 200)->setSerializationContext($this->getSerializerContext(array("list")));
        return $this->handleView($view);
    }

    /**
     * Create a new {{ entity_name }} record
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function post{{ entity_name|capitalize }}sAction() {
        $serializer = SerializerBuilder::create()->setObjectConstructor(
            new DoctrineObjectConstructor($this->get("doctrine"), new UnserializeObjectConstructor())
        )->setPropertyNamingStrategy(
            new JMSCamelCaseNamingStrategy()
        )->build();
        $entity = $serializer->deserialize(
            $this->getRequest()->getContent(),
            '{{ entity_class }}',
            'json',
            DeserializationContext::create()->setGroups(array("Default", "post"))
        );
        $validator = $this->get('validator');
        $validations = $validator->validate($entity);
        if ($validations->count() === 0) {
            $manager = $this->get('doctrine.orm.default_entity_manager');
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
                    "{{route_name_prefix}}get_{{ entity_name|lower }}",
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function put{{ entity_name|capitalize }}Action($id) {
        /** @var EntityManager $manager */
        $manager = $this->get('doctrine.orm.default_entity_manager');
        $entity = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        if ($entity === null) {
            return $this->handleView(View::create('', 404));
        }
        $serializer = SerializerBuilder::create()->setObjectConstructor(
            new DoctrineObjectConstructor($this->get("doctrine"), new UnserializeObjectConstructor())
        )->setPropertyNamingStrategy(
            new JMSCamelCaseNamingStrategy()
        )->build();
        $entity = $serializer->deserialize(
            $this->getRequest()->getContent(),
            '{{ entity_class }}',
            'json',
            DeserializationContext::create()->setGroups(array("Default", "put"))
        );
        $entity->setId($id);
        $validator = $this->get('validator');
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patch{{ entity_name|capitalize }}Action($id) {
        /** @var EntityManager $manager */
        $manager = $this->get('doctrine.orm.default_entity_manager');
        $entity = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        if ($entity === null) {
            return $this->handleView(View::create('', 404));
        }
        $content = json_decode($this->getRequest()->getContent(), true);
        $content['id'] = $id;
        $serializer = SerializerBuilder::create()->setObjectConstructor(
            new DoctrineObjectConstructor($this->get("doctrine"), new FailedObjectConstructor())
        )->setPropertyNamingStrategy(
            new JMSCamelCaseNamingStrategy()
        )->build();
        $entity = $serializer->deserialize(
            json_encode($content),
            '{{ entity_class }}',
            'json',
            DeserializationContext::create()->setGroups(array("Default", "patch"))
        );
        $validator = $this->get('validator');
        $validations = $validator->validate($entity);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete{{ entity_name|capitalize }}Action($id) {
        /** @var EntityManager $manager */
        $manager = $this->get('doctrine.orm.default_entity_manager');
        $entity = $manager->getRepository('{{ entity_bundle }}:{{ entity }}')->find($id);
        $manager->remove($entity);
        $manager->flush();
        return $this->handleView(View::create(null, 204));
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

    protected function getSerializerContext($groups = array(), $version = null) {
        $serializeContext = SerializationContext::create();
        $serializeContext->enableMaxDepthChecks();
        $serializeContext->setGroups(array(\JMS\Serializer\Exclusion\GroupsExclusionStrategy::DEFAULT_GROUP)+$groups);
        if ($version !== null) $serializeContext->setVersion($version);
        return $serializeContext;
    }
}
