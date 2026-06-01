#!/bin/bash
# DriveNow ERP — create one isolated database per microservice.
# Runs once, on first initialization of the Postgres data volume. The default
# database (drivenow_auth) is created by POSTGRES_DB; this adds the other seven.
# All are owned by the same POSTGRES_USER (the `drivenow` role) but are
# physically separate databases — no service can query another's tables.
set -e

for db in \
    drivenow_fleet \
    drivenow_rental \
    drivenow_crm \
    drivenow_billing \
    drivenow_maintenance \
    drivenow_analytics \
    drivenow_client; do
    echo "  creating database: $db"
    psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
        CREATE DATABASE $db OWNER $POSTGRES_USER;
EOSQL
done

echo "DriveNow: all service databases created."
