(function($){

var memory = {};
	memory['old_id'] = "";
	memory['old_key'] = "";
	memory['old_txt'] = "";
var memFlg = false;

//カスタムフィールドクリック処理
$(function(){

	//update,delete
	function post_field(val){
		$('#postcustom #the-list tr').each(function(i){
				var input = $(this).children('td').children('input').val();
				var text = $(this).children('td').children('textarea').val();
				if(val['old_key']==input
					&& val['old_txt']==text
					&& val['id'] == $(this).index() ){

					switch(val['request']){
						case '.updatemeta':
							$(this).children('td').children('textarea:first').val(val['textarea']);
							$(this).children('td').children('input:first').val(val['input']);
						break;
					}

					$(this).children('.left').children('.submit').find(val['request']).trigger("click");
					return false;
				}
		});
	}

	//デリートボタン
	$('.cf-delete').live('click',function(){
		var del = {}; // ここで連想配列を代入
		del['request'] = ".deletemeta";
		del['slug'] = wmsSlug_wms;
		del['id'] = $(this).parent().index(); //cf_updated_box内＝配列要素番号 //data-field用
		del['input'] = $(this).siblings('input').val(); //inputボックスの中の値を取得（変更後の名前）
		del['textarea'] = $(this).siblings('textarea').val(); //textareaボックスの中の値を取得（変更後の名前）

		if( 'cf_'+del['id'] != memory['old_id'] ) {
			 //一度もフォーカスしないでdeleteボタンを押した場合
			del['old_key'] = del['input'];
			del['old_txt'] = del['textarea'];
		}else{
			 //フォーカスしてからdeleteボタンを押した場合
			 //または同じエリア内でupdateボタンを押した直後にdeleteボタンを押した場合
			del['old_key'] = memory['old_key'];
			del['old_txt'] = memory['old_txt'];
		}

		var del_confirm = "";
		var cut_num = 15;
		var del_area = "\n"+"Key: " + del['input'] +"\n"+"Text: ";
		if(cut_num < del['textarea'].length){
			del_area += del['textarea'].substr(0,cut_num)+"...";
		}else{
			del_area += del['textarea'];
		}

		if(wmsLang_wms=="ja"){
			del_confirm = confirm('この項目を削除します。よろしいですか？'+del_area) ;
		}else{
			del_confirm = confirm('Would you like to delete this field?'+del_area) ;
		}

		if ( del_confirm == true) {
			//新規作成未確定時は送信しない。
			if(wmsPostnewphpFlg_wms==false){
				post_field(del); //配列delを渡す
			}
			$(this).parent()
			.css( 'background', 'pink' )
			.fadeOut(650,function(){
				$(this).remove();
				$('#cf_updated_box').children('div').each(function(i){
				//idの連番が要素数と一致してないものだけ処理
					if($(this).attr('id') != 'cf_'+ i){
						$(this).attr('id', 'cf_'+ i);
						$(this).children('.key_num')
								.fadeOut(650,function(){
								$(this).text( (i+1)+'. ' );
								$(this).fadeIn(650);
								});
					}
				});
			});
			$('#cf_updated_index')
			.children('#cf_li'+del['id'])
			.fadeOut(650,function(){
				$(this).remove(); //indexの自分へのリンクも削除。
				$('#cf_updated_index').fadeOut(650,function(){
					var li_txt = "";
					$(this).children('li').each(function(i){
						$(this).attr('id', 'cf_li'+ i)
						$(this).children('a').attr('href', '#cf_'+ i);
						li_txt = $(this).text().match(/[^0-9\.].*/);
						$(this).children('a').text( (i+1) +'. '+ li_txt );
					});
					$(this).fadeIn(650);
				});
			});



		}
	});

	//アップデートボタン
	$('.cf-update').live('click',function(){
		var up = {}; // ここで連想配列を代入
		up['request'] = ".updatemeta";
		up['slug'] = wmsSlug_wms;
		up['id'] = $(this).parent().index(); //cf_updated_box内＝配列要素番号 //data-field用
		up['input'] = $(this).siblings('input').val(); //inputボックスの中の値を取得（変更後の名前）
		up['textarea'] = $(this).siblings('textarea').val(); //textareaボックスの中の値を取得（変更後の名前）
		var upFlg = false;
		memFlg = false; //初回時記録フラグ解除

		//入力された値が記録と異なる場合のみ処理
		if( up['input'] != memory['old_key']
			|| up['textarea'] != memory['old_txt']){

			if( 'cf_'+up['id'] == memory['old_id']) {
				up['old_key'] = memory['old_key'];
				up['old_txt'] = memory['old_txt'];
			}

			upFlg = true;
		}

		//エリアに値があるかどうかのチェック
		//同じキー名が他にもあるかどうかのチェック
		if( box_check(up) && search_same_key_text(up)){
			//入力されたキーとエリアが全く同じ場合、新規作成未確定時は送信しない。
			if(wmsPostnewphpFlg_wms==false){
				if(upFlg){
					post_field(up); //配列upを渡す
					//フォーカス時に記録するキーとテキストの値も更新する
					memory['old_key'] = up['input'];
					memory['old_txt'] = up['textarea'];
				}
			}

			$(this).parent()
			.css( 'background-color', 'yellow' )
			.animate({'background-color': 'none'}, 650 );
			$(this).siblings('.key').html(up['input']); //.keyの中に値を代入（変更後の名前）
			var txt = (up['id']+1)+'. '+ up['input']; //更新されたキー名を連番にして格納
			$('#cf_updated_index').children('#cf_li'+up['id'])
			.fadeOut(0,function(){
				$(this).fadeIn(650)
				.children('a').html(txt); //アンカーテキストを更新
			});
		}
	});


	//同一キー検索処理
	function search_same_key_text(ele){
		var otherkey = "";
		var othertext = "";
		var find_name = "";
		var same_cnt = 0;

		$('#cf_updated_box .cf-field').each(function(i){
			//自分の要素番号以外を処理
			if( i != ele['id'] ){
				otherkey = $(this).children('input').val();
				if(ele['input'] == otherkey || ['old_key'] == otherkey) {
					othertext = $(this).children('textarea').val();
					if(ele['textarea'] == othertext || ['old_txt'] == othertext) {
						var cut_num = 15;
						find_name += (i+1)+'. '+otherkey+'\n';
						if(cut_num < othertext.length){
							find_name += othertext.substr(0,cut_num)+"..."+'\n';
						}else{
							find_name += othertext+'\n';
						}
						same_cnt++
					}
				}
			}
		});

		if(same_cnt==0){
			return true;
		}else{
			var txt ="";
			if(wmsLang_wms=="ja"){
				txt = same_cnt +"件 同じkey名とテキストの組合せが見つかりました。";
				txt += "\n他と重複しますが、入力してもよろしいですか？";
				txt += "\n\n一致したキー名と値\n";
			}else{
				txt = "Found "+ same_cnt +" same combination of key name and text.";
				txt += "\nThe overlap with the other, but Are you sure you want to input please.";
				txt += "\n\nAccorded key name and text\n";
			}

			var ask_confirm = confirm(txt+find_name) ;
			if(ask_confirm == true ) return true;
		return false;
		}
	}

	//インプットエリア変換
	$('.cf-field .key').each(function(i){
		var thisValueLength = $(this).html().length;
		$(this).siblings('.cnt_key').html(thisValueLength);
		var txt = $(this).html().replace(/"/g, '&quot;');
		$(this).after('<input type="text" value="'+txt+'" />');
		$(this).siblings('input').css('width','35%')
		$(this).remove();
	});

	//テキストエリア変換
	$('.cf-field pre').each(function(i){
		var thisValueLength = $(this).html().replace(/[\r|\n]/g, '').length;
		$(this).siblings('.cnt_txt').html(thisValueLength);
		to_textarea(this);
	});
	$('#cf_fields_new textarea').css('height','106px');


	//文字数カウント
	$('.cf-field textarea, .cf-field input').live('keydown keyup keypress change',function(){
		var thisValueLength = $(this).val().replace(/[\r|\n]/g, '').length; //変更：改行はカウントしない。
		var tagname = $(this).get(0).tagName;
		//変更：キーごとに分岐
		if(tagname=="INPUT"){
			$(this).siblings('.cnt_key').html(thisValueLength);
		}else if(tagname=="TEXTAREA"){
			$(this).siblings('.cnt_txt').html(thisValueLength);
		}
	});

	//テキストエリア生成リサイズ処理
	function to_textarea(ele){
		var txt = $(ele).html().replace(/&amp;/g, '&'); //php側でエスケープした&を元に戻して取得。
		if(txt==""){
			var preh = $(ele).html("dammytxt").outerHeight();
			$(ele).html("");
		}else{
			//pre内でhtmlソースをテキスト表示させるため、<>の2文字を特殊文字に変換。
			var plaintxt = txt.replace(/</g, '&lt;').replace(/>/g, '&gt;');
			var preh = $(ele).html(plaintxt).outerHeight();
		}
		var cssObj = {
			height: preh,
		}
		$(ele).after('<textarea></textarea>');
		$(ele).siblings('textarea').val(txt).css(cssObj);
		$(ele).remove();
	}

	function box_check(box){
		var box_check = {};
		if(wmsLang_wms=="ja"){
			box_check['key'] = "keyに文字を入力して下さい。";
			box_check['txt'] = "テキストエリアに文字を入力して下さい。";
		}else{
			box_check['key'] = "Please enter the characters in the key.";
			box_check['txt'] = "Please enter the characters in the textarea.";
		}

		if( box['input'].replace(/\s/g,'') === "") {
			alert(box_check['key']);
			return false;
		} else if( box['textarea'].replace(/\s/g,'') === "" ) {
			alert(box_check['txt']);
			return false;
		}else{
			return true;
		}
	}


	//add-new
	function add_field(val){
		var target = $('#postcustom #newmeta tbody tr').children('td');
		target.find('input#metakeyinput').val(val['input']);
		target.find('textarea#metavalue').val(val['textarea']);
		target.children('.submit').find(val['request']).trigger("click");
	}

	//新規作成ボタン
	$('.cf-add-new').live('click',function(){
		var add = {}; // ここで連想配列を代入
		add['request'] = "#newmeta-submit";
		add['slug'] = wmsSlug_wms;
		add['id'] = $('#cf_updated_box > div').index()+1; //cf_updated_box内のdivの個数+1＝自分の番号 //data-field用
		add['input'] = $(this).siblings('input').val(); //inputボックスの中の値を取得（変更後の名前）
		add['textarea'] = $(this).siblings('textarea').val(); //inputボックスの中の値を取得（変更後の名前）
		var updated_cnt = $('#cf_updated_box > div').length;

		if( box_check(add) && search_same_key_text(add) ){
			//新規作成未確定時は送信しない。
			if(wmsPostnewphpFlg_wms==false){
				add_field(add); //配列addを渡す
			}

			$(this).before('<button type="button" class="cf-delete">Delete</button>');
			$(this).before('<button type="button" class="cf-update">Update</button>');

			$(this).parent().clone()
			.attr('id', 'cf_'+ updated_cnt)
			.appendTo( '#cf_updated_box' )
			.css( 'background-color', 'yellow' )
			.animate({'background-color': 'none'}, 650 );
			$('#cf_updated_box .cf-add-new').siblings('pre').remove(); //clone作成時にadd-newのresize_area()が処理途中の場合、残ってしまうpreタグを削除

			var newarea = $('#cf_updated_box .cf-add-new').siblings('textarea').html( add['textarea'].replace(/&/g, '&amp;') );
			resize_area(newarea);

			$('#cf_updated_box .cf-add-new').siblings('.key').html( add['input'] ); //.keyの中に値を代入（変更後の名前）
			$('#cf_updated_box .cf-add-new').siblings('#a_new').remove(); //#cf_fields_newボックスのArea top削除
			$('#cf_updated_box .cf-add-new').siblings('input').before( (updated_cnt+1) + '. ' );
			$('#cf_updated_box .cf-add-new').parent().append('<a href="#cf_' + updated_cnt + '">Area top</a>'); //新規作成ボックスのArea top追加
			$('#cf_updated_index').append('<li id="cf_li'+updated_cnt+'"><a href="#cf_' + updated_cnt + '">'+ (updated_cnt+1) +'. '+ add['input'] +'</a></li>');
			$('#cf_updated_index')
			.children('#cf_li'+updated_cnt)
			.fadeOut(0,function(){
				$(this).fadeIn(650);
			});

			$('#cf_updated_box p strong').parent().remove();
			$('#cf_updated_box .cf-add-new').siblings('.key_num').remove();
			$('#cf_updated_box .cf-add-new').remove();
			$('#cf_fields_new .cf-delete').remove();
			$('#cf_fields_new .cf-update').remove();
			$('#cf_fields_new input').val('');
			$('#cf_fields_new textarea').val('');
			$('#cf_fields_new .cnt_key').html('0');
			$('#cf_fields_new .cnt_txt').html('0');

			//元のサイズに戻す
			$(this).siblings('textarea').animate({ 
				height: '106px',
			}, 500 );

		}

	});

	//インプットボックス、フィールドフォーカス時
	$('.cf-field input, .cf-field textarea')
	.live('focus', function () {
		//毎回同じエリアでは、初回フォーカスのみエリア内のデータを記録
		var nowelem = $(this).closest('[id]').attr('id');
		if(nowelem != memory['old_id']) memFlg = false;

		if( memFlg == false){
			memory['old_id'] = $(this).closest('[id]').attr('id');
			memory['old_key'] = $('#cf_updated_box '+'#'+memory['old_id']).children('input').val();
			memory['old_txt'] = $('#cf_updated_box '+'#'+memory['old_id']).children('textarea').val();
			memFlg = true;
		}

	} );

	//フィールドフォーカス時
	$('.cf-field textarea')
	.live('focus', function (){
		var hf = ($(this).outerHeight()+100);
		$(this).animate({ 
			height: hf,
		}, 500 );
	}).live('keyup', function(e){
		//Enterキー[13]が上がった、もしくはキーが上がったときエリア内空白
		if(e.keyCode==13 || $(this).val()==""){
			resize_area(this);
		}
	}).live('blur', function (){
			resize_area(this);
	});

	function resize_area(area){
		//最後の処理が終了するまで続けて実行しない。
		if($(area).siblings('pre').length == 0){
			//pre内でhtmlソースをテキスト表示させるため、<>の2文字を特殊文字に変換。
			var thv = $(area).val().replace(/</g, '&lt;').replace(/>/g, '&gt;');
			$(area).after('<pre></pre>');
			if(thv==""){
				var he = $(area).siblings('pre').html("dammytxt").outerHeight(); //"dammytxt"で高さ生成
				$(area).siblings('pre').html("");
			}else{
				var he = $(area).siblings('pre').html(thv +'\n'+"dammytxt").outerHeight(); //"dammytxt"で改行直後の行に高さ生成。
			}
			$(area).siblings('pre').hide();
			var id = $(area).closest('[id]').attr('id');
			if($(area).val()=="" && id=='cf_fields_new') var he = '106px';

			var wi = $(area).css('width');
			$(area).animate({ 
				width: wi,
				height: he
			}, 500, function(){
				//処理完了時点でエリア内の高さが変わっている場合（処理中に連続改行などがあった場合）
				var thv2 = $(area).val().replace(/[<>]/g, ' ');
				if(thv2==""){
					var he2 = $(area).siblings('pre').html("dammytxt").outerHeight();
					$(area).siblings('pre').html("");
				}else{
					var he2 = $(area).siblings('pre').html(thv2 +'\n'+"dammytxt").outerHeight();
				}
				if(he<he2){
					$(area).animate({ 
						height: he2,
					}, 500, function(){
						$(area).siblings('pre').remove();
					});
				}else{
					$(area).siblings('pre').remove();
				}
			});
		}
	}


	$.resize_area_all = function(){
		var windowTop = $(window).scrollTop();
		var gap = 0;
		$('#cf_updated_box .cf-field, #cf_fields_new')
				.map(function(index, el) {
	        		var area = $(this).children('textarea');
					var thv = $(area).val().replace(/[<>]/g, ' ');
					$(area).after('<pre></pre>');
					if(thv==""){
						var he = $(area).siblings('pre').html("dammytxt").outerHeight();
					}else{
						var he = $(area).siblings('pre').html(thv).outerHeight();
					}
					$(area).siblings('pre').hide();

					//リサイズ前と後の高さの差分取得
					if(windowTop>area.offset().top){
						if(he!=area.outerHeight()){
							gap += he - area.outerHeight();
						}
					}
					var id = $(area).closest('[id]').attr('id');
					if($(area).val()=="" && id=='cf_fields_new') he = 80;

					$(area).css('width', '100%');
					$(area).animate({ 
						height: he
					}, 500, function(){
						$(area).siblings('pre').remove();
					});
				});

		//リサイズ後のウィンドウ位置へスクロール
		var position = $(window).scrollTop()+gap;
		$('body,html').animate({scrollTop:position}, 500);

	}

});



})(jQuery);