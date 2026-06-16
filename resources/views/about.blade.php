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
                                    <li><a href="#">About Us</a></li>
                                </ul>
                            </div>
                            <div class="bread-title">
                                <h2>About Us</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="about-agency ptb-60 block-1">

        <div class="container">

            <h2 class="text-center">About Signify</h2>
            <div class="row ptb-30">
                <div class="col-lg-6 v-center">
                    <div class="about-image">
                        <img src="img/download/8.jpeg" alt="about us" class="img-fluid" />
                    </div>
                    <!-- <div class="row in-stats small about-statistics">
      <div class="col-lg-4 col-sm-4">
       <div class="statistics">
        <div class="statnumb counter-number">
         <span class="counter">200</span><span class="services-cuntr">+</span>
         <p>Happy Clients</p>
        </div>
       </div>
      </div>
      <div class="col-lg-4 col-sm-4">
       <div class="statistics">
        <div class="statnumb">
         <span class="counter">95</span><span>k</span>
         <p>Hours Worked</p>
        </div>
       </div>
      </div>
      <div class="col-lg-4 col-sm-4">
       <div class="statistics mb0">
        <div class="statnumb counter-number">
         <span class="counter">300</span><span class="services-cuntr">+</span>
         <p>Projects Done</p>
        </div>
       </div>
      </div>
     </div> -->

                </div>
                <div class="col-lg-6">
                    <div class="common-heading text-l ">

                        <p class="text-justify">
                            Signify is a signage company that specializes in Indoor signages, Outdoor Signages, and
                            Custom Signages. We offer a wide range of visual communication solutions to help businesses
                            increase their visibility and convey their messages effectively.
                        </p>
                        <p class="text-justify">
                            We specialize in designing,
                            manufacturing and installing high-quality indoor and outdoor branding, signage, and retail
                            display solutions. Our services include small format signage, outdoor signs, digital
                            signages, and more.
                        </p>
                    </div>

                </div>


            </div>

    </section>
    <section class="missionvision dark-bg4 xbg-gradient3 pb-5">
        <div class="container">
            <div class="row mt30 d-flex align-items-stretch">
                <div class="col-lg-6 col-sm-12 mt30">
                    <div class="s-block2 bg-light shadow p-3 rounded text-center h-100 d-flex flex-column">
                        <div class="card-icon"><img src="images/icons/vision.png" alt="icon" class="w80 mb20"></div>
                        <h3>Our Misson</h3>
                        <p class="mt15  text-justify pb-4 flex-grow-1">
							{{-- <img src="./img/gps.png" width="15px" class="me-3" alt=""> --}}
								Mission is to provide the highest quality products and exceptional
                            service to our customers through innovation, creativity, and dedication of our entire team,
                            while keeping the company profitable.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12 mt30">
                    <div class="s-block2 bg-light shadow text-center p-3 rounded h-100 d-flex flex-column">
                        <div class="card-icon"><img src="images/icons/mountain.png" alt="icon" class="w80 mb20">
                        </div>
                        <h3>Our Vision</h3>
                        <p class="mt15 text-justify flex-grow-1">
							{{-- <img src="./img/gps.png" width="15px" class="me-2" alt=""> --}}
								Vision is to strive to be seen as trusted
                            signage experts who elevate the level of experience and solutions offered in the signage
                            industry. We are passionate about providing solutions and continuously improving all areas
                            of Our service.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>





    @include('footer')

    <script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="js/vendor/modernizr-3.5.0.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/plugin.min.js"></script>
    <script src="js/dark-mode.js"></script>

    <script src="js/main.js"></script>
</body>

</html>
