<?php namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;

class User extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_users';

	/**
	 * The Eloquent group model.
	 *
	 * @var string
	 */
	protected static $groupModel = 'Moonlight\Models\Group';

	/**
	 * The user groups pivot table name.
	 *
	 * @var string
	 */
	protected static $userGroupsPivot = 'admin_users_groups_pivot';

	/**
	 * The assets folder name.
	 *
	 * @var string
	 */
	protected $assetsName = 'assets';

    public function getDates()
	{
		return array('created_at', 'updated_at', 'last_login');
	}

	public function isSuperUser()
	{
		return $this->superuser ? true : false;
	}

	public function groups()
	{
		return $this->belongsToMany(static::$groupModel, static::$userGroupsPivot);
	}

	public function addGroup(Group $group)
	{
		if ( ! $this->inGroup($group)) {
			$this->groups()->attach($group);
		}
	}

	public function removeGroup(Group $group)
	{
		if ($this->inGroup($group)) {
			$this->groups()->detach($group);
		}

		return true;
	}

	public function inGroup(Group $group)
	{
		foreach ($this->getGroups() as $_group) {
			if ($_group->id == $group->id) {
				return true;
			}
		}

		return false;
	}

	public function getGroups()
	{
        return $this->groups()->get();
	}

	public function getUnserializedParameters()
	{
		try {
			return unserialize($this->parameters);
		} catch (\Exception $e) {}

		return null;
	}

	public function getParameter($name)
	{
		$unserializedParameters = $this->getUnserializedParameters();

		return
			isset($unserializedParameters[$name])
			? $unserializedParameters[$name]
			: null;
	}

	public function setParameter($name, $value)
	{
		try {

			$unserializedParameters = $this->getUnserializedParameters();

			$unserializedParameters[$name] = $value;

			$parameters = serialize($unserializedParameters);

			$this->parameters = $parameters;

			$this->save();

		} catch (\Exception $e) {}

		return $this;
	}

	public function hasAccess($name)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			if ($group->hasAccess($name)) {
				return true;
			}
		}

		return false;
	}

	public function hasUpdateDefaultAccess(Item $item)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getItemAccess($item);
			if (in_array($access, array('update', 'delete'))) {
				return true;
			}
		}

		return false;
	}

	public function hasViewAccess(Model $element)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getElementAccess($element);
			if (in_array($access, array('view', 'update', 'delete'))) {
				return true;
			}
		}

		return false;
	}

	public function hasUpdateAccess(Model $element)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getElementAccess($element);
			if (in_array($access, array('update', 'delete'))) {
				return true;
			}
		}

		return false;
	}

	public function hasDeleteAccess(Model $element)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getElementAccess($element);
			if (in_array($access, array('delete'))) {
				return true;
			}
		}

		return false;
	}
    
    public function getAssetsName()
	{
		return $this->assetsName;
	}

	public function getAssets()
	{
		return $this->getAssetsName();
	}

	public function getAssetsPath()
	{
		return
			public_path()
            .DIRECTORY_SEPARATOR
			.$this->getAssets()
			.DIRECTORY_SEPARATOR;
	}
    
    public function getFolderName()
	{
		return $this->getTable();
	}
    
	public function getFolder()
	{
		return
			$this->getAssetsName()
			.DIRECTORY_SEPARATOR
			.$this->getFolderName();
	}
    
    public function getFolderPath()
	{
		return
            public_path()
            .DIRECTORY_SEPARATOR
			.$this->getFolder()
			.DIRECTORY_SEPARATOR;
	}
    
    public function getPhoto()
    {
        return $this->photo;
    }
    
    public function photoExists()
	{
		return $this->getPhoto() && file_exists($this->getPhotoAbsPath());
	}
    
    public function getPhotoAbsPath()
    {
        return $this->getPhoto()
            ? $this->getFolderPath().$this->getPhoto()
            : null;
    }
    
    public function getPhotoSrc()
    {
        return 
			$this->getPhoto()
			&& file_exists($this->getPhotoAbsPath())
            ? asset($this->getFolder().DIRECTORY_SEPARATOR.$this->getPhoto())
            : null;
    }
}
