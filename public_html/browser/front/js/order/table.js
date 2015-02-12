$(function() {
	$('#order_goods_refresh_link a').click(function(e){
		if(!table_check()) return false;
		
		cart_restruct(false);	
		
		e.preventDefault();
	});
	
	$('#order_goods_table .td_del a').click(function(e){
		var key_to_delete = $(this).parents('.goods_line').find('.line_key').val();
		
		cart_restruct(key_to_delete);
		
		$(this).parents('.goods_line').remove();
		
		e.preventDefault();
	});
	
});

var table_check = function(){
	var flag = true;
	
	$('#order_goods_table').find('.goods_amount').each(function(i,inp){
		var val = $(inp).val(); 
			val = $.trim(val);
			val = val.replace('.',',');
			
		if(val=='' || !$.isNumeric(val) || !Math.floor(val)==val){
			$(inp).addClass('err');
			flag = false;
		}else{
			$(inp).removeClass('err');
		}
		 		
		
	});
	
	return flag;
};

var cart_restruct = function(key_to_delete){
	var goods = {};
	$('#order_goods_table').find('.goods_line').each(function(i,line){
		var key = $(line).find('.line_key').val();
		var amount = $(line).find('.goods_amount').val();
		
		if(key==key_to_delete) amount = 0;
		
		goods[key] = amount;
	});
	
	$.ajax({
		url: '/ajax/',
	    type:'POST',
		dataType:'json',
		data:  {method:'cart_restruct',goods:goods},
		async:true,
		success:function(resp){
			if(resp.empty){
				location.href = '/order/';
			}else{
				$('#order_goods_values_container').html(resp.values);
				$('#order_goods_gift_container').html(resp.gift);
				
				$('#cart_gift_select select').chosen({disable_search:true});				
			}
		}
	});		
	
};