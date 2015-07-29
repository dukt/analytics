#!/bin/bash

export PLUGIN_VERSION_VAR="${CI_BRANCH}_VERSION"
export PLUGIN_VERSION=${!PLUGIN_VERSION_VAR}
export PLUGIN_VERSION_BUILD="${PLUGIN_VERSION}.${CI_BUILD_NUMBER}"

export SOURCE_FOLDER="Source/"

echo "Preparing build ${PLUGIN_VERSION_BUILD}"

# Build Source

cd $SOURCE_FOLDER

composer update

cd ../

./Utils/applyVersion.sh ${PLUGIN_VERSION}.${CI_BUILD_NUMBER}