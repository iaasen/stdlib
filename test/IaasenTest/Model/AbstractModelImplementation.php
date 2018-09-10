<?php
/**
 * User: ingvar.aasen
 * Date: 10.09.2018
 * Time: 13:18
 */

namespace IaasenTest\Model;


use Iaasen\Entity\DateTime;

/**
 * Class AbstractModelImplementation
 * @package IaasenTest\Model
 * @property bool $bool
 * @property int $int
 * @property float $float
 * @property string $string
 * @property string[] $stringArray
 * @property \stdClass $stdClass
 * @property DateTime $dateTime
 */
class AbstractModelImplementation extends \Iaasen\Model\AbstractModel
{
	/** @var bool */
	protected $bool;
	/** @var int */
	protected $int;
	/** @var float */
	protected $float;
	/** @var string */
	protected $string;
	/** @var string[] */
	protected $stringArray;
	/** @var DateTime */
	protected $dateTime;
}