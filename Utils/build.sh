#!/bin/bash

export SOURCE_FOLDER="Source/"

# Build Source

cd $SOURCE_FOLDER

composer update

cd ../

./applyVersion.sh ${PLUGIN_VERSION}.${CI_BUILD_NUMBER}