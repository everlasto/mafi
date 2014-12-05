<?php

    //$resources = array("js"=>"", "css"=>"");
    
    //$data["resources"]= $resources;
    $meta = array( "__md"=>$app->request()->params('__md') );
    
    $app->view()->setData(array("view"=>$view, "data"=>$data, "meta"=>$meta));
    $app->render('gateway.php');

?>