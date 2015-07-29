#!/bin/bash

echo $CI
echo $CI_BUILD_NUMBER
echo $CI_BUILD_URL
echo $CI_PULL_REQUEST
echo $CI_BRANCH
echo $CI_COMMIT_ID
echo $CI_COMMITTER_NAME
echo $CI_COMMITTER_EMAIL
echo $CI_COMMITTER_USERNAME
echo $CI_MESSAGE
echo $CI_NAME

export SOURCE_FOLDER="Source/"

# Build Source

cd $SOURCE_FOLDER

composer update

cd ../