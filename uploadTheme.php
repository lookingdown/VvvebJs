<?php
//This script is used by theme upload to create theme folder and copy uploaded css file into it.

//trim and replace eventual empty spaces in name 
$uploadFolder = str_replace(' ', '_', trim($_POST['name']));
$uploadPath = 'css/dist/';

mkdir( $uploadPath.$uploadFolder, 0700 );

move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath.$uploadFolder. '/bootstrap.min.css');

echo $uploadFolder;
