<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Test;

if (interface_exists(Test::class)) {
    class_alias(Test::class, 'PHPUnit_Framework_Test');
}
