<?php
// 表示するリストの項目を設定
// 共通設定
// 管理画面にAll postsリストを表示
// All postsリストの上部
// すべての投稿情報を取得 ポストタイプ名=> array =>(連番）投稿情報 WP_Post Object
// 指定の投稿情報をすべて取得 項目名 => array =>(連番）値
// All postsリストキャッシュ部分の処理
// カテゴリ一覧 作成処理
// 表示するリストの項目名変換
// ControlフォームHTML

// 表示するリストの項目を設定
function all_posts_set_names($line_num=null) {

//ファイルの存在確認、なければgetpostの処理、あれば、ファイルないを読み込んで、
//配列（IDとかの名称が入った配列があるので、キーと$getpost->値に配置すればいい。）を整えて返す。

	$getpost = get_post();
	$handle = WP_PLUGIN_DIR.'/with-melty-support/cache/all-posts.csv';
	if( !file_exists($handle) ){
		$handle = WP_PLUGIN_DIR.'/with-melty-support/admin/default/all-posts.csv';
	}
	$fp = fopen($handle, 'r');
	while ($array = fgetcsv( $fp )) {
		$lines[] = $array;
	}
	fclose ($fp);

	return $lines;
}

// 共通設定
function all_posts_common() {

	$common = array(
					'delimiter' => '/',
					//'new_window' => ' target="_blank" ',
					);

	return $common;

}
// 管理画面にAll postsリストを表示
function all_posts_wms() {

	$url  = $_SERVER['REQUEST_URI'];// 現在のページURLのサイトURL以降を取得
	if ( strstr($url,'/post-new.php') || strstr($url,'/post.php') ){
		$result .= all_posts_this_page();// All postsリストの上部
	}
	$cache = call_cache_wms('all-posts');//キャッシュデータを取得
	if(!empty($cache)){
		$result .= $cache;//キャッシュデータを取得
	}else{
		$lang = get_locale();
		if($lang == 'ja'){
			$result .= '<p>Controlタブからリストを再作成してください。</p>';
		}else{
			$result .= '<p>Please creating list at Control tab.</p>';
		}
	}

	return $result;
}

// All postsリストの上部
function all_posts_this_page(){

	$common = all_posts_common();
	if($pageFlg){
		$postcat = get_the_category();
		$posttag = get_the_tags();
		if ($postcat) {
			foreach($postcat as $cat) {
					$catslug .= $cat->slug . $common['delimiter'];
			}
		}
		if ($posttag) {
			foreach($posttag as $tag) {
					$tagslug .= $tag->slug . $common['delimiter'];
			}
		}
	}
	
	$csv = all_posts_set_names();
	$mainarg = $csv[1];

	$getpost = get_post();
	foreach( $mainarg as $val ){
		$main[] = $getpost->$val;
	}

	$slugs = array(
				$catslug,
				$tagslug
				);
	$headarg = array_merge($main,$slugs);

	foreach( $headarg as $key => $head ){
		if(empty($head)) $headarg[$key] = "nothing";
	}

	$result .= '<p>';
	$result .= '<strong>This page:</strong> ';

	if(get_permalink($getpost->ID)){
		$result .= '<span class="ap_to_page"><a href="'. get_permalink($getpost->ID).'"'. $view_set['new_window'] .'>&nbsp;&raquo;page&nbsp;</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
		$result .= '<br>';
	}

	$break_cnt = count($headarg)-2;
	foreach( $headarg as $key => $arg ){
		switch($key){
			case $break_cnt:
				$result .= '<br>';
				$result .= 'Categories: '.$arg;
			break;

			case $break_cnt+1:
				$result .= '<br>';
				$result .= ' Tags: '.$arg;
			break;

			default:
				$result .= $arg.$common['delimiter'];
			break;
		}
	}
	$result .= '</p>';

	return $result;

}

