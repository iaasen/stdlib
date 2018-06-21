<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 30.06.2017
 * Time: 16:11
 */

namespace Iaasen\Service;


use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

abstract class AbstractRestResource extends AbstractResourceListener
{
	/**
	 * Create a resource
	 *
	 * @param  mixed $data
	 * @return ApiProblem|mixed
	 */
	public function create($data)
	{
		return new ApiProblem(405, 'The POST method has not been defined');
	}

	/**
	 * Delete a resource
	 *
	 * @param  mixed $id
	 * @return ApiProblem|mixed
	 */
	public function delete($id)
	{
		return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
	}

	/**
	 * Delete a collection, or members of a collection
	 *
	 * @param  mixed $data
	 * @return ApiProblem|mixed
	 */
	public function deleteList($data)
	{
		return new ApiProblem(405, 'The DELETE method has not been defined for collections');
	}

	/**
	 * Fetch a resource
	 *
	 * @param  mixed $id
	 * @return ApiProblem|mixed
	 */
	public function fetch($id)
	{
		return new ApiProblem(405, 'The GET method has not been defined for individual resources');
	}

	/**
	 * Fetch all or a subset of resources
	 *
	 * @param  array $params
	 * @return ApiProblem|mixed
	 */
	public function fetchAll($params = [])
	{
		return new ApiProblem(405, 'The GET method has not been defined for collections');
	}

	/**
	 * Patch (partial in-place update) a resource
	 *
	 * @param  mixed $id
	 * @param  mixed $data
	 * @return ApiProblem|mixed
	 */
	public function patch($id, $data)
	{
		return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
	}

	/**
	 * Patch (partial in-place update) a collection or members of a collection
	 *
	 * @param  mixed $data
	 * @return ApiProblem|mixed
	 */
	public function patchList($data)
	{
		return new ApiProblem(405, 'The PATCH method has not been defined for collections');
	}

	/**
	 * Replace a collection or members of a collection
	 *
	 * @param  mixed $data
	 * @return ApiProblem|mixed
	 */
	public function replaceList($data)
	{
		return new ApiProblem(405, 'The PUT method has not been defined for collections');
	}

	/**
	 * Update a resource
	 *
	 * @param  mixed $id
	 * @param  mixed $data
	 * @return ApiProblem|mixed
	 */
	public function update($id, $data)
	{
		return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
	}


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
		if($end === null) { // Default is beginning of next month
			if($month == 12) {
				$_month = 1;
				$_year = $year + 1;
			}
			else {
				$_month = $month + 1;
				$_year = $year;
			}
			$end = mktime(0, 0, 0, $_month, 1, $_year);
			if($end > time()) $end = time();
		}
		return [$start, $end];
	}

	protected function requestForSingleMonth() {
		$event = $this->getEvent();
		$year = (bool) $event->getRouteParam('year', $event->getQueryParam('year'));
		$month = (bool) $event->getRouteParam('month', $event->getQueryParam('month'));
		return $year && $month;
	}

}