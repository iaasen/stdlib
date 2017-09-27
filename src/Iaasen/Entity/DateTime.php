<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 15.06.2017
 * Time: 10:51
 */

namespace Iaasen\Entity;


/**
 * Extending \DateTime to make it work well with json_encode. Intended for use in REST services
 * Class DateTime
 * @package Oppned\Entity
 */
class DateTime extends \DateTime implements \JsonSerializable
{
	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	function jsonSerialize()
	{
		return $this->format('c');
	}
}