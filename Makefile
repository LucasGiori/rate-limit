PWD = $(shell pwd -L)
DOCKER_RUN = docker container exec php81

update:
	- ${DOCKER_RUN} composer update

test:
	- ${DOCKER_RUN} composer test

php-cs:
	- ${DOCKER_RUN} composer php-cs

open-dashboard:
	- xdg-open report/html-coverage/dashboard.html

open-html:
	- xdg-open report/html-coverage/index.html

clean-report:
	- @sudo rm -r report

clean-cache:
	- @sudo rm .phpunit.result.cache