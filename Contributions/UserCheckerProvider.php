<?php

namespace Modera\SecurityBundle\Contributions;

use Sli\ExpanderBundle\Ext\OrderedContributorInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserCheckerProvider implements OrderedContributorInterface
{
    private $order;

    private $userChecker;

    public function __construct(UserCheckerInterface $userChecker, $order = 0)
    {
        $this->userChecker = $userChecker;
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return array($this->userChecker);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->order;
    }
}
