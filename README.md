# scores

A PHP slim app providing a RESTful API through Apache and MYSQL.

public endpoints:
http://13.56.107.102/scores/public/api/tetris_scores get top scores.
http://13.56.107.102/scores/public/api/tetris_scores/player/{name} get top scores for player named {name}.
http://13.56.107.102/scores/public/api/tetris_scores/add post high score as json {"name": {String:name}, "score": {int:score}}.
