# (var) execute a shell command in a live postgres container, as a `postgres` Linux user
EXEC_LIVE := exec -ti docker_postgres_1 su postgres sh -c

# ---------------------------------------------
all:
	docker build -t symfony/code code
	docker build -t symfony/php-fpm php-fpm
	docker build -t symfony/nginx nginx
	docker build -t postgres postgres

# ---------------------------------------------
install:
	docker-compose up -d
	docker ${EXEC_LIVE} "createuser -d -s ode"
	docker ${EXEC_LIVE} "createdb -O ode ode"
	docker ${EXEC_LIVE} "psql -c \"GRANT ALL PRIVILEGES ON DATABASE ode to ode;\""
	docker ${EXEC_LIVE} "psql -c \"ALTER USER ode PASSWORD 'ode';\"";
	docker ${EXEC_LIVE} "pg_restore -O -d ode \/var\/www\/symfony\/doc\/postgresql\/init.dump";

# ---------------------------------------------
.PHONY: all install
