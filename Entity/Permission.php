<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="modera_security_permission")
 *
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class Permission
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
     * Name of symfony security role, something like "ROLE_USER".
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string")
     */
    private ?string $roleName = null;

    /**
     * A name of this role that can be easily understood by administrator, for instance - "Access admin section".
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $position = 0;

    /**
     * @var Collection<int, Permission>
     *
     * @ORM\ManyToMany(targetEntity="Permission", cascade={"persist"})
     *
     * @ORM\JoinTable(
     *     name="modera_security_rolehierarchy",
     *     joinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")}
     * )
     */
    private Collection $roles;

    /**
     * @var Collection<int, UserInterface>
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="permissions", cascade={"persist"})
     *
     * @ORM\JoinTable(
     *     name="modera_security_permissionusers"
     * )
     */
    private Collection $users;

    /**
     * @var Collection<int, Group>
     *
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="permissions", cascade={"persist"})
     *
     * @ORM\JoinTable(
     *     name="modera_security_permissiongroups"
     * )
     */
    private Collection $groups;

    /**
     * @ORM\ManyToOne(targetEntity="PermissionCategory", inversedBy="permissions", cascade={"persist"})
     */
    private ?PermissionCategory $category = null;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
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
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }
    }

    public function addGroup(Group $group): void
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }
    }

    public function addRole(self $role): void
    {
        $this->roles[] = $role;
    }

    public function hasGroup(Group $group): bool
    {
        return $this->groups->contains($group);
    }

    public function getRole(): string
    {
        return $this->getRoleName();
    }

    // boilerplate:

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param Collection<int, Group> $groups
     */
    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRoleName(string $roleName): void
    {
        $this->roleName = $roleName;
    }

    public function getRoleName(): string
    {
        return $this->roleName ?? '';
    }

    /**
     * @param Collection<int, Permission> $roles
     */
    public function setRoles(Collection $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
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

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setCategory(?PermissionCategory $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): ?PermissionCategory
    {
        return $this->category;
    }
}
