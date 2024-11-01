<?php
//初期起動時の表示処理

/*
今後の予定

*/

function info_print_html(){

	$data = set_plugin_info();
	$version = implode(".", $data['version']);
	$site_ja = $data['site_url']['ja'];
	$contact_ja = $data['site_url']['ja_about'];
	$site_en = $data['site_url']['en'];
	$contact_en = $data['site_url']['en_about'];
	$lang = get_locale();

	if($lang == 'ja'){

$result =<<<_htmlsrc
	<div id="wrap_wms">
	<p>
	With melty support をお使い頂き、ありがとうございます。<br>
	もし何かお気づきの点がありましたら、下記リンク先のメールフォームからご連絡ください。<br>
	また、よろしければ何か購入される際は、配布サイトに立ち寄って頂けると幸いです。
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
	</div>
_htmlsrc;

	}else{

$result =<<<_htmlsrc
	<div id="wrap_wms">
	<p>
	Thank you for using With melty support.<br>
	If you have a point of noticed, please contact me by email form below link.<br>
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
	</div>
_htmlsrc;

	}

	return $result;

}

?>