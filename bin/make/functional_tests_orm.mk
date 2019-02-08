
functional_tests_orm:
	@if [ "${CONSOLE}" = "" ]; then echo "Console executable missing"; exit 1; fi
	@echo
	@echo '+++ create ORM database +++'
	@${CONSOLE} doctrine:schema:drop --env=orm --force
	@${CONSOLE} doctrine:database:create --env=orm
	@${CONSOLE} doctrine:schema:create --env=orm
	@echo '+++ run ORM functional tests +++'
ifeq ($(HAS_XDEBUG), 0)
	phpunit --prepend build/xdebug-filter.php -c phpunit.xml.dist --coverage-clover build/logs/clover.xml --testsuite "functional tests with orm"
else
	phpunit -c phpunit.xml.dist --testsuite "functional tests with orm"
endif
	@${CONSOLE} doctrine:database:drop --force
