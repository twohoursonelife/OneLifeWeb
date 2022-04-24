#!/bin/bash
set -eu

DOMAIN="web.twohoursonelife.com"
GAME_SERVER="play.twohoursonelife.com 8005"

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
sudo cp /var/www/$DOMAIN/OneLifeWeb/site.conf /etc/nginx/sites-available/$DOMAIN
sudo ln -s /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
sudo systemctl restart nginx

# App setup
cd ~
git clone https://github.com/twohoursonelife/OneLifeData7
cp -r ~/OneLifeData7/faces/ /var/www/$DOMAIN/OneLifeWeb/web/public/lineageServer/faces

mkdir /var/www/$DOMAIN/OneLifeWeb/web/public/photoServer/photos

mkdir /var/www/$DOMAIN/OneLifeWeb/data/diffDownloads/patches
ln -s /var/www/$DOMAIN/OneLifeWeb/data/diffDownloads /var/www/$DOMAIN/OneLifeWeb/web/public/downloads

echo "twohoursonelife $GAME_SERVER" >> /var/www/$DOMAIN/OneLifeWeb/web/public/reflector/remoteServerList.ini

# https setup. DO NOT enable redirects
sudo apt install -y certbot python3-certbot-nginx
certbot -n --agree-tos --nginx --no-redirect -m admin@twohoursonelife.com -d $DOMAIN

# Done
echo "Setup Complete. Check server address is correct in /etc/nginx/sites-avaliable/$DOMAIN and complete OneLifeWeb/web/public/config.php"