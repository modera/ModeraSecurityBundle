<?php

namespace Modera\SecurityBundle\Tests\Unit\EventListener;

use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\EventListener\RootUserHandlerInjectionListener;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class RootUserHandlerInjectionListenerTest extends \PHPUnit\Framework\TestCase
{
    private $container;

    private $rootUserHandler;

    public function setUp(): void
    {
        $this->rootUserHandler = \Phake::mock('Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface');

        $this->container = \Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        \Phake::when($this->container)
            ->get('modera_security.root_user_handling.handler')
            ->thenReturn($this->rootUserHandler)
        ;
    }

    private function createEvent($object = null)
    {
        $event = \Phake::mock('Doctrine\Persistence\Event\LifecycleEventArgs');

        \Phake::when($event)
            ->getObject()
            ->thenReturn($object)
        ;

        return $event;
    }

    public function testPostLoadWithEntity()
    {
        $user = \Phake::mock(User::class);

        $event = $this->createEvent($user);

        $listener = new RootUserHandlerInjectionListener($this->container);
        $listener->postLoad($user, $event);

        \Phake::verify($user)->init($this->rootUserHandler);
    }
}
