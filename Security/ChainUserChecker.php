<?php

namespace Modera\SecurityBundle\Security;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ChainUserChecker implements UserCheckerInterface
{
    /**
     * @var ContributorInterface
     */
    private $resourcesProvider;

    /**
     * @param ContributorInterface $resourcesProvider
     */
    public function __construct(ContributorInterface $resourcesProvider)
    {
        $this->resourcesProvider = $resourcesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        foreach ($this->resourcesProvider->getItems() as $userChecker) {
            /* @var $userChecker UserCheckerInterface */
            $userChecker->checkPreAuth($user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        foreach ($this->resourcesProvider->getItems() as $userChecker) {
            /* @var $userChecker UserCheckerInterface */
            $userChecker->checkPostAuth($user);
        }
    }
}
