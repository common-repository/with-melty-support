(function($){
$(function(){
	/*smoothScroll*/
	// #で始まるアンカーをクリックした場合に処理
	$('.smoothScroll a[href^=#]').live('click',function() { //変更：適用範囲を.smoothScroll a に限定
		// スクロールの速度
		var speed = 350;// ミリ秒
		// アンカーの値取得
		var href= $(this).attr('href');
		// 移動先を取得
		var target = $(href == "#" || href == "" ? 'html' : href);
		// 移動先を数値で取得
		var position = target.offset().top-38;//変更：上部にある管理バーが高さ28pxなので28+10px

		var distance = Math.abs(position-$(window).scrollTop());
		var windowHeight = $(window).height();
		if( distance > windowHeight*2){
			$('#tabcontent').animate({opacity:0}, speed, function(){
				//変更：画面表示を消す。
				// スムーススクロール
				$('body,html').animate({scrollTop:position}, speed, 'swing', function(){
					 //変更：画面表示を戻す。表示完了後にfunction()内実行
					$('#tabcontent').animate({opacity:1}, speed, function(){
						$(href).children('input').focus(); //変更：フォーカスを移動先にあるinput要素に合わせる。
					});
				});
			});
		}else{
			$('body,html').animate({scrollTop:position}, speed, 'swing', function(){
				$(href).children('input').focus(); //変更：フォーカスを移動先にあるinput要素に合わせる。
			});
			
		}

		return false;
	});
});

$(function(){


	//タブUI
	//display:none になってしまった要素は.width()などで値が取得できなくなるため最後に実行。
	$('#tabcontent > div').hide(); //初期では非表示

	$('#tabnavi a').live('click', function () {
		$('#tabcontent > div').hide().filter(this.hash).fadeIn(); //アンカー要素を表示

		//classに.lineだけが残るようにするため.fill-upを削除
		if( $(this).parent().hasClass('fill-up') ) $(this).parent().removeClass('fill-up');

		//選択中のタブと同じ行をクリックしたときは処理しない。
		var otherLineFlg = false;
		if( $('#tabnavi .active').parent().attr('class')!=$(this).parent().attr('class') ){
			otherLineFlg = true;
		}

		$('#tabnavi a').removeClass('active');
		$(this).addClass('active');

		if(otherLineFlg){
			$('#tabnavi').animate({opacity:0}, 100, function(){
				//一番下以外の行の配置を昇順にするため、idから要素を初期の順に再配置
				$('#tabnavi').html(
					$('#tabnavi li').sort(function(a, b){
						return parseInt($(a).attr('id'), 10) - parseInt($(b).attr('id'), 10);
					})
				);
				$.tab_line_click();
			});
			$('#tabnavi').animate({opacity:1}, 100);
		}

		return false; //いれてないとアンカーリンクになる
	}).filter(':eq(0)').click(); //最初の要素をクリックした状態に


//#tabcontent内、.wmstabcontentタブ切り替え
	$('.wmstabcontent > div').hide(); //初期では非表示

	$('.wmstabnavi a').click(function () {
		$('.wmstabcontent > div').hide().filter(this.hash).fadeIn(); //アンカー要素を表示

		$('.wmstabnavi a').removeClass('active');
		$(this).addClass('active');

		return false; //いれてないとアンカーリンクになる
	}).filter(':eq(0)').click(); //最初の要素をクリックした状態に

});

//多段タブクリック時
$.tab_line_click = function(){
	//classに.lineだけが残るようにするため.fill-upを削除
	if( $('#tabnavi .active').parent().hasClass('fill-up') ) $('#tabnavi .active').parent().removeClass('fill-up');
	var line_num = $('#tabnavi .active').parent().attr('class');
	var li_html = "";
	//クリックされた行を最後部に追加
	$('#tabnavi li').each(function(){
		if( $(this).hasClass(line_num) ){
			var id = $(this).attr('id');
			li_html += '<li id="'+ id +'">'+ $(this).html() +'</li>';
		}
	});
	$('#tabnavi').append(li_html);
	$('#tabnavi').children('.'+line_num).remove();

	//行番号の振り直し処理
	$.tab_line_change();
}

//多段タブ行振り直し処理
$.tab_line_change = function(){
	var maxW = $('#tabnavi').outerWidth(true)-1; //最大幅と同じになると要素が落ちてしまうので、最大幅より小さくなるよう調整。
	var line = 0;
	var cnt = 1;

	//リサイズ時・多段タブ入れ替え時に実行
	//line～クラス、新たに振り直すため削除
	if( $("#tabnavi li").attr("class") ){
		//class="line*"にマッチするclass名を全部削除
		$("#tabnavi li").removeClass (function (index, css) {
			return (css.match (/\bline\S+/g) || []).join(' ');
		});
	}

	//初期表示・リサイズ時・多段タブ入れ替え時に実行
	//line～クラス、新たに振り直し
	$('#tabnavi li').each(function(i){
		line += $(this).outerWidth(true);
		if(maxW<line){
			cnt += 1;
			line = $(this).outerWidth(true);
		}
		$(this).addClass( 'line'+ cnt );
		//最後の要素に余白を設定。余白がないと多段入れ替え時に要素が詰められてしまうため。
		if( $(this).index()+1==$('#tabnavi li').length ){
			$(this).css('margin-right', maxW-line+'px');
			$(this).addClass( 'fill-up' );

		}
	});
}

$(function(){
	//更新ボタンクリック時
	$('#publishing-action').live('click',function(){
		var pub = {}; // ここで連想配列を代入
		var postFlg = false;

		//変更前のスラッグ名と変更後のスラッグ名をセット
		var newslug = $('#editable-post-name-full').html();
		if(wmsSlug_wms != newslug){
			pub['slug'] = newslug;
			pub['oldslug'] = wmsSlug_wms;
			//alert(pub['oldslug'] + '-->>' + pub['slug']);
			postFlg = true;
		}

		//新規作成確定時fieldの全データを格納
		if(wmsPostnewphpFlg_wms){
			$('#cf_updated_box .cf-field').each(function(i){
				pub['key'+i] = $(this).children('input').val(); //inputボックスの中の値を取得（変更後の名前）
				pub['val'+i] = $(this).children('textarea').val(); //inputボックスの中の値を取得（変更後の名前）
			});
			pub['newid'] = $('#post_ID').val();

			postFlg = true;
		}

		if(postFlg){
			pub['request'] = "publishing";
			$.wmsPost_wms(pub);
			//alert(pub['key1'] + '-->>' + pub['val1']);
		}

	});
});

$(function(){
	var timer = false;
	var first = false;
	$(window).resize(function() {
		if (first==false){
			$('#tabnavi .fill-up').css('margin-right', '');
			first = true;
		}
		if (timer !== false) {
			clearTimeout(timer);
		}
		timer = setTimeout(function() {
			console.log('resized');
			//何らかの処理;
			first = false;
			tab_line_resize();

			//#custom_fieldsが存在しているときだけ実行
			if($('#custom_fields')[0]){
				$.resize_area_all();
			}
	    }, 200);
	});



	//多段タブリサイズ時
	function tab_line_resize(){
			$('#tabnavi').animate({opacity:0}, 100, function(){
				$('#tabnavi .fill-up').removeClass('fill-up');
				//リサイズ時に要素の配置順が変わってしまうため、idから要素を初期の順に再配置
				$('#tabnavi').html(
					$('#tabnavi li').sort(function(a, b){
						return parseInt($(a).attr('id'), 10) - parseInt($(b).attr('id'), 10);
					})
				);

				//.lineをすべて消去。新たなウィンドウサイズで振りなおし
				$.tab_line_change(); //行番号付加
				$.tab_line_click(); //選択タブ行最後部移動
			});
			$('#tabnavi').animate({opacity:1}, 100);
	}

	//必ず最後に読み込みさせたい処理（画面描画完了後に実行）
	$(window).load(function(){
		wmsSlug_wms = $('#editable-post-name-full').html();
		if($(location).attr('href').match(/.+\/post-new\.php|\?post_type=.+/)){
			wmsPostnewphpFlg_wms = true;
		}
		if($(location).attr('href').match(/.+\/post\.php.+/)){
			wmsPostnewphpFlg_wms = false;
		}
		wmsUrl_wms = "../wp-content/plugins/with-melty-support/";
		wmsLang_wms = $('html').attr('lang');

		//初期表示
		$.tab_line_change(); //行番号付加
		$.tab_line_click(); //選択タブ行最後部移動

	});
});

})(jQuery);