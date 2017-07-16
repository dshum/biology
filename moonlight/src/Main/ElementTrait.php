<?php

namespace Moonlight\Main;

use Illuminate\Database\Eloquent\SoftDeletes;

trait ElementTrait 
{
	use SoftDeletes;

	protected $item = null;
	protected $assetsName = 'assets';

	public function getDates()
	{
		return array(
			'created_at',
			'updated_at',
			'deleted_at',
		);
	}

	public function setParent(ElementInterface $parent)
	{
		$item = Element::getItem($this);

		$propertyList = $item->getPropertyList();

		foreach ($propertyList as $propertyName => $property) {
			if (
				$property->isOneToOne()
				&& $property->getRelatedClass() == $parent->getClass()
			) {
				$this->$propertyName = $parent->id;
			}
		}

		return $this;
	}

	public function getItem()
	{
		if ($this->item) return $this->item;

		$site = \App::make('site');

		$class = $this->getClass();

		$this->item = $site->getItemByName($class);

		return $this->item;
	}

	public function getClass()
	{
		return get_class($this);
	}

	public function getClassId()
	{
		return Element::getClassId($this);
	}

	public function getProperty($name)
	{
		$item = Element::getItem($this);

		$property = $item->getPropertyByName($name);

		return $property->setElement($this);
	}

	public function equalTo($element)
	{
		return
			$element instanceof ElementInterface
			&& Element::getClassId($this) == Element::getClassId($element)
			? true : false;
	}

	public function getAssetsName()
	{
		return $this->assetsName;
	}

	public function getFolderName()
	{
		return $this->getTable();
	}

	public function getFolderHash()
	{
		return null;
	}

	public function getFolder()
	{
		return
			$this->getAssetsName()
			.DIRECTORY_SEPARATOR
			.$this->getFolderName();
	}

	public function getHref()
	{
		return null;
	}
    
    public function getTouchListView()
    {
        return null;
    }
}