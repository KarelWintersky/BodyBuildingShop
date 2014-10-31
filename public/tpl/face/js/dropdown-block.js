$(function() {
	   
	$.fn.dropDownBlock = function(block, options) {
		var defaults = { 
			speed: 'fast',
			top: $(this).height(),
			left: 0
		}, 
		opts 	= $.extend(defaults, options),
  		toggler = $(this),
  		block 	= $('#'+$(this).attr('id')+'_list'),
  		val 	= $('#'+$(this).attr('id')+'_value'),
  		item 	= $(block).find('li');
  		
  		toggler.css({'outline': 'none'}).addClass('dropDownBlock-attached');
  		
  		$(toggler).click(function(e) {
  			e.preventDefault();
  			$(block).css({
        		'position' 	: 'absolute',
        		'width' 	: (toggler.width()+28) + 'px',
        		'top' 		: (opts['top']+1) + 'px',
        		'left' 		: opts['left'] + 'px'
      		});
      		if($(block).is(':visible')) $(block).fadeOut(opts['speed']);
      		else $(block).fadeIn(opts['speed']);      		
      		this.focus();
  		});
  		$(toggler).blur(function() {
  			$(block).fadeOut(opts['speed']);
  		});
  		
  		$(item).click(function(){

  			$(item).each(function(i,el){
  				$(el).removeClass('active');
  			});
  			 			
  			$(this).addClass('active');  			
  			
  			$(val).val($(this).attr('val')).change();  				
  			$(toggler).val($(this).html()).change();
  			  			
  		});
	};

	dropdown_blocks();
	
});

function dropdown_blocks(){
	$('.dropdown_name').each(function(i,elem){
		if(!$(elem).hasClass('dropDownBlock-attached') && !$(elem).hasClass('disabled')){
			$(elem).dropDownBlock();	
		}
	});
}