// All postsリストキャッシュ部分の処理
function all_posts_cache_wms($setpost_type="",$set_names=""){

	$csv = all_posts_set_names();

	//初期値設定部分をどこかとつなげたら、ユーザー設定で操作可能。
	if($setpost_type==""){
		$setpost_type = $csv[0];
		$menu = array(
				'type' => 'order',
				'is' => 'all'
			);
	}

	$set_id = array('ID');

	if($set_names==""){
		$set_names = $csv[1];
	}

	$common = all_posts_common();
	$view_set = array(
						'delimiter' => $common['delimiter'],
						'box_id' => 'all_posts',
						'edit_url' => get_admin_url('', 'edit.php'),
						'new_url' => get_admin_url('', 'post-new.php'),
						//'new_window' => $common['new_window'],
						);

	$mainarg = $csv[1];
	$names = all_posts_change_names($mainarg);
	$to_link = array(
				'menu' => 'Menu',
				'delimiter' => '"'. $common['delimiter'] .'"',
				);
	$headline = array_merge($names,$to_link);

	//display
	foreach($headline as $key => $line){
		if($key=='delimiter'){
			$linetext .= '<span id="ap_'. $key .'">'. $line .'</span><span class="ap_delimiter">'. $common['delimiter'] .'</span>';
		}else{
			$linetext .= '<a href="" id="ap_'. $key .'" class="ap_display">'. $line .'</a><span class="ap_delimiter">'. $common['delimiter'] .'</span>';
		}
	}
	$linetext .= '<a href="" id="ap_tab">"tab"</a>';

	//sort
	$sort = $names;
	unset($sort['taxonomies']);
	foreach($sort as $key => $line){
		$sorttext .= '<a href="" id="ap_sort_'. $key .'" class="ap_sort">'. $line .'</a><span class="ap_delimiter">'. $common['delimiter'] .'</span>';
	}

	foreach($setpost_type as $setpost){
			$menu['order'] = $setpost;
			$get_post_id = get_post_order_wms($set_id,$menu); //$setpost_type使って、必要な分だけ投稿情報を取得
			//[ID] => [0] => 370,[1] => 689 ...
			//扱いやすいようにデータを整形し直す。
				if(is_array($get_post_id)){
					foreach($get_post_id as $idkey => $idarr){
						$get_type_ids[$setpost] = $idarr;
						//[post] => [0] => 370,[1] => 689 ...
					}
				}
	}

	//HTMLソース化
	$result .= '<div id="all-post-list">'."\n";

	//INDEX
	$result .= '<p><strong>INDEX:</strong> ';
	foreach($get_type_ids as $type => $post_id){
		$result .= '<a href="#'. $type .'_point">'. $type .'</a>&nbsp;'. $view_set['delimiter'] .'&nbsp;';
	}
	$result .= '</p>'."\n";

	//全投稿タイプ一覧
	foreach($get_type_ids as $type => $post_id){
		$result .= '<div id="'. $type .'_point" class="area_top">'."\n";
		$result .= '<p><strong>'. $type .'</strong>'."\n";
		$result .= '<a href="'. $view_set['edit_url'] .'?post_type='. $type .'"'. $view_set['new_window'] .'> &raquo;view </a></span>'. $view_set['delimiter'];
		$result .= '<a href="'. $view_set['new_url'] .'?post_type='. $type .'"'. $view_set['new_window'] .'> &raquo;add&nbsp;new </a></span>'. $view_set['delimiter'];
		$result .= '<a href="#'. $view_set['box_id'] .'"> &raquo;index </a></span>'. $view_set['delimiter'];
		$result .= '</p>';

		//カテゴリ一覧
		$articlesFlg = false;
		if($type == "post"){
			$result .= create_taxonomies_view_wms($view_set,$type,$csv[2]);
			$articlesFlg = true;
		}

		//asort($post_id); //値昇順 = id番号HTML出力昇順
		//arsort($post_id); //値降順 = id番号HTML出力降順
		//ksort($post_id); //キー昇順 = スラッグ名HTML出力降順
		krsort($post_id); //キー降順 = スラッグ名HTML出力昇順
		$result .= '<p class="ap_display"><strong>Display:</strong> [&nbsp;'. $linetext .'&nbsp;]</p>';
		$result .= '<p class="ap_sort"><strong>Sort:</strong> [&nbsp;'. $sorttext .'&nbsp;]</p>';
		$result .= '<p class="ap_area">';
		$result .= '<span class="postlist-num">'.count($post_id).' items</span>'."\n";
		$result .= '<span class="postlist-wrap">'."\n";
		foreach($post_id as $id){
			$now = get_post($id);
			$result .= '<span class="postlist">'."\n";
				foreach($set_names as $names){
					switch($names){
						case 'url':
							$data = get_permalink($id);
							$result .= '<span class="ap_'. $names .'">'. $data .'<span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
						break;
						default:
							if( $now->$names==null ){
								$data = "null";
							}else{
								$data = str_replace(" ", "&nbsp;", $now->$names);
							}
							$result .= '<span class="ap_'. $names .'">'. $data .'<span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
						break;
						case 'taxonomies':
							$result .= create_taxonomies_list_wms($id,$view_set);
						break;
					}
				}
			$result .= '<span class="ap_menu">';
			$result .= '<span class="ap_to_page"><a href="'. get_permalink($id).'"'. $view_set['new_window'] .'>&nbsp;&raquo;page&nbsp;</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
			$result .= '<span class="ap_to_edit"><a href="'. get_edit_post_link($id).'"'. $view_set['new_window'] .'>&nbsp;&raquo;edit&nbsp;</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
			$result .= '</span>';
			$result .= '</span>'."\n";
		}
		$result .= '</span>'."\n";
		$result .= '</p>'."\n";
		if($articlesFlg) $result .= '</div>'."\n";
		$result .= '</div>'."\n";
	}

	$result .= '</div>'."\n"; //#all-post

	return $result;

}

