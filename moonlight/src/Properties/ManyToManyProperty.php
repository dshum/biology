<?php 

namespace Moonlight\Properties;

use Illuminate\Support\Facades\Log;
use Moonlight\Main\Item;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\Element;

class ManyToManyProperty extends BaseProperty 
{
	protected $relatedClass = null;
    protected $relatedMethod = null;
    
    protected $list = [];

	public function __construct($name) {
		parent::__construct($name);

		return $this;
	}

	public static function create($name)
	{
		return new self($name);
	}

	public function setRelatedClass($relatedClass)
	{
		Item::assertClass($relatedClass);

		$this->relatedClass = $relatedClass;

		return $this;
	}

	public function getRelatedClass()
	{
		return $this->relatedClass;
	}
    
    public function setRelatedMethod($relatedMethod)
	{
		$this->relatedMethod = $relatedMethod;

		return $this;
	}

	public function getRelatedMethod()
	{
		return $this->relatedMethod;
	}

	public function setList($list)
	{
		$this->list = $list;

		return $this;
	}

	public function getList()
	{
		return $this->list;
	}
    
    public function setElement(ElementInterface $element)
	{
        $site = \App::make('site');
        
		$name = $this->getName();
        $relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
        
		$this->element = $element;

		if (method_exists($this->element, $name)) {
			$this->setList($this->element->{$name}()->get());
		}

		return $this;
	}
    
    public function set()
	{
        $name = $this->getName();
        $value = $this->buildInput();

		try {
			$ids = explode(',', $value);

			if (method_exists($this->element, $name)) {
				$this->element->{$name}()->sync($ids);
			}
		} catch (\Exception $e) {}

		return $this;
	}
    
    public function getBrowseView()
	{
		$site = \App::make('site');
		
		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
		$list = $this->getList();

		$elements = [];

		foreach ($list as $element) {
            $elements[] = [
                'id' => $element->id,
                'classId' => $element->getClassId(),
                'name' => $element->{$mainProperty},
            ];
        }

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'elements' => $elements,
		];

		return $scope;
	}
    
    public function getEditView()
	{
		$site = \App::make('site');

		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
        $list = $this->getList();

		$elements = [];

		foreach ($list as $element) {
            $elements[] = [
                'id' => $element->id,
                'classId' => $element->getClassId(),
                'name' => $element->{$mainProperty},
            ];
        }

		$scope = [
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'elements' => $elements,
			'readonly' => $this->getReadonly(),
			'required' => $this->getRequired(),
			'relatedClass' => $relatedItem->getNameId(),
		];

		return $scope;
	}
    
    public function getSearchView()
	{
		return null;
	}
    
    public function isManyToMany()
	{
		return true;
	}
}
