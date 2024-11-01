(function($){

//All_posts一覧項目表示切り替え
$(function(){

	//All_posts上部のdisplay[]内項目クリック
	$('#all_posts .ap_display').live('click',function () {
		var target_id = '#'+$(this).attr('id');
		var target_class = '.'+$(this).attr('id');

		if($(this).hasClass('off')){
			$(target_class).fadeIn(); //アンカー要素を表示
			$('#all_posts '+target_id).css('font-weight','');
			$('#all_posts '+target_id).removeClass('off');
		}else{
			$(target_class).fadeOut(); //アンカー要素を表示
			$('#all_posts '+target_id).css('font-weight','bold');
			$('#all_posts '+target_id).addClass('off');
		}
		return false;
	});

	//All_posts上部の[]内項目tabクリック
	$('#all_posts #ap_tab').live('click',function () {

		if($(this).hasClass('on')){
			var back = $('#ap_delimiter').html().replace(/"/g, ''); //アンカー要素を表示
			$('#all_posts .ap_delimiter').animate({opacity:1}, 400);
			$('#all_posts .ap_delimiter').html(back);
			$('#all_posts #ap_tab').css('font-weight','');
			$('#all_posts #ap_tab').removeClass('on');
		}else{
			var replace = '\t';
			$('#all_posts .ap_delimiter').animate({opacity:0}, 400,function(){
				$(this).show();
				$(this).html(replace);
			});
			$('#all_posts #ap_tab').css('font-weight','bold');
			$('#all_posts #ap_tab').addClass('on');
		}
		return false;
	});

	//All_posts上部のsort[]内項目クリック
	$('#all_posts .ap_sort').live('click',function () {
		var target = $(this).parent().next().children('.postlist-wrap');
		var target_class = '.'+$(this).attr('id').replace('_sort','');
		var txt = "";
		var ascFlg = false;

		if($(this).siblings('.pre')[0]){
			var pre = $(this).siblings('.pre');
			pre.css('font-weight','');
			txt = pre.text().substr(0,pre.text().length-1);
			pre.text(txt);
			pre.removeClass('pre');
			pre.removeClass('asc');
		}
		if( $(this).css("font-weight")=='bold' ){
			txt = $(this).text().substr(0,$(this).text().length-1);
			$(this).text(txt);
		}

		if($(this).hasClass('asc')){
			$(this).css('font-weight','bold').append('&darr;');
			$(this).removeClass('asc');
		}else{
			$(this).css('font-weight','bold').append('&uarr;');
			$(this).addClass('asc');
			$(this).addClass('pre');
			ascFlg = true;
		}

		$(this).parent().next().animate({opacity:0}, 250, function(){

			target.html(
				target.children('.postlist').sort(function(a, b){
					var a2 = $(a).children(target_class).html().replace(/<span class=\"ap_delimiter.+<\/span>/,'');
					var b2 = $(b).children(target_class).html().replace(/<span class=\"ap_delimiter.+<\/span>/,'');

					if( $.isNumeric(a2) ){
						if(ascFlg){
							return a2 - b2;
						}else{
							return b2 - a2;
						}
					}else{
						if(ascFlg){
							return a2 > b2;
						}else{
							return b2 > a2;
						}
					}
				})
			);
		});
		$(this).parent().next().animate({opacity:1}, 250);

		return false;
	});

});

	//showlist
	$('#all_posts .ap_tax_show_list').live('click',function () {
		var target = $(this).parent().parent().attr('id');
		var showlist_id = target+'_showlist';

		if($(this).hasClass('show')){
			var replace = $(this).html().replace('hide','show');
			$(this).html(replace).children('a').css('font-weight','');
			$(this).removeClass('show');
			$(this).parent().next('#'+ showlist_id)
					.animate({opacity:0},350,function(){
						$(this).remove();
					});
		}else{
			var replace = $(this).html().replace('show','hide');
			$(this).html(replace).children('a').css('font-weight','bold');
			$(this).addClass('show');

			if($(this).parent().next('#'+ showlist_id)[0]==undefined){
				$(this).parent().after('<span id="'+ showlist_id +'" class="showlist"></span>');
				var target_class = '.'+target;

				var postnum = 0;
				var postlist_wrap = "";
				var check = "";
				var displayHtml = "";
				var sortHtml = "";
				$(this).closest('.area_top')
				.find('.ap_area').find('.postlist')
				.each(function(){
					if( $(this).children('.ap_taxonomies').children(target_class)[0] ){
						postlist_wrap += '<span class="postlist">'+ $(this).html() +'</span>';
						postnum++;
					}
				});
				if(postnum==0) postnum = 'no';

				var postnumHtml = '<span class="postlist-num">'+ postnum +' items</span>';
				if(postnum==1){
					displayHtml = '<span class="showlist_display">'+ $(this).closest('.area_top').find('.ap_area').siblings('.ap_display').html() +'</span>';
				}else if(postnum>1){
					displayHtml = '<span class="showlist_display">'+ $(this).closest('.area_top').find('.ap_area').siblings('.ap_display').html() +'</span>';
					sortHtml = '<span class="showlist_sort">'+ $(this).closest('.area_top').find('.ap_area').siblings('.ap_sort').html() +'</span>';
				}
				var areahtml = '<span class="showlist_area">'+ postnumHtml+'<span class="postlist-wrap">'+ postlist_wrap +'</span></span>';

				$(this).parent().next('#'+ showlist_id).html(displayHtml+sortHtml+areahtml);
				$(this).parent().next('#'+ showlist_id).css({opacity:0}).animate({opacity:1},350);

			}
		}

		return false;
	});

})(jQuery);
