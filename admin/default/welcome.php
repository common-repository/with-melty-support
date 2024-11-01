<?php
//初期起動時の表示処理

/*
今後の予定

*/

function welcome_print_html(){

	$lang = get_locale();
	if($lang == 'ja'){

$result =<<<_htmlsrc
	<div id="wrap_wms">
	<p>
	<strong>
	ようこそ、With melty support へ
	</strong>
	</p>
	<?php echo "!!!!!!"; ?>
	<p>
	With melty support をインストールして頂きありがとうございます。<br>
	下のスタートボタンをクリックしていただくと、必要なデータを作成しご利用可能になります。<br>
	データの作成に、数秒～数十秒かかりますので時間に余裕を持って始めてください。
	</p>
	<p id="wms_start" class="area">
	スタートする
	<button type="button" class="all-control" >Start</button>
	</p>
	</div>
_htmlsrc;

	}else{

$result =<<<_htmlsrc
	<div id="wrap_wms">
	<p>
	<strong>
	Welcome to "With melty support"
	</strong>
	</p>
	<p>
	Thank you install "With melty support".<br>
	If you have you click the Start button below, you will be available to create the necessary data.<br>
	Please begun with time to spare to create the data, because it takes a few seconds to a few tens of seconds.
	</p>
	<p id="wms_start" class="area">
	Get started
	<button type="button" id="wms_start" class="all-control" >Start</button>
	</p>
	</div>
_htmlsrc;

	}

	return $result;

}

?>