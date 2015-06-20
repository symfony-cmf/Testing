<?php

namespace Symfony\Cmf\Component\Testing\Database\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Cmf\Component\Testing\Exception\DropFailedException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class DoctrineManager implements ManagerInterface, ContainerAwareInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     *
     * @throws \BadMethodCallException When no container was set
     */
    protected function getContainer()
    {
        if (null === $this->container) {
            throw new \BadMethodCallException('This method cannot be executed without a container.');
        }

        return $this->container;
    }

    public function dropDatabase(ProcessBuilder $processBuilder)
    {
        $process = $processBuilder
            ->setArguments(array('doctrine:database:drop', '--force'))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            $output = null !== $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();

            throw new DropFailedException('Error when dropping database: '.$output);
        }
    }

    /**
     * Gets the Doctrine ObjectManager
     *
     * @param null $managerName
     *
     * @return ObjectManager
     */
    public function getOm($managerName = null)
    {
        if (!$this->om) {
            $this->om = $this->getRegistry()->getManager($managerName);
        }

        return $this->om;
    }
}
