<!DOCTYPE html>
<html lang="en" class="no-js">

<head>
    <meta charset="utf-8" />
    <title>Signify</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#4302b2">

    <link href="images/logo/logo.png" rel="icon">
    <link href="css/swiper.min.css" rel="stylesheet">
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
    <style>
        .slide-bg-image {
            position: relative;
            background-size: cover;
            background-position: center;
            height: 100vh;
            /* Adjust height as needed */
        }

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6));
            /* Dark gradient overlay */
            z-index: 1;
            /* Make sure the gradient is on top of the background image */
        }

        .slide-inner .container {
            position: relative;
            z-index: 2;
            /* Ensure content is on top of the gradient */
        }

        .text-white {
            color: #ffffff;
        }

        .slide-title h3,
        .slide-text p {
            color: white;
            z-index: 2;
        }

        .service-card-app {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .tec-icon {
            height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body>

    <!--<div class="onloadpage" id="page_loader">
  <div class="pre-content">
   <div class="logo-pre"><img src="images/logo.png" alt="Logo" class="img-fluid" /></div>
   <div class="pre-text- text-radius text-light text-animation bg-b">Niwax - Creative Agency & Portfolio HTML
    Template Are 2 Seconds Away. Have Patience</div>
  </div>
 </div>-->


    @include('header')
    <section class="hero-slider hero-style">
        <div class="swiper-container">

            <div class="swiper-wrapper">

                <div class="swiper-slide">
                    <!-- <img src="" alt=""> -->
                    <div class="slide-inner slide-bg-image background-img"
                        data-background="{{ asset('images/sri/home1.jpg') }}">
                        {{-- <div class="gradient-overlay"></div> 
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <div data-swiper-parallax="300" class="slide-title">
                                        <h3 class="text-white">Comfortable Metal</h3>
                                    </div>
                                    <div data-swiper-parallax="400" class="slide-text">
                                        <h4 class="text-white">Experience the Comfort of Home</h4>
                                        <p>Relax in fully furnished rooms designed for your comfort and convenience, ensuring a stress-free stay.</p>
                                    </div>


                                    <div class="clearfix"></div>
                                    <div data-swiper-parallax="500" class="slide-btns">
                                        <a href="{{route('contact')}}" class="btn-main bg-btn lnk">Enquiry Now <i
                                                class="fas fa-chevron-right fa-icon"></i><span
                                                class="circle"></span></a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                </div>
                            </div>

                        </div> --}}
                    </div>
                </div>


                <div class="swiper-slide">
                    <div class="slide-inner slide-bg-image background-image"
                        data-background="{{ asset('images/sri/home2.jpeg') }}">
                        {{-- <div class="gradient-overlay"></div>  --}}
                        {{-- <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <div data-swiper-parallax="300" class="slide-title">
                                        <h3 class="text-white">Hygienic Food</h3>
                                    </div>
                                    <div data-swiper-parallax="400" class="slide-text">
                                        <h4 class="text-white">Delicious and Healthy Meals</h4>
                                        <p>Enjoy fresh, home-style meals every day, catering to both vegetarian and non-vegetarian preferences.</p>
                                    </div>


                                    <div class="clearfix"></div>
                                    <div data-swiper-parallax="500" class="slide-btns">
                                        <a href="{{route('contact')}}" class="btn-main bg-btn lnk">Enquiry Now <i
                                                class="fas fa-chevron-right fa-icon"></i><span
                                                class="circle"></span></a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- <img src="images/hero/web-development.png"> -->
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>




                <div class="swiper-slide">
                    <div class="slide-inner slide-bg-image background-img"
                        data-background="{{ asset('images/sri/home3.jpg') }}">
                        {{-- <div class="gradient-overlay"></div> 
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <div data-swiper-parallax="300" class="slide-title">
                                        <h3 class="text-white">Prime Location</h3>
                                    </div>
                                    <div data-swiper-parallax="400" class="slide-text">
                                        <h4 class="text-white">Convenience at Your Doorstep</h4>
                                        <p>Situated near DLF Back Gate, enjoy easy access to workplaces, colleges, and essential facilities.</p>
                                    </div>


                                    <div class="clearfix"></div>
                                    <div data-swiper-parallax="500" class="slide-btns">
                                        <a href="{{route('contact')}}" class="btn-main bg-btn lnk">Enquiry Now <i
                                                class="fas fa-chevron-right fa-icon"></i><span
                                                class="circle"></span></a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- <img src="images/hero/digital-marketing.png"> -->
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>

            </div>


            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>

        </div>
    </section>


    <section class="about-sec-app pad-tb pt60 dark-bg2">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="common-heading text-l">
                        <h2 class="mb30">Welcome to <span class="text-second text-bold">Signify </span></h2>
                        <p class="text-justify"> Signify is a signage company that specializes in Indoor signages,
                            Outdoor Signages, and Custom Signages. </p>
                        <p class="mt10 text-justify">We offer a wide range of visual communication solutions to help
                            businesses increase their visibility and convey their messages effectively. We specialize in
                            designing, manufacturing and installing high-quality indoor and outdoor branding, signage,
                            and retail display solutions.</p>
                        <p class="mt10 text-justify">Our services include small format signage, outdoor signs, digital
                            signages, and more.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="funfact">
                        <div class="row">

                            <div class="col-lg-6 col-md-6 col-sm-12 col-6">
                                <div class="funfct srcl2">
                                    <img src="images/icons/startup.svg" alt="startup icon">
                                    <span class="services-cuntr counter">6</span><span class="services-cuntr">+</span>
                                    <p>Years Experience </p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-6">
                                <div class="funfct srcl3">
                                    <img src="images/icons/team.svg" alt="team">
                                    <span class="services-cuntr counter">50</span><span class="services-cuntr">+</span>
                                    <p>Experienced Professionals
                                    </p>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-6">
                                <div class="funfct srcl5">
                                    <img src="images/icons/computers.svg" alt="projects">
                                    <span class="services-cuntr counter">100</span><span class="services-cuntr">%</span>
                                    <p>Successful Installations</p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-6">
                                <div class="funfct srcl1">
                                    <img src="images/icons/deal.svg" alt="Satisfaction">
                                    <span class="services-cuntr counter">100</span><span class="services-cuntr">%</span>
                                    <p>Client Satisfaction</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="service-section-app  dark-bg2 pb-5">

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="common-heading ptag">
                        <span>Our Products</span>
                        <h2>We offer Metal, Acrylic, Vinyl, Neon, Safety, Backlit Signages, Digital Prints, Glass Films,
                            and more to elevate your brand.</h2>
                    </div>
                </div>
            </div>
            <div class="row upset">
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.2s">
                    <div class="service-card-app hoshd">
                        <h4>Metal Signages</h4>
                        <div class="tec-icon mt30">
                            <img src="{{ asset('images/sri/steel.jpg') }}" alt="">
                        </div>
                        <p class="mt20 text-justify">
                            Metal signages are durable and versatile, often used for outdoor advertising, business
                            sings, and informational signs.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Acrylic Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/concrete.webp') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Acrylic signages are known for their sleek and modern appearance.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Vinyl Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/sand.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Vinyl singages are flexible and cost-effective, often used for banners, window graphics, and
                            vechile wraps.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Night Glow Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/wages.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Night glow signages are designed to be visible in low-light conditions.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Safety Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/checklist.png') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Saftety signages are essential for conveying important safety information and warnings.
                        </p>
                    </div>
                    <a href="{{ route('product') }}" class="btn btn-primary mt-5 d-flex justify-content-center">View
                        More</a>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Back Lit Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/electricwire.webp') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Back lit signages are illuminated from behind, creating a striking visual effect.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    <!-- <section class="clients-section-app pad-tb">
  <div class="container">
   <div class="row justify-content-center">
    <div class="col-lg-8">
     <div class="common-heading text-w">

      <h2 class="mb30">Some of our Clients</h2>
     </div>
    </div>
   </div>
   <div class="row">
    <div class="col-lg-12">
     <div class="client-logoset">
      <ul class="row text-center clearfix apppg">
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay=".2s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-1.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Admire</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay=".4s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-2.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Annai Masala</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay=".6s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-3.jpg" alt="clients"
          class="img-fluid"></div>
        <p>MK Auto Plast</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay=".8s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-4.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Bestie</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="1s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-5.jpg" alt="clients"
          class="img-fluid"></div>
        <p>BTM</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="1.2s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-6.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Camping</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="1.4s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-7.jpg" alt="clients"
          class="img-fluid"></div>
        <p>CPS</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="1.6s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-8.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Escon Logo</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="1.8s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-9.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Event Service</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="2s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-10.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Jai Academy</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="2.2s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-11.jpg" alt="clients"
          class="img-fluid"></div>
        <p>JJ</p>
       </li>
       <li class="col-lg-2 col-md-3 col-sm-4 col-6 mt30 wow fadeIn" data-wow-delay="2.4s">
        <div class="brand-logo hoshd"><img src="images/client/client-logo-12.jpg" alt="clients"
          class="img-fluid"></div>
        <p>Jungle Hikers</p>
       </li>
      </ul>
     </div>
     <div class="-cta-btn mt70">
      <div class="free-cta-title v-center wow zoomInDown">
       <a href="our-clients.html" class="btn-outline lnk">View More<span class="circle"></span></a>
      </div>
     </div>
    </div>
   </div>
  </div>
 </section> -->

    <section class="testinomial-section-app bg-none pad-tb">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <div class="common-heading text-l">
                        <span>What Our Residents Say</span>
                        <h2>Over 200+ Satisfied Guests</h2>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="pl50">
                        <div class="shape shape-a1"><img src="images/shape/shape-3.svg" alt="shape"></div>
                        <div class="testimonial-card-a tcd owl-carousel">
                            <div class="testimonial-card">
                                <div class="tt-text">
                                    <p>
                                        Signify designed and installed an amazing outdoor sign for our store. It has
                                        significantly increased foot traffic and brand visibility!

                                    </p>
                                </div>
                                <div class="client-thumbs mt30">
                                    <div class="media v-center upset">
                                        <div class="user-image bdr-radius"><img
                                                src="images/user-thumb/pojo-testimonial.png" alt="girl"
                                                class="img-fluid rounded-circle" /></div>
                                        <div class="media-body user-info v-center">
                                            <h5> – Rahul M</h5>

                                            <i class="fas fa-quote-right posiqut"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="testimonial-card">
                                <div class="tt-text">
                                    <p>
                                        The custom neon signage from Signify perfectly matches our brand identity. Their
                                        attention to detail and quick turnaround were impressive! </p>
                                </div>
                                <div class="client-thumbs mt30">
                                    <div class="media v-center upset">
                                        <div class="user-image bdr-radius"><img
                                                src="images/user-thumb/pojo-testimonial.png" alt="girl"
                                                class="img-fluid rounded-circle" /></div>
                                        <div class="media-body user-info v-center">
                                            <h5>– Priya S</h5>

                                            <i class="fas fa-quote-right posiqut"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="testimonial-card">
                                <div class="tt-text">
                                    <p>
                                        We ordered backlit and acrylic signs for our office, and the quality was
                                        outstanding. Highly recommend Signify for professional signage solutions!
                                    </p>
                                </div>
                                <div class="client-thumbs mt30">
                                    <div class="media v-center upset">
                                        <div class="user-image bdr-radius"><img
                                                src="images/user-thumb/pojo-testimonial.png" alt="girl"
                                                class="img-fluid rounded-circle" /></div>
                                        <div class="media-body user-info v-center">
                                            <h5>– Arjun S</h5>

                                            <i class="fas fa-quote-right posiqut"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="testimonial-card">
                                <div class="tt-text">
                                    <p>

                                        Signify created a stunning backlit sign for our store. The quality and design
                                        have truly elevated our brand presence! </p>
                                </div>
                                <div class="client-thumbs mt30">
                                    <div class="media v-center upset">
                                        <div class="user-image bdr-radius"><img
                                                src="images/user-thumb/pojo-testimonial.png" alt="girl"
                                                class="img-fluid rounded-circle" /></div>
                                        <div class="media-body user-info v-center">
                                            <h5>– Ravi S</h5>

                                            <i class="fas fa-quote-right posiqut"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="testimonial-card">
                                <div class="tt-text">
                                    <p>
                                        The custom acrylic signage from Signify was exactly what we needed. Professional
                                        service and quick installation!
                                    </p>
                                </div>
                                <div class="client-thumbs mt30">
                                    <div class="media v-center upset">
                                        <div class="user-image bdr-radius"><img
                                                src="images/user-thumb/pojo-testimonial.png" alt="girl"
                                                class="img-fluid rounded-circle" /></div>
                                        <div class="media-body user-info v-center">
                                            <h5>– Meera S</h5>

                                            <i class="fas fa-quote-right posiqut"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('footer')


    <!-- <div class="footer-row3">
  <div class="copyright">
   <div class="container">
    <div class="row">
     <div class="col-lg-12">
      <div class="footer-social-media-icons">
       <a href="https://www.facebook.com/careercounsellorkerala?mibextid=ZbWKwL
					" target="blank"><i class="fab fa-facebook"></i></a>
    
     <a href=" https://instagram.com/mycareercounsellor?igshid=OGQ5ZDc2ODk2ZA==" target="blank"><i
       class="fab fa-instagram"></i></a>
     <a href="https://youtube.com/@mycareercounsellor?si=deOSl3bAqhPsV4ph" target="blank"><i
       class="fab fa-youtube"></i></a>

      </div>

     </div>
    </div>
   </div>
  </div>
 </div> -->


    <script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="js/vendor/modernizr-3.5.0.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/plugin.min.js"></script>
    <script src="js/preloader.js"></script>
    <script src="js/dark-mode.js"></script>
    <script src="js/swiper.min.js"></script>

    <script src="js/main.js"></script>


</body>

</html>
