SHELL := /bin/bash

tests:
	symfony console doctrine:database:drop --force --env=test || true
	symfony console doctrine:database:create --env=test
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --env=test
	symfony php bin/phpunit tests/Controller/ConferenceControllerTest.php
	symfony php bin/phpunit tests/Controller/CommentControllerTest.php
	symfony php bin/phpunit tests/SpamCheckerTest.php
.PHONY: tests