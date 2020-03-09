ALTER TABLE improvements DROP FOREIGN KEY fk_improvement_game;
ALTER TABLE units DROP FOREIGN KEY fk_unit_player;
ALTER TABLE players DROP FOREIGN KEY fk_player_game;
ALTER TABLE players DROP FOREIGN KEY fk_player_user;
ALTER TABLE terrain DROP FOREIGN KEY fk_terrain_game;
ALTER TABLE resources DROP FOREIGN KEY fk_resource_game;

DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS improvements;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS terrain;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS terrain;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS improvements;
