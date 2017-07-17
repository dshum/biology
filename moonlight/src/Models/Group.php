<?php namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Site;
use Moonlight\Main\Item;
use Moonlight\Main\Element;

class Group extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_groups';

    /**
	 * The user groups pivot table name.
	 *
	 * @var string
	 */
	protected $pivotTable = 'admin_users_groups_pvot';

    /**
	 * The group users.
	 *
	 * @var array
	 */
	protected $groupUsers;
    
    private $permissionTitles = [
        'deny' => 'Доступ закрыт',
        'view' => 'Просмотр элементов',
        'update' => 'Изменение элементов',
        'delete' => 'Удаление элементов',
    ];

    public function getDates()
	{
		return array('created_at', 'updated_at');
	}

	public function users()
	{
		return $this->belongsToMany('Moonlight\Models\User', $this->pivotTable);
	}

    public function getUsers()
	{
		return $this->users()->get();
	}

	public function hasAccess($name)
	{
		return $this->getPermission($name) ? true : false;
	}

	public function getUnserializedPermissions()
	{
		try {
			return unserialize($this->permissions);
		} catch (\Exception $e) {}

		return null;
	}

	public function getPermission($name)
	{
		$unserializedPermissions = $this->getUnserializedPermissions();

		return
			isset($unserializedPermissions[$name])
			? $unserializedPermissions[$name]
			: null;
	}

	public function setPermission($name, $value)
	{
		$unserializedPermissions = $this->getUnserializedPermissions();

		$unserializedPermissions[$name] = $value;

		$permissions = serialize($unserializedPermissions);

		$this->permissions = $permissions;

		return $this;
	}
    
    public function getPermissionTitle()
    {
        $name = $this->default_permission;
        
        return isset($this->permissionTitles[$name])
            ? $this->permissionTitles[$name]
            : null;
    }

	public function itemPermissions()
	{
		return $this->hasMany('Moonlight\Models\GroupItemPermission');
	}

	public function elementPermissions()
	{
		return $this->hasMany('Moonlight\Models\GroupElementPermission');
	}

	public function getItemPermission($class)
	{
		return $this->itemPermissions()->where('class', $class)->first();
	}

	public function getElementPermission($classId)
	{
		return $this->elementPermissions()->where('class_id', $classId)->first();
	}

	public function getItemAccess(Item $item)
	{
		$itemPermission = $this->getItemPermission($item->getName());

		if ($itemPermission) return $itemPermission->permission;

		return $this->default_permission;
	}

	public function getElementAccess(Model $element)
	{
		$elementPermission = $this->getElementPermission(Element::getClassId($element));

		if ($elementPermission) return $elementPermission->permission;

		$itemPermission = $this->getItemPermission(Element::getClass($element));

		if ($itemPermission) return $itemPermission->permission;

		return $this->default_permission;
	}

}
