<?php
//キャッシュ生成処理
//作成したキャッシュファイルの呼び出し
//キャッシュ生成ボタン
// 投稿・ページの更新時にキャッシュを更新する
// カテゴリの更新時にキャッシュを更新する
// タグの更新時にキャッシュを更新する
// POST処理

//キャッシュ生成処理
//引数を使えば、指定のキャッシュのみを更新することも可能。
function make_cache_wms($names="") {

	if($names == ""){
		$names = default_cache_list();
	}

	foreach($names as $key => $val){
		// 作成するファイル名の指定
		$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/'. $key .'.html';

		// ファイルの存在確認
		if( !file_exists($handle) ){
			// ファイル作成
			touch( $handle );
			// ファイルのパーティションの変更
			chmod( $handle, 0644 );
		}
		// ファイルが存在している場合実行
		if( file_exists($handle) ){
			$fp = fopen($handle, 'w');
			$content = $val;
			fwrite( $fp, $content);
			fclose ($fp);
		}
	}
}


//作成したキャッシュファイルの呼び出し
function call_cache_wms($filename) {

	$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/'. $filename .'.html';
	// ファイルが存在している場合実行
	if( file_exists($handle) ){
		$fp = fopen($handle, 'r');
		$content = fread( $fp, filesize($handle) ); // ファイルサイズ分読みこみ
		fclose ($fp);
		return $content;
	}else{
		return false;
	}

}

//キャッシュ生成ボタン
function control_form_wms(){

	$result = call_cache_wms('all-controls');//キャッシュデータを取得
	if($result==false){
		$result = control_form_cache_wms();
		$names = array(
						'all-controls' => control_form_cache_wms()
						);
		make_cache_wms($names);
	}

	return $result;
}

//Control HTMLキャッシュ作成
function control_form_cache_wms(){

	$names = default_control_list();

	foreach($names as $key => $array){
		$num++;
		$text = ucwords( str_replace('-', ' ', $key) );
		$list .= '<li><a href="#'. str_replace('-', '_', $key) .'_control">'. $num .'. '. $text .'</a></li>';
		$content .= $array;
	}

	$index = '<strong>INDEX:</strong><br><ul class="wmstabnavi">'. $list .'</ul>';
	$result = $index .'<div class="wmstabcontent">'. $content .'</div>';
	return $result;
}

// 投稿・ページの更新時にキャッシュを更新する
// カテゴリの更新時にキャッシュを更新する
// タグの更新時にキャッシュを更新する

function update_cache(){
	//all-postsのみ更新
	$names = array(
					'all-posts' => all_posts_cache_wms(),
					);
	make_cache_wms($names);
}

// 投稿・ページの更新直後と削除直後にキャッシュを更新する
add_action('save_post','update_cache');
add_action('deleted_post','update_cache');

// コメントが追加・更新・削除された直後にキャッシュを更新する
add_action('comment_post','update_cache');
add_action('edit_comment','update_cache');
add_action('deleted_comment','update_cache');

// カテゴリの作成直後と更新直後と削除直後にキャッシュを更新する
add_action('create_category','update_cache');
add_action('edit_category','update_cache');
add_action('delete_category','update_cache');

// タグの作成直後と更新直後と削除直後にキャッシュを更新する
add_action('create_post_tag','update_cache');
add_action('edit_post_tag','update_cache');
add_action('delete_post_tag','update_cache');

// POST処理
function post_make_cache_wms($array){

	if( isset($array) ){
		$req = $array['call'];
		$req = str_replace('_', '-', $req);
		$subject = $req;
		$pattern = '/^(.*)-control$/';
		preg_match($pattern, $subject, $match);
		$req = $match[1];
		save_csv_wms($req,$array);

		$names = default_cache_list(); //csvが書き換えられてから呼び出し
		$val = $names[$req];
		$order = array(
						$req => $val,
						'all-controls' => control_form_cache_wms()
						);
		make_cache_wms($order);
	}

}

function save_csv_wms($names,$array){
	//csvのキャッシュ作成処理。とりあえず、今回はcacheフォルダにキャッシュと同じ名前で作成。

	// 作成するファイル名の指定
	$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/'. $names .'.csv';

	// ファイルの存在確認
	if( !file_exists($handle) ){
		// ファイル作成
		touch( $handle );
		// ファイルのパーティションの変更
		chmod( $handle, 0644 );
	}
	// ファイルが存在している場合実行
	if( file_exists($handle) ){
		foreach($array as $keyname => $val){
			$subject = $keyname;
			$pattern = '/^val-g(\d+)-c(\d+)/';
			preg_match($pattern, $subject, $match);
			if(isset($match[1])) $content[$match[1]][] = $val;
		}

		$fp = fopen($handle, 'w');
		foreach ($content as $fields) {
			fputcsv($fp, $fields);
		}

		fclose ($fp);
	}

}

//control-list.csvの読みだし
function default_control_list(){
	$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/control-list.csv';
	if( !file_exists($handle) ){
		$handle = WP_PLUGIN_DIR.'/with-melty-support/admin/default/control-list.csv';
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


?>