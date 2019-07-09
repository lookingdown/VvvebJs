<?php
//This script is used by template upload to copy template file to template folder and makes sure to set extension to html to be found.

define('UPLOAD_FOLDER', 'demo/templates/');
define('UPLOAD_PATH', '/');

$filename = $_POST['name'];

if(pathinfo($filename, PATHINFO_EXTENSION) != 'html')$filename = pathinfo($filename, PATHINFO_FILENAME).".html";
	
move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_FOLDER . $filename);

echo UPLOAD_PATH . UPLOAD_FOLDER . $filename;
