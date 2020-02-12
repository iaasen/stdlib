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
 * @package Iaasen\Entity
 * @deprecated Use \Iaasen\DateTime
 */
class DateTime extends \Iaasen\DateTime {}