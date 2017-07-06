<?php

namespace Moonlight\Controllers;

use Log;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\Element;
use Moonlight\Properties\BaseProperty;
use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\DateProperty;
use Moonlight\Properties\DatetimeProperty;
use Carbon\Carbon;

class TrashController extends Controller
{   
    /**
     * Return the count of element list.
     *
     * @return Response
     */
    public function count($class)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $site = \App::make('site');
        
        $item = $site->getItemByName($class);
        
        if ( ! $item) {
            return response()->json(['count' => 0]);
        }        
        
        $propertyList = $item->getPropertyList();

		if ( ! $loggedUser->isSuperUser()) {
			$permissionDenied = true;
			$deniedElementList = [];
			$allowedElementList = [];

			$groupList = $loggedUser->getGroups();

			foreach ($groupList as $group) {
				$itemPermission = $group->getItemPermission($item->getNameId())
					? $group->getItemPermission($item->getNameId())->permission
					: $group->default_permission;

				if ($itemPermission != 'deny') {
					$permissionDenied = false;
					$deniedElementList = [];
				}

				$elementPermissionList = $group->elementPermissions;

				$elementPermissionMap = [];

				foreach ($elementPermissionList as $elementPermission) {
					$classId = $elementPermission->class_id;
					$permission = $elementPermission->permission;
                    
					$array = explode(Element::ID_SEPARATOR, $classId);
                    $id = array_pop($array);
                    $class = implode(Element::ID_SEPARATOR, $array);
					
                    if ($class == $item->getNameId()) {
						$elementPermissionMap[$id] = $permission;
					}
				}

				foreach ($elementPermissionMap as $id => $permission) {
					if ($permission == 'deny') {
						$deniedElementList[$id] = $id;
					} else {
						$allowedElementList[$id] = $id;
					}
				}
			}
		}

        $criteria = $item->getClass()->onlyTrashed();

		if ( ! $loggedUser->isSuperUser()) {
			if (
				$permissionDenied
				&& sizeof($allowedElementList)
			) {
				$criteria->whereIn('id', $allowedElementList);
			} elseif (
				! $permissionDenied
				&& sizeof($deniedElementList)
			) {
				$criteria->whereNotIn('id', $deniedElementList);
			} elseif ($permissionDenied) {
                return response()->json(['count' => 0]);
			}
		}

		$count = $criteria->count();
        
        $scope['count'] = $count;
            
        return response()->json($scope);
    }
    
    public function active(Request $request, $class, $name)
	{
		$scope = [];
        
        $loggedUser = LoggedUser::getUser();

        $site = \App::make('site');
        
        $item = $site->getItemByName($class);
        
        if ( ! $item) {
            $scope['message'] = 'Класс не найден.';
            return response()->json($scope);
        }
        
        $property = $item->getPropertyByName($name);
        
        if ( ! $property) {
            $scope['message'] = 'Свойство класса не найдено.';
            return response()->json($scope);
        }
        
        $active = $request->input('active');

        $activeProperties = $loggedUser->getParameter('activeTrashProperties') ?: [];
        
        if ( 
            ! $active 
            && isset($activeProperties[$item->getNameId()][$property->getName()])
        ) {
            unset($activeProperties[$item->getNameId()][$property->getName()]);
        } elseif ($active) {
            $activeProperties[$item->getNameId()][$property->getName()] = 1;
        }
        
        $loggedUser->setParameter('activeTrashProperties', $activeProperties);

		return response()->json($scope);
	}
    
    public function item(Request $request, $class)
	{
		$scope = [];
        
        $loggedUser = LoggedUser::getUser();

        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if ( ! $currentItem) {
            $scope['message'] = 'Класс не найден.';
            return response()->json($scope);
        }
        
        $activeProperties = $loggedUser->getParameter('activeTrashProperties') ?: [];
        
        $propertyList = $currentItem->getPropertyList();
        
        $properties = [];
        
        foreach ($propertyList as $propertyName => $property) {
			if ($property->getHidden()) continue;
            
            $searchView = $property->setRequest($request)->getSearchView();
            
            if ( ! $searchView) continue;
            
            $isActive = isset($activeProperties[$currentItem->getNameId()][$property->getName()])
                ? 1 : 0;
            
            $properties[] = [
                'name' => $property->getName(),
                'title' => $property->getTitle(),
                'className' => $property->getClassName(),
                'view' => $searchView,
                'active' => $isActive,
            ];
		}
        
		$scope['item'] = [
            'id' => $currentItem->getNameId(),
            'name' => $currentItem->getTitle(),
        ];
        
        $scope['properties'] = $properties;

		return response()->json($scope);
	}
    
    public function first()
	{
		$scope = [];

        $site = \App::make('site');
        
		$itemList = $site->getItemList();
        
        $first = sizeof($itemList) ? array_shift($itemList) : null;
        
		$scope['item'] = $first ? [
            'id' => $first->getNameId(),
            'name' => $first->getTitle(),
        ] : null;

		return response()->json($scope);
	}
    
    public function items()
	{
		$scope = [];

        $site = \App::make('site');
        
		$itemList = $site->getItemList();
        
        $items = [];
        
        foreach ($itemList as $item) {
            $items[] = [
                'id' => $item->getNameId(),
                'name' => $item->getTitle(),
                'count' => 0,
            ];
        }
        
		$scope['items'] = $items;

		return response()->json($scope);
	}
}