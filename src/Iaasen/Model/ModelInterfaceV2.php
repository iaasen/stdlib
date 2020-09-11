<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 08.11.2015
 * Time: 14.48
 */

namespace Iaasen\Model;

interface ModelInterfaceV2
{
    public function __get($name);
    public function __set($name, $value);
    public function __isset($name);
    public function __unset($name);
	public function __clone();
    public function __toString();

	/**
	 * @param array $data
	 * @return array
	 */
    public function exchangeArray(array $data);

	/**
	 * @return array
	 */
    public function getArrayCopy();
    public function databaseSaveArray();
}