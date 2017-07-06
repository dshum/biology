<?php

namespace Moonlight\Controllers;

use Log;
use Validator;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Moonlight\Utils\ImageUtils;

class ProfileController extends Controller
{
    const PHOTO_RESIZE_WIDTH = 100;
    const PHOTO_RESIZE_HEIGHT = 100;
    
    /**
     * Save profile of the logged user.
     *
     * @return Response
     */
    public function savePassword(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
		$validator = Validator::make($request->all(), [
            'password_old' => 'required',
            'password' => 'required|min:6|max:25|confirmed',
        ], [
            'password_old.required' => 'Введите текущий пароль',
            'password.required' => 'Введите новый пароль',
            'password.min' => 'Минимальная длина пароля 6 символов',
            'password.max' => 'Максимальная длина пароля 25 символов',
            'password.confirmed' => 'Введенные пароли должны совпадать',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'password_old',
                'password',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][] = [
                        'name' => $field,
                        'message' => $messages->first($field)
                    ];
                }
            }
        }
        
        $password_old = $request->input('password_old');
        $password = $request->input('password');
        
        if (
            $password_old
            && ! password_verify($password_old, $loggedUser->password)) {
            $scope['errors'][] = [
                'name' => 'password_old',
                'message' => 'Неправильный текущий пароль'
            ];
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }
        
        if ($password) {
            $loggedUser->password = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $loggedUser->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_SAVE_PROFILE_ID,
			'ID '.$loggedUser->id.' ('.$loggedUser->login.')'
		);
        
        $scope['saved'] = $loggedUser->id;
        
        return response()->json($scope);
    }
    
    /**
     * Save profile of the logged user.
     *
     * @return Response
     */
    public function save(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
		$validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email',
            'file' => 'dimensions:min_width=100,min_height=100|max:1024|mimes:jpeg,pjpeg,png,gif',
        ], [
            'first_name.required' => 'Введите имя',
            'first_name.max' => 'Слишком длинное имя',
            'last_name.required' => 'Введите фамилию',
            'last_name.max' => 'Слишком длинная фамилия',
            'email.required' => 'Введите адрес электронной почты',
            'email.email' => 'Некорректный адрес электронной почты',
            'file.dimensions' => 'Минимальный размер изображения: 100x100 пикселей',
            'file.max' => 'Максимальный размер файла: 1024 Кб',
            'file.mimes' => 'Допустимый формат файла: jpg, png, gif',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'first_name',
                'last_name',
                'email',
                'file',
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
        
        $loggedUser->first_name = $request->input('first_name');
        $loggedUser->last_name = $request->input('last_name');
        $loggedUser->email = $request->input('email');

        /*
         * Upload photo
         */
        
        $assetsPath = $loggedUser->getAssetsPath();
        $folderPath = $loggedUser->getFolderPath();
        
        if ($request->hasFile('file')) {
            if ($loggedUser->photoExists()) {
                try {
                    unlink($loggedUser->getPhotoAbsPath());
                    
                    $loggedUser->photo = null;
                } catch (\Exception $e) {}
            }
        
			$file = $request->file('file');

			if ($file->isValid() && $file->getMimeType()) {
				$path = $file->getRealPath();
                $extension = $file->getClientOriginalExtension();
                $hash = substr(md5(rand()), 0, 8);
                $filename = sprintf('photo_%s.%s',
					$hash,
					$extension
				);

                if ( ! file_exists($assetsPath)) {
					mkdir($assetsPath, 0755);
				}     

				if ( ! file_exists($folderPath)) {
					mkdir($folderPath, 0755);
				}
                
                ImageUtils::resizeAndCopy(
                    $path,
                    $folderPath.$filename,
                    self::PHOTO_RESIZE_WIDTH,
                    self::PHOTO_RESIZE_HEIGHT,
                    100
                );
                
                $loggedUser->photo = $filename;
            }
        } elseif ($request->input('drop')) {
            if ($loggedUser->photoExists()) {
                try {
                    unlink($loggedUser->getPhotoAbsPath());
                    
                    $loggedUser->photo = null;
                } catch (\Exception $e) {}
            }
        }
        
        $loggedUser->save();
        
        UserAction::log(
			UserActionType::ACTION_TYPE_SAVE_PROFILE_ID,
			'ID '.$loggedUser->id.' ('.$loggedUser->login.')'
		);
        
        $groups = [];
            
        $userGroups = $loggedUser->getGroups();

        foreach ($userGroups as $group) {
            $groups[] = [
                'id' => $group->id,
                'name' => $group->name,
            ];
        }
        
        $scope['user'] = [
            'id' => $loggedUser->id,
            'login' => $loggedUser->login,
            'first_name' => $loggedUser->first_name,
            'last_name' => $loggedUser->last_name,
            'email' => $loggedUser->email,
            'photo' => $loggedUser->getPhotoSrc(),
            'super' => $loggedUser->isSuperUser(),
            'groups' => $groups,
            'last_login' => $loggedUser->last_login ? $loggedUser->last_login->format('c') : null,
            'created_at' => $loggedUser->created_at->format('c'),
            'updated_at' => $loggedUser->updated_at->format('c'),
        ];
        
        return response()->json($scope);
    }
    
    /**
     * Edit profile of the logged user.
     * 
     * @return View
     */
    public function parameters(Request $request)
    {
        $scope = [];

		$loggedUser = LoggedUser::getUser();
        
        $scope['user'] = [
            'id' => $loggedUser->id,
            'login' => $loggedUser->login,
            'first_name' => $loggedUser->first_name,
            'last_name' => $loggedUser->last_name,
            'email' => $loggedUser->email,
            'parameters' => $loggedUser->getUnserializedParameters(),
        ];

		return response()->json($scope);
    }
    
    /**
     * Edit profile of the logged user.
     * 
     * @return View
     */
    public function edit(Request $request)
    {
        $scope = [];

		$loggedUser = LoggedUser::getUser();
        
        $groups = [];
            
        $userGroups = $loggedUser->getGroups();

        foreach ($userGroups as $group) {
            $groups[] = [
                'id' => $group->id,
                'name' => $group->name,
            ];
        }

        $scope['user'] = [
            'id' => $loggedUser->id,
            'login' => $loggedUser->login,
            'first_name' => $loggedUser->first_name,
            'last_name' => $loggedUser->last_name,
            'email' => $loggedUser->email,
            'photo' => $loggedUser->getPhotoSrc(),
            'super' => $loggedUser->isSuperUser(),
            'groups' => $groups,
            'last_login' => $loggedUser->last_login ? $loggedUser->last_login->format('c') : null,
            'created_at' => $loggedUser->created_at->format('c'),
            'updated_at' => $loggedUser->updated_at->format('c'),
        ];

		return response()->json($scope);
    }
}