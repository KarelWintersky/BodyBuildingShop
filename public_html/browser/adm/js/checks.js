function check_single_name(form){

	var f = true;

	$(form).find('.single_name').each(function(e,elem){
		if($(elem).val()==''){
			$(elem).addClass('err');
			f = false;
		}else{
			$(elem).removeClass('err');
		}
	});

	return f;
}

function goods_form_check(form){

	var fl = true;

	var id = $(form).find('input[name="id"]').val();

	var name = $(form).find('input[name="name"]');
	if($(name).val()==''){
		$(name).addClass('err');
		$(name).parent().find('.errF').html('Необходимо указать название товара');
		fl = false;
	}else{
		$(name).removeClass('err');
		$(name).parent().find('.errF').html('');
	}

	var price = $(form).find('input[name="price_1"]');
	if($(price).val()=='' || !numCheck($(price).val())){
		$(price).addClass('err');
		$(price).parent().find('.errF').html('Необходимо указать цену в числовом формате');
		fl = false;
	}else{
		$(price).removeClass('err');
		$(price).parent().find('.errF').html('');
	}

	var barcode = $(form).find('input[name="barcode"]');
	if($(barcode).val()=='' || !numCheck($(barcode).val())){
		$(barcode).addClass('err');
		$(barcode).parent().find('.errF').html('Необходимо указать корректный штрихкод');
		fl = false;
		barcode_err = true;
	}else{
		$(barcode).removeClass('err');
		$(barcode).parent().find('.errF').html('');
		barcode_err = false;
	}

	if(!barcode_err){
		$.ajax({
			url: '/ajax/',
		    type:'POST',
			dataType:'text',
			data:  {method:'goods_barcode_check',barcode:$(barcode).val(),id:id},
			async:false,
			success:function(resp){
				if(resp!=0){
					$(barcode).addClass('err');
					$(barcode).parent().find('.errF').html('Товар с таким штрихкодом уже существует: <a href="'+resp+'" target="_blank">посмотреть</a>');
					fl = false;
				}else{
					$(barcode).removeClass('err');
					$(barcode).parent().find('.errF').html('');
				}
			}
		});
	}

	var packing = $(form).find('input[name="packing"]');
	if($(packing).val()==''){
		$(packing).addClass('err');
		$(packing).parent().find('.errF').html('Необходимо указать упаковку');
		fl = false;
	}else{
		$(packing).removeClass('err');
		$(packing).parent().find('.errF').html('');
	}

	var weight = $(form).find('input[name="weight"]');
	if($(weight).val()=='' || !numCheck($(weight).val())){
		$(weight).addClass('err');
		$(weight).parent().find('.errF').html('Необходимо указать корректный вес');
		fl = false;
	}else{
		$(weight).removeClass('err');
		$(weight).parent().find('.errF').html('');
	}

	var personal_discount = $(form).find('input[name="personal_discount"]');
	if($(personal_discount).val()=='' || !numCheck_1($(personal_discount).val()) || $(personal_discount).val()>100){
		$(personal_discount).addClass('err');
		$(personal_discount).parent().find('.errF').html('Необходимо указать корректную величину скидки');
		fl = false;
	}else{
		$(personal_discount).removeClass('err');
		$(personal_discount).parent().find('.errF').html('');
	}

	if(!fl){
		$('#overall_err_msg').show();
	}else{
		$('#overall_err_msg').hide();
	}

	return fl;
}


