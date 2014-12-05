<?php

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

use Slim\Slim;

function boiler_plate_code(){
}

//Initialize app
$app = new Slim(array(
    'debug' => true
));

//Specify your application name
$app->setName('mafia-game');


$app->get( '/data/all_players', function() use($app){
    
    $data = array();
    
    require "Game/game.php";
    $data["all"] = Security::get_valid_members();
    $resp = array();
    foreach( $data["all"] as $id ){
        $name = Members::get_name($id);
        $resp[] = $name;
    }
    $data["all"] = $resp;
    
    $view = "json.php";
    include "Game/boilerplate.php"; 
});

$app->get( '/data/active_players/:gameid', function($gameid) use($app){
    
    $data = array();
    
    
    require "Game/game.php";
        
    if(!Security::isValidGame($gameid)){
        $data = array("status"=>"error");
        $view = "json.php";
        include "Game/boilerplate.php"; 
        exit;
    }

    $g = new Game($gameid);
    $data["active"] = $g->get_alive_members();
    $resp = array();
    foreach( $data["active"] as $id ){
        $name = Members::get_name($id);
        $resp[] = $name;
    }
    $data["active"] = $resp;
    $data["status"]= "ok";
    
    $view = "json.php";
    include "Game/boilerplate.php"; 
});

$app->put( '/:userid/game/add', function($userid) use($app){
    
    $data = array();
    $players = $app->request()->put('players');
    $players = json_decode($players);
    
    require "Game/game.php";
    if(!Security::is_god($userid) /*|| count($players)<3*/ ){
        die("Unauthorized/Less than 3 players");
    }
    
    $g = new Game(time());
    $gameId = $g->init($players);
    $data = array("gameId"=>$gameId);
    
    $view = "json.php";
    include "Game/boilerplate.php"; 
});

$app->put( '/:userid/:gameid/game/remove/', function($userid, $gameid) use($app){
    
    $data = array();
    $player = $app->request()->put('player');
    
    require "Game/game.php";
    if(!Security::is_god($userid) || !Security::isValidGame($gameid) ){
        $data = array("status"=>"error");
        $view = "json.php";
        include "Game/boilerplate.php"; 
        exit;
    }
    
    $g = new Game($gameid);
    $g->kill_member($player);
    $data = array("status"=>"ok");
    
    $view = "json.php";
    include "Game/boilerplate.php"; 
});

$app->get( '/game/votingState/:gameid', function($gameid) use($app){
    $data = array();
    
    require "Game/game.php";
    
    if(!Security::isValidGame($gameid)){
        $data = array("status"=>"error");
        $view = "json.php";
        include "Game/boilerplate.php"; 
        exit;
    }

    $vo = array();
    $so = array();
    
    $g = new Game($gameid);
    $votes = $g->get_votes();
    foreach($votes as $voter=>$against){
        $voter = Members::get_name($voter);
        $against = Members::get_name($against);
        
        $vo[$voter]=$against;
        if(isset($so[$against])){
            $so[$against] = $so[$against]+1;
        }
        else{
            $so[$against] = 1;
        }
    }
    
    $data = array("status"=>"ok", "votes"=>$vo, "summary"=>$so);

    $view = "json.php";
    include "Game/boilerplate.php"; 
});

$app->put( '/:userid/game/initVoting/:gameid', function($userid, $gameid) use($app){
    
    
    require "Game/game.php";

    if(!Security::is_god($userid) || !Security::isValidGame($gameid)){
        $data = array("status"=>"error");
        $view = "json.php";
        include "Game/boilerplate.php"; 
        exit;
    }

    $g = new Game($gameid);
    $g->init_voting();
    
    $data = array("status"=>"ok");
    
    $view = "json.php";
    include "Game/boilerplate.php"; 
});

//Gets the game voting page
$app->get( '/:userid/:gameid', function($userid, $gameid) use($app){

    $data = array( "user_id"=> $userid, "game_id"=>$gameid);
    
    require "Game/game.php";
    if(Security::is_god($userid)){
        $app->redirect('/'.$userid.'/');
    }
    if(!Security::isValidMember($userid)){
        die("Invalid user");
    }
    if(!Security::isValidGame($gameid)){
        die("Invalid gameID");
    }
    
    $g = new Game($gameid);
    if( !$g->is_alive($userid,false) ){
        die("Sorry, You are either dead or not in current game.");
    }
    
    $data["active"] = $g->get_alive_members();
    $resp = array();
    foreach( $data["active"] as $id ){
        $name = Members::get_name($id);
        if($id != $userid )
            $resp[] = $name;
    }
    $data["active"]=$resp;

    $data["dead"] = $g->get_dead();
    $resp = array();
    foreach( $data["dead"] as $id ){
        $name = Members::get_name($id);
        $resp[]=$name;
    }
    $data["dead"]=$resp;

    $data["voted"] = $g->get_vote($userid);
    if($data["voted"])
        $data["voted"]= Members::get_name($data["voted"]);
        
    $view = "main.php";
    include "Game/boilerplate.php";
    
});

//Gets the game admin page
$app->get( '/:userid/', function($userid) use($app){

    $data = array( "user_id"=> $userid );
    
    require "Game/game.php";
    $data["all"] = Security::get_valid_members();
    $resp = array();
    foreach( $data["all"] as $id ){
        $name = Members::get_name($id);
        if($id!=$userid)
            $resp[] = $name;
    }
    $data["all"] = $resp;

    $view = "admin.php";
    include "Game/boilerplate.php";
    
});

//Gets the game voting page
$app->get( '/:userid/:gameid/vote/', function($userid, $gameid) use($app){
    $player =  $app->request()->get('player');

    require "Game/game.php";
    if(Security::is_god($userid) || !Security::isValidMember($userid)){
        $data = array("status"=>"error", "why"=>"n.a");
        $view = "json.php";
        include "Game/boilerplate.php"; 
        exit;
    }
    if(!Security::isValidGame($gameid)){
        $data = array("status"=>"error", "why"=>"Invalid game");
        $view = "json.php";
        include "Game/boilerplate.php"; 
        exit;
    }
    
    $g = new Game($gameid);
    if( !$g->is_alive($userid,false) || !$g->is_alive($player) ){
        $data = array("status"=>"error", "why"=>"You/Opponent is dead.");
        $view = "json.php";
        include "Game/boilerplate.php"; 
        exit;
    }

    if( $g->add_vote( $userid, Members::get_id($player) ) )
        $data = array("status"=>"ok");
    else
        $data = array("status"=>"error", "why"=>"Unknown");
    
    $view = "json.php";
    include "Game/boilerplate.php"; 
});

$app->run();

?>