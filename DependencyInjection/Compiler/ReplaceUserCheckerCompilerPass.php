<?php

namespace Modera\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that replaces standard user checker with our chained implementation. Since Symfony 2.8 there is
 * possibility set user checker service by configuration so this class would removed after update.
 *
 * @author    Konstantin Myakshin <koc-dp@yandex.ru>
 * @copyright 2016 Modera Foundation
 */
class ReplaceUserCheckerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        //TODO: remove this compiler pass when update to Symfony 2.8
        $userCheckerDef = clone $container->getDefinition('security.user_checker');
        $container->setDefinition('modera_security.native_user_checker', $userCheckerDef);

        $userCheckerProvider = $container->getDefinition('modera_security.native_user_checker_provider');
        $userCheckerProvider->replaceArgument(0, new Reference('modera_security.native_user_checker'));

        // replace original user checker with our
        $container->setAlias('security.user_checker', 'modera_security.chain_user_checker');
    }
}
