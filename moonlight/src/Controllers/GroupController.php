<?php namespace Moonlight\Controllers;

use Validator;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Models\GroupItemPermission;
use Moonlight\Models\GroupelementPermission;

class GroupController extends Controller {

    public function saveElementPermissions($id)
    {
        $scope = array();

        $group = \Cache::rememberForever(
            "getGroupById($id)",
            function() use ($id) {
                return Group::find($id);
            }
        );

        if ( ! $group) {
            $scope['state'] = 'error_group_not_found';
            return \Response::json($scope, 404);
        }

        $loggedUser = LoggedUser::getUser();

        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['state'] = 'error_admin_access_denied';
            return \Response::json($scope, 403);
        }

        if ($loggedUser->inGroup($group)) {
            $scope['state'] = 'error_group_access_denied';
            return \Response::json($scope, 403);
        }

        $elementPermissions = \Input::all();

        if ( ! is_array($elementPermissions)) {
            return \Response::json($scope);
        }

        $input = [];

        foreach ($elementPermissions as $elementPermission) {
            $classId = $elementPermission['classId'];
            $permission = $elementPermission['permission'];
            $input[$classId] = $permission;
        }

        $site = \App::make('site');

        $itemList = $site->getItemList();

        $itemElementList = [];

        foreach ($itemList as $item) {
            if ( ! $item->getElementPermissions()) continue;

            $elementList =
                $item->getClass()->
                orderBy($item->getMainProperty())->
                get();

            if ( ! sizeof ($elementList)) continue;

            foreach ($elementList as $element) {
                $itemElementList[$item->getName()][Element::getClassId($element)] = Element::getClassId($element);
            }
        }

        foreach ($itemElementList as $elementList) {
            foreach ($elementList as $classId) {
                $rules[$classId] = 'required|in:deny,view,update,delete';
                $messages[$classId.'.required'] = 'Поле обязательно к заполнению';
                $messages[$classId.'.in'] = 'Некорректное право доступа';
            }
        }

        $validator = \Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            $messages = $validator->messages()->getMessages();
            $errors = array();

            foreach ($messages as $field => $messageList) {
                foreach ($messageList as $message) {
                    $errors[$field][] = $message;
                }
            }

            $scope['error'] = $errors;

