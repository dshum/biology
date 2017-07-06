<?php namespace Moonlight\Controllers;

use Log;
use Validator;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Utils\UserJwtCodec;

class UserController extends Controller {

    /**
     * Delete user.
     *
     * @return Response
     */
    public function delete(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
		$user = User::find($id);
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif ( ! $user) {
            $scope['error'] = 'Пользователь не найден.';
        } elseif ($user->id == $loggedUser->id) {
            $scope['error'] = 'Нельзя удалить самого себя.';
        } elseif ($user->isSuperUser()) {
            $scope['error'] = 'Нельзя удалить суперпользователя.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }
        
        $groups = $user->getGroups();

        foreach ($groups as $group) {
            $user->removeGroup($group);
        }
        
        $user->delete();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_DROP_USER_ID,
			'ID '.$user->id.' ('.$user->login.')'
		);
        
        $scope['deleted'] = $user->id;
        
        return response()->json($scope);
    }

    /**
     * Add user.
     *
     * @return Response
     */
    public function add(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }
        
        $validator = Validator::make($request->all(), [
            'login' => 'required|max:25',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email',
            'groups' => 'array',
        ], [
            'login.required' => 'Введите логин.',
            'login.max' => 'Слишком длинный логин.',
            'first_name.required' => 'Введите имя.',
            'first_name.max' => 'Слишком длинное имя.',
            'last_name.required' => 'Введите фамилию.',
            'last_name.max' => 'Слишком длинная фамилия.',
            'email.required' => 'Введите адрес электронной почты.',
            'email.email' => 'Некорректный адрес электронной почты.',
            'groups.array' => 'Некорректные группы.',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'login',
                'first_name',
                'last_name',
                'email',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][] = [
                        'name' => $field,
                        'message' => $messages->first($field)
                    ];
                }
            }
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }   
        
        $user = new User;

        $user->login = $request->input('login');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        
        /*
         * Set password
         */
        
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $password = substr(str_shuffle($chars), 0, 6);
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        
        $user->save();
        
        /*
         * Set groups
         */
        
        $groups = $request->input('groups');
        
        Log::info($groups);

        if ($groups) {
            foreach ($groups as $id) {
                $group = Group::find($id);
                
                if ($group) {
                    $user->addGroup($group);
                }
            }
        }
        
        UserAction::log(
			UserActionType::ACTION_TYPE_ADD_USER_ID,
			'ID '.$user->id.' ('.$user->login.')'
		);
        
        $scope['added'] = $user->id;
        
        return response()->json($scope);
    }

    /**
     * Save user.
     *
     * @return Response
     */
    public function save(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
		$user = User::find($id);
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif ( ! $user) {
            $scope['error'] = 'Пользователь не найден.';
        } elseif ($user->id == $loggedUser->id) {
            $scope['error'] = 'Нельзя редактировать самого себя.';
        } elseif ($user->isSuperUser()) {
            $scope['error'] = 'Нельзя редактировать суперпользователя.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }
        
        $validator = Validator::make($request->all(), [
            'login' => 'required|max:25',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email',
            'groups' => 'array',
        ], [
            'login.required' => 'Введите логин',
            'login.max' => 'Слишком длинный логин',
            'first_name.required' => 'Введите имя',
            'first_name.max' => 'Слишком длинное имя',
            'last_name.required' => 'Введите фамилию',
            'last_name.max' => 'Слишком длинная фамилия',
            'email.required' => 'Введите адрес электронной почты',
            'email.email' => 'Некорректный адрес электронной почты',
            'groups.array' => 'Некорректные группы.',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'login',
                'first_name',
                'last_name',
                'email',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][] = [
                        'name' => $field,
                        'message' => $messages->first($field)
                    ];
                }
            }
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }        

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        
        /*
         * Set groups
         */
        
        $groups = $request->input('groups');
        
        $userGroups = $user->getGroups();
        
        foreach ($userGroups as $group) {
			if (
                ! $groups 
                || ! in_array($group->id, $groups)
            ) {
				$user->removeGroup($group);
			}
		}

        if ($groups) {
            foreach ($groups as $id) {
                $group = Group::find($id);
                
                if ($group) {
                    $user->addGroup($group);
                }
            }
        }
        
        $user->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_SAVE_USER_ID,
			'ID '.$user->id.' ('.$user->login.')'
		);
        
        $scope['saved'] = $user->id;
        
        return response()->json($scope);
    }

	/**
     * Edit user.
     * 
     * @return Response
     */
    public function user(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['state'] = 'error_admin_access_denied';
            return response()->json($scope);
        }
        
        $user = User::find($id);
        
        if ( ! $user) {
            $scope['state'] = 'error_user_not_found';
            return response()->json($scope);
        }
        
        if (
            $user->id == $loggedUser->id
            || $user->isSuperUser()
        ) {
            $scope['state'] = 'error_user_access_denied';
            return response()->json($scope);
        }
        
        $groupList = Group::orderBy('name', 'asc')->get();
        
        $groups = [];
        
        foreach ($groupList as $group) {
            $groups[] = [
                'id' => $group->id,
                'name' => $group->name,
            ];
        }
            
        $user->groups = $user->getGroups();
        
        $userGroups = [];

        foreach ($user->groups as $group) {
            $userGroups[] = $group->id;
        }
        
        $scope['groups'] = $groups;
        
        $scope['user'] = [
            'id' => $user->id,
            'login' => $user->login,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'super' => $user->isSuperUser(),
            'groups' => $userGroups,
            'last_login' => $user->last_login ? $user->last_login->format('c') : null,
            'created_at' => $user->created_at->format('c'),
            'updated_at' => $user->updated_at->format('c'),
        ];
        
        return response()->json($scope);
    }
    
    /**
     * List of users.
     * 
     * @return Response
     */

	public function users()
	{
		$scope = array();

        $loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			response()->json($scope, 403);
		}
        
        $key = config('app.key');

		$userList = User::orderBy('login', 'asc')->get();

        $scope['key'] = $key;
        $scope['users'] = [];

		foreach ($userList as $user) {
            $groups = [];
            
            $user->groups = $user->getGroups();
            
            foreach ($user->groups as $group) {
                $groups[] = [
                    'id' => $group->id,
                    'name' => $group->name,
                ];
            }

            $scope['users'][] = [
                'id' => $user->id,
                'login' => $user->login,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'super' => $user->isSuperUser(),
                'groups' => $groups,
                'last_login' => $user->last_login ? $user->last_login->format('c') : null,
                'created_at' => $user->created_at->format('c'),
                'updated_at' => $user->updated_at->format('c'),
            ];
        }

		return response()->json($scope);
	}
    
    public function token()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

        $userCodec = app(UserJwtCodec::class);

		$scope['login'] = $loggedUser->login;
        $scope['token'] = $userCodec->encode($loggedUser);

		return response()->json($scope);
	}
}