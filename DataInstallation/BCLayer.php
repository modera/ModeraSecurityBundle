<?php

namespace Modera\SecurityBundle\DataInstallation;

use Doctrine\ORM\EntityManagerInterface;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @internal
 * @since 2.54.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class BCLayer
{
    /**
     * @var ManagerRegistry
     */
    private $doctrineRegistry;

    private $mapping = array(
        // old technical name => new
        'user-management' => 'administration',
        'site' => 'general',
    );

    /**
     * @param ManagerRegistry $doctrineRegistry
     */
    public function __construct(ManagerRegistry $doctrineRegistry)
    {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * @param string $technicalName
     *
     * @return string|false
     */
    public function resolveNewPermissionCategoryTechnicalName($technicalName)
    {
        return isset($this->mapping[$technicalName]) ? $this->mapping[$technicalName] : false;
    }

    public function syncPermissionCategoryTechnicalNamesInDatabase()
    {
        // In this method we are going to rename old categories' technical names,
        // find all permissions that could have possible been attached to already new categories and attach
        // then to the renamed old ones. We are choosing this not-straighforward approach because
        // it is theoretically more likely that there're some data linked already to old categories
        // rather than to the new one, so it is more bulletproof simply to renew old ones and attempt
        // to delete new ones

        $em = $this->doctrineRegistry->getManagerForClass(PermissionCategory::clazz());
        $categoryRepository = $em->getRepository(PermissionCategory::clazz());
        $permissionRepository = $em->getRepository(Permission::clazz());

        if (!$em instanceof EntityManagerInterface) {
            throw new \RuntimeException('Instance of EntityManager is expected.');
        }
        /* @var EntityManagerInterface $em */

        $categoryIdsToRemove = [];

        foreach ($this->mapping as $oldTechnicalName => $newTechnicalName) {
            /* @var PermissionCategory $newCategory */
            $newCategory = $categoryRepository->findOneBy(array(
                'technicalName' => $newTechnicalName,
            ));
            /* @var PermissionCategory $oldCategory */
            $oldCategory = $categoryRepository->findOneBy(array(
                'technicalName' => $oldTechnicalName,
            ));

            $oldCategoryExists = null !== $oldCategory;
            $newPermissionCategoryAlreadyHasBeenInstalledBefore = null !== $newCategory;
            if ($oldCategoryExists) {
                if ($newPermissionCategoryAlreadyHasBeenInstalledBefore) {
                    /* @var Permission[] $permissions */
                    $permissions = $permissionRepository->findBy(array(
                        'category' => $newCategory->getId(),
                    ));

                    foreach ($permissions as $permission) {
                        $permission->setCategory($oldCategory);
                    }

                    $categoryIdsToRemove[] = $newCategory->getId();
                }

                // renaming existing old category name to a new one
                $oldCategory->setTechnicalName(
                    $this->resolveNewPermissionCategoryTechnicalName($oldCategory->getTechnicalName())
                );
            }
        }

        $em->flush();

        if (count($categoryIdsToRemove)) {
            try {
                $query = $em->createQuery(sprintf('DELETE FROM %s e WHERE e.id IN (?0)', PermissionCategory::clazz()));
                $query->execute([$categoryIdsToRemove]);
            } catch (\Exception $e) {
                // It might happen that we can't delete those categories because there are some other established
                // relations with them, in this case we just suppress the problem because it is not that critical
                // if they remain in database, they just won't be used
            }
        }
    }
}