<!DOCTYPE html>
<html lang="en" class="no-js">

<head>
	<meta charset="utf-8" />
	<title>Signify</title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<link href="images/logo/logo.png" rel="icon">

	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/plugin.min.css" rel="stylesheet">
	<link href="css/font-awesome/5.11.2/css/all.min.css" rel="stylesheet">
	<link
		href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&amp;family=Poppins:wght@300;400;500;600;700;800;900&amp;display=swap"
		rel="stylesheet">

	<link href="css/style.css" rel="stylesheet">
	<link href="css/responsive.css" rel="stylesheet">
	<link href="css/darkmode.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet">
</head>

<body>

@include('header')


	<section class="breadcrumb-area banner-1">
		<div class="text-block">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 v-center">
						<div class="bread-inner">
							<div class="bread-menu">
								<ul>
									<li><a href="index.html">Home</a></li>
									<li><a href="#">Contact Us</a></li>
								</ul>
							</div>
							<div class="bread-title">
								<h2>Contact Us</h2>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>


	<section class="contact-page pad-tb">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-5 contact2dv">
					<div class="info-wrapr">
						<h3 class="mb-4">Contact us</h3>
						<div class="dbox d-flex align-items-start">
							<div class="icon d-flex align-items-center justify-content-center">
								<i class="fas fa-map-marker"></i>
							</div>
							<div class="text pl-4">
								<!-- <p><span>Registered Office:</span>Maliyakkal, Princy Villa Chekom ,Pathanapuram Kollam , Kerala-689695.</p> -->
								<p><span>Corporate Office:</span>100, Alapakkam Main Road, Alapkkam, Porur, Chennai -
									600116.</p>
							</div>
							
						</div>
						<div class="dbox d-flex align-items-start">
							<div class="icon d-flex align-items-center justify-content-center">
								<i class="fas fa-phone-alt"></i>
							</div>
							<div class="text pl-4">
								<p><span>Phone:</span> <a href="tel:+917401707707">+91 74017 07707</a></p>
								{{-- <p><span>Phone:</span> <a href="tel:+91 70928 50142">+91  70928 50142</a></p> --}}
							</div>
						</div>
						<div class="dbox d-flex align-items-start">
							<div class="icon d-flex align-items-center justify-content-center">
								<i class="fas fa-envelope"></i>
							</div>
							<div class="text pl-4">
								<p><span>Email:</span> <a
										href="mailto:signify@gmail.com">signify@gmail.com

									</a>
								</p>
							</div>
						</div>
						
						{{-- <div class="dbox d-flex align-items-start">
							<div class="icon d-flex align-items-center justify-content-center">
								<i class="fas fa-digital-tachograph"></i>
							</div>
							<div class="text pl-4">
								<p><span>Digital Visting Card:</span> <a
										href="https://mikivcard.com/sai-saravana-pg" target="_blank">https://mikivcard.com/sai-saravana-pg 

									</a>
								</p>
							</div>
						</div> --}}

					</div>
				</div>
				<div class="col-lg-7 m-mt30 pr30 pl30">
					<div class="common-heading text-l">
						<h2 class="mt0 mb0">Get in touch</h2>
						<p class="mb60 mt10">We will catch you as early as we receive the message</p>
					</div>
					<div class="form-block">
						<form method="post" action="https://formsubmit.co/signify@gmail.com">
							<input type="hidden" name="_subject" value="New Form Submission in Signify">
							<div class="row">
								<div class="form-group col-sm-6">
									<input type="text" name="first_name" placeholder="First Name" required>									
								</div>
								<div class="form-group col-sm-6">
									<input type="text" name="Last_name" placeholder="Last Name" required>
								</div>
							</div>
							<div class="row">
								<div class="form-group col-sm-6">
									<input type="email" name="email" placeholder="Enter Email" required
										>									
								</div>
								<div class="form-group col-sm-6">
									<input type="text" name="phone" placeholder="Enter mobile" required
										>									
								</div>
							</div>
							<div class="form-group">
								<textarea name="message" rows="5" placeholder="Enter your message" required></textarea>
								
							</div>

							<button type="submit" class="btn lnk btn-main bg-btn"> <span
									class="circle"></span>Submit</button>							
							<div class="clearfix"></div>

						</form>
								
						
					</div>
				</div>
			</div>
		</div>
	</section>


	<div class="contact-location">
		<div class="container-fluid">
			<div class="row justify-content-center">
				<div class="col-lg-12">
					<div class="map-div">
						<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3886.800748496793!2d80.1627279745477!3d13.048351313192!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a52613df516fa2d%3A0xf0fca0c7b8a7f98!2s100%2C%20Alapakkam%20Main%20Rd%2C%20Thirumurugan%20Nagar%2C%20Alappakam%2C%20Alapakkam%2C%20Porur%2C%20Chennai%2C%20Tamil%20Nadu%20600116!5e0!3m2!1sen!2sin!4v1742652893359!5m2!1sen!2sin" width="100%" height="850" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
					</div>
				</div>
			</div>
		</div>
	</div>


	@include('footer')
	{{-- <div class="footer-row3">
		<div class="copyright">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="footer-social-media-icons">
							<a href="" target="blank"><i class="fab fa-facebook"></i></a>
							
						<a href=" " target="blank"><i
								class="fab fa-instagram"></i></a>
						<a href="" target="blank"><i
								class="fab fa-youtube"></i></a>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div> --}}


	<script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
	<script src="js/vendor/modernizr-3.5.0.min.js"></script>
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/plugin.min.js"></script>
	<script src="js/dark-mode.js"></script>

	<script src="js/main.js"></script>
</body>

</html>