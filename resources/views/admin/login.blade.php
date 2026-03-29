<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Login Page</title>

    <!-- ================= Favicon ================== -->
    <!-- Standard -->
    <link rel="shortcut icon" href="http://placehold.it/64.png/000/fff">
    <!-- Retina iPad Touch Icon-->
    <link rel="apple-touch-icon" sizes="144x144" href="http://placehold.it/144.png/000/fff">
    <!-- Retina iPhone Touch Icon-->
    <link rel="apple-touch-icon" sizes="114x114" href="http://placehold.it/114.png/000/fff">
    <!-- Standard iPad Touch Icon-->
    <link rel="apple-touch-icon" sizes="72x72" href="http://placehold.it/72.png/000/fff">
    <!-- Standard iPhone Touch Icon-->
    <link rel="apple-touch-icon" sizes="57x57" href="http://placehold.it/57.png/000/fff">

    <!-- Styles -->
    <link href={{ asset("assets/css/lib/font-awesome.min.css") }} rel="stylesheet">
    <link href={{ asset("assets/css/lib/themify-icons.css") }} rel="stylesheet">
    <link href={{ asset("assets/css/lib/bootstrap.min.css") }} rel="stylesheet">
    <link href={{ asset("assets/css/lib/helper.css") }} rel="stylesheet">
    <link href={{ asset("assets/css/style.css") }} rel="stylesheet">
</head>

<body class="bg-primary">

    <div class="unix-login">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="login-content">


                        <div class="login-form">
                            <div class="login-logo">
                                <img src={{ asset("assets/logo/logo.jpeg") }} alt="Logo">
                            </div>
                            @include('admin.layouts._validation_messages')
                            @include('admin.layouts._messages')
                            <h4>Admin Login</h4>
                            <form action="{{ route('admin.login') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label>User ID</label>
                                    <input id="userId" type="text" class="form-control" placeholder="User ID" name="userId">
                                    <span class="text-danger font-weight-bold" id="msg"></span>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input id="password" type="password" class="form-control" placeholder="Password" name="password">
                                </div>
                                <button id="logInButton" type="submit" class="btn btn-primary btn-flat m-b-30 m-t-30">Log In</button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script>
    $(document).on('click', '#userId', function () {
        $('#msg').text('');
        $('#logInButton').unbind(event.preventDefault());

    });
    $(document).on('click', '#password', function () {
        $('#msg').text('');
        $('#logInButton').unbind(event.preventDefault());

    });
    $(document).on('click', '#logInButton', function () {
        var userId = $('#userId').val();
        var password = $('#password').val();
        if(userId != password){
            $('#msg').text('User ID and Password Invalid !!');
            event.preventDefault();

        }
    });
</script>

</html>
