# Makefile
# Les Polypodes, 2014
# Licence: MIT
# Source: https://github.com/polypodes/Build-and-Deploy/blob/master/build/Makefile

# To enable this quality-related tasks, add these dependencies to your composer.json:
# they'll be available in the ./bin dir :
#
#    "require-dev": {
#	     (...)
#        "phpunit/phpunit":             "~3.7",
#        "squizlabs/php_codesniffer":   "2.0.x-dev",
#        "sebastian/phpcpd":            "*",
#        "phploc/phploc" :              "*",
#        "phpmd/phpmd" :                "2.0.*",
#        "pdepend/pdepend" :            "2.0.*",
#        "fabpot/php-cs-fixer":         "@stable"
#    },


# To list all tasks:
# me@myserver$~: make -qp | awk -F":" "/^[a-zA-Z0-9][^$#\/\t=]*:([^=]|$)/ {split(\$1,A,/ /);for(i in A)print A[i]}"

# Usage:

# me@myserver$~: make help
# me@myserver$~: make install
# me@myserver$~: make reinstall
# me@myserver$~: make update
# me@myserver$~: make tests
# me@myserver$~: make quality
# etc.

############################################################################
# Vars

# PostgreSQL connection params
DB_LASTDUMP := $(shell if [ -d ./dumps ] ; then ls -Art ./dumps | tail -n 1 ; fi)
DB_HOST	    := $(shell if [ -f app/config/parameters.yml ] ; then cat app/config/parameters.yml | grep 'db_host1' | sed 's/db_host1: //' | sed 's/^ *//;s/ *$$//' ; fi)
DB_PORT	    := $(shell if [ -f app/config/parameters.yml ] ; then cat app/config/parameters.yml | grep 'db_port1' | sed 's/db_port1: //' | sed 's/^ *//;s/ *$$//' ; fi)
DB_NAME     := $(shell if [ -f app/config/parameters.yml ] ; then cat app/config/parameters.yml | grep 'db_name1' | sed 's/db_name1: //' | sed 's/^ *//;s/ *$$//' ; fi)
DB_USER	    := $(shell if [ -f app/config/parameters.yml ] ; then cat app/config/parameters.yml | grep 'db_user1' | sed 's/db_user1: //' | sed 's/^ *//;s/ *$$//' ; fi)
DB_PASSWORD := $(shell if [ -f app/config/parameters.yml ] ; then cat app/config/parameters.yml | grep 'db_password1' | sed 's/db_password1: //' | sed 's/null//' | sed 's/^ *//;s/ *$$//' ; fi)
DB_VARS     := -h ${DB_HOST} -p ${DB_PORT} -U ${DB_USER}
# Pathes
PWD         := $(shell pwd)
VENDOR_PATH := $(PWD)/vendor
BIN_PATH    := $(PWD)/bin
WEB_PATH    := $(PWD)/web
# Other vars
NOW         := $(shell date +%Y-%m-%d--%H-%M-%S)
REPO        := "https://github.com/LiberTIC/ODEV2.git"
BRANCH      := 'master'
# Colors
YELLOW      := $(shell tput bold ; tput setaf 3)
GREEN       := $(shell tput bold ; tput setaf 2)
RESETC      := $(shell tput sgr0)

# Custom MAKE options
ifndef VERBOSE
  MAKEFLAGS += --no-print-directory
endif

############################################################################
# Mandatory tasks:

all: .git/hook/pre-commit vendor/autoload.php check help

help:
	@echo "\n${GREEN}Usual tasks:${RESETC}\n"
	@echo "\tTo prepare install:\tmake"
	@echo "\tTo install:\t\tmake install"
	@echo "\tTo update from git:\tmake update"
	@echo "\tTo reinstall:\t\tmake reinstall\t(will dump & erase your database)\n\n"

	@echo "${GREEN}Other specific tasks:${RESETC}\n"
	@echo "\tTo check code quality:\tmake quality"
	@echo "\tTo fix code style:\tmake cs-fix"
	@echo "\tTo clear all caches:\tmake clear"
	@echo "\tTo run tests:\t\tmake tests\t(will dump & erase your database)\n"

vendor/autoload.php:
	@composer -v >/dev/null 2>&1 || { echo >&2 "This Makefile requires composer but it's not installed or not in your PATH. Please checkout the install doc: https://getcomposer.org/doc/00-intro.md and install it the 'globally' way. Aborting."; exit 1; }
	@composer self-update
	@composer install --prefer-dist --optimize-autoloader

.git/hook/pre-commit:
	@curl -s -o .git/hooks/pre-commit https://raw.githubusercontent.com/polypodes/Build-and-Deploy/master/hooks/pre-commit
	@chmod +x .git/hooks/pre-commit

############################################################################
# Database related tasks

pgCreateRole:
	@echo
	@echo "Creating role ${DB_NAME} using doc/postgresql/role.sql..."
	@psql --version >/dev/null 2>&1 || { echo >&2 "This Makefile requires psql but it's not installed or not in your PATH. Please checkout the install doc: http://www.postgresql.org. Aborting."; exit 1; }
	psql -h ${DB_HOST} -p ${DB_PORT} -f ./doc/postgresql/role.sql
	@echo "done"

createDb:
	@echo
	@echo "Create PostgreSQL database ${DB_NAME}..."
	@createDb --version >/dev/null 2>&1 || { echo >&2 "This Makefile requires createDb but it's not installed or not in your PATH. Please checkout the install doc: http://www.postgresql.org. Aborting."; exit 1; }
	createDb -h ${DB_HOST} -p ${DB_PORT} ${DB_NAME}
	@echo "done"

