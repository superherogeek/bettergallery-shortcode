jQuery( document ).ready( function($) {
// http://stackoverflow.com/questions/744319/get-css-rules-percentage-value-in-jquery works with 'auto' as well
	originalImageWidth = $( '.osp-image' )[0].style.width;
	originalImageMargin = $( '.osp-image' )[0].style.margin;

function osp_bg_changeToMobile() {
	// Get the total number of images in gallery
	totalImages = $( '.osp-image' ).length;
	imageSize = 120;
	margin = 10;

	$('.osp-image').each( function(){
		$( this ).css( 'width', imageSize + 'px' );
		$( this ).css( 'margin-left', margin + 'px' );
		$( this ).css( 'margin-right', margin + 'px' );

		if( $( this ).hasClass( 'cf' ) ) {
			//Remove any clears for floats on images
			$( this ).removeClass( 'cf' );
			$( this ).addClass( 'cf-off' );
		}
	});

	totalMargins = totalImages * ( margin * 2 );
	imageContainerWidth = ( totalImages * imageSize ) + totalMargins;

	$( '.img-container' ).css( 'width', imageContainerWidth + 'px' );	
}
function osp_bg_resetGallery() {

	$('.img-container').css('width', 'auto');
	
	 $('.osp-image').each(function(){
	 	//Reset image sizes and margin
		$(this).css('width', originalImageWidth);
		$(this).css('margin', originalImageMargin);


	 	// Reset any clears for floats
		if($(this).hasClass('cf-off')) {
			$(this).removeClass('cf-off');
			$(this).addClass('cf');
		}
	});
}
	// If document loads in mobile, change the gallery immediately to mobile
		screenWidth = $(window).width();

    	if (screenWidth <= 480) {
    		osp_bg_changeToMobile();
    	} 
	$('.img-container').change(function(){
		console.log($(this).scrollLeft());
	});

	$(window).resize(function(){ 
    	screenWidth = $(window).width();

    	if (screenWidth <= 480) {
    		osp_bg_changeToMobile();
    	} else {
    		// Window Size is greater than 480. Reset Everything if the container's width is not equal to 'auto', meaning it never was changed.
    		if( $('.img-container')[0].style.width != 'auto') {
				osp_bg_resetGallery();
    		}
    	}
	});
});