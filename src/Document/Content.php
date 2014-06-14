<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\Testing\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * Test content document
 *
 * Very simple, referenceable document.
 *
 * @deprecated This Document is deprecated as of 1.1 and will be removed in 
 * 2.0. Move the fixture to your own bundle instead.
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

