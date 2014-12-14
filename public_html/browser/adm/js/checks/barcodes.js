var barcodes_check = function(){
	var flag = true;
	
	$('.table_barcodes').find('.err').removeClass('err');
	$('.table_barcodes').find('.td_err').html('');
	
	$('.table_barcodes tr.value_line').each(function(e,line){
		var deleted = $(line).find('.val_del').is(':checked');
		
		if(!deleted){
			var barcode = $(line).find('.val_barcode');
			var barcode_old = $(line).find('.val_barcode_old');
			var packing = $(line).find('.val_packing');
			var weight = $(line).find('.val_weight');
			var price = $(line).find('.val_price');
			
			if(!$(packing).val()){
				flag = false;
				$(packing).addClass('err');
			}
	
			if(!$(weight).val()){
				flag = false;
				$(weight).addClass('err');
			}		
			
			if(!$(price).val()){
				flag = false;
				$(price).addClass('err');
			}		
					
			if(!$(barcode).val()){
				flag = false;
				$(barcode).addClass('err');			
			}else if(!if_barcode_exists(barcode,barcode_old)) flag = false;
		}
	});
	
	return flag;
};

var if_barcode_exists = function(barcode,barcode_old,goods_id){
	var flag = true;
	
	$.ajax({
		url: '/ajax/',
	    type:'POST',
		dataType:'text',
		data:  {
			method:'goods_barcode_check',
			barcode:$(barcode).val(),
			barcode_old:$(barcode_old).val()
			},
		async:false,
		success:function(resp){
			flag = (resp==1);
			
			if(!flag){
				$(barcode).addClass('err');
				$(barcode).parents('tr').find('.td_err').html('Дубль штрихкода');
			}
		}
	});	
	
	return flag;
};