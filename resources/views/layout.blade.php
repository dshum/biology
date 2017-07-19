<!DOCTYPE html>
<html>
<head>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>@yield('title')</title>
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	<script>
		$(function() {
			
		});
	</script>
</head>
<body>
	<div class="container">
		<nav>
			<logo><a href="{{ route('welcome') }}"><i class="fa fa-graduation-cap"></i>Тесты ЕГЭ по биологии</a></logo>
			<user>
				@if (Auth::check())
				<a href="{{ route('home') }}">{{ Auth::user()->email }}</a>
				@else
				<a href="{{ route('login') }}">Войти</a> &nbsp; | &nbsp; <a href="{{ route('register') }}">Зарегистрироваться</a>
				@endif
			</user>
		</nav>
		<main>
			@yield('content')
		</main>
		<footer>
			@if (Auth::check())
			<div class="right"><a href="{{ route('logout') }}"><i class="fa fa-sign-out"></i>Выход</a></div>
			<div class="right"><a href="{{ route('profile') }}"><i class="fa fa-user"></i>Профиль</a></div>
			@endif
			<div>Тесты ЕГЭ по биологии, {{ date('Y') }}</div>
		</footer>
	</div>
</body>
</html>