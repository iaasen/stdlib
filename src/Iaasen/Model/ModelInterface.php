<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 08.11.2015
 * Time: 14.48
 */

namespace Iaasen\Model;

interface ModelInterface
{
    public function __get($name);
    public function __set($name, $value);
    public function __isset($name);
    public function __unset($name);
	public function __clone();
    public function __toString();
    public function exchangeArray($data);
    public function getArrayCopy();
    public function databaseSaveArray();
}