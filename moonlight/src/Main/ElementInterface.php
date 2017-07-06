<?php

namespace Moonlight\Main;

interface ElementInterface 
{
	public function getItem();

	public function getClass();

	public function getClassId();

	public function getProperty($name);

	public function equalTo($element);

	public function getAssetsName();

	public function getFolderName();

	public function getFolderHash();

	public function getFolder();

	public function getHref();
    
    public function getTouchListView();
}
