var sb_catalog_tog = function(toggler,tog_block_id,level_id){

	var block = $(tog_block_id);
	var cookie_exp;

	if($(block).is(':visible')){
		v_add_class = 'block_closed';
		v_rem_class = 'block_opened';
		cookie_exp = 300;
	}else{
		v_add_class = 'block_opened';
		v_rem_class = 'block_closed';
		cookie_exp = -1;
	}

	$.cookie('sb_catalog_tog['+level_id+']', '1', {
		expires: cookie_exp,
		path: '/'
	});

	$(toggler).removeClass(v_rem_class).addClass(v_add_class);

	$(block).slideToggle(1100);
};

var goods_tog_block = function(toggler,tog_block_id,goods_block_id){

	var block = $(tog_block_id);

	if($(block).is(':visible')){
		cookie_exp = 300;
		$(toggler).parents('.goods_tog_block_h').removeClass('vis').addClass('invis');
	}else{
		cookie_exp = -1;
		$(toggler).parents('.goods_tog_block_h').removeClass('invis').addClass('vis');
	}

	$.cookie('goods_block['+goods_block_id+']', '1', {
		expires: cookie_exp,
		path: '/'
	});

	$(block).slideToggle(700);

	return false;
};

function form_sbm(f){$(f).submit();return false;}

function feat_change_list(elem){
	var string_array = [];
	$(elem).parents('.goods_item_feats').find('input[type="hidden"]').each(function(i,e){
		string_array.push($(e).val());
	});
	string = string_array.join(',');

	$(elem).parents('.goods_parent').find('input.goods_feats_string').val(string);

}

function goods_ostatok_check(goods_id,cookie_stored_data){
	var reply = true;
	$.ajax({
		url: '/ajax/',
	    type:'POST',
		dataType:'text',
		data:  {method:'goods_ostatok_check',goods_id:goods_id,cookie_stored_data:cookie_stored_data},
		async:false,
		success:function(resp){
			reply = (resp==1) ? true : false;
		}
	});

	return reply;
}

function add2cart(trigger,packing){

	var color_select = $(trigger).parents('.barcode_line').find('.barcode_line_colors').find('select');
	var barcode_select = $(trigger).parents('.barcode_line').find('.barcode_line_features').find('select');
	var barcode_input = $(trigger).parents('.barcode_line').find('.barcode_line_features').find('.first_barcode');
	
	var barcode = ($(barcode_select).length>0) 
		? $(barcode_select).val()
		: $(barcode_input).val();		

	var color = ($(color_select).length>0)
		? $(color_select).val()
		: false;		
				
	var cookie_string = barcode+':'+packing;
	
	if(color) cookie_string = cookie_string+':'+color;

	var arr = {};
	var cart = ($.cookie('thecart')) ? $.cookie('thecart') : '';
	if(cart!=''){
		cart = cart.split('|');
		$.each(cart,function(i,elem){
			var tmparr = elem.split(':');
			
			arr[i] = {
				'barcode' : tmparr[0],
				'packing' : tmparr[1],
				'amount'  : tmparr[2],
				'color'   : (tmparr[3]!=undefined) ? tmparr[3] : false
				};
		});		
	}
	
	var added = false;
	var j = 1;
	$.each(arr,function(i,elem){
		if(barcode==elem['barcode']){
			if(color){
				if(elem['color'] && color==elem['color']){
					arr[i]['amount'] = arr[i]['amount'] + 1;
					added = true;					
				}
			}else{
				arr[i]['amount'] = parseInt(arr[i]['amount']) + 1;
				added = true;
			}
		}
		j++;
	});
	
	if(!added)
		arr[j] = {
			'barcode' : barcode,
			'packing' : packing,
			'amount'  : 1,
			'color'   : color				
			};
	
	var output = [];
	$.each(arr,function(i,elem){
		if(!elem.color) delete elem.color;
	
		var the_arr = [];
		$.each(elem,function(key,val){
			the_arr.push(val);
		});
		
		output.push(the_arr.join(':'));
	});
	output = output.join('|');
	
	$.cookie('thecart', output, {
		expires: 300,
		path: '/'
	});

	//обноваляем кнопки
	var lnk_class = '.'+$(trigger).attr('class');
	$(lnk_class).each(function(i,e){
		var added_am = ($(e).find('.added').size()>0) ? true : false;
		stored_am = (added_am) ? $(e).find('.added .added_i').html() : 0;
		stored_am++;

		if(added_am){
			$(e).find('.added .added_i').html(stored_am);
		}else{
			$(e).append('<div class="added"><div class="added_i">'+stored_am+'</div></div>');
		}

	});

	cart_generate();

	return false;
}

function cart_generate(){
	$.ajax({
		url: '/ajax/',
	    type:'POST',
		dataType:'text',
		data:  {method:'cart_construct'},
		async:false,
		success:function(html){
			$('#head_cart_content').html(html);
		}
	});
}



function form_AccountOrder(form){

	var sum = $(form).find('input[name="sum"]');
	var flag = true;

	if(!numCheck($(sum).val()) || $(sum).val()==0 || $(sum).val()==''){
		flag = false;
		$(sum).addClass('err');
	}

	return flag;
}

function numCheck(val){
	var nReg = /^[0-9]+$/;
	if(nReg.test(val)){return true;}else{return false;}
}

function form_profileInfo_preloader(form){
	$('#cart_preloader').addClass('profile');
	$('#cart_preloader').show();
	var button = $(form).find('input[type="submit"]');
	$(button).val('Подождите, данные сохраняются...').attr('disabled','disabled');

	return true;
}

$(function() {

	$('.ggm_item a').click(function(e){

		var that = $(this);

		$('#goods_gallery_big a').removeClass('active');

		$('#goods_gallery_big a').each(function(i,elem){
			if($(elem).attr('id')==$(that).attr('data-rel')){

				$(elem).addClass('active');

			}
		});

		e.preventDefault();

	});

	cart_generate();

	$('.td_bill a.td_bill_lnk').click(function(e){
		var order_id = $(this).attr('rel');
		window.open('/openbill/?o='+order_id,'mywindow','width=700,height=600');

		e.preventDefault();
	});

	$('.order_bill_link').click(function(e){
		var order_id = $(this).attr('rel');
		window.open('/openbill/?o='+order_id,'mywindow','width=700,height=600');

		e.preventDefault();
	});

	$('select#goto_faq').change(function(e){
		var this_page_url = $('#this_page_url').val();
		
		var alias = $(this).val();
		
		if(this_page_url!='faq') { if(alias) location.href = alias; }
		else{
			var hash = alias.split("#");
			hash = hash[1];
			
			var elem = $('a[name="'+hash+'"]');
				
			$('html, body').animate({scrollTop:$(elem).offset().top}, 600);
			
			window.location.hash = hash;
		}
	});	
	
	$('select#goto_grower').change(function(e){
		var alias = $(this).val();
		if(alias) location.href = '/growers/'+alias+'/';
	});
	
	$('select').chosen({disable_search:true});

});