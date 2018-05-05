<?php

class db {

    private $dbhost = 'localhost';
    private $dbuser = 'username';
    private $dbpass = 'password';
    private $dbname = 'game_scores';

    public function connect() {
        $conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        if ($conn->connect_errno) {
            echo $conn->connect_error;
            exit();
        }

        return $conn;
    }

}

const DEFAULT_URL = 'https://test-809ff.firebaseio.com/';

class fire {
    public function connect() {
        return new \Firebase\FirebaseLib(DEFAULT_URL);
    }

}

class mongo {

    private $dbhost = 'mongodb://localhost:27017';
    private $dbuser = 'username';
    private $dbpass = 'password';
    private $dbname = 'game_scores';

    public function connect() {
        $client = new MongoDB\Client($this->dbhost);
        return $client;
    }

}

?>
