  <div class="sidebar sidebar-hide-to-small sidebar-shrink sidebar-gestures">
      <div class="nano">
          <div class="nano-content">
              <ul>
                  <div class="logo">
                      <a href="{{ route('admin.dashboard') }}">
                          <div class="login-logo" style="margin-top:-15px;">
                              <img style="width:100%;" src={{ asset('assets/logo/logo.jpeg') }} alt="Logo" />
                          </div>
                      </a>
                  </div>

                  <li><a href="{{ route('admin.dashboard') }}"><i class="ti-home"></i>Dashboard</a></li>
                  <li><a href="{{ route('admin.employee_list') }}"><i class="ti-layout-grid4-alt"></i>Employee List
                          (Treewise)</a></li>
                  <li>
                      <a class="sidebar-sub-toggle"><i class="ti-layout-grid4-alt"></i>Agenda<span
                              class="sidebar-collapse-icon ti-angle-down"></span></a>
                      <ul>
                          <li><a href="{{ route('admin.agenda_on_my_supervision') }}">On My Supervision</a></li>
                          <li><a href="{{ route('admin.agenda_all_employee') }}">All Employee</a></li>
                      </ul>
                  </li>
                  <li>
                      <a class="sidebar-sub-toggle"><i class="ti-layout-grid4-alt"></i>Task<span
                              class="sidebar-collapse-icon ti-angle-down"></span></a>
                      <ul>
                          <li><a href="{{ route('admin.task_on_my_supervision') }}">On My Supervision</a></li>
                          <li><a href="{{ route('admin.task_all_employee') }}">All Employee</a></li>
                      </ul>
                  </li>
                  <li>
                      <a class="sidebar-sub-toggle"><i class="ti-layout-grid4-alt"></i>Tour<span
                              class="sidebar-collapse-icon ti-angle-down"></span></a>
                      <ul>
                          <li><a href="{{ route('admin.tour_on_my_supervision') }}">On My Supervision</a></li>
                          <li><a href="{{ route('admin.tour_all_employee') }}">All Employee</a></li>
                      </ul>
                  </li>
                  <li>
                      <a class="sidebar-sub-toggle"><i class="ti-layout-grid4-alt"></i>Rating & Reviews<span
                              class="sidebar-collapse-icon ti-angle-down"></span></a>
                      <ul>
                          <li><a href="{{ route('admin.rating_review_on_my_supervision') }}">On My Supervision</a></li>
                          {{-- <li><a href="{{ route('admin.rating_review_all_employee') }}">All Employee</a></li> --}}
                      </ul>
                  </li>

                  {{-- <li><a href="{{ route('admin.sendMail') }}"><i class="ti-email"></i>Send Mail</a></li> --}}
                  @php
                      $userId = session('userId');
                  @endphp
                  @if ($userId == '1021' || $userId == '3221')
                      <li><a href="{{ route('admin.rating_review_all_employee') }}"><i class="ti-layout-grid4-alt"></i>Overall
                              Rating</a></li>
                  @endif

                  <li><a href="{{ route('admin.emp_assign_task') }}"><i class="ti-check-box"></i>Assign Task</a></li>

                  {{-- <li>

                        <form method="post" action="{{ route('admin.logout') }}">
                        @csrf
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input style="padding: 10px;" class="btn btn-primary w-50 mt-2" type="submit" value="Logout">
                        </form>
                    </li> --}}
              </ul>
          </div>
      </div>
  </div>
