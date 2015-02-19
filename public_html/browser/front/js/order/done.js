$(function() {
	if($('input#openbill').size()>0){
		var num = $('input#openbill').val(); 
		window.open('/order/bill/?o='+num,'mywindow','width=700,height=600');
	}
});