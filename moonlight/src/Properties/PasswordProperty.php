<?php 

namespace Moonlight\Properties;

class PasswordProperty extends BaseProperty
{
	public static function create($name)
	{
		return new self($name);
	}
    
    public function searchQuery($query)
	{
		return $query;
	}

	public function getSearchView()
	{
		return null;
	}
    
    public function getListView()
    {
        return null;
    }
}
