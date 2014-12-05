<?php

require_once "Game/game.php";

if( !Security::is_god($model["user_id"]) ){
    ?>

    <div class="row">
        
        <div>
            <p>Paste GameID</p>
            <input type=text id=gameid autocomplete="on"> 
            <button onclick="go()">Enter</button>
        </div>

        <script>
            function go(){
                url = window.location.href;
                if(url[url.length-1]=="/")
                    url = url.substr(0,[url.length-1]);
                window.location = url + "/" + $("#gameid").val();
            }
        </script>
        
    </div>
    <?php
exit;
}

?>

<h2>GOD</h2>

<div id="msg-wrapper">
<div id="msg" class="info">Welcome God.</div>
</div>

<div class="interact">
    <button id="action-new-game">New Game</button>
    <button id="voting-start">Open Voting</button>
    <button id="voting-status" onclick="voting_status('game/votingState/')">Voting Status</button>
    <button id="remove-player">Active Players</button>

    <div id="new_game" class="row hidden">
        
        <div id="valid_players" class="col col-1-2">
        </div>
        <div id="chosen_players" class="col col-1-2"></div>
        <button class="clear center" id="action" onclick="create_game()">Start</button>
    </div>

    <div id="valid_players_bk" class="hidden">
    <?php
        foreach($model["all"] as $name){
            echo "<div class='player' onclick='move_to(this,1)'>".$name."</div>";
        }
    ?>
    </div>
    
    <div style="clear:both;margin:1px"></div>
    
</div>
    <br>

<div id=vote_status_bar class="clear center"></div>

<script>
    
    $("#voting-start").click(function(){
        cp.html("");
        cp.hide();
        if(!gid){
            gid = prompt("Enter gameID").trim();
            if(!gid)
                return;
        }
        if( gid ){
            loading();
            $.ajax({
                url: window.location.href+"/game/initVoting/" + gid,
                type: 'PUT',
                success: function(r){
                    r = JSON.parse(r);
                    if(  r && r.status=="ok" ){
                        flash("Voting open now.");
                    }
                    else{
                        flash("Error in opening voting");
                        gid = 0;
                    }
                }
            });
        }
    });
    
    function create_game(){
        if(!confirm("Sure to create new game?")){
            return;
        }
        if( cp.find(".player").length ){
            var pl = [];
            cp.find(".player").each(function(){
                pl.push($(this).html());
            });
            pl = JSON.stringify(pl);
            loading();
            $.ajax({
                url: window.location.href+"/game/add",
                type: 'PUT',
                success: function(data){
                    data = JSON.parse(data);  
                    data = data["gameId"];
                    $("#new_game").hide();
                    flash("Game created. Please copy and broadcast the game id <h2>"+data+"</h2>");
                    gid = data;
                },
                data: 'players='+pl,
                error: function(e){
                    gid = 0;
                }
                
            });
        }
    }

    function get_player_el(data, index, func){
        if(!func)
            func = "move_to";
        return "<div class='player' onclick='"+func+"(this,"+index+")'>"+data+"</div>"
    }
    
    function move_to(el, index){
        if(index)
            cp.append((get_player_el($(el).html(), 0)));
        else
            vp.append((get_player_el($(el).html(), 1)));
        $(el).remove();
    }
    
    $("#action-new-game").click(function(){
        flash("Click players on left to add them and click Start", false);
        cp.html("").show();
        vp.html($("#valid_players_bk").html());
        $("#new_game").show();
        vp.show();
        $("#action").show().attr("onclick","create_game()").html("Start");
    });
    
    $("#remove-player").click(function(){
        cp.html("");
        if(!gid){
            gid = prompt("Enter gameID").trim();
            if(!gid)
                return;
        }
        loading();
        $.get("data/active_players/"+gid, function(res){
            res = JSON.parse(res);
            if(res && res.status=="ok"){
                res = res.active;
                vp.html("");
                $("#new_game").show();
                $("#action").hide();
                for (name in res){
                    vp.append( (get_player_el(res[name], 1, "remove_player")) );
                    
                }
                flash("Click players to kill them", false);
            }
            else{
                flash("Error");
                gid = 0;
            }
        });
    });
    
    function remove_player(el, ex){
        
        if(!gid){
            gid = prompt("Enter gameID").trim();
            if(!gid)
                return;
        }

        if(confirm("Are you sure to kill him/her?")){
            loading();
            $.ajax({
                url: window.location.href + "/" + gid + "/game/remove/",
                type: 'PUT',
                data: "player="+$(el).html(),
                success: function(r){
                    r = JSON.parse(r);
                    if(  r.status=="ok" ){
                        flash($(el).html() + " is dead.");
                        $(el).remove();
                    }
                    else{
                        flash("Error occured.");
                    }
                },
                error:function(e){ gid = 0;}
            });

        }
    }
    
</script>

<style>
#valid_players:before{
/*    content: "All";*/
}
#chosen_players:before{
    content: "Selected";
}
#chosen_players .player{
    background: #0d7963;
}
.vote-sum{
    background: #555;
    padding:10px;
    color:#fff;
}
</style>