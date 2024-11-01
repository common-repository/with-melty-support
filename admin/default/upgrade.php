<?php
//初期起動時の表示処理

/*
今後の予定
キャッシュがないことを確認してから、指定のURLからHTMLソースをダウンロード、必要部分だけ切り取ってキャッシュ。
できれば、ほかと共通のキャッシュ更新確認とかしてほしいかも。
defaultにバージョン情報とかを保管しておく所を作って、ダウンロードしたデータとかからうまいことバージョンの比較をする。
バージョンが同じならそのまま表示、同じでなければ最新ではないという断りの文章を入れてキャッシュ作成する。
*/

function upgrade_print_html(){

	$data = set_plugin_info();
	$version = implode(".", $data['version']);
	$site_ja = $data['site_url']['ja'];
	$contact_ja = $data['site_url']['ja_about'];
	$site_en = $data['site_url']['en'];
	$contact_en = $data['site_url']['en_about'];
	$lang = get_locale();
	if($lang == 'ja'){

	$limit = favor_tab_limit_wms('upgrade');
	$text = "Upgradeタブは、アップグレードされてから約７日間表示されます。";
	switch($limit){
		case $limit<0:
			$countDown = 'まもなく表示は消えます。';
		break;
		case $limit<0.00069:
			$countDown = 'あと'. floor(24*60*60*$limit) .'秒表示されます。';
		break;
		case $limit<0.04166:
			$countDown = 'あと'. floor(24*60*$limit) .'分表示されます。';
		break;
		case $limit<1:
			$hour = floor(24*$limit);
			$minute = floor(24*60*$limit-60*$hour);
			$countDown = 'あと'. $hour .'時間'. $minute .'分表示されます。';
		break;
		default:
			$hour = floor(24*$limit);
			$day = ceil($limit);
			$countDown = 'あと約'. $day .'日間（'. $hour .'時間）表示されます。';
		break;
	}

$result =<<<_htmlsrc
	<div id="wrap_wms">
	<p>
	アップグレードありがとうございます！<br>
	気になる点などありましたら、下記リンク先のメールフォームから連絡ください。<br>
	機会があれば、配布サイトから買い物していただけたら嬉しいです。<br>
	今後ともよろしくお願いします。
	</p>
	<p>
	配布サイト メールフォーム: <br>
	<a href="$contact_ja" target="_blank">$contact_ja</a><br>
	配布サイト: <br>
	<a href="$site_ja" target="_blank">$site_ja</a>
	</p>
	<p>
	現在、お使いのバージョンは
	$version
	です。
	</p>
	<p>
	$text<br>
	$countDown
	</p>
	</div>
_htmlsrc;

	}else{

$result =<<<_htmlsrc
	<div id="wrap_wms">
	<p>
	Thank you for upgrade.<br>
	If you have any point to be worried about, please contact me by email form below link.<br>
	</p>
	<p>
	Distribution sites mail form: <br>
	<a href="$contact_en" target="_blank">$contact_en</a><br>
	Distribution sites: <br>
	<a href="$site_en" target="_blank">$site_en</a>
	</p>
	<p>
	Now version of using is
	$version
	.
	</p>
	<p>
	$text<br>
	$countDown
	</p>
	</div>
_htmlsrc;

	}

	return $result;

}

?>