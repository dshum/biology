<?php namespace Moonlight\Controllers;

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Utils\UserJwtCodec;
use Carbon\Carbon;

//use App\Mail\OrderShipped;

class LoginController extends Controller {
    
    public function restore(Request $request)
    {
        $scope = [];
        
        $login = $request->input('login');
        
        if ( ! $login) {
			$scope['error'] = 'Введите логин.';
			return response()->json($scope);
		}
        
        $user = User::where('login', $login)->first();

		if ( ! $user) {
			$scope['error'] = 'Пользователь не найден.';
			return response()->json($scope);
		}
        
        Mail::to($request->user())->send(new RestorePassword($order));
        
        $scope['restored'] = $login;
        
        return response()->json($scope);
    }

	public function login(Request $request)
	{
		$scope = [];

		$login = $request->input('login');
		$password = $request->input('password');

		if ( ! $login) {
			$scope['error'] = 'Введите логин.';
			return response()->json($scope);
		}

		if ( ! $password) {
			$scope['error'] = 'Введите пароль.';
			return response()->json($scope);
		}

		$user = User::where('login', $login)->first();

		if ( ! $user) {
			$scope['error'] = 'Неправильный логин или пароль.';
			return response()->json($scope);
		}

		if ( ! password_verify($password, $user->password)) {
			$scope['error'] = 'Неправильный логин или пароль.';
			return response()->json($scope);
		}

		if ($user->banned) {
			$scope['error'] = 'Пользователь заблокирован.';
			return response()->json($scope);
		}
        
        $user->last_login = Carbon::now();
        $user->save();

		LoggedUser::setUser($user);

		UserAction::log(
			UserActionType::ACTION_TYPE_LOGIN_ID,
			$user->login
		);

		$userCodec = app(UserJwtCodec::class);

		$scope['token'] = $userCodec->encode($user);
        
        return response()->json($scope);
	}

}