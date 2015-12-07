<?php

@trigger_error('The resources/config/'.basename(__FILE__).' file is deprecated since version 1.3 and will be removed in 2.0. Include the config file in your own library instead.');

$loader->import(CMF_TEST_CONFIG_DIR.'/dist/sonata_admin.yml');
