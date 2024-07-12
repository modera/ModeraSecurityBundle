<?php

namespace Modera\SecurityBundle\Model;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class Permission implements PermissionInterface
{
    /**
     * @see PermissionInterface::getRole()
     */
    private string $role;

    /**
     * @see PermissionInterface::getName()
     */
    private string $name;

    /**
     * @see PermissionInterface::getCategory()
     */
    private ?string $category;

    /**
     * @see PermissionInterface::getDescription()
     */
    private ?string $description;

    public function __construct(string $name, string $role, ?string $category = null, ?string $description = null)
    {
        $this->category = $category;
        $this->description = $description;
        $this->name = $name;
        $this->role = $role;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
}
