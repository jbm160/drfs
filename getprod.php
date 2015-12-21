<?php
// This is a template for a PHP scraper on morph.io (https://morph.io)
// including some code snippets below that you should find helpful
$local = 1;
$baseurl = "http://www.woodcraft.com";
$o = fopen("./detprodlist.csv", "w+");
$r = fopen("./reviews.csv", "w+");
$i = fopen("./images.csv", "w+");
$e = fopen("./errors.csv", "a+");
$revs = array();
fputcsv($i,array("Original Image URL","New Image URL"));
$data = array(
  "sku",
  "_store",
  "_attribute_set",
  "_type",
  "_category",
  "_root_category",
  "_product_websites",
  "created_at",
  "description",
  "gift_wrapping",
  "has_options",
  "image",
  "manufacturer",
  "meta_keyword",
  "msrp_display_actual_price_type",
  "msrp_enabled",
  "name",
  "options_container",
  "page_layout",
  "price",
  "required_options",
  "short_description",
  "small_image",
  "status",
  "tax_class_id",
  "thumbnail",
  "updated_at",
  "url_key",
  "url_path",
  "visibility",
  "weight",
  "qty",
  "min_qty",
  "use_config_min_qty",
  "is_qty_decimal",
  "backorders",
  "use_config_backorders",
  "min_sale_qty",
  "use_config_min_sale_qty",
  "max_sale_qty",
  "use_config_max_sale_qty",
  "is_in_stock",
  "use_config_notify_stock_qty",
  "manage_stock",
  "use_config_manage_stock",
  "stock_status_changed_auto",
  "use_config_qty_increments",
  "qty_increments",
  "use_config_enable_qty_inc",
  "enable_qty_increments",
  "is_decimal_divided",
  "_media_attribute_id",
  "_media_image",
  "_media_lable",
  "_media_position",
  "_media_is_disabled",
  "grouped_skus"
  );
fputcsv($o,$data);
$data = array(
  "sku",
  "review_title",
  "rating",
  "reviewer",
  "reviewer_loc",
  "review_date",
  "review_detail");
fputcsv($r,$data);

if ($local) {
  require '../scraperwiki-php/scraperwiki.php';
  require '../scraperwiki-php/scraperwiki/simple_html_dom.php';
} else {
  require 'scraperwiki.php';
  require 'scraperwiki/simple_html_dom.php';
}

