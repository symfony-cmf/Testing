<?php

namespace Symfony\Cmf\Component\Testing\DataFixtures\PHPCR;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\Document\Generic;

class LoadBaseData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $root = $manager->find(null, '/');
        $base = new Generic;
        $base->setNodename('test');
        $base->setParent($root);
        $manager->persist($base);
        $manager->flush();
    }
}

