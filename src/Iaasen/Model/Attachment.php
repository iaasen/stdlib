<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 11.11.2015
 * Time: 19.18
 */

namespace Iaasen\Model;

/**
 * Class ClaimAttachment
 * @package Claim\Model
 * @property int $id
 * @property string $original_filename
 * @property string $saved_filename
 * @property string $url
 * @property string $type
 * @property int $size
 * @property int $error
 * @property string $tmp_name
 *
 */
class Attachment extends AbstractModel
{
	/** @var int */
	protected $id;

	/** @var string */
	protected $original_filename;

	/** @var string */
	protected $saved_filename;

	/** @var  string */
	protected $url;

	/** @var string */
	protected $type;

	/** @var int */
	protected $size;

	/** @var int */
	protected $error;

	/** @var string */
	protected $tmp_name;

	/** @var string */
	protected $full_path;

	/**
	 * ClaimAttachment constructor.
	 * Config options:
	 *  - file_location
	 *  - attachment_folder
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		parent::__construct();
		if(count($config)) {
			$this->full_path = realpath($config['file_location'] . '/' . $config['attachment_folder']);
		}
	}

	public function __set($name, $value) {
		if($name == 'file') {
			foreach($value AS $key => $attr) {
				if($key == 'name') parent::__set('original_filename', $attr);
				else parent::__set($key, $attr);
			}
		}
		else {
			parent::__set($name, $value);
		}
	}

	public function databaseSaveArray() {
		$data = parent::databaseSaveArray();
		unset($data['tmp_name']);
		unset($data['error']);
		unset($data['full_path']);
		return $data;
	}

//	public function createAccessKey() {
//		$this->access_key = bin2hex(openssl_random_pseudo_bytes(16));
//		return $this->access_key;
//	}

	public function getHumanFilesize($precision = 2) {
		$size = $this->size;
		$units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
		$step = 1024;
		$i = 0;
		while (($size / $step) > 0.9) {
			$size = $size / $step;
			$i++;
		}
		return round($size, $precision). ' ' . $units[$i];
	}

	public function getFilenameWithFullPath() {
		return $this->full_path . '/' . $this->saved_filename;
	}
}