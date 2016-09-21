<?PHP
namespace Oppned;

###################################################
##  Class: General\Message
##  Version: 0.4 (PHP5)
##  Date: 17th of March 2014
##  Copyright 2005, 2011, 2014: Ingvar Aasen, Steinkjer, Norway
##  Code: Ingvar Aasen
###################################################
/*
CSS FORMATTING:
div.message {
	color: black;
	font-style: normal;
	font-size: 1.2em;
}
div.message.ok {
	background-color: lightgreen;
}
div.message.warning {
	background-color: #ffff77;
}
div.message.error {
	background-color: #ff0000;
}
div.message.debug {
	background-color: #8888ff;
}
div.message.debugerror {
	background-color: #ffa;
}
*/

use Zend\Session\Container;
use Zend\Stdlib\SplQueue;

class Message {
	
	private static $messages;
	private static $debugmode = true;
	private static $container = 'Message';
	private static $namespace = 'default';
	
	private static $css_main = 'message';
	private static $css = Array(
		1 => 'ok',
		2 => 'warning',
		3 => 'error',
		4 => 'debug',
		5 => 'debugerror',
		6 => 'debug'
	);
		
	/**
	 * 
	 * Push a new message into the stack.
	 * Type suggestion:
	 * 1 - OK
	 * 2 - Warning
	 * 3 - Error
	 * 4 - Debug
	 * 5 - Debug error
	 * 6 - User access
	 * 
	 * @param int $type
	 * @param String $text
	 */
	static function create($type, $text) {
		$container = new Container(self::$container);
		if (!isset($container->{self::$namespace}) || !($container->{self::$namespace} instanceof SplQueue)) {
			$container->setExpirationHops(2);
			$container->{self::$namespace} = new SplQueue();
				
		}
		$container->{self::$namespace}->push(array($type, substr($text, 0, 1000)));
	}
	
	/**
	 * 
	 * Returns the messages in a list of DIV's following the names given in the static $css_classes
	 * @param unknown_type $max_level
	 */
	static function returnHtml($max_level = false) {
		$container = new Container(self::$container);
		if(!isset($container->{self::$namespace})) return false;
		$n = $container->{self::$namespace};
		$lines = count($n);
				
		$answer = '';
		for($i = 0; $i < $lines; $i++) {
			$current = $n->shift();
			$css = self::$css[$current[0]];
			
			if($max_level == false || $current[0] <= $max_level) {
				$answer .= '<div class="'.self::$css_main.' '.$css.'"> '.$current[1].'</div>' . "\n";
			}
			else {
				$n->push($current);
			}
		}
		
		if(strlen($answer)) $answer = '<div class=messagebox>' . $answer . '</div>';
		return $answer;
	}
	
	static function returnText($max_level = false) {
		$container = new Container(self::$container);
		if(!isset($container->{self::$namespace})) return false;
		$n = $container->{self::$namespace};
		$lines = count($n);
				
		$answer = '';
		for($i = 0; $i < $lines; $i++) {
			$current = $n->shift();
			$css = self::$css[$current[0]];
			
			if($max_level == false || $current[0] <= $max_level) {
				$answer .= $css.' - '.$current[1]."\n";
			}
			else {
				$n->push($current);
			}
		}
		
		return $answer;
	}
	
	static function returnArray($max_level = false) {
		$container = new Container(self::$container);
		if(!isset($container->{self::$namespace})) return array();
		
		$msg = array();
		$n = $container->{self::$namespace};
		$lines = count($n);
		
		for($i = 0; $i < $lines; $i++) {
			$current = $n->shift();
				
			if($max_level == false || $current[0] <= $max_level) {
				$answer[] = array('level' => $current[0], 'message' => $current[1], 'css' => self::$css[$current[0]]);
			}
			else {
				$n->push($current);
			}
		}
		return $answer;
	}
	
	static function count($max_level = false) {
		$container = new Container(self::$container);
		if(!isset($container->{self::$namespace})) return 0;
		$n = $container->{self::$namespace};
		$count = 0;
		foreach($n AS $l) {
			if($max_level == false || $l[0] <= $max_level) $count++;
		}
		return $count;
	}
	
	
	
	/*
	function log($message) {
		$data = strftime('%D %T').' - ';
		$data .= $message."\n";
		$filehandle = fopen("/Applications/XAMPP/xamppfiles/htdocs/oppned/phplog.html", 'a');
		fwrite($filehandle, $data);
		fclose($filehandle);
	}
	*/
}
## End of file
?>