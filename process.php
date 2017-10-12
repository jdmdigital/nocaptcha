<?php 
// Process Form Stuff Here (server-side)

function valid_email($email) {
    return !!filter_var($email, FILTER_VALIDATE_EMAIL);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$errors         = array();      // array to hold validation errors
	$data           = array();      // array to pass back data
	
	$yourname = $_POST['yourName'];
	$fakeemail = $_POST['fakeEmail'];
	
	if (empty($yourname))
		$errors['yourName'] = 'Name is required.';
	
	if (empty($fakeemail)) 
		$errors['fakeEmail'] = 'Email is required.';
	
	if (!valid_email($email))
		$errors['fakeEmail'] = 'Email looks too fake.';
	
	if ($_POST['captcha'] == '1')
		$errors['captcha'] = 'I thought you looked like a robot.';
	
	if ( ! empty($errors)) {
		// if there are items in our errors array, return those errors
		$data['success'] = false;
		$data['errors']  = $errors;
	} else {
	
		// if there are no errors process our form, then return a message
	
		// DO ALL YOUR FORM PROCESSING HERE
		// THIS CAN BE WHATEVER YOU WANT TO DO (LOGIN, SAVE, UPDATE, WHATEVER)
	
		// show a message of success and provide a true success variable
		$data['success'] = true;
		$data['message'] = 'Sent.  You\'re not a robot!';
	}
	
	// return all our data to an AJAX call
	echo json_encode($data);

} else {
	echo '<h1>Nothing to see here!</h1>';
}

?>
