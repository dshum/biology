<?php 

namespace Moonlight\Properties;

use Moonlight\Main\Element;

class TextfieldProperty extends BaseProperty 
{
	public static function create($name)
	{
		return new self($name);
	}
    
    public function getSearchView()
	{
        $site = \App::make('site');
        
		$request = $this->getRequest();
        $name = $this->getName();
        $relatedClass = $this->getItemClass();
        $relatedItem = $this->getItem();
        $mainProperty = $relatedItem->getMainProperty();

        if ($mainProperty) {
            $id = (int)$request->input($name);
            
            $element = $id 
                ? $relatedClass::find($id)
                : null;

            $value = $element
                ? [
                    'id' => $element->id, 
                    'name' => $element->{$mainProperty}
                ] : null;

            $scope = array(
                'name' => $this->getName(),
                'title' => $this->getTitle(),
                'value' => $value,
                'open' => $element !== null,
                'relatedClass' => $relatedItem->getNameId(),
                'isMainProperty' => $this->isMainProperty(),
            );
        } else {
            $value = $request->input($name);
            
            $scope = array(
                'name' => $this->getName(),
                'title' => $this->getTitle(),
                'value' => $value,
                'isMainProperty' => $this->isMainProperty(),
            );
        }

		return $scope;
	}
}
