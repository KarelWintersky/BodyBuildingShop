$(function() {	
	$('#coupon_trigger a').click(function(e){
		var coupon = $('#coupon_area').val();
		
		$.ajax({
			url: '/ajax/',
		    type:'POST',
			dataType:'text',
			data:  {method:'apply_coupon',coupon:coupon},
			async:false,
			success:function(resp){
				$('#order_goods_values_container').html(resp);
			}
		});
		
		e.preventDefault();
	});	
	
});