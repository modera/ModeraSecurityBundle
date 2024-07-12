<?php

namespace Modera\SecurityBundle\EventListener;

use Modera\SecurityBundle\ModeraSecurityBundle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\Firewall\SwitchUserListener;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2019 Modera Foundation
 */
class SwitchUserSubscriber implements EventSubscriberInterface
{
    private ?string $redirectUri = null;

    private ?string $usernameParameter = null;

    /**
     * @param array<string, mixed> $bundleConfig
     */
    public function __construct(array $bundleConfig = [])
    {
        if (\is_array($bundleConfig['switch_user'] ?? null) && \is_string($bundleConfig['switch_user']['parameter'] ?? null)) {
            $this->usernameParameter = $bundleConfig['switch_user']['parameter'];
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->redirectUri) {
            $event->setResponse(new RedirectResponse($this->redirectUri));
            $this->redirectUri = null;
        }
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        if ($this->usernameParameter) {
            $request = $event->getRequest();
            $targetUser = $event->getTargetUser();

            $username = $request->get($this->usernameParameter) ?: $request->headers->get($this->usernameParameter);

            if (SwitchUserListener::EXIT_VALUE !== $username) {
                $exit = false;
                $token = $event->getToken();
                if ($token instanceof SwitchUserToken) {
                    $exit = $token->getOriginalToken()->getUser() && $token->getOriginalToken()->getUser()->getUserIdentifier() === $targetUser->getUserIdentifier();
                }

                if ($exit || \in_array(ModeraSecurityBundle::ROLE_ROOT_USER, $targetUser->getRoles())) {
                    $this->redirectUri = \str_replace($targetUser->getUserIdentifier(), SwitchUserListener::EXIT_VALUE, $request->getUri());
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
            SecurityEvents::SWITCH_USER => 'onSwitchUser',
        ];
    }
}
