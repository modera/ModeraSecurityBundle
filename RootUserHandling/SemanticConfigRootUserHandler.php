<?php

namespace Modera\SecurityBundle\RootUserHandling;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Modera\SecurityBundle\DependencyInjection\ModeraSecurityExtension;
use Modera\SecurityBundle\ModeraSecurityBundle;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\User;

/**
 * This implementation will use semantic bundle configuration to retrieve information about root user.
 *
 * @see \Modera\SecurityBundle\DependencyInjection\Configuration
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class SemanticConfigRootUserHandler implements RootUserHandlerInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $config = $container->getParameter(ModeraSecurityExtension::CONFIG_KEY);

        $this->config = $config['root_user'];

        $this->config['switch_user_role'] = null;
        if (isset($config['switch_user']) && $config['switch_user']) {
            $this->config['switch_user_role'] = $config['switch_user']['role'];
        }

        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function isRootUser(User $user)
    {
        /* @var User $rootUser */
        $rootUser = $this->getUser();

        if (!$rootUser) {
            throw new RootUserNotFoundException('Unable to find root user using query: '.json_encode($this->config['query']));
        }

        return $rootUser->isEqualTo($user);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->em->getRepository(User::class)->findOneBy($this->config['query']);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->config['roles'];

        if (is_string($roles) && '*' == $roles) {
            $query = sprintf('SELECT e.roleName FROM %s e', Permission::class);
            $query = $this->em->createQuery($query);

            $roleNames = array();
            foreach ($query->getResult(Query::HYDRATE_SCALAR) as $roleName) {
                $roleNames[] = $roleName['roleName'];
            }

            $roles = $roleNames;
        }

        if (!is_array($roles)) {
            throw new \RuntimeException('Neither "*" nor array is used to define root user roles!');
        }

        if ($this->config['switch_user_role']) {
            $roles[] = $this->config['switch_user_role'];
        }

        $roles[] = ModeraSecurityBundle::ROLE_ROOT_USER;

        return $roles;
    }
}
