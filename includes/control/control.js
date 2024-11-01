(function($){

//Control処理
$(function(){

	//ボタン連続押し制御
	//if($('.sending').length==0){ で動かしたくない処理を囲んで内部に記述。
	function sending_btn_stopper(btn,time){
			$(btn).after('<span></span>');
			$(btn).next("span").addClass('sending').hide();
			//時刻が設定されていれば指定時間で解除
			if(time >= 0){
				setTimeout(function(){
					$('.sending').remove();
				},time);
			}
	}

	//すべて
	//送信ボタン処理
	$('#all_control .all-control').click(function () {
		//送信・作成メッセージが画面に出ている間は処理しない。連続処理制御。
		if($('.sending').length==0){
			//処理完了メッセージが出ていれば削除する。
			if($('.completed').length==1){
				$('.completed').fadeOut(650,function(){
					$(this).remove();
				});
			}

			var ctrl = {};
			var empty = false;
			var skip = false;
			var call = $(this).closest('.area').attr('id');

			$('#all_control #'+call).find('.display-change, .submit-list').each(function(j){
				var disp = $(this).hasClass('display-change');
				var list = $(this).hasClass('submit-list');
				if(list == true && skip != j){
					//disp存在しないとき
					var vals = $(this).find('input:checked')
								.map(function(index, el) { return $(this).val(); });
					if(vals[0]==undefined) empty = true; //すべてのチェックが空
				}else if(disp == true){
					//disp存在するとき
					var target = $(this).children('span').html();
					var delimiter = target.slice(target.length-1);
					target = target.slice(0,target.length-1);//空配列を作成させないため、末尾の区切り文字を排除。
					var vals = target.split(delimiter);
					if(vals=="") empty = true; //すべてのチェックが空
					skip = j+1; //次のリストは取得しない
				}
				if(list == true && skip != j || disp == true){
					$.each(vals, function(i,value){
						ctrl['val-g'+j+'-c'+i] = value;
					});
				}
			});

			//項目にチェックが入ってない場合。
			if(empty!=false){
				var txt ="";
				if(wmsLang_wms=="ja"){
					txt = "項目にチェックを入れてください。";
				}else{
					txt = "Please check input box.";
				}
				alert(txt);
				return false;
			}

			ctrl['request'] = "all-control";
			ctrl['call'] = call;

			$.wmsPost_wms(ctrl);

			//送信メッセージ処理
			var txt ="";
			if(wmsLang_wms=="ja"){
				txt = "データ作成中。 完了メッセージが表示されるまで、少々お待ちください…。";
			}else{
				txt = "Creating a data. Please wait for completion message to appear...";
			}

			var imgtag = '<img src="'+wmsUrl_wms+'admin/img/preloader.gif" width="10px" height="10px"  style="margin:0 5px;"/>';
			$(this).after('<span><br>'+imgtag+txt+'</span>');
			$(this).next("span").addClass('sendmes').css("color","#f00000").hide();

			sending_btn_stopper(this); //一度操作したらボタン操作禁止

			$('.sendmes').fadeIn(650);
		}
	});

	//.display-changeを見つけて、その子要素のspanを返す。
	function is_disp(elem){
		var is_disp = $(elem).closest('.submit-list').prev('.display-change').length;
		var disp ="";
		if(is_disp==1){
			disp = $(elem).closest('.submit-list').prev('.display-change').children('span');
		}else if(is_disp==0){
			if($('.completed').length==0){
				disp = $(elem).closest('.submit-list').prev().prev('.display-change').children('span');
			}else if($('.completed').length==1){
				disp = $(elem).closest('.submit-list').prev('.completed').prev().prev('.display-change').children('span');
			}
		}

		return disp;
	}

	//Change list
	$('#all_control .submit-list').click(function (eo) {
		if(eo.target.tagName=='INPUT'){
			//処理完了メッセージが出ていれば削除する。
			if($('.completed').length==1){
				$('.completed').fadeOut(650,function(){
					$(this).remove();
				});
			}

			var target = is_disp(this);
			//.display-changeが存在する場合。
			if(target.length==1){
				var val = "";
				var delimiter = "";
				if($(eo.target).attr('checked')){
					//checkが入ったとき
					val = $(target).html();
					var now = $(target).parent().siblings('.display-now').children('span').html();
					delimiter = now.slice(now.length-1);
					if(val==delimiter) val="";
					val += eo.target.value + delimiter;
					$(target).html(val);
				}else{
					//checkが外れたとき
					val = $(target).html();
					var now = $(target).parent().siblings('.display-now').children('span').html();
					delimiter = now.slice(now.length-1);
					val = eo.target.value + delimiter;
					var replace = $(target).html().split(val).join('');
					$(target).html(replace);
				}
			}
		}
	});

	//Uncheck All, Check All
	$('#all_control .all-control-uncheck').click(function (eo) {
		//処理完了メッセージが出ていれば削除する。
		if($('.completed').length==1){
			$('.completed').fadeOut(650,function(){
				$(this).remove();
			});
		}
		var disp = is_disp( $(this) );
		var txt ="";
		var now = $(disp).parent().siblings('.display-now').children('span').html();
		var delimiter = now.slice(now.length-1);

		if( $(this).hasClass('check') ){
			//Check All click
			//すべてのチェックを入れる
			$(this).closest('.submit-list').each(function(){
				$(this).find('input').attr('checked', true);
			});

			if(disp.length==1){
				//changeがあれば、チェックが入ったinputの値をテキストにしてchangeにセット
				var vals = $(this).closest('.submit-list').find('input:checked')
							.map(function(index, el) { return $(this).val()+delimiter; });
				var insert = vals.get().join('');
				$(disp).html(insert);
			}

			//テキストを変える
			if(wmsLang_wms=="ja"){
				txt = "チェックを元に戻す";
			}else{
				txt = "Undo check";
			}
			//.check->.undo
 			$(this).text(txt).removeClass('check');
			$(this).text(txt).addClass('undo');
		}else if( $(this).hasClass('undo') ){
			//Undo click
			//保存していたチェック状態を取得。changeがあれば値をセットする。
			var undo = $(this).prev('.change-undo').html();
			if(disp.length==1) $(disp).html(undo);
			//保存していたチェック状態を配列化。マップで今チェックされてる値を一つずつ比較。
			var check = undo.split(delimiter);
			$(this).closest('.submit-list').find('input')
				.map(function() {
					//値がcheckに存在しなければチェックを外す
					if( $.inArray($(this).val(), check)==-1 ) $(this).attr('checked', false);
				});
			//保存用のspanを削除
			$(this).prev('.change-undo').remove();

			//テキストを変える
			if(wmsLang_wms=="ja"){
				txt = "すべてクリアする";
			}else{
				txt = "Uncheck All";
			}
			//.undo->nothing
			$(this).text(txt).removeClass('undo');
		}else{
			//Uncheck All click
			if( $(this).prev('.change-undo').length==0 ){
				//一度だけ実行。
				//チェックの状態をテキストにしてspanタグで囲んで非表示にする。
				if(disp.length==1){
					var undocheck = $(disp).html();
				}else if(disp.length==0){
					var vals = $(this).closest('.submit-list').find('input:checked')
						.map(function(index, el) { return $(this).val(); });
					var undocheck = $(vals).get().join(delimiter);
				}
				//保存用のspanを作成
				$(this).before('<span class="change-undo"></span>');
				$(this).prev('span').hide().html(undocheck);
			}

			//すべてのチェックを外す
			$(this).closest('.submit-list').each(function(){
				$(this).find('input').attr('checked', false);
			});

			//changeがあれば、値を全部消す。
			if(disp.length==1) $(disp).html('');

			//テキストを変える
			if(wmsLang_wms=="ja"){
				txt = "すべてチェックする";
			}else{
				txt = "Check All";
			}
			//nothing->.check
			$(this).text(txt).addClass('check');
		}
			return false;
	});


});

})(jQuery);
