<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserPermissionRepository")
 */
class UserPermission
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Permission", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $permission;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $viewable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $added;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $editable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $removable;

    /**
     * UserPermission constructor.
     * @param $user
     * @param $permission
     * @param $viewable
     * @param $added
     * @param $editable
     * @param $removable
     */
    public function __construct($user, $permission, $viewable, $added, $editable, $removable)
    {
        $this->user = $user;
        $this->permission = $permission;
        $this->viewable = $viewable;
        $this->added = $added;
        $this->editable = $editable;
        $this->removable = $removable;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $permission
     */
    public function setPermission($permission): void
    {
        $this->permission = $permission;
    }

    /**
     * @return mixed
     */
    public function getViewable()
    {
        return $this->viewable;
    }

    /**
     * @param mixed $viewable
     */
    public function setViewable($viewable): void
    {
        $this->viewable = $viewable;
    }

    /**
     * @return mixed
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param mixed $added
     */
    public function setAdded($added): void
    {
        $this->added = $added;
    }

    /**
     * @return mixed
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * @param mixed $editable
     */
    public function setEditable($editable): void
    {
        $this->editable = $editable;
    }

    /**
     * @return mixed
     */
    public function getRemovable()
    {
        return $this->removable;
    }

    /**
     * @param mixed $removable
     */
    public function setRemovable($removable): void
    {
        $this->removable = $removable;
    }
}
