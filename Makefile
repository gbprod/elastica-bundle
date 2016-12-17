.PHONY: install test-unit

install: vendor

test-unit: vendor
	vendor/bin/phpunit

vendor: composer.lock
	composer install

composer.lock: composer.json
	composer update --lock
