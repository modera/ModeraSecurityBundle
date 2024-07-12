<?php

namespace Modera\SecurityBundle\Tests\Unit\DependencyInjection;

use Modera\SecurityBundle\DependencyInjection\MailServiceAliasCompilerPass;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class MailServiceAliasCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->setParameter(
            ModeraSecurityExtension::CONFIG_KEY,
            array(
                'password_strength' => array(
                    'mail' => array(
                        'service' => 'foo_service',
                    ),
                ),
            )
        );

        $compilerPass = new MailServiceAliasCompilerPass();
        $compilerPass->process($container);

        $this->assertEquals(
            'foo_service',
            (string)$container->getAlias('modera_security.password_strength.mail.mail_service')
        );
    }
}
