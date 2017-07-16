<?php

namespace Moonlight\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\Element;
use Moonlight\Main\Site;
use Moonlight\Main\UserActionType;
use Moonlight\Models\Favorite;
use Moonlight\Models\UserAction;
use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\FileProperty;
use Moonlight\Properties\ImageProperty;

class BrowseController extends Controller
{
    /**
     * Browse plugin.
     *
     * @return Response
     */
    public function plugin(Request $request, $classId, $method)
    {
        $element = Element::getByClassId($classId);
        
        if ( ! $element) {
            $scope['error'] = 'Элемент не найден';
            
            return response()->json($scope);
        }
        
        $browsePlugin = \App::make('site')->getBrowsePlugin($classId);

        if ( ! $browsePlugin) {
            $scope['error'] = 'Плагин не найден';
            
            return response()->json($scope);
        }

        return \App::make($browsePlugin)->$method($request, $element);
    }
    
    /**
     * Order elements.
     *
     * @return Response
     */
    public function order(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $elements = $request->input('element');

        if (is_array($elements) && sizeof($elements) > 1) {
            foreach ($elements as $order => $classId) {
                $element = Element::getByClassId($classId);
                
                if ($element && $loggedUser->hasUpdateAccess($element)) {
                    $item = Element::getItem($element);
                    if ($item->getOrderProperty()) {
                        $element->{$item->getOrderProperty()} = $order;
                        $element->save();
                    }
                }
            }

            $scope['ordered'] = $elements;
        }

        return response()->json($scope);
    }
    
