<?php

       if(isset($_POST['action'])){
         if($_POST['action'] != 'copy'){
            copy('css/dist/'.$_POST['action'].'/bootstrap.min.css', 'css/bootstrap.min.css');          
         }
		 file_put_contents("curtheme.php", "<?php \$themes='".$_POST['action']."';");
		 echo "true";
       }