<?php namespace Moonlight\Controllers;

use Log;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Carbon\Carbon;

class LogController extends Controller {

	const DEFAULT_PER_PAGE = 10;

	public function form()
	{
		$scope = [];

		$loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			return response()->json($scope, 403);
		}

		$userList = User::orderBy('login')->get();

		$userActionTypeList = UserActionType::getActionTypeNameList();

		$actionTypeList = array();

		foreach ($userActionTypeList as $name => $title) {
			$actionTypeList[] = [
				'name' => $name,
				'title' => $title,
			];
		}

		$scope['users'] = $userList;
		$scope['types'] = $actionTypeList;

		return response()->json($scope);
	}

	public function log(Request $request)
	{
		$scope = [];

		$loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			return response()->json($scope, 403);
		}

        $comments = $request->input('comments');
		$userId = $request->input('user');
        $actionType = $request->input('type');
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

		if ($actionType && ! UserActionType::actionTypeExists($actionType)) {
			$actionType = null;
		}

		if ($dateFrom) {
			try {
				$dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom);
			} catch (\Exception $e) {
				$dateFrom = null;
			}
		}

		if ($dateTo) {
			try {
				$dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->modify('+1 day');
			} catch (\Exception $e) {
				$dateTo = null;
			}
		}

		$userActionListCriteria = UserAction::where(
			function($query) use (
				$userId, $actionType, $comments, $dateFrom, $dateTo
			) {
				if ($userId) {
					$query->where('user_id', $userId);
				}

				if ($actionType) {
					$query->where('action_type_id', $actionType);
				}

				if ($comments) {
					$query->where('comments', 'ilike', "%$comments%");
				}

				if ($dateFrom) {
					$query->where('created_at', '>=', $dateFrom->format('Y-m-d'));
				}

				if ($dateTo) {
					$query->where('created_at', '<', $dateTo->format('Y-m-d'));
				}
			}
		);

		$userActionListCriteria->
		orderBy('created_at', 'desc');

		$userActionList = $userActionListCriteria->paginate(self::DEFAULT_PER_PAGE);
		
		$userActions = array();

		foreach ($userActionList as $userAction) {
            $actionTypeName = UserActionType::getActionTypeName(
				$userAction->action_type_id
			);
            
            $user = $userAction->user ? [
                'login' => $userAction->user->login,
                'first_name' => $userAction->user->first_name,
                'last_name' => $userAction->user->last_name,
                'email' => $userAction->user->email,
                'avatar' => $userAction->user->getPhotoSrc(),
            ] : [
                'login' => 'undefined',
            ];
            
			$userActions[] = [
				'user' => $user,
				'action_type' => $userAction->action_type_id,
				'action_type_name' => $actionTypeName,
				'url' => $userAction->url,
				'comments' => $userAction->comments,
				'created_at' => $userAction->created_at->format('c'),
			];
		}

		$count = $userActionList->total();
		$currentPage = $userActionList->currentPage();
        $hasMorePages = $userActionList->hasMorePages();

		$scope['actions'] = $userActions;
		$scope['pager'] = [
            'total' => $count,
            'currentPage' => $currentPage,
            'hasMorePages' => $hasMorePages,
            'nextPage' => $currentPage + 1,
        ];

		return response()->json($scope);
	}

}
