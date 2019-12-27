CREATE TABLE users (
   id INT NOT NULL AUTO_INCREMENT,
   email VARCHAR(254) NOT NULL UNIQUE,
   password VARCHAR(60) NOT NULL,
   name VARCHAR(50) NOT NULL,
   verified BIT NOT NULL
);

CREATE TABLE tokens (
   timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   value VARCHAR(64) NOT NULL,
   user_id INT NOT NULL,
   user_agent VARCHAR(255) NULL,
   ip_address VARCHAR(45) NULL,
   INDEX(value),
   CONSTRAINT fk_token_user FOREIGN KEY (user_id) REFERENCES users(id)
);

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

CREATE TABLE games (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   x SMALLINT NOT NULL,
   y SMALLINT NOT NULL,
   name VARCHAR(50) NOT NULL
);

CREATE TABLE players (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   user_id INT NOT NULL,
   game_id INT NOT NULL,
   name VARCHAR(50) NOT NULL,
   CONSTRAINT fk_player_user FOREIGN KEY (user_id) REFERENCES users(id),
   CONSTRAINT fk_player_game FOREIGN KEY (game_id) REFERENCES games(id)
);

CREATE TABLE terrain (
   game_id INT NOT NULL,
   x SMALLINT NOT NULL,
   y SMALLINT NOT NULL,
   type VARCHAR(20) NOT NULL,
   PRIMARY KEY(game_id, x, y),
   CONSTRAINT fk_terrain_game FOREIGN KEY (game_id) REFERENCES games(id)
);

CREATE TABLE resources (
   game_id INT NOT NULL,
   x SMALLINT NOT NULL,
   y SMALLINT NOT NULL,
   type VARCHAR(20) NOT NULL,
   quantity FLOAT NOT NULL,
   PRIMARY KEY(game_id, x, y),
   CONSTRAINT fk_resource_game FOREIGN KEY (game_id) REFERENCES games(id)
);

CREATE TABLE improvements (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   game_id INT NOT NULL,
   x SMALLINT NOT NULL,
   y SMALLINT NOT NULL,
   type VARCHAR(20) NOT NULL,
   INDEX(game_id, x, y),
   CONSTRAINT fk_improvement_game FOREIGN KEY (game_id) REFERENCES games(id)
);

CREATE TABLE units (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   player_id INT NOT NULL,
   x SMALLINT NOT NULL,
   y SMALLINT NOT NULL,
   action VARCHAR(20) NULL,
   INDEX(player_id),
   INDEX(x, y),
   CONSTRAINT fk_unit_player FOREIGN KEY (player_id) REFERENCES players(id)
);

CREATE TABLE equipment (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   unit_id INT NULL,
   x SMALLINT NOT NULL,
   y SMALLINT NOT NULL,
   type VARCHAR(20) NOT NULL,
   INDEX(unit_id),
   INDEX(x, y),
   CONSTRAINT fk_equipment_unit FOREIGN KEY (unit_id) REFERENCES units(id)
);

INSERT INTO games (name, x, y) VALUES ('dummy', 10, 10), ('alpha', 10, 10);

INSERT INTO players (user_id, game_id, name) VALUES (1, 1, 'Knut');

