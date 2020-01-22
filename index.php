<?php

header('Content-Type: application/json');

require_once('config.php');
require_once('classes/rest.php');
require_once('classes/user.php');

$server = new Rest();
$server->handleRequest($_REQUEST, $_SERVER);