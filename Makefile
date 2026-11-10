# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
SRCDIR = .
ABSSRCDIR = $(CURDIR)
DEV_LIB_DIR = $(ABSSRCDIR)/dev-scripts/lib

TS_PHP_SOURCE_DIRS = lib

include $(DEV_LIB_DIR)/makefile/app.mk
include $(DEV_LIB_DIR)/makefile/phpunit.mk

app-toolkit: vendor-scoped/autoload.php
.PHONY: app-toolkit

toolkit-l10n: app-toolkit
vue-components-l10n: node_modules

PHPUNIT_COMPOSER_JSON = $(ABSSRCDIR)/vendor-bin/phpunit/composer.json
PHPUNIT_COMPOSER_LOCK = $(wildcard $(ABSSRCDIR)/vendor-bin/phpunit/composer.lock)
PHING_COMPOSER_JSON = $(ABSSRCDIR)/vendor-bin/phing/composer.json
PHING_COMPOSER_LOCK = $(wildcard $(ABSSRCDIR)/vendor-bin/phing/composer.lock)

$(PHPUNIT): composer.lock $(PHPUNIT_COMPOSER_JSON) $(PHPUNIT_COMPOSER_LOCK)
	if [ ! -f $(ABSSRCDIR)/vendor-bin/phpunit/composer.lock ] || [ $(PHPUNIT_COMPOSER_JSON) -nt $(ABSSRCDIR)/vendor-bin/phpunit/composer.lock ]; then \
	  $(COMPOSER) bin phpunit update; \
	else \
	  $(COMPOSER) bin phpunit install; \
	fi

$(PHING): composer.lock $(PHING_COMPOSER_JSON) $(PHING_COMPOSER_LOCK)
	if [ ! -f $(ABSSRCDIR)/vendor-bin/phing/composer.lock ] || [ $(PHING_COMPOSER_JSON) -nt $(ABSSRCDIR)/vendor-bin/phing/composer.lock ]; then \
	  $(COMPOSER) bin phing update; \
	else \
	  $(COMPOSER) bin phing install; \
	fi

unit-tests integration-tests: $(PHPUNIT)