    /**
     * Copy elements.
     *
     * @return Response
     */
    public function copy(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $ones = $request->input('ones');
        $checked = $request->input('checked');
        
        if ( ! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $classId) {
            $element = Element::getByClassId($classId);
            
            if ($element && $loggedUser->hasViewAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if ( ! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для копирования.';
            
            return response()->json($scope);
        }

        foreach ($elements as $element) {
            $elementItem = Element::getItem($element);
            $propertyList = $elementItem->getPropertyList();
            
            $clone = new $element;
            
            foreach ($propertyList as $propertyName => $property) {
                if ($property instanceof OrderProperty) {
                    $property->setElement($clone)->set();
                    continue;
                }

                if (
                    $property->getHidden()
                    || $property->getReadonly()
                ) continue;

                if (
                    (
                        $property instanceof FileProperty
                        || $property instanceof ImageProperty
                    )
                    && ! $property->getRequired()
                ) continue;

                if (
                    $property->isOneToOne()
                    && isset($ones[$propertyName])
                    && $ones[$propertyName]
                ) {
                    $clone->$propertyName = $ones[$propertyName];
                } elseif ($element->$propertyName !== null) {
                    $clone->$propertyName = $element->$propertyName;
                } else {
                    $clone->$propertyName = null;
                }
            }

            $clone->save();
            
            $scope['copied'][] = Element::getClassId($clone);
        }
        
        if (isset($scope['copied'])) {
            UserAction::log(
                UserActionType::ACTION_TYPE_COPY_ELEMENT_LIST_ID,
                implode(', ', $scope['copied'])
            );
        }
        
        return response()->json($scope);
    }
    
    /**
     * Copy elements.
     *
     * @return Response
     */
    public function move(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $ones = $request->input('ones');
        $checked = $request->input('checked');
        
        if ( ! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $classId) {
            $element = Element::getByClassId($classId);
            
            if ($element && $loggedUser->hasUpdateAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if ( ! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для переноса.';
            
            return response()->json($scope);
        }

        foreach ($elements as $element) {
            $elementItem = Element::getItem($element);
            $propertyList = $elementItem->getPropertyList();
            
            $changed = false;

            foreach ($propertyList as $propertyName => $property) {
                if (
                    $property->getHidden()
                    || $property->getReadonly()
                ) continue;

                if (
                    $property->isOneToOne()
                    && isset($ones[$propertyName])
                ) {
                    $element->$propertyName = $ones[$propertyName]
                        ? $ones[$propertyName] : null;
                    
                    $changed = true;
                }
            }
            
            if ($changed) {
                $element->save();
                
                $scope['moved'][] = Element::getClassId($element);
            }
        }
        
        if (isset($scope['moved'])) {
            UserAction::log(
                UserActionType::ACTION_TYPE_MOVE_ELEMENT_LIST_ID,
                implode(', ', $scope['moved'])
            );
        }
        
        return response()->json($scope);
    }
    
    /**
     * Delete elements.
     *
     * @return Response
     */
    public function delete(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $checked = $request->input('checked');
        
        if ( ! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $classId) {
            $element = Element::getByClassId($classId);
            
            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if ( ! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для удаления.';
            
            return response()->json($scope);
        }
        
        $site = \App::make('site');
        
        $itemList = $site->getItemList();
        
        foreach ($elements as $element) {
            $elementItem = Element::getItem($element);
            $className = Element::getClass($element);
            
            foreach ($itemList as $item) {
                $itemName = $item->getName();
                $propertyList = $item->getPropertyList();

                foreach ($propertyList as $property) {
                    if (
                        $property->isOneToOne()
                        && $property->getRelatedClass() == $className
                    ) {
                        $count = $element->
                            hasMany($itemName, $property->getName())->
                            count();

                        if ($count) {
                            $scope['restricted'][] = $element->{$elementItem->getMainProperty()};
                        }
                    }
                }
            }
        }

		if (isset($scope['restricted'])) {
            $scope['error'] = 'Сначала удалите вложенные элементы следующих элементов: '
                .implode(', ', $scope['restricted']);
            
            return response()->json($scope);
        }
        
        foreach ($elements as $element) {
            if ($element->delete()) {
                $scope['deleted'][] = Element::getClassId($element);
            }
        }
        
        if (isset($scope['deleted'])) {
            UserAction::log(
                UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID,
                implode(', ', $scope['deleted'])
            );
        }
        
        return response()->json($scope);
    }
    
    /**
     * Delete elements from trash.
     *
     * @return Response
     */
    public function forceDelete(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $checked = $request->input('checked');
        
        if ( ! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $classId) {
            $element = Element::getOnlyTrashedByClassId($classId);
            
            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if ( ! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для удаления.';
            
            return response()->json($scope);
        }
        
        foreach ($elements as $element) {
            $item = Element::getItem($element);

            $propertyList = $item->getPropertyList();

            foreach ($propertyList as $propertyName => $property) {
                $property->setElement($element)->drop();
            }

            $element->forceDelete();
            
            $scope['deleted'][] = Element::getClassId($element);
        }
        
        if (isset($scope['deleted'])) {
            UserAction::log(
                UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_ID,
                implode(', ', $scope['deleted'])
            );
        }
        
        return response()->json($scope);
    }
    
    /**
     * Delete elements from trash.
     *
     * @return Response
     */
    public function restore(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $checked = $request->input('checked');
        
        if ( ! is_array($checked) || ! sizeof($checked)) {
            $scope['error'] = 'Пустой список элементов.';
            
            return response()->json($scope);
        }
        
        $elements = [];
        
        foreach ($checked as $classId) {
            $element = Element::getOnlyTrashedByClassId($classId);
            
            if ($element && $loggedUser->hasDeleteAccess($element)) {
                $elements[] = $element;
            }
        }
        
        if ( ! sizeof($elements)) {
            $scope['error'] = 'Нет элементов для восстановления.';
            
            return response()->json($scope);
        }
        
        foreach ($elements as $element) {
            $element->restore();
            
            $scope['restored'][] = Element::getClassId($element);
        }
        
        if (isset($scope['restored'])) {
            UserAction::log(
                UserActionType::ACTION_TYPE_RESTORE_ELEMENT_LIST_ID,
                implode(', ', $scope['restored'])
            );
        }
        
        return response()->json($scope);
    }
    
    /**
     * Return the count of element list.
     *
     * @return Response
     */
    public function count(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $class = $request->input('item');
        $classId = $request->input('classId');
        
        $site = \App::make('site');
        
        $item = $site->getItemByName($class);
        
        if ( ! $item) {
            return response()->json(['count' => 0]);
        }
        
        $element = $classId 
            ? Element::getByClassId($classId) : null;

		if ( ! $element && ! $item->getRoot()) {
			return response()->json(['count' => 0]);
		}
        
        $propertyList = $item->getPropertyList();

		if ($element) {
			$flag = false;
            
			foreach ($propertyList as $propertyName => $property) {
				if (
					($property->isOneToOne() || $property->isManyToMany())
					&& $property->getRelatedClass() == Element::getClass($element)
				) {
                    $flag = true;
                }
			}
            
			if ( ! $flag) {
				return response()->json(['count' => 0]);
			}
		}

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

        $criteria = $item->getClass()->where(
            function($query) use ($propertyList, $element) {
                if ($element) {
                    $query->orWhere('id', null);
                }

                foreach ($propertyList as $propertyName => $property) {
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

		$count = $criteria->count();
        
        $scope['count'] = $count;
            
        return response()->json($scope);
    }
    
    /**
     * Open closed item.
     *
     * @return Response
     */
    public function open(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $class = $request->input('item');
        $classId = $request->input('classId');
        
        $site = \App::make('site');
        
        $item = $site->getItemByName($class);
        
        if ( ! $item) {
            return response()->json([]);
        }
        
        $element = Element::getByClassId($classId);
        
        $lists = $loggedUser->getParameter('lists');
        $cid = $element ? Element::getClassId($element) : Site::ROOT;
        $lists[$cid][$item->getNameId()] = true;
        $loggedUser->setParameter('lists', $lists);

        return response()->json([]);
    }
    
    /**
     * Close opened item.
     *
     * @return Response
     */
    public function close(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $class = $request->input('item');
        $classId = $request->input('classId');
        
        $site = \App::make('site');
        
        $item = $site->getItemByName($class);
        
        if ( ! $item) {
            return response()->json([]);
        }
        
        $element = Element::getByClassId($classId);
        
        $lists = $loggedUser->getParameter('lists');
        $cid = $element ? Element::getClassId($element) : Site::ROOT;
        $lists[$cid][$item->getNameId()] = false;
        $loggedUser->setParameter('lists', $lists);

        return response()->json([]);
    }
    
    /**
     * Show element list.
     *
     * @return Response
     */
    public function elements(Request $request)
    {
        $scope = [];
        
        // usleep(rand(100000, 800000));
        
        $loggedUser = LoggedUser::getUser();
        
        $class = $request->input('item');
        $classId = $request->input('classId');
        
        $site = \App::make('site');
        
        $item = $site->getItemByName($class);
        
        if ( ! $item) {
            return response()->json([]);
        }
        
        $element = Element::getByClassId($classId);
        
        $lists = $loggedUser->getParameter('lists');
        $cid = $element ? Element::getClassId($element) : Site::ROOT;
        $lists[$cid][$item->getNameId()] = true;
        $loggedUser->setParameter('lists', $lists);
        
        return $this->elementListView($element, $item);
    }
    
    protected function elementListView($element, $currentItem)
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
        
        $perpage = $currentItem->getPerPage();
            
        $elementList = $criteria->paginate($perpage);

        $total = $elementList->total();

        $pager = $total > $perpage ? [
            'currentPage' => $elementList->currentPage(),
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
                'item' => [
                    'id' => $currentItem->getNameId(),
                    'name' => $currentItem->getTitle(),
                ]
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
                'classId' => Element::getClassId($element),
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
    
    /**
     * Show element list for autocomplete.
     *
     * @return Response
     */
    public function autocomplete(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $class = $request->input('item');
        $query = $request->input('query');
        
        $site = \App::make('site');
        
        $currentItem = $site->getItemByName($class);
        
        if ( ! $currentItem) {
            return response()->json($scope);
        }
        
        $mainProperty = $currentItem->getMainProperty();

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

        $criteria = $currentItem->getClass()->query();
        
        if ($query) {
            $criteria->whereRaw(
                "cast(id as text) ilike :query or $mainProperty ilike :query",
                ['query' => '%'.$query.'%']
            );
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
        
        $orderByList = $currentItem->getOrderByList();

		foreach ($orderByList as $field => $direction) {
            $criteria->orderBy($field, $direction);
        }

		$elements = $criteria->limit(10)->get();
        
        $scope['suggestions'] = [];
        
        foreach ($elements as $element) {
            $scope['suggestions'][] = [
                'value' => $element->$mainProperty,
                'classId' => Element::getClassId($element),
                'id' => $element->id,
            ];
        }
        
        return response()->json($scope);
    }
    
    /**
     * Show element.
     *
     * @return Response
     */
    public function element(Request $request, $classId)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $element = Element::getByClassId($classId);
        
        if ( ! $element) {
            $scope['state'] = 'error_element_not_found';
            return response()->json($scope);
        }
        
        $currentItem = Element::getItem($element);
        
        $parent = Element::getParent($element);
        
        $parentList = Element::getParentList($element);
        
        $parents = [];
        
        foreach ($parentList as $parent) {
            $parentItem = Element::getItem($parent);
            $parentMainProperty = $parentItem->getMainProperty();
            $parents[] = [
                'id' => $parent->id,
                'classId' => Element::getClassId($parent),
                'name' => $parent->{$parentMainProperty}
            ];
        }

        $scope['element'] = [
            'id' => $element->id,
            'classId' => Element::getClassId($element),
            'parent' => $parent
                ? [
                    'id' => $parent->id,
                    'classId' => Element::getClassId($parent),
                    'name' => $parent->{Element::getItem($parent)->getMainProperty()},
                ] : null,
            'name' => $element->{$currentItem->getMainProperty()},
            'created_at' => $element->created_at->format('c'),
        ];
            
        $scope['parents'] = $parents;
        
        $scope['item'] = [
            'id' => $currentItem->getNameId(),
            'name' => $currentItem->getTitle(),
        ];
            
        return response()->json($scope);
    }
    
    /**
     * Show browse element.
     *
     * @return Response
     */
    public function browse(Request $request, $classId)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $lists = $loggedUser->getParameter('lists');
        
        $element = Element::getByClassId($classId);
        
        if ( ! $element) {
            $scope['state'] = 'error_element_not_found';
            return response()->json($scope);
        }
        
        $currentItem = Element::getItem($element);
        
        $site = \App::make('site');
        
        $itemList = $site->getItemList();
        
        $binds = [];
        
        foreach ($site->getBinds() as $name => $classes) {
            if (
                $name == Element::getClassId($element) 
                || $name == $currentItem->getNameId()
            ) {
                foreach ($classes as $class) {
                    $binds[] = $class;
                }
            }
        }

		$items = [];
        $creates = [];
        
        foreach ($binds as $bind) {
            $item = $site->getItemByName($bind);

            if ( ! $item) continue;

            $propertyList = $item->getPropertyList();

            $mainPropertyTitle = $item->getMainPropertyTitle();

            foreach ($propertyList as $property) {
                if (
                    $property->isOneToOne()
                    && $property->getRelatedClass() == Element::getClass($element)
                ) {
                    $defaultOpen = $property->getOpenItem();
                    
                    $open = isset($lists[$classId][$item->getNameId()])
                        ? $lists[$classId][$item->getNameId()]
                        : $defaultOpen;

                    $items[] = [
                        'id' => $item->getNameId(),
                        'name' => $item->getTitle(),
                        'mainProperty' => $mainPropertyTitle,
                        'open' => $open,
                    ];

                    if ($item->getCreate()) {
                        $creates[] = [
                            'id' => $item->getNameId(),
                            'name' => $item->getTitle(),
                        ];
                    }
                    
                    break;
                } elseif (
                    $property->isManyToMany()
                    && $property->getRelatedClass() == Element::getClass($element)
                ) {
                    $defaultOpen = $property->getOpenItem();
                    
                    $open = isset($lists[$classId][$item->getNameId()])
                        ? $lists[$classId][$item->getNameId()]
                        : $defaultOpen;
                    
                    $items[] = [
                        'id' => $item->getNameId(),
                        'name' => $item->getTitle(),
                        'mainProperty' => $mainPropertyTitle,
                        'open' => $open,
                    ];

                    if ($item->getCreate()) {
                        $creates[] = [
                            'id' => $item->getNameId(),
                            'name' => $item->getTitle(),
                        ];
                    }
                    
                    break;
                }
            }
        }

		$scope['items'] = $items;
        $scope['creates'] = $creates;
            
        return response()->json($scope);
    }
    
    /**
     * Show browse root.
     *
     * @return Response
     */
    public function root(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $lists = $loggedUser->getParameter('lists');
        
        $site = \App::make('site');
        
        $itemList = $site->getItemList();
        $binds = $site->getBinds();

		$items = [];
        $creates = [];
        
        if (isset($binds[Site::ROOT])) {
            foreach ($binds[Site::ROOT] as $itemNameId) {
                $item = $site->getItemByName($itemNameId);
                
                if ( ! $item) continue;
                
                $open = isset($lists[Site::ROOT][$item->getNameId()])
                    ? $lists[Site::ROOT][$item->getNameId()]
                    : false;

                $mainPropertyTitle = $item->getMainPropertyTitle();
                
                $items[] = [
                    'id' => $item->getNameId(),
                    'name' => $item->getTitle(),
                    'mainProperty' => $mainPropertyTitle,
                    'open' => $open,
                ];
                
                if ($item->getCreate()) {
                    $creates[] = [
                        'id' => $item->getNameId(),
                        'name' => $item->getTitle(),
                    ];
                }
            }
        }

		$scope['items'] = $items;
        $scope['creates'] = $creates;
            
        return response()->json($scope);
    }
}