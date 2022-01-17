<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Orm\Entity
 * @Orm\Table(name="modera_security_permissioncategory")
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionCategory
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $technicalName;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $position = 0;

    /**
     * @Orm\OneToMany(targetEntity="Permission", mappedBy="category", cascade={"persist"})
     */
    private $permissions;

    /**
     * @param string $name
     * @param string $technicalName
     */
    public function __construct($name = null, $technicalName = null)
    {
        $this->name = $name;
        $this->technicalName = $technicalName;

        $this->permissions = new ArrayCollection();
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
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $technicalName
     */
    public function setTechnicalName($technicalName)
    {
        $this->technicalName = $technicalName;
    }

    /**
     * @return mixed
     */
    public function getTechnicalName()
    {
        return $this->technicalName;
    }

    /**
     * @param mixed $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