INSERT INTO terrain (game_id, x, y, type) VALUES
(1, 0, 0, 'water'), (1, 0, 1, 'water'), (1, 0, 2, 'water'), (1, 0, 3, 'grass'), (1, 0, 4, 'grass'), (1, 0, 5, 'grass'), (1, 0, 6, 'grass'), (1, 0, 7, 'grass'), (1, 0, 8, 'grass'), (1, 0, 9, 'grass'),
(1, 1, 0, 'water'), (1, 1, 1, 'water'), (1, 1, 2, 'water'), (1, 1, 3, 'grass'), (1, 1, 4, 'grass'), (1, 1, 5, 'grass'), (1, 1, 6, 'grass'), (1, 1, 7, 'grass'), (1, 1, 8, 'grass'), (1, 1, 9, 'grass'),
(1, 2, 0, 'water'), (1, 2, 1, 'water'), (1, 2, 2, 'water'), (1, 2, 3, 'water'), (1, 2, 4, 'water'), (1, 2, 5, 'water'), (1, 2, 6, 'grass'), (1, 2, 7, 'grass'), (1, 2, 8, 'grass'), (1, 2, 9, 'grass'),
(1, 3, 0, 'water'), (1, 3, 1, 'water'), (1, 3, 2, 'water'), (1, 3, 3, 'water'), (1, 3, 4, 'water'), (1, 3, 5, 'water'), (1, 3, 6, 'grass'), (1, 3, 7, 'water'), (1, 3, 8, 'water'), (1, 3, 9, 'grass'),
(1, 4, 0, 'water'), (1, 4, 1, 'water'), (1, 4, 2, 'water'), (1, 4, 3, 'water'), (1, 4, 4, 'water'), (1, 4, 5, 'water'), (1, 4, 6, 'grass'), (1, 4, 7, 'water'), (1, 4, 8, 'water'), (1, 4, 9, 'water'),
(1, 5, 0, 'water'), (1, 5, 1, 'water'), (1, 5, 2, 'water'), (1, 5, 3, 'grass'), (1, 5, 4, 'grass'), (1, 5, 5, 'water'), (1, 5, 6, 'water'), (1, 5, 7, 'water'), (1, 5, 8, 'water'), (1, 5, 9, 'water'),
(1, 6, 0, 'water'), (1, 6, 1, 'grass'), (1, 6, 2, 'grass'), (1, 6, 3, 'grass'), (1, 6, 4, 'grass'), (1, 6, 5, 'water'), (1, 6, 6, 'water'), (1, 6, 7, 'water'), (1, 6, 8, 'water'), (1, 6, 9, 'water'),
(1, 7, 0, 'water'), (1, 7, 1, 'water'), (1, 7, 2, 'grass'), (1, 7, 3, 'grass'), (1, 7, 4, 'water'), (1, 7, 5, 'water'), (1, 7, 6, 'water'), (1, 7, 7, 'water'), (1, 7, 8, 'water'), (1, 7, 9, 'water'),
(1, 8, 0, 'water'), (1, 8, 1, 'water'), (1, 8, 2, 'water'), (1, 8, 3, 'water'), (1, 8, 4, 'water'), (1, 8, 5, 'water'), (1, 8, 6, 'water'), (1, 8, 7, 'water'), (1, 8, 8, 'water'), (1, 8, 9, 'grass'),
(1, 9, 0, 'water'), (1, 9, 1, 'water'), (1, 9, 2, 'water'), (1, 9, 3, 'water'), (1, 9, 4, 'water'), (1, 9, 5, 'water'), (1, 9, 6, 'water'), (1, 9, 7, 'desert'), (1, 9, 8, 'desert'), (1, 9, 9, 'desert');

INSERT INTO resources (game_id, x, y, type, quantity) VALUES
(1, 0, 6, 'bronze', 5000),
(1, 1, 6, 'copper', 3000),
(1, 2, 6, 'gold', 500),
(1, 3, 6, 'iron', 2000),
(1, 4, 6, 'sandstone', 10000),
(1, 2, 7, 'silver', 1000);

INSERT INTO improvements (game_id, x, y, type) VALUES
(1, 0, 3, 'castle'),
(1, 6, 1, 'tower'),
(1, 6, 2, 'craftshop'),
(1, 7, 2, 'fisher'),
(1, 5, 3, 'market'),
(1, 6, 3, 'church'),
(1, 7, 3, 'temple'),
(1, 9, 8, 'pyramid'),
(1, 9, 9, 'oracle');

INSERT INTO units (player_id, x, y, action) VALUES
(1, 0, 4, NULL);
