#!/bin/bash
set -eu

echo -n "Enter domain for web server (web.twohoursonelife.com): "
read DOMAIN

echo -n "Enter game server domain and port (play.twohoursonelife.com 8005): "
read GAME_SERVER

# Add PHP repository
sudo apt install software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

# Install packages
sudo apt install -y php5.6 php5.6-fpm php5.6-mysql php5.6-curl php5.6-xml php5.6-xdebug nginx certbot python3-certbot-nginx

# Setup firewall
sudo ufw allow ssh
sudo ufw allow 'nginx full'
sudo ufw --force enable

# Setup web root
sudo mkdir -p /var/www/$DOMAIN
cd /var/www/$DOMAIN
sudo chown -R $USER:$USER /var/www/$DOMAIN
git clone https://github.com/twohoursonelife/OneLifeWeb

# Setup web config
sudo cp /var/www/$DOMAIN/OneLifeWeb/site.conf /etc/nginx/sites-available/$DOMAIN
sudo sed -i 's/web.twohoursonelife.com/$DOMAIN/' filename
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

# Generate HTTPS certificate and setup
certbot -n --agree-tos --nginx --no-redirect -m admin@twohoursonelife.com -d $DOMAIN

# Done
echo "Setup Complete. Further config may be required. See SETUP.md"