<?php
// This is a template for a PHP scraper on morph.io (https://morph.io)
// including some code snippets below that you should find helpful
$local = 1;
if ($local) {
  require '../scraperwiki-php/scraperwiki.php';
  require '../scraperwiki-php/scraperwiki/simple_html_dom.php';
} else {
  require 'scraperwiki.php';
  require 'scraperwiki/simple_html_dom.php';
}

//
// // Read in a page
$baseurl="http://www.drsfostersmith.com";
//$f = fopen("./categories.csv", "w+");
$p = fopen("./prodlist.csv", "w+");
$html = $baseurl . "/fish-supplies/pr/c/3578";
$d = new simple_html_dom();
$d->load(scraperwiki::scrape($html));
$e = new simple_html_dom();
$c = new simple_html_dom();
//echo $d->innertext;
$cats = array();
getCategories($d);
//foreach ($cats as $cat) {
//	fputcsv($f,$cat);
//}
//fclose($f);
fclose($p);

// parse the categories and save to database
// database columns:
//   Category name
//   path
//   URL
//   
function getCategories($d){
  global $baseurl, $f, $p, $local, $cats;
  $topcat = $d->find('#e1 > li');
  foreach ($topcat as $top) {
//echo $top->innertext . "\n";
  	$catname = $top->find('a > div',0)->innertext;
  	$caturl = $top->find('a',0)->href;
  	$catpath = "|";
  	$cats[] = array($catname,$catpath,$caturl);
//  	echo "Saved category /" . $catname . "\n";
  	getProducts($caturl, $catpath . $catname);
  	getChildren($caturl, $catpath . $catname);
  }
}

function getChildren($url,$path) {
	global $baseurl, $cats, $e;
	$e->load(scraperwiki::scrape($baseurl . $url));
	$children = $e->find('#subCats > li');
	for ($x = 1; $x < count($children); $x++) {
		$childname = $children[$x]->find('a > div',0)->innertext;
		$childpath = $path . "|";
		$childurl = $children[$x]->find('a',0)->href;
		$cats[] = array($childname,$childpath,$childurl);
//		echo "Saved category " . $childpath . $childname . "\n";
		getProducts($childurl, $childpath . $childname);
	}
}

function getProducts($url, $path) {
	global $p, $c, $baseurl;
	$c->load(scraperwiki::scrape($baseurl . $url));
echo "Looking for products: " . $baseurl . $url . "\n";
	$prods = $c->find('div.product2014item');
//$str = var_export($prods);
//$str2 = $c;
//echo "\$prods = " . $str . "\n";
//echo  "HTML: " . $str2 . "\n";
	if (count($prods) == 0) {
		echo "No products found at " . $url . "\n";
	} else {
		foreach ($prods as $prod) {
			if (strpos($prod->class,"product2014cattab") === FALSE) {
				$prodname = $prod->find('a.product_link > div',0)->innertext;
				$produrl = $prod->find('a',0)->href;
				fputcsv($p, array($prodname,$path,$produrl));
				"Saved product: " . $prodname . "\n";
			}
		}
		if (!is_null($c->find('div.pagnbtn',0))) {
			getProducts($c->find('div.pagnbtn > a',0)->href, $path);
		}
	}
}


// print_r($dom->find("table.list"));
//
// // Write out to the sqlite database using scraperwiki library
// scraperwiki::save_sqlite(array('name'), array('name' => 'susan', 'occupation' => 'software developer'));
//
// // An arbitrary query against the database
// scraperwiki::select("* from data where 'name'='peter'")

// You don't have to do things with the ScraperWiki library.
// You can use whatever libraries you want: https://morph.io/documentation/php
// All that matters is that your final data is written to an SQLite database
// called "data.sqlite" in the current working directory which has at least a table
// called "data".
?>
