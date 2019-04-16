<?php
include_once '../../inc/dbi.php';
define('MAX_FILE_LIMIT', 1024 * 1024 * 2);//2 Megabytes max html file size

//Save content to database 0 save content in file 1

$content=1;

/*
//uncomment this if you dont have in a separate file and comment include above
$host     = '127.0.0.1';
$database = 'database';
$user     = 'user';
$pass     = 'password';
$charset  = 'utf8';

$dsn = "mysql:host=$host;dbname=$database;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
	PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false,
];
$pdo = new ExtendedPdo($dsn, $user, $pass, $opt);
*/

//Required not empty should be your from logged in user
$user_id='13';

	function sanitizeFileName($fileName)
	{
		//sanitize, remove double dot .. and remove get parameters if any
		$fileName = __DIR__ . '/' . preg_replace('@\?.*$@' , '', preg_replace('@\.{2,}@' , '', preg_replace('@[^\/\\a-zA-Z0-9\-\._]@', '', $fileName)));
		return $fileName;
	}

	$html = ""; 
	if (isset($_POST['startTemplateUrl']) && !empty($_POST['startTemplateUrl'])) 
		{
		$startTemplateUrl = sanitizeFileName($_POST['startTemplateUrl']);
		$html = file_get_contents($startTemplateUrl);
	} else if (isset($_POST['html']))
	{
		$html = substr($_POST['html'], 0, MAX_FILE_LIMIT);
		$newFileName = $_POST['fileName'];
	}


	// Prepend
	$dbcontent="";
	if($content==0)$dbcontent=" echo \$content;\n";
	
	$before = new DOMText("PHPSTART\n include_once 'inc/top.php';\nPHPSTOP\nDOCTYPE \nHTMLSTART \nPHPSTART\n require_once 'inc/head.php'; \nPHPSTOP\n BODYSTART \nPHPSTART\n include_once 'inc/header.php';\n".$dbcontent."PHPSTOP");
	// Append
	$after = new DOMText("\nPHPSTART \n include_once 'inc/footer.php'; \n include_once 'inc/last.php'; \nPHPSTOP\n BODYSTOP \nHTMLSTOP");



	function removeDomNodes($html, $xpathString)
	{
		$dom = new DOMDocument;
		$dom->loadHtml($html);

		$xpath = new DOMXPath($dom);
    while ($node = $xpath->query($xpathString)->item(0))
    {
        $node->parentNode->removeChild($node); 
    }
    return $dom->saveHTML();
	}

		$remove = removeDomNodes(removeDomNodes(removeDomNodes($html, '//footer'),'//script'),'//header');

		$d = new DOMDocument;
		$bodyContent = new DOMDocument;
		$emptyBody =  new DOMDocument;
		$d->loadHTML($remove);
		$body = $d->getElementsByTagName('body')->item(0);
		foreach ($body->childNodes as $child){
				$bodyContent->appendChild($bodyContent->importNode($child, true));
			}
		
    if($content==8)
    	{  
    		$bodyContent->appendChild($after);
    		$bodyContent->insertBefore($before, $bodyContent->firstChild);
    		//$fileName = sanitizeFileName($_POST['fileName']);
    		}

    		$searchVal = array("PHPSTART", "PHPSTOP", "<!--?php", "?-->", "DOCTYPE", "HTMLSTART", "BODYSTART", "BODYSTOP", "HTMLSTOP", "EDITABLE_CONTENT");   
    		  
    		// Array containing replace string from  search string 
    		$replaceVal = array("<?php", "?>", "<?php", "?>", "<!DOCTYPE html>", "<html>", "<body>", "</body>", "</html>",""); 
    	

		$theFileName = strtok(preg_replace('/\s+/', '_', $newFileName),  '.').".php";
        
		//Adjust the query to your database table
		if($content==1){
			$sqlf = "INSERT INTO pages (name, author_id, date, status) VALUES (?,?,UNIX_TIMESTAMP(),?) ON DUPLICATE KEY UPDATE  author_id= ?, date=UNIX_TIMESTAMP(), status= ? ";
			$stmt= $pdo->prepare($sqlf);
			$stmt->execute([$theFileName, $user_id, 'draft', $user_id, 'newcopy']);
		}else{
			$content = trim($bodyContent->saveHTML());	
			$sqlf = "INSERT INTO pages (name, author_id, content1, date, status) VALUES (?,?,?,UNIX_TIMESTAMP(),?) ON DUPLICATE KEY UPDATE  author_id= ?, content1=?, date=UNIX_TIMESTAMP(), status= ? ";
			$stmt= $pdo->prepare($sqlf);
			$stmt->execute([$theFileName, $user_id, $content, 'draft', $user_id, $content, 'newcopy']);
		}

		//prepare for write to file with header footer and includes
			

    if($content==1){
    	    $bodyContent->appendChild($after);
	    	$bodyContent->insertBefore($before, $bodyContent->firstChild);
		if (file_put_contents($theFileName, str_replace($searchVal, $replaceVal, $bodyContent->saveHTML()))) 
			echo $theFileName;
	   }elseif($content==0){
	   		$emptyBody->appendChild($after);
	    	$emptyBody->insertBefore($before, $emptyBody->firstChild);
	   	if (file_put_contents($theFileName, str_replace($searchVal, $replaceVal, $emptyBody->saveHTML()))) 
			echo $theFileName;
	   }
	else 
		echo 'Error saving file '  . $fileName;
