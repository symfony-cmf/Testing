
functional_tests_orm:
	@if [ "${CONSOLE}" = "" ]; then echo "Console executable missing"; exit 1; fi
	@echo
	@echo '+++ create ORM database +++'
	@${CONSOLE} doctrine:schema:drop --env=orm --force
	@${CONSOLE} doctrine:database:create --env=orm
	@${CONSOLE} doctrine:schema:create --env=orm
	@echo '+++ run ORM functional tests +++'
	@vendor/bin/simple-phpunit --testsuite "functional tests with orm"
	@${CONSOLE} doctrine:database:drop --force
