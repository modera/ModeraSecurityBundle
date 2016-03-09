<?php

namespace Modera\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReplaceUserCheckerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // remove this compiler pass when update to Symfony 2.8
        $userChecckerDef = clone $container->getDefinition('security.user_checker');
        $container->setDefinition('modera_security.native_user_checker', $userChecckerDef);

        $userCheckerProvider = $container->getDefinition('modera_security.native_user_checker_provider');
        $userCheckerProvider->replaceArgument(0, new Reference('modera_security.native_user_checker'));

        // replace original user checker with our
        $container->setAlias('security.user_checker', 'modera_security.chain_user_checker');
    }

}
