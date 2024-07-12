<?php

namespace Modera\SecurityBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\Entity\UserInterface;
use Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * TODO move logic Doctrine's repository ?
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UserService
{
    private EntityManagerInterface $em;
    private RootUserHandlerInterface $rootUserHandler;
    private ?RoleHierarchyInterface $roleHierarchy;
    private ?TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $em,
        RootUserHandlerInterface $rootUserHandler,
        ?RoleHierarchyInterface $roleHierarchy = null,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->em = $em;
        $this->rootUserHandler = $rootUserHandler;
        $this->roleHierarchy = $roleHierarchy;
        $this->tokenStorage = $tokenStorage;
    }

    public function save(UserInterface $user): void
    {
        if (!$user->getId()) {
            $this->em->persist($user);
        }
        $this->em->flush($user);
    }

    /**
     * @throws \RuntimeException If given used is root user and cannot be deleted
     */
    public function remove(UserInterface $user): void
    {
        if ($this->rootUserHandler->isRootUser($user)) {
            throw new \RuntimeException(T::trans('ROOT user cannot be removed.'));
        }

        $this->em->remove($user);
        $this->em->flush($user);
    }

    /**
     * @throws \RuntimeException If given used is root user and cannot be disabled
     */
    public function disable(UserInterface $user): void
    {
        if ($this->rootUserHandler->isRootUser($user)) {
            throw new \RuntimeException(T::trans('ROOT user cannot be disabled.'));
        }

        $user->setActive(false);
        $this->em->flush($user);
    }

    public function enable(UserInterface $user): void
    {
        $user->setActive(true);
        $this->em->flush($user);
    }

    /**
     * Find user by some property.
     *
     * @param mixed $value Mixed value
     */
    public function findUserBy(string $property, $value): ?UserInterface
    {
        return $this->em->getRepository(User::class)->findOneBy([$property => $value]);
    }

    /**
     * Find users by some property.
     *
     * @param mixed $value Mixed value
     *
     * @return UserInterface[]
     */
    public function findUsersBy(string $property, $value): array
    {
        return $this->em->getRepository(User::class)->findBy([$property => $value]);
    }

    public function getAuthenticatedUser(): ?UserInterface
    {
        if (null === $this->tokenStorage) {
            return null;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        // @deprecated since 5.4, $user will always be a UserInterface instance
        if (!\is_object($user = $token->getUser()) || !($user instanceof UserInterface)) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }

    public function getRootUser(): UserInterface
    {
        return $this->rootUserHandler->getUser();
    }

    public function isRootUser(UserInterface $user): bool
    {
        return $this->rootUserHandler->isRootUser($user);
    }

    public function isGranted(UserInterface $user, string $roleName): bool
    {
        $roles = $user->getRoles();
        if (null !== $this->roleHierarchy) {
            $roles = $this->roleHierarchy->getReachableRoleNames($roles);
        }

        return \in_array($roleName, $roles, true);
    }

    /**
     * @return UserInterface[]
     */
    public function getUsersByRole(string $roleName): array
    {
        $ids = $this->getIdsByRole($roleName);
        if (\count($ids)) {
            return $this->findUsersBy('id', $ids);
        }

        return [];
    }

    /**
     * @return int[]
     */
    public function getIdsByRole(string $roleName): array
    {
        $ids = [];

        $qb = $this->em->createQueryBuilder();
        $qb->select('p, u, g')
            ->from(Permission::class, 'p')
            ->leftJoin('p.users', 'u')
            ->leftJoin('p.groups', 'g')
            ->where($qb->expr()->eq('p.roleName', ':roleName'))
                ->setParameter('roleName', $roleName)
        ;

        $query = $qb->getQuery();

        /** @var ?array{
         *     'users': array{'id': int}[],
         *     'groups': array{'id': int}[],
         * } $permission */
        $permission = $query->getOneOrNullResult($query::HYDRATE_ARRAY);

        if ($permission) {
            foreach ($permission['users'] as $u) {
                $ids[] = $u['id'];
            }

            $groupIds = [];
            foreach ($permission['groups'] as $g) {
                $groupIds[] = $g['id'];
            }

            if (\count($groupIds)) {
                $qb = $this->em->createQueryBuilder();
                $qb->select('g, u')
                    ->from(Group::class, 'g')
                    ->leftJoin('g.users', 'u')
                    ->where($qb->expr()->in('g.id', $groupIds))
                ;

                /** @var array{
                 *     'users': array{'id': int}[]
                 * }[] $groups
                 */
                $groups = $qb->getQuery()->getArrayResult();
                foreach ($groups as $g) {
                    foreach ($g['users'] as $u) {
                        $ids[] = $u['id'];
                    }
                }
            }
        }

        return \array_keys(\array_flip($ids));
    }
}
