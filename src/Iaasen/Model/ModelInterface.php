<?php

namespace Iaasen\Model;

/**
 * @deprecated Use ModelInterfaceV2
 */
interface ModelInterface
{
    public function __get($name);
    public function __set($name, $value);
    public function __isset($name);
    public function __unset($name);

	/**
	 * When an object is cloned using keyword 'clone'
	 * the __clone() function will be run on the copy.
	 * @return void
	 */
	public function __clone();

    public function __toString();
	public function databaseSaveArray();

	public function exchangeArray($data);
	public function getArrayCopy();
}