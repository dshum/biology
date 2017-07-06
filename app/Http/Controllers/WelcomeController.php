<?php 

namespace App\Http\Controllers;

use App\Subject;
use App\Topic;
use App\Subtopic;
use App\Test;

class WelcomeController extends Controller {

	public function __construct()
	{
//		$this->middleware('guest');
	}

	public function index()
	{
		$scope = [];

		$subjects = Subject::where('hidden', false)->orderBy('order')->get();
		$topics = Topic::where('hidden', false)->orderBy('order')->get();
		$subtopics = Subtopic::where('hidden', false)->orderBy('order')->get();
		$tests = Test::orderBy('order')->get();

		$scope['subjects'] = $subjects;
		$scope['topics'] = $topics;
		$scope['subtopics'] = $subtopics;
		$scope['tests'] = $tests;

		return view('welcome', $scope);
	}

} 