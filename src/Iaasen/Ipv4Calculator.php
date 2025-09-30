<?php
/**
 * User: ingvar.aasen
 * Date: 28.04.2017
 */

namespace Iaasen;


class Ipv4Calculator
{
	/**
	 * Check if $ip is between $start and $end
	 */
	public static function isInRange(string $ip, string $start, string $end): bool
    {
		return (ip2long($ip) >= ip2long($start) && ip2long($ip) <= ip2long($end));
	}

	public static function getNetworkIp(string $ip, string $cidr): string
    {
		$ip = ip2long($ip);
		$mask = ip2long(self::getNetmaskFromCidr($cidr));
		return long2ip($ip & $mask);
	}

	public static function getBroadcastIp(string $ip, int $cidr): string
    {
		return self::getIpRangeEnd($ip, $cidr);
	}

	public static function getNetmaskFromCidr(int $cidr): string
    {
        $netmask = str_repeat(1, $cidr);
        $netmask .= str_repeat(0, 32 - $cidr);
		$netmask = bindec($netmask);
		return long2ip($netmask);
	}

	public static function getCidrFromNetmask(string $netmask) : int
    {
		$mask = ip2long($netmask);
		$base = ip2long('255.255.255.255');
		return (int) (32 - log(($mask ^ $base)+1, 2));
	}

	public static function getWildcardMaskFromNetmask(string $mask): string
    {
		$mask = ip2long($mask);
		$full = ip2long('255.255.255.255');
		return long2ip($mask ^ $full);
	}

	public static function getIpRangeStart(string $ip, int $cidr): string
    {
		$ip = ip2long($ip);
		$mask = ip2long(self::getNetmaskFromCidr($cidr));
		return long2ip($ip & $mask);
	}

	public static function getIpRangeEnd(string $ip, int $cidr): string
    {
		$ip = ip2long($ip);
		$mask = ip2long(self::getNetmaskFromCidr($cidr));
		$start = $ip & $mask;
		return long2ip($start + self::getIpRangeCount($cidr) - 1);
	}

	public static function getIpRangeCount(int $cidr): int
    {
		return 1 << 32 - $cidr;
	}

	public static function addToIp(string $ip, int $add) : string
    {
		$ip = ip2long($ip) + $add;
		if($ip > 4294967295) {
			trigger_error("Cannot add $add to $ip", E_USER_NOTICE );
			$ip = 4294967295; // Highest IP possible
		}
		return long2ip($ip);
	}

	/**
	 * Gives an array of all IP addresses in given network
	 * Will only give answer for $cidr down to /20 (4096 addresses)
	 * @return string[]|false
	 */
	public static function getIpRange(string $ip, int $cidr): array|false
    {
		if($cidr < 20 || $cidr > 32) return false;

		$start = ip2long(self::getIpRangeStart($ip, $cidr));
		$count = self::getIpRangeCount($cidr);

		$current = $start;
		$range = [];
		for($i = 0; $i <= $count; $i++) {
			$range[] = long2ip($current);
			$current++;
		}
		return $range;
	}

	public static function isRfc1918(string $ip, int $cidr) : bool
    {
		$validRfc1918 = false;
		$parts = explode('.', $ip);
		if($parts[0] == 10 && $cidr >= 8) $validRfc1918 = true;
		elseif($parts[0] == 172 && $parts[1] >= 16 && $parts[1] <= 31 && $cidr >= 12) $validRfc1918 = true;
		elseif($parts[0] == 192 && $parts[1] == 168 && $cidr >= 16) $validRfc1918 = true;
		return $validRfc1918;
	}

}
