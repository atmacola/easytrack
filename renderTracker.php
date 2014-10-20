<?php 
/**
 * renderTracker is make to render data who come from navigationTracker
 * It is useless if you not use it with it
 * 
 * That class is useful for render nice table for display the tracker's data
 * You can use it like you want, but have a look at the exemple > render.php
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
 * @version    1.0
 */

class renderTracker
{
	private $db, $table, $index, $limit, $filter, $where, $pageLimit, $whereArray, $order, $orderBy, $bind;
	
	/**
	 * Init vars
	 * @param PDO $pdo
	 */
	public function __construct(PDO $pdo=null){
		$this->db = $pdo;
		$this->table = null;
		$this->setIndex();
		$this->limit = 10;
		$this->filter = array('render' => 'table');
		$this->data = array();
		$this->where = 1;
		$this->where(1);
		$this->orderBy = 'master.id';
		$this->order = 'DESC';
		$this->pageLimit = 10;
		$this->whereArray = array();
		$this->bind = array();
	}
	
	/**
	 * Get sql table's name
	 * @return String $table
	 */
	public function getTable(){
		return $this->table;
	}
	
	/**
	 * Get the actual page
	 * @return number
	 */
	public function getIndex(){
		return $this->index;
	}
	
	/**
	 * Get the number of row in output
	 * @return number
	 */
	public function getLimit(){
		return $this->limit;
	}
	
	/**
	 * Get the options
	 * @return Ambigous <array, multitype:string , multitype:>
	 */
	public function getFilter(){
		return $this->filter;
	}
	
	/**
	 * Get the most important thing, data
	 * @return Ambigous <multitype:, array>
	 */
	public function getData(){
		return $this->data;
	}
	
	/**
	 * Get the maximum number of page shown
	 * @return number
	 */
	public function getpageLimit(){
		return $this->pageLimit;
	}
	
	/**
	 * Set table name
	 * @param string $table
	 * @return renderTracker
	 */
	public function setTable($table){
		$this->table = (String) $table;
		return $this;
	}
	
	/**
	 * Set SQL limit
	 * @param number $limit
	 * @return renderTracker
	 */
	public function setLimit($limit){
		$this->limit = (integer) $limit;
		return $this;
	}
	
	/**
	 * Set the page shown (index * limit = sql_index)
	 * @param string $index
	 * @return renderTracker
	 */
	public function setIndex($index = null){
		if ($index) $this->index = (integer) $index;
		elseif (isset($this->filter['get'])){ 
			$key = $this->filter['get'];
			if (isset($_GET[$key])){
				$this->index = ($_GET[$key] -1) * $this->limit;
			}
		}
		else $this->index = 0;
		return $this;
	}
	
	/**
	 * Redefine filter options
	 * @param mixed $filter
	 * @return renderTracker
	 */
	public function setFilter($filter){
		$this->filter = (array) $filter;
		$this->setIndex();
		return $this;
	}

	/**
	 * Set the data
	 * @param array $data
	 * @return renderTracker
	 */
	public function setData($data){
		$this->data = (array) $data;
		return $this;
	}
	
	/**
	 * Set the maximum page link
	 * @param number $limit
	 * @return renderTracker
	 */
	public function setPageLimit($limit){
		$this->pageLimit = (integer) $limit;
		return $this;
	}
	
	/**
	 * Set the orderBy value
	 * @param string $order
	 * @return renderTracker
	 */
	public function setOrderBy($order){
		$this->orderBy = (String) $order;
		return $this;
	}
	
	/**
	 * Set order to ASC or DESC
	 * @param string $order
	 * @return renderTracker
	 */
	public function setOrder($order){
		$order = strtoupper($order);
		if ($order == 'DESC' || $order == 'ASC'){
			$this->order = (String) $order;
			return $this;
		}
		else{
			return false;
		}
	}
	
	/**
	 * Change order <ASC> <-> <DESC>
	 * @return renderTracker
	 */
	public function switchOrder(){
		$this->order = ($this->order == 'DESC') ? 'ASC' : 'DESC';
		return $this;
	}
	
	/**
	 * Add a filter option... (read manual)
	 * @param mixed $filter
	 * @return renderTracker
	 */
	public function addFilter($filter){
		$this->filter = array_merge($this->filter, (array) $filter);
		$this->setIndex();
		return $this;
	}
	
	/**
	 * Add data
	 * @param array $data
	 * @return renderTracker
	 */
	public function addData($data){
		$this->data = array_merge($this->data, (array) $data);
		return $this;
	}
	
	/**
	 * Remove a filter. Search and destroy all keys and values contained in $filter
	 * @param mixed $filter
	 * @return renderTracker
	 */
	public function removeFilter($filter){
		$filter = (array) $filter;
		foreach($filter as $v){
			unset($this->filter[array_search($v, $this->filter)],$this->filter[$v]);
		}
		$this->setIndex();
		return $this;
	}
	
