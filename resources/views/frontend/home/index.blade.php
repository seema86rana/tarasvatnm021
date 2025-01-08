@extends('layouts.frontend')

@section('title', $title)

@section('css')
@endsection

@section('content')
    <!-- Header -->
    <header id="header" class="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="text-container">
                        <h1 class="h1-large">The ahevalnest for <span class="replace-me">real-time insights, streamlined report, insightful choices</span></h1>
                        <p class="p-large">Online reporting provides quick access to data, easy sharing, and secure, personalized reports for better decision-making.</p>
                        @auth
                            <a class="btn-solid-lg" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
                            
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        @else
                            <a class="btn-solid-lg" href="{{ route('login') }}">Log In</a>
                        @endauth
                    </div> <!-- end of text-container -->
                </div> <!-- end of col -->
                <div class="col-lg-6">
                    <div class="image-container">
                        <img class="img-fluid" src="{{ asset('/') }}assets/frontend/images/header-illustration.svg" alt="alternative">
                    </div> <!-- end of image-container -->
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </header> <!-- end of header -->
    <!-- end of header -->


    <!-- Features -->
    <div id="features" class="cards-1">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="h2-heading">Tarasvat ahevalnest is packed with <span>awesome features</span></h2>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
            <div class="row">
                <div class="col-lg-12">
                    
                    <!-- Card -->
                    <div class="card">
                        <div class="card-icon">
                            <span class="fas fa-headphones-alt"></span>
                        </div>
                        <div class="card-body">
                            <h4 class="card-title">Real-Time Data Monitoring</h4>
                            <p>Real-time data for smarter decisions: monitor machine performance, efficiency, and stoppages.</p>
                        </div>
                    </div>
                    <!-- end of card -->

                    <!-- Card -->
                    <div class="card">
                        <div class="card-icon green">
                            <span class="far fa-clipboard"></span>
                        </div>
                        <div class="card-body">
                            <h4 class="card-title">Interactive Data Insights</h4>
                            <p>Interactive data insights with color-coded machine start, stop, and efficiency for easy analysis.</p>
                        </div>
                    </div>
                    <!-- end of card -->

                    <!-- Card -->
                    <div class="card">
                        <div class="card-icon blue">
                            <span class="far fa-comments"></span>
                        </div>
                        <div class="card-body">
                            <h4 class="card-title">Automated Reporting</h4>
                            <p>Automated bar chart reports with past comparisons, sent via email and WhatsApp.</p>
                        </div>
                    </div>
                    <!-- end of card -->

                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of cards-1 -->
    <!-- end of services -->


    <!-- Details 1 -->
    <div id="details" class="basic-1 bg-gray">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-xl-5">
                    <div class="text-container">
                        <h2>Manage your customer’s expectations and get them to trust you</h2>
                        <p>Deliver consistent machine performance to satisfy customer needs and foster trust.</p>
                        <p>Empower your customers to manage users with detailed machine reports, performance, and efficiency, driving more clients and boosting production.</p>
                        <!-- <a class="btn-solid-reg" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Modal</a> -->
                    </div> <!-- end of text-container -->
                </div> <!-- end of col -->
                <div class="col-lg-6 col-xl-7">
                    <div class="image-container">
                        <img class="img-fluid" src="{{ asset('/') }}assets/frontend/images/details-1.svg" alt="alternative">
                    </div> <!-- end of image-container -->
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of basic-1 -->
    <!-- end of details 1 -->


    <!-- Details Modal -->
    <div id="staticBackdrop" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="row">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="col-lg-8">
                        <div class="image-container">
                            <img class="img-fluid" src="{{ asset('/') }}assets/frontend/images/details-modal.jpg" alt="alternative">
                        </div> <!-- end of image-container -->
                    </div> <!-- end of col -->
                    <div class="col-lg-4">
                        <h3>Goals Setting</h3>
                        <hr>
                        <p>In gravida at nunc sodales pretium. Vivamus semper, odio vitae mattis auctor, elit elit semper magna ac tum nico vela spider</p>
                        <h4>User Feedback</h4>
                        <p>Sapien vitae eros. Praesent ut erat a tellus posuere nisi more thico cursus pharetra finibus posuere nisi. Vivamus feugiat</p>
                        <ul class="list-unstyled li-space-lg">
                            <li class="d-flex">
                                <i class="fas fa-chevron-right"></i>
                                <div class="flex-grow-1">Tincidunt sem vel brita bet mala</div>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-chevron-right"></i>
                                <div class="flex-grow-1">Sapien condimentum sacoz sil necr</div>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-chevron-right"></i>
                                <div class="flex-grow-1">Fusce interdum nec ravon fro urna</div>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-chevron-right"></i>
                                <div class="flex-grow-1">Integer pulvinar biolot bat tortor</div>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-chevron-right"></i>
                                <div class="flex-grow-1">Id ultricies fringilla fangor raq trinit</div>
                            </li>
                        </ul>
                        <a id="modalCtaBtn" class="btn-solid-reg" href="#your-link">Details</a>
                        <button type="button" class="btn-outline-reg" data-bs-dismiss="modal">Close</button>
                    </div> <!-- end of col -->
                </div> <!-- end of row -->
            </div> <!-- end of modal-content -->
        </div> <!-- end of modal-dialog -->
    </div> <!-- end of modal -->
    <!-- end of details modal -->


    <!-- Details 2 -->
    <div class="basic-2">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="image-container">
                        <img class="img-fluid" src="{{ asset('/') }}assets/frontend/images/details-2.png" alt="alternative">
                    </div> <!-- end of image-container -->
                </div> <!-- end of col -->
                <div class="col-lg-6">
                    <div class="text-container">
                        <h2>Anyone can start using this site with minimum skills</h2>
                        <p>Our system offers an exceptional user experience, with a birdview and a helpful section for easy understanding.</p>
                        <ul class="list-unstyled li-space-lg">
                            <li class="d-flex">
                                <i class="fas fa-square"></i>
                                <div class="flex-grow-1"><b>Sign up</b> with our system.</div>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-square"></i>
                                <div class="flex-grow-1"><b>Add device information</b> for seamless tracking.</div>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-square"></i>
                                <div class="flex-grow-1"><b>Enter shift</b> details for accurate reporting.</div>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-square"></i>
                                <div class="flex-grow-1"><b>Enjoy Birdview</b> to monitor machine status effortlessly, with a helpful guide.</div>
                            </li>
                        </ul>
                    </div> <!-- end of text-container -->
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of basic-2 -->
    <!-- end of details 2 -->


    <!-- Testimonials -->
  <?php /*  <div class="slider-1 bg-gray">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="h2-heading">Few words from our clients</h2>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
            <div class="row">
                <div class="col-lg-12">

                    <!-- Card Slider -->
                    <div class="slider-container">
                        <div class="swiper-container card-slider">
                            <div class="swiper-wrapper">
                                
                                <!-- Slide -->
                                <div class="swiper-slide">
                                    <div class="card">
                                        <img class="card-image" src="{{ asset('/') }}assets/frontend/images/testimonial-1.jpg" alt="alternative">
                                        <div class="card-body">
                                            <p class="testimonial-text">Tortor sodales eget. Vivamus imperdiet leo eu risus tincidunt uris. Proin placerat, urna hendrerit placerat erase convallis</p>
                                            <p class="testimonial-author">Jude Thorn - Designer</p>
                                        </div>
                                    </div>
                                </div> <!-- end of swiper-slide -->
                                <!-- end of slide -->
        
                                <!-- Slide -->
                                <div class="swiper-slide">
                                    <div class="card">
                                        <img class="card-image" src="{{ asset('/') }}assets/frontend/images/testimonial-2.jpg" alt="alternative">
                                        <div class="card-body">
                                            <p class="testimonial-text">Eros volutpat ante mauris euismod sem, ut varius nisi lectus in urna. Integer luctus, nunc eget maximus intem, orci risus</p>
                                            <p class="testimonial-author">Roy Smith - Developer</p>
                                        </div>
                                    </div>        
                                </div> <!-- end of swiper-slide -->
                                <!-- end of slide -->
        
                                <!-- Slide -->
                                <div class="swiper-slide">
                                    <div class="card">
                                        <img class="card-image" src="{{ asset('/') }}assets/frontend/images/testimonial-3.jpg" alt="alternative">
                                        <div class="card-body">
                                            <p class="testimonial-text">Sed congue ex quam, sit amet venenatis dolor lacinia vulputate. Nunc pulvinar ex ex, sit amet scelerisque tellus pretium semper</p>
                                            <p class="testimonial-author">Marsha Singer - Marketer</p>
                                        </div>
                                    </div>        
                                </div> <!-- end of swiper-slide -->
                                <!-- end of slide -->
        
                                <!-- Slide -->
                                <div class="swiper-slide">
                                    <div class="card">
                                        <img class="card-image" src="{{ asset('/') }}assets/frontend/images/testimonial-4.jpg" alt="alternative">
                                        <div class="card-body">
                                            <p class="testimonial-text">Etiam est lorem, interdum non semper ut, bibendum vitae ante. Pellente sollicitun sagittis lectus. Aenean in comod</p>
                                            <p class="testimonial-author">Tim Shaw - Designer</p>
                                        </div>
                                    </div>
                                </div> <!-- end of swiper-slide -->
                                <!-- end of slide -->
        
                                <!-- Slide -->
                                <div class="swiper-slide">
                                    <div class="card">
                                        <img class="card-image" src="{{ asset('/') }}assets/frontend/images/testimonial-5.jpg" alt="alternative">
                                        <div class="card-body">
                                            <p class="testimonial-text">Quisque nec turpis placerat, accumsan lorem lobortis, vestibulum elit. Fusce finibus nisl varius semper elementum vivamus</p>
                                            <p class="testimonial-author">Lindsay Spice - Marketer</p>
                                        </div>
                                    </div>        
                                </div> <!-- end of swiper-slide -->
                                <!-- end of slide -->
        
                                <!-- Slide -->
                                <div class="swiper-slide">
                                    <div class="card">
                                        <img class="card-image" src="{{ asset('/') }}assets/frontend/images/testimonial-6.jpg" alt="alternative">
                                        <div class="card-body">
                                            <p class="testimonial-text">Vulputate sed tellus nec, imperdiet luctus purus. Morbi lobortis massa a mi interdum condimentum. Integer non gravida nisi</p>
                                            <p class="testimonial-author">Ann Blake - Developer</p>
                                        </div>
                                    </div>        
                                </div> <!-- end of swiper-slide -->
                                <!-- end of slide -->
                            
                            </div> <!-- end of swiper-wrapper -->
        
                            <!-- Add Arrows -->
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                            <!-- end of add arrows -->
        
                        </div> <!-- end of swiper-container -->
                    </div> <!-- end of slider-container -->
                    <!-- end of card slider -->

                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of slider-1 -->
    <!-- end of testimonials --> */ ?>


    <!-- Invitation -->
    <div class="basic-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h4>Tarasvat will change the way you think about ahevalnest solutions due to it’s advanced tools and integrated functionalities</h4>
                    @auth
                        <a class="btn-outline-lg page-scroll" href="javascript:void(0);"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @else
                        <a class="btn-outline-lg page-scroll" href="{{ route('login') }}">Log in</a>
                    @endauth
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of basic-3 -->
    <!-- end of invitation -->


    <!-- Pricing -->
 <?php /*   <div id="pricing" class="cards-2 bg-gray">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="h2-heading">Free forever tier and 2 pro plans</h2>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
            <div class="row">
                <div class="col-lg-12">
                    
                    <!-- Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <img class="decoration-lines" src="{{ asset('/') }}assets/frontend/images/decoration-lines.svg" alt="alternative"><span>Free tier</span><img class="decoration-lines flipped" src="{{ asset('/') }}assets/frontend/images/decoration-lines.svg" alt="alternative">
                            </div>
                            <ul class="list-unstyled li-space-lg">
                                <li>Fusce pulvinar eu mi acm</li>
                                <li>Curabitur consequat nisl bro</li>
                                <li>Reget facilisis molestie</li>
                                <li>Vivamus vitae sem in tortor</li>
                                <li>Pharetra vehicula ornares</li>
                                <li>Vivamus dignissim sit amet</li>
                                <li>Ut convallis aliquama set</li>
                            </ul>
                            <div class="price">Free</div>
                            <a href="sign-up.html" class="btn-solid-reg">Sign up</a>
                        </div>
                    </div>
                    <!-- end of card -->

                    <!-- Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <img class="decoration-lines" src="{{ asset('/') }}assets/frontend/images/decoration-lines.svg" alt="alternative"><span>Advanced</span><img class="decoration-lines flipped" src="{{ asset('/') }}assets/frontend/images/decoration-lines.svg" alt="alternative">
                            </div>
                            <ul class="list-unstyled li-space-lg">
                                <li>Nunc commodo magna quis</li>
                                <li>Lacus fermentum tincidunt</li>
                                <li>Nullam lobortis porta diam</li>
                                <li>Announcing of invita mro</li>
                                <li>Dictum metus placerat luctus</li>
                                <li>Sed laoreet blandit mollis</li>
                                <li>Mauris non luctus est</li>
                            </ul>
                            <div class="price">$19<span>/month</span></div>
                            <a href="sign-up.html" class="btn-solid-reg">Sign up</a>
                        </div>
                    </div>
                    <!-- end of card -->

                    <!-- Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <img class="decoration-lines" src="{{ asset('/') }}assets/frontend/images/decoration-lines.svg" alt="alternative"><span>Professional</span><img class="decoration-lines flipped" src="{{ asset('/') }}assets/frontend/images/decoration-lines.svg" alt="alternative">
                            </div>
                            <ul class="list-unstyled li-space-lg">
                                <li>Quisque rutrum mattis</li>
                                <li>Quisque tristique cursus lacus</li>
                                <li>Interdum sollicitudin maec</li>
                                <li>Quam posuerei pellentesque</li>
                                <li>Est neco gravida turpis integer</li>
                                <li>Mollis felis. Integer id quam</li>
                                <li>Id tellus hendrerit lacinia</li>
                            </ul>
                            <div class="price">$29<span>/month</span></div>
                            <a href="sign-up.html" class="btn-solid-reg">Sign up</a>
                        </div>
                    </div>
                    <!-- end of card -->

                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of cards-2 -->
    <!-- end of pricing --> */ ?>


    <!-- Questions -->
    <div id="faq" class="accordion-1">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="h2-heading">Frequent questions</h2>
                </div> <!-- end of col -->
            </div> <!-- end of row -->   
            <div class="row">
                <div class="col-lg-12">
                    <div class="accordion" id="accordionExample">
                        
                        <!-- Accordion Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Is the system scalable as my business grows?</button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                <div class="accordion-body">Yes, the system is designed to scale with your business, allowing you to add more devices, users, and locations as needed.</div>
                            </div>
                        </div>
                        <!-- end of accordion-item -->

                        <!-- Accordion Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">How do I add new users or devices?</button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                <div class="accordion-body">You can request to admin to add new users or devices through the admin panel right now, In future user can easily do the same things.</div>
                            </div>
                        </div>
                        <!-- end of accordion-item -->

                        <!-- Accordion Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">What types of data can I track with the system?</button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                <div class="accordion-body">You can track a wide range of data, including machine performance, efficiency, no of stoppage, last running time, and last stopped time.</div>
                            </div>
                        </div>
                        <!-- end of accordion-item -->

                    </div> <!-- end of accordion -->
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of accordion-1 -->
    <!-- end of questions -->
@endsection

@section('script')
@endsection
