<?php

namespace Modera\SecurityBundle\DataInstallation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;

/**
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class BCLayer
{
    private ManagerRegistry $doctrineRegistry;

    /**
     * @var array<string, string>
     */
    private array $mapping = [
        // old technical name => new
        'user-management' => 'administration',
        'site' => 'general',
    ];

    public function __construct(ManagerRegistry $doctrineRegistry)
    {
        $this->doctrineRegistry = $doctrineRegistry;
    }

    public function resolveNewPermissionCategoryTechnicalName(string $technicalName): ?string
    {
        return $this->mapping[$technicalName] ?? null;
    }

    public function syncPermissionCategoryTechnicalNamesInDatabase(): void
    {
        // In this method we are going to rename old categories' technical names,
        // find all permissions that could have possible been attached to already new categories and attach
        // then to the renamed old ones. We are choosing this not-straightforward approach because
        // it is theoretically more likely that there are some data linked already to old categories
        // rather than to the new one, so it is more bulletproof simply to renew old ones and attempt
        // to delete new ones

        $em = $this->doctrineRegistry->getManagerForClass(PermissionCategory::class);

        if (!$em instanceof EntityManagerInterface) {
            throw new \RuntimeException('Instance of EntityManager is expected.');
        }
        /** @var EntityManagerInterface $em */
        $categoryRepository = $em->getRepository(PermissionCategory::class);
        $permissionRepository = $em->getRepository(Permission::class);

        $categoryIdsToRemove = [];

        foreach ($this->mapping as $oldTechnicalName => $newTechnicalName) {
            /** @var ?PermissionCategory $newCategory */
            $newCategory = $categoryRepository->findOneBy([
                'technicalName' => $newTechnicalName,
            ]);

            /** @var ?PermissionCategory $oldCategory */
            $oldCategory = $categoryRepository->findOneBy([
                'technicalName' => $oldTechnicalName,
            ]);

            $oldCategoryExists = null !== $oldCategory;
            $newPermissionCategoryAlreadyHasBeenInstalledBefore = null !== $newCategory;

            if ($oldCategoryExists) {
                if ($newPermissionCategoryAlreadyHasBeenInstalledBefore) {
                    /** @var Permission[] $permissions */
                    $permissions = $permissionRepository->findBy([
                        'category' => $newCategory->getId(),
                    ]);

                    foreach ($permissions as $permission) {
                        $permission->setCategory($oldCategory);
                    }

                    $categoryIdsToRemove[] = $newCategory->getId();
                }

                $newTechnicalName = $this->resolveNewPermissionCategoryTechnicalName($oldCategory->getTechnicalName());
                if ($newTechnicalName) {
                    // renaming existing old category name to a new one
                    $oldCategory->setTechnicalName($newTechnicalName);
                }
            }
        }

        $em->flush();

        if (\count($categoryIdsToRemove)) {
            try {
                $query = $em->createQuery(\sprintf('DELETE FROM %s e WHERE e.id IN (?0)', PermissionCategory::class));
                $query->execute([$categoryIdsToRemove]);
            } catch (\Exception $e) {
                // It might happen that we can't delete those categories because there are some other established
                // relations with them, in this case we just suppress the problem because it is not that critical
                // if they remain in database, they just won't be used
            }
        }
    }
}
