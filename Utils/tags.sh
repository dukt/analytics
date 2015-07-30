#!/bin/bash

if GIT_DIR=./.git git show-ref --tags | egrep -q "refs/tags/$1$"
then
    echo "Found tag"
else
    echo "Tag not found"
fi
