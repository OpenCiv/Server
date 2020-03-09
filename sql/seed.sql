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
