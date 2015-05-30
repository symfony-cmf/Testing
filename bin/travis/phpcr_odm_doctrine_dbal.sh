#!/bin/bash
# make the script display the commands it runs to help debugging failures
set -x

DIR_NAME=`dirname $0`
CONSOLE_DIR=$DIR_NAME"/.."

if ! $CONSOLE_DIR"/console" doctrine:phpcr:init:dbal --drop --force; then
    # To support Jackalope <1.2
    $CONSOLE_DIR"/console" doctrine:phpcr:init:dbal --drop
fi

$CONSOLE_DIR"/console" doctrine:phpcr:repository:init
