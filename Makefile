PWD = $(shell pwd -L)
DOCKER_RUN = docker run --rm -it --network host -v ${PWD}:/app lucas770docker/php:build-8.1

update:
	- ${DOCKER_RUN} 'composer update'

test:
	- ${DOCKER_RUN} 'composer test'

php-cs:
	- ${DOCKER_RUN} 'composer php-cs'

open-dashboard:
	- xdg-open report/html-coverage/dashboard.html

open-html:
	- xdg-open report/html-coverage/index.html

clean-report:
	- @sudo rm -r report

clean-cache:
	- @sudo rm .phpunit.result.cache