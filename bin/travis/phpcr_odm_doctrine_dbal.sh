#!/bin/bash

# composer install --dev
php Tests/Functional/App/console doctrine:phpcr:init:dbal
php Tests/Functional/App/console doctrine:phpcr:repository:init
