<?php 

namespace App\Http\Controllers\Plugins;

use Illuminate\Support\Facades\Log;
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

        $lastUsers = User::orderBy('created_at', 'desc')->limit(5)->get();

        $userCount = User::count();
        $testCount = Test::count();
        $questionCount = Question::count();

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

		return response()->json($scope);
	}

} 