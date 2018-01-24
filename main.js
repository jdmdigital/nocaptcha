/*
 * No Captcha v0.1.5
 */


// Preload the page (ideally just once)
function preloaderStop() {
	"use strict";
	$('#main').removeClass('blurred');
	$('#main-preloader').fadeOut();
	//$('#main-preloader .loading').removeAttr('style'); // clean-up
}

// Style Form During Processing
// Accepts TRUE or FALSE to add or remove the processing styles
// Must be a better way of doing this
function formloading(loading){
	"use strict";
	if(loading === false){
		//$('#main-preloader').fadeOut().removeClass('processing');
		$('#form-loading').fadeOut();
		setTimeout(function() {
			$('form#nocaptcha').removeClass('processing blurred');
		}, 150);
	} else { 
		$('form#nocaptcha').addClass('processing blurred');
		//$('#main').addClass('processing');
		setTimeout(function() {
			$('#form-loading').fadeIn();
		}, 300);
	}
} // END formloading()


/* == The Good Stuff == */
$(document).ready(function() {
	"use strict";
	
	$('html').removeClass('no-js').addClass('js');
	
	$('form#nocaptcha').submit(function(evt) {
		evt.preventDefault();
		formloading(true);
		
		// Pretend the form takes 3 seconds to process...
		setTimeout(function() {
			formloading(false);
			$('#toogoofy').text('Submission Successful!').addClass('text-success').fadeTo(1,150);
			$('#feedback-info').slideDown();
			
		}, 3000);
	}); // END Submit Functions
	
	// Fire the Preloader Stop when everything is done (or assumed done)
	if($('#nocaptcha').length){
		setTimeout(preloaderStop, 700);
	}

}); // Doc.ready


