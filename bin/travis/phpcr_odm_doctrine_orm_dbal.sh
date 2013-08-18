#!/bin/bash

DIR_NAME=`dirname $0`
CONSOLE_DIR=$DIR_NAME"/.."

# composer install --dev
php $CONSOLE_DIR"/console" doctrine:database:create --env=orm
php $CONSOLE_DIR"/console" doctrine:schema:create --env=orm
php $CONSOLE_DIR"/console" doctrine:phpcr:repository:init
