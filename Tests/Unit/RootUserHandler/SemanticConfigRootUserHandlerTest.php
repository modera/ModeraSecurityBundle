<?php

namespace Modera\SecurityBundle\Tests\Unit\RootUserHandler;

use Doctrine\ORM\Query;
use Modera\SecurityBundle\RootUserHandling\SemanticConfigRootUserHandler;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\ModeraSecurityBundle;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SemanticConfigRootUserHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testIsRootUser()
    {
        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $bundleConfig = array(
            'root_user' => array(
                'query' => array('dat', 'is', 'query'),
            ),
        );

        $em = \Phake::mock('Doctrine\ORM\EntityManagerInterface');

        \Phake::when($container)->getParameter(ModeraSecurityExtension::CONFIG_KEY)->thenReturn($bundleConfig);
        \Phake::when($container)->get('doctrine.orm.entity_manager')->thenReturn($em);

        $handler = new SemanticConfigRootUserHandler($container);

        $anonymousUser = \Phake::mock(User::class);
        $rootUser = \Phake::mock(User::class);

        $dbUser = \Phake::mock(User::class);
        \Phake::when($dbUser)->isEqualTo($anonymousUser)->thenReturn(false);
        \Phake::when($dbUser)->isEqualTo($rootUser)->thenReturn(true);

        $userRepository = \Phake::mock('Doctrine\Persistence\ObjectRepository');
        \Phake::when($userRepository)->findOneBy($bundleConfig['root_user']['query'])->thenReturn($dbUser);
        \Phake::when($em)->getRepository(User::class)->thenReturn($userRepository);

        $this->assertFalse($handler->isRootUser($anonymousUser));
        $this->assertTrue($handler->isRootUser($rootUser));
    }

    public function testGetRolesWithAsterisk()
    {
        $em = \Phake::mock('Doctrine\ORM\EntityManagerInterface');
        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $bundleConfig = array(
            'root_user' => array(
                'roles' => '*',
            ),
        );

        $databaseRoles = array(
            array('roleName' => 'FOO_ROLE'),
            array('roleName' => 'BAR_ROLE'),
        );

        \Phake::when($container)->get('doctrine.orm.entity_manager')->thenReturn($em);
        \Phake::when($container)->getParameter(ModeraSecurityExtension::CONFIG_KEY)->thenReturn($bundleConfig);
        $query = \Phake::mock('Doctrine\ORM\AbstractQuery');
        \Phake::when($em)->createQuery(sprintf('SELECT e.roleName FROM %s e', Permission::class))->thenReturn($query);
        \Phake::when($query)->getResult(Query::HYDRATE_SCALAR)->thenReturn($databaseRoles);

        $handler = new SemanticConfigRootUserHandler($container);

        $this->assertSame(array('FOO_ROLE', 'BAR_ROLE', ModeraSecurityBundle::ROLE_ROOT_USER), $handler->getRoles());
    }

    public function testGetRolesAsArray()
    {
        $em = \Phake::mock('Doctrine\ORM\EntityManagerInterface');
        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $bundleConfig = array(
            'root_user' => array(
                'roles' => array('FOO_ROLE', 'BAR_ROLE'),
            ),
        );

        \Phake::when($container)->getParameter(ModeraSecurityExtension::CONFIG_KEY)->thenReturn($bundleConfig);
        \Phake::when($container)->get('doctrine.orm.entity_manager')->thenReturn($em);

        $handler = new SemanticConfigRootUserHandler($container);

        $expected = array_merge($bundleConfig['root_user']['roles'], array(ModeraSecurityBundle::ROLE_ROOT_USER));
        $this->assertSame($expected, $handler->getRoles());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetRolesNeitherStringNorArrayDefined()
    {
        $em = \Phake::mock('Doctrine\ORM\EntityManagerInterface');
        $container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $bundleConfig = array(
            'root_user' => array(
                'roles' => new \stdClass(),
            ),
        );

        \Phake::when($container)->getParameter(ModeraSecurityExtension::CONFIG_KEY)->thenReturn($bundleConfig);
        \Phake::when($container)->get('doctrine.orm.entity_manager')->thenReturn($em);

        $handler = new SemanticConfigRootUserHandler($container);

        $handler->getRoles();
    }
}
