jQuery(document).ready( function() {
	jQuery('#searchicon').click(function() {
		jQuery('#jumbosearch').fadeIn();
		jQuery('#jumbosearch input').focus();
	});
	jQuery('#jumbosearch .closeicon').click(function() {
		jQuery('#jumbosearch').fadeOut();
	});
	jQuery('body').keydown(function(e){
	    
	    if(e.keyCode == 27){
	        jQuery('#jumbosearch').fadeOut();
	    }
	});
			
	jQuery('#site-navigation ul.menu').slicknav({
		label: 'Menu',
		duration: 1000,
		prependTo:'#slickmenu'
	});	 
	
	jQuery(document).scroll(function() {
		if (jQuery(window).width() > 768 ) {
			if ( jQuery('#top-bar').visible() == false ) {
				jQuery('#static-bar').fadeIn('slow');
			}
			else {
				jQuery('#static-bar').fadeOut('fast');
			}
		}	
	});
	
	
	jQuery('.flex-images').flexImages({rowHeight: 200, object: '.item', truncate: true});
  
});

jQuery(function () {
        jQuery.stellar({
            horizontalScrolling: false,
            verticalOffset: 0
        });
    });
    
jQuery(window).load(function() {
    jQuery('#nivoSlider').nivoSlider({
        prevText: "<i class='fa fa-chevron-circle-left'></i>",
        nextText: "<i class='fa fa-chevron-circle-right'></i>",
        pauseTime: 5000,
        beforeChange: function() {
	        jQuery('.slider-wrapper .nivo-caption').animate({
													opacity: 0,
													marginLeft: -10,
													},500,'linear');
	        
        },
        afterChange: function() {
	        jQuery('.slider-wrapper .nivo-caption').animate({
													opacity: 1,
													marginLeft: 0,
													},500,'linear');
        },
        animSpeed: 700,
        
    });
});  
    
var slideout = new Slideout({
    'panel': document.getElementById('page'),
    'menu': document.getElementById('mobile-static-menu'),
    'padding': 256,
    'tolerance': 70
});     

jQuery('.mobile-toggle-button').toggle(function(){
		slideout.open();
		jQuery('.mobile-toggle-button').animate({left: 211},300, 'swing');
		jQuery('.mobile-toggle-button i').removeClass('fa-bars').addClass('fa-close');
	},
	function() {
		slideout.close();
		jQuery('.mobile-toggle-button').animate({left: 5},300, 'swing');
		jQuery('.mobile-toggle-button i').removeClass('fa-close').addClass('fa-bars');
	});
	
// Toggle button
/*
document.querySelector('.mobile-toggle-button').addEventListener('click', function() {
	slideout.toggle();
}); 
*/    