//各投稿のタグ名一覧
function create_taxonomies_list_wms($id,$view_set){
	//slugのクラスがついたspanでくくられたカテゴリ名とタグ名を出力させる。
	$order = array(
					'category',
					'post_tag',
					'link_category'
					);

	foreach($order as $name){
		$terms[$name] = wp_get_object_terms( $id, $name );
	}
	$terms = array_filter($terms);
	$endcnt = count($terms);
	foreach($terms as $key => $term){
		$i++;
		switch($key){
			case 'category':
				$list .= 'Category<span class="ap_delimiter">'. $view_set['delimiter'] .'</span>';
			break;
			case 'post_tag':
				$list .= 'Tag<span class="ap_delimiter">'. $view_set['delimiter'] .'</span>';
			break;
			case 'link_category':
				$list .= 'Link category<span class="ap_delimiter">'. $view_set['delimiter'] .'</span>';
			break;
		}
		foreach($term as $tax){
			$list .= '<span class="ap_'. $tax->slug .'"><a href="#ap_'. $tax->slug .'">'. $tax->name .'</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
		}
		if($i != $endcnt) $list .= '<br>';
	}
	$result .= '<span class="ap_taxonomies">'. $list .'</span>';
	return $result;
}

//カテゴリ一覧 作成処理
function create_taxonomies_view_wms($view_set,$post_type,$csv_2){

	//post category,tag
	//タクソノミー情報取得引数 => 投稿一覧URL
	$taxonomies = array(
						'category' => $view_set['edit_url'].'?category_name=',
					    'post_tag' => $view_set['edit_url'].'?tag='
						);

	//各種設定。ソート対象、順、すべてのカテゴリー情報を取得
	$args = array(
				'orderby' => 'slug', 
				'order' => 'ASC',
				'get' => 'all'
				);

	//内部リンク生成用
	$index_id = $post_type .'_point';
	$listname = array(
				'category', 
				'tag',
				'articles'
				);

	//取得データリスト
	$set_names = $csv_2;

	//display
	foreach($set_names as $change){
		$word = $change;
		if( $word=='term_id' || $word=='term_taxonomy_id' ) $word = str_replace('id', 'ID', $word);
		$word = str_replace('_', '&nbsp;', $word);
		$change_names[$change] = ucfirst($word); //小文字の単語のみ頭文字を大文字にする。
	}
	foreach($change_names as $key => $line){
		$linetext .= '<a href="" id="ap_tax_'. $key .'" class="ap_display">'. $line .'</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span>';
	}
	$linetext .= '<a href="" id="ap_tax_menu" class="ap_display">Menu</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span>';
	$linetext .= '<a href="" id="ap_tab">"tab"</a>';

	//sort
	foreach($change_names as $key => $line){
		$sorttext .= '<a href="" id="ap_tax_sort_'. $key .'" class="ap_sort">'. $line .'</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span>';
	}


	//HTML開始

	//内部リンク生成 taxonomies部分、articles部分
	$result .= '<p><strong>'. $post_type .' INDEX:</strong> ';
	foreach($listname as $name){
		$result .= '<a href="#'. $post_type .'_'. $name .'_point">'. $name .'</a>&nbsp;'. $view_set['delimiter'] .'&nbsp;';
	}
	$result .= '</p>'."\n";

	foreach($taxonomies as $name => $url){
		switch($name){
			case 'post_tag':
				$text = 'tag';
			break;
			default:
				$text = $name;
			break;
		}

		//taxonomies部分生成
		$result .= '<p id="'. $post_type .'_'. $text .'_point'.'"><strong>'.' - '. $post_type .' '. $text .' - '.'</strong> ';
		$result .= '<a href="'. get_admin_url('', 'edit-tags.php') .'?taxonomy='. $name .'"'. $view_set['new_window'] .'> &raquo;view </a>'. $view_set['delimiter'];
		$result .= '<a href="#'. $index_id .'"> &raquo;'. $post_type .' index </a>'. $view_set['delimiter'];
		$result .= '</p>';
		$result .= '<p><strong>Display:</strong> [&nbsp;'. $linetext .'&nbsp;]</p>';
		$result .= '<p><strong>Sort:</strong> [&nbsp;'. $sorttext .'&nbsp;]</p>';
		$result .= '<p>';
		$tax_data = get_terms( $name,$args );//カテゴリの情報を取得
		$result .= '<span class="postlist-num">'.count($tax_data).' items</span>'."\n";
		$result .= '<span class="postlist-wrap">'."\n";
		foreach($tax_data as $tax) {
			$result .= '<span id="ap_'. $tax->slug .'" class="postlist">'."\n";
			foreach($set_names as $names) {
				$result .= '<span class="ap_tax_'. $names .'">'. $tax->$names .'<span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
			}
			$result .= '<br>';
			$result .= '<span class="ap_tax_menu">';
			$result .= '<span class="ap_tax_to_view"><a href="'. $url. $tax->slug .'"'. $view_set['new_window'] .'>&nbsp;&raquo;view</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
			$result .= '<span class="ap_tax_to_page"><a href="'. get_tax_url_wms($tax->slug) .'"'. $view_set['new_window'] .'>&nbsp;&raquo;page&nbsp;</a><span class="ap_delimiter">'. $view_set['delimiter'] .' </span></span>';
			$result .= '<span class="ap_tax_to_edit"><a href="'. get_admin_url('', 'edit-tags.php') .'?action=edit&taxonomy='. $name .'&tag_ID='. $tax->term_taxonomy_id .'&post_type=post"'. $view_set['new_window'] .'>&nbsp;&raquo;edit&nbsp;</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
			$result .= '<span class="ap_tax_show_list"><a href="">&nbsp;&raquo;show&nbsp;list&nbsp;</a><span class="ap_delimiter">'. $view_set['delimiter'] .'</span></span>';
			$result .= '</span>';
			$result .= '</span>'."\n";
		}
		$result .= '</span>'."\n";
		$result .= '</p>'."\n";
	}

		//articles開始部分生成
		$result .= '<div id="'. $post_type .'_articles_point'.'">'."\n";
		$result .= '<strong>'.' - '. $post_type .' articles - '.'</strong>'."\n";
		$result .= '<a href="'. $view_set['edit_url'] .'?post_type='. $post_type .'"'. $view_set['new_window'] .'> &raquo;view </a>'. $view_set['delimiter'];
		$result .= '<a href="'. $view_set['new_url'] .'?post_type='. $post_type .'"'. $view_set['new_window'] .'> &raquo;add&nbsp;new </a></span>'. $view_set['delimiter'];
		$result .= '<a href="#'. $index_id .'"> &raquo;'. $post_type .' index </a>'. $view_set['delimiter'];
		$result .= '<br>';

	return $result;
}

