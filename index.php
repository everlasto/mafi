<?php

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

use Slim\Slim;

//Initialize app
$app = new Slim(array(
    'debug' => true
));

//Specify your application name
$app->setName('mafia-game');

//Gets the documentation for usage
$app->get( '/', function(){
	echo "Welcome to mafia!";
});

$app->run();

?>