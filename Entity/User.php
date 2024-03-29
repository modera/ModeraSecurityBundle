<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Sli\ExtJsIntegrationBundle\DataMapping\PreferencesAwareUserInterface;
use Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface;
use Modera\SecurityBundle\PasswordStrength\BadPasswordException;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\Validator\Constraints\Username;
use Modera\SecurityBundle\Validator\Constraints\Email;

/**
 * @ORM\Table(name="modera_security_user")
 * @ORM\Entity
 * @UniqueEntity("personalId")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class User implements \Serializable, UserInterface, PreferencesAwareUserInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     *
     * @Email
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     *
     * @Username
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(name="personal_id", type="string", nullable=true, unique=true)
     */
    private $personalId;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(name="middle_name", type="string", nullable=true)
     */
    private $middleName;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $state = self::STATE_NEW;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(
     *   name="modera_security_users_groups",
     *   joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    private $groups;

    /**
     * @var Permission[]
     *
     * @ORM\ManyToMany(targetEntity="Permission", mappedBy="users", cascade={"persist"})
     */
    private $permissions;

    /**
     * You can use this field to keep meta-information associated with given user. To minimize chance of occurring
     * overlapped keys please store your values under bundle name which owns contributed configuration values. For
     * example, if you have a bundle AcmeFooBundle which wants to save some values to this field then store all values
     * under "acme_foo" key.
     *
     * @ORM\Column(type="json")
     */
    private $meta = array();

    /**
     * @var RootUserHandlerInterface
     */
    private $rootUserHandler;

    public function __construct()
    {
        $this->isActive = true;
        $this->salt = md5(uniqid(null, true));

        $this->groups = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * Necessary for providing support for so called "root-users".
     *
     * @see #getRoles() method
     *
     * @private
     *
     * @param RootUserHandlerInterface $rootUserHandler
     */
    public function init(RootUserHandlerInterface $rootUserHandler)
    {
        $this->rootUserHandler = $rootUserHandler;
    }

    /**
     * @param Group $group
     *
     * @return bool
     */
    public function addToGroup(Group $group)
    {
        if (!$group->hasUser($this)) {
            $group->addUser($this);

            return true;
        }

        return false;
    }

    /**
     * @param Permission $role
     */
    public function addPermission(Permission $role)
    {
        $role->addUser($this);
        if (!$this->permissions->contains($role)) {
            $this->permissions[] = $role;
        }
    }

    /**
     * @return Permission[]
     */
    public function getRawRoles()
    {
        $roles = array();
        foreach ($this->getGroups() as $group) {
            foreach ($group->getPermissions() as $role) {
                $roles[] = $role;
            }
        }
        foreach ($this->permissions as $role) {
            $roles[] = $role;
        }

        return $roles;
    }

    /**
     * This method also takes into account possibility that a user might be "root".
     *
     * @see #init() method
     * @see \Modera\SecurityBundle\RootUserHandling\RootUserHandlerInterface
     *
     * {@inheritdoc}
     */
    public function getRoles()
    {
        if ($this->rootUserHandler) {
            if ($this->rootUserHandler->isRootUser($this)) {
                return $this->rootUserHandler->getRoles();
            }
        }

        $roles = array('ROLE_USER');
        foreach ($this->getRawRoles() as $role) {
            $roles[] = $role->getRoleName();
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @deprecated Use User::isActive() method
     *
     * @return bool
     */
    public function isEnabled()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use User::isActive() method.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->isActive;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(SymfonyUserInterface $user)
    {
        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->isActive !== $user->isActive()) {
            return false;
        }

        return true;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'salt' => $this->salt,
            'isActive' => $this->isActive,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        foreach (unserialize($serialized) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @deprecated Use native ::class property
     *
     * @return string
     */
    public static function clazz()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use native ::class property.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return get_called_class();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @deprecated Use User::isActive() method
     *
     * @return bool
     */
    public function getIsActive()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use User::isActive() method.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->isActive();
    }

    /**
     * @deprecated Use User::setActive() method
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated. Use User::setActive() method.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->setActive($isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = trim($email) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = trim($username) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @since 2.56.0
     *
     * @throws BadPasswordException
     *
     * @param PasswordManager $passwordManager
     * @param string $plainPassword
     */
    public function validateAndSetPassword(PasswordManager $passwordManager, $plainPassword)
    {
        $passwordManager->encodeAndSetPassword($this, $plainPassword);
    }

    /**
     * Most of the time you want to use #validateAndSetPassword() method instead.
     *
     * @param string $encodedPassword
     */
    public function setPassword($encodedPassword)
    {
        $this->password = $encodedPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonalId()
    {
        return $this->personalId;
    }

    /**
     * @param string $personalId
     */
    public function setPersonalId($personalId)
    {
        $this->personalId = preg_replace('/[^[:alnum:][:space:]-]/u', '', trim($personalId)) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = preg_replace('/[^[:alnum:][:space:]-]/u', '', trim($firstName)) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = preg_replace('/[^[:alnum:][:space:]-]/u', '', trim($lastName)) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = preg_replace('/[^[:alnum:][:space:]-]/u', '', trim($middleName)) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullName($pattern = 'first last')
    {
        $data = array(
            'first' => $this->getFirstName(),
            'last' => $this->getLastName(),
            'middle' => $this->getMiddleName(),
        );

        $keys = array();
        $values = array();
        foreach ($data as $key => $value) {
            $keys[] = '/\b'.$key.'\b/';
            $values[] = $value;
        }

        $name = trim(preg_replace($keys, $values, $pattern));

        if (!$name) {
            return $this->getUsername();
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $gender = strtolower($gender);
        if (!in_array($gender, array(self::GENDER_MALE, self::GENDER_FEMALE))) {
            $gender = null;
        }

        $this->gender = $gender;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        if (self::STATE_ACTIVE !== $state) {
            $state = self::STATE_NEW;
        }

        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @param Group[] $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;
    }

    public function clearMeta()
    {
        $this->meta = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferences()
    {
        return array(
            PreferencesAwareUserInterface::SETTINGS_DATE_FORMAT => 'Y-m-d',
            PreferencesAwareUserInterface::SETTINGS_DATETIME_FORMAT => 'Y-m-d H:i:s',
        );
    }
}
