<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 15.06.2017
 * Time: 10:51
 */

namespace Iaasen;


/**
 * Extending \DateTime to make it work well with json_encode. Intended for use in REST services
 * Class DateTime
 * @package Iaasen
 */
class DateTime extends \DateTime implements \JsonSerializable
{
	const HOLIDAYS = [
		'2020-01-01' => 'Første nyttårsdag',
		'2020-04-09' => 'Skjærtorsdag',
		'2020-04-10' => 'Langfredag',
		'2020-04-12' => 'Første påskedag',
		'2020-04-13' => 'Andre påskedag',
		'2020-05-01' => 'Arbeidernes dag',
		'2020-05-17' => 'Grunnlovsdag',
		'2020-05-21' => 'Kristi Himmelfartsdag',
		'2020-05-31' => 'Første pinsedag',
		'2020-06-01' => 'Andre pinsedag',
		'2020-12-25' => 'Første juledag',
		'2020-12-26' => 'Andre juledag',

		'2021-01-01' => 'Første nyttårsdag',
		'2021-04-01' => 'Skjærtorsdag',
		'2021-04-02' => 'Langfredag',
		'2021-04-04' => 'Første påskedag',
		'2021-04-05' => 'Andre påskedag',
		'2021-05-01' => 'Arbeidernes dag',
		'2021-05-13' => 'Kristi Himmelfartsdag',
		'2021-05-17' => 'Grunnlovsdag',
		'2021-05-23' => 'Første pinsedag',
		'2021-05-24' => 'Andre pinsedag',
		'2021-12-25' => 'Første juledag',
		'2021-12-26' => 'Andre juledag',

		'2022-01-01' => 'Første nyttårsdag',
		'2022-04-14' => 'Skjærtorsdag',
		'2022-04-15' => 'Langfredag',
		'2022-04-17' => 'Første påskedag',
		'2022-04-18' => 'Andre påskedag',
		'2022-05-01' => 'Arbeidernes dag',
		'2022-05-17' => 'Grunnlovsdag',
		'2022-05-26' => 'Kristi Himmelfartsdag',
		'2022-06-05' => 'Første pinsedag',
		'2022-06-06' => 'Andre pinsedag',
		'2022-12-25' => 'Første juledag',
		'2022-12-26' => 'Andre juledag',

		'2023-01-01' => 'Første nyttårsdag',
		'2023-04-06' => 'Skjærtorsdag',
		'2023-04-07' => 'Langfredag',
		'2023-04-09' => 'Første påskedag',
		'2023-04-10' => 'Andre påskedag',
		'2023-05-01' => 'Arbeidernes dag',
		'2023-05-17' => 'Grunnlovsdag',
		'2023-05-18' => 'Kristi Himmelfartsdag',
		'2023-05-28' => 'Første pinsedag',
		'2023-05-29' => 'Andre pinsedag',
		'2023-12-25' => 'Første juledag',
		'2023-12-26' => 'Andre juledag',

		'2024-01-01' => 'Første nyttårsdag',
		'2024-03-28' => 'Skjærtorsdag',
		'2024-03-29' => 'Langfredag',
		'2024-03-31' => 'Første påskedag',
		'2024-04-01' => 'Andre påskedag',
		'2024-05-01' => 'Arbeidernes dag',
		'2024-05-09' => 'Kristi Himmelfartsdag',
		'2024-05-17' => 'Grunnlovsdag',
		'2024-05-19' => 'Første pinsedag',
		'2024-05-20' => 'Andre pinsedag',
		'2024-12-25' => 'Første juledag',
		'2024-12-26' => 'Andre juledag',

		'2025-01-01' => 'Første nyttårsdag',
		'2025-04-17' => 'Skjærtorsdag',
		'2025-04-18' => 'Langfredag',
		'2025-04-20' => 'Første påskedag',
		'2025-04-21' => 'Andre påskedag',
		'2025-05-01' => 'Arbeidernes dag',
		'2025-05-17' => 'Grunnlovsdag',
		'2025-05-29' => 'Kristi Himmelfartsdag',
		'2025-06-08' => 'Første pinsedag',
		'2025-06-09' => 'Andre pinsedag',
		'2025-12-25' => 'Første juledag',
		'2025-12-26' => 'Andre juledag',

		'2026-01-01' => 'Første nyttårsdag',
		'2026-04-02' => 'Skjærtorsdag',
		'2026-04-03' => 'Langfredag',
		'2026-04-05' => 'Første påskedag',
		'2026-04-06' => 'Andre påskedag',
		'2026-05-01' => 'Arbeidernes dag',
		'2026-05-14' => 'Kristi Himmelfartsdag',
		'2026-05-17' => 'Grunnlovsdag',
		'2026-05-24' => 'Første pinsedag',
		'2026-05-25' => 'Andre pinsedag',
		'2026-12-25' => 'Første juledag',
		'2026-12-26' => 'Andre juledag',

		'2027-01-01' => 'Første nyttårsdag',
		'2027-03-25' => 'Skjærtorsdag',
		'2027-03-26' => 'Langfredag',
		'2027-03-28' => 'Første påskedag',
		'2027-03-29' => 'Andre påskedag',
		'2027-05-01' => 'Arbeidernes dag',
		'2027-05-06' => 'Kristi Himmelfartsdag',
		'2027-05-16' => 'Første pinsedag',
		'2027-05-17' => 'Grunnlovsdag/Andre pinsedag',
		'2027-12-25' => 'Første juledag',
		'2027-12-26' => 'Andre juledag',

		'2028-01-01' => 'Første nyttårsdag',
		'2028-04-13' => 'Skjærtorsdag',
		'2028-04-14' => 'Langfredag',
		'2028-04-16' => 'Første påskedag',
		'2028-04-17' => 'Andre påskedag',
		'2028-05-01' => 'Arbeidernes dag',
		'2028-05-17' => 'Grunnlovsdag',
		'2028-05-25' => 'Kristi Himmelfartsdag',
		'2028-06-04' => 'Første pinsedag',
		'2028-06-05' => 'Andre pinsedag',
		'2028-12-25' => 'Første juledag',
		'2028-12-26' => 'Andre juledag',

		'2029-01-01' => 'Første nyttårsdag',
		'2029-03-29' => 'Skjærtorsdag',
		'2029-03-30' => 'Langfredag',
		'2029-04-01' => 'Første påskedag',
		'2029-04-02' => 'Andre påskedag',
		'2029-05-01' => 'Arbeidernes dag',
		'2029-05-10' => 'Kristi Himmelfartsdag',
		'2029-05-17' => 'Grunnlovsdag',
		'2029-05-20' => 'Første pinsedag',
		'2029-05-21' => 'Andre pinsedag',
		'2029-12-25' => 'Første juledag',
		'2029-12-26' => 'Andre juledag',

		'2030-01-01' => 'Første nyttårsdag',
		'2030-04-18' => 'Skjærtorsdag',
		'2030-04-19' => 'Langfredag',
		'2030-04-21' => 'Første påskedag',
		'2030-04-22' => 'Andre påskedag',
		'2030-05-01' => 'Arbeidernes dag',
		'2030-05-17' => 'Grunnlovsdag',
		'2030-05-30' => 'Kristi Himmelfartsdag',
		'2030-06-09' => 'Første pinsedag',
		'2030-06-10' => 'Andre pinsedag',
		'2030-12-25' => 'Første juledag',
		'2030-12-26' => 'Andre juledag',
	];

