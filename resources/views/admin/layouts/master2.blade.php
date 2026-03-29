<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.layouts._head')
    @yield('styles')

</head>

<body>


    <!-- /# sidebar -->
    @include('admin.layouts._sidebar')

    <div class="header">
        @include('admin.layouts._header')
    </div>


    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                {{-- @include('admin.layouts._contentHeader') --}}
                <!-- /# row -->
                <section id="main-content">

                    @include('admin.layouts._messages')
                    @include('admin.layouts._validation_messages')
                    @yield('contentBody')


                   @include('admin.layouts._footer')

                </section>
            </div>
        </div>
    </div>

    <!-- jquery vendor -->
    @include('admin.layouts._scripts')
    @include('admin.layouts._toastr_alert')
    @yield('scripts')
    <script>
        $(document).ready(function() {
            $('#table').DataTable({
                "pageLength": 10,
                "targets": 'no-sort',
                "bSort": false,
                "order": [],
                "language": {
                    "lengthMenu": "",
                },
                searchHighlight: true,
                dom: '<"top"lfB>rt<"bottom"ip><"clear">',
                buttons: [
                    'excel'
                ]
            });
        });
    </script>
</body>

</html>
