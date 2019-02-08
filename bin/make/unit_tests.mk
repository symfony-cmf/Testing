
unit_tests:
	@echo
	@echo '+++ run unit tests +++'
ifeq ($(HAS_XDEBUG), 0)
	phpunit --prepend build/xdebug-filter.php -c phpunit.xml.dist --coverage-clover build/logs/clover.xml --testsuite "unit tests"
else
	phpunit -c phpunit.xml.dist --testsuite "unit tests"
endif
	@vendor/bin/simple-phpunit
