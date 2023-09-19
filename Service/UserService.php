<?php

namespace Modera\SecurityBundle\Service;

use Doctrine\ORM\EntityManager;
use Modera\FoundationBundle\Translation\T;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\User;
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
    private EntityManager $em;
    private RootUserHandlerInterface $rootUserHandler;
    private ?RoleHierarchyInterface $roleHierarchy = null;
    private ?TokenStorageInterface $tokenStorage = null;

    public function __construct(
        EntityManager $em,
        RootUserHandlerInterface $rootUserHandler,
        ?RoleHierarchyInterface $roleHierarchy = null,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->em = $em;
        $this->rootUserHandler = $rootUserHandler;
        $this->roleHierarchy = $roleHierarchy;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param User $user
     */
    public function save(User $user)
    {
        if (!$user->getId()) {
            $this->em->persist($user);
        }
        $this->em->flush($user);
    }

    /**
     * @throws \RuntimeException If given used is root user and cannot be deleted
     *
     * @param User $user
     */
    public function remove(User $user)
    {
        if ($this->rootUserHandler->isRootUser($user)) {
            throw new \RuntimeException(T::trans('ROOT user cannot be removed.'));
        }

        $this->em->remove($user);
        $this->em->flush($user);
    }

    /**
     * @throws \RuntimeException If given used is root user and cannot be disabled
     *
     * @param User $user
     */
    public function disable(User $user)
    {
        if ($this->rootUserHandler->isRootUser($user)) {
            throw new \RuntimeException(T::trans('ROOT user cannot be disabled.'));
        }

        $user->setActive(false);
        $this->em->flush($user);
    }

    /**
     * @param User $user
     */
    public function enable(User $user)
    {
        $user->setActive(true);
        $this->em->flush($user);
    }

    /**
     * Find user by some property.
     *
     * @param $property
     * @param $value
     *
     * @return null|User
     */
    public function findUserBy($property, $value)
    {
        return $this->em->getRepository(User::class)->findOneBy(array($property => $value));
    }

    /**
     * Find users by some property.
     *
     * @param $property
     * @param $value
     *
     * @return User[]
     */
    public function findUsersBy($property, $value)
    {
        return $this->em->getRepository(User::class)->findBy(array($property => $value));
    }

    /**
     * @return ?User
     */
    public function getAuthenticatedUser()
    {
        if (null === $this->tokenStorage) {
            return null;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        // @deprecated since 5.4, $user will always be a UserInterface instance
        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }

    /**
     * @return User
     */
    public function getRootUser()
    {
        return $this->rootUserHandler->getUser();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isRootUser(User $user)
    {
        return $this->rootUserHandler->isRootUser($user);
    }

    /**
     * @param User $user
     * @param string $roleName
     *
     * @return bool
     */
    public function isGranted(User $user, string $roleName): bool
    {
        $roles = $user->getRoles();
        if (null !== $this->roleHierarchy) {
            $roles = $this->roleHierarchy->getReachableRoleNames($roles);
        }
        return in_array($roleName, $roles, true);
    }

    /**
     * @param string $roleName
     *
     * @return User[]
     */
    public function getUsersByRole($roleName)
    {
        $ids = $this->getIdsByRole($roleName);
        if (count($ids)) {
            return $this->findUsersBy('id', $ids);
        }

        return array();
    }

    /**
     * @param $roleName
     *
     * @return array
     */
    public function getIdsByRole($roleName)
    {
        $ids = array();

        $qb = $this->em->createQueryBuilder();
        $qb->select('p, u, g')
            ->from(Permission::class, 'p')
            ->leftJoin('p.users', 'u')
            ->leftJoin('p.groups', 'g')
            ->where($qb->expr()->eq('p.roleName', ':roleName'))
            ->setParameter('roleName', $roleName)
        ;

        $query = $qb->getQuery();
        $permission = $query->getOneOrNullResult($query::HYDRATE_ARRAY);

        if ($permission) {
            foreach ($permission['users'] as $u) {
                $ids[] = $u['id'];
            }

            $groupIds = array();
            foreach ($permission['groups'] as $g) {
                $groupIds[] = $g['id'];
            }

            if (count($groupIds)) {
                $qb = $this->em->createQueryBuilder();
                $qb->select('g, u')
                    ->from(Group::class, 'g')
                    ->leftJoin('g.users', 'u')
                    ->where($qb->expr()->in('g.id', $groupIds))
                ;

                $groups = $qb->getQuery()->getArrayResult();

                foreach ($groups as $g) {
                    foreach ($g['users'] as $u) {
                        $ids[] = $u['id'];
                    }
                }
            }
        }

        return array_keys(array_flip($ids));
    }
}
