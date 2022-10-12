<?php

namespace Modera\SecurityBundle\Entity;

use Modera\SecurityBundle\Model\UserInterface as ModeraUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
interface UserInterface extends EquatableInterface, SymfonyUserInterface, ModeraUserInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return Group[]
     */
    public function getGroups();

    /**
     * @return Permission[]
     */
    public function getPermissions();

    /**
     * @return array
     */
    public function getMeta();
}
