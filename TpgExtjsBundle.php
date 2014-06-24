<?php

namespace Tpg\ExtjsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass;
use Symfony\Component\DependencyInjection\Definition;
use Tpg\ExtjsBundle\DependencyInjection\SerializerParserPass;

class TpgExtjsBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {

        parent::build($builder);

        $builder->addCompilerPass(new LazyServiceMapPass('tpg_extjs.serialization_visitor', 'format',
            function(ContainerBuilder $container, Definition $def) {
                if ($container->hasDefinition("tpg_extjs.orm_serializer"))
                    $container->getDefinition('tpg_extjs.orm_serializer')->replaceArgument(3, $def);
                if ($container->hasDefinition("tpg_extjs.odm_serializer"))
                    $container->getDefinition('tpg_extjs.odm_serializer')->replaceArgument(3, $def);
                if ($container->hasDefinition("tpg_extjs.phpcr_serializer"))
                    $container->getDefinition('tpg_extjs.phpcr_serializer')->replaceArgument(3, $def);
            }
        ));
        $builder->addCompilerPass(new LazyServiceMapPass('tpg_extjs.deserialization_visitor', 'format',
            function(ContainerBuilder $container, Definition $def) {
                if ($container->hasDefinition("tpg_extjs.orm_serializer"))
                    $container->getDefinition('tpg_extjs.orm_serializer')->replaceArgument(4, $def);
                if ($container->hasDefinition("tpg_extjs.odm_serializer"))
                    $container->getDefinition('tpg_extjs.odm_serializer')->replaceArgument(4, $def);
                if ($container->hasDefinition("tpg_extjs.phpcr_serializer"))
                    $container->getDefinition('tpg_extjs.phpcr_serializer')->replaceArgument(4, $def);
            }
        ));

        $builder->addCompilerPass(new SerializerParserPass());
    }
}
