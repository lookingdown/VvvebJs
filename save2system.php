<?php
include_once '../../inc/dbi.php';
define('MAX_FILE_LIMIT', 1024 * 1024 * 2);//2 Megabytes max html file size

//Required not empty
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
	$before = new DOMText("PHPSTART\n include_once 'inc/toper.php';\n include_once 'inc/timeelapsed.php'; \nPHPSTOP\nDOCTYPE \nHTMLSTART \nPHPSTART\n require_once 'inc/head.php'; \nPHPSTOP\n BODYSTART \nPHPSTART\n include_once 'inc/header.php';\nPHPSTOP");
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
		$mock = new DOMDocument; 
		$d->loadHTML($remove);
		$body = $d->getElementsByTagName('body')->item(0);
	foreach ($body->childNodes as $child){
			$mock->appendChild($mock->importNode($child, true));
		}

		$mock->appendChild($after);
		$mock->insertBefore($before, $mock->firstChild);
		$fileName = sanitizeFileName($_POST['fileName']);

		$searchVal = array("PHPSTART", "PHPSTOP", "<!--?php", "?-->", "DOCTYPE", "HTMLSTART", "BODYSTART", "BODYSTOP", "HTMLSTOP", "EDITABLE_CONTENT");   
		  
		// Array containing replace string from  search string 
		$replaceVal = array("<?php", "?>", "<?php", "?>", "<!DOCTYPE html>", "<html>", "<body>", "</body>", "</html>",""); 

		$theFileName = strtok(preg_replace('/\s+/', '_', $newFileName),  '.').".php";
        
		//Adjust the query to your database table
		$sqlf = "INSERT INTO pages (name, author_id, date, status) VALUES (?,?,UNIX_TIMESTAMP(),?) ON DUPLICATE KEY UPDATE  author_id= ?, date=UNIX_TIMESTAMP(), status= ? ";
		$stmt= $pdo->prepare($sqlf);
		$stmt->execute([$theFileName, $user_id, 'draft', $user_id, 'newcopy']);

	if (file_put_contents($theFileName, str_replace($searchVal, $replaceVal, $mock->saveHTML()))) 
		echo $theFileName;
	else 
		echo 'Error saving file '  . $fileName;
