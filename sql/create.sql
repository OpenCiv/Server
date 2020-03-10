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