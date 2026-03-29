@php
    $user = auth()->user();
   // dd($user->sup_id);
@endphp
 <div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="float-left">
                <div class="hamburger sidebar-toggle">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </div>
            </div>
            <div class="float-right">

                <div class="navbar--nav ml-auto">
                    <ul class="nav">
                        <!-- Nav User Start -->
                        <li class="nav-item dropdown nav--user online">
                            <a href="#" class="nav-link" data-toggle="dropdown">
                                {{-- <span class="user-avatar">{{ $user->emp_name }}</span> --}}
                                <i class="fa fa-angle-down"></i>
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <form method="post" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <input style="margin-left: 40px;" class="btn btn-sm btn-primary" type="submit" value="Logout">
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <!-- Nav User End -->
                    </ul>
                </div>

            <!--     <div class="dropdown dib">
                    <div class="header-icon" data-toggle="dropdown">
                        <span class="user-avatar">{{ $user->name }}
                            <i class="ti-angle-down f-s-10"></i>
                        </span>
                        <div class="drop-down dropdown-profile dropdown-menu dropdown-menu-right">

                            <div class="dropdown-content-body">
                                <ul>
                                    <li>
                                        <form method="post" action="{{ route('admin.logout') }}">
                                        @csrf
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input style="padding: 10px;" class="btn btn-primary w-100 mt-2" type="submit" value="Logout">
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
