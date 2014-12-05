
<?php

    require_once "Game/game.php";
    $name = Members::get_name($model["user_id"]);

?>

<h2><?=$name?></h2>
<div id="msg-wrapper">
<div id="msg" class="info">
    <?php 
        if( $model["voted"] ) 
            echo "You have already voted!"; 
        else 
            echo "Click a player to vote against. ";
    ?>
</div>
</div>

<div class="interact">

    <div id="new_game">
        <div id="alive_players">
        <?php
            foreach($model["active"] as $name){
                if( $name == $model["voted"] )
                    echo "<div class='voted player' onclick='vote_to(this)'>".$name."</div>";
                else
                    echo "<div class='player' onclick='vote_to(this)'>".$name."</div>";
            }
        ?>
        </div>
        <div id="dead_players">
        <?php
            foreach($model["dead"] as $name){
                echo "<div class='player'>".$name."</div>";
            }
        ?>
        </div>
    </div>

    <button id="checkvote" onclick="voting_status('../game/votingState/',true);this.innerHTML='Refresh status';">
        Voting status
    </button>

    <div id="valid_players"></div>

    <br>
    
    <div id=vote_status_bar class="clear center"></div>
    
    <div id="my_votes"></div>
</div>

<script>
    var voted_for = '<?php echo $model["voted"]; ?>';
    function vote_to(el){
        var pl = $(el).html().trim();
        if(!confirm("Are you sure to vote against "+pl+"?")){
            return;
        }
        $.get(window.location.href + "/vote/?player="+pl, function(r){
            r = JSON.parse(r);
            if(r && r.status=="ok"){
                $(".player").removeClass("voted");
                $(el).addClass("voted");
                flash("Voted successfully");
                $("#checkvote").trigger("click");
            }
            else{
                if(r.status=="error")
                    flash("Error: "+r.why);
                else
                    flash("Error in posting vote");
            }
        });
    }
    window.onload=function(){
        gid = <?php echo $model["game_id"]; ?>;
        me = "<?php echo Members::get_name($model["user_id"]); ?>";
        vp = $("#valid_players");
        window.setTimeout(function(){window.location.reload();}, 120000 );
    }
</script>

    