pgInit:
	@echo
	@echo "Initializing ${DB_NAME} db tables using ./doc/pstgresql/init.dump..."
	@pg_restore --version >/dev/null 2>&1 || { echo >&2 "This Makefile requires pg_restore but it's not installed or not in your PATH. Please checkout the install doc: http://www.postgresql.org. Aborting."; exit 1; }
	pg_restore ${DB_VARS} -O -d ${DB_NAME} ./doc/postgresql/init.dump
	@echo "done"

dumps:
	@echo
	@echo "Creating db dumps folder..."
	@mkdir ./dumps
	@echo "done"

pgDump: dumps
	@echo
	@echo "Dumping existing ${DB_NAME} db into ./dumps ..."
	@pg_dump --version >/dev/null 2>&1 || { echo >&2 "This Makefile requires pg_dump but it's not installed or not in your PATH. Please checkout the install doc: http://www.postgresql.org. Aborting."; exit 1; }
	pg_dump ${DB_VARS} -Fc -d ${DB_NAME} -f ./dumps/${NOW}.dump
	@echo "done"

pgRestore:
	@echo
	@echo "Restoring existing ${DB_NAME} db using last ./dumps/${DB_LASTDUMP}..."
	@pg_restore --version >/dev/null 2>&1 || { echo >&2 "This Makefile requires pg_restore but it's not installed or not in your PATH. Please checkout the install doc: http://www.postgresql.org. Aborting."; exit 1; }
	pg_restore ${DB_VARS} -O -d ${DB_NAME} ./dumps/${DB_LASTDUMP}
	@echo "done"

dropDb: pgDump
	@echo
	@echo "Drop database ${DB_NAME}..."
	@dropdb --version >/dev/null 2>&1 || { echo >&2 "This Makefile requires dropdb but it's not installed or not in your PATH. Please checkout the install doc: http://www.postgresql.org. Aborting."; exit 1; }
	dropdb ${DB_VARS} ${DB_NAME}
	@echo "done"

installDb: createDb pgInit

resetDb: dropDb installDb

connect: psqlcheck
	@echo
	@echo "Connection to psql..."
	@psql --version >/dev/null 2>&1 || { echo >&2 "This Makefile requires psql but it's not installed or not in your PATH. Please checkout the install doc: http://www.postgresql.org. Aborting."; exit 1; }
	psql ${DB_VARS} ${DB_NAME}


############################################################################
# PHP-related tasks:

check:
	@php app/check.php

pull:
	@git pull origin $(BRANCH)

assets:
	@echo "\nPublishing assets..."
	@php app/console assets:install --symlink

clear: vendor/autoload.php
	@echo
	@echo "Resetting caches..."
	@php app/console cache:clear --env=prod --no-debug
	@php app/console cache:clear --env=dev

explain:
	@echo "git pull origin master + update db schema + build integration + copy new assets + rebuild prod cache"
	@echo "Note you can change the git remote repo username in .git/config"

behavior: vendor/autoload.php
	@echo "Run behavior tests..."
	@bin/behat --lang=fr  "@AcmeDemoBundle"

unit: vendor/autoload.php pdepend
	@echo "Run unit tests..."
	@php bin/phpunit -c build/phpunit.xml -v

codecoverage: vendor/autoload.php
	@echo "Run coverage tests..."
	@bin/phpunit -c build/phpunit.xml -v --coverage-html ./build/codecoverage

continuous: vendor/autoload.php
	@echo "Starting continuous tests..."
	@while true; do bin/phpunit -c build/phpunit.xml -v; done

sniff: vendor/autoload.php
	@bin/phpcs --standard=PSR2 src -n

dry-fix:
	@bin/php-cs-fixer fix . --config=sf23 --dry-run -vv

cs-fix:
	@bin/phpcbf --standard=PSR2 src
	@bin/php-cs-fixer fix src --config=sf23 -vv

#quality must remain quiet, as far as it's used in a pre-commit hook validation
quality: sniff dry-fix

phpdoc:
	@echo "Generate PHP documentation in doc/api/ folder..."
	bin/phpdoc -d ./src -t ./doc/api
	@echo "done: have a look at doc/api/index.html"

build:
	@mkdir -p build/pdepend

tests: reinstall quality behavior unit codecoverage

# packagist-based dev tools to add to your composer.json. See http://phpqatools.org
stats: quality build
	@echo "Some stats about code quality"
	@bin/phploc src
	@bin/phpcpd src
	@bin/phpmd src text codesize,unusedcode
	@bin/pdepend --summary-xml=build/pdepend/summary.xml --jdepend-chart=build/pdepend/jdepend.svg --overview-pyramid=build/pdepend/pyramid.svg src

update: vendor/autoload.php
	@$(MAKE) explain
	@$(MAKE) pull
	@$(MAKE) clear
	@$(MAKE) done

robot:
	@echo "User-agent: *" > $(WEB_PATH)/robots.txt
	@echo "Disallow: " >> $(WEB_PATH)/robots.txt

unrobot:
	@echo "User-agent: *" > $(WEB_PATH)/robots.txt
	@echo "Disallow: /" >> $(WEB_PATH)/robots.txt

done:
	@echo
	@echo "${GREEN}Done.${RESETC}"

# Tasks sets:

install: installDb clear done

reinstall: resetDb install


############################################################################
# .PHONY tasks list
.PHONY: all install reinstall help check pull assets clear explain
.PHONY: pgCreateRole createDb pgInit pgDump pgRestore dropDb installDb resetDb connect
.PHONY: behavior unit codecoverage continuous sniff dry-fix cs-fix quality phpdoc build tests
.PHONY:	stats update robot unrobot done

# vim:ft=make
