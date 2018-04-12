<?php

const DEFAULT_URL = 'https://test-809ff.firebaseio.com/';

class fire {
    public function connect() {
        return new \Firebase\FirebaseLib(DEFAULT_URL);
    }

}

?>
