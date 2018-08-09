<?php

namespace Modera\SecurityBundle\Tests\Unit\Entity;

use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testFirstLastMiddleName()
    {
        $user = new User();

        $user->setFirstName('<First:Name>');
        $user->setLastName('*Last@Name*');
        $user->setMiddleName('"Middle_Name"');

        $this->assertSame('FirstName', $user->getFirstName());
        $this->assertSame('LastName', $user->getLastName());
        $this->assertSame('MiddleName', $user->getMiddleName());

        $user->setFirstName('First-Name');
        $user->setLastName('Last Name');
        $user->setMiddleName('Middle - Name');

        $this->assertSame('First-Name', $user->getFirstName());
        $this->assertSame('Last Name', $user->getLastName());
        $this->assertSame('Middle - Name', $user->getMiddleName());
    }

    public function testGetRawRoles()
    {
        $user = new User();

        $this->assertEquals(0, count($user->getRawRoles()));

        // ---

        $groupPermission = \Phake::mock(Permission::clazz());
        $userPermission = \Phake::mock(Permission::clazz());

        $group = \Phake::mock(Group::clazz());
        \Phake::when($group)
            ->getPermissions()
            ->thenReturn([$groupPermission])
        ;

        $user->addPermission($userPermission);
        $user->setGroups([$group]);

        $userRoles = $user->getRawRoles();

        $this->assertEquals(2, count($userRoles));
        $this->assertSame($groupPermission, $userRoles[0]);
        $this->assertSame($userPermission, $userRoles[1]);
    }

    public function testGetRoles()
    {
        $user = new User();

        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        // ---

        $rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');
        \Phake::when($rootUserHandler)
            ->isRootUser($user)
            ->thenReturn(false)
        ;
        \Phake::when($rootUserHandler)
            ->getRoles()
            ->thenReturn(['ROLE_FOO', 'ROLE_BAR'])
        ;

        $user->init($rootUserHandler);
        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        // ---

        \Phake::when($rootUserHandler)
            ->isRootUser($user)
            ->thenReturn(true)
        ;

        $this->assertEquals(['ROLE_FOO', 'ROLE_BAR'], $user->getRoles());
    }

    public function testValidateAndSetPassword()
    {
        $pm = \Phake::mock(PasswordManager::class);

        $user = new User();
        $user->validateAndSetPassword($pm, 'foo1234');

        \Phake::verify($pm)
            ->encodeAndSetPassword($user, 'foo1234')
        ;
    }

    public function testGetFullName()
    {
        $user = new User();
        $user->setFirstName('First');
        $user->setLastName('Last');
        $user->setMiddleName('Middle');
        $user->setUsername('johnsnow');

        $this->assertSame('First Last', $user->getFullName());

        $user->setFirstName('');
        $user->setLastName('');
        $user->setMiddleName('');
        $this->assertSame('johnsnow', $user->getFullName());
    }
}
