PHP ?= php

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: install
install: vendor ## Installs dependencies

.PHONY: test
test: cs-test phpunit ## Tests the application

.PHONY: cs-test
cs-test: build/php-cs-fixer ## Tests coding styles
	$(PHP) build/php-cs-fixer fix --diff --dry-run

.PHONY: cs-fix
cs-fix: build/php-cs-fixer ## Fixes coding styles
	$(PHP) build/php-cs-fixer fix --diff

.PHONY: coverage
coverage: vendor ## Generates code coverage
	$(PHP) vendor/bin/phpunit --coverage-html build/coverage --coverage-text=build/coverage.text
	$(PHP) tests/coverage-checker.php build/coverage.text 100

.PHONY: phpunit
phpunit: vendor ## Executes PHPUnit tests
	$(PHP) vendor/bin/phpunit

build/php-cs-fixer:
	test -d build || mkdir build
	curl -L https://cs.symfony.com/download/php-cs-fixer-v2.phar -o build/php-cs-fixer
	chmod a+x build/php-cs-fixer

vendor:
	composer install
