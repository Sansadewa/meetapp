<!DOCTYPE html>
<html lang="en">
<head>
	<title>MeetApp Kalsel</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="_token" content="{{ csrf_token() }}">
    {{-- <meta name="base_url" content="{{ config('app.url') }}"> --}}
    <meta name="base_url" content="{{ url('') }}">
    <link rel="shortcut icon" href="{{ url('public/image/meetappico.png') }}" type="image/png">
	<link rel="stylesheet" type="text/css" href="{{ url('public/login/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('public/login/font-awesome-4.7.0/css/font-awesome.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('public/login/iconic/css/material-design-iconic-font.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('public/login/animate.css') }}">
	<!-- <link rel="stylesheet" type="text/css" href="{{ url('public/logo/hamburgers.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('public/logo/animsition.min.css') }}"> -->
	<link rel="stylesheet" type="text/css" href="{{ url('public/login/util.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('public/login/main.css') }}">
</head>
<body>
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<form class="login100-form validate-form">
					<div style="text-align: center; display: none">
						<img src="{{ url('public/image/flame-151.png') }}" width="40" alt="homepage" class="light-logo" />
					</div>
					
					<span class="login100-form-title p-b-26">
						MeetApp Kalsel
					</span>

					@if(session('message_auth'))
					<div style="color: #7c2b2a;background-color: #fcdddc;border-color: #fbcfce;position: relative;padding: .75rem 1.25rem;margin-bottom: 1rem;border: 1px solid transparent;border-radius: 4px; font-size:small">
						<i class="zmdi zmdi-info-outline"></i> {{ session('message_auth') }}
					</div>
					@endif

					
					
					<div class="wrap-input100 validate-input" data-validate = "Enter username">
						<input class="input100" type="text" id="username" name="username">
						<span class="focus-input100" data-placeholder="Username"></span>
					</div>

					<div class="wrap-input100 validate-input" data-validate="Enter password">
						<span class="btn-show-pass">
							<i class="zmdi zmdi-eye"></i>
						</span>
						<input class="input100" type="password" id="password" name="pass">
						<span class="focus-input100" data-placeholder="Password"></span>
					</div>

					<div class="container-login100-form-btn">
						<div class="wrap-login100-form-btn">
							<div class="login100-form-bgbtn"></div>
							<button class="login100-form-btn">
								Login
							</button>
						</div>
					</div>

					<div class="text-center p-t-80">
						<span class="txt1">
							Don’t have an account?
						</span>

						<a class="txt2" href="javascript:void(0)">
							Sign Up
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	

	<div id="dropDownSelect1"></div>
	
	<script src="{{ url('public/login/jquery-3.2.1.min.js') }}"></script>
	<!-- <script src="{{ url('public/login/animsition.min.js') }}"></script> -->
	<script src="{{ url('public/login/popper.js') }}"></script>
	<script src="{{ url('public/login/bootstrap.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
	<script src="{{ url('public/login/main.js') }}"></script>

</body>
</html>