// すべての投稿情報を取得 ポストタイプ名=> array =>(連番）投稿情報 WP_Post Object
function get_all_posts_wms($menu=""){

	if($menu==""){
		$menu = array(
						'type' => 'all',
						'is' => 'all',
					);
	}

	switch($menu['type']){
		case 'order': //指定の投稿タイプ
			if(is_array($menu['order'])){
				$post_types = $menu['order']; //配列で渡されている場合
			}else{
				$post_types[0] = $menu['order']; //文字列で渡されている場合
			}
		break;

		case 'my':
			$post_types =  array(
				'page',
				'post',
				'attachment'
				);
		break;

		case 'all':
			$args=array(
						'public'   => true,
						);
			$output = 'names'; // names or objects, note names is the default
			$operator = 'and'; // 'and' or 'or'
			$post_types=get_post_types($args,$output,$operator); 
		break;

		case 'custom':
			$args=array(
						'public'   => true,
						'_builtin' => false
						); 
			$output = 'names'; // names or objects, note names is the default
			$operator = 'and'; // 'and' or 'or'
			$post_types=get_post_types($args,$output,$operator); 
		break;
	}

	foreach ($post_types as $post_type ) {

		$order = 'nopaging=1&orderby=name&order=desc&post_status=any&post_type=';
		$data = get_posts($order.$post_type);

		switch($menu['is']){
			case 'nothing':
				if($data==null){ //データが作成されてない投稿タイプのみ取得
					$post_posts[$post_type] = $data;
				}
			break;

			case 'is':
				if(!$data==null){ //データが作成されている投稿タイプのみ取得
					$post_posts[$post_type] = $data;
				}
			break;

			case 'all':
				$post_posts[$post_type] = $data;  //データの有無に関係なくすべての投稿タイプ取得
			break;
		}

	}
	return $post_posts;
}

