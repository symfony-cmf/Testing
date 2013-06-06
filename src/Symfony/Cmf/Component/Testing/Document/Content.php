<?php

namespace Symfony\Cmf\Component\Testing\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * Test content document
 *
 * Very simple, referenceable document.
 *
 * @PHPCRODM\Document(referenceable=true)
 */
class Content
{
    /**
     * @PHPCRODM\Id(strategy="parent")
     */
    protected $id;

    /**
     * @PHPCRODM\ParentDocument
     */
    protected $parent;

    /**
     * @PHPCRODM\NodeName
     */
    protected $name;

    /**
     * @PHPCRODM\String
     */
    protected $title;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle() 
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
}

