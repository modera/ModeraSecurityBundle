<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Groups are used to group users.
 *
 * @ORM\Entity(repositoryClass="Modera\SecurityBundle\Entity\GroupRepository")
 *
 * @ORM\Table(name="modera_security_usersgroup", uniqueConstraints={@ORM\UniqueConstraint(name="refName_idx", columns={"refName"})})
 *
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class Group
{
    /**
     * @ORM\Column(type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @var Collection<int, UserInterface>
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="groups", cascade={"persist"})
     */
    private Collection $users;

    /**
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * Reference name that maybe used in code to refer exact group.
     * Group with ref.name will be created through fixtures.
     *
     * Please note, there is no mandatory Regex assert.
     * But in modera/backend-security-bindle controller this value will
     * be normalized by self::normalizeRefNameString
     *
     * So if plan to use UI editing of your group, try to stick to this Regex assert.
     *
     * @Assert\Regex("/[A-Z_]{0,}/")
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $refName = null;

    /**
     * @var Collection<int, Permission>
     *
     * @ORM\ManyToMany(targetEntity="Permission", mappedBy="groups", cascade={"persist"})
     */
    private Collection $permissions;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * @deprecated Use native ::class property
     */
    public static function clazz(): string
    {
        @\trigger_error(\sprintf(
            'The "%s()" method is deprecated. Use native ::class property.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return \get_called_class();
    }

    public function addUser(UserInterface $user): void
    {
        $this->users->add($user);
        if (!$user->getGroups()->contains($this)) {
            $user->getGroups()->add($this);
        }
    }

    public function addPermission(Permission $role): void
    {
        $role->addGroup($this);
        if (!$this->permissions->contains($role)) {
            $this->permissions->add($role);
        }
    }

    public function hasPermission(Permission $role): bool
    {
        return $this->permissions->contains($role);
    }

    public function hasUser(UserInterface $user): bool
    {
        return $this->users->contains($user);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param Collection<int, UserInterface> $users
     */
    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }

    /**
     * @return Collection<int, UserInterface>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<int, Permission> $roles
     */
    public function setPermissions(Collection $roles): void
    {
        $this->permissions = $roles;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getRefName(): ?string
    {
        return $this->refName;
    }

    public function setRefName(?string $refName): void
    {
        $this->refName = $refName;
    }

    public static function normalizeRefNameString(string $proposedRefName): ?string
    {
        $modifiedRefName = \strtoupper($proposedRefName);

        return \preg_replace('/[^A-Z_]+/', '', $modifiedRefName);
    }
}
