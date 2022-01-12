<?php

namespace Modera\SecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class MailServiceAliasCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter(ModeraSecurityExtension::CONFIG_KEY);

        $aliasConfig = array();
        $aliasConfig['modera_security.password_strength.mail.mail_service'] = $config['password_strength']['mail']['service'];

        $container->addAliases($aliasConfig);
    }
}
