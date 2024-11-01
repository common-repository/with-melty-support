(function($){
//送信処理
$.wmsPost_wms = function (val, handle){
	if(!handle) handle = WMS.endpoint;

	$.ajax({
		type: 'post',
		url: handle, // リクエストURL ここを変数にして引数で渡すようにすれば、広がるかも
		data: { 'action': WMS.action, 'posts':val }, // データ
		dataType: 'text',

		success: function(data,dataType){
			//alert(data);

			//完了メッセージ処理
			var txt ="";
			var btntxt ="";
			var btn ="";
			if(wmsLang_wms=="ja"){
				txt = "データ作成完了しました。 引き続き、このページをご利用の場合は表示を更新してください。";
				btntxt = '更新する';
			}else{
				txt = "Was complete data creation. Please reload your display, if you use this page to continue.";
				btntxt = 'Reload';
			}
			btn = '<button onclick="location.reload(true); return false;" style="margin:0 5px;">'+btntxt+'</button>';

			$('.sendmes').fadeOut(650,function(){
				$(this).siblings('.sending').remove(); //ボタン制御フラグ用の要素を削除
				$('.sendmes').html('<br>'+ txt + btn).fadeIn(650,function(){
		 			$(this).removeClass('sendmes');
					$(this).addClass('completed');
				});
			});

		}
	});
}

})(jQuery);