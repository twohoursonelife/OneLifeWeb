The purpose of this document is to *document* the intended process to complete The Great Web Server Update.
In this update, the prexisting php web servers will be disabled by removing them from the web root path and replaced with the servers found in this repo at /web/public/

# The Great Web Server Update

### Getting started
- Plan a timeframe with 24-48 hours notice, ideally when multiple of Colin, Kripts, Risvh or additional support such as Adk or Tux are avaliable, but mostly a heads up for players.
- Ensure pre-requisite tasks completed.
- Shutdown the game server and dictator bot.
- Locate new database, empty. (Except for the table oldLineageLives, a record of premigration lives that will not be migrated) (`PROD_2HOL`, in this case with user `game_web_server` for config use and user `thol` for migration tasks)

### Nginx changes
- Rename existing web root `/var/www/play.twohoursonelife.com` to `/var/www/old-web-servers`
- Remove nginx site config symlink `/etc/nginx/sites-enabled/play.twohoursonelife.com`
- Rename nginx site config `/etc/nginx/sites-avaliable/play.twohoursonelife.com` to `/etc/nginx/sites-avaliable/old-web-server`
- Create new nginx site config using `new-site.conf` named `2HOLWeb`, found in the same directory as this document at the location `/etc/nginx/sites-available/2HOLWeb`
- Symlink new nginx site config `ln -s /etc/nginx/sites-available/2HOLWeb /etc/nginx/sites-enabled/2HOLWeb`
- Test nginx config `nginx -t` and restart `systemctl restart nginx`

### Deploy new PHP servers
- Clone this directory into place. In `/var/www/` run `git clone https://github.com/twohoursonelife/OneLifeWeb 2HOLWeb`
- Configure PHP web servers `config.php` `reflector/requiredVersion.php`
- Clone https://github.com/twohoursonelife/OneLifeData7 into home directory and copy faces directory into /var/www/2HOLWeb/web/public/lineageServer/

### Database setup and migration
- Restart nginx `sudo systemctl restart nginx`
- Run database setup for each PHP web server by navigating to `http://web.twohoursonelife/serverName/server.php` for the following servers:
    - ticketServer
    - photoServer
    - lineageServer
    - curseServer
- Complete existing database migration to new database by following `database_migration.md`, found in the same directory as this document.
- Insert into lineageServer_servers `id = 1, server = mainServer`

### Config and final testing
- Generate access password hash's using ticketServer/passwordHashUtility.php
- Update game server settings to new web server URL `web.twohoursonelife.com` and mirror changes in OneLife repository.
    - Update server ID to 'mainServer'
- Use certbot to generate optional SSL certs and config.
- Restart game server. Login using your own client, confirm login and death log successful.
- Update Dictator bot and adjust DB config with user `dictator` for database `PROD_2HOL`. Restart Dictator.
- Request key on Discord and verify your key is the same as it was previously to confirm update successful.
- Update complete and can be announced.
- Monitor for PHP errors and user error reports in Discord.


# Pre-requisits
- Install PHP-FPM 5.6. Servers are not yet migrated to more up to date versions. https://tecadmin.net/install-php-ubuntu-20-04/
- Gather PHP web server configuration information.
    - DB Address db.twohoursonelife.com
    - DB Name DEV_2HOL
    - DB Username dev_game_web_server
    - DB Password q95ybkwh!9RhB!8!
    - Shared game server secret rival-reveler-strike
    - Password hasing pepper i3oimgfm7g7jfx3mb267mmc4khtvkhrnsvwuh9gk
    - Access passwords (Max 20 chars) ambition-limes-bloom
    - Hashs of access passwords (passwordHashUtility.php)  7f1ab5219362cbf0b1667f1757519b0dad0a7375
    - Shared encryption secret nrf42488ep697bxvwd7suon7bkwk2427tyiu8o68
- Gather dictator configuration information.
    - DB Address db.twohoursonelife.com
    - DB Name DEV_2HOL
    - DB Username dev_dictator
    - DB Password 7I1a8OpPSV0F3aJq
- Ensure dictator MySQL account has sufficient permissions for PROD_2HOL database.

# Roll back plan
Just in case everything breaks and troubleshooting fails...
- `sudo rm -rf /`
