<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 28.04.2017
 * Time: 16:16
 */

namespace Oppned;


class Ipv4Calculator
{
	/**
	 * Check if $ip is between $start and $end
	 * @param string $ip
	 * @param string $start
	 * @param string $end
	 * @return bool
	 */
	public static function isInRange($ip, $start, $end) {
		return (ip2long($ip) >= ip2long($start) && ip2long($ip) <= ip2long($end));
	}

	/**
	 * @param string $ip
	 * @param string $mask
	 * @return string
	 */
	public static function getNetworkIp($ip, $cidr) {
		$ip = ip2long($ip);
		$mask = ip2long(self::getNetmaskFromCidr($cidr));
		return long2ip($ip & $mask);
	}

	/**
	 * @param string $ip
	 * @param int $cidr
	 * @return string
	 */
	public static function getBroadcastIp($ip, $cidr) {
		return self::getIpRangeEnd($ip, $cidr);
	}


	/**
	 * @param int $cidr
	 * @return string
	 */
	public static function getNetmaskFromCidr($cidr) {
		$netmask = '';
		for($i = 0; $i < $cidr; $i++) {
			$netmask .= 1;
		}
		for($i = 0; $i < 32 - $cidr; $i++) {
			$netmask .= 0;
		}
		$netmask = bindec($netmask);

		return long2ip($netmask);
	}

	/**
	 * @param string $netmask
	 * @return int
	 */
	public static function getCidrFromNetmask($netmask) {
		$mask = ip2long($netmask);
		$base = ip2long('255.255.255.255');
		return 32 - log(($mask ^ $base)+1, 2);
	}


	/**
	 * @param string $mask
	 * @return string
	 */
	public static function getWildcardMaskFromNetmask($mask) {
		$mask = ip2long($mask);
		$full = ip2long('255.255.255.255');
		return long2ip($mask ^ $full);
	}

	/**
	 * @param string $ip
	 * @param int $cidr
	 * @return string
	 */
	public static function getIpRangeStart($ip, $cidr) {
		$ip = ip2long($ip);
		$mask = ip2long(self::getNetmaskFromCidr($cidr));
		return long2ip($ip & $mask);
	}

	/**
	 * @param string $ip
	 * @param int $cidr
	 * @return string
	 */
	public static function getIpRangeEnd($ip, $cidr) {
		$ip = ip2long($ip);
		$mask = ip2long(self::getNetmaskFromCidr($cidr));
		$start = $ip & $mask;
		return long2ip($start + self::getIpRangeCount($cidr) - 1);
	}

	/**
	 * @param int $cidr
	 * @return int
	 */
	public static function getIpRangeCount($cidr) {
		return 1 << 32 - $cidr;
	}

	/**
	 * Gives an array of all IP addresses in given network
	 * Will only give answer for $cidr down to /20 (4096 addresses)
	 * @param string $ip
	 * @param int $cidr
	 * @return array|bool
	 */
	public static function getIpRange($ip, $cidr) {
		if($cidr < 20 || $cidr > 32) return false;

		$start = ip2long(self::getIpRangeStart($ip, $cidr));
		$count = self::getIpRangeCount($cidr);

		$current = $start;
		$range = array();
		for($i = 0; $i <= $count; $i++) {
			$range[] = long2ip($current);
			$current++;
		}
		return $range;
	}
}