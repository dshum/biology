<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;

class LoginController extends Controller {

	protected $redirectTo = '/home';

	public function __construct()
	{
		$this->middleware('guest')->except('logout');
	}

	public function login(Request $request)
	{
		$email = $request->input('email');
		$password = $request->input('password');
		$remember = $request->input('remember');

		$scope['email'] = $email;
		$scope['password'] = $password;
		$scope['remember'] = $remember;

		if ( ! $email) {
			$scope['error'] = 'Введите e-mail.';
			return view('login', $scope);
		}

		if ( ! $password) {
			$scope['error'] = 'Введите пароль.';
			return view('login', $scope);
		}

		$user = User::where('email', $email)->first();

		if ( ! $user) {
			$scope['error'] = 'Неправильный e-mail или пароль.';
			return view('login', $scope);
		}

		if ( ! password_verify($password, $user->password)) {
			$scope['error'] = 'Неправильный логин или пароль.';
			return view('login', $scope);
		}

		if ($user->banned) {
			$scope['error'] = 'Пользователь заблокирован.';
			return view('login', $scope);
		}
        
        // $user->last_login = Carbon::now();
        // $user->save();

		Auth::login($user, $remember);

		return redirect()->route('home');
	}


	public function logout(Request $request)
	{
		$scope = [];

		Auth::logout();

		return redirect()->back();
	}


	public function index(Request $request)
	{
		$scope = [];

		$scope['email'] = null;
		$scope['password'] = null;
		$scope['remember'] = false;
		$scope['error'] = null;

		return view('login', $scope);
	}

} 