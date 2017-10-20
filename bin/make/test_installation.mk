
test_installation:
	@if [ "${PACKAGE}" = "" ] || [ "${VERSION}" = "" ]; then echo "Package name or version missing"; exit 1; fi
	@echo
	@echo '+++ testing installation into a blank symfony application +++'
	vendor/symfony-cmf/testing/bin/scripts/check_install.sh -p${PACKAGE} -v${VERSION}
