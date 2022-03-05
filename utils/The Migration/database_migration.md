# Migration from original 2HOL database to similar schema as OHOL
Queries to migrate the existing 2HOL database (Created circa August 2019) data to a new DB which follows similar schema as OHOL.
The update will be known as The Great Web Server Update.

### Migrate old table `users` to `ticketServer_tickets`
Migration
```SQL
INSERT INTO DEV_2HOL.ticketServer_tickets (DEV_2HOL.ticketServer_tickets.key_id, DEV_2HOL.ticketServer_tickets.login_key, DEV_2HOL.ticketServer_tickets.discord_id, DEV_2HOL.ticketServer_tickets.email, DEV_2HOL.ticketServer_tickets.blocked, DEV_2HOL.ticketServer_tickets.time_played, DEV_2HOL.ticketServer_tickets.last_activity)
SELECT id, l_key, discord_id, email, banned, time_played, last_activity
FROM keymaker.users;
```

Following migration, amend newTable.creation_date for all records to one specific long past point in time as this data was not previously recorded so that when viewing this data at a later date, this can be deduced.
```SQL
UPDATE ticketServer_tickets
SET creation_date = "2000-01-01 00:00:00"
```

#
#
#
## Migration Complete
Do not follow any further steps, all have been depreciated.
Queries kept for testing/dev purposes.
#
#
#

### Migrate old table `server_lives` to `lineageServer_lives`
On original table to prepare for migration *(This has been completed 2022-02-16)*
```SQL
ALTER TABLE keymaker.server_lives ADD lineage_depth INT NOT NULL AFTER deepest_descendant_life_id;
```

*(This has been completed 2022-02-16)*
```SQL
UPDATE keymaker.server_lives set lineage_depth = deepest_descendant_generation - generation WHERE deepest_descendant_generation != -1 and generation != -1;
```

Migration
This is no longer necessary. This migration is inadequate and the work required to complete it is more than we care about migrating this data. (Theres a new users table for lineage, which would need to be populated from scratch)
```SQL
INSERT INTO DEV_2HOL.lineageServer_lives (DEV_2HOL.lineageServer_lives.id, DEV_2HOL.lineageServer_lives.death_time, DEV_2HOL.lineageServer_lives.server_id, DEV_2HOL.lineageServer_lives.user_id, DEV_2HOL.lineageServer_lives.player_id, DEV_2HOL.lineageServer_lives.parent_id, DEV_2HOL.lineageServer_lives.killer_id, DEV_2HOL.lineageServer_lives.death_cause, DEV_2HOL.lineageServer_lives.display_id, DEV_2HOL.lineageServer_lives.age, DEV_2HOL.lineageServer_lives.name, DEV_2HOL.lineageServer_lives.male, DEV_2HOL.lineageServer_lives.last_words, DEV_2HOL.lineageServer_lives.generation, DEV_2HOL.lineageServer_lives.eve_life_id, DEV_2HOL.lineageServer_lives.deepest_descendant_generation, DEV_2HOL.lineageServer_lives.deepest_descendant_life_id, DEV_2HOL.lineageServer_lives.lineage_depth)
SELECT id, death_time, server_id, user_id, player_id, parent_id, killer_id, death_cause, display_id, age, name, male, last_words, generation, eve_life_id, deepest_descendant_generation, deepest_descendant_life_id, lineage_depth
FROM keymaker.server_lives;
```