#!/bin/bash

for PATH in $(ls SoftwareTesting)
do
        "phpunit $(pwd)/SoftwareTesting/$PATH"
done
