
unit_tests:
	@echo
	@echo '+++ run unit tests +++'
ifeq ($(HAS_XDEBUG), 0)
	@vendor/bin/simple-phpunit --prepend build/xdebug-filter.php -c phpunit.xml.dist --coverage-clover build/logs/clover.xml --testsuite "unit tests"
else
	@vendor/bin/simple-phpunit -c phpunit.xml.dist --testsuite "unit tests"
endif
