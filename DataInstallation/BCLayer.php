<?php

namespace Modera\SecurityBundle\DataInstallation;

/**
 * @internal
 * @since 2.54.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class BCLayer
{
    private $mapping = array(
        'user-management' => 'administration' // MPFE-964; see \Modera\BackendSecurityBundle\Contributions\PermissionCategoriesProvider
    );

    /**
     * @param string $technicalName
     *
     * @return string|false
     */
    public function resolveNewPermissionCategoryTechnicalName($technicalName)
    {
        return isset($this->mapping[$technicalName]) ? $this->mapping[$technicalName] : false;
    }
}