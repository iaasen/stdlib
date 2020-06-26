<?php
/**
 * User: ingvar
 * Date: 26.06.2020
 * Time: 16.16
 */

namespace Iaasen;


class DateTimeWithoutTimezone extends DateTime
{
	public function jsonSerialize()
	{
		return $this->format('Y-m-d\TH:i:s');
	}
}