<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->get('/api/tetris_scores', function(Request $request, Response $response) {
    $sql = "select * from tetris_scores";

    try {
        $db = new db();
        $conn = $db->connect();
        $result = $conn->query($sql);

        if ($conn->errno) {

            echo '{"error": {"text": "'.$conn->error.'"}}';
        } else {
            echo json_encode($result->fetch_all($resulttype = MYSQLI_ASSOC));
        }
    } catch (ErrorException $e) {
        echo '{"error": {"text": "'.$e->getMessage().'"}}';
    }
});

$app->get('/api/tetris_scores/fire', function(Request $request, Response $response) {
    $path = "/scores/tetris";

    try {
        $fire = new fire();
        $conn = $fire->connect();
        $data = $conn->get($path);
        $data = json_decode($data, true);

        if($data) {
			function cmp_score($a, $b) {
				return $b['score'] - $a['score'];
			}
			usort($data, "cmp_score");
			$data = json_encode($data);

            echo $data;
        } else {
            echo '{"error": {"text": "No data available."}}';
        }
    } catch (ErrorException $e) {
        echo '{"error": {"text": "'.$e->getMessage().'"}}';
    }
});

$app->get('/api/tetris_scores/player/{name}', function(Request $request, Response $response) {

    $name = $request->getAttribute('name');

    $sql = "select * 
			from tetris_scores
			where name = '$name'";

    try {
        $db = new db();
        $conn = $db->connect();
        $result = $conn->query($sql);

        if ($conn->errno) {

            echo '{"error": {"text": "'.$conn->error.'"}}';
        } else {
            echo json_encode($result->fetch_all($resulttype = MYSQLI_ASSOC));
        }
    } catch (ErrorException $e) {
        echo '{"error": {"text": "'.$e->getMessage().'"}}';
    }
});

$app->get('/api/tetris_scores/fire/player/{name}', function(Request $request, Response $response) {
    $name = $request->getAttribute('name');

    $path = "/scores/tetris";

    try {
        $fire = new fire();
        $conn = $fire->connect();
        $data = $conn->get($path, Array('orderBy' => '"name"', 'equalTo' => '"'.$name.'"')); 
        $data = json_decode($data, true);

        if($data) {
			function cmp_score($a, $b) {
				return $b['score'] - $a['score'];
			}
			usort($data, "cmp_score");
			$data = json_encode($data);

            echo $data;
        } else {
            echo '{"error": {"text": "No data available."}}';
        }
    } catch (ErrorException $e) {
        echo '{"error": {"text": "'.$e->getMessage().'"}}';
    }
});

$app->post('/api/tetris_scores/add', function(Request $request, Response $response) {

    $name = $request->getParam('name');
    $score = $request->getParam('score');
    $max_size = 10;
    
    try {
        $db = new db();
        $conn = $db->connect();

        $sql = "create database if not exists game_scores";
        $conn->query($sql);

        $conn->select_db("game_scores");
        $sql = "show tables like 'tetris_scores'";
        if(!$conn->query($sql)->num_rows) {
            $sql = "create table tetris_scores (
                        name varchar(30) not null,
                        score int,
                        date date,
                        `ip-address` varchar(30)
                    );";
            $conn->query($sql);
            push_score($conn, $name, $score);
        } else {
            $sql = "select count(*) 
                from tetris_scores";

            $result = $conn->query($sql);
            if ($conn->errno) {

                echo '{"error": {"text": "'.$conn->error.'"}}';
            } else {
                if ($result->fetch_row()[0] < $max_size) {
                    push_score($conn, $name, $score);
                } else {
                    $last_offset = ($max_size - 1);
                    $sql = "select score 
                        from tetris_scores
                        limit 1 offset $last_offset";
                    $result = $conn->query($sql);
                    $score_to_beat = $result->fetch_row()[0];
                    if ($score > $score_to_beat) {
                        $sql = "delete 
                            from tetris_scores
                            order by score asc 
                            limit 1";

                        $conn->query($sql);
                        push_score($conn, $name, $score);
                    } else echo '{"result": {"message": "Score is not a highscore.", "code": 0}}';
                }
            }
        }
    } catch (Exception $e) {
        echo '{"error": {"text": "'.$e->getMessage().'"}}';
    }
});

$app->post('/api/tetris_scores/fire/add', function(Request $request, Response $response) {

    $score = $request->getParam('score');
    $max_size = 10;

    $path = "/scores/tetris";

    try {
        $fire = new fire();
        $conn = $fire->connect();
       	
        $data = $conn->get($path, Array('shallow' => 'true'));
        $data = json_decode($data, true);

        if(count($data) < $max_size) {
			$data = $request->getParsedBody();

            push_score_fire($conn, $path, $data);
        } else {
			$data = $conn->get($path, Array('orderBy' => '"score"', 'limitToFirst' => 1));
			$data = json_decode($data, true);

            $key = key($data);
            $high_score = $data[$key]['score'];
            if($score > $high_score) {
				$data = $request->getParsedBody();
    
                push_score_fire($conn, $path, $data);
            } else echo '{"result": {"message": "Score is not a highscore.", "code": 0}}';
        }
    } catch (ErrorException $e) {
        echo '{"error": {"text": $e->getMessage()}}';
    }
    
});

function push_score_fire($conn, $path, $data) {

    $data['date'] = date('Y-m-d');
    $data['ip-address'] = $_SERVER['REMOTE_ADDR'];
    $conn->push($path, $data); 
    echo '{"result": {"message": "Highscore added.", "code": 1}}';

}

function push_score($conn, $name, $score) {

    $sql = "insert into tetris_scores(name, score, date, `ip-address`) values(?, ?, ?, ?)";
    try {
        //update database with a prepared statement
        $push = $conn->prepare($sql);
        $push->bind_param("siss", $name, $score, date('Y-m-d'), $_SERVER['REMOTE_ADDR']);
        if($push->execute()) {
			//keep the table ordered 
			$conn->query("alter table tetris_scores order by score desc;");
			echo '{"result": {"message": "Highscore added.", "code": 1}}';
		} else {
			echo '{"error": {"text": "Fail to add highscore."}';
		}
        $push->free_result();
        $push->close();

    } catch (Exception $e) {
        echo '{"error": {"text": "'.$e->getMessage().'"}}';
    }
}

?>
