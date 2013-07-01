<?php

namespace Tpg\ExtjsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use JMS\DiExtraBundle\DependencyInjection\Compiler\LazyServiceMapPass;
use Symfony\Component\DependencyInjection\Definition;

class TpgExtjsBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        $builder->addCompilerPass(new LazyServiceMapPass('tpg_extjs.serialization_visitor', 'format',
            function(ContainerBuilder $container, Definition $def) {
                $container->getDefinition('tpg_extjs.serializer')->replaceArgument(3, $def);
            }
        ));
        $builder->addCompilerPass(new LazyServiceMapPass('tpg_extjs.deserialization_visitor', 'format',
            function(ContainerBuilder $container, Definition $def) {
                $container->getDefinition('tpg_extjs.serializer')->replaceArgument(4, $def);
            }
        ));
    }
}
