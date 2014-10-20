<?php 
/**
 * This file is part of easytrack
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
 
	session_start();
	include_once '../navigationTracker.php';
	include_once '../renderTracker.php';
	
	$host = 'localhost';
	$dbname = 'test';
	$user = 'root';
	$password = '';
	
	$nav = new navigationTracker(); // Yes, this page is tracked too =)
	$pdo = new PDO('mysql:host='.$host.';dbname='.$dbname.';charset=utf8', $user, $password);
	
	/**
	 * For render tracker data, you have 2 choices, 
	 * use pdo object and use the renderTracker for database query, 
	 * or use your favorite ORM like Doctrine and use $render->setData($array) 
	 */
	$render = new renderTracker($pdo);
	$render->setTable('navigation_tracker')
		->addFilter(array('get'=>'page'));	
	
	/**
	 * Here for the form
	 */
	if ($_POST){
		$_SESSION['post'] = $_POST;
	}
	else{
		$_POST = (isset($_SESSION['post'])) ? $_SESSION['post'] : $_POST;
	}
	
	$idcheck = $id1 = $id2 = '';
	if (isset($_POST['id-check'])){
		$idcheck = " checked";
		$id1 = $_POST['id1'];
		$id2 = $_POST['id2'];
		$render->where("master.id BETWEEN :id1 AND :id2")
			->bind(':id1', $id1)
			->bind(':id2', $id2);
	}
	
	$ipcheck = $ip = '';
	if (isset($_POST['ip-check'])){
		$ipcheck = " checked";
		$ip = $_POST['ip'];
		$render->where("ip.ip LIKE :ip")
			->bind(':ip',$ip);
	} 
	
	$datecheck = '';
	$date1 = $date2 = date("Y-m-j H:i:s");
	if (isset($_POST['date-check'])){
		$datecheck = " checked";
		$date1 = $_POST['date1'];
		$date2 = $_POST['date2'];
		$render->where("date BETWEEN :date1 AND :date2")
			->bind(':date1',$date1)
			->bind(':date2',$date2);
	}
	
	$pagecheck = $page = '';
	if (isset($_POST['page-check'])){
		$pagecheck = " checked";
		$page = $_POST['page'];
		$render->where("master.page = :page")
			->bind(':page',$page);
	}
	
	$prevcheck = $prev = '';
	if (isset($_POST['prev-check'])){
		$prevcheck = " checked";
		$prev = $_POST['prev'];
		$render->where("master.prevpage = :prev")
			->bind(':prev',$prev);
	}
	
	$navicheck = $navi = '';
	if (isset($_POST['navi-check'])){
		$navicheck = " checked";
		$navi = $_POST['navi'];
		$render->where("master.navigator = :navi")
			->bind(':navi',$navi);
	}
	
	$langcheck = $lang = '';
	if (isset($_POST['lang-check'])){
		$langcheck = " checked";
		$lang = $_POST['lang'];
		$render->where("language.name = :lang")
			->bind(':lang',$lang);
	}
	
	$latencycheck = $latency1 = $latency2 = '';
	if (isset($_POST['latency-check'])){
		$latencycheck = " checked";
		$latency1 = $_POST['latency1'];
		$latency2 = $_POST['latency2'];
		$render->where("master.latency BETWEEN :latency1 AND :latency2")
			->bind(':latency1',$latency1)
			->bind(':latency2',$latency2);
	}
	
	$order = 'DESC';
	$orderBy = 'master.id';
	if (isset($_POST['order'])){
		if (($_POST['order'] == 'DESC' || $_POST['order'] == 'ASC') && renderTracker::checkColumn($_POST['orderby'])){
			$order = $_POST['order'];
			$orderBy = $_POST['orderby'];
		}
		else{
			$order = 'DESC';
			$orderBy = 'master.id';
		}
		$render->setOrder($order)->setOrderBy($orderBy);
	}
	
	/**
	 * Here the database request
	 */
	$sql = "SELECT * FROM navigation_tracker_page ORDER BY page LIMIT 100";
	$req = $pdo->prepare($sql);
	$req->execute();
	$pageList = $req->fetchAll(PDO::FETCH_ASSOC);
	
	$sql = "SELECT * FROM navigation_tracker_navigator ORDER BY name LIMIT 100";
	$req = $pdo->prepare($sql);
	$req->execute();
	$naviList = $req->fetchAll(PDO::FETCH_ASSOC);
	
	$render->requestDatabase(); // Only work if PDO is defined
