# Cloudflare Dynamic IP DNS Updater

This tool allow you to have a specific DNS record such as `home.example.com` that point to your home IP

### Requirements

- `composer`
- `php-7.4`

### Installation
```
git clone https://github.com/andp97/cloudflare-dynamic-ip.git
cd cloudflare-dynamic-ip
INSTALL_PATH=$(pwd)
composer install
nano .env
echo -e "*/15 * * * * $INSTALL_PATH/cf-ip 2&1> /dev/null\n"
crontab -e
```

You can also execute this script via bash
```
#Get dns record name from env and target ip from remote host (https://ip.andreapavone.com) 
./cf-ip
#Passing parameters to the script
./cf-ip <dns_record_name> <target_ip>
```

On `.env` file you can set:
- `CLOUDFLARE_TOKEN` (see how can you generate token here: https://support.cloudflare.com/hc/en-us/articles/200167836-Managing-API-Tokens-and-Keys)
- `CLOUDFLARE_ZONE_NAME` or `CLOUDFLARE_ZONE_ID`
- `DNS_RECORD_NAME` is the subdomain that you prefer to use (for example: **home**.example.com). If you prefer to be safer use a randomized token for the `DNS_RECORD_NAME`


### Update
```
git pull
composer update
```

