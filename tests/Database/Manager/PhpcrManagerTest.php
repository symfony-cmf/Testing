<?php

namespace Symfony\Cmf\Component\Testing\Tests\Database\Manager;

use Symfony\Cmf\Component\Testing\Database\Manager\PhpcrManager;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class PhpcrManagerTest extends BaseManagerTest
{
    /**
     * @var PhpcrManager
     */
    protected $manager;

    public function setUp()
    {
        $this->manager = new PhpcrManager();
        $this->manager->setContainer($this->getContainer());
    }

    public function testSetUpDatabase()
    {
        $this->assertProcessExecuted(array('doctrine:phpcr:init:dbal', '--drop', '--force'));
        $this->assertProcessExecuted(array('doctrine:phpcr:repository:init'));

        $this->manager->setUpDatabase($this->getProcessBuilder());
    }

    public function testSetUpFallsBackToOldDbalInitCommand()
    {
        $this->assertProcessExecuted(array('doctrine:phpcr:init:dbal', '--drop', '--force'), false);
        $this->assertProcessExecuted(array('doctrine:phpcr:init:dbal', '--drop'), true);
        $this->assertProcessExecuted(array('doctrine:phpcr:repository:init'));

        $this->manager->setUpDatabase($this->getProcessBuilder());
    }
}
