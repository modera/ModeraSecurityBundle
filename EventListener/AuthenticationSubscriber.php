<?php

namespace Modera\SecurityBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class AuthenticationSubscriber implements EventSubscriberInterface
{
    private ObjectManager $om;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->om = $doctrine->getManager();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime());

            if (UserInterface::STATE_NEW === $user->getState()) {
                $user->setState(UserInterface::STATE_ACTIVE);
            }

            if ($this->om instanceof EntityManagerInterface) {
                $this->om->createQuery(\sprintf(
                    'UPDATE %s u SET u.lastLogin = :lastLogin, u.state = :state WHERE u.id = :id',
                    User::class
                ))
                    ->setParameter('lastLogin', $user->getLastLogin())
                    ->setParameter('state', $user->getState())
                    ->setParameter('id', $user->getId())
                    ->execute()
                ;
            } else {
                $this->om->persist($user);
                $this->om->flush();
            }
        }
    }
}
