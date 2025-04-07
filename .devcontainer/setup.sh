#!/bin/bash

set -e

# Update and install dependencies
apt-get update
apt-get install -y libicu-dev

# Install and enable intl extension
docker-php-ext-install intl

# Manually enable the extension
echo "extension=intl.so" > /usr/local/etc/php/conf.d/20-intl.ini
