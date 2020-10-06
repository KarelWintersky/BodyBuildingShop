#!/usr/bin/make

PROJECT = bbs
PATH_PROJECT = $(DESTDIR)/var/www/$(PROJECT)
PATH_WWW = $(PATH_PROJECT)/www

help:
	@perl -e '$(HELP_ACTION)' $(MAKEFILE_LIST)

install:	##@system Install package. Don't run it manually!!!
	install -d $(PATH_PROJECT)
	cp -r cron $(PATH_PROJECT)
	cp -r kernel $(PATH_PROJECT)
	cp -r public_html $(PATH_PROJECT)
	cp -r tpl $(PATH_PROJECT)
	git rev-parse --short HEAD > $(PATH_PROJECT)/_version
	git log --oneline --format=%B -n 1 HEAD | head -n 1 >> $(PATH_PROJECT)/_version
	git log --oneline --format="%at" -n 1 HEAD | xargs -I{} date -d @{} +%Y-%m-%d >> $(PATH_PROJECT)/_version
	install -d $(PATH_WWW)/sitemaps

update:			##@build Update project from GIT
	@echo Updating project from GIT
	git pull

build:			##@build Build project to DEB Package
	@echo Building project to DEB-package
	export DEBFULLNAME="Karel Wintersky" && export DEBEMAIL="karel.wintersky@gmail.com" && dpkg-buildpackage -rfakeroot --no-sign --build=binary

dchv:           ##@development Set version in changelog: use `make dchv VERSION=x.y.z`
	export DEBFULLNAME="Karel Wintersky" && export DEBEMAIL="karel.wintersky@gmail.com" && dch -v ${VERSION}

dchr:           ##@development Fix version in changelog file: use `make dchr`
	export DEBFULLNAME="Karel Wintersky" && export DEBEMAIL="karel.wintersky@gmail.com" && dch --release --distribution unstable

# ------------------------------------------------
# Add the following 'help' target to your makefile, add help text after each target name starting with '##'
# A category can be added with @category
# args := `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`

GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)
HELP_ACTION = \
	%help; while(<>) { push @{$$help{$$2 // 'options'}}, [$$1, $$3] if /^([a-zA-Z\-_]+)\s*:.*\#\#(?:@([a-zA-Z\-]+))?\s(.*)$$/ }; \
	print "usage: make [target]\n\n"; for (sort keys %help) { print "${WHITE}$$_:${RESET}\n"; \
	for (@{$$help{$$_}}) { $$sep = " " x (32 - length $$_->[0]); print "  ${YELLOW}$$_->[0]${RESET}$$sep${GREEN}$$_->[1]${RESET}\n"; }; \
	print "\n"; }

# -eof-

