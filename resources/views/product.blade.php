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

        .tec-icon img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 8px;
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
    <section class="breadcrumb-area banner-1">
        <div class="text-block">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 v-center">
                        <div class="bread-inner">
                            <div class="bread-menu">
                                <ul>
                                    <li><a href="index.html">Home</a></li>
                                    <li><a href="#">Products</a></li>
                                </ul>
                            </div>
                            <div class="bread-title">
                                <h2>Products</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="service-section-app  dark-bg2 pb-5 mt-5">

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="common-heading ptag">
                        <span>Our Products</span>
                        {{-- <h2>We offer Metal, Acrylic, Vinyl, Neon, Safety, Backlit Signages, Digital Prints, Glass Films, and more to elevate your brand.</h2> --}}
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
                        <p class="mt20 text-justify">
                            They can be made from materials like aluminum, steel, or brass and can be customized with
                            various finishes and coatings.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Acrylic Signages</h4>
                        <ul class="-service-list mt10">

                        </ul>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/concrete.webp') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Acrylic signages are known for their sleek and modern appearance.
                        </p>
                        <p class="mt20 text-justify">
                            They are made from a transparent plastic material that can be easily shaped and colored,
                            making them ideal for indoor use, such as office signs and decorative displays.
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
                        <p class="mt20 text-justify">
                            They are made from a durable vinyl material that can be printed with vibrant colors and
                            detailed graphics.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Night Glow Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/sand.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Night glow signages are designed to be visible in low-light conditions.
                        </p>
                        <p class="mt20 text-justify">
                            They are often used for emergency exits, safety instructions, and other critical information
                            that needs to be seen in the dark.
                        </p>
                        <p class="mt20 text-justify">
                            These signs are typically made with photoluminescent materials.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Safety Signages</h4>
                        <ul class="-service-list mt10">

                        </ul>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/wages.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Saftety signages are essential for conveying important safety information and warnings.
                        </p>
                        <p class="mt20 text-justify">
                            They are used in workplaces, public areas, and construction sites to prevent accidents and
                            ensure compliance with safety regulations.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Back Lit Signages</h4>
                        <ul class="-service-list mt10">

                        </ul>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/checklist.png') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Back lit signages are illuminated from behind, creating a striking visual effect.
                        </p>
                        <p class="mt20 text-justify">
                            They are commonly used for storefronts, advertising displays, and menu boards.
                        </p>
                        <p class="mt20 text-justify">
                            These signs use LED lights or other light sources to enhance visibility.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>ACP Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/electricwire.webp') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            ACP (Aluminum Composite Panel) signages are made from a combination of aluminum and plastic.
                        </p>
                        <p class="mt20 text-justify">
                            They are lightweight, durable, and weather-resistant, making them suitable for both indoor
                            and outdoor use.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>WPC Signages</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/tickets.png') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            WPC (Wood Plastic Composite) signages combine the look of wood with the durability of
                            plastic.
                        </p>
                        <p class="mt20 text-justify">
                            They are eco-friendly and resistant to moisture, making them ideal for outdoor applications.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.2s">
                    <div class="service-card-app hoshd">
                        <h4>Neon Signages</h4>
                        <div class="tec-icon mt30">
                            <img src="{{ asset('images/sri/interior.jpg') }}" alt="">
                        </div>
                        <p class="mt20 text-justify">
                            Neon signages are eye-catching and vibrant, made using gas-filled tubes that emit a bright
                            glow when electrified. These signs are often used for advertising, storefronts, and
                            decorative purposes.
                        </p>
                        <p class="mt20 text-justify">
                            Neon signs can be custom-made in various shapes, colors, and sizes, making them a popular
                            choice for businesses looking to attract attention and create a unique visual impact.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Digital Prints</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/granite.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Digital prints are high-quality images printed using digital technology.
                        </p>
                        <p class="mt20 text-justify">
                            They can be used for a variety of signage applications, including posters, banners, and wall
                            graphics.
                        </p>
                        <p class="mt20 text-justify">
                            Digital printing allows for precise and vibrant color reproduction.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Photo & Canvas Frames</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/drawing.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Photo frames are designed to display and protect photographs. They come in a variety of
                            materials, including wood, metal, plastic, and glass.
                            Photo frames can be simple and functional or decorative and ornate, making them suitable for
                            different styles and settings. They are commonly used to showcase personal memories, family
                            portraits, and artwork.
                        </p>
                        <p class="mt20 text-justify">
                            Canvas frames are used to mount and display canvas prints. These frames can be made from
                            wood or metal and are designed to support the stretched canvas, ensuring it remains taut and
                            flat. Canvas frames can be simple or decorative, and they are often used for displaying
                            artwork, photographs, and custom prints in homes, galleries, and offices.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Sandwich Boards</h4>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/material.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Sandwich acrylic boards are display frames made from two sheets of clear acrylic. The
                            content, such as a poster or sign, is placed between these sheets, creating a sleek and
                            modern look.
                        </p>
                        <p class="mt20 text-justify">
                            These boards are lightweight and durable, offering a glass-like appearance without the
                            fragility and weight of glass. They are commonly used in businesses, hotels, schools, and
                            other settings where a professional and clean display is needed.
                        </p>

                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Glass Films</h4>
                        <ul class="-service-list mt10">

                        </ul>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/tiles.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Glass films are thin sheets of material applied to glass surfaces to enhance their
                            appearance, provide privacy, or improve energy efficiency.
                            These films can be decorative, such as frosted or etched designs, or functional, like
                            UV-blocking or safety films.
                        </p>
                        <p class="mt20 text-justify">
                            They are commonly used on windows, doors, and partitions in homes, offices, and commercial
                            spaces.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt30 wow fadeIn" data-wow-delay="0.5s">
                    <div class="service-card-app hoshd">
                        <h4>Marketing Signages</h4>
                        <ul class="-service-list mt10">

                        </ul>
                        <div class="tec-icon mt30">
                            <ul class="servc-icon-sldr">
                                <img src="{{ asset('images/sri/transport.jpg') }}" alt="">
                            </ul>
                        </div>
                        <p class="mt20 text-justify">
                            Marketing signages are used to promote products, services, and brands.
                        </p>
                        <p class="mt20 text-justify">
                            They can be found in retail stores, trade shows, and events, and are designed to attract
                            attention and convey marketing messages effectively.
                        </p>

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
