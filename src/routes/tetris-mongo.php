<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/api/tetris_scores/mongo', function(Request $request, Response $response) {
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

$app->get('/api/tetris_scores/mongo/player/{name}', function(Request $request, Response $response) {
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

$app->post('/api/tetris_scores/mongo/add', function(Request $request, Response $response) {

    echo "hello";
    $score = $request->getParam('score');
    $max_size = 10;

    try {
        $mongo = new mongo();
        $client = $mongo->connect();

        $collection = $client->scores->tetris;

        $result = $collection->insertOne($request->getParsedBody());
        echo "Inserted with Object ID '{$result->getInsertedId()}'";
    } catch (ErrorException $e) {
    
    }
    /*
    $path = "/scores/tetris";

    try {
        $fire = new fire();
        $conn = $fire->connect();
       	
        $data = $conn->get($path, Array('shallow' => 'true'));
        $data = json_decode($data, true);

        if(count($data) < $max_size) {
			$data = $request->getParsedBody();
            push_score_fire($conn, $path, $data, false);
        } else {
			$data = $conn->get($path, Array('orderBy' => '"score"', 'limitToFirst' => 1));
			$data = json_decode($data, true);

            $key = key($data);
            $high_score = $data[$key]['score'];
            if($score > $high_score) {
				$data = $request->getParsedBody();
    
                push_score_fire($conn, $path."/".$key, $data, true);
            } else echo '{"result": {"message": "Score is not a highscore.", "code": 0}}';
        }
    } catch (ErrorException $e) {
        echo '{"error": {"text": $e->getMessage()}}';
    }
    */
});

function push_score_mongo($conn, $path, $data, $replace) {

    $data['date'] = date('Y-m-d');
    $data['ip-address'] = $_SERVER['REMOTE_ADDR'];
    if($replace) $conn->set($path, $data);
    else $conn->push($path, $data); 
    echo '{"result": {"message": "Highscore added.", "code": 1}}';

}
?>
