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

session_start(); // navigationTracker can use $_SESSION for large speed increase!
include_once '../navigationTracker.php';

if (isset($_POST['data'])){
	/**
	 * Edit this
	 */
	$host = 'localhost';
	$dbname = 'test';
	$user = 'root';
	$password = '';
	
	$json = json_decode($_POST['data']);
	
	$pdo = new PDO('mysql:host='.$host.';dbname='.$dbname.';charset=utf8', $user, $password);
	$nav = new navigationTracker($json);
	
	/**
	 * Nice init for register human navigation
	 */
	$nav->setConnexion($pdo)
		->setTable('navigation_tracker')
		//->setFilter('disallow bot') // Default value, can be 'allow bot' or 'bot only'
		//->disableBuildTable() // Disable table creation >> Increase speed
		->addRow();
	
	/**
	 * Useful for register bot indexation in a different table
	 */
	$nav->setTable('navigation_tracker_bot')
		->setFilter('bot only')
		//->setHuman(false) // only for debug, emulate a bot
		->addRow();
}