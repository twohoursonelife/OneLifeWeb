# The Great Migration

This directory exists to write a process for a major migration for 2HOL, involving a complete overhaul of our database and PHP web servers.

The migration was successfully completed on March 17 2022 with no major issues.

The only notable oversight was missing php packages on the target server. Pre migration php 7.2 was in use. I decided to roll back to php 5.6 for code compatibility but did not assess that I would need to reinstall the necessary packages for this specific version of php. I was able to quickly find the list of packages in the OneLife repo and install them without issue.