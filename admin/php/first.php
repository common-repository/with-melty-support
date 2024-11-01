<?php

//welcomeタブ、startボタン処理
//初期ページ処理 admin-post.php から呼出し
function start_post_wms(){

	//初期ファイルリスト
	$files = array(
					'tab-list.csv',
					'tab-list-post.csv',
					'cache-list.csv',
					'control-list.csv',
					'all-posts.csv',
					);

	$sourceDir  = WP_PLUGIN_DIR.'/with-melty-support/admin/default/';
	$destinationDir = WP_PLUGIN_DIR.'/with-melty-support/cache/';

	//cacheフォルダへコピー処理
	foreach($files as $filename){
		copy( $sourceDir.$filename, $destinationDir.$filename );
	}

	//データ作成後、キャッシュを作成 $destinationDir
	//Allpost
	make_cache_wms();

}

?>