	/**
	 * Remove data rows. Search and destroy all keys and values contained in $data
	 * @param unknown $data
	 * @return renderTracker
	 */
	public function removeData($data){
		$data = (array) $data;
		foreach($data as $v){
			unset($this->data[array_search($v, $this->data)],$this->data[$v]);
		}
		return $this;
	}
	
	/**
	 * Get the data with database
	 * @return renderTracker|boolean
	 */
	public function requestDatabase(){
		if ($this->db && self::checkColumn($this->orderBy)){
			$sql = "SELECT master.`id`, ip.`ip`, master.`date`, page.`page`, prevpage.`page` as prevpage, 
						navigator.`name` as navigator, language.`name` as lang, master.`latency`, 
						master.`page` as page_id, master.`prevpage` as prevpage_id, master.`navigator` as navigator_id
					FROM `".$this->table."` as master 
					LEFT JOIN `".$this->table."_ip` as ip ON ip.id = master.`ip`
					LEFT JOIN `".$this->table."_page` as page ON page.`id` = master.`page`
					LEFT JOIN `".$this->table."_page` as prevpage ON prevpage.`id` = master.`prevpage`
					LEFT JOIN `".$this->table."_navigator` as navigator ON navigator.`id` = master.`navigator`
					LEFT JOIN `".$this->table."_language` as language ON language.`id` = master.`language`
					WHERE ".$this->where." ORDER BY ".$this->orderBy." ".$this->order.", master.id DESC LIMIT ".$this->index.",".$this->limit;
			$req = $this->db->prepare($sql);
			$req->execute($this->bind);
			$this->data = $req->fetchAll(PDO::FETCH_ASSOC);
			return $this;
		}
		else return false;
	}
	
	/**
	 * Transform data into table
	 * @return boolean|renderTracker
	 */
	public function renderData(){
		if (sizeOf($this->data) > 0){
			$table = '';
			switch ($this->filter['render']){
				case 'table': default:
					$table = '<table class="easytrack"><thead><tr>';
					
					$column = array();
					
					while (list($key) = each($this->data[0])){
						if ($key != 'page_id' && $key != 'prevpage_id' && $key != 'navigator_id'){
							$table .= '<th><a href="#key-'.$key.'">'.$key.'</a></th>';
							$column[$key] = $key;
						}
					}
					$table .= '</tr></thead><tbody>';
					
					foreach($this->data as $key => $data){
						$class = ($key%2) ? 'pair' : 'odd';
						$table .= '<tr class="'.$class.'">';
						foreach ($data as $k => $value){
							if ($k != 'page_id' && $k != 'prevpage_id' && $k != 'navigator_id'){
								if ($column[$k] == 'page') $idData = ' id-data="'.$data['page_id'].'"'; 
								elseif ($column[$k] == 'prevpage') $idData = ' id-data="'.$data['prevpage_id'].'"';
								elseif ($column[$k] == 'navigator') $idData = ' id-data="'.$data['navigator_id'].'"';
								else  $idData =	'';
								
								if ($column[$k] != 'date'){
									$table .= '<td><a href="#'.$column[$k].'"'.$idData.'">'.$value.'</a></td>';
								}
								else{
									list($date,$time) = explode(' ',$value);
									list($year,$month,$day) = explode('-',$date);
									list($hour,$minute,$second) = explode(':',$time);
									$table .= '<td class="easytrack-date"><a href="#year">'.$year.'</a>-<a href="#month">'.$month.'</a>-<a href="#day">'.$day.'</a> 
											<a href="#hour">'.$hour.'</a>:<a href="#minute">'.$minute.'</a>:<a href="#second">'.$second.'</a></td>';
								}
							}
						}
						$table .= '</tr>';
					}
					$table .= '</tbody></table>';
					break;
			}
			return $table;
		}
		else return false;
		
		return $this;
	}
	
