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
    public function process(ContainerBuilder $container): void
    {
        /** @var array{'password_strength': array{'mail': array{'service': string}}} $config */
        $config = $container->getParameter(ModeraSecurityExtension::CONFIG_KEY);

        $aliasConfig = [];
        $aliasConfig['modera_security.password_strength.mail.mail_service'] = $config['password_strength']['mail']['service'];

        $container->addAliases($aliasConfig);
    }
}
