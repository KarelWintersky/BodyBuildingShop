$(function() {	
	$('#coupon_trigger a').click(function(e){
		var coupon = $('#coupon_area').val();
		
		$.ajax({
			url: '/ajax/',
		    type:'POST',
		    dataType:'json',
			data:  {method:'apply_coupon',coupon:coupon},
			async:false,
			success:function(resp){
				$('#order_goods_values_container').html(resp.values);
				
				if(!resp.exists && coupon) $('#coupon_error').show();
				else $('#coupon_error').hide();
				
				if(resp.exists) $('#coupon_success').show();
				else $('#coupon_success').hide();				
			}
		});
		
		e.preventDefault();
	});	
	
});