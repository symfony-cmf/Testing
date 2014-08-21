#!/bin/bash

DIR_NAME=`dirname $0`
CONSOLE_DIR=$DIR_NAME"/.."

# composer install --dev
$CONSOLE_DIR"/console" doctrine:database:create --env=orm
$CONSOLE_DIR"/console" doctrine:schema:create --env=orm
$CONSOLE_DIR"/console" doctrine:phpcr:repository:init
