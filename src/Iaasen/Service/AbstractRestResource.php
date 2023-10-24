<?php
/**
 * User: ingvar.aasen
 * Date: 30.06.2017
 */

namespace Iaasen\Service;


use Iaasen\WithOptions;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\Http\Header\Authorization;

abstract class AbstractRestResource extends AbstractResourceListener {

	/**
	 * Returns an array with start and end times in unix timestamp
	 * Result is based on post/query parameters: year, month, start, end
	 * year and month are integers
	 * start and end are unix timestamps
	 * @return int[]
	 */
	protected function calculateStartEnd() : array {
		$event = $this->getEvent();
		$year = $event->getRouteParam('year', $event->getQueryParam('year', date('Y')));
		$month = $event->getRouteParam('month', $event->getQueryParam('month', date('m')));
		$start = $event->getQueryParam('start');
		$end = $event->getQueryParam('end');

		if($start && !is_numeric($start)) $start = strtotime($start);
		if($end && !is_numeric($end)) $end = strtotime($end);

		if($start === null) { // Default is beginning of current month
			$start = mktime(0, 0, 0, $month, 1, $year);
		}

		// Calculate end of this month (actually beginning of next month)
		if($month == 12) {
			$_month = 1;
			$_year = $year + 1;
		}
		else {
			$_month = $month + 1;
			$_year = $year;
		}
		$endOfThisMonth = mktime(0, 0, 0, $_month, 1, $_year);


		if($end === null) { // Default is beginning of next month
			$end = $endOfThisMonth;
		}

		// Don't go beyond current month
		if($end > $endOfThisMonth) $end = $endOfThisMonth;

		return [(int) $start, (int) $end];
	}


	/**
	 * @deprecated
	 */
	protected function requestForSingleMonth() {
		return $this->isRequestForSingleMonth();
	}


	protected function isRequestForSingleMonth() : bool {
		$event = $this->getEvent();
		$year = (bool) $event->getRouteParam('year', $event->getQueryParam('year'));
		$month = (bool) $event->getRouteParam('month', $event->getQueryParam('month'));
		return $year && $month;
	}


	/**
	 * @return int[]|null [year, month]
	 */
	protected function getRequestForSingleMonth() : ?array {
		if(!$this->isRequestForSingleMonth()) return null;
		$event = $this->getEvent();
		return [
			(int) $event->getRouteParam('year', $event->getQueryParam('year')),
			(int) $event->getRouteParam('month', $event->getQueryParam('month')),
		];
	}


	/**
	 * @param array|string|null $withString
	 * @param array $default
	 * @return array
	 */
	public static function extractWith($withString, array $default = []) : array {
		return WithOptions::extractWith($withString, $default);
	}


	protected function getAccessTokenFromRequest() : ?string {
		/** @var Authorization $authorization */
		$authorization = $this->getEvent()->getRequest()->getHeaders()->get('Authorization');
		if(!$authorization) return null;
		$matches = [];
		$success = preg_match('/^Bearer (.+)$/', trim($authorization->getFieldValue()), $matches);
		if(!$success) return null;
		return $matches[1];
	}

}
