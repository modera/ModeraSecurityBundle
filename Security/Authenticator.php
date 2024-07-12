<?php

namespace Modera\SecurityBundle\Security;

use Modera\SecurityBundle\Entity\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class Authenticator implements AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface
{
    private DefaultAuthenticationSuccessHandler $successHandler;

    private DefaultAuthenticationFailureHandler $failureHandler;

    public function __construct(
        HttpUtils $httpUtils,
        HttpKernelInterface $httpKernel,
        ?LoggerInterface $logger = null
    ) {
        $this->successHandler = new DefaultAuthenticationSuccessHandler($httpUtils);
        $this->failureHandler = new DefaultAuthenticationFailureHandler($httpKernel, $httpUtils, [], $logger);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->successHandler->setOptions($options);
        $this->failureHandler->setOptions($options);
    }

    public function setFirewallName(string $firewallName): void
    {
        $this->successHandler->setFirewallName($firewallName);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->isXmlHttpRequest()) {
            $result = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];

            return new JsonResponse($result);
        }

        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        if ($request->isXmlHttpRequest()) {
            $result = static::getAuthenticationResponse($token);

            return new JsonResponse($result);
        }

        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    /**
     * @return array{
     *     'success': bool,
     *     'profile'?: array{
     *         'id': ?int,
     *         'name': ?string,
     *         'email': ?string,
     *         'username': ?string,
     *         'meta': array<string, mixed>,
     *     },
     * }
     */
    public static function getAuthenticationResponse(?TokenInterface $token): array
    {
        $response = ['success' => false];
        if ($token && $token->getUser() instanceof UserInterface) {
            $user = $token->getUser();
            $response = [
                'success' => true,
                'profile' => self::userToArray($user),
            ];
        }

        return $response;
    }

    /**
     * @return array{
     *     'id': ?int,
     *     'name': ?string,
     *     'email': ?string,
     *     'username': ?string,
     *     'meta': array<string, mixed>,
     * }
     */
    public static function userToArray(UserInterface $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'meta' => $user->getMeta(),
        ];
    }
}
