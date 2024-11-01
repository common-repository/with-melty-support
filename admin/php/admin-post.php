<?php
// ajaxからプラグイン内のファイルを使う処理
// 管理画面でJSを読み込むフックを登録

// ajaxからプラグイン内のファイルを使う処理
// jsファイルのajaxからアクセスするとwp内の関数（プラグインの関数も）が動作しない。
// wp-load.phpを読み込む方法だとwpのバージョンなどによる階層の違いとかで失敗する可能性があるらしいので、この方法に変更。

function _post_wms(){
	if( isset($_POST) ){

		$req = $_POST['posts']['request'];
		$array = $_POST['posts'];

		switch($req){
			case 'publishing': //投稿ボタンをクリック時
				custom_fields_update_wms($array);
			break;

			case 'all-control': //キャッシュ生成ボタンをクリック時
				if($array['call']=='wms_start'){
					require_once(WP_PLUGIN_DIR.'/with-melty-support/admin/php/first.php');
					start_post_wms(); //first.php
				}else{
					post_make_cache_wms($array);
				}
			break;

		}
	}
}
function add_load_init() {
	add_action('wp_ajax_post_wms', '_post_wms');
}
add_action('init', 'add_load_init');


//管理画面でJSを読み込むフックを登録
function _enqueue_script_wms(){
	wp_enqueue_script('wms-onload', WP_PLUGIN_URL."/with-melty-support/admin/js/common-ajax.js", array('jquery'));
	wp_localize_script('wms-onload', 'WMS', array(
        'endpoint' => admin_url('admin-ajax.php'),
        'action' => 'post_wms'
    ));
}
add_action( 'admin_enqueue_scripts', '_enqueue_script_wms' );

?>