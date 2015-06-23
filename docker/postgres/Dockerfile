FROM debian:jessie

MAINTAINER Ronan Guilloux <ronan.guilloux@gmail.com>

ENV PG_VERSION 9.3
RUN apt-get update
RUN apt-get -y install postgresql postgresql-client postgresql-contrib

RUN echo "host    all             all             0.0.0.0/0 trust" >> /etc/postgresql/$PG_VERSION/main/pg_hba.conf
RUN echo "listen_addresses='*'" >> /etc/postgresql/$PG_VERSION/main/postgresql.conf

RUN service postgresql start && \
 su postgres sh -c "createuser -d -r -s ODE" && \
 su postgres sh -c "createdb -O ODE ODE" && \
 su postgres sh -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE ODE to ODE;\""

EXPOSE 5432
CMD ["su", "postgres", "-c", "/usr/lib/postgresql/$PG_VERSION/bin/postgres -D /var/lib/postgresql/$PG_VERSION/main/ -c config_file=/etc/postgresql/$PG_VERSION/main/postgresql.conf"]