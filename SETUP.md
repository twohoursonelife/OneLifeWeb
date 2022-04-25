# Server setup for OneLifeWeb

## Basic setup

$5/month [DigitalOcean](https://m.do.co/c/930cfa370b47) droplet will do the trick.

For testing I use the root user, but for best practice setup seee [initial server setup](https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-20-04).

## Main setup
Run [setup.sh](https://github.com/twohoursonelife/OneLifeWeb/blob/main/setup.sh)

Either clone this directory into your home directory and run the script or copy past the script into a new file in your home directory.

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
- It may be necessary to copy downloads and patches from an existing server to the new server. This should be kept in `OneLifeWeb/data/diffDownloads`
- Enter current game version in `OneLifeWeb/data/diffDownloads/requiredVersion.php`


