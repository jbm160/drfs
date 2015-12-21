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
$baseurl="http://www.bulkreefsupply.com";
$f = fopen("./categories.csv", "w+");
$html = $baseurl . "/index.php";
$d = new simple_html_dom();
$d->load(scraperwiki::scrape($html));
//echo $d->innertext;
$cats = array();
getCategories($d);
foreach ($cats as $cat) {
	fputcsv($f,$cat);
}
fclose($f);

// parse the categories and save to database
// database columns:
//   Category name
//   path
//   URL
//   
function getCategories($d){
  global $baseurl, $f, $local, $cats;
  $topcat = $d->find('ul[id=nav] > li.level0');
  foreach ($topcat as $top) {
//echo $top->innertext . "\n";
  	$catname = $top->find('span.cat-title-sub-menu',0)->innertext;
  	$caturl = $top->find('a',0)->href;
  	$cats[] = array($catname,"|",$caturl);
  	$l1 = $top->find('ul.level0 > li.level1');
  	foreach ($l1 as $lev1) {
  		$l1name = $lev1->find('a > span',0)->innertext;
  		$l1path = $catname . "|";
  		$l1url = $lev1->find('a',0)->href;
  		$cats[] = array($l1name,$l1path,$l1url);
  		if (strpos($lev1->class,"parent") !== FALSE) {
  			getChildren($lev1,$l1path . $l1name);
  		}
  	}
  }
}

function getChildren($d,$path) {
	global $cats;
	$children = $d->find('ul li');
	foreach ($children as $child) {
		$childname = $child->find('a > span',0)->innertext;
		$childpath = $path . "|";
		$childurl = $child->find('a',0)->href;
		$cats[] = array($childname,$childpath,$childurl);
		if (strpos($child->class,"parent") !== FALSE) {
			getChildren($child,$childpath . $childname);
		}
	}
}

/**
<li class="level0 nav-1 level-top first parent">
	<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems.html" class="level-top">
		<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/40x50/0d4ee7c69174471e919e78cde2d2af90/rodi.png">
		<span>RO/DI</span>
		<span class="arrow"></span>
	</a>
	<div class="sub-menu" style="display: none; width: 638px;">
		<div class="static-block-sub-menu">
			<span class="cat-title-sub-menu">RO/DI</span>
			<div class="cat-static-block-content"><a href="http://www.bulkreefsupply.com/brs-gift-card.html"><p style="color:#2aace3; text-decoration:none"><b>BRS e-Gift Cards</b></p></a></div>
		</div>
		<ul class="level0" style="display: none; width: 607px;">
			<li class="level1 nav-1-1 first parent" style="height: 217px;">
				<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-systems.html">
					<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/74x56/0d4ee7c69174471e919e78cde2d2af90/200409-brs-4-stagewatersaverplus-a_1.jpg">
					<span>Reverse Osmosis Systems</span>
					<span class="arrow"></span>
				</a>
				<ul class="level1">
					<li class="level2 nav-1-1-1 first">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-systems/value-systems.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/200409-brs-4-stagewatersaverplus-a_2.jpg">
							<span>Value Systems (Chlorine)</span>
							<span class="arrow"></span>
						</a>
					</li>
					<li class="level2 nav-1-1-2">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-systems/universal-systems.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/200410-brs-6-stageuniversal-a.jpg">
							<span>Universal Systems (Chlorine &amp; Chloramines)</span>
							<span class="arrow"></span>
						</a>
					</li>
					<li class="level2 nav-1-1-3 last">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-systems/drinking-water-systems.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/200418-brs-5-stagedrinking-a.jpg">
							<span>RO and Drinking Water Systems</span>
							<span class="arrow"></span>
						</a>
					</li>
				</ul>
			</li>
			<li class="level1 nav-1-2 parent" style="height: 217px;">
				<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-filters.html">
					<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/74x56/0d4ee7c69174471e919e78cde2d2af90/Reverse-Osmosis-Filters.png">
					<span>Reverse Osmosis Filters</span>
					<span class="arrow"></span>
				</a>
				<ul class="level1">
					<li class="level2 nav-1-2-4 first">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-filters/filter-kits.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/Reverse-Osmosis-Filter-Kits.png">
							<span>Filter Kits</span>
							<span class="arrow"></span>
						</a>
					</li>
					<li class="level2 nav-1-2-5">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-filters/carbon-blocks-sediment-filters.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/Reverse-Osmosis-Carbon-Blocks-Sediment-Filters.png">
							<span>Carbon Blocks &amp; Sediment Filters</span>
							<span class="arrow"></span>
						</a>
					</li>
					<li class="level2 nav-1-2-6">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-filters/di-resin.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/DI_Resin_1.png">
							<span>DI Resin</span>
							<span class="arrow"></span>
						</a>
					</li>
					<li class="level2 nav-1-2-7 last">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-filters/reverse-osmosis-membranes.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/Reverse_Ossmosis_Membrane_1.png">
							<span>Reverse Osmosis Membranes</span>
							<span class="arrow"></span>
						</a>
					</li>
				</ul>
			</li>
			<li class="level1 nav-1-3 parent" style="height: 217px;">
				<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-accessories.html">
					<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/74x56/0d4ee7c69174471e919e78cde2d2af90/Reverse_Osmosis_Accessories.png">
					<span>Reverse Osmosis Accessories</span>
					<span class="arrow"></span>
				</a>
				<ul class="level1">
					<li class="level2 nav-1-3-8 first last">
						<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-accessories/upgrade-kits.html">
							<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/49x49/0d4ee7c69174471e919e78cde2d2af90/images/catalog/product/placeholder/thumbnail.jpg">
							<span>Upgrade Kits</span>
							<span class="arrow"></span>
						</a>
					</li>
				</ul>
			</li>
			<li class="level1 nav-1-4" style="height: 217px;">
				<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/reverse-osmosis-fittings-valves.html">
					<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/74x56/0d4ee7c69174471e919e78cde2d2af90/206000-John-Guest-3-Way-Ball-Valve-1-4-inch-QC_1_2.jpg">
					<span>Reverse Osmosis Tubing, Fittings &amp; Valves</span>
					<span class="arrow"></span>
				</a>
			</li>
			<li class="level1 nav-1-5 last" style="height: 217px;">
				<a href="http://www.bulkreefsupply.com/bulk-reverse-osmosis-filters-systems/canisters-brackets-clips.html">
					<img class="category-thumbnail" src="http://media.cdn.bulkreefsupply.com/media/catalog/category/cache/1/thumbnail/74x56/0d4ee7c69174471e919e78cde2d2af90/Reverse_Osmosis_Canisters.png">
					<span>Canisters, Brackets &amp; Clips</span>
					<span class="arrow"></span>
				</a>
			</li>
		</ul>
	</div>
</li>
*/

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
