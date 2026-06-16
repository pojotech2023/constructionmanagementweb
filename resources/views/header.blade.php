<header class="header-pr nav-bg-b main-header navfix fixed-top menu-white">
    <div class="container-fluid m-pad">
        <div class="menu-header">
            <div class="dsk-logo"><a class="nav-brand" href="index.html">
                    <img src="images/logo/logo.png" alt="Logo" class="mega-white-logo" />
                    <img src="images/logo/logo.png" alt="Logo" class="mega-darks-logo" />
                </a>
            </div>
            <div class="custom-nav" role="navigation">
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('home') }}" class="menu-links">Home</a>

                    </li>
                    <li>
                        <a href="{{ route('about') }}" class="menu-links">About Us</a>
                    </li>

                    <li>
                        <a href="{{ route('product') }}" class="menu-links">Products</a>
                    </li>
                    <li>
                        <a href="{{ route('form') }}" class="menu-links">What are you looking for?</a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="menu-links">Contact Us</a>
                    </li>
                    <li>
                        <a href="{{ route('profile') }}" class="menu-links">Profile</a>
                    </li>
                    
                </ul>

                <ul class="nav-list right-end-btn">
                    <li class="hidemobile"><a data-bs-toggle="offcanvas" href="#offcanvasExample"
                            class="btn-round- btn-br bg-btn2"><i class="fas fa-phone-alt"></i></a></li>
                    <li class="hidemobile"><a href="{{ route('login') }}" class="btn-br bg-btn3 btshad-b2 lnk"><span
                                class="circle"></span>Login </a> </li>

                    <li class="hidedesktop"><a data-bs-toggle="offcanvas" href="#offcanvasExample"
                            class="btn-round- btn-br bg-btn2"><i class="fas fa-phone-alt"></i></a></li>
                    <li class="navm- hidedesktop"> <a class="toggle" href="#"><span></span></a></li>
                </ul>
            </div>
        </div>

        <nav id="main-nav">
            <ul class="first-nav">
                <li>
                    <a href="{{ route('home') }}">Home</a>

                </li>
                <li>
                    <a href="{{ route('about') }}">About Us</a>
                </li>

                <li>
                    <a href="{{ route('product') }}">Products</a>
                </li>
                <li>
                    <a href="{{ route('form') }}">What are you looking for?</a>
                </li>
                <li>
                    <a href="{{ route('contact') }}">Contact Us</a>
                </li>
                <li>
                    <a href="{{ route('profile') }}">Profile</a>
                </li>
                <li><a href="{{ route('login') }}"><span class="circle"></span>Login </a> </li>
            </ul>
        </nav>
    </div>
</header>


<div class="niwaxofcanvas offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample">
    <div class="offcanvas-body">
        <div class="cbtn animation">
            <div class="btnclose"> <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button></div>
        </div>

        <div class="getintouchblock mt30">
            <h4>Get In Touch</h4>

            <div class="media mt15">
                <div class="icondive"><img src="images/icons/call.svg" alt="icon"></div>
                <div class="media-body getintouchinfo">
                    <a href="tel:+917401707707">+91 74017 07707 <span></span></a>
                    {{-- <a href="tel:+91 70928 50142">+91 70928 50142 <span></span></a> --}}
                </div>
            </div>
            <div class="media mt15">
                <div class="icondive"><img src="images/icons/whatsapp.svg" alt="icon"></div>
                <div class="media-body getintouchinfo">
                    <a href="https://wa.me/+917401707707" target="_blank">+91 74017 07707 <span></span></a>
                    {{-- <a href="https://wa.me/+9170928 50142" target="_blank">+91 70928 50142 <span></span></a> --}}
                </div>
            </div>
            <div class="media mt15">
                <div class="icondive"><img src="images/icons/mail.svg" alt="icon"></div>
                <div class="media-body getintouchinfo">
                    <a href="mailto:signify@gmail.com" target="_blank">signify@gmail.com
                        <span>Online Support</span></a>
                </div>
            </div>
            <div class="media mt15">
                <div class="icondive"><img src="images/icons/map.svg" alt="icon"></div>
                <div class="media-body getintouchinfo">
                    <a href="#">
                        100, Alapakkam Main Road, Alapkkam, Porur, Chennai -
                        600116.
                        <span>Visit Our Office</span></a>
                </div>
            </div>
        </div>
        <div class="contact-data mt30">
            <h4>Follow Us On:</h4>
            <div class="social-media-linkz mt10">
                <a href="
					" target="blank"><i class="fab fa-facebook"></i></a>
                <a href=""><img src="./img/download/google.png" width="18px" alt=""></a>
                <a href="" target="blank"><img src="./img/download/whatsapp.png" width="18px"
                        alt=""></a>
                <a href=" " target="blank"><i class="fab fa-instagram"></i></a>
                <!-- <a href="javascript:void(0)" target="blank"><i class="fab fa-linkedin"></i></a> -->
                <a href="" target="blank"><i class="fab fa-youtube"></i></a>
                <!-- <a href="javascript:void(0)" target="blank"><i class="fab fa-pinterest-p"></i></a> -->
            </div>
        </div>
    </div>
</div>
