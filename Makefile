cs:
	docker compose run --rm phpcli vendor/bin/php-cs-fixer fix --ansi --verbose

cq:
	docker compose run --rm phpcli vendor/bin/phpstan analyse --level max src tests

test:
	docker compose run --rm phpcli vendor/bin/phpunit tests

setup:
	docker compose run --rm phpcli composer install
	docker compose exec mysql bash -c "mysql -u root -h mysql --password=root_password promocode < /data/schema.sql && mysql -u root --password=root_password -e 'CREATE DATABASE promocode_test;' && mysql -u root -h mysql --password=root_password promocode_test < /data/schema.sql"
	docker compose exec mysql bash -c "mysql -u root --password=root_password -e 'GRANT ALL PRIVILEGES ON promocode_test.* TO promocode_user WITH GRANT OPTION;'"
	docker compose run --rm phpcli php bin/generate-codes.php

check: cs cq test