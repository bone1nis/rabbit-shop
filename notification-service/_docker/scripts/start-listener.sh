#!/bin/sh

cd /_docker/scripts
./wait-for-it.sh rabbitmq:5672 --timeout=50 --strict -- echo "RabbitMQ is up"

cd /var/www || exit 1

php artisan rabbitmq:order-status-listener