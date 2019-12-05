
unit_tests:
	@echo
	@echo '+++ run unit tests +++'
ifeq ($(HAS_XDEBUG), 0)
	@vendor/bin/simple-phpunit --coverage-clover build/logs/clover.xml --testsuite "unit tests"
else
	@vendor/bin/simple-phpunit --testsuite "unit tests"
endif
