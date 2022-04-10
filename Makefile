DOCKER_RUN=docker run --rm -v "$(CURDIR)":"/app" -w "/app"
DOCKER_PHP=$(DOCKER_RUN) php:8.1-alpine3.14 php
DOCKER_COMPOSER=$(DOCKER_RUN) composer:2 --prefer-dist --optimize-autoloader

vendor:
	$(DOCKER_COMPOSER) install

tools/php-cs-fixer/vendor:
	$(DOCKER_COMPOSER) install --working-dir=tools/php-cs-fixer

fix: tools/php-cs-fixer/vendor
	$(DOCKER_PHP) tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

test: vendor
	$(DOCKER_PHP) bin/phpunit tests
