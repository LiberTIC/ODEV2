# `EXEC` var can be use only when you rule does not rely on `run` (that then creates new PID)
EXEC := exec -ti $(shell docker ps -f name=postgres -q) su postgres sh -c
B2DEXISTS := $(shell boot2docker version 2>/dev/null)

all:
	docker build -t symfony/code code
	docker build -t symfony/php-fpm php-fpm
	docker build -t symfony/nginx nginx
	docker build -t postgres postgres

install: run db

# `EXEC_LIVE` must be set at runtime only,
# because of the run rule, that must first end up with new containers PIDs.
db:
	$(eval EXEC_LIVE := exec -ti $(shell docker ps -f name=postgres -q) su postgres sh -c)
	docker ${EXEC_LIVE} "createuser -d -s ode"
	docker ${EXEC_LIVE} "createdb  -T template0 -E UTF8 -O ode ode"
	docker ${EXEC_LIVE} "psql -c \"GRANT ALL PRIVILEGES ON DATABASE ode to ode;\""
	docker ${EXEC_LIVE} "psql -c \"ALTER USER ode PASSWORD 'ode';\"";
	docker ${EXEC_LIVE} "psql -d ode -f \/var\/www\/symfony\/doc\/postgresql\/ODE.sql";

run:
	docker-compose up -d
ifdef B2DEXISTS
	@echo "Website is running on `boot2docker ip`";
else
	@echo "Website is running";
endif

stop:
	docker-compose stop

# Symfony's PHP App Console
pac:
	docker exec -ti $(docker ps -f name=php -q) php /var/www/symfony/app/console

# ---------------------------------------------
.PHONY: all install run stop db pac
