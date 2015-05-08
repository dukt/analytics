#!/bin/bash

export PLUGIN_NAME="analytics"
export SOURCE_FOLDER="Source/"
export BUILD_FOLDER="Build/"
export BUILD_PLUGIN_FOLDER="${BUILD_FOLDER}/${PLUGIN_NAME}/"
export PLUGIN_NAME_WITH_VERSION="${PLUGIN_NAME}-${PLUGIN_VERSION}.${BUILD_NUMBER}"

# Copy Build

rm -rf $BUILD_FOLDER
mkdir $BUILD_FOLDER
cp -R $SOURCE_FOLDER $BUILD_PLUGIN_FOLDER

# Clean up Build/

find ./$BUILD_PLUGIN_FOLDER -name ".git" -exec rm -rf {} \;
rm ./$BUILD_PLUGIN_FOLDER/composer.json
rm ./$BUILD_PLUGIN_FOLDER/composer.lock

# zip

rm -rf Zip/
mkdir Zip/
cp -R $BUILD_PLUGIN_FOLDER Zip/$PLUGIN_NAME_WITH_VERSION

cd Zip/
zip -r "${PLUGIN_NAME_WITH_VERSION}.zip" $PLUGIN_NAME_WITH_VERSION
rm -rf $PLUGIN_NAME_WITH_VERSION
cd ../

ls -la

# mv "${FOLDERNAME}.zip" "../zip/${FOLDERNAME}.zip"
