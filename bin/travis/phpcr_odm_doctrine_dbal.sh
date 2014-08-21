#!/bin/bash

DIR_NAME=`dirname $0`
CONSOLE_DIR=$DIR_NAME"/.."

# composer install --dev
$CONSOLE_DIR"/console" doctrine:phpcr:init:dbal --drop
$CONSOLE_DIR"/console" doctrine:phpcr:repository:init
