$(document).ready(function(){
	Shadowbox.init({
		continuous: true
	});
});

function level_sort_change(elem){
	var cookie_type = $('input[name="cookie_type"]').val();

	var sort_by = $(elem).val();
	var level_id = $('input[name="level_id"]').val();

	var level_link = $('input[name="level_link"]').val();

	$.cookie(cookie_type+'[sort]['+level_id+']', sort_by, {
		expires: 300,
		path: '/'
	});

	window.location.href = level_link;
}

function display_number_change(elem){
	var cookie_type = $('input[name="cookie_type"]').val();
	var display = $(elem).val();
	var level_id = $('input[name="level_id"]').val();
	var reqiure_file = $('input[name="reqiure_file"]').val();

	var level_link = $('input[name="level_link"]').val();

	$.cookie(cookie_type+'[display_number]['+reqiure_file+']['+level_id+']', display, {
		expires: 300,
		path: '/'
	});

	window.location.href = level_link;
}

function display_type_change(elem){
	var cookie_type = $('input[name="cookie_type"]').val();
	var display_type = $(elem).attr('id');
	var level_id = $('input[name="level_id"]').val();

	var level_link = $('input[name="level_link"]').val();

	$.cookie(cookie_type+'[display_type]['+level_id+']', display_type, {
		expires: 300,
		path: '/'
	});

	window.location.href = level_link;
}