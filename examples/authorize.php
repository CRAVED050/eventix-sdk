<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Janyk\Eventix\Client as EventixClient;

$eventix = new EventixClient(getenv('EVENTIX_CLIENT_ID'), getenv('EVENTIX_CLIENT_SECRET'), getenv('EVENTIX_REDIRECT_URI'));
if(! isset($_GET['code'])) {
    header("Location: " . $eventix->redirect());
    exit();
}

$eventix->authorize($_GET['code']);
var_dump($eventix);
