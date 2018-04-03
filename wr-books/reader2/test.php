<?php

//require_once('./BookGluttonEpub.php');
//require_once('./BookGluttonZipEpub.php');
require_once('./ePubServer.php');



$file = './comparison-epub.epub';//'./H. G. Wells - The War of the Worlds.epub';


/*echo "Opening $file as OPS in temp dir:\n";

$epub = new BookGluttonEpub();
$epub->setLogVerbose(true);
$epub->setLogLevel(2);
$epub->open($file);
print_r($epub->getMetaPairs());


echo "Now opening $file as virtual zip (no filesystem on disk):\n";

$epub = new BookGluttonZipEpub();
$epub->enableLogging();
$epub->loadZip($file);
print_r($epub->getMetaPairs());

echo "There are ".$epub->getFlatNav()->length." navPoints here.\n";
echo "NCX:\n";
foreach($epub->getFlatNav() as $np) {
  echo $np->nodeValue."\n";
  $arr = $epub->navElToArray($np);
  echo "src: " . $arr['src']."\n";
}

echo "\n\nGetNavPoints"."\n";
foreach($epub->getNavPoints() as $point) {
  //echo $np->nodeValue."\n";
  $parts = parse_url($point['src']);
	$partial_path = $parts['path'];
  //$partial_fragment = $parts['fragment'];
  echo "\npath: ".$partial_path."\n";//.$partial_fragment;
}
echo "\n\nGet CURRENT TEST XML"."\n";

//foreach($epub->getSpineItems() as $spineitem) {
//  echo $spineitem."\n";
//}

//$temp = new EpubAssetServer();
//$temp->serve($file, "about");
*/
$tmpEPUB = new ePubServer($file, "/reader2");
$tmpEPUB->displayChapter("acknowledgements");

echo "*** TOC ***"; 
$tmpEPUB->displayTableOfContents();

?>