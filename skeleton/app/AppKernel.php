<?php

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;

class AppKernel extends TestKernel
{
    public function registerBundles()
    {
        return array(
        //    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        //    new \Symfony\Bundle\MonologBundle\MonologBundle(),
        //    new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
        //    new \Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
        );
    }
}
