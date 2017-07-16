<?php

namespace Moonlight\Controllers;

use Log;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\UserActionType;
use Moonlight\Main\Element;
use Moonlight\Main\Site;
use Moonlight\Models\Group;
use Moonlight\Models\GroupItemPermission;
use Moonlight\Models\GroupelementPermission;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Carbon\Carbon;

class TreeController extends Controller
{
    public function open($classId)
    {
        $scope = [];

        $loggedUser = LoggedUser::getUser();

        $open = $loggedUser->getParameter('open') ?: [];

        if (! isset($open[$classId])) {
            $open[$classId] = true;

            $loggedUser->setParameter('open', $open);
        }

        $scope['opened'] = true;

        return response()->json($scope);
    }

    public function close($classId)
    {
        $scope = [];

        $loggedUser = LoggedUser::getUser();

        $open = $loggedUser->getParameter('open') ?: [];

        if (isset($open[$classId])) {
            unset($open[$classId]);

            $loggedUser->setParameter('open', $open);
        }

        $scope['closed'] = true;

        return response()->json($scope);
    }

    /**
     * Tree node content.
     * 
     * @return Response
     */
    public function node($classId = null)
    {
        $scope = [];

        $scope['items'] = $this->items($classId);

        return response()->json($scope);
    }

    protected function items($classId = null)
    {   
        $loggedUser = LoggedUser::getUser();

        $element = $classId ? Element::getByClassId($classId) : null;
        
        $site = \App::make('site');
        
        $items = $site->getItemList();
        
        $itemList = [];
        
        if ($element) {
            foreach ($items as $item) {
                if ( ! $item->getTree()) continue;
                
                $propertyList = $item->getPropertyList();

                foreach ($propertyList as $property) {
                    if (
                        $property->isOneToOne()
                        && $property->getRelatedClass() == Element::getClass($element)
                    ) {
                        $itemList[] = $item;
                        break;
                    }
                }
            }
        } else {
            foreach ($items as $item) {
                if ($item->getRoot()) {
                    $itemList[] = $item;
                }
            }
        }
        
        $items = [];
        
        foreach ($itemList as $item) {
            $elements = $this->elements($element, $item);
            
            if (empty($elements)) continue;
            
            $items[] = [
                'id' => $item->getNameId(),
                'name' => $item->getTitle(),
                'elements' => $elements,
            ];
        }

        return $items;
    }
    
    protected function children($element)
    {
        $site = \App::make('site');
        
        $items = $site->getItemList();
        
        $itemList = [];

        foreach ($items as $item) {
            if ( ! $item->getTree()) continue;

            $propertyList = $item->getPropertyList();

            foreach ($propertyList as $property) {
                if (
                    $property->isOneToOne()
                    && $property->getRelatedClass() == Element::getClass($element)
                ) {
                    $itemList[] = $item;
                    break;
                }
            }
        }
        
        $total = 0;
        
        foreach ($itemList as $item) {
            $total += $this->count($element, $item);
        }
        
        return $total;
    }
    
    protected function count($element, $currentItem)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $classId = $element ? Element::getClassId($element) : null;
        
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
            function($query) use ($propertyList, $element) {
                if ($element) {
                    $query->orWhere('id', null);
                }

                foreach ($propertyList as $property) {
                    if (
                        $element
                        && $property->isOneToOne()
                        && $property->getRelatedClass() == Element::getClass($element)
                    ) {
                        $query->orWhere(
                            $property->getName(), $element->id
                        );
                    } elseif (
                        ! $element
                        && $property->isOneToOne()
                    ) {
                        $query->orWhere(
                            $property->getName(), null
                        );
                    }
                }
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
                return 0;
			}
		}
        
        $count = $criteria->count();

        return $count;
    }
    
    protected function elements($element, $currentItem)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $classId = $element ? Element::getClassId($element) : null;
        
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
            function($query) use ($propertyList, $element) {
                if ($element) {
                    $query->orWhere('id', null);
                }

                foreach ($propertyList as $property) {
                    if (
                        $element
                        && $property->isOneToOne()
                        && $property->getRelatedClass() == Element::getClass($element)
                    ) {
                        $query->orWhere(
                            $property->getName(), $element->id
                        );
                    } elseif (
                        ! $element
                        && $property->isOneToOne()
                    ) {
                        $query->orWhere(
                            $property->getName(), null
                        );
                    }
                }
            }
        );
        
        foreach ($propertyList as $property) {
            if (
                $element
                && $property->isManyToMany()
                && $property->getRelatedClass() == Element::getClass($element)
            ) {
                $criteria = $element->{$property->getRelatedMethod()}();
                break;
            }
        }

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
        
        if ($hasOrderProperty) {
            $elementList = $criteria->get();
            
            $total = sizeof($elementList);
            $pager = null;
        } else {
            $perpage = $currentItem->getPerPage();
            
            $elementList = $criteria->paginate($perpage);
            
            $total = $elementList->total();
            
            $pager = $total > $perpage ? [
                'currentPage' => $elementList->currentPage(),
                'lastPage' => $elementList->lastPage(),
            ] : null;
        }

        $open = $loggedUser->getParameter('open') ?: [];

        $elements = [];

        foreach ($elementList as $element) {
            $children = $this->children($element);

            $items = $children
                ? $this->items(Element::getClassId($element)) 
                : [];

            $isOpen = isset($open[Element::getClassId($element)])
                ? true : false;
            
            $elements[] = [
                'id' => $element->id,
                'classId' => Element::getClassId($element),
                'name' => $element->{$currentItem->getMainProperty()},
                'children' => $children,
                'items' => $items,
                'open' => $isOpen,
            ];
        }
        
        return $elements;
    }
}