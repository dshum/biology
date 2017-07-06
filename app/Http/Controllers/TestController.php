<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Test;

class TestController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request, $id)
	{
		$scope = [];

        $test = Test::where('id', $id)->first();

        if (!$test) {
            return redirect()->route('welcome');
        }

        $scope['test'] = $test;

		return view('test', $scope);
	}

} 