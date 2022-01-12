<?php

namespace Modera\SecurityBundle\Tests\Unit\EventListener;

use Modera\SecurityBundle\EventListener\AuthenticationSubscriber;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class AuthenticationSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private function createAuthenticationSubscriber()
    {
        $om = \Phake::mock('Doctrine\ORM\EntityManagerInterface');
        $user = \Phake::mock(User::clazz());
        $doctrine = \Phake::mock('Doctrine\Persistence\ManagerRegistry');

        \Phake::when($om)->persist($user)->thenReturn(null);
        \Phake::when($om)->flush()->thenReturn(null);
        \Phake::when($doctrine)->getManager()->thenReturn($om);

        return new AuthenticationSubscriber($doctrine);
    }

    public function testUserStateChangeOnAuthenticationSuccess()
    {
        $user = new User();
        $subscriber = $this->createAuthenticationSubscriber();

        $event = \Phake::mock('Symfony\Component\Security\Core\Event\AuthenticationEvent');
        $token = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        \Phake::when($event)->getAuthenticationToken()->thenReturn($token);
        \Phake::when($token)->getUser()->thenReturn($user);

        $this->assertSame(User::STATE_NEW, $user->getState());
        $this->assertNull($user->getLastLogin());
        $subscriber->onAuthenticationSuccess($event);
        $this->assertSame(User::STATE_ACTIVE, $user->getState());
        $this->assertInstanceOf(\DateTime::class, $user->getLastLogin());
    }
}
