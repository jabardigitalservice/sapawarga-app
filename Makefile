PREFIX=registry.gitlab.com/jdsteam/sapa-warga/sapawarga-app/sapawarga-backend

build-php:
	@docker build -t ${PREFIX}-php-fpm:dev -f docker/php-fpm/Dockerfile api/
