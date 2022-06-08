<?php

namespace Iaasen\Model;

use Laminas\Stdlib\ArraySerializableInterface;

interface ModelInterfaceV2 extends ArraySerializableInterface
{
    public function __get(string $name);
    public function __set(string $name, $value) : void;
    public function __isset(string $name) : bool;
    public function __unset(string $name) : void;

	/**
	 * When an object is cloned using keyword 'clone'
	 * the __clone() function will be run on the copy.
	 * @return void
	 */
	public function __clone();

    public function __toString() : string;
    public function databaseSaveArray() : array;
}