function user_form_check(form){
	var fl = true;

	var name = $(form).find('input[name="name"]');
	if($(name).val()==''){
		$(name).addClass('err');
		$(name).parent().find('.errF').html('Необходимо указать имя пользователя');
		fl = false;
	}else{
		$(name).removeClass('err');
		$(name).parent().find('.errF').html('');
	}

	var email = $(form).find('input[name="email"]');
	if($(email).val()=='' || !mailCheck($(email).val())){
		$(email).addClass('err');
		$(email).parent().find('.errF').html('Необходимо указать корректный email');
		fl = false;
	}else{
		$(email).removeClass('err');
		$(email).parent().find('.errF').html('');
	}

	var personal_discount = $(form).find('input[name="personal_discount"]');
	if($(personal_discount).val()=='' || !numCheck_1($(personal_discount).val()) || $(personal_discount).val()>100){
		$(personal_discount).addClass('err');
		$(personal_discount).parent().find('.errF').html('Необходимо указать корректное значение');
		fl = false;
	}else{
		$(personal_discount).removeClass('err');
		$(personal_discount).parent().find('.errF').html('');
	}

	var max_nalog = $(form).find('input[name="max_nalog"]');
	if($(max_nalog).val()=='' || !numCheck($(max_nalog).val())){
		$(max_nalog).addClass('err');
		$(max_nalog).parent().find('.errF').html('Необходимо указать корректное значение');
		fl = false;
	}else{
		$(max_nalog).removeClass('err');
		$(max_nalog).parent().find('.errF').html('');
	}

	var my_account = $(form).find('input[name="my_account"]');
	if($(my_account).val()=='' || !numCheck($(my_account).val())){
		$(my_account).addClass('err');
		$(my_account).parent().find('.errF').html('Необходимо указать корректное значение');
		fl = false;
	}else{
		$(my_account).removeClass('err');
		$(my_account).parent().find('.errF').html('');
	}

	if(!fl){
		$('#overall_err_msg').show();
	}else{
		$('#overall_err_msg').hide();
	}

	return fl;

}


function page_form_check(form){
	var fl = true;

	var name = $(form).find('input[name="name"]');
	if($(name).val()==''){
		$(name).addClass('err');
		$(name).parent().find('.errF').html('Необходимо указать название страницы');
		fl = false;
	}else{
		$(name).removeClass('err');
		$(name).parent().find('.errF').html('');
	}

	if(!fl){
		$('#overall_err_msg').show();
	}else{
		$('#overall_err_msg').hide();
	}

	return fl;
}


function news_form_check(form){
	var fl = true;

	var name = $(form).find('input[name="name"]');
	if($(name).val()==''){
		$(name).addClass('err');
		$(name).parent().find('.errF').html('Необходимо указать название новости');
		fl = false;
	}else{
		$(name).removeClass('err');
		$(name).parent().find('.errF').html('');
	}

	if(!fl){
		$('#overall_err_msg').show();
	}else{
		$('#overall_err_msg').hide();
	}

	return fl;
}


function grower_form_check(form){
	var fl = true;

	var name = $(form).find('input[name="name"]');
	if($(name).val()==''){
		$(name).addClass('err');
		$(name).parent().find('.errF').html('Необходимо указать название производителя');
		fl = false;
	}else{
		$(name).removeClass('err');
		$(name).parent().find('.errF').html('');
	}

	if(!fl){
		$('#overall_err_msg').show();
	}else{
		$('#overall_err_msg').hide();
	}

	return fl;
}


function article_form_check(form){
	var fl = true;

	var name = $(form).find('input[name="name"]');
	if($(name).val()==''){
		$(name).addClass('err');
		$(name).parent().find('.errF').html('Необходимо указать название статьи');
		fl = false;
	}else{
		$(name).removeClass('err');
		$(name).parent().find('.errF').html('');
	}

	if(!fl){
		$('#overall_err_msg').show();
	}else{
		$('#overall_err_msg').hide();
	}

	return fl;
}


function level_form_check(form){
	var fl = true;

	var name = $(form).find('input[name="name"]');
	if($(name).val()==''){
		$(name).addClass('err');
		$(name).parent().find('.errF').html('Необходимо указать название раздела');
		fl = false;
	}else{
		$(name).removeClass('err');
		$(name).parent().find('.errF').html('');
	}

	if(!fl){
		$('#overall_err_msg').show();
	}else{
		$('#overall_err_msg').hide();
	}

	return fl;

}

function coupon_add_check(form){
	var fl = true;

	var percent = $(form).find('input[name="percent"]');
	if($(percent).val()=='' || !numCheck($(percent).val()) || $(percent).val()>100){
		$(percent).addClass('err');
		fl = false;
	}else{
		$(percent).removeClass('err');
	}

	return fl;
}

function numCheck(val){
	var numReg = /^[0-9]+$/;
	return (numReg.test(val)) ? true : false;
}

function numCheck_1(val){
	var numReg = /^[0-9]{1,2}.{0,1}[0-9]{0,2}$/;
	return (numReg.test(val)) ? true : false;
}

function mailCheck(val){
	var reg_mail = /[0-9a-z_\-]+@[0-9a-z_\-]+\.[a-z]{2,4}/i;
	return (reg_mail.test(val)) ? true : false;
}