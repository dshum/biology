<?php 

namespace Moonlight\Properties;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Moonlight\Main\Item;
use Moonlight\Main\ElementInterface;

abstract class BaseProperty
{
	protected $item = null;
	protected $name = null;
	protected $title = null;
	protected $class = null;

	protected $show = false;
	protected $required = false;
	protected $readonly = false;
	protected $hidden = false;
	protected $editable = false;
    
    protected $openItem = false;

    protected $itemClass = null;
	protected $element = null;
	protected $value = null;
    
    protected $request = null;

	protected $trashed = false;

	protected $rules = array();
	protected $messages = array();

	public function __construct($name)
	{
		$this->name = $name;
		$this->class = class_basename(get_class($this));

		return $this;
	}

	public function getClassName()
	{
		return class_basename(get_class($this));
	}

	public function setItem(Item $item)
	{
		$this->item = $item;

		$itemClass = $item->getName();

		$this->itemClass = new $itemClass;

		return $this;
	}

	public function getItem()
	{
		return $this->item;
	}

	public function getItemClass()
	{
		return $this->itemClass;
	}

	public function getItemName()
	{
		return $this->item->getName();
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function isMainProperty()
	{
		return
			$this->item->getMainProperty() == $this->name
			? true
			: false;
	}

	public function setShow($show)
	{
		$this->show = $show;

		return $this;
	}

	public function getShow()
	{
		return $this->show;
	}

	public function setRequired($required = true)
	{
		$this->required = $required;

		if ($required) {
			$this->
			addRule('required', 'Поле обязательно к заполнению');
		}

		return $this;
	}

	public function getRequired()
	{
		return $this->required;
	}

	public function setReadonly($readonly)
	{
		$this->readonly = $readonly;

		return $this;
	}

	public function getReadonly()
	{
		return $this->readonly;
	}

	public function setHidden($hidden)
	{
		$this->hidden = $hidden;

		return $this;
	}

	public function getHidden()
	{
		return $this->hidden;
	}

	public function setEditable($editable)
	{
		$this->editable = $editable;

		return $this;
	}

	public function getEditable()
	{
		return $this->editable;
	}
    
    public function setOpenItem($openItem)
	{
		$this->openItem = $openItem;

		return $this;
	}

	public function getOpenItem()
	{
		return $this->openItem;
	}

	public function setElement(ElementInterface $element)
	{
		$this->element = $element;
		$this->value = $element->{$this->getName()};
		$this->trashed = $element->trashed();

		return $this;
	}
    
    public function dropElement()
	{
		$this->element = null;
		$this->value = null;

		return $this;
	}

	public function getElement()
	{
		return $this->element;
	}
    
    public function setRequest(Request $request)
	{
		$this->request = $request;

		return $this;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function isTrashed()
	{
		return $this->trashed;
	}

	public function searchQuery($query)
	{
        $request = $this->getRequest();
		$name = $this->getName();

		$value = $request->input($name);

		if ($value) {
			$query->where($name, 'ilike', "%$value%");
		}

		return $query;
	}

	public function searching()
	{
		$request = $this->getRequest();
        $name = $this->getName();

		$value = $request->input($name);

		return $value !== null
			? true : false;
	}
    
    public function buildInput()
    {
        $request = $this->getRequest();
        $name = $this->getName();
        
        $value = $request->input($name);
        
        if (! mb_strlen($value)) $value = null;
        if ($value === 'null') $value = null;
        
        return $value;
    }

	public function set()
	{
        $name = $this->getName();
        $value = $this->buildInput();

		$this->element->$name = $value;

		if ($this->isMainProperty() && ! $value) {
			$this->element->$name = $this->element->id
				? $this->element->getClassId()
				: 'Element';
		}

		return $this;
	}

	public function drop() {}

	public function getBrowseView()
	{
		$element = $this->getElement();
		$classId = $element ? $element->getClassId() : null;

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'isMainProperty' => $this->isMainProperty(),
			'classId' => $classId,
			'trashed' => $this->isTrashed(),
		];

		return $scope;
	}

	public function getEditView()
	{
		$scope = [
			'name' => $this->getName(),
			'title' => $this->getTitle(),
            'isMainProperty' => $this->isMainProperty(),
			'value' => $this->getValue(),
			'readonly' => $this->getReadonly(),
		];

		return $scope;
	}

	public function getEditListView()
	{
		return $this->getListView();
	}

	public function getSearchView()
	{
		$request = $this->getRequest();
        $name = $this->getName();
        $value = $request->input($name);

		if (! mb_strlen($value)) $value = null;

		$scope = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $value,
			'isMainProperty' => $this->isMainProperty(),
		);

		return $scope;
	}

	public function setRules($rules)
	{
		$this->rules = $rules;

		return $this;
	}

	public function addRule($rule, $message = null)
	{
		$this->rules[$rule] = $message ?: $rule;

		return $this;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function isOneToOne()
	{
		return false;
	}
    
    public function isManyToMany()
	{
		return false;
	}

	protected function setter()
	{
		return 'set'.$this->camelize($this->getName());
	}

	protected function getter()
	{
		return 'get'.$this->camelize($this->getName());
	}
    
    protected function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }
}
