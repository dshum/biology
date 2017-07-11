<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Test;
use App\UserTest;

class HomeController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request)
	{
		$scope = [];

		$user = Auth::user();

		$userTests = UserTest::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

		$tests = [];

		foreach ($userTests as $userTest) {
			$test = Test::where('id', $userTest->test_id)->first();
			$tests[] = $test;
		}

		$scope['userTests'] = $userTests;

		return view('home', $scope);
	}

} 