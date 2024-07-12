<?php

namespace Modera\SecurityBundle\Tests\Unit\Security;

use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\Security\Authenticator;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class AuthenticatorTest extends \PHPUnit\Framework\TestCase
{
    private function createAuthenticator()
    {
        $httpUtils = \Phake::mock('Symfony\Component\Security\Http\HttpUtils');
        $httpKernel = \Phake::mock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $response = \Phake::mock('Symfony\Component\HttpFoundation\Response');

        \Phake::when($httpUtils)->createRedirectResponse->thenReturn($response);

        return new Authenticator($httpUtils, $httpKernel);
    }

    public function testResponseOnAuthenticationFailure()
    {
        $authenticator = $this->createAuthenticator();

        $request = \Phake::mock('Symfony\Component\HttpFoundation\Request');
        $session = \Phake::mock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $exception = \Phake::mock('Symfony\Component\Security\Core\Exception\AuthenticationException');

        \Phake::when($request)->getSession()->thenReturn($session);

        $resp = $authenticator->onAuthenticationFailure($request, $exception);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $resp);

        \Phake::when($request)->isXmlHttpRequest()->thenReturn(true);

        $resp = $authenticator->onAuthenticationFailure($request, $exception);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $resp);
    }

    public function testResponseOnAuthenticationSuccess()
    {
        $authenticator = $this->createAuthenticator();

        $request = \Phake::mock('Symfony\Component\HttpFoundation\Request');
        $session = \Phake::mock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $token = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        \Phake::when($request)->getSession()->thenReturn($session);

        $resp = $authenticator->onAuthenticationSuccess($request, $token);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $resp);

        \Phake::when($request)->isXmlHttpRequest()->thenReturn(true);

        $resp = $authenticator->onAuthenticationSuccess($request, $token);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $resp);
    }

    public function testGetAuthenticationResponse()
    {
        $token = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $resp = Authenticator::getAuthenticationResponse($token);
        $this->assertInternalType('array', $resp);
        $this->assertArrayHasKey('success', $resp);
        $this->assertFalse($resp['success']);

        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john.doe@test.test');
        $user->setUsername('john.doe');

        \Phake::when($token)->isAuthenticated()->thenReturn(true);
        \Phake::when($token)->getUser()->thenReturn($user);
        \Phake::when($token)->getRoleNames()->thenReturn(array('ROLE_USER'));

        $resp = Authenticator::getAuthenticationResponse($token);
        $this->assertInternalType('array', $resp);
        $this->assertArrayHasKey('success', $resp);
        $this->assertTrue($resp['success']);
        $this->assertArrayHasKey('profile', $resp);
        $this->assertInternalType('array', $resp['profile']);
        $this->assertEquals(array(
            'id' => $user->getId(),
            'name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'meta' => $user->getMeta(),
        ), $resp['profile']);
    }

    public function testUserToArray()
    {
        $user = \Phake::mock(User::class);
        \Phake::when($user)->getId()->thenReturn(777);
        \Phake::when($user)->getFullName()->thenReturn('John Doe');
        \Phake::when($user)->getEmail()->thenReturn('john.doe@example.org');
        \Phake::when($user)->getUserIdentifier()->thenReturn('john.doe');
        \Phake::when($user)->getUsername()->thenReturn('john.doe');
        \Phake::when($user)->getMeta()->thenReturn(array());

        $result = Authenticator::userToArray($user);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEquals(777, $result['id']);
        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals('john.doe@example.org', $result['email']);
        $this->assertEquals('john.doe', $result['username']);
        $this->assertInternalType('array', $result['meta']);
    }
}
