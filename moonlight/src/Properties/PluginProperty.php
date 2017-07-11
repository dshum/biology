<?php 

namespace Moonlight\Properties;

use Moonlight\Main\ElementInterface;

class PluginProperty extends BaseProperty 
{
	public static function create($name)
	{
		return new self($name);
	}

	public function setElement(ElementInterface $element)
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
        $item = $this->getItem();
		$mainProperty = $item->getMainProperty();

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'element' => $element ? [
                'id' => $element->id,
                'classId' => $element->getClassId(),
                'name' => $element->{$mainProperty},
                'trashed' => $this->isTrashed(),
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
        $item = $this->getItem();
		$mainProperty = $item->getMainProperty();

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'element' => $element ? [
                'id' => $element->id,
                'classId' => $element->getClassId(),
                'name' => $element->{$mainProperty},
                'trashed' => $this->isTrashed(),
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