	public $workdayStart = 8;
	public $workdayEnd = 16;


	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return $this->format('c');
	}


	public  function formatMysql(){
		return $this->format('Y-m-d H:i:s');
	}


	public function isWorkday() : bool {
		return !$this->isHoliday() && !$this->isWeekend();
	}


	public function isHoliday() : bool {
		return isset(self::HOLIDAYS[$this->format('Y-m-d')]);
	}

	public function isWeekend() : bool {
		return $this->format('N') > 5;
	}


	public function isWorkhour() : bool {
		return
			!$this->isHoliday() &&
			!$this->isWeekend() &&
			$this->format('G') >= $this->workdayStart &&
			$this->format('G') < $this->workdayEnd;
	}


	public function addDays(int $days, $workdaysOnly = true) : self {
		$daysLeft = $days;
		$oneDayInterval = new \DateInterval('P1D');
		while($daysLeft > 0) {
			$this->add($oneDayInterval);
			if($workdaysOnly && $this->isWorkday() || !$workdaysOnly) $daysLeft--;
		}
		return $this;
	}


	public function addHours(int $hours, $workhoursOnly = true) : self {
		if($workhoursOnly && !$this->isWorkhour()) {
			$this->addHoursInternal(1)->setTime(8, 0);
		}
		return $this->addHoursInternal($hours, $workhoursOnly);
	}

	private function addHoursInternal(int $hours, $workhoursOnly = true) : self {
		$hoursLeft = $hours;
		$oneHourInterval = new \DateInterval('PT1H');
		while($hoursLeft > 0) {
			$this->add($oneHourInterval);

			if($workhoursOnly && $this->isWorkhour() || !$workhoursOnly) {
				$hoursLeft--;
			}
		}
		return $this;
	}
}