<?php
    $css_groups = "reset,basic,utils";
    $js_groups = "jquery,basic";
?>

<?php 
    $model = $this->data["data"]; 
?>

<?php
    if(!isset($model["resources"])){
        $model["resources"]=array();
    }
    foreach( $model["resources"] as $key=>$data ){
        if($key == "js" ){
            $data = str_replace(".js", "", $data);
            $js_groups = $js_groups.",".$data;
        }
        else if($key == "css" ){
            $data = str_replace(".css", "", $data);
            $css_groups = $css_groups.",".$data;
        }
    }
    $js_groups = array_unique( explode(",", $js_groups) ); 
    $css_groups = array_unique( explode(",", $css_groups) );

    unset($model["resources"]);
    $model["css_groups"] = $css_groups;
    $model["js_groups"] = $js_groups;
?>

<?php
    if( $meta["__md"] ){
        echo "<html><body><pre>";
        $model["css_groups"] = implode(".css,", $model["css_groups"]);
        $model["js_groups"] = implode(".js,", $model["js_groups"]);
        print_r($model);
        echo "</pre></body></html>";
        exit;
    }
?>

<?php
    if( $this->data['view']=="json.php" ){
        include "json.php";
        exit;
    }
?>

<?php
    include "header.php";
?>

<?php include $this->data['view']; ?>

<?php
    include "footer.php";
?>
