<?php $version = '1.0'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title>Invisible Captcha Demo | JDM Digital</title>
	<meta name="description" content=""/>

	<link rel="dns-prefetch" href="//maxcdn.bootstrapcdn.com" />
	<link rel='dns-prefetch' href='//static-jdmdigital.netdna-ssl.com' />
	<link rel="dns-prefetch" href="//code.jquery.com" />
	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com" />
	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com" />

	<link rel="canonical" href="https://jdmdigital.co/nocaptcha/" />
	<link rel="publisher" href="https://plus.google.com/+JDMDigitalAgencyDallas"/>
	<meta property="og:locale" content="en_US" />
	<meta property="og:title" content="Invisible Captcha Demo | JDM Digital" />
	<meta property="og:description" content="" />
	<meta property="og:url" content="https://jdmdigital.co/nocaptcha/" />
	<meta property="og:site_name" content="JDM Digital" />
	<meta property="article:publisher" content="https://www.facebook.com/JDMdigital" />
	<meta property="og:image" content="" />
	<meta name="twitter:card" content="summary_large_image" />
	<meta name="twitter:description" content="" />
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

	<link rel="shortlink" href="" />

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

	<!-- Custom styles -->
	<link href="custom.css?v=2" rel="stylesheet">
</head>

<body>

	<nav class="navbar navbar-expand-md justify-content-between navbar-dark bg-dark fixed-top">
		<a class="navbar-brand" href="https://jdmdigital.co/nocaptcha/">No Captcha</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nocaptchaNav" aria-controls="nocaptchaNav" aria-expanded="false" aria-label="Toggle Menu">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="nocaptchaNav">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
					<a class="nav-link" href="http://demo.jdmdigital.co/nocaptcha">Demo <span class="sr-only">(current)</span></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#">About</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Docs</a>
					<div class="dropdown-menu" aria-labelledby="dropdown01">
						<a class="dropdown-item" href="#">GitHub Repo</a>
						<a class="dropdown-item" href="#">ReadMe File</a>
						<a class="dropdown-item" href="#">Issues</a>
					</div>
				</li>
			</ul>
			<a href="#" class="btn btn-outline-success d-none d-lg-inline-block mb-3 mb-md-0 ml-md-3">Download</a>
		</div>
	</nav>

	<div class="container">
		
		<div class="wrapper">
			<h1>Invisible Captcha</h1>
			<p class="lead">We'll put the form here.  It won't collect ANY information.  It won't really even submit, expect to say that it "would have" or "wouldn't have."</p>
			<p>This form is purely for demonstration purposes.  No information is collected or retained.  It literally doesn't do anything except show you how it might work.  Enter whatever you want.</p>
			<div class="form-wrap">
				<form id="nocaptcha">
					<input type="hidden" id="somethingcrazy" name="somethingcrazy" value="" />
					<div class="row">
						<div class="col-sm">
							<div class="form-group required">
								<label for="yourName">Your Name <span class="text-danger" aria-label="Required">*</span></label>
								<input type="text" class="form-control form-control-lg required-text" id="yourName" aria-describedby="nameHelp" placeholder="Jane Doe" autocomplete="off" tabindex="1" required />
								<small id="nameHelp" class="form-text text-muted">Enter any name you want. You can <a href="https://en.wikipedia.org/wiki/Ishmael_(Moby-Dick)" target="_blank" rel="noopener nofollow">call me Ishmael</a>.</small>
							</div>
						</div><!--/col-->
						<div class="col-sm">
							<div class="form-group required">
								<label for="fakeEmail">Fake Email Address <span class="text-danger" aria-label="Required">*</span></label>
								<input type="email" class="form-control form-control-lg required-email" id="fakeEmail" aria-describedby="emailHelp" placeholder="you@mail.com" autocomplete="off"  tabindex="2" required />
								<small id="emailHelp" class="form-text text-muted">It'll get validated, but NEVER actually used. Don't worry.</small>
							</div>
						</div><!--/col-->
					</div><!--/row-->
					<div class="row">
						<div class="col-sm">
							<div class="form-group">
								<label for="favoriteColor">Favorite Color</label>
								<input type="text" class="form-control form-control-lg" id="favoriteColor" aria-describedby="colorHelp" placeholder="Blue"  autocomplete="off"  tabindex="3" />
								<small id="colorHelp" class="form-text text-muted">A <a href="https://www.livescience.com/34105-favorite-colors.html" title="Humanity's Favorite Colors" target="_blank" rel="noopener nofollow">2012 study</a> found "Blue" is humanity's favorite color. Is it yours?</small>
							</div>
						</div>
						<div class="col-sm">
							<div class="form-group">
								<label for="favoriteNumber">Favorite Number</label>
								<input type="tel" class="form-control form-control-lg" id="favoriteNumber" aria-describedby="numberHelp" placeholder="73"  autocomplete="off"  tabindex="4" />
								<small id="numberHelp" class="form-text text-muted">73 is the 21st prime number, its mirror 37 is the 12th and its mirror 21 is the product of multiplying, <a href="http://nautil.us/blog/-found-the-worlds-favorite-number" title="The World’s Favorite Number" target="_blank" rel="noopener nofollow">wait for it</a>, 7 and 3!</small>
							</div>
						</div>
					</div><!--/row-->
					<div class="fax-wrap">
						<label for="contact_me_by_fax">Website URL</label>
						<input type="tel" class="fax" id="contact_me_by_fax" name="contact_me_by_fax" value="" autocomplete="off" tabindex="-1" />
					</div>
					<div class="form-group extra-mg">
						<div class="checkbox-wrapper">
							<div class="checkbox checkbox-rounded">
								<input type="checkbox" class="robot-check" id="captcha" name="captcha" autocomplete="off" value="1"  tabindex="6" checked />
								<label for="captcha"></label>
							</div>
							<p class="checkbox-label">I am 100% robot</p>
						</div>
						<span class="form-text text-muted for-checkbox">You're <b>not a robot</b>, are you? Sorry we have to ask.</span>
					</div>
					<div class="row">
						<div class="col-sm-4">
							<button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="5">Send It</button>
						</div>
						<div class="col-sm-8">
							<span class="btn btn-link" id="toogoofy">You appear &quot;robot-like&quot;...</span>
						</div>
					</div>
					<button type="reset" class="btn btn-light btn-lg" tabindex="7">Reset</button>
				</form>
				<div id="loader">
					<img src="loading.svg" alt="Working" height="200" width="200" />
					WORKING
				</div><!-- END #loader -->
			</div><!--/form-wrap-->
		</div>

	</div><!-- /.container -->
	
	<footer class="footer">
		<div class="container-fluid">
			<span class="text-muted">Copyright &copy; <a href="https://jdmdigital.co">JDM Digital</a></span>
		</div>
	</footer>

	<!-- Placed at the end of the document so the pages load faster -->	
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script>window.jQuery || document.write('<script src="jquery.min.js"><\/script>')</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
	
	<!-- Magic Happens Here -->
	<script src="main.js?v=2"></script>	
</body>

</html>
