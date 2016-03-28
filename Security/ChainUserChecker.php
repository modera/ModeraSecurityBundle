<?php

namespace Modera\SecurityBundle\Security;

use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Konstantin Myakshin <koc-dp@yandex.ru>
 * @copyright 2016 Modera Foundation
 */
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
        /* @var $userChecker UserCheckerInterface */
        foreach ($this->resourcesProvider->getItems() as $userChecker) {
            $userChecker->checkPreAuth($user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        /* @var $userChecker UserCheckerInterface */
        foreach ($this->resourcesProvider->getItems() as $userChecker) {
            $userChecker->checkPostAuth($user);
        }
    }
}
