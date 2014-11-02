var this_page_url;

function level_sort_change(elem){
	var cookie_type = $('input[name="cookie_type"]').val();

	var sort_by = $(elem).val();
	var level_id = $('input[name="level_id"]').val();

	var level_link = $('input[name="level_link"]').val();

	$.cookie(cookie_type+'[sort]['+level_id+']', sort_by, {
		expires: 300,
		path: '/'
	});

	window.location.href = level_link;
}

function display_number_change(elem){
	var cookie_type = $('input[name="cookie_type"]').val();
	var display = $(elem).val();
	var level_id = $('input[name="level_id"]').val();
	var reqiure_file = $('input[name="reqiure_file"]').val();

	var level_link = $('input[name="level_link"]').val();

	$.cookie(cookie_type+'[display_number]['+reqiure_file+']['+level_id+']', display, {
		expires: 300,
		path: '/'
	});

	window.location.href = level_link;
}

function display_type_change(elem){
	var cookie_type = $('input[name="cookie_type"]').val();
	var display_type = $(elem).attr('id');
	var level_id = $('input[name="level_id"]').val();

	var level_link = $('input[name="level_link"]').val();

	$.cookie(cookie_type+'[display_type]['+level_id+']', display_type, {
		expires: 300,
		path: '/'
	});

	window.location.href = level_link;
}

function sb_catalog_tog(toggler,tog_block_id,level_id){

	var block = $(tog_block_id);
	var v_class;
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

}

function goods_tog_block(toggler,tog_block_id,goods_block_id){

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
}

function goto_faq(elem){
	var alias = $(elem).val();
	if(alias!=0) location.href = alias;
}

function goto_grower(elem){
	var grower_alias = $(elem).val();
	if(grower_alias!=0){
		location.href = '/growers/'+grower_alias+'/';
	}
}

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
		if(!elem.color) delete elem.color
	
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
		stored_am = (added_am) ? $(e).find('.added').html() : 0;
		stored_am++;

		if(added_am){
			$(e).find('.added').html(stored_am);
		}else{
			$(e).append('<div class="added">'+stored_am+'</div>');
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
			$('#basket_i').html(html);
		}
	});
}

function long_cart_generate(){
	if($('#long_cart_table_container').size()>0){

		$('#cart_preloader').show();

		var cart_courier_phone = ($('.cart_courier_phone').size()>0) ? $('.cart_courier_phone').val() : '';

		$.ajax({
			url: '/ajax/',
		    type:'POST',
			dataType:'text',
			data:  {method:'long_cart_construct',this_page_url:this_page_url,cart_courier_phone:cart_courier_phone},
			async:true,
			success:function(html){
				$('#long_cart_table_container').html(html);
				dropdown_blocks();
				$('#cart_preloader').hide();
				delivery_payment_match();
				tooltip_trigger_attach();
				
				$('.radio_block').find('.radio_line').each(function(i,elem){
					if($(elem).find('input[type="radio"]').is(':checked')){
						$(elem).addClass('active');
						
						var thehint = $(elem).find('.thehint').val(); 
						$(elem).parents('.radio_block').find('.radios_right').html(thehint);
					}
						
				});
			}
		});

	}
}

function add_gift_2_cart(trigger){
	var gift_id = $(trigger).val();

	$.cookie('cart_gift_id', gift_id, {
		expires: 300,
		path: '/'
	});

	long_cart_generate();

}

function long_cart_line_del(toggler){
	var cookie_string = $(toggler).parents('.long_cart_line').find('input[name="cookie_string"]').val();
	var this_cookie = $.cookie('thecart');
		var this_cookie_arr = this_cookie.split('|');
				
	var new_cookie_arr = [];
	$.each(this_cookie_arr,function(i,elem){
		if(elem!=cookie_string){
			new_cookie_arr.push(elem);
		}
	});

	var new_cookie_str = new_cookie_arr.join('|');

	$.cookie('thecart', new_cookie_str, {
		expires: 300,
		path: '/'
	});

	cart_generate();

	long_cart_generate();

	if(new_cookie_str==='' && this_page_url[this_page_url.length-1]!='cart'){location.href='/cart/';}

	return false;
}

