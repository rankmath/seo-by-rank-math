#!/usr/bin/env bash
# usage: travis.sh before|after

if [ $1 == 'before' ]; then

    if [[ ${PHPCS} == 1 ]]; then
      composer global require wp-coding-standards/wpcs
	  phpcs --config-set installed_paths $HOME/.composer/vendor/wp-coding-standards/wpcs
	  # composer global require automattic/vipwpcs
      # phpcs --config-set installed_paths $HOME/.composer/vendor/wp-coding-standards/wpcs,$HOME/.composer/vendor/automattic/vipwpcs
    fi

    if [[ ${COVERAGE} == 1 ]]; then
      curl -L https://codeclimate.com/downloads/test-reporter/test-reporter-latest-linux-amd64 > ./cc-test-reporter
      chmod +x ./cc-test-reporter
      ./cc-test-reporter before-build
	fi

fi

if [ $1 == 'after' ]; then

	if [[ ${COVERAGE} == 1 ]]; then
      ./cc-test-reporter after-build --exit-code $TRAVIS_TEST_RESULT
	fi

fi