#!/bin/bash

DIR_NAME=`dirname $0`
CONSOLE_DIR=$DIR_NAME"/.."

# composer install --dev
php $CONSOLE_DIR"/console" doctrine:phpcr:init:dbal --drop
php $CONSOLE_DIR"/console" doctrine:phpcr:repository:init