function delivery_payment_match(){

	if($('#delivery_payment_err').size()>0){
		var payment_type = false;
		$(".payment_radio").each(function(i,el){
			if($(el).attr('checked')===true){
				payment_type = parseInt($(el).val());
			}
		});

		var delivery_type = false;
		$(".delivery_radio").each(function(i,el){
			if($(el).attr('checked')===true){
				delivery_type = $(el).val();
			}
		});

		var match = {
			'1': [1,2,3,4,6,7],
			'2': [2,3,4,5,6,7],
			'3': [2,3,4,6,7],
			'4': [2,3,4,5,6,7]
		};

		if($.inArray(payment_type, match[delivery_type])==-1){
			$('#delivery_payment_err').show();
		}else{
			$('#delivery_payment_err').hide();
		}
	}

}

function payment_type_change(trigger){
	var payment_type = $(trigger).val();

	$(trigger).parents('.radio_block').find('.radio_line').removeClass('active');
	$(trigger).parents('.radio_line').addClass('active');
	
	var thehint = $(trigger).parents('.radio_line').find('.thehint').val(); 
	$(trigger).parents('.radio_block').find('.radios_right').html(thehint);
	
	$.cookie('payment_type', payment_type, {
		expires: 300,
		path: '/'
	});

	delivery_payment_match();

}

function delivery_type_change(trigger){
	var delivery_type = $(trigger).val();
	
	$.cookie('delivery_type', delivery_type, {
		expires: 300,
		path: '/'
	});
	long_cart_generate();
}

function long_cart_line_refresh(){

	var cookie_arr = [];

	$('.long_cart_line').each(function(i,line){
		var cookie_string = $(line).find('input[name="cookie_string"]').val();
		var goods_amount = $(line).find('input[name="goods_amount"]').val();
		if(goods_amount>0){
			cookie_string = cookie_string.split(':');
			cookie_string[2] = goods_amount; 
			
			cookie_arr.push(cookie_string.join(':'));
		}
	});
	
	var new_cookie_str = cookie_arr.join('|');

	$.cookie('thecart', new_cookie_str, {
		expires: 300,
		path: '/'
	});

	cart_generate();

	long_cart_generate();

	if(new_cookie_str==='' && this_page_url[this_page_url.length-1]!='cart'){location.href='/cart/';}

	return false;
}

function long_cart_submit(form){

	var flag = true;

	$('#cart_err').hide();

	if($('#delivery_2').is(':checked') && $('input[name="phone"]').val()==''){
		$('#cart_err').html('Необходимо указать номер телефона для курьерской доставки.');
		$('#cart_err').show();
		flag = false;
	}

	return flag;
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

function cart_check_submit(form){
	$('#cart_preloader').show();
	var button = $(form).find('input[type="submit"]');
	$(button).val('Подождите, идет перерасчет...').attr('disabled','disabled');
}

function form_profileInfo_preloader(form){
	$('#cart_preloader').addClass('profile');
	$('#cart_preloader').show();
	var button = $(form).find('input[type="submit"]');
	$(button).val('Подождите, данные сохраняются...').attr('disabled','disabled');

	return true;
}

function tooltip_trigger_attach(){
	$('.tooltip_trigger').tooltip({
		relative: true,
		tipClass: 'tooltip_container'
	});
}

$(function() {

	this_page_url = $.parseJSON($('input[name="this_page_url"]').val());

	tooltip_trigger_attach();

	$('.ggm_item a').click(function(e){

		var that = $(this);

		$('#goods_gallery_big a').removeClass('active');

		$('#goods_gallery_big a').each(function(i,elem){
			if($(elem).attr('id')==$(that).attr('rel')){

				$(elem).addClass('active');

			}
		});

		e.preventDefault();

	});

	cart_generate();

	long_cart_generate();

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

	$('select.chzn').chosen({disable_search:true});

});