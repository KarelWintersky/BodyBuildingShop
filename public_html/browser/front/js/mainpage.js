jQuery(document).ready(function() {
    jQuery('#growers_carousel_body').jcarousel();
    
	$('#main_page_module').AccordionImageMenu({
		'height': 319,
		'openDim': 839,
		'closeDim': 238,
		'duration': 600,
		'openItem': 0,
		'border': 1,
		'color': '#ffffff',
		'effect': 'easeInOutSine'
	});    
});