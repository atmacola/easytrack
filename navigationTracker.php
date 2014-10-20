<?php
/**
 * navigationTracker is a single file, asynchronous tracker.
 * It is a part of Ludo's EasyTrack package.
 *
 * You want to know what your users do, when and how ?
 * It is very easy to know what the people do in your web-site
 * with the help of this tracker. For a fast use, all you need is
 * this class, add a $tracker = new navigationTracker(); on top, 
 * and a echo $nav->getScript('tracker.php'); just before end of <body> tag.
 * Don't forget to get the tracker.php from "exemple" directory, edit it
 * for a correct connexion with your database and enjoy !
 * 
 * Can work with Jquery too >> $nav->getScript('tracker.php',true);
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @category   Tracker
 * @package    atmacola/easytrack
 * @author     Ludovic Ganée <www.ludo-portfolio.fr>
 * @license    http://www.gnu.org/licenses/
 * @version    1.2
 */

class navigationTracker
{
	private $db, $ip, $table, $navigator, $buildTable, $filter, $allowHuman, $allowBot, $isHuman;
	
	/**
	 * Init navigationTracker Object
	 * You can add JSON to param for change some default value ( need json_decode() )
	 * @param stdClass $obj (optional)
	 */
	
	public function __construct(stdClass $obj = null){
		if ($obj){
			$this->beginTime =htmlspecialchars($obj->beginTime);
			$this->prevpage = htmlspecialchars($obj->prevpage);
			$this->page = htmlspecialchars($obj->page);
		}
		else{
			$this->beginTime = self::getMicrotime();
			$this->prevpage = htmlspecialchars($this->setPrev()->getPrev());
			$this->page = htmlspecialchars($this->setPage()->getPage());
		}
		
		$this->db = null;
		$this->table = 'navigationTracker';
		$this->navigator = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);
		$this->language = htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$this->ip = htmlspecialchars(self::getRealIp());
		$this->buildTable = true;
		$this->filter = $this->setFilter('disallow bot');
		$this->isHuman = self::userIsHuman();
	}
	
	/**
	 * Add PDO object for database use
	 * @param PDO $db
	 */
	public function setConnexion(PDO $db){
		$this->db = $db;
		return $this;
	}
	
	/**
	 * Get the PDO object
	 * @return Ambigous <NULL, PDO>
	 */
	public function getConnexion(){
		return $this->db;
	}
	
	/**
	 * get $page value
	 * @return Sting $page
	 */
	public function getPage(){
		return $this->page;
	}
	
	/**
	 * set Page, default value is $_SERVER['QUERY_STRING']
	 * @param string $page (optional)
	 * @return navigationTracker
	 */
	public function setPage($page = null){
		$queryString = ($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
		$this->page = (!$page) ? 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$queryString : $page;
		return $this;
	}
	
	/**
	 * get the "come from" page
	 * @return Ambigous <unknown, string>
	 */
	public function getPrev(){
		return $this->prevpage;
	}
	
	/**
	 * set the "come from" page, default is $_SERVER['HTTP_REFERER']
	 * @param string $prevpage
	 * @return navigationTracker
	 */
	public function setPrev($prevpage = null){
		$this->prevpage = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $prevpage;
		return $this;
	}
	
	/**
	 * Return table's name
	 * @return String
	 */
	public function getTable(){
		return $this->table;
	}
	
	/**
	 * Set sql table's name
	 * @param String $table
	 * @return navigationTracker
	 */
	public function setTable($table){
		$this->table = $table;
		return $this;
	}
	
	/**
	 * get the filter's name
	 * @return String $filter
	 */
	public function getFilter(){
		return $this->filter;
	}
	
	/**
	 * True if the user is not a bot
	 * @return boolean
	 */
	public static function userIsHuman(){
		return !preg_match('/(bot|spider|yahoo|netcraft|W3C_Validator)/i', $_SERVER[ "HTTP_USER_AGENT" ] );
	}
	
	/**
	 * Can be useful for bot simulation (debug).
	 * make setHuman(false) for "be" a bot
	 * @param boolean $bool
	 * @return navigationTracker
	 */
	public function setHuman($bool = true){
		$this->isHuman = $bool;
		return $this;
	}
	
	/**
	 * Filter who will be registered in database.
	 * available filter: 'allow bot', 'disallow bot', 'bot only'
	 * @param string $filter
	 * @return navigationTracker
	 */
	public function setFilter($filter = 'disallow bot'){
		switch ($filter){
			case 'allow bot': $this->allowHuman = true; $this->allowBot = true; break;
			case 'disallow bot': $this->allowHuman = true; $this->allowBot = false; break;
			case 'bot only': $this->allowHuman = false; $this->allowBot = true;  break;
			default: $filter = 'disallow bot'; $this->allowHuman = true; $this->allowBot = false;
		}
		$this->filter = $filter;
		return $this;
	}
	
	/**
	 * Add a new table if not exists (require PDO connexion)
	 * @return navigationTracker
	 */
	private function buildTable(){
		$sql = "
			CREATE TABLE IF NOT EXISTS `".$this->table."` (
			 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `ip` mediumint(8) unsigned NOT NULL,
			  `date` datetime NOT NULL,
			  `page` mediumint(8) unsigned NOT NULL,
			  `navigator` mediumint(8) unsigned NOT NULL,
			  `language` mediumint(8) unsigned NOT NULL,
			  `prevpage` mediumint(8) unsigned NOT NULL,
			  `latency` float unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
				
			CREATE TABLE IF NOT EXISTS `".$this->table."_ip` (
			  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  `ip` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;		
				
			CREATE TABLE IF NOT EXISTS `".$this->table."_language` (
			  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  `language` varchar(255) NOT NULL,
			  `name` varchar(2) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;		
				
			CREATE TABLE IF NOT EXISTS `".$this->table."_navigator` (
			  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  `navigator` varchar(255) NOT NULL,
			  `name` varchar(20) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
				
			CREATE TABLE IF NOT EXISTS `".$this->table."_page` (
			  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  `page` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		
		$this->db->exec($sql);
		
		return $this;
	}
	
	/**
	 * Disable buildTable execution
	 * Increase sql speed when the table is already created
	 * 
	 * @return navigationTracker
	 */
	public function disableBuildTable(){
		$this->buildTable = false;
		return $this;
	}
	
	/**
	 * Get a timestamp for page loading
	 * @return number
	 */
	public static function getMicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * get the difference between beginTime and now
	 * @return number
	 */
	public function getLoadingTime(){
		return (self::getMicrotime() - $this->beginTime);
	}
	
	/**
	 * Ignore proxy IP for get the true one
	 * @return String $ip
	 */
	public static function getRealIp(){
		return 
			(isset($_SERVER["HTTP_CLIENT_IP"])) ? $_SERVER["HTTP_CLIENT_IP"] :
			(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) ? $_SERVER["HTTP_X_FORWARDED_FOR"] :
			(isset($_SERVER["HTTP_X_FORWARDED"])) ? $_SERVER["HTTP_X_FORWARDED"] :
			(isset($_SERVER["HTTP_FORWARDED_FOR"])) ? $_SERVER["HTTP_FORWARDED_FOR"] :
			(isset($_SERVER["HTTP_FORWARDED"])) ? $_SERVER["HTTP_FORWARDED"] :
			$_SERVER["REMOTE_ADDR"];
	}
	
	/**
	 * Preformated script for direct use in your page
	 * @param String $url (path to tracker.php)
	 * @param boolean $jquery (optional)
	 * @return string
	 */
	public function getScript($url = "/tracker.php", $jquery = false){
		$that = $this;
		unset($that->db, $that->ip, $that->table);
		
		$query = json_encode($that);
		
		if ($jquery){
			return "
				<script>
					$.ajax({
						url : '".$url."',
						type : 'POST',
						data : 'data=".$query."'
					});
				</script>";
		}
		else return "
		<script>
			var xhr = null;
			
			if (window.XMLHttpRequest || window.ActiveXObject) {
				if (window.ActiveXObject) {
					try {
						xhr = new ActiveXObject(\"Msxml2.XMLHTTP\");
					} catch(e) {
						xhr = new ActiveXObject(\"Microsoft.XMLHTTP\");
					}
				} else {
					xhr = new XMLHttpRequest();
				}
			}
			xhr.open(\"POST\", \"".$url."\", true);
			xhr.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
			xhr.send('data=".$query."');
		</script>";
	}
	
	/**
	 * Add a new row in table (require PDO connexion)
	 * @return number|boolean
	 */
	public function addRow(){
		
		if ($this->db && (($this->allowHuman && $this->isHuman) || ($this->allowBot && !$this->isHuman))){
			if ($this->buildTable) $this->buildTable();
			
			$ip = $this->find('ip',false);
			$navigator = $this->find('navigator',true);
			$language = $this->find('language',true);
			$page = $this->getPageId(htmlspecialchars($this->page));
			$prevpage = $this->getPageId(htmlspecialchars($this->prevpage));
			
			$sql = "INSERT INTO `".$this->table."`(`id`, `ip`, `date`, `page`, `navigator`, `language`, `prevpage`, `latency`) 
					VALUES (null,
						".$ip.",
						'".date("Y-m-d H:i:s")."',
						".$page.",
						".$navigator.",
						".$language.",
						".$prevpage.",
						".$this->getLoadingTime()."
					)";
			$req = $this->db->exec($sql);
			return $req;
		}
		else{
			return false;
		}
	}
	
	/**
	 * Detect navigator's name and his version in HTTP_USER_AGENT
	 * @return string
	 */
	private function getNavigatorName(){
		$s = $this->navigator;
		$posMSIE = strpos($s, 'MSIE');
		$posChrome = strpos($s, 'Chrome');
		$posFirefox = strpos($s, 'Firefox');
		$posSafari = strpos($s, 'AppleWebKit');
		
		//IE
		if (strpos($s,'Trident/7.0') !== false) return 'IE 11.0';
		elseif ($posMSIE !== false) return 'IE '.substr($s, ($posMSIE +4),5 );
		
		//CHROME
		elseif($posChrome !== false) return 'CHROME '.substr($s, ($posChrome +7),13 );
		
		//FIREFOX
		elseif($posFirefox !== false) return 'FIREFOX '.substr($s, ($posFirefox +8),4 );
		
		//SAFARI
		elseif($posSafari !== false) return 'SAFARI '.substr($s, ($posSafari +11),4 );
		
		else return '';
	}
	
	/**
	 * Get the main language in navigator (2 first letters)
	 * @return string
	 */
	private function getLanguageName(){
		return substr($this->language, 0 , 2);
	}
	
	/**
	 * $name is the column name and the suffix of table...
	 * If there are a similar entry, return the id
	 * If not, new entry is created
	 * 
	 * @param string $name
	 * @param boolean $haveAShortName
	 * @return number
	 */
	private function find($name, $haveAShortName){
		if (isset($_SESSION['track_'.$name])) $result = $_SESSION['track_'.$name];
		else {
			// Search for this entry
			$sql = "SELECT `id` FROM `".$this->table."_".$name."` WHERE `".$name."` LIKE '".$this->{$name}."' LIMIT 1";
			$req = $this->db->prepare($sql);
			$req->execute();
			$result = $req->fetch(PDO::FETCH_ASSOC);
		
			if ($result !== false){ // Entry found
				$_SESSION['track_'.$name] = $result['id'];
				$result = $result['id'];
			}
			else{
				// New entry
				$upperName = ucfirst($name);
				$sql = ($haveAShortName) ? "INSERT INTO `".$this->table."_".$name."` (`id`, `".$name."`, `name`)
										VALUES (null,'".$this->{$name}."','".$this->{'get'.$upperName.'Name'}()."')" :
										"INSERT INTO `".$this->table."_".$name."` (`id`, `".$name."`)
										VALUES (null,'".$this->{$name}."')";
				$this->db->exec($sql);
					
				// Get the last id
				$sql = "SELECT `id` FROM `".$this->table."_".$name."` ORDER BY `id` DESC LIMIT 1";
				$req = $this->db->prepare($sql);
				$req->execute();
				$result = $req->fetch(PDO::FETCH_ASSOC);
					
				$_SESSION['track_'.$name] = $result['id'];
				$result = $result['id'];
			}
		}
		return $result;
	}
	
	/**
	 * Get the id of $url in _page table
	 * If it not exists, new row is added
	 * @param string $url
	 * @return mixed
	 */
	private function getPageId($url){
		$sql = "SELECT `id` FROM `".$this->table."_page` WHERE `page` LIKE '".$url."' LIMIT 1";
		$req = $this->db->prepare($sql);
		$req->execute();
		$result = $req->fetch(PDO::FETCH_ASSOC);
		
		if ($result === false) {
			$sql = "INSERT INTO `".$this->table."_page` (`id`, `page`) 
					VALUES (null,'".$url."')";
			$this->db->exec($sql);
			
			$sql = "SELECT `id` FROM `".$this->table."_page` ORDER BY `id` DESC LIMIT 1";
			$req = $this->db->prepare($sql);
			$req->execute();
			$result = $req->fetch(PDO::FETCH_ASSOC);
		}
		
		return $result['id'];
	}
}
