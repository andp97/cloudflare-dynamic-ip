# How to upgrade from `v1.x` to `v2.x`

### Using git repository 
 
```shell
git pull
git checkout `v2`
ln -s builds/cloudflare-ddns cf-ip
```

### Migrating from git repository to `cloudflare-ddns` bin
Follow these steps:
1. Backup the .env file
2. Remove the repository direcotry
3. Create a new direcotry 
4. Put into the `.env` file

**Example:**
```shell
[user@host]$ pwd
/opt/cloudflare-dynamic-ip/
[user@host]$ cd ..
[user@host]$ cp /opt/cloudflare-dynamic-ip/.env cf-env.txt
[user@host]$ rm -rf /opt/cloudflare-dynamic-ip/
[user@host]$ mkdir "cloudflare-ddns" && cd cloudflare-ddns
[user@host]$ curl -s -o cloudflare-ddns https://github.com/andp97/cloudflare-dynamic-ip/releases/download/v2.0.0/cloudflare-ddns
[user@host]$ cp ../cf-env.txt .env
```