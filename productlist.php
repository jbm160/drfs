<?php
// This is a template for a PHP scraper on morph.io (https://morph.io)
// including some code snippets below that you should find helpful
$local = 1;
$baseurl = "http://www.bulkreefsupply.com";
$o = fopen("./prodlist.csv", "w+");
if ($local) {
  require '../scraperwiki-php/scraperwiki.php';
  require '../scraperwiki-php/scraperwiki/simple_html_dom.php';
} else {
  require 'scraperwiki.php';
  require 'scraperwiki/simple_html_dom.php';
}

//
// // Read in a page
echo "Opening categories.csv for reading...\n";
if (($f = fopen("./categories.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($f)) !== FALSE) {
    getProducts($data[2],$data[1] . $data[0]);
  }
  fclose($f);
}
fclose($o);


// parse the categories and save to database
// database columns:
//   Category name
//   path
//   URL
//   Will need to use sort prodlist.csv | uniq to fix the file and remove duplicates
//   Prodtype 0 = simple, 1 = grouped or configurable
function getProducts($u,$cat){
  global $o;
  $d = new simple_html_dom();
  $d->load(scraperwiki::scrape($u));
//echo "Loaded URL: " . $u . "\n";
  $items = $d->find('li.grid-item');
  if (count($items) > 0) {
  	foreach ($items as $p) {
  		$prod = $p->find('p.product-name > a',0);
  		$prodname = trim($prod->innertext);
  		$prodURL = $prod->href;
  		if (!is_null($p->find('p.minimal-price',0))) {
  		  $prodtype = 1;
  		} else {
  		  $prodtype = 0;
  		}
  		fputcsv($o,array($prodname, $prodtype, $cat, $prodURL));
echo $prodname . "\n";
  	}
  	if (!is_null($d->find('p.next',0))) {
  		getProducts($d->find('p.next',0)->href,$cat);
  	}
  }
}

?>
