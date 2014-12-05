<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<head>
    <title>Mafia</title>
       <?php
            foreach($model["css_groups"] as $css){
                if($css)
                    echo "<link rel=stylesheet href='http://localhost:8080/php-apps/mafi/static/css/".$css.".css'>";
            }
        ?>
       <?php
            foreach($model["js_groups"] as $js){
                if($js)
                    echo "<script src='http://localhost:8080/php-apps/mafi/static/js/".$js.".js'></script>";
            }
        ?>
</head>
<body>
    <div class="page-header">
        <h2>Mafia</h2>
    </div>
    
    <div class="page-content">
