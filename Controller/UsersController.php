<?php

namespace Modera\BackendSecurityBundle\Controller;

use Modera\SecurityBundle\Entity\User;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\DataMapping\DataMapperInterface;
use Modera\ServerCrudBundle\Hydration\DoctrineEntityHydrator;
use Modera\ServerCrudBundle\Hydration\HydrationProfile;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class UsersController extends AbstractCrudController
{
    /**
     * @return array
     */
    public function getConfig()
    {
        $self = $this;
        return array(
            'entity' => User::clazz(),
            'hydration' => array(
                'groups' => array(
                    'main-form' => ['id', 'username', 'email', 'firstName', 'lastName', 'middleName'],
                    'list' => function(User $user) {
                        $groups = array();
                        foreach ($user->getGroups() as $group) {
                            $groups[] = $group->getName();
                        }

                        return array(
                            'id'         => $user->getId(),
                            'username'   => $user->getUsername(),
                            'email'      => $user->getEmail(),
                            'firstName'  => $user->getFirstName(),
                            'lastName'   => $user->getLastName(),
                            'middleName' => $user->getMiddleName(),
                            'groups'     => $groups,
                        );
                    },
                    'compact-list' => ['id', 'username', 'fullname'],
                    'delete-user' => ['username']
                ),
                'profiles' => array(
                    'list',
                    'delete-user',
                    'main-form',
                    'compact-list',
                    'modera-backend-security-group-groupusers' => HydrationProfile::create(false)->useGroups(array('compact-list'))
                )
            ),
            'map_data_on_create' => function(array $params, User $entity, DataMapperInterface $defaultMapper, ContainerInterface $container) use ($self) {
                $defaultMapper->mapData($params, $entity);

                if (isset($params['plainPassword']) && $params['plainPassword']) {
                    $plainPassword = $params['plainPassword'];
                } else {
                    $plainPassword = $self->generatePassword();
                }
                $self->setPassword($entity, $plainPassword);
            },
            'map_data_on_update' => function(array $params, User $entity, DataMapperInterface $defaultMapper, ContainerInterface $container) use ($self) {
                $defaultMapper->mapData($params, $entity);

                if (isset($params['plainPassword']) && $params['plainPassword']) {
                    $self->setPassword($entity, $params['plainPassword']);
                }
            },
        );
    }

    /**
     * @Remote
     */
    public function generatePasswordAction(array $params)
    {
        $plainPassword = $this->generatePassword();
        return array(
            'success' => true,
            'result'  => array(
                'plainPassword' => $plainPassword,
            )
        );
    }

    /**
     * @param int $length
     * @return string
     */
    private function generatePassword($length = 8)
    {
        $plainPassword = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++) {
            $plainPassword .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $plainPassword;
    }

    /**
     * @param User $user
     * @param $plainPassword
     */
    private function setPassword(User $user, $plainPassword)
    {
        $factory  = $this->get('security.encoder_factory');
        $encoder  = $factory->getEncoder($user);
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password);
        $user->eraseCredentials();
    }
}
