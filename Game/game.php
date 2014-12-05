<?php


class Game{
    
    public $data_dir = "data/";
    public $game_file = "";
    private $gid = 0;
    
    public function Game($game_id){
        $this->gid = $game_id;
        $this->game_file = $this->data_dir.$game_id;
    }
    
    public function init($players){
        if( !file_exists($this->game_file) ){
            $voted = array();
            if(!$players)
                $players = array();
            else
                foreach($players as $i=>$p){
                    $players[$i] = Members::get_id($p);
                    $voted[$p] = "";
                }
                        
            FileHandler::put_json_data($this->game_file, 
                array("players"=>$players, "alive"=>$players, "meta"=>array("last_vote"=>time(), "dead"=>array()), "voted"=>$voted) 
            );
        }
        return $this->gid;
    }
    
    public function init_voting(){
        $data = FileHandler::get_json_data($this->game_file);
        $players = $this->get_alive_members();
        $voted = array();
        foreach($players as $p)
            $voted[$p] = "";
        $data->voted = $voted;
        $data->meta->last_vote = time();
        FileHandler::put_json_data($this->game_file, $data);
    }
    
    public function add_vote($member_id, $against){
        $data = FileHandler::get_json_data($this->game_file);

        $found = false;
        foreach($data->voted as $k=>$v){
            if($k == $member_id){
                $data->voted->$k = $against;
                break;
                $found = true;
            }
        }
        if(!$found)
            $data->voted->$member_id=$against;
        
        FileHandler::put_json_data($this->game_file, $data);   
        return true;
    }
     
    public function get_votes(){
        $data = FileHandler::get_json_data($this->game_file);
        return $data->voted;
    }
    
    public function get_vote($member_id){
        $votes = $this->get_votes();
        if(isset($votes->$member_id))
            return $votes->$member_id;
    }
    
    public function get_alive_members(){
        $data = FileHandler::get_json_data($this->game_file);
        return $data->alive;
    }
    
    public function get_dead(){
        $data = FileHandler::get_json_data($this->game_file);
        return $data->meta->dead;
    }

    public function is_alive($member_id, $is_name=true){
        if($is_name){
            $member_id = Members::get_id($member_id);
        }
        return (in_array($member_id, $this->get_alive_members())===true);
    }
    
    public function kill_member($member_id){
        $member_id = Members::get_id($member_id);
        $data = FileHandler::get_json_data($this->game_file);
        foreach($data->alive as $i=>$v){
            if($v==$member_id){
                unset($data->alive[$i]);
                $data->meta->dead[] = $member_id;
            }
        }
        FileHandler::put_json_data($this->game_file, $data);        
    }
    
    public function add_member($member_id){
        if( Security::isValidMember($member_id) ){
            $members = $this->get_all_members();
            if( !in_array($member_id, $members) ){
                $members[] = $member_id;
                $data = FileHandler::get_json_data($this->game_file);
                if($data && $data["players"]){
                    $data["players"] = $members;
                }
                FileHandler::put_json_data($this->game_file, $data);
            }
            return $members;
        }
    }
    
    public function get_all_members(){
        $data = FileHandler::get_json_data($this->game_file);
        if($data)
            return $data["players"];
        else
            return array();
    }

}

class FileHandler{
    
    public static function get_data($filename){
        return trim(file_get_contents($filename));
    }
    
    public static function get_json_data($filename){
        $data = self::get_data($filename);
        if($data && strlen($data)){
            return json_decode($data);
        }
        return "";
    }
    
    public static function put_json_data($filename, $arr){
        $arr = json_encode($arr);
        self::put_data($filename, $arr);
    }
    
    public static function put_data($f, $d){
        file_put_contents( $f, $d );
    }

}

class Members{

    public static function get_id($name){
        return str_rot13($name);
    }
    
    public static function get_name($id){
        return str_rot13($id);
    }
    
}

class Security{
    
    public static $valid_player_file = "data/valid";
    public static $god_file = "data/god";
    
    public static function isValidMember($member_id){
        $valid = FileHandler::get_data(self::$valid_player_file);
        if($valid){
            $valid = explode(",",$valid);
            if( in_array($member_id, $valid) ){
                return true;
            }
        }
        return false;
    }

    public static function isValidGame($game_id){
        return file_exists("data/".$game_id);
    }
    
    public static function is_god($member_id){
        if( Filehandler::get_data(self::$god_file) == trim($member_id) ){
            return true;
        }
        return false;
    }
               
    public static function addValidMember($member_id){
        $valid = FileHandler::get_data(self::$valid_player_file);
        if($valid){
            $valid = explode(",", $valid);
            if( !in_array($member_id, $valid) ){
                $valid[]=$member_id;
            }
        }
        else{
            $valid = array($member_id);
        }
        FileHandler::put_data( self::$valid_player_file, implode(",", $valid) );
    }

    public static function get_valid_members(){
        return explode(",", FileHandler::get_data(self::$valid_player_file));
    }
    
    public static function removeValidMember($member_name){
    }

}

?>