// 指定の投稿情報をすべて取得 項目名 => array =>(連番）値
// 個別に処理を分岐させたほうがいいかも。個別に取りだしたいとき、無駄に処理してる部分がほとんど。
// 配列でループ制御できるようにした。無駄なく処理ができる。
function get_post_order_wms($setnames=null,$menu="") {

	if(!isset($setnames)){
		$setnames = array(
							'ID',
							'post_author',
							'post_date',
							'post_date_gmt',
							'post_content',
							'post_title',
							'post_excerpt',
							'post_status',
							'comment_status',
							'ping_status',
							'post_password',
							'post_name',
							'to_ping',
							'pinged',
							'post_modified',
							'post_modified_gmt',
							'post_content_filtered',
							'post_parent',
							'guid',
							'menu_order',
							'post_type',
							'post_mime_type',
							'comment_count',
							'filter'
							);
	}else if(!is_array($setnames)){
		$setnames = array($setnames);
	}

	$all = get_all_posts_wms($menu);
	 //[post] => [0] => WP_Post Object
	foreach ($all as $key => $val ) { //[post] => [0]
		foreach ($val as $key1 => $val2 ) {
			foreach ($setnames as $set ) {
				$result[$set][] = $val2->$set;
			}
		}
	}

	if(count($result)>1){ //$result['post_name']が空で出力されるのを防ぐ
		$post_name = array_unique($result['post_name']); //重複を削除
		$result['post_name'] = array_merge($post_name); //配列番号を連番にして戻す
	}

	return $result;

}


// 表示するリストの項目名変換
// 投稿情報名だけが入ったキーなしの配列が渡される。
function all_posts_change_names($names) {

	foreach($names as $change){
		$word = str_replace('post_', '', $change);
		$word = str_replace('_', '&nbsp;', $word);
		if($word == "name") $word = "slug";
		$result[$change] = ucfirst($word); //小文字の単語のみ頭文字を大文字にする。
	}

	return $result;
}


