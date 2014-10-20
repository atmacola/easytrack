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
	
	// Declare this on the top for have a nice timestamp for the loading time
	$nav = new navigationTracker();
?>
<!doctype html>
<html>
	<head>
		<title>Exemple of navigationTracker use</title>
	</head>
	<body>
		<p>Lorem Ipsum... <br>
		<a href="render.php">Get last 10 connexions</a></p>
		<?php echo $nav->getScript('tracker.php'); // add this before end of body ?>
	</body>
</html>