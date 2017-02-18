<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestSuite;

if (class_exists(TestSuite::class)) {
    class_alias(TestSuite::class, 'PHPUnit_Framework_TestSuite');
}
