<?php
$sql = 'SHOW COLUMNS FROM `'.PREFIX.'brand-logo` WHERE FIELD = \'seo_title\'';
$dbRes = DB::query($sql);

if(!$arRes = DB::fetchArray($dbRes)){
 	$sqlArray['seo_title'] = 'ALTER TABLE `'.PREFIX.'brand-logo` 
    			ADD COLUMN `seo_title` text NOT NULL AFTER `sort`;';
    $sqlArray['seo_keywords'] = 'ALTER TABLE `'.PREFIX.'brand-logo` 
    			ADD COLUMN `seo_keywords` text NOT NULL AFTER `sort`';
    $sqlArray['seo_desc'] = 'ALTER TABLE `'.PREFIX.'brand-logo` 
    			ADD COLUMN `seo_desc` text NOT NULL AFTER `sort`;';
    foreach ($sqlArray as $sql) {
    	DB::query($sql);
    }
}

$newfile = 'brand.php';

copy(PAGE_DIR.$newfile, PAGE_DIR.'brand_copy.php');

$file = PLUGIN_DIR.'brand/brandviews.php';
copy($file, PAGE_DIR.$newfile);