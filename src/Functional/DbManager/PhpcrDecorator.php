<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Testing\Functional\DbManager;

use Doctrine\Bundle\PHPCRBundle\Test\RepositoryManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A decorator around the DoctrinePHPCRBundle RepositoryManager class to
 * provide BC with the PHPCR manager in this bundle.
 */
class PhpcrDecorator extends RepositoryManager
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->get('doctrine_phpcr'), $container->get('doctrine_phpcr.initializer_manager'));
    }

    public function getOm(string $managerName = null): DocumentManagerInterface
    {
        return $this->getDocumentManager($managerName);
    }

    /**
     * Create a test node, if the test node already exists, remove it.
     */
    public function createTestNode(): void
    {
        $session = $this->getDocumentManager()->getPhpcrSession();

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        $session->getRootNode()->addNode('test', 'nt:unstructured');

        $session->save();
    }
}
