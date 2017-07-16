<?php

namespace Moonlight\Controllers;

use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\Element;
use Moonlight\Models\User;
use Moonlight\Models\FavoriteRubric;
use Moonlight\Models\Favorite;

class HomeController extends Controller
{
    /**
     * Show favorite rubric list for autocomplete.
     *
     * @return Response
     */
    public function rubrics(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $classId = $request->input('classId');
        
        $favoriteRubrics = FavoriteRubric::orderBy('order')->get();
        
        $scope['suggestions'] = [];
        
        foreach ($favoriteRubrics as $favoriteRubric) {
            $scope['suggestions'][] = [
                'value' => $favoriteRubric->name,
                'data' => $favoriteRubric->id,
            ];
        }
        
        return view('plugin', $scope);
    }
    
    /**
     * Add/remove favorite element.
     *
     * @return Response
     */
    public function save(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $classId = $request->input('classId');
        $rubricId = $request->input('rubricId');
        $rubric = $request->input('rubric');
        $action = $request->input('action');
        
        if ($action == 'orderRubrics') {
            $rubrics = $request->input('rubric');
            
            if (is_array($rubrics) && sizeof($rubrics) > 1) {
                foreach ($rubrics as $order => $id) {
                    $favoriteRubric = FavoriteRubric::find($id);
                    
                    if ($favoriteRubric) {
                        $favoriteRubric->order = $order;
                        $favoriteRubric->save();
                    }
                }
                
                $scope['ordered'] = $rubrics;
            }
            
            return response()->json($scope);
        }
        
        if ($action == 'order') {
            $favorites = $request->input('favorite');
            
            if (is_array($favorites) && sizeof($favorites) > 1) {
                foreach ($favorites as $order => $id) {
                    $favorite = Favorite::find($id);
                    
                    if ($favorite) {
                        $favorite->order = $order;
                        $favorite->save();
                    }
                }
                
                $scope['ordered'] = $favorites;
            }
            
            return response()->json($scope);
        }
        
        if ($action == 'dropRubric' && $rubricId) {
            $favoriteRubric = FavoriteRubric::find($rubricId);
            
            if ($favoriteRubric) {
                $favoriteRubric->delete();
                $scope['deleted'] = $favoriteRubric->id;
            }
            
            return response()->json($scope);
        }
        
        $element = Element::getByClassId($classId);
        
        if ( ! $element) {
            return response()->json(['error' => 'Элемент не найден.']);
        }
        
        $favorite = Favorite::where(
            function($query) use ($loggedUser, $classId) {
                $query->where('user_id', $loggedUser->id);
                $query->where('class_id', $classId);
            }
        )->first();
        
        if ($action == 'drop' && $favorite) {
            $favorite->delete();
            $scope['deleted'] = $favorite->id;
            
            return response()->json($scope);
        }
        
        if ( ! $rubric) {
            return response()->json(['error' => 'Рубрика не указана.']);
        }
        
        $favoriteRubric = FavoriteRubric::where(
            function($query) use ($loggedUser, $rubric) {
                $query->where('user_id', $loggedUser->id);
                $query->where('name', $rubric);
            }
        )->first();
        
        if ( ! $favoriteRubric) {
            $favoriteRubric = new FavoriteRubric;
            
            $favoriteRubric->user_id = $loggedUser->id;
            $favoriteRubric->name = $rubric;
            
            $favoriteRubric->save();
        }

        if ($action == 'add' && ! $favorite) {
            $favorite = new Favorite;
            
            $favorite->user_id = $loggedUser->id;
            $favorite->class_id = $classId;
            $favorite->rubric_id = $favoriteRubric->id;
            
            $favorite->save();
            
            $scope['added'] = $favorite->id;
        }
        
        return response()->json($scope);
    }
    
    /**
     * Show the favorites.
     *
     * @return Response
     */
    public function favorites(Request $request)
    {
        $scope = [];
        
        $loggedUser = LoggedUser::getUser();
        
        $favoriteRubricList = FavoriteRubric::where('user_id', $loggedUser->id)->orderBy('order')->get();
        $favoriteList = Favorite::where('user_id', $loggedUser->id)->orderBy('order')->get();
        
        $rubrics = [];
        $favorites = [];
        
        foreach ($favoriteRubricList as $favoriteRubric) {
            $rubrics[] = [
                'id' => $favoriteRubric->id,
                'name' => $favoriteRubric->name,
            ];
        }
        
        foreach ($favoriteList as $favorite) {
            $element = $favorite->getElement();
            
            if ( ! $element) continue;
            
            $item = Element::getItem($element);
            $mainProperty = $item->getMainProperty();
            
            $favorites[$favorite->rubric_id][] = [
                'id' => $favorite->id,
                'class_id' => $favorite->class_id,
                'name' => $element->{$mainProperty},
            ];
        }
        
        $scope['rubrics'] = $rubrics;
        $scope['favorites'] = $favorites;
            
        return response()->json($scope);
    }
    
    /**
     * Show the home.
     *
     * @return Response
     */
    public function show(Request $request)
    {
        $scope = [
            'api' => 'v1',
        ];
            
        return response()->json($scope);
    }
}