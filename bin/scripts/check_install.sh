#!/usr/bin/env bash

usage="$(basename "$0") [-hl] [-t] -- Script to test a bundle installation with on a symfony application.
    where:
        -h  show help text
        -p  The  complete package name of the bundle
        -s  The Symfony version to use
        -v  The version/branch to install;"

BASE_DIR=${PWD}
BUILD_DIR=${BASE_DIR}/build

PACKAGE_NAME=''
VERSION='dev-master'
SYMFONY_VERSION="^3.3"
function installBundle() {
    DIR=${BUILD_DIR}/${PACKAGE_NAME}/${VERSION}
    if [ "${SYMFONY_VERSION}" = *"dev" ]; then STABILITY_FLAG=' -s dev'; else STABILITY_FLAG=''; fi
    mkdir -p ${DIR}
    echo "Create directory ${DIR}"
    cd ${DIR}
    echo "+++ Create Symfony skeleton app +++ "
    composer create-project${STABILITY_FLAG} "symfony/skeleton:${SYMFONY_VERSION}" test-app
    cd test-app/
    composer config extra.symfony.allow-contrib true
    if [ "${VERSION}" = "dev"* ]; then perl -pi -e 's/^}$/,"minimum-stability":"dev"}/' composer.json; fi
    REQUIRE=${PACKAGE_NAME}":"${VERSION}
    echo "+++ Require bundle ${REQUIRE} +++"
    composer req -n "${REQUIRE}"
    OUT=$?
    if [ ${OUT} -eq 0 ];then
       echo "+++ Install is fine +++"
       exit 0
    else
       echo "+++ Problems to install package +++"
       exit ${OUT}
    fi
    echo $?
    echo "+++ We should fetch composer exit code here +++"
}

while getopts :hv:p:s: option
do
    case "${option}"
    in
        p) PACKAGE_NAME=${OPTARG};;
        v) VERSION=${OPTARG};;
        s) SYMFONY_VERSION=${OPTARG};;
        h) echo "${usage}"
           exit 1
           ;;
        :) printf "missing argument for -%s\n" "$OPTARG" >&2
           echo "$usage" >&2
           exit 1
           ;;
        \?) printf "illegal option: -%s\n" "$OPTARG" >&2
            echo "$usage" >&2
            exit 1
            ;;
    esac
done

installBundle

