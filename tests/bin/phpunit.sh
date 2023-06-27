#!/usr/bin/env bash
if [[ ${COVERAGE} == 1 ]]; then
    travis_fold start "PHP.coverage" && travis_time_start
    phpunit --coverage-clover build/logs/clover.xml
    travis_time_finish && travis_fold end "PHP.coverage"
else
	travis_fold start "PHP.tests" && travis_time_start
    phpunit
    travis_time_finish && travis_fold end "PHP.tests"
fi