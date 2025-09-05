<?php

namespace Iaasen\Model;


interface ModelInterfaceV2
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

    /**
     * Nicked from \Laminas\Stdlib\ArraySerializableInterface
     * Populate the object with the provided array
     */
    public function exchangeArray(array $array): void;

    /**
     * Nicked from \Laminas\Stdlib\ArraySerializableInterface
     * Return an array representation of the object
     */
    public function getArrayCopy(): array;

    public function databaseSaveArray() : array;

}