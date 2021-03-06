# Cloudflare Dynamic IP DNS updater

This tool allow you to have a specific DNS record souch as home.example.com that point to your home IP

### Requirements

- composer
- php-7.4

### Installation

git clone @

cd cloudflare-dynamic-ip

INSTALL_PATH=$(pwd)

composer install

nano .env

echo -e "*/15 * * * * $INSTALL_PATH/cf-ip 2&1> /dev/null\n"

crontab -e

### Update

git pull

composer update

