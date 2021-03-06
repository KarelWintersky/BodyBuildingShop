$(function() {	
	$('#form_OrderDelivery').submit(function(){
		truncate_errors(this);
		
		var current_method = $('input[name="delivery"]:checked').val();
		
		if(current_method==2) return delivery_courier(this);
		else if(current_method==4) return delivery_self(this);
		
		return true;
	});
	
	$('.address_catch').change(function(){
		var block = $(this).parents('.address_catch_parent');
		
		var zipcode = $(block).find('.fl_zipcode input').val();
		var city = $(block).find('.fl_city input').val();
		
		$.ajax({
			url: '/ajax/',
		    type:'POST',
			dataType:'json',
			data:  {
				method:'delivery_courier',
				zipcode:zipcode,
				city:city
				},
			async:true,
			success:function(resp){
				$(block).parents('.fod_item').find('.fod_item_cost').html(resp.cost);
				$(block).parents('.fod_item').find('.fod_item_text').html(resp.text);
			}
		});		
	});
});

var truncate_errors = function(form){
	$(form).find('.errF').hide();
	
	$(form).find('.err').removeClass('err');
};

var delivery_courier = function(form){
	var flag = true;
	
	var block = $(form).find('#courier_fields');
	
	var name = $(block).find('.fl_name input');
	var phone = $(block).find('.fl_phone input');
	var city = $(block).find('.fl_city input');
	
	if(!$(name).val()){
		$(name).addClass('err');
		flag = false;
	}
	
	if(!$(phone).val()){
		$(phone).addClass('err');
		flag = false;
	}
	
	if(!$(city).val()){
		$(city).addClass('err');
		flag = false;
	}	
	
	if(!flag) $(block).find('.errF').show();
	
	return flag;
};

var delivery_self = function(form){
	var flag = true;
	
	var block = $(form).find('#self_fields');
	
	var name = $(block).find('.fl_name input');
	var phone = $(block).find('.fl_phone input');
	
	if(!$(name).val()){
		$(name).addClass('err');
		flag = false;
	}
	
	if(!$(phone).val()){
		$(phone).addClass('err');
		flag = false;
	}	
	
	if(!flag) $(block).find('.errF').show();
	
	return flag;
};