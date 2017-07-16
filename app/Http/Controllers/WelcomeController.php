<?php 

namespace App\Http\Controllers;

use App\Subject;
use App\Topic;
use App\Subtopic;
use App\Test;

class WelcomeController extends Controller {

	public function __construct()
	{
		// $this->middleware('guest');
	}

	public function index()
	{
		$scope = [];

		$subjects = \Cache::tags('Subject')->remember('subjects', 60, function() {
			return Subject::where('hidden', false)->orderBy('order')->get();
		});

		$topics = \Cache::tags('Topic')->remember('topics', 60, function() {
			return Topic::where('hidden', false)->orderBy('order')->get();
		});

		$subtopics = \Cache::tags('Subtopic')->remember('subtopics', 60, function() {
			return Subtopic::where('hidden', false)->orderBy('order')->get();
		});

		$tests = \Cache::tags('Test')->remember('tests', 60, function() {
			return Test::orderBy('order')->get();
		});

		$scope['subjects'] = $subjects;
		$scope['topics'] = $topics;
		$scope['subtopics'] = $subtopics;
		$scope['tests'] = $tests;

		return view('welcome', $scope);
	}

} 