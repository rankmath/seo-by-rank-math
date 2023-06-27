#!/usr/bin/env bash

if [[ ${PHPCS} == 1 ]]; then
	travis_fold start "PHP.code-style" && travis_time_start
    phpcs -q --runtime-set ignore_warnings_on_exit 1
    travis_time_finish && travis_fold end "PHP.code-style"
fi