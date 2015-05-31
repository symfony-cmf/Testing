<?php

namespace Symfony\Cmf\Component\Testing\Exception;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ManagerNotFoundException extends \OutOfRangeException
{
    public function __construct($driver)
    {
        parent::__construct(sprintf('No database manager found for driver "%s".', $driver));
    }
}
