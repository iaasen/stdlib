<?php
/**
 * User: ingvar.aasen
 * Date: 13.05.2019
 * Time: 18:20
 */

namespace Iaasen;


use League\Geotools\Coordinate\Ellipsoid;

class GRS80Ellipsoid extends Ellipsoid
{
	public function __construct()
	{
		parent::__construct('GRS80', 6378137.0, 298.257222101);
	}
}