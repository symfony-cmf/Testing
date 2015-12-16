<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

@trigger_error('The resources/config/'.basename(__FILE__).' file is deprecated since version 1.3 and will be removed in 2.0. Include the config file in your own library instead.');

$loader->import(CMF_TEST_CONFIG_DIR.'/dist/sonata_admin.yml');
