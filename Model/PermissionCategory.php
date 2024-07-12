<?php

namespace Modera\SecurityBundle\Model;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionCategory implements PermissionCategoryInterface
{
    private string $name;

    private string $technicalName;

    public function __construct(string $name, string $technicalName)
    {
        $this->name = $name;
        $this->technicalName = $technicalName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }
}
