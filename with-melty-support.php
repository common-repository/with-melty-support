<?php
/*
Plugin Name: With Melty Support
Plugin URI: http://wordpress.org/plugins/with-melty-support/
Description: Support WordPress and You. Make list of All posts, Support to use Custom fields.
Author: f-reach
Version: 1.0.1
Author URI: http://wmsymphony.web.fc2.com/
*/



/*
 * includesフォルダにあるphpファイルをすべて読み込む
 */

$dir1 = glob(WP_PLUGIN_DIR.'/with-melty-support/includes/*.php');
$dir2 = glob(WP_PLUGIN_DIR.'/with-melty-support/includes/*/*.php');
$all = array_merge($dir1, $dir2);
foreach($all as $file){
	require_once $file;
}

?>