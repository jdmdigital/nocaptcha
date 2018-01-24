<!DOCTYPE html>
<html lang="en" class="no-js">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title>Invisible Captcha Demo | JDM Digital</title>
	<meta name="description" content="An experiment to prevent spambot form submissions without annoying the heck out of your human form submitters. "/>

	<link rel="dns-prefetch" href="//maxcdn.bootstrapcdn.com" />
	<link rel='dns-prefetch' href='//static-jdmdigital.netdna-ssl.com' />
	<link rel="dns-prefetch" href="//code.jquery.com" />
	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com" />

	<link rel="canonical" href="https://jdmdigital.co/demo/nocaptcha/" />
	<link rel="publisher" href="https://plus.google.com/+JDMDigitalAgencyDallas"/>
	<meta property="og:locale" content="en_US" />
	<meta property="og:title" content="Invisible Captcha Demo | JDM Digital" />
	<meta property="og:description" content="This live demo form runs all sorts of anti-spam tests, but doesn't use a CAPTCHA. Try and fool it!" />
	<meta property="og:url" content="https://jdmdigital.co/demo/nocaptcha/" />
	<meta property="og:site_name" content="JDM Digital" />
	<meta property="article:publisher" content="https://www.facebook.com/JDMdigital" />
	<meta property="og:image" content="" />
	<meta name="twitter:card" content="summary_large_image" />
	<meta name="twitter:description" content="This live demo form runs all sorts of anti-spam tests, but doesn't use a CAPTCHA. Try and fool it!" />
	<meta name="twitter:title" content="Invisible Captcha Demo | JDM Digital" />
	<meta name="twitter:site" content="@jdmdigital" />
	<meta name="twitter:image" content="" />
	<meta name="twitter:creator" content="@jdmdigital" />
	<meta name="author" content="JDM Digital" />
	<link rel="shortcut icon" href="https://static-jdmdigital.netdna-ssl.com/wapp/favicon.ico"/>
	<link rel="apple-touch-icon" href="https://static-jdmdigital.netdna-ssl.com/wapp/favicon-chrome.png" media="screen" />
	<link rel="apple-touch-icon" sizes="76x76" href="https://static-jdmdigital.netdna-ssl.com/wapp/icon76.png" media="screen"/>
	<link rel="apple-touch-icon" sizes="120x120" href="https://static-jdmdigital.netdna-ssl.com/wapp/icon120.png" media="screen"/>
	<link rel="apple-touch-icon" sizes="152x152" href="https://static-jdmdigital.netdna-ssl.com/wapp/icon152.png" media="screen"/>
	<link rel="icon" sizes="192x192" href="https://static-jdmdigital.netdna-ssl.com/wapp/favicon-chrome.png" media="screen"/>

	<link rel="shortlink" href="https://jdmdig.it/nocaptchademo" />

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
	
	<!-- Popins Font -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:700,900" media="screen"> 
	
	<!-- Custom styles -->
	<link rel="stylesheet" href="demo.css" >
	<link rel="stylesheet" href="styles.css" >
</head>

