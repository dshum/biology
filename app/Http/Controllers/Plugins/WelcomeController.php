<?php 

namespace App\Http\Controllers\Plugins;

use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\User;
use App\Topic;
use App\Test;
use App\Question;
use App\Answer;

class WelcomeController extends Controller {

	public function index()
	{
		$scope = [];

        $lastUsers = \Cache::tags('User')->remember('lastUsers', 1440, function() {
            return User::orderBy('created_at', 'desc')->limit(5)->get();
        });

        $userCount = \Cache::tags('User')->remember('userCount', 1440, function() {
            return User::count();
        });

        $testCount = \Cache::tags('Test')->remember('testCount', 1440, function() {
            return Test::count();
        });

        $questionCount = \Cache::tags('Question')->remember('questionCount', 1440, function() {
            return Question::count();
        });

        $scope['lastUsers'] = [];

        foreach ($lastUsers as $lastUser) {
            $scope['lastUsers'][] = [
                'id' => $lastUser->id,
                'classId' => $lastUser->getClassId(),
                'email' => $lastUser->email,
            ];
        }

        $scope['userCount'] = $userCount;
        $scope['testCount'] = $testCount;
        $scope['questionCount'] = $questionCount;

        \Log::info(\DB::getQueryLog());

		return response()->json($scope);
	}

} 