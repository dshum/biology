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

class SearchController extends Controller
{
    /**
     * Sort items.
     *
     * @return Response
     */
    public function sort(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $sort = $request->input('sort');
        
        $search = $loggedUser->getParameter('search') ?: [];

        if (in_array($sort, ['rate', 'date', 'name', 'default'])) {
			$search['sort'] = $sort;
			$loggedUser->setParameter('search', $search);
		}
        
        $html = $this->itemListView();
        
        return response()->json(['html' => $html]);
    }
    
    public function item5(Request $request, $class)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if ( ! $currentItem) {
            return redirect()->route('search');
        }
        
        $search = $loggedUser->getParameter('search') ?: [];

        $search['sortDate'][$class] =
            Carbon::now()->toDateTimeString();

        if (isset($search['sortRate'][$class])) {
            $search['sortRate'][$class]++;
        } else {
            $search['sortRate'][$class] = 1;
        }
        
        $loggedUser->setParameter('search', $search);
        
        $mainPropertyName = $currentItem->getMainProperty();
        $mainProperty = $currentItem->getPropertyByName($mainPropertyName);
        
        $propertyList = $currentItem->getPropertyList();
        
        $sortProperty = isset($search['sort'])
            ? $search['sort'] : 'default';
        $map = [];
        
        if ($sortProperty == 'name') {
			foreach ($propertyList as $property) {
				$map[$property->getTitle()] = $property;
			}

			ksort($map);
		} elseif ($sortProperty == 'date') {
			$sortPropertyDate =
				isset($search['sortPropertyDate'][$class])
				? $search['sortPropertyDate'][$class]
				: [];

			arsort($sortPropertyDate);

			foreach ($sortPropertyDate as $propertyName => $date) {
				$map[$propertyName] = $currentItem->getPropertyByName($propertyName);
			}

			foreach ($propertyList as $property) {
				$map[$property->getName()] = $property;
			}
		} elseif ($sortProperty == 'rate') {
			$sortPropertyRate =
				isset($search['sortPropertyRate'][$class])
				? $search['sortPropertyRate'][$class]
				: [];

			arsort($sortPropertyRate);

			foreach ($sortPropertyRate as $propertyName => $rate) {
				$map[$propertyName] = $currentItem->getPropertyByName($propertyName);
			}

			foreach ($propertyList as $property) {
				$map[$property->getName()] = $property;
			}
		} else {
            foreach ($propertyList as $property) {
				$map[] = $property;
			}
		}
        
        $properties = [];
        $orderProperties = [];
        $ones = [];
        $hasOrderProperty = false;
        
        foreach ($propertyList as $property) {
            if ($property instanceof OrderProperty) {
                $orderProperties[] = $property;
                $hasOrderProperty = true;
            }
            
            if ($property->getHidden()) continue;
            if ($property->getName() == 'deleted_at') continue;
            
            $orderProperties[] = $property;
        }
        
        foreach ($map as $property) {
            if ($property->getHidden()) continue;
            if ($property->isMainProperty()) continue;
            if ($property->getName() == 'deleted_at') continue;
            
			$properties[] = $property->setRequest($request);
            
            if ($property->isOneToOne()) {
                $ones[] = $property;
            }
		}

		unset($map);
        
        $action = $request->input('action');
        
        if ($action == 'search') {
            $elements = $this->elementListView($request, $currentItem);
        } else {
            $elements = null;
        }
        
        $onesCopy = view('moonlight::onesCopy', ['ones' => $ones])->render();
        $onesMove = view('moonlight::onesMove', ['ones' => $ones])->render();
        
        $sort = $request->input('sort');
        
        $scope['currentItem'] = $currentItem;
        $scope['mainProperty'] = $mainProperty;
        $scope['properties'] = $properties;
        $scope['orderProperties'] = $orderProperties;
        $scope['elementsView'] = $elements;
        $scope['onesCopy'] = $onesCopy;
        $scope['onesMove'] = $onesMove;
        $scope['hasOrderProperty'] = $hasOrderProperty;
        $scope['sort'] = $sort;
            
