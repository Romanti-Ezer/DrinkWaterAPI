<?php

// Database
define('DBHOST', 'localhost');
define('DBNAME', 'drinkwater');
define('DBUSER', 'root');
define('DBPASSWORD', '');

// Routes
define('ROUTES', array(
    array( 'requestMethod' => 'POST', 'path' => '/^users$/', 'class' => 'User', 'method' => 'store'),
    array( 'requestMethod' => 'POST', 'path' => '/^login$/', 'class' => 'User', 'method' => 'login'),
    array( 'requestMethod' => 'GET', 'path' => '/^users\/\d+$/', 'class' => 'User', 'method' => 'show'),
    array( 'requestMethod' => 'GET', 'path' => '/^users$/', 'class' => 'User', 'method' => 'index'),
    array( 'requestMethod' => 'PUT', 'path' => '/^users\/\d+$/', 'class' => 'User', 'method' => 'update'),
    array( 'requestMethod' => 'DELETE', 'path' => '/^users\/\d+$/', 'class' => 'User', 'method' => 'destroy'),
    array( 'requestMethod' => 'POST', 'path' => '/^users\/\d+\/drink$/', 'class' => 'User', 'method' => 'drink'),
));

// HTTP Responses
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_CONFLICT', 409);
define('HTTP_UNPROCESSABLE_ENTITY', 422); 
define('HTTP_INTERNAL_SERVER_ERROR', 500);