	/**
	 * Here the page engine... render page system
	 * @param string $postKey
	 * @return boolean
	 */
	public function renderPage($postKey){
		if ($this->db){
			$render = '';
			$text = array();
			$text['first'] = $text['prev'] = $text['next'] = $text['last'] = '&nbsp;';
			if (isset($this->filter['page-text'])){
				$text['first'] = (isset($this->filter['page-text']['first'])) ? $this->filter['page-text']['first'] : '&nbsp;';
				$text['prev'] = (isset($this->filter['page-text']['prev'])) ? $this->filter['page-text']['prev'] : '&nbsp;';
				$text['next'] = (isset($this->filter['page-text']['next'])) ? $this->filter['page-text']['next'] : '&nbsp;';
				$text['last'] = (isset($this->filter['page-text']['last'])) ? $this->filter['page-text']['last'] : '&nbsp;';
			}
			$number = $this->getCount();
			
			$actual = (isset($_GET[$postKey])) ? (integer) $_GET[$postKey] : 1;
			$before = ceil(($this->pageLimit -1) / 2);
			$pageCount = ceil($number / $this->limit);
			$prevPage = ($actual -1 > 1) ? $actual -1 : 1;
			$nextPage = ($actual +1 <= $pageCount) ? $actual +1 : $pageCount;
			$select = array();
			$select['first'] = ($actual == 1) ? 'class="easytrack-button active"' : 'class="easytrack-button"';
			$select['prev'] = ($actual == 1) ? 'class="easytrack-button active"' : 'class="easytrack-button"';
			$select['next'] = ($actual == $pageCount) ? 'class="easytrack-button active"' : 'class="easytrack-button"';
			$select['last'] = ($actual == $pageCount) ? 'class="easytrack-button active"' : 'class="easytrack-button"';
			$j = 1;
			
			$render = '<table class="easytrack_page"><tbody><tr>
					<td class="easytrack-first"><a href="?'.$postKey.'=1"'.$select['first'].'>'.$text['first'].'</a></td>
					<td class="easytrack-prev"><a href="?'.$postKey.'='.$prevPage.'"'.$select['prev'].'>'.$text['prev'].'</a></td>';
			$j = 1;
			$page = 1;
			$cpt = 1;
			$digit = '';
			while ($cpt <= $this->pageLimit){
				$page = $actual - $before + $j;
				if ($page >= 1){
					if ($page <= $pageCount){
						$digit = 	($page < 10 && ($number / $this->limit) > 100) ? '00' :
						($page < 10 && ($number / $this->limit) > 10) ? '0' : '';
						$attr = ($page == $actual) ? ' href="#" class="active"' : 'href="?'.$postKey.'='.$page.'"';
						$render .= '<td><a '.$attr.'>'.$digit.$page.'</a></td>';
					}
					$cpt++;
				}
				$j++;
			}
			$render .= 	'<td class="easytrack-next"><a href="?'.$postKey.'='.$nextPage.'"'.$select['next'].'>'.$text['next'].'</a></td>
					 <td class="easytrack-last"><a href="?'.$postKey.'='.$pageCount.'"'.$select['last'].'>'.$text['last'].'</a></td>
				  </tr></tbody></table>';
			return $render;
		}
		else return false;
	}
	
	/**
	 * Return number of row in table
	 * @param string $column
	 * @return boolean|number
	 */
	public function getCount(){
		if (!isset($this->count) && $this->db){
			$where = $this->where;
			$sql = "SELECT COUNT(*) as 'count' FROM `".$this->table."` as master 
					LEFT JOIN `".$this->table."_ip` as ip ON ip.id = master.`ip`
					LEFT JOIN `".$this->table."_page` as page ON page.`id` = master.`page`
					LEFT JOIN `".$this->table."_page` as prevpage ON prevpage.`id` = master.`prevpage`
					LEFT JOIN `".$this->table."_navigator` as navigator ON navigator.`id` = master.`navigator`
					LEFT JOIN `".$this->table."_language` as language ON language.`id` = master.`language` WHERE ".$where." LIMIT 1";
			$req = $this->db->prepare($sql);
			$req->execute($this->bind);
			$number = $req->fetch(PDO::FETCH_ASSOC);
			$this->count = (integer) $number['count'];
		}
		elseif(!$this->db){
			return false;
		}
		return $this->count;
	}
	
	/**
	 * Add a where clause
	 * @param mixed $where
	 * @return renderTracker
	 */
	public function where($where = 1){
		if ($where == 1) return $this;
		$where = (array) $where;
		$this->whereArray = array_merge($this->whereArray, $where);
		$first = true;
		foreach($this->whereArray as $value){
			if ($first){
				$this->where = $value;
				$first = false;
			}
			else{
				$this->where .= ' AND '.$value;
			}
		}
		
		return $this;
	}
	
	public function bind($paramName, $value){
		$this->bind = array_merge($this->bind, array($paramName => $value));
		return $this;
	}
	
	/**
	 * Check if a column name is correct
	 * @param string $name
	 * @return boolean
	 */
	public static function checkColumn($name){
		$regex = '/^(master|ip|date|page|prevpage|navigator|language|latency)\.id$|^(navigator|language)\.name$|^master\.(ip|date|page|navigator|language|prevpage|latency)$|^ip\.ip$|^language\.language$|^navigator\.navigator$|^(page|prevpage)\.page$/';
		return (preg_match($regex, $name)) ? true : false;
	}
}