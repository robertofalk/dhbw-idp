#!/bin/bash

set -e

# Update and install dependencies
apt-get update
apt-get install -y libicu-dev

# Install and enable intl extension
docker-php-ext-install intl
docker-php-ext-enable xdebug
