<?php
namespace Tpg\ExtjsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SerializerParserPass implements CompilerPassInterface {

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (
            $container->getDefinition("nelmio_api_doc.parser.jms_metadata_parser") !== null &&
            (
                $container->getAlias("fos_rest.serializer") == "tpg_extjs.orm_serializer" ||
                $container->getAlias("fos_rest.serializer") == "tpg_extjs.odm_serializer" ||
                $container->getAlias("fos_rest.serializer") == "tpg_extjs.serializer"
            )
        ) {
            $container
                ->getDefinition("nelmio_api_doc.parser.jms_metadata_parser")
                ->replaceArgument(1, new Reference("tpg_extjs.naming_strategy"));
        }
    }
}