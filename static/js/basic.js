var vp,cp,gid,me=0;

window.onload=function(){
 vp = $("#valid_players");
 cp = $("#chosen_players");
 gid = "";
}

function flash(data, autohide){
    if(!data){
        $("#msg").hide();
        return;
    }
    $("#msg").show();
    
    $("#msg").removeAttr("class");
    if( data.toLowerCase().indexOf("error")>-1 )
        $("#msg").addClass("error");
    else if( data.toLowerCase().indexOf("success")>-1 )
        $("#msg").addClass("success");
    else
        $("#msg").addClass("info");
    $("#msg").html(data);
    if(autohide)
        window.setTimeout(function(){ $("#msg").html(""); }, 2000);
}

    
function loading(){
    flash('<div class="sk-spinner sk-spinner-rotating-plane"></div>');
}

function voting_status(url, main){
    cp && cp.hide();
    var el = $("#vote_status_bar");
    el.html("");
    if(!gid){
        gid = prompt("Enter gameID").trim();
        if(!gid)
            return;
    }

    loading();

    $.get(url+gid, function(res){
        res = JSON.parse(res);
        if(res && res.status=="ok"){
            var table = res.votes;
            var summary = res.summary;
            vp.html("");
            $("#new_game").show();
            var exist =false;

            $("#action").attr("onclick","voting_status('"+url+"')").html("Refresh");
            for (i in summary){
                var source = [];
                for (record in table){
                    if( table[record].trim() == i.trim() ){
                        source.push(record);
                    }
                }
                if(main){
                    $(".player").each(function(){
                        if( i==me ){
                            exist = true;
                            $("#my_votes").html( "Votes against you:<span class=num>" + summary[i] + "</span>-" + "<span class=small>"+source.join(",")+"</span>" );
                        }
                        if($(this).html() == i){
                            exist = true;
                            $(this).html( $(this).html().split("-")[0] + "- <span class=num>" + summary[i] + "</span>-" + "<span class=small>"+source.join(",")+"</span>" );
                        }
                    });
                }
                else if(i && source.join(",")!=""){
                el.append( "<div class=vote-sum><span class=name>"+i+"</span><span class=num> "+ summary[i]+ " </span> <span class='against small'>"+ source.join(",") + "</span></div>" );
                }
            }
            flash("");
            if( !main && el.html() == "" )
                flash("No votes yet");
            if( main && !exist )
                flash("No votes yet");
            else
                flash("Here you go.");
        }
        else{
            flash("Error occured.");
            gid = 0;
        }
    });
}
