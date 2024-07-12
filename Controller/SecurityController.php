<?php

namespace Modera\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SecurityController extends Controller
{
    private AuthenticationUtils $helper;

    public function __construct(AuthenticationUtils $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @Route("/login", name="_security_login")
     */
    public function loginAction(): Response
    {
        return $this->render('@ModeraSecurity/security/login.html.twig', [
            'last_username' => $this->helper->getLastUsername(),
            'error' => $this->helper->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/login_check", name="_security_check")
     */
    public function securityCheckAction(): void
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="_security_logout")
     */
    public function logoutAction(): void
    {
        // The security layer will intercept this request
    }
}
