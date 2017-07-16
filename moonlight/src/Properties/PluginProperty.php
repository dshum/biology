<?php 

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Element;

class PluginProperty extends BaseProperty 
{
	public static function create($name)
	{
		return new self($name);
	}

	public function setElement(Model $element)
	{
		$this->element = $element;

		$getter = $this->getter();

		$this->value = $element->$getter();

		return $this;
	}

	public function set()
	{
		return $this;
	}
    
    public function searchQuery($query)
	{
		return $query;
	}

    public function getBrowseView()
	{
		$element = $this->getElement();
        $item = Element::getItem($element);
		$mainProperty = $item->getMainProperty();

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'element' => $element ? [
                'id' => $element->id,
                'classId' => Element::getClassId($element),
                'name' => $element->{$mainProperty},
            ] : null,
            'item' => [
                'id' => $item->getNameId(),
                'name' => $item->getTitle(),
            ],
		];

		return $scope;
	}

	public function getEditView()
	{
        $element = $this->getElement();
        $item = Element::getItem($this);
		$mainProperty = $item->getMainProperty();

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'element' => $element ? [
                'id' => $element->id,
                'classId' => Element::getClassId($element),
                'name' => $element->{$mainProperty},
            ] : null,
            'item' => [
                'id' => $item->getNameId(),
                'name' => $item->getTitle(),
            ],
		];

		return $scope;
	}

	public function getSearchView()
	{
		return null;
	}
}