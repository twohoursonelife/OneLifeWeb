# Server setup for OneLifeWeb

## Basic setup

$5/month [DigitalOcean](https://m.do.co/c/930cfa370b47) droplet will do the trick.

For testing I use the root user, but for best practice setup seee [initial server setup](https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-20-04).
1. [Install Nginx](https://www.digitalocean.com/community/tutorials/how-to-install-nginx-on-ubuntu-20-04)
2. [Secure Nginx with Let's Encrypt](https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-20-04)

This script will do it all
```bash
$DOMAIN = "web.twohoursonelife.com"
$GAME_SERVER = "play.twohoursonelife.com 8005"

# Setup
sudo apt update
sudo ufw allow ssh
sudo ufw allow 'nginx full'
sudo ufw --force enable

# Install PHP
sudo apt install software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php5.6 php5.6-fpm php5.6-mysql php5.6-curl php5.6-xml php5.6-xdebug

# Install nginx
sudo apt install -y nginx

# Setup web root
sudo mkdir -p /var/www/$DOMAIN
cd /var/www/$DOMAIN
sudo chown -R $USER:$USER /var/www/$DOMAIN
git clone https://github.com/twohoursonelife/OneLifeWeb

# Setup web config
sudo cp /var/www/$DOMAIN/OneLifeWeb/site.conf /etc/nginx/sites-avaliable/$DOMAIN
sudo ln -s /etc/nginx/site-avaliable/$DOMAIN /etc/nginx/sites-enabled/
sudo systemctl restart nginx

# Game web servers setup
cd ~
git clone https://github.com/twohoursonelife/OneLifeData7
cp - r ~/OneLifeData7/faces/ /var/www/$DOMAIN/OneLifeWeb/web/public/lineageServer/faces

mkdir /var/www/$DOMAIN/OneLifeWeb/web/public/photoServer/photos

mkdir /var/www/$DOMAIN/OneLifeWeb/data/diffDownloads/patches
ln -s /var/www/$DOMAIN/OneLifeWeb/data/diffDownloads /var/www/$DOMAIN/web/public/downloads

echo "twohoursonelife $GAME_SERVER" >> /var/www/$DOMAIN/OneLifeWeb/web/public/reflector/remoteServerList.ini

# https setup. DO NOT enable redirects
certbot -d $DOMAIN

echo "Setup Complete. Check server address is correct in /etc/nginx/sites-avaliable/$DOMAIN and complete OneLifeWeb/web/public/config.php"
```

## config.php
Required details for config

- DB Address (db.twohoursonelife.com)
- DB Name (Name)
- DB Username (Username)
- DB Password (Password)
- Shared game server secret (String)
- Password hasing pepper (40 chars [a-zA-Z0-9])
- Access passwords (Max 20 chars)
- Hashs of access passwords (Use ticketServer/passwordHashUtility.php after password hasing pepper configured)
- Shared encryption secret (40 chars [a-zA-Z0-9])

## Furter config
- It may be necessary to copy downloads and patches from an existing server to the new server. This should kept in OneLifeWeb/data/diffDownloads
- Enter current game version in `OneLifeWeb/data/diffDownloads/requiredVersion.php`


