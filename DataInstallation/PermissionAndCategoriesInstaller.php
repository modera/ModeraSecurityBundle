<?php

namespace Modera\SecurityBundle\DataInstallation;

use Doctrine\ORM\EntityManager;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\FoundationBundle\Utils\DeprecationNoticeEmitter;
use Modera\SecurityBundle\Model\PermissionCategoryInterface;
use Modera\SecurityBundle\Model\PermissionInterface;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Modera\SecurityBundle\Entity\Permission;

/**
 * Service responsible for installing permissions and permission categories so later they can be used to manage
 * user permissions.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionAndCategoriesInstaller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContributorInterface
     */
    private $permissionCategoriesProvider;

    /**
     * @var ContributorInterface
     */
    private $permissionsProvider;

    /**
     * @var BCLayer
     */
    private $bcLayer;

    /**
     * @var DeprecationNoticeEmitter
     */
    private $deprecationNoticeEmitter;

    /**
     * @var array
     */
    private $sortingPosition;

    /**
     * @internal Since 2.54.0
     *
     * @param EntityManager            $em
     * @param ContributorInterface     $permissionCategoriesProvider
     * @param ContributorInterface     $permissionsProvider
     * @param BCLayer                  $bcLayer
     * @param DeprecationNoticeEmitter $deprecationNoticeEmitter
     */
    public function __construct(
        EntityManager $em,
        ContributorInterface $permissionCategoriesProvider,
        ContributorInterface $permissionsProvider,
        BCLayer $bcLayer = null,
        DeprecationNoticeEmitter $deprecationNoticeEmitter = null,
        array $sortingPosition = array()
    ) {
        $this->em = $em;
        $this->permissionCategoriesProvider = $permissionCategoriesProvider;
        $this->permissionsProvider = $permissionsProvider;
        $this->bcLayer = $bcLayer;
        $this->deprecationNoticeEmitter = $deprecationNoticeEmitter;
        $this->sortingPosition = array_merge(array(
            'categories' => array(),
            'perrmissions' => array(),
        ), $sortingPosition);
    }

    /**
     * @return array
     */
    public function installCategories()
    {
        $permissionCategoriesInstalled = 0;
        $sortingPosition = $this->sortingPosition['categories'];

        /* @var PermissionCategoryInterface[] $permissionCategories */
        $permissionCategories = $this->permissionCategoriesProvider->getItems();
        if (count($permissionCategories) > 0) {
            foreach ($permissionCategories as $permissionCategory) {
                /* @var PermissionCategory $entityPermissionCategory */
                $entityPermissionCategory = $this->em->getRepository(PermissionCategory::class)->findOneBy(array(
                    'technicalName' => $permissionCategory->getTechnicalName(),
                ));

                if (!$entityPermissionCategory) {
                    $entityPermissionCategory = new PermissionCategory();
                    $entityPermissionCategory->setTechnicalName($permissionCategory->getTechnicalName());

                    $this->em->persist($entityPermissionCategory);

                    ++$permissionCategoriesInstalled;
                }

                $entityPermissionCategory->setName($permissionCategory->getName());

                $position = 0;
                if (isset($sortingPosition[$entityPermissionCategory->getTechnicalName()])) {
                    $position = $sortingPosition[$entityPermissionCategory->getTechnicalName()];
                }
                $entityPermissionCategory->setPosition($position);
            }
        }

        $this->em->flush();

        if ($this->bcLayer) {
            $this->bcLayer->syncPermissionCategoryTechnicalNamesInDatabase();
        }

        return array(
            'installed' => $permissionCategoriesInstalled,
            //'removed' => 0,
        );
    }

    /**
     * @return array
     */
    public function installPermissions()
    {
        $permissionInstalled = 0;
        $sortingPosition = $this->sortingPosition['perrmissions'];

        /* @var PermissionInterface[] $permissions */
        $permissions = $this->permissionsProvider->getItems();
        foreach ($permissions as $permission) {
            $entityPermission = $this->em->getRepository(Permission::class)->findOneBy(array(
                'roleName' => $permission->getRole(),
            ));

            if (!$entityPermission) {
                $entityPermission = new Permission();
                $entityPermission->setRoleName($permission->getRole());

                $this->em->persist($entityPermission);

                ++$permissionInstalled;
            }

            $entityPermission->setDescription($permission->getDescription());
            $entityPermission->setName($permission->getName());

            $position = 0;
            if (isset($sortingPosition[$entityPermission->getRoleName()])) {
                $position = $sortingPosition[$entityPermission->getRoleName()];
            }
            $entityPermission->setPosition($position);

            $categoryTechnicalName = $permission->getCategory();
            if ($this->bcLayer) {
                // MPFE-964, see \Modera\BackendSecurityBundle\Contributions\PermissionCategoriesProvider
                $newCategoryName = $this->bcLayer->resolveNewPermissionCategoryTechnicalName($categoryTechnicalName);
                if (false !== $newCategoryName) {
                    $this->emitDeprecationNotice(sprintf(
                        'Permission category "%s" is deprecated, you must use "%s" category instead when contributing new permissions.',
                        $categoryTechnicalName, $newCategoryName
                    ));

                    $categoryTechnicalName = $newCategoryName;
                }
            }

            /* @var PermissionCategory $category */
            $category = $this->em->getRepository(PermissionCategory::class)->findOneBy(array(
                'technicalName' => $categoryTechnicalName,
            ));
            if ($category) {
                $entityPermission->setCategory($category);
            }
        }

        $this->em->flush();

        return array(
            'installed' => $permissionInstalled,
            //'removed' => 0,
        );
    }

    private function emitDeprecationNotice($notice)
    {
        if ($this->deprecationNoticeEmitter) {
            $this->deprecationNoticeEmitter->emit($notice);
        }
    }
}
