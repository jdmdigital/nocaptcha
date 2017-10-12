// Magic Stuff Goes Here

/* == The Good Stuff == */
$( document ).ready(function() {
	// Inversed Checkboxes for AntiSpam
	$('.robot-check').click(function() {
		if($('.robot-check').prop('checked')) {
			$('.robot-check').parent().parent().find('.checkbox-label').text('I am 100% robot');
		} else {
			$('.robot-check').parent().parent().find('.checkbox-label').text('I\'m NOT a robot!');
		}
	});
	
	// Form Input Timing
	$('.form-control').focus(function() {			
		if($('#somethingcrazy').length && $('#somethingcrazy').val() == '') {
			var timeStart = Date.now();
			$('#somethingcrazy').val(timeStart);
		} 
	});
	
	// AJAX Submission
	$('form#nocaptcha').submit(function(evt) {			
		// I'll take it from here...
		evt.preventDefault();
		
		var the_form = $('form#nocaptcha');
		var is_sending = false;
		var goofy = true; // assume it's a robot for now
		var timeEnd = Date.now();
		var goodTime = 3000; // form complete time in human speed
		var timeStart = $('#somethingcrazy').val();
		var theTime = timeEnd - timeStart;
		
		if(!$('.robot-check').prop('checked')) {
			goofy = false;
		} 
		
		if(!goofy && !is_sending) {
			$('.form-wrap').addClass('loading');
			$.ajax({
				url		: 'process.php',
				type	: 'post',
				dataType: 'json',
				data	: $(this).serialize(),
				beforeSend: function () {
					is_sending = true;
					$(the_form).addClass('is-sending');
				},
				error: handleFormError,
				success	: function(response) {
					//console.log(response);
					if(response.status === 'success') {
						// Trigger Success Message
						console.log(response);
						handleFormSuccess(response);
					} else {
						handleFormError(response);
					}
				},
				complete : function() {
					// housekeeping for success AND failure
					$(the_form).addClass('was-validated');
					$(the_form).removeClass('is-sending');
					$('.form-wrap').removeClass('loading');
					console.log(response);
				}
			}); // END .ajax
			
		} // END !goofy
		
		else {
			$('#toogoofy').fadeTo(200, 1);
		} // it's too goofy
		
	}); // END submit function
	
});  // END doc.ready
