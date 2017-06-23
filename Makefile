ENV?=local

ifeq ($(ENV), local)
	SYMFONY_ENV=dev
else ifeq ($(ENV), test)
	SYMFONY_ENV=test
else ifeq ($(ENV), demo)
	SYMFONY_ENV=prod
else
	SYMFONY_ENV=$(ENV)
endif

DIRECTORY_NAME := $(shell pwd | xargs basename | tr -cd 'A-Za-z0-9_-')
DOCKER_HOST_IP := $(shell docker-machine ip 2> /dev/null)

THIS_FILE := $(lastword $(MAKEFILE_LIST))

DC=docker-compose \
	-p $(ENV)_$(DIRECTORY_NAME) \
    -f docker-compose.yml \
    -f docker/docker-compose.$(ENV).yml \
    -f docker/docker-compose.override.yml

PHP=$(DC) run --rm php
COMPOSER=$(PHP) php -n -d extension=zip.so -d memory_limit=-1 composer.phar

all: configure composer-install init vendors-install
database-reload: database-drop database-create schema-update fixtures-load
lint: lint-fix
test: test-unit test-component test-functional

configure:
	cp -n docker/docker-compose.override.yml.dist docker/docker-compose.override.yml

#tells you which docker container are running under this project
ps:
	$(DC) ps

version:
	$(DC) --version

pull:
	$(DC) pull

#rebuilds the containers
build:
	$(DC) build

init:
	$(DC) pull
	$(DC) build

stop:
	$(DC) kill

#cleans the containers for all environments
clean-all:
	$(eval $@_NAME := $(shell echo $(DIRECTORY_NAME) | tr -d '_-'))
	docker stop $(shell docker ps -a -q --filter="name=$($@_NAME)")
	docker rm -vf $(shell docker ps -a -q --filter="name=$($@_NAME)")

#removes ALL containers, not just does under this project
clean-docker:
	docker kill $(docker ps -a -q)
	docker rm $(docker ps -a -q)

composer-install:
	$(PHP) bash -c 'if [ -f composer.phar ]; then echo "Updating composer..." && php composer.phar self-update; else echo "Installing composer..." && curl -s http://getcomposer.org/installer | php; fi'

composer-dump-autoload:
	$(COMPOSER) dump-autoload

database-drop:
	$(PHP) php bin/console -v doctrine:database:drop --force

database-create:
	$(PHP) php bin/console -v doctrine:database:create

schema-update:
	$(PHP) php bin/console -v doctrine:schema:update --force

fixtures-load:
	$(PHP) php bin/console -v --no-interaction hautelook_alice:doctrine:fixtures:load

lint:
	$(PHP) php -n bin/php-cs-fixer fix --no-interaction --dry-run --diff -vvv

lint-fix:
	$(PHP) php -n bin/php-cs-fixer fix --no-interaction

vendors-install:
	$(COMPOSER) install --no-interaction --prefer-source

vendors-install-prod:
	rm -rf vendor
	$(COMPOSER) install --no-interaction --prefer-dist --optimize-autoloader --no-dev

vendors-update:
	$(COMPOSER) update

test-functional:
	$(PHP) php vendor/phpunit/phpunit/phpunit -v tests/Functional

test-functional-debug:
	$(DC) run --rm -e XDEBUG=1 php vendor/phpunit/phpunit/phpunit -v tests/Functional

test-component:
	$(PHP) php vendor/phpunit/phpunit/phpunit -v tests/Component

test-component-debug:
	$(DC) run --rm -e XDEBUG=1 php vendor/phpunit/phpunit/phpunit -v tests/Component

test-unit:
	$(PHP) php vendor/phpunit/phpunit/phpunit -v tests/Unit

test-unit-debug:
	$(DC) run --rm -e XDEBUG=1 php vendor/phpunit/phpunit/phpunit -v tests/Unit
