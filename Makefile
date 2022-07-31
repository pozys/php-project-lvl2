lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin tests

tests:
	composer exec --verbose phpunit tests

install:
	composer install