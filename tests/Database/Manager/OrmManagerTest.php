<?php

namespace Symfony\Cmf\Component\Testing\Tests\Database\Manager;

use Symfony\Cmf\Component\Testing\Database\Manager\OrmManager;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class OrmManagerTest extends BaseManagerTest
{
    /**
     * @var OrmManager
     */
    protected $manager;

    public function setUp()
    {
        $this->manager = new OrmManager();
        $this->manager->setContainer($this->getContainer());
    }

    public function testSetUp()
    {
        $this->assertProcessExecuted(array('doctrine:schema:drop', '--env=orm', '--force'));
        $this->assertProcessExecuted(array('doctrine:database:create', '--env=orm'));
        $this->assertProcessExecuted(array('doctrine:schema:create', '--env=orm'));

        $this->manager->setUpDatabase($this->getProcessBuilder());
    }
}