<body>

	<nav class="navbar navbar-expand-md justify-content-between navbar-dark bg-dark fixed-top">
		<a class="navbar-brand" href="https://jdmdigital.co/demo/nocaptcha/">No Captcha</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nocaptcha-nav" aria-controls="nocaptcha-nav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="nocaptcha-nav">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
					<a class="nav-link" href="https://jdmdigital.co/demo/nocaptcha/">Live Demo <span class="sr-only">(current)</span></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#">Article</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="https://jdmdigital.co/demo/nocaptcha/extras/" id="extras-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Extras</a>
					<div class="dropdown-menu" aria-labelledby="extras-dropdown">
						<a class="dropdown-item link-external" href="https://github.com/jdmdigital/nocaptcha">View Code on GitHub</a>
						<a class="dropdown-item link-external" href="#">Inspriation Article</a>
						<a class="dropdown-item link-external" href="https://jdmdigital.co/labs/" title="JDM Labs">Other Code Projects</a>
					</div>
				</li>
			</ul>
			<a class="link-external btn btn-light d-none d-sm-inline-block" href="https://github.com/jdmdigital/nocaptcha/releases" title="Latest Release on GitHub">Download</a>
		</div>
	</nav>
	
	<div id="main" class="container blurred" role="main">
		<div class="title-clone">Invisible Captcha</div>
		<div class="wrapper fadeIn">
			<h1>Invisible Captcha Demo</h1>
			<p class="lead">This form runs all sorts of anti-spam tests, but <strong>doesn't use a CAPTCHA</strong>.  It won't collect ANY information.  It won't really even submit, except to say if it "would" or "wouldn't" have based on our tests. See the <a href="#">Medium article</a> for details.</p>
			<form id="nocaptcha" action="process.php" method="post">
				<input type="hidden" id="somethingcrazy" name="somethingcrazy" value="" />
				<div class="row">
					<div class="col-sm">
						<div class="form-group required">
							<label for="yourName">Your Name <span class="text-danger" aria-label="Required">*</span></label>
							<input type="text" class="form-control form-control-lg required-text" id="yourName" aria-describedby="nameHelp" placeholder="Jane Doe" autocomplete="off" required tabindex="1" />
							<small id="nameHelp" class="form-text text-muted">Enter any name you want. You can <a href="https://en.wikipedia.org/wiki/Ishmael_(Moby-Dick)" target="_blank" rel="noopener nofollow" tabindex="7">call me Ishmael</a>.</small>
						</div>
					</div><!--/col-->
					<div class="col-sm">
						<div class="form-group required">
							<label for="fakeEmail">Fake Email Address <span class="text-danger" aria-label="Required">*</span></label>
							<input type="email" class="form-control form-control-lg required-email" id="fakeEmail" aria-describedby="emailHelp" placeholder="you@mail.com" autocomplete="off" required tabindex="2" />
							<small id="emailHelp" class="form-text text-muted">It'll get validated, but NEVER actually used. Don't worry.</small>
						</div>
					</div><!--/col-->
				</div><!--/row-->
				<div class="row">
					<div class="col-sm">
						<div class="form-group">
							<label for="favoriteColor">Favorite Color</label>
							<input type="text" class="form-control form-control-lg" id="favoriteColor" aria-describedby="colorHelp" placeholder="Blue"  autocomplete="off" tabindex="3" />
							<small id="colorHelp" class="form-text text-muted">A <a href="https://www.livescience.com/34105-favorite-colors.html" title="Humanity's Favorite Colors" target="_blank" rel="noopener nofollow" tabindex="8">2012 study</a> found "Blue" is humanity's favorite color. Is it yours?</small>
						</div>
					</div>
					<div class="col-sm">
						<div class="form-group">
							<label for="favoriteNumber">Favorite Number</label>
							<input type="tel" class="form-control form-control-lg" id="favoriteNumber" aria-describedby="numberHelp" placeholder="73"  autocomplete="off" tabindex="4" />
							<small id="numberHelp" class="form-text text-muted">73 is the 21st prime number, its mirror 37 is the 12th and its mirror 21 is the product of multiplying, <a href="http://nautil.us/blog/-found-the-worlds-favorite-number" title="The World’s Favorite Number" target="_blank" rel="noopener nofollow" tabindex="9">wait for it</a>, 7 and 3!</small>
						</div>
					</div>
				</div><!--/row-->
				<div class="fax-wrap">
					<label for="contact_me_by_fax">Website URL</label>
					<input type="tel" class="fax" id="contact_me_by_fax" name="contact_me_by_fax" value="" autocomplete="off" tabindex="-1" />
				</div>
				<!--<div class="form-group">
					<div class="form-check">
						<label class="form-check-label">
							<input type="checkbox" class="form-check-input" id="robotcheck">
							I'm a Robot!
						</label>
					</div>
					<small id="robotHelp" class="form-text text-muted">You're not a robot, are you? Sorry we have to ask.</small>
				</div>-->
				<div class="checkbox-wrapper">
					<div class="checkbox checkbox-rounded">
						<input type="checkbox" class="robot-check" id="captcha" name="captcha" autocomplete="off" checked tabindex="5" />
						<label for="captcha"></label>
					</div>
					<p class="checkbox-label">I am 100% robot</p>
				</div>
				<p class="help-block for-checkbox">You're <b>not a robot</b>, are you? Sorry we have to ask.</p>
				<div class="row">
					<div class="col-sm-4">
						<button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="6">Send with No Captcha</button>
					</div>
					<div id="responses" class="col-sm-8">
						<span class="btn btn-link" id="toogoofy">You appear &quot;robot-like&quot;...</span>
					</div>
				</div>
				<div id="feedback-info" class="feedback">
					<pre>Fields obviscated... <span class="text-warning">Nope</span><br />All required fields... <span class="text-success">Completed and Validated</span><br />Honeypot is empty... <span class="text-success">Yup</span><br />Robot checkbox test... <span class="text-success">Passed</span><br />Connected to Akismet... <span class="text-danger">Nope</span><br />Akismet analysis... <span class="text-warning">N/A</span><br />Time to complete form... <span class="text-success">2.5 seconds</span><br /><br />Would the form have submitted? <span class="text-success">Absolutely!</span></pre>
				</div><!--/feedback-info-->
			</form>
			<div id="form-loading" style="display: none"><img src="loading.svg" alt="Loading..." /></div>
		</div>

	</div><!-- /.container -->
	
	<div id="main-preloader" class="preload">
		<div class="alert alert-danger fadeIn" role="alert">
			<h4 class="alert-heading">Javascript Disabled</h4>
			<p>You must have Javascript enabled in your browser to use this form. Sorry about that.</p>
			<a href="https://jdmdigital.co/demo/nocaptcha/" class="btn btn-secondary">Refresh</a>
		</div>
	</div>
	
	<footer class="footer">
		<div class="container-fluid">
			<span class="text-muted">Copyright &copy; <a href="https://jdmdigital.co">JDM Digital</a></span>
		</div>
	</footer>

	<!-- Placed at the end of the document so the pages load faster -->	
	<!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>-->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<script>window.jQuery || document.write('<script src="jquery.min.js"><\/script>')</script>
	<script defer async src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
	<script defer async src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
	
	<!-- Magic Happens Here -->
	<script defer src="main.js?v=2"></script>	
</body>

</html>