// ControlフォームHTML
function all_posts_control_wms() {

	//各基本情報 チェックボックス用
	//投稿タイプ
	$args=array(
				'public'   => true,
				);
	$output = 'names'; // names or objects, note names is the default
	$operator = 'and'; // 'and' or 'or'
	$setnames[0] = get_post_types($args,$output,$operator);
	$br[0] = 3; //折り返し個数
	$title[0] = 'Post Types';

	//投稿情報
	$setnames[1] = array(
						'ID',
						'post_author',
						'post_date',
						'post_date_gmt',
						'post_content',
						'post_title',
						'post_excerpt',
						'post_status',
						'comment_status',
						'ping_status',
						'post_password',
						'post_name',
						'to_ping',
						'pinged',
						'post_modified',
						'post_modified_gmt',
						'post_content_filtered',
						'post_parent',
						'guid',
						'menu_order',
						'post_type',
						'post_mime_type',
						'comment_count',
						'filter',
						'url', //not get_post()
						'taxonomies', //not get_post()
						);
	$br[1] = 5; //折り返し個数
	$title[1] = 'Post Datas';

	//タクソノミー情報
	$setnames[2] = array(
						'term_id',
						'name',
						'slug',
						'term_group',
						'term_taxonomy_id',
						'taxonomy',
						'description',
						'parent',
						'count'
						);
	$br[2] = 3; //折り返し個数
	$title[2] = 'Taxonomies';

	//ボタン表記
	$lang = get_locale();
	if($lang == 'ja'){
		$btntxt = '作成する';
		$checktxt = 'すべてクリアする';
	}else{
		$btntxt = 'Create';
		$checktxt = 'Uncheck All';
	}
	$btn = '<button type="button" class="all-control" >'. $btntxt .'</button>';
	$uncheck = '<a href="" class="all-control-uncheck" >'. $checktxt .'</a>';

	//delimiterなど共通設定
	$common = all_posts_common();

	//displayデータ（チェック済みボックス）
	$csv = all_posts_set_names();

	//post_typeに余計な文字が入っていないかチェック
	$check_key=0;
	foreach($csv[0] as $check_post_type){
		if(in_array($check_post_type, $setnames[0]) == false){
			unset($csv[0][$check_key]);
		}
		$check_key++;
	}

	//HTML開始
	$result .= '<div id="all_posts_control" class="area">';

	foreach( $title as $line ){
		$index .= '<a href="#ap_ctrl_'. str_replace(' ', '_', $line) .'">'. $line .'</a>'. $common['delimiter'];
	}
	$result .= '<p>'. $index .'</p>';

	$i=0;
	foreach( $csv as $now_area ){

		$result .= '<div id="ap_ctrl_'. str_replace(' ', '_', $title[$i]) .'">';
		$disptext = "";
		foreach($now_area as $text){
			$disptext .= $text .$common['delimiter'];
		}

		//投稿タイプチェックボックス
		$result .= '<p> &#8211; '. $title[$i] .' &#8211; '. $common['delimiter'] .'<a href="#all_posts_control"> &raquo;Area Top</a></p>';
		$result .= '<p class="display-now">Now:&nbsp;[&nbsp;<span>'. $disptext .'</span>&nbsp;]&nbsp;</p>';
		$result .= '<p class="display-change">Change:&nbsp;[&nbsp;<span>'. $disptext .'</span>&nbsp;]</p>&nbsp;';
		$result .= $btn;
		$result .= '<ul class="submit-list"><div>';
		$cnt=0;
		foreach($setnames[$i] as $setbox){
			$cnt++;
			$checked = '';
			if( in_array($setbox,$now_area) ) $checked = ' checked';
			$result .= '<li><label><input type="checkbox" name="all-posts" value="'. $setbox .'"'. $checked .'>'. $setbox .' </label></li>';
			if($cnt%$br[$i]==0) $result .= '</div><div>';
		}
		$result .= '<li>'. $uncheck .'</li>';
		$result .= '</div></ul>';
		$result .= '</div>';
		$i++;
	}
	$result .= '<div class="to-top"><a href="#all_control">Control top</a></div>';
	$result .= '</div>';
	return $result;
}

?>