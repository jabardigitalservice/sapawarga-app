#!/bin/sh

printf "Checking database connection...\n\n"
mysql_ready() {
    mysqladmin ping --host=$MYSQL_HOST --user=root --port=$MYSQL_PORT --password=$MYSQL_ROOT_PASSWORD > /dev/null 2>&1
}

while !(mysql_ready)
do
    sleep 3
    echo "Waiting for database connection ..."
done

printf "Upgrading database...\n\n"
./yii migrate --migrationPath=@yii/rbac/migrations --interactive=0
./yii migrate/up --interactive=0

printf "Starting php-fpm...\n\n"
php-fpm

exec "$@"
