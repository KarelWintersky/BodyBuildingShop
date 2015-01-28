var this_page_url;

$(function() {

	this_page_url = $.parseJSON($('input[name="this_page_url"]').val());

	long_cart_generate();

});

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
				//dropdown_blocks();
				$('#cart_preloader').hide();
				delivery_payment_match();
				
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

function cart_check_submit(form){
	$('#cart_preloader').show();
	var button = $(form).find('input[type="submit"]');
	$(button).val('Подождите, идет перерасчет...').attr('disabled','disabled');
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