        return view('moonlight::searchItem', $scope);
    }
    
    public function index(Request $request)
    {        
        $html = $this->itemListView();
    
        return view('moonlight::search', ['html' => $html]);
    }
    
    protected function itemListView() {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $site = \App::make('site');
        
        $itemList = $site->getItemList();
        
        $search = $loggedUser->getParameter('search') ?: [];
        
        $sort = isset($search['sort'])
			? $search['sort'] : 'default';
        
        $map = [];
        
        if ($sort == 'name') {
			foreach ($itemList as $item) {
				$map[$item->getTitle()] = $item;
			}

			ksort($map);
		} elseif ($sort == 'date') {
			$sortDate = isset($search['sortDate'])
				? $search['sortDate'] : [];

			arsort($sortDate);

			foreach ($sortDate as $class => $date) {
				$map[$class] = $site->getItemByName($class);
			}

			foreach ($itemList as $item) {
				$map[$item->getNameId()] = $item;
			}
		} elseif ($sort == 'rate') {
			$sortRate = isset($search['sortRate'])
				? $search['sortRate'] : array();

			arsort($sortRate);

			foreach ($sortRate as $class => $rate) {
				$map[$class] = $site->getItemByName($class);
			}

			foreach ($itemList as $item) {
				$map[$item->getNameId()] = $item;
			}
		} else {
			foreach ($itemList as $item) {
				$map[] = $item;
			}
		}

		$items = [];

		foreach ($map as $item) {
			$items[] = $item;
		}

		unset($map);
        
        $sorts = [
            'rate' => 'частоте',
            'date' => 'дате',
            'name' => 'названию',
            'default' => 'умолчанию',
        ];
        
        if ( ! isset($sorts[$sort])) {
            $sort = 'default';
        }

		$scope['items'] = $items;
        $scope['sorts'] = $sorts;
        $scope['sort'] = $sort;
        
        return view('moonlight::searchList', $scope)->render();
    }
    
    public function elements(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $class = $request->input('item');
        
        $site = \App::make('site');
        
        $item = $site->getItemByName($class);
        
        if ( ! $item) {
            return response()->json([]);
        }
        
        return $this->elementListView($item);
    }
    
    protected function elementListView($currentItem)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $propertyList = $currentItem->getPropertyList();

		if ( ! $loggedUser->isSuperUser()) {
			$permissionDenied = true;
			$deniedElementList = [];
			$allowedElementList = [];

			$groupList = $loggedUser->getGroups();

			foreach ($groupList as $group) {
				$itemPermission = $group->getItemPermission($currentItem->getNameId())
					? $group->getItemPermission($currentItem->getNameId())->permission
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
					
                    if ($class == $currentItem->getNameId()) {
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

        $criteria = $currentItem->getClass()->where(
            function($query) use ($propertyList) {
                
            }
        );

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
        
        $hasOrderProperty = false;
        
        $orderByList = $currentItem->getOrderByList();

		foreach ($orderByList as $field => $direction) {
            $criteria->orderBy($field, $direction);
            $property = $currentItem->getPropertyByName($field);
            if ($property instanceof OrderProperty) {
                $hasOrderProperty = true;
            }
        }
        
        $perpage = $currentItem->getPerPage();
            
        $elementList = $criteria->paginate($perpage);

        $total = $elementList->total();

        $pager = $total > $perpage ? [
            'currentPage' => $elementList->currentPage(),
            'hasMorePages' => $elementList->hasMorePages(),
            'nextPage' => $elementList->currentPage() + 1,
            'lastPage' => $elementList->lastPage(),
            
        ] : null;

        $elements = [];
        $properties = [];
        $views = [];
        
        foreach ($propertyList as $property) {
            if ( ! $property->getShow()) continue;

            $properties[] = [
                'name' => $property->getName(),
                'title' => $property->getTitle(),
                'className' => $property->getClassName(),
            ];
        }

        foreach ($elementList as $element) {
            foreach ($propertyList as $property) {
                if ( ! $property->getShow()) continue;
                
                $views[$property->getName()] = $property->
                    setElement($element)->
                    getBrowseView();
            }
            
            $elements[] = [
                'id' => $element->id,
                'classId' => $element->getClassId(),
                'name' => $element->{$currentItem->getMainProperty()},
                'views' => $views,
            ];
        }

        $scope['total'] = $total;
        $scope['pager'] = $pager;
        $scope['elements'] = $elements;
        $scope['properties'] = $properties;
        
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

        $activeProperties = $loggedUser->getParameter('activeSearchProperties') ?: [];
        
        if ( 
            ! $active 
            && isset($activeProperties[$item->getNameId()][$property->getName()])
        ) {
            unset($activeProperties[$item->getNameId()][$property->getName()]);
        } elseif ($active) {
            $activeProperties[$item->getNameId()][$property->getName()] = 1;
        }
        
        $loggedUser->setParameter('activeSearchProperties', $activeProperties);

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
        
        $activeProperties = $loggedUser->getParameter('activeSearchProperties') ?: [];
        
        $propertyList = $currentItem->getPropertyList();
        
        $properties = [];
        
        foreach ($propertyList as $propertyName => $property) {
			if ($property->getHidden()) continue;
            if ($propertyName == 'deleted_at') continue;
            
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

        $mainPropertyTitle = $currentItem->getMainPropertyTitle();
        
		$scope['item'] = [
            'id' => $currentItem->getNameId(),
            'name' => $currentItem->getTitle(),
            'mainProperty' => $mainPropertyTitle,
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

        if ($first) {
            $mainPropertyTitle = $first->getMainPropertyTitle();

            $scope['item'] = [
                'id' => $first->getNameId(),
                'name' => $first->getTitle(),
                'mainProperty' => $mainPropertyTitle,
            ];
        }

		return response()->json($scope);
	}
    
    public function items()
	{
		$scope = [];

        $site = \App::make('site');
        
		$itemList = $site->getItemList();
        
        $items = [];
        
        foreach ($itemList as $item) {
            $mainPropertyTitle = $item->getMainPropertyTitle();

            $items[] = [
                'id' => $item->getNameId(),
                'name' => $item->getTitle(),
                'mainProperty' => $mainPropertyTitle,
            ];
        }
        
		$scope['items'] = $items;

		return response()->json($scope);
	}
}