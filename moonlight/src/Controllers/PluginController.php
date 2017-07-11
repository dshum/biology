<?php namespace Moonlight\Controllers;

use Log;
use Validator;
use Illuminate\Http\Request;
use Moonlight\Main\LoggedUser;

class PluginController extends Controller {

    public function browse(Request $request, $classId)
    {
        $scope = [];

        $site = \App::make('site');

        $browsePlugin = $site->getBrowsePlugin($classId);

        if ($browsePlugin) {
            $scope['plugin'] = $browsePlugin;
        }

        return response()->json($scope);
    }
}