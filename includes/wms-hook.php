<?php
//display js
function admin_display_wms() {

	$result .= "<style>#wms_box1 #tabnavi, #wms_box1 #tabcontent{";
	$result .= "filter:alpha(opacity=0);-moz-opacity:0;opacity:0;";
	$result .= "}</style>\n";

	echo $result;
}
add_action('admin_head', 'admin_display_wms');

//css,js import
function admin_meta_over_ride_wms() {

	if(call_cache_wms('meta-src')){
		$result = call_cache_wms('meta-src');
	}else{
		$result = make_meta_cache_wms();
	}

	$result .= "<style>#wms_box1 #tabnavi, #wms_box1 #tabcontent{";
	$result .= "filter:alpha(opacity=1);-moz-opacity:1;opacity:1;";
	$result .= "}</style>\n";

	echo $result;
}
add_action('admin_footer', 'admin_meta_over_ride_wms');

//css,js import src
function make_meta_cache_wms() {
	$css1 = glob(WP_PLUGIN_DIR.'/with-melty-support/includes/*.css');
	$css2 = glob(WP_PLUGIN_DIR.'/with-melty-support/includes/*/*.css');
	$js1 = glob(WP_PLUGIN_DIR.'/with-melty-support/includes/*.js');
	$js2 = glob(WP_PLUGIN_DIR.'/with-melty-support/includes/*/*.js');

	$css = array_merge($css1, $css2);
	$js = array_merge($js1, $js2);

	foreach($css as $file){
		$url = str_replace(WP_PLUGIN_DIR, WP_PLUGIN_URL ,$file);
		$result .= '<link rel="stylesheet" type="text/css" href="'. $url .'">'."\n";
	}
	foreach($js as $file){
		$url = str_replace(WP_PLUGIN_DIR, WP_PLUGIN_URL ,$file);
		$result .= '<script type="text/javascript" src="'. $url .'"></script>'."\n";
	}

	$names['meta-src'] = $result;
	make_cache_wms($names);

	return $result;
}

//ajax hook. post from js file.
require_once (WP_PLUGIN_DIR.'/with-melty-support/admin/php/admin-post.php');

?>