?>
<!doctype html>
<html>
	<head>
		<title>Exemple of renderTracker use</title>
		<style>
			/* Very basic CSS... */
			.easytrack thead{background-color: black; color: white;}
			th, td{padding: 5px;}
			.pair{background-color: lightgray;}
			.odd{background-color: gray;}
			.easytrack_page a{background-color: #eee; display: inline-block; padding: 5px;}
			.easytrack_page .active{background-color: #ccc;}
			.easytrack td{height: 45px;}
			label{display: inline-block; width: 200px;}
			a{color: inherit; text-decoration: none; display: inline-block; width: 100%; min-height: 15px;}
			a:hover{text-decoration: underline;}
			.easytrack-date a{width: auto;}
			/* .easytrack-button.active{display: none;} /* Display or not "begin/prev/next/last" active button */
		</style>
	</head>
	<body>
		<form method="post" action="render.php" id="track-form">
			<fieldset>
				<legend>Filter</legend>
				<div><input type="checkbox" id="id-check" name="id-check"<?php echo $idcheck; ?>><label for="id-check">ID</label><input type="number" name="id1" id="id1" min="1" value="<?php echo $id1; ?>"><input type="number" name="id2" id="id2" min="1" value="<?php echo $id2; ?>"></div>
				<div><input type="checkbox" id="ip-check" name="ip-check"<?php echo $ipcheck; ?>><label for="ip-check">IP Search</label><input type="text" placeholder="<?php echo $_SERVER["REMOTE_ADDR"]; ?>" name="ip" id="ip" value="<?php echo $ip; ?>"></div>
				<div><input type="checkbox" id="date-check" name="date-check"<?php echo $datecheck; ?>><label for="date-check">Date</label><input type="datetime" name="date1" id="date1" value="<?php echo $date1; ?>"><input type="datetime" name="date2" id="date2" value="<?php echo $date2; ?>"></div>
				<div><input type="checkbox" id="page-check" name="page-check"<?php echo $pagecheck; ?>><label for="page-check">Page</label><select id="page" name="page">
					<?php foreach($pageList as $value){
						$selected = ($page == $value['id']) ? ' selected' : '';
						echo '<option'.$selected.' value="'.$value['id'].'">'.$value['page'].'</option>';
					} ?>
				</select></div>
				<div><input type="checkbox" id="prevpage-check" name="prev-check"<?php echo $prevcheck; ?>><label for="prevpage-check">Previous page</label><select id="prevpage" name="prev">
					<?php foreach($pageList as $value){
						$selected = ($prev == $value['id']) ? ' selected' : '';
						echo '<option'.$selected.' value="'.$value['id'].'">'.$value['page'].'</option>';
					} ?>
				</select></div>
				<div><input type="checkbox" id="navigator-check" name="navi-check"<?php echo $navicheck; ?>><label for="navigator-check">Navigator</label><select id="navigator" name="navi">
					<?php foreach($naviList as $value){
						$selected = ($navi == $value['id']) ? ' selected' : '';
						echo '<option'.$selected.' value="'.$value['id'].'">'.$value['name'].'</option>';
					} ?>
				</select></div>
				<div><input type="checkbox" id="lang-check" name="lang-check"<?php echo $langcheck; ?>><label for="lang-check">Language</label><input type="text" placeholder="fr" maxlength="2" name="lang" id="lang" value="<?php echo $lang; ?>"></div>
				<div><input type="checkbox" id="latency-check" name="latency-check"<?php echo $latencycheck; ?>><label for="latency-check">Latency</label><input type="number" name="latency1" id="latency1" step="any" min="0" value="<?php echo $latency1; ?>"><input type="number" name="latency2" id="latency2"  step="any" min="0" value="<?php echo $latency2; ?>"></div>
				<div><input type="submit" name="send" id="send" value="Filter"></div>
			</fieldset>
			<input type="hidden" id="orderby" name="orderby" value="<?php echo $orderBy; ?>">
			<input type="hidden" id="order" name="order" value="<?php echo $order; ?>">
		</form>
		<?php 
			echo '<p>Found '.$render->getCount().' Results</p>'.
				$render->renderData().
				$render->addFilter(array( // Default value is &nbsp; don't care if you use background
					'page-text' => array(
						'first' => 'Begin',
						'prev' => '<<',
						'next' => '>>',
						'last' => 'End'
					)))
					->renderPage('page'); // Display page selection. That say to display 'id' in $_GET['page']
		?>
		<p><a href="index.php">Just a page</a></p>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script>
			// Each value from table are clickable, and interact with the filter form!
			$('.easytrack td a').click(function(){
				var link = $(this).attr('href');
				var id = $(this).html();

				var regex = new RegExp(/year|month|day|hour|minute|second/);
				if(regex.test(link)){
					$('#date-check').prop('checked',true);
					var text = $(this).parent().text();
					var fullDate = text.substr(0,10) + ' ' + text.substr(-8,8);
					fullDate = fullDate.split(' ');
					var dateList = fullDate[0].split('-');
					var timeList = fullDate[1].split(':');
					var year = dateList[0];
					var beginMonth = '01';
					var endMonth = '12';
					var beginDay = '01';
					var endDay = '31';
					var beginHour = '00';
					var endHour = '23';
					var beginMinute = '00';
					var endMinute = '59';
					var beginSecond = '00';
					var endSecond = '59';
					switch(link){
						case '#second': beginSecond = endSecond = timeList[2];
						case '#minute': beginMinute = endMinute = timeList[1];
						case '#hour': beginHour = endHour = timeList[0];
						case '#day': beginDay = endDay = dateList[2];
						case '#month': beginMonth = endMonth = dateList[1];
					}
					$('#date1').val(year  +  '-' + beginMonth + '-' + beginDay + ' ' + beginHour + ':' + beginMinute + ':' + beginSecond);
					$('#date2').val(year  +  '-' + endMonth + '-' + endDay + ' ' + endHour + ':' + endMinute + ':' + endSecond);
				}
				else{
					$(link+'-check').prop('checked',true);
					if(link == '#id'){
						$('#id1,#id2').val(id);
					}
					else if(link == '#latency'){
						var latency = parseFloat(id);
						var min = (latency < 0.1) ? 0 : latency - 0.1;
						$('#latency1').val(min);
						$('#latency2').val(latency + 0.1);
					}
					else if(link == '#page' || link == '#prevpage' || link == '#navigator'){
						$(link).val($(this).attr('id-data'));
					}
					else{
						$(link).val(id);
					}
				}
				setTimeout(function(){
					$('#track-form').submit();
				},400);
			});

			// can change the ORDER BY with table header
			$('.easytrack th a').click(function(){
				var text = $(this).attr('href');
				var name = text.substr(5, text.lenght);
				var order = $('#order').val();
				var orderBy = 'master.id';
				
				order = (order == 'DESC') ? 'ASC' : 'DESC';
				$('#order').val(order);
				
				switch (name){
					case 'id': orderBy = 'master.id'; break;
					case 'ip': orderBy = 'ip.ip'; break;
					case 'date': orderBy = 'master.date'; break;
					case 'page': orderBy = 'page.page'; break;
					case 'prevpage': orderBy = 'prevpage.page'; break;
					case 'navigator': orderBy = 'navigator.name'; break;
					case 'lang': orderBy = 'language.name'; break;
					case 'latency': orderBy = 'master.latency'; break;
				}
				
				$('#orderby').val(orderBy);
				setTimeout(function(){
					$('#track-form').submit();
				},400);
			});

			// check-box auto check when you edit the input/select
			$('#track-form input,#track-form select').change(function(){
				var regex = new RegExp(/-check$/);
				var id = $(this).attr('id');
				if(!regex.test(id)){
					var text = id.replace(/[0-9]/g, '');
					$('#'+text+'-check').prop('checked',true);
				}
			});
		</script>
		<?php 
			// See navigationTracker.php || exemple/index.php || exemple/tracker.php
			echo $nav->getScript('tracker.php',true);
		?>
	</body>
</html>