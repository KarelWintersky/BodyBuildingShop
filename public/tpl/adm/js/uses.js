function listSrt(){
	$('.list_sort li').each(function(n){
		n++;
		$(this).find('.input_sort').val(n);
	});
}

function tableSrt(){
	$('.catalog_table tr').each(function(n){
		n++;
		$(this).find('.input_sort').val(n);
	});
}

function barcodesSrt(){
	$('.table_barcodes tr').each(function(n){
		n++;
		$(this).find('.input_sort').val(n);
	});	
}

function delConfirm(el){
	$(el).parent().find('.del_confirm').slideDown('slow');
	return false;
}

function delConfirmHide(el){
	$(el).parents('.del_confirm').slideUp('slow');
	return false;
}

function form_sbm(f){$(f).submit();return false;}

$(document).ready(function(){

	dp_Attach();

	$(".list_sort").sortable({
		cursor: 'move',
		stop: function(event, ui){listSrt();}
	});

	$(".catalog_table").sortable({
		cursor: 'move',
		items: '.table_sort',
		stop: function(event, ui){tableSrt();}
	});

	$(".table_barcodes").sortable({
		cursor: 'move',
		items: 'tr',
		stop: function(event, ui){barcodesSrt();}
	});
	
	
});

function form_tog(elem,tog_id,user_id){

	var cookie_exp;

	var togBlock = $(elem).parents('.form_1_tog').find('.form_1_tog_i');

	if($(togBlock).is(':visible')){
		$(elem).html('развернуть');
		cookie_exp = -1;
	}else{
		$(elem).html('свернуть');
		cookie_exp = 60;
	}

	$.cookie('form_tog_opened['+user_id+']['+tog_id+']', '1', {
		expires: cookie_exp,
		path: '/',
	});

	$(togBlock).slideToggle(1100);

	return false;

}

function feat_img_caption(elem){
	$(elem).parents('.feature_line').find('.feat_img_caption').show();
}

function dp_Attach(){
	$("input.datepicker").datePicker({startDate:'01/01/2012',clickInput:true});
}

function set_orders_paging(t,form){
	var pv = $(form).find('select[name="pag_val"]').val();
	var level_link = $(form).find('input[name="level_link"]').val();

	$.cookie('adm_orders_paging['+t+']', pv, {
		expires: 300,
		path: '/',
	});

	window.location.href = level_link;
}

function good_features_spoiler(){
	var block = $('#block_GoodFeatures_Spoiler');
	var trigger = $('#block_GoodFeatures_Trigger a');

	if($(block).is(':visible')){
		$(block).slideUp(800);
		$(trigger).html('развернуть');
	}else{
		$(block).slideDown(800);
		$(trigger).html('скрыть');
	}

	return false;
}

function stat_goods_table(){
	var goods_ids = jQuery.parseJSON($('input[name="stat_goods_ids"]').val());

	if(goods_ids.length>0){
		$.each(goods_ids,function(i,id){
			$.ajax({
				url: '/ajax/',
			    type:'POST',
				dataType:'json',
				data:  {method:'stat_goods_table',goods_id:id},
				success:function(resp){
					var line = '<tr>'+
													'<td>'+
														'<a href="/adm/catalog/'+resp['goods_parent_id']+'/'+resp['goods_level_id']+'/'+resp['id']+'/" target="_blank">'+
															resp['name']+
														'</a>'+
													'</td>'+
													'<td>'+resp['p1']+'</td>'+
													'<td>'+resp['p2']+'</td>'+
													'<td>'+resp['p3']+'</td>'+
													'<td>'+
														'<input type="checkbox" name="del['+resp['id']+']">'+
													'</td>'+
												'</tr>';
					$('.stat_goods_table').append(line);
				}
			});
		});
	}

}

function barcode_add(goods_id){

	$.ajax({
		url: '/ajax/',
	    type:'POST',
		dataType:'text',
		data:  {method:'barcode_add',goods_id:goods_id},
		success:function(resp){
			$('.table_barcodes').append(resp);
		}
	});

	return false;
}