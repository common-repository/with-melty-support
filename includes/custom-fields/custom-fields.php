<?php

// 管理画面にCustom Fieldsを表示
// 更新用HTML key val
// 新規作成用フォーム
// フィールド読み込み処理
// POST処理 cf作成・更新処理
// すべての投稿のカスタムフィールドの値を取得する

// 管理画面にCustom Fieldsを表示
// 引数に任意のファイル名を指定することも可能。
function custom_fields_wms($field_name=""){

	if($field_name) $order['slug'] = $field_name;

	$cf = get_custom_fields_wms($array); //postの値を受け取って、csvの配列データを返す。
	$num = 0;
	if(is_array($cf)){
		foreach($cf as $row => $array ){
			foreach($array as $arr ){
				$key = $row;
				$val = $arr;
				$no++;
				$list .= '<li id="cf_li'. $num .'"><a href="#cf_'. $num .'">'. $no .'. '. $key .'</a></li>';
				$fields .= custom_fields_meta_wms($key,$val,$num);
				$num++;
			}
		}
	}
	$index = '<strong>INDEX:</strong><br><ul id="cf_updated_index">'. $list .'</ul>';
	$tonew = '<p><a href="#cf_fields_new">Make New Custom Field</a></p>';
	$updated = '<div id="cf_updated_box">'. $fields .'</div>';
	$addnew = custom_fields_new_wms();

	$result = $tonew.$index.$updated.$addnew;

	return $result;

}

// 更新用HTML key val
//Custom Fieldのフィールド名の部分にあたる
function custom_fields_meta_wms($meta_key="",$meta_val="",$num=""){

	//引数で配列のメタデータを取得
	//一覧作成ループ
	//各meta_key, meta_value の生成ループ

//jsではDOMでHTMLになったものを取得してしまうので、&を一旦普通のテキストにする。
$meta_val = str_replace('&','&amp;',$meta_val); //特殊文字無効化、jsで戻すのでpreタグ表示可能な特殊文字に変換。

$key_num = $num+1; //keyの連番

$meta =<<<_fields_meta
	<div id="cf_$num" class="cf-field">
	<span class="key_num">$key_num.  </span><span class="key">$meta_key</span>
	<button type="button" class="cf-delete">Delete</button>
	<button type="button" class="cf-update">Update</button>
	<span class="cnt_key"></span>
	<span class="cnt_txt"></span>
	<pre>$meta_val</pre>
	<a href="#custom_fields">Field top</a>
	<a href="#cf_$num">Area top</a>
	</div>
_fields_meta;

	return $meta;

}

// 新規作成用フォーム
function custom_fields_new_wms(){

$new =<<<_fields_new
	<div id="cf_fields_new" class="cf-field">
	<p><strong>Add New Custom Field:</strong></p>
	<span class="key_num">Key </span><span class="key"></span>
	<button type="button" class="cf-add-new">Add new</button>
	<span class="cnt_key"></span>
	<span class="cnt_txt"></span>
	<pre></pre>
	<a href="#custom_fields">Field top</a>
	<a href="#cf_fields_new" id="a_new">Area top</a>
	</div>
_fields_new;

	return $new;

}

//フィールド読み込み処理
function get_custom_fields_wms($array,$key="display",$sort=true){

	if($key=="display"){
		$get_cf = get_post_custom(); //管理ページの投稿IDを自動的に取得
		if(is_array($get_cf)) {
			foreach($get_cf as $name => $cf){
				switch($name){
					case '_edit_last':
					case '_edit_lock':
					case '_wp_old_slug':
					case '_encloseme':
						//処理なし
					break;

					default:
						$result[$name] = $cf;
					break;
				}
			}
		}
	}else if($key=="all"){
		$result = get_post_custom($ID);
	}

	//新規作成時で未確定の状態では隠しフィールドも存在してないので値がない状態で$resultに格納される。
	//$resultに値がないのでソートしないようにする。値を渡すとksortがエラー表示される。
	if($sort==true&&isset($result)) ksort($result); //アルファベット昇順でソートする。

	return $result;

}

// POST処理 cf作成・更新処理
function custom_fields_update_wms($array){

	$req = $array['request'];

	//複数の同じキー名のフィールドがある場合のため、古い値を判定に使わないといけない。
	switch($req){

		case 'publishing': //publishボタンをクリック時（新規投稿確定時）

			if(isset($array['newid'])){
				//新規作成時。
				$ID = $array['newid'];
				//受け取った配列からキーと値を整理
				foreach($array as $keyname => $pubdf){

					$subject = $keyname;
					$pattern = '/^(key|val)\d+/';
					preg_match($pattern, $subject, $match);

					if(isset($match[1])){
						if($match[1]=='key') $new_key = $pubdf;
						if($match[1]=='val') {
							$new_val = $pubdf;
							add_post_meta( $ID, $new_key, $new_val );
						}
					}
				}
			}

		break;
	}

}

// すべての投稿のカスタムフィールドの値を取得する
// オプションで任意の投稿のみ、キー名の一覧のみ取得とかもできれば
function get_all_custom_wms($order='all',$keyname=false){

	if($order=='all'){
		$IDarray = get_post_order_wms('ID'); //すべての投稿IDを取得
	}else if( is_numeric($order) ){
		$IDarray[0] = $order;
	}

	foreach($IDarray as $IDarr){
		$obj = get_post( $IDarr );
		$custom[$obj->post_name] = get_post_custom($IDarr);
	}

	if($keyname){
		foreach($custom as $key => $customarr){
			foreach($customarr as $key2 => $val){
				$names[$key][] = $key2;
			}
		}
		return $names;
	}

	return $custom;

}
?>