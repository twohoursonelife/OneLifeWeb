# Migration from original 2HOL database to similar schema as OHOL
Queries to migrate the existing 2HOL database (Created circa August 2019) data to a new DB which follows similar schema as OHOL.
The update will be known as The Great Web Server Update.

### Migrate old table `users` to `ticketServer_tickets`
Migration
```SQL
INSERT INTO PROD_2HOL.ticketServer_tickets (
    PROD_2HOL.ticketServer_tickets.key_id, 
    PROD_2HOL.ticketServer_tickets.login_key, 
    PROD_2HOL.ticketServer_tickets.discord_id,
     PROD_2HOL.ticketServer_tickets.email, 
     PROD_2HOL.ticketServer_tickets.blocked, 
     PROD_2HOL.ticketServer_tickets.time_played, 
     PROD_2HOL.ticketServer_tickets.last_activity)
SELECT id, l_key, discord_id, email, banned, time_played, last_activity
FROM keymaker.users;
```

Following migration, amend creation_date for all records to todays date at midnight as this data was not previously recorded so that when viewing this data at a later date, this can be deduced.
```SQL
UPDATE ticketServer_tickets
SET creation_date = "2022-03-01 00:00:00"
```

### Migrate old table `server_lives` to `old_lineage_lives`
This will migrate the old server lives into a new table within the new database to be archived.

On original table to prepare for migration *(This has been completed 2022-02-16)*
```SQL
ALTER TABLE keymaker.server_lives ADD lineage_depth INT NOT NULL AFTER deepest_descendant_life_id;
```

*(This has been completed 2022-02-16)*
```SQL
UPDATE keymaker.server_lives set lineage_depth = deepest_descendant_generation - generation WHERE deepest_descendant_generation != -1 and generation != -1;
```

Migration
```SQL
INSERT INTO PROD_2HOL.old_lineage_lives (
    PROD_2HOL.old_lineage_lives.id, 
    PROD_2HOL.old_lineage_lives.death_time, 
    PROD_2HOL.old_lineage_lives.server_id, 
    PROD_2HOL.old_lineage_lives.user_id, 
    PROD_2HOL.old_lineage_lives.player_id, 
    PROD_2HOL.old_lineage_lives.parent_id, 
    PROD_2HOL.old_lineage_lives.killer_id, 
    PROD_2HOL.old_lineage_lives.death_cause, 
    PROD_2HOL.old_lineage_lives.display_id, 
    PROD_2HOL.old_lineage_lives.age, 
    PROD_2HOL.old_lineage_lives.name, 
    PROD_2HOL.old_lineage_lives.male, 
    PROD_2HOL.old_lineage_lives.last_words, 
    PROD_2HOL.old_lineage_lives.generation, 
    PROD_2HOL.old_lineage_lives.eve_life_id, 
    PROD_2HOL.old_lineage_lives.deepest_descendant_generation, 
    PROD_2HOL.old_lineage_lives.deepest_descendant_life_id, 
    PROD_2HOL.old_lineage_lives.lineage_depth)
SELECT id, death_time, server_id, user_id, player_id, parent_id, killer_id, death_cause, display_id, age, name, male, last_words, generation, eve_life_id, deepest_descendant_generation, deepest_descendant_life_id, lineage_depth
FROM keymaker.server_lives;
```