$(function() {	
	$('#form_OrderDelivery').submit(function(){
		var current_method = $('input[name="delivery"]:checked').val();
		
		if(current_method==2) delivery_courier(this);
		else if(current_method==4) delivery_self(this);
		
		return false;
	});	
});

var delivery_courier = function(form){
	var block = $(form).find('#courier_fields');
	
	var name = $(block).find('.fl_name input');
	var phone = $(block).find('.fl_phone input');
	var city = $(block).find('.fl_city input');
	
	if(!$(name).val()){
		$(name).addClass('err');
	}
	
	if(!$(phone).val()){
		$(phone).addClass('err');
	}
	
	if(!$(city).val()){
		$(city).addClass('err');
	}	
};

var delivery_self = function(form){
	var block = $(form).find('#self_fields');
	
	var name = $(block).find('.fl_name input');
	var phone = $(block).find('.fl_phone input');
	
	if(!$(name).val()){
		$(name).addClass('err');
	}
	
	if(!$(phone).val()){
		$(phone).addClass('err');
	}	
};