            return \Response::json($scope, 422);
        }

        $permissionList = [];

        $defaultPermission = $group->default_permission
            ? $group->default_permission
            : 'deny';

        foreach ($itemList as $item) {
            $permissionList[$item->getName()] = $defaultPermission;
        }

        $itemPermissions = $group->itemPermissions;

        foreach ($itemPermissions as $itemPermission) {
            $class = $itemPermission->class;
            $permission = $itemPermission->permission;
            $permissionList[$class] = $permission;
        }

        $elementPermissions = $group->elementPermissions;

        foreach ($elementPermissions as $elementPermission) {
            $classId = $elementPermission->class_id;
            $permissionList[$classId] = $elementPermission;
        }

        foreach ($itemElementList as $itemName => $elementList) {
            $itemPermission = $permissionList[$itemName];

            foreach ($elementList as $classId) {
                $permission = $input[$classId];

                if (
                    $itemPermission !== $permission
                    && ! isset($permissionList[$classId])
                ) {
                    $elementPermission = new GroupElementPermission;

                    $elementPermission->group_id = $group->id;
                    $elementPermission->class_id = $classId;
                    $elementPermission->permission = $permission;

                    $elementPermission->save();
                } elseif (
                    $itemPermission !== $permission
                    && isset($permissionList[$classId])
                    && $permissionList[$classId]->permission !== $permission
                ) {
                    $elementPermission = $permissionList[$classId];

                    $elementPermission->permission = $permission;

                    $elementPermission->save();
                } elseif (
                    $itemPermission === $permission
                    && isset($permissionList[$classId])
                ) {
                    $elementPermission = $permissionList[$classId];

                    $elementPermission->delete();
                }
            }
        }

        UserAction::log(
            UserActionType::ACTION_TYPE_SAVE_ELEMENT_PERMISSIONS_ID,
            'ID '.$group->id.' ('.$group->name.')'
        );

        $scope['group'] = [
            'id' => $group->id,
            'name' => $group->name,
            'admin' => $group->hasAccess('admin'),
            'defaultPermission' => $group->default_permission,
            'createdAt' => $group->created_at->format('c'),
            'updatedAt' => $group->updated_at->format('c'),
        ];

        return json_encode($scope);
    }
    
    /**
     * Delete group.
     *
     * @return Response
     */
    public function delete(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
		$group = Group::find($id);
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif ( ! $group) {
            $scope['error'] = 'Группа не найдена.';
        } elseif ($loggedUser->inGroup($group)) {
            $scope['error'] = 'Нельзя удалить группу, в которой вы состоите.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }
        
        $group->delete();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_DROP_GROUP_ID,
			'ID '.$group->id.' ('.$group->name.')'
		);
        
        $scope['deleted'] = $group->id;
        
        return response()->json($scope);
    }
    
    /**
     * Add group.
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
            'name' => 'required|max:255',
            'default_permission' => 'required|in:deny,view,update,delete',
        ], [
            'name.required' => 'Введите название.',
			'default_permission.required' => 'Укажите доступ к элементам',
			'default_permission.in' => 'Некорректный доступ',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'name',
                'default_permission',
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
        
        $group = new Group;
        
        $group->name = $request->input('name');
		$group->default_permission = $request->input('default_permission');
        
        $admin = $request->has('admin') && $request->input('admin') ? true : false;
        
        $group->setPermission('admin', $admin);
        
        $group->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_ADD_GROUP_ID,
			'ID '.$group->id.' ('.$group->name.')'
		);
        
        $scope['added'] = $group->id;
        
        return response()->json($scope);
    }

    /**
     * Save group.
     *
     * @return Response
     */
    public function save(Request $request, $id)
    {
        $scope = array();

        $loggedUser = LoggedUser::getUser();

        $group = Group::find($id);
        
        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['error'] = 'У вас нет прав на управление пользователями.';
        } elseif ( ! $group) {
            $scope['error'] = 'Группа не найдена.';
        } elseif ($loggedUser->inGroup($group)) {
            $scope['error'] = 'Нельзя редактировать группу, в которой вы состоите.';
        } else {
            $scope['error'] = null;
        }
        
        if ($scope['error']) {
            return response()->json($scope);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'default_permission' => 'required|in:deny,view,update,delete',
        ], [
            'name.required' => 'Введите название.',
			'default_permission.required' => 'Укажите доступ к элементам.',
			'default_permission.in' => 'Некорректный доступ.',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'name',
                'default_permission',
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
        
        $group->name = $request->input('name');
		$group->default_permission = $request->input('default_permission');
        
        $admin = $request->has('admin') && $request->input('admin') ? true : false;
        
        $group->setPermission('admin', $admin);
        
        $group->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_SAVE_GROUP_ID,
			'ID '.$group->id.' ('.$group->name.')'
		);
        
        $scope['saved'] = $group->id;
        
        return response()->json($scope);
    }
    
    /**
     * Edit group.
     * 
     * @return Response
     */

    public function group(Request $request, $id)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();

        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['state'] = 'error_admin_access_denied';
            return response()->json($scope);
        }

        $group = Group::find($id);

        if ( ! $group) {
            $scope['state'] = 'error_group_not_found';
            return response()->json($scope);
        }
        
        if ($loggedUser->inGroup($group)) {
			$scope['state'] = 'error_group_access_denied';
			return response()->json($scope);
		}

        $scope['group'] = [
            'id' => $group->id,
            'name' => $group->name,
            'admin' => $group->hasAccess('admin'),
            'default_permission' => $group->default_permission,
            'created_at' => $group->created_at->format('c'),
            'updated_at' => $group->updated_at->format('c'),
        ];

        return response()->json($scope);
    }
    
    /**
     * List of groups.
     * 
     * @return Response
     */

    public function groups()
    {
        $scope = [];

        $loggedUser = LoggedUser::getUser();

        if ( ! $loggedUser->hasAccess('admin')) {
            $scope['state'] = 'error_admin_access_denied';
            return response()->json($scope);
        }

        $groupList = Group::orderBy('name', 'asc')->get();

        $scope['groups'] = [];

        foreach ($groupList as $group) {
            $scope['groups'][] = [
                'id' => $group->id,
                'name' => $group->name,
                'admin' => $group->hasAccess('admin'),
                'default_permission' => $group->getPermissionTitle(),
                'created_at' => $group->created_at->format('c'),
                'updated_at' => $group->updated_at->format('c'),
            ];
        }

        return response()->json($scope);
    }
}