//
// // Read in a page
echo "Opening prodlist.csv for reading...\n";
if (($f = fopen("./prodlist.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($f)) !== FALSE) {
echo "Retrieving " . $data[0] . "\n";
    $produrl = $data[3];
    $prodtype = $data[1];
    $prodcat = $data[2];
    if (!getProduct($produrl,$prodtype,$prodcat)) {
      fputcsv($e,$data);
    }
  }
  fclose($f);
}
fclose($o);
fclose($r);
fclose($i);
fclose($e);


// parse the categories and save to database
/** database columns:
sku
_store
_attribute_set
_type
_category
_root_category = "Home"
_product_websites = "base"
created_at = "12/12/15 22:48"
description = d$->find('div[id=prospecsbox]',0)->outertext . d$->find('div[id=ctl00_cphContent_divShippingBilling]',0)->outertext
gift_wrapping = "No"
has_options = 0
image = 
manufacturer = trim(d$->find('span[id=ctl00_cphContent_hidebrandid]',0)->first_child()->innertext)
meta_keyword = ""
msrp_display_actual_price_type = "Use config"
msrp_enabled = "Use config"
name = trim(d$->find('div[id=productname]',0)->first_child()->innertext)
options_container = "Product Info Column"
page_layout = "1 column"
price = trim(d$->find('div[id=proprice]',0)->first_child()->innertext,"$ ")
required_options = 0
short_description = ""
small_image = 
status = 1
tax_class_id = 2
thumbnail
updated_at
url_key = 
url_path
visibility
weight = 1.0000
qty = 0
min_qty = 1
use_config_min_qty = 1
is_qty_decimal = 0
backorders = 0
use_config_backorders = 1
min_sale_qty = 1
use_config_min_sale_qty = 1
max_sale_qty = 0
use_config_max_sale_qty = 1
is_in_stock = 1
use_config_notify_stock_qty = 1
manage_stock = 0
use_config_manage_stock = 1
stock_status_changed_auto = 0
use_config_qty_increments = 1
qty_increments = 0
use_config_enable_qty_inc = 1
enable_qty_increments = 0
is_decimal_divided = 0
_media_attribute_id = 88
_media_image
_media_lable
_media_position
_media_is_disabled

*/
//   sku
//   path
//   URL
//   
function getProduct($u,$type,$cat){
  global $baseurl, $o, $r, $i, $e, $local, $revs;
  $d = new simple_html_dom();
  $d->load(scraperwiki::scrape($u));
//echo "Loaded URL: " . $u . "\n";
  if (strpos($d->find('div[typeof=Product]',0)->class,"grouped") !== FALSE) {
//  if (!is_null($d->find('div.grouped[typeof=Product]',0))) {
    return(getProductMult($d,$type,$cat));
  }
  $prodname = $d->find('div[itemprop=name]',0)->firstChild()->innertext;
//echo "Line 181: " . $prodname . "\n";
  $imgfileurlcache = $d->find('a.product-image[rel=gal1]',0)->href;
  $im = explode("/",strstr($imgfileurlcache,"media/"));
  $imgfileurl = strstr($imgfileurlcache,"media/",true) . implode("/",array($im[0],$im[1],$im[2],$im[7],$im[8],$im[9]));
  $imgfile = $im[9];
  $img = implode("/",array($im[7],$im[8],$im[9]));
  fputcsv($i,array($imgfileurl,$img, $imgfileurlcache));
  if (!is_null($d->find('div[itemprop=description]',0))) {
    $shortdesc = trim($d->find('div[itemprop=description]',0)->innertext);
  } else {
    $shortdesc = "";
  }
  if (!is_null($d->find('#tab-full-details',0))) {
    $description = trim($d->find('#tab-full-details',0)->innertext);
  } else {
    $description = "";
  }
  $brand = "";
  $prodsku = $d->find('meta[itemprop=sku]',0)->content;
  $data = array(
    $prodsku,
    "",
    "Default",
    "simple",
    $cat,
    "Home",
    "base",
    "12/12/15 22:48",
    $description,
    "No",
    0,
    $img,
    $brand,
    "",
    "Use config",
    "Use config",
    $prodname,
    "Product Info Column",
    "1 column",
    $d->find('meta[itemprop=price]',0)->content,
    0,
    $shortdesc,
    $img,
    1,
    2,
    $img,
    "12/12/15 22:48",
    "",
    "",
    4,
    1.0000,
    0,
    1,
    1,
    0,
    0,
    1,
    1,
    1,
    0,
    1,
    1,
    1,
    0,
    1,
    0,
    1,
    0,
    1,
    0,
    0,
    88,
    $img,
    $d->find('a.product-image[rel=gal1]',0)->title,
    1,
    0,
    ""
  );
  fputcsv($o,$data);
  getImages($d);
  $revs[$prodsku] = 0;
  getReviews($d,$prodsku);
  echo "Saved " . $prodsku . ": " . $prodname . ", simple product.\n";
  return 1;
}

function getProductMult($d,$type,$cat){
  global $i, $o, $revs;
  $imgfileurlcache = $d->find('a.product-image[rel=gal1]',0)->href;
  $imgtitle = $d->find('a.product-image[rel=gal1]',0)->title;
  $im = explode("/",strstr($imgfileurlcache,"media/"));
  $imgfileurl = strstr($imgfileurlcache,"media/",true) . implode("/",array($im[0],$im[1],$im[2],$im[7],$im[8],$im[9]));
  $imgfile = $im[9];
  $img = implode("/",array($im[7],$im[8],$im[9]));
  fputcsv($i,array($imgfileurl,$img, $imgfileurlcache));
  if (!is_null($d->find('div[itemprop=description]',0))) {
    $shortdesc = trim($d->find('div[itemprop=description]',0)->innertext);
  } else {
    $shortdesc = "";
  }
  if (!is_null($d->find('#tab-full-details',0))) {
    $description = trim($d->find('#tab-full-details',0)->innertext);
  } else {
    $description = "";
  }
  $brand = "";
  if (count($d->find('table.grouped-items-table > tbody > tr.item')) < 2) {
    $prodsku = trim($d->find('table.grouped-items-table > tbody > tr.item span.sku',0)->innertext,"SKU: ");
    $prodname = $d->find('table.grouped-items-table > tbody > tr.item div.product-name',0)->innertext;
    $prodprice = trim($d->find('table.grouped-items-table > tbody > tr.item span.price',0)->innertext,"$ ");
    $prodvis = 4;
    $groupedskus = "";
    $prodtype = "simple";
    getGroupedSku($prodsku,$prodtype,$cat,$description,$img,$brand,$prodname,$prodprice,$shortdesc,$prodvis,$imgtitle,$groupedskus);
  } else {
    $groupskus = array();
    foreach ($d->find('table.grouped-items-table > tbody > tr.item') as $item) {
      if (!is_null($item->find('span.sku',0))) {
        $prodsku = trim($item->find('span.sku',0)->innertext,"SKU: ");
        $prodname = $item->find('div.product-name',0)->innertext;
        $prodprice = trim($item->find('span.price',0)->innertext,"$ ");
        $prodvis = 1;
        $groupedskus = "";
        $prodtype = "simple";
        $groupskus[] = $prodsku;
        getGroupedSku($prodsku,$prodtype,$cat,$description,$img,$brand,$prodname,$prodprice,$shortdesc,$prodvis,$imgtitle,$groupedskus);
      }
    }
    $prodsku = $groupskus[0] . "g";
    $prodname = $d->find('div[itemprop=name]',0)->firstChild()->innertext;
    $prodprice = "";
    $prodvis = 4;
    $groupedskus = implode(",",$groupskus);
    $prodtype = "grouped";
    getGroupedSku($prodsku,$prodtype,$cat,$description,$img,$brand,$prodname,$prodprice,$shortdesc,$prodvis,$imgtitle,$groupedskus);
  }
  getImages($d);
  $revs[$prodsku] = 0;
  getReviews($d,$prodsku);
  return 1;
}

function getGroupedSku($prodsku,$prodtype,$cat,$description,$img,$brand,$prodname,$prodprice,$shortdesc,$prodvis,$imgtitle,$groupedskus) {
  global $o;
  $data = array(
    $prodsku,
    "",
    "Default",
    $prodtype,
    $cat,
    "Home",
    "base",
    "12/12/15 22:48",
    $description,
    "No",
    0,
    $img,
    $brand,
    "",
    "Use config",
    "Use config",
    $prodname,
    "Product Info Column",
    "1 column",
    $prodprice,
    0,
    $shortdesc,
    $img,
    1,
    2,
    $img,
    "12/12/15 22:48",
    "",
    "",
    $prodvis,
    1.0000,
    0,
    1,
    1,
    0,
    0,
    1,
    1,
    1,
    0,
    1,
    1,
    1,
    0,
    1,
    0,
    1,
    0,
    1,
    0,
    0,
    88,
    $img,
    $imgtitle,
    1,
    0,
    $groupedskus
  );
  fputcsv($o,$data);
  echo "Saved " . $prodsku . ": " . $prodname . ", " . $prodtype . " product.\n";
  return 1;
}

function getImages($d) {
//echo "getImages: " . $d->find('div[itemprop=name]',0)->firstChild()->innertext . "\n";
  global $i,$o;
  $thumbs = $d->find('a.product-image[rel=gal1]');
  if (count($thumbs) > 1) {
    for ($x = 1; $x <= (count($thumbs) - 1); $x++) {
      $imgfileurlcache = $thumbs[$x]->href;
      $im = explode("/",strstr($imgfileurlcache,"media/"));
      $imgfileurl = strstr($imgfileurlcache,"media/",true) . implode("/",array($im[0],$im[1],$im[2],$im[7],$im[8],$im[9]));
      $imgfile = $im[9];
      $img = implode("/",array($im[7],$im[8],$im[9]));
      fputcsv($i,array($imgfileurl,$img, $imgfileurlcache));
      $data = array(
        "","","","","","","","","","",
        "","","","","","","","","","",
        "","","","","","","","","","",
        "","","","","","","","","","",
        "","","","","","","","","","",
        "","88",
        $img,
        $thumbs[$x]->title,
        ($x + 1),
        0,
        ""
        );
      fputcsv($o,$data);
    }
  }
}

function getReviews($d,$sku) {
//echo "getReviews: " . $sku . "\n";
  global $r,$revs;
  $reviews = $d->find('#product-reviews-list > li.review');
  if (count($reviews) > 0) {
    $revs[$sku] += count($reviews);
    foreach ($reviews as $rev) {
      $data = array(
        $sku,
        trim($rev->find('span[property=name]',0)->innertext),
        round(($rev->find('meta[property=ratingValue]',0)->content)/20,1),
        trim($rev->find('span[property=author]',0)->innertext),
        "",
        trim($rev->find('meta[property=datePublished]',0)->content,"on "),
        trim($rev->find('div.review-text',0)->innertext)
      );
      fputcsv($r,$data);
    }
  }
  if (!is_null($d->find('a.i-next',0))) {
    $newurl = $d->find('a.i-next',0)->href;
//echo "Another page of reviews found: " . $newurl . "\n";
    $d->load(scraperwiki::scrape($newurl));
    getReviews($d,$sku);
  } else {
    echo "Saved " . $revs[$sku] . " reviews for sku: " . $sku . ".\n";
  }
}

?>
