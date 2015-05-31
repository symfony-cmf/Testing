<?php

namespace Symfony\Cmf\Component\Testing;

use Symfony\Cmf\Component\Testing\Database\Manager\ManagerInterface;

/**
 * An interface that can be implemented when the test needs
 * a database connection.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface RequiresDatabaseInterface
{
    /**
     * The name of the database driver which is used in this test.
     *
     * For instance, the return value can be "phpcr" or "orm".
     *
     * @return string
     */
    public function getDatabaseDriverName();

    /**
     * Sets the DB Manager that is used in the test.
     *
     * @param ManagerInterface $manager
     */
    public function setDbManager(ManagerInterface $manager);
}
