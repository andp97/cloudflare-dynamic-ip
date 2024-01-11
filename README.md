# Cloudflare ☁️ Dynamic DNS IP Updater

This tool allow you to have a specific DNS record such as `home.example.com` that point to your home IP


#### Use Case
I suggest you to install it on a Raspberry Pi!🍓

### Requirements

- Domain Name
- [Cloudflare Account](https://www.cloudflare.com/) 
- Raspberry Pi4 or any other Linux Based OS
- `php-8`

### Installation

#### Download
```
mkdir cloudflare-ddns && cd cloudflare-ddns
curl -s -o cloudflare-ddns https://github.com/andp97/cloudflare-dynamic-ip/releases/download/v2.0.7a/cloudflare-ddns
```
#### `.env` setup
```shell
cat > .env << EOF
CLOUDFLARE_TOKEN="CHANGE_ME"
CLOUDFLARE_ZONE_NAME="CHANGE_ME"
DNS_RECORD_NAME="home-ip-CHANGE_ME_WITH_A_RANDOM_TOKEN"
EOF
```

#### Crontab setup
```shell
echo -e "*/15 * * * * <install_dir>/cloudflare-ddns 2>&1 > /dev/null\n"
crontab -e
```

### Run script
Get dns record name from env and target ip from remote host (https://ip.andp97.dev/api/vs) 
```shell
./cloudflare-ddns
```
Passing parameters to the script
```shell
./cloudflare-ddns update <dns_record_name> <target_ip>
```

### Help
```shell
[user@host]$ cloudflare-ddns update --help
Description:
  Update the default Cloudflare record IP to current Public IP

Usage:
  update [<dns_record_name> [<target_ip> [<zone_name>]]]

Arguments:
  dns_record_name       Cloudflare API Token
  target_ip             DNS Record Target IP
  zone_name             DNS Zone Name
```

### Enviroment Variables

| Variable               | Type     | Required |
|------------------------|----------|----------|
| `CLOUDFLARE_TOKEN`     | `string` | Yes      |
| `CLOUDFLARE_ZONE_NAME` | `string` |          |
| `CLOUDFLARE_ZONE_ID`   | `string` |          |
| `DNS_RECORD_NAME`      | `string` |          |

On `.env` file you can set:
- `CLOUDFLARE_TOKEN` (see how can you generate token here: https://support.cloudflare.com/hc/en-us/articles/200167836-Managing-API-Tokens-and-Keys)
- `CLOUDFLARE_ZONE_NAME` or `CLOUDFLARE_ZONE_ID`
- `DNS_RECORD_NAME` is the subdomain that you prefer to use (for example: **home**.example.com). If you prefer to be safer use a randomized token for the `DNS_RECORD_NAME`

### Upgrading from `v1.x`?
[UPGRADE.md](https://github.com/andp97/cloudflare-dynamic-ip/blob/v2/UPGRADE.md)

### Update
```
git pull
composer update
```

