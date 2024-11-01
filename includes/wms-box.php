<?php
// With melty supportのボックスを生成
// 管理画面すべてにボックスを追加（ダッシュボード以外）
// 管理画面のダッシュボードにボックスを追加
// アップグレード時の処理
// check version, upgrade, downgrade

// With melty supportのボックスを生成
function add_wms_box1() {

	check_version_upgrade_wms();

	$tabArray = default_tab_list_wms();

	$lang = get_locale();
	if($lang == 'ja'){
		if( file_exists(WP_PLUGIN_DIR.'/with-melty-support/cache/tab-list-post.csv')
			&& file_exists(WP_PLUGIN_DIR.'/with-melty-support/cache/tab-list.csv')
			){
			$existsFlg = false;

			if( file_exists(WP_PLUGIN_DIR.'/with-melty-support/cache/favor.txt') ){

					$filename = 'favor';
					$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/'. $filename .'.txt';
					$start = date("Y-m-d", mktime(0, 0, 0, date("m"), 26, date("Y")) );
					$now = date("Y-m-d");
					if(strtotime($start)<=strtotime($now)){
						if( !file_exists($handle) ){
							touch( $handle );
							chmod( $handle, 0666 );
						}
					}
					$limit = favor_tab_limit_wms($filename);
					if(0<$limit){
						$existsFlg = true;
					}

			}

			if( file_exists(WP_PLUGIN_DIR.'/with-melty-support/cache/upgrade.txt') ){
				//upgrade.txtがあればそちらを優先させる
				$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/upgrade.txt';
				if(file_exists($handle)) $filename = 'upgrade';
				$limit = favor_tab_limit_wms($filename);
				if(0<$limit){
					$existsFlg = true;
				}
			}

			if($existsFlg){
				if($filename=='upgrade'){
					$displaytxt = 'Upgraded';
				}else{
					$displaytxt = ucwords($filename);
				}
				unset($tabArray['wms_info']);
				$tabArray[$filename] = array(
							$displaytxt,
							default_page_wms($filename)
							);
			}
		}

	}

	foreach($tabArray as $key => $array){
		$num++;
		$tabnavi .= '<li id="'. $num .'"><a href="#'. $key .'">'. $array[0] .'</a></li>';
		$tabcontent .= '<div id="'. $key .'" class="smoothScroll">'. $array[1] .'</div>';
	}

	$result .= '<ul id="tabnavi">'. $tabnavi .'</ul>';//#tabnavi
	$result .= '<div id="tabcontent">'. $tabcontent .'</div>';//#tabcontent

	echo $result;

}

// 管理画面すべてにボックスを追加（ダッシュボード以外）
function add_my_box_hooks() {
    //add_meta_box('ID名', 'タイトル', '表示内容を記述した関数名', '投稿タイプ', 'normal', 'high');
    //ID名を変えることで複数生成の指定ができる。同じ内容のボックスを複数生成することも可能。
    $postType = get_post_types(Array('public' => true));
    foreach( $postType as $value ){
	    add_meta_box('wms_box1', 'With melty support', 'add_wms_box1', $value, 'normal', 'low');
	    //add_meta_box('my_box2', 'All posts', 'add_wms_box1', $value, 'normal', 'low');
    }
}
function add_my_box_init() {
    add_action('admin_menu', 'add_my_box_hooks');
}
add_action('init', 'add_my_box_init');

// 管理画面のダッシュボードにボックスを追加
function dashboard_widget_function() {
	add_wms_box1(); //Allpostリスト他
}

function add_dashboard_widgets() {
	wp_add_dashboard_widget('wms_box1', 'With melty support', 'dashboard_widget_function');
}

add_action('wp_dashboard_setup', 'add_dashboard_widgets' );

// アップグレード時の処理
function favor_tab_limit_wms($filename){

	$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/'. $filename .'.txt';

	// ファイルが存在している場合実行
	if( file_exists($handle) ){
		if(filesize($handle)==0){
			$limit = date("Y-m-d H:i:s", mktime(date("H")+24*7, date("i"), date("s"), date("m"), date("d"), date("Y")) ); //168時間後（７日後）
			$fp = fopen($handle, 'w');
			fwrite($fp, $limit); //表示期限を書きこみ
			fclose ($fp);
			$now = date("Y-m-d H:i:s");
		}else if(filesize($handle)>0){
			$fp = fopen($handle, 'r');
			$limit = fread( $fp, filesize($handle) ); // ファイルサイズ分読みこみ
			fclose ($fp);
			$now = date("Y-m-d H:i:s");
			if(strtotime($limit)<strtotime($now)) unlink($handle); //表示期限を超えていたら削除
		}
	}

	$result = ( strtotime($limit)-strtotime($now) )/ (60 * 60 * 24); //秒数の差を計算して日付に変換

	return $result;
}

// check version, upgrade, downgrade
function check_version_upgrade_wms(){

	$data = set_plugin_info();
	unset($data['site_url']);

	$handle = WP_PLUGIN_DIR.'/with-melty-support/admin/data/now-wms.csv';
	if( file_exists($handle) ){
		$upgrade_txt = WP_PLUGIN_DIR.'/with-melty-support/cache/upgrade.txt';
		$makeUpgradeFlg = false;
		$fp = fopen($handle, 'r');
			while ($array = fgetcsv( $fp )) {
				$now_wms[] = $array;
			}
		fclose ($fp);

		//check version
		for($i=0;$i<3;$i++){
			$check = $data['version'][$i] - $now_wms[0][$i]; //(old or new)-now
			if($check != 0) break;
		}
		switch($check){
			case 0:
				//check include folder
				asort($now_wms[1]);
				$now_include_folder = count($now_wms[1]);
				$include_folder = count($data['include_folder']);
				if($include_folder>$now_include_folder){ //(old or new)>now
					$makefile = 'upgrade';
				}else if($include_folder<$now_include_folder){ //(old or new)<now
					$makefile = 'downgrade';
				}
			break;
			case $check>0:
				$makefile = 'upgrade';
			break;
			case $check<0:
				$makefile = 'downgrade';
			break;

		}

		if($makefile=='upgrade' && !file_exists($upgrade_txt)){
			// ファイル作成
			touch( $upgrade_txt );
			// ファイルのパーティションの変更
			chmod( $upgrade_txt, 0644 );
		}else if( $makefile=='downgrade' ){
			if( file_exists($upgrade_txt) ){
				//Downgradeだった場合、必要ないので削除
				unlink($upgrade_txt);
			}
		}

		//ファイルの更新 
		$fp = fopen($handle, 'w');
		foreach ($data as $fields) {
			fputcsv($fp, $fields);
		}
		fclose ($fp);

	}else if( !file_exists($handle) ){
		// ファイル作成
		touch( $handle );
		// ファイルのパーティションの変更
		chmod( $handle, 0644 );

		$fp = fopen($handle, 'w');
		foreach ($data as $fields) {
			fputcsv($fp, $fields);
		}
		fclose ($fp);
	}
}

function set_plugin_info(){

	$data['version'] = array(
							1, //major
							0, //minor
							1, //revision
							);

	$data['site_url'] = array(
							'ja' => "http://wmsymphony.web.fc2.com/",
							'ja_about' => "http://wmsymphony.web.fc2.com/about.html",
							'en' => "http://wmsymphony.web.fc2.com/en/",
							'en_about' => "http://wmsymphony.web.fc2.com/en/about.html",
							);

	$data['include_folder'] = array(
									'all-posts',
									'common',
									'control',
									'custom-fields',
									);

	return $data;
}

?>