<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>後臺管理</title>
    <link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet" />
    <script src="{{asset('assets/js/pace.min.js')}}"></script>
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">

    <link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}" />
</head>

<body class="bg-login">

    {{-- <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
    {{-- <style>
    * {
        margin: 0;
        padding: 0;
    }

    .filter-on {
        filter: blur(25px);
    }

    img {
        width: 100px;
    }

    .d-flex-on {
        display: block !important;
    }

    .container-fluid {
        background-image: url("https://uhdwallpapers.org/uploads/converted/20/01/14/the-mandalorian-5k-1920x1080_477555-mm-90.jpg");
        width: 100%;
        height: 100%;
        background-position: center center;
        background-attachment: scroll;
    }

    .form-wrapper {
        display: none;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.7);
        position: absolute;
        top: 30%;
        left: 38%;
        transform: translate-Y(-50%, -50%);
        width: 350px;
    }

    form {
        position: absolute;
        top: 20%;
        left: 20%;
        transform: translateY(-50%, -50%);
        text-align: center;

    }

    label {
        color: #fff;
    }

    input {
        width: 180px;
        height: 30px;
    }

</style> --}}
    {{-- <div class="container-fluid" id="container">
    <div class="info d-flex-on" style="display:none; position: fixed;top:50%;left:50%;transform:translate(-50%,-50%)">
        <h1 style='text-align center;text-align:center'>歡迎使用</h1>
        <h3 style='text-align center;text-align:center'>大立生鮮後台系統</h3>

    </div>
</div> --}}
    <div class="wrapper">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
            <div class="container-fluid">
                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                    <div class="col mx-auto">
                        <!-- <div class="mb-4 text-center">
                            <img src="https://cloud.gini-edu.com/image/logo.png" width="180" alt="" />
                        </div> -->
                        <div class="card">
                            <div class="card-body">
                                <div class="border p-4 rounded">
                                    <div class="text-center">
                                        <h3 class="fw-bold">後台登入</h3>
                                    </div>
                                  
                                    <div class="form-body">
                                        <form class="row g-3" action="{{route('adminAuth')}}" method="post">
                                            @csrf
                                            <div class="col-12">
                                                <!-- <label for="inputEmailAddress" class="form-label">IP</label> -->
                                                <input type="text" class="form-control" name='ip' id="ip" placeholder="IP" hidden>
                                            </div>
                                            <div class="col-12">
                                                <label for="inputEmailAddress" class="form-label">使用者帳號</label>
                                                <input type="text" class="form-control" name='phone'  id="inputEmailAddress" placeholder="輸入使用者帳號" autocomplete="off">
                                            </div>
                                            <div class="col-12">
                                                <label for="inputChoosePassword" class="form-label">使用者密碼</label>
                                                <div class="input-group" id="show_hide_password">
                                                    <input type="password" class="form-control border-end-0"
                                                        id="inputChoosePassword"  name='password'
                                                        placeholder="輸入使用者密碼"> <a href="javascript:;"
                                                        class="input-group-text bg-transparent"><i
                                                            class='bx bx-hide'></i></a>
                                                </div>
                                            </div>
                                            {{-- <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="flexSwitchCheckChecked" checked>
                                                    <label class="form-check-label"
                                                        for="flexSwitchCheckChecked">Remember
                                                        Me</label>
                                                </div>
                                            </div> --}}
                                            {{-- <div class="col-md-6 text-end"> <a
                                                    href="authentication-forgot-password.html">Forgot Password ?</a>
                                            </div> --}}
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary"><i
                                                            class="bx bxs-lock-open"></i>登入</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>
    </div>

    
    <script src="{{asset('assets/js/jquery.min.js')}}"></script>
    <script>
        $(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
    </script>
    <script>
        $(document).ready(function() {
        // $("#container").click(function() {
        //     $("#container").toggleClass('filter-on');
        //     $("#form").toggleClass('d-flex-on');
        //     $(".info").toggleClass('d-flex-on');
        // });
        //  $("body").click(function() {
        //   $("#container").removeClass('filter-on');
        //   $("#form").removeClass('d-flex-on');
        // });
        // setTimeout(function(){
        //     $("#container").toggleClass('filter-on');
        //     $("#form").toggleClass('d-flex-on');
        //     $(".info").toggleClass('d-flex-on');
        // },3000)

    });
    </script>
</body>

</html>