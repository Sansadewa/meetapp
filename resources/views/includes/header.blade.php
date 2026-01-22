<header class="topbar">
    <nav class="navbar top-navbar navbar-expand-lg navbar-dark">
        <div class="navbar-header">
            <a class="nav-toggler waves-effect waves-light d-block d-lg-none" href="javascript:void(0)"><i
                    class="ti-menu ti-close"></i></a>
            <a class="navbar-brand" href=".">
                <!-- Logo icon -->
                <b class="logo-icon">
                    <img src="{{ url('public/image/meetappico.png') }}" width="45" alt="homepage" class="light-logo" />
                </b>
                <!--End Logo icon -->
                <!-- Logo text -->
                <span class="logo-text">
                    <img src="{{ url('public/image/meetapp.png') }}" class="light-logo" alt="homepage" />
                </span>
            </a>
            <!-- ============================================================== -->
            <!-- Toggle which is visible on mobile only -->
            <!-- ============================================================== -->
            <a class="topbartoggler d-block d-lg-none waves-effect waves-light" href="javascript:void(0)"
                data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
        </div>
        <div class="navbar-collapse collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <!-- This is  -->
                <!--   <li class="nav-item"> <a class="nav-link sidebartoggler d-none d-md-block waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li> -->
            </ul>
            <!-- ============================================================== -->
            <!-- Right side toggle and nav items -->
            <!-- ============================================================== -->
            <ul class="navbar-nav justify-content-end">
                <!-- Profile -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"> <i class="fas fa-video"></i>
                        <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right mailbox animated bounceInDown">
                        <ul class="list-style-none">
                            <li>
                                <div class="border-bottom rounded-top py-3 px-4">
                                    <div class="mb-0 font-weight-medium font-16">Zoom Request</div>
                                </div>
                            </li>
                            <li>
                                <div class="message-center notifications position-relative ps-container ps-theme-default"
                                    style="height:250px;" data-ps-id="89d57c61-e9d3-744c-f5be-0fcef8f30b56">
                                    <!-- Message -->
                                    <a href="javascript:void(0)"
                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                        <span class="btn btn-danger rounded-circle btn-circle"><i
                                                class="fa fa-link"></i></span>
                                        <div class="w-75 d-inline-block v-middle pl-2">
                                            <h5 class="message-title mb-0 mt-1">Luanch Admin</h5> <span
                                                class="font-12 text-nowrap d-block time text-truncate">Just see the my
                                                new admin!</span> <span class="font-12 text-nowrap d-block subtext">9:30
                                                AM</span>
                                        </div>
                                    </a>
                                    <!-- Message -->
                                    <a href="javascript:void(0)"
                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                        <span class="btn btn-success rounded-circle btn-circle"><i
                                                class="ti-calendar"></i></span>
                                        <div class="w-75 d-inline-block v-middle pl-2">
                                            <h5 class="message-title mb-0 mt-1">Event today</h5> <span
                                                class="font-12 text-nowrap d-block time text-truncate">Just a reminder
                                                that you have event</span> <span
                                                class="font-12 text-nowrap d-block subtext">9:10 AM</span>
                                        </div>
                                    </a>
                                    <!-- Message -->
                                    <a href="javascript:void(0)"
                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                        <span class="btn btn-info rounded-circle btn-circle"><i
                                                class="ti-settings"></i></span>
                                        <div class="w-75 d-inline-block v-middle pl-2">
                                            <h5 class="message-title mb-0 mt-1">Settings</h5> <span
                                                class="font-12 text-nowrap d-block time text-truncate">You can customize
                                                this template as you want</span> <span
                                                class="font-12 text-nowrap d-block subtext">9:08 AM</span>
                                        </div>
                                    </a>
                                    <!-- Message -->
                                    <a href="javascript:void(0)"
                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                        <span class="btn btn-primary rounded-circle btn-circle"><i
                                                class="ti-user"></i></span>
                                        <div class="w-75 d-inline-block v-middle pl-2">
                                            <h5 class="message-title mb-0 mt-1">Pavan kumar</h5> <span
                                                class="font-12 text-nowrap d-block time text-truncate">Just see the my
                                                admin!</span> <span class="font-12 text-nowrap d-block subtext">9:02
                                                AM</span>
                                        </div>
                                    </a>
                                    <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;">
                                        <div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                                    </div>
                                    <div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;">
                                        <div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link border-top text-center text-dark pt-3" href="./request-zoom">
                                    <strong>Check all requests</strong> <i class="fa fa-angle-right"></i> </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <img src="{{ url('public/image/flamenco-196.png') }}" alt="user" width="30"
                            class="profile-pic rounded-circle" />
                    </a>
                    <div class="dropdown-menu mailbox dropdown-menu-right animated bounceInDown">
                        <ul class="dropdown-user list-style-none">
                            <li>
                                <div class="dw-user-box p-3 d-flex">
                                    <div class="u-img"><img src="{{ url('public/image/flamenco-196.png') }}" alt="user"
                                            class="rounded" width="80"></div>
                                    <div class="u-text ml-2">
                                        <h4 class="mb-0">{{session('nama')}}</h4>
                                        <p class="text-muted mb-1 font-14">{{ session('nama_unit_kerja') ? session('nama_unit_kerja') : (session('level') == '2' ? 'Administrator' : '') }}
                                        </p>
                                        <a href="./profile"
                                            class="btn btn-rounded btn-danger btn-sm text-white d-inline-block">Lihat
                                            Profile</a>
                                    </div>
                                </div>
                            </li>
                            <li role="separator" class="dropdown-divider"></li>
                            <li class="user-list"><a class="px-3 py-2" href="./today"><i class="mdi mdi-calendar-text"></i> Rapat Hari Ini</a>
                            </li>
                            <li class="user-list"><a class="px-3 py-2" href="./lists"><i class="mdi mdi-calendar-text"></i> Daftar Rapat</a>
                            </li>
                            <li class="user-list"><a class="px-3 py-2" href="./rapat"><i class="mdi mdi-comment-processing-outline"></i> Buat Rapat</a>
                            </li>
                            @if(session('level') == '2')
                            <li class="user-list"><a class="px-3 py-2" href="./request-zoom"><i class="fas fa-video"></i> Request Zoom</a></li>
                            @endif                            
                            <li role="separator" class="dropdown-divider"></li>
                            <li class="user-list"><a class="px-3 py-2" href="./logout"><i class="fa fa-power-off"></i>
                                    Logout</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>