
functional_tests_orm:
	@if [ "${CONSOLE}" = "" ]; then echo "Console executable missing"; exit 1; fi
	@echo
	@echo '+++ create ORM database +++'
	@${CONSOLE} doctrine:schema:drop --env=orm --force
	@${CONSOLE} doctrine:database:create --env=orm || echo "Failed to create database. If this is sqlite, this is normal. Otherwise there will be an error with schema creation"
	@${CONSOLE} doctrine:schema:create --env=orm
	@echo '+++ run ORM functional tests +++'
ifeq ($(HAS_XDEBUG), 0)
	@vendor/bin/simple-phpunit --coverage-clover build/logs/clover.xml --testsuite "functional tests with orm"
else
	@vendor/bin/simple-phpunit --testsuite "functional tests with orm"
endif
	@${CONSOLE} doctrine:database:drop --force
