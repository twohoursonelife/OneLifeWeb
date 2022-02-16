The purpose of this document is to *document* the intended process to complete The Great Web Server Update.
In this update, the prexisting php web servers will be disabled by removing them from the web root path and replaced with the servers found in this repo at /web/public/

# The Great Web Server Update
- Plan a timeframe with 24-48 hours notice, ideally when multiple of Colin, Kripts, Risvh or additional support such as Adk or Tux are avaliable, but mostly a heads up for players.
- Shutdown the game server, nginx (play.twohoursonelife.com) and dictator bot.
- Locate new database, completely empty and fresh. (`PROD_2HOL`, in this case with user `game_server` for config use and user `thol` for migration tasks)
- Complete existing database migration to new database by following `database_migration.md`, found in the same directory as this document.
- Rename existing web root `/var/www/play.twohoursonelife.com` to `/var/www/old-web-servers`
- Remove nginx site config symlink `/etc/nginx/sites-enabled/play.twohoursonelife.com`
- Rename nginx site config `/etc/nginx/sites-avaliable/play.twohoursonelife.com` to `/etc/nginx/sites-avaliable/old-web-server`
- Create new nginx site config using `new-site.conf` named `2HOLWeb`, found in the same directory as this document.
- Symlink new nginx site config to `/etc/nginx/sites-enabled/2HOLWeb`
- Test nginx config `nginx -t`
