#!/usr/bin/env bash

if [[ ${PHPLINT} == 1 ]]; then
	travis_fold start "PHP.check" && travis_time_start
    find -L . -path ./vendor -prune -o -path ./node_modules -prune $SKIP_CLI -o -name '*.php' -print0 | xargs -0 -n 1 -P 4 php -l
    travis_time_finish && travis_fold end "PHP.check"
fi