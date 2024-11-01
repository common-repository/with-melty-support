<?php
// 改行文字を別の文字に置換
// 初期ページ表示処理
// キャッシュ作成時のデフォルト
// タブの表示
// タクソノミースラッグからURLを取得

// 改行文字を別の文字に置換
function replace_newline_wms($val,$brtag="",$endmark=""){

		$newline = array(
					"\r\n", //"\r\n"を一番先に置換対象にしないと"\n"と"\r"の2回置換処理されてしまう。
					"\r",
					"\n"
					);
		$result = str_replace($newline, $brtag, $val);//改行文字があれば、$brtagに変換する。

		if($endmark){
			$result = rtrim($result,$endmark);//explodeで最後に余分な配列を作らないように末尾の区切り文字を削除。
		}

		return $result;
}

//初期ページ表示処理
//Info,Thanksで常用
//upgrededほか任意のページの表示に使用。
function default_page_wms($filename){

	$handle = WP_PLUGIN_DIR.'/with-melty-support/admin/default/'. $filename .'.php';
	// ファイルが存在している場合実行
	if( file_exists($handle) ){
		require_once($handle);
		$function_name = $filename.'_print_html';
		$content = $function_name();
		return $content;
	}

}

// キャッシュ作成時のデフォルト
function default_cache_list(){
	$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/cache-list.csv';
	if( !file_exists($handle) ){
		$handle = WP_PLUGIN_DIR.'/with-melty-support/admin/default/cache-list.csv';
	}
	$fp = fopen($handle, 'r');
	while ($array = fgetcsv( $fp )) {
		$array0 = array_shift($array);
		if (function_exists($array[0])) {
			$names[$array0] = $array[0]($array[1]); //array_shift後の要素[1]の名前の関数があれば、可変関数として代入し直す。[2]以降に引数を記述。
		}
	}
	fclose ($fp);
	return $names;
}

// タブの表示
function default_tab_list_wms(){

	$url  = $_SERVER['REQUEST_URI'];// 現在のページURLのサイトURL以降を取得
	if ( strstr($url,'/post-new.php') || strstr($url,'/post.php') ){
		//id名 => array( #tabnaviリンクテキスト, #tabcontent内容or関数呼び出し ), 
		$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/tab-list-post.csv';
	}else{
		//id名 => array( #tabnaviリンクテキスト, #tabcontent内容or関数呼び出し ), 
		$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/tab-list.csv';
	}

	if( !file_exists($handle) ){
		$handle = WP_PLUGIN_DIR.'/with-melty-support/admin/default/tab-list-first.csv';
		$upgrade = WP_PLUGIN_DIR.'/with-melty-support/cache/upgrade.txt';
		if( file_exists($upgrade) ) unlink($upgrade);
	}
	$fp = fopen($handle, 'r');
	while ($array = fgetcsv( $fp )) {
		$array0 = array_shift($array);
		$tabArray[$array0] = $array;
		if (function_exists($array[1])) {
			$tabArray[$array0][1] = $array[1]($array[2]); //array_shift後の要素[1]の名前の関数があれば、可変関数として代入し直す。[2]以降に引数を記述。
		}
	}
	fclose ($fp);
	return $tabArray;
}

// タクソノミースラッグからURLを取得
function get_tax_url_wms($slug){
	if( get_term_by( 'slug' , $slug , 'category' ) ){
		$cat = get_term_by( 'slug' , $slug , 'category' );
		$permalink = get_category_link($callID);
	}else if( get_term_by( 'slug' , $slug , 'post_tag' ) ){
		$tag = get_term_by( 'slug' , $slug , 'post_tag' );
		$permalink = get_tag_link($callID);
	}
	return $permalink;
}

?>