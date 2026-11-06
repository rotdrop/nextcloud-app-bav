# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
SRCDIR = .
ABSSRCDIR = $(CURDIR)
DEV_LIB_DIR = $(ABSSRCDIR)/dev-scripts/lib

TS_PHP_SOURCE_DIRS = lib

# bav has no templates/ directory, and rsync fails on missing sources.
APPSTORE_FILES =\
 appinfo\
 css\
 js\
 img\
 l10n\
 lib\
 composer\
 vendor\
 vendor-scoped\
 CHANGELOG.md\
 COPYING\
 README.md

include $(DEV_LIB_DIR)/makefile/app.mk

app-toolkit: vendor-scoped/autoload.php
.PHONY: app-toolkit

toolkit-l10n: app-toolkit
vue-components-l10n: node_modules
