<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PermissionRepository")
 */
class Permission
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;


    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $viewable;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $added;


    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $editable;


    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $removable;

    /**
     * Permission constructor.
     * @param $name
     * @param $viewable
     * @param $added
     * @param $editable
     * @param $removable
     */
    public function __construct($name, $viewable, $added, $editable, $removable)
    {
        $this->name = $name;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
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
