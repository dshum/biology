<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\User;
use App\Mail\Register;

class RegisterController extends Controller {

	protected $redirectTo = '/home';

	public function __construct()
	{
		$this->middleware('guest');
	}

	public function complete(Request $request)
    {
		$scope = [];

		return view('register.complete', $scope);
    }

    public function activate(Request $request)
    {
		$scope = [];

		$email = $request->input('email');
		$code = $request->input('code');

		$user = User::where('email', $email)->first();

		if (! $user) {
			return view('register.activate', $scope);
		}

		if ($code !== substr(md5($user->email), 8, 8)) {
			return view('register.activate', $scope);
		}

		$user->activated = true;

		$user->save();

		return redirect()->route('complete');
    }

	public function success(Request $request)
    {
		$scope = [];

		return view('register.success', $scope);
    }

	public function register(Request $request)
	{
		$email = $request->input('email');
		$password = $request->input('password');
		$first_name = $request->input('first_name');
		$last_name = $request->input('last_name');

		$validator = Validator::make($request->all(), [
			'email' => 'required|email|unique:users,email',
			'password' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
        ], [
			'email.required' => 'Введите e-mail',
            'email.email' => 'Некорректный e-mail',
			'email.unique' => 'Такой e-mail уже зарегистрирован',
			'password.required' => 'Придумайте пароль',
            'first_name.required' => 'Введите имя',
            'last_name.required' => 'Введите фамилию',
        ]);
        
        if ($validator->fails()) {
			$scope['errors'] = $validator->errors();
			$scope['email'] = $email;
			$scope['password'] = $password;
			$scope['first_name'] = $first_name;
			$scope['last_name'] = $last_name;
            
            return view('register.index', $scope);
        }

		$user = new User;

		$user->email = $email;
		$user->password = password_hash($password, PASSWORD_DEFAULT);
		$user->first_name = $first_name;
		$user->last_name = $last_name;
		$user->service_section_id = 1;
		$user->activated = false;

		$user->save();

		Mail::to($email)->send(new Register($user));

		return redirect()->route('success');
	}

	public function index(Request $request)
	{
		$scope = [];

		$scope['email'] = null;
		$scope['password'] = null;
		$scope['first_name'] = null;
        $scope['last_name'] = null;

		return view('register.index', $scope);
	}

} 