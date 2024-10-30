<?php
/*
Plugin Name: Larsens Calender
Plugin URI: http://larsmachtpraktikum.stil-etage.de
Description: Larsens Calender implements a Calender to show you events with a lot of options.
Version: 1.2
Author: Lars K&ouml;ster
Author URI: http://www.larsen.de.be

/*  Copyright 2008  Lars Köster  (email : LarsHoyerswerda@web.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
	Thanks to Nick Jantschke @ stil-etage http://www.stil-etage.de
*/

/*	UPDATE 1.1
	now we use the shortcode API to place the calender on a page
	we use a table instead of lists 
	
	UPDATE 1.2
	you can add and style urls
*/

//SYSTEM STUFF

function set_larsenscalender_options(){
	add_option('oldDates', 2, 'what to do with old days'); 
	add_option('saveTimeOldDates', 0, 'how long shell old dates be saved?');
	add_option('LCday', 1, 'show day or not');
	add_option('LCmonth', 2, '1= show day as int, 2= show day as string, 3 show day as short string');
	add_option('LCyear', 1, 'show year or not');
	add_option('LCtime', 2, '2= hh:mm, 1= hhuhr');
	add_option('LChowmany', 0, 'how many events should be shown');
	add_option('LCinternational', -1, 'international format');
	add_option('LCbreak', " ", 'the way the event element should be sperated');
	add_option('LCorder', 1, 'newest bottom ==1 or newest top == 2');
}

function unset_larsenscalender_options(){
	delete_option('oldDates');
	delete_option('saveTimeOldDates');
	delete_option('LCday');
	delete_option('LCmonth');
	delete_option('LCyear');
	delete_option('LCtime');
	delete_option('LChowmmany');
	delete_option('LCinternational');
	delete_option('LCbreak');
	delete_option('LCorder');
}

register_activation_hook(__FILE__, 'set_larsenscalender_options');
register_deactivation_hook(__FILE__, 'unset_larsenscalender_options');

add_action('wp_head', 'LCstyles');

load_plugin_textdomain('laca','wp-content/plugins/larsens-calender/');

function LCstyles() {
	$pfad = get_option('siteurl')."/wp-content/plugins/larsens-calender/";
	$style.= "<link rel=\"stylesheet\" href=\"".$pfad."larsenscalender.css\" type=\"text/css\" media=\"screen\" />";
	print($style);
}

function modify_menu(){	
	add_menu_page(__("Kalender", "laca"),  __("Kalender", "laca") ,'manage_options',__FILE__,'start');
	add_submenu_page(__FILE__, __("Kalender", "laca"), __("Eintr&auml;ge hinzuf&uuml;gen", "laca"), 'manage_options', 'datei2' , 'addDate');
	add_submenu_page(__FILE__, __("Kalender", "laca"), __("Eintr&auml;ge bearbeiten", "laca"), 'manage_options', 'datei3' , 'editDate');
	add_submenu_page(__FILE__, __("Kalender", "laca"), __("Eintr&auml;ge l&ouml;schen", "laca"), 'manage_options', 'datei4' , 'removeDate');
		add_submenu_page(__FILE__, __("Kalender", "laca"), __("Einstellungen", "laca"), 'manage_options', 'datei5' , 'larsenscalenderOptions');
}

add_action('admin_menu', 'modify_menu');				
//END SYSTEM STUFF

function larsensCalender(){
	return echolarsensCalender(get_option('LCday'), get_option('LCmonth'), get_option('LCyear'), get_option('LCtime'), get_option('LChowmany'), get_option('LCorder'), get_option('LCinternational'));
}

function start(){
	echo"<div class=\"wrap\"><h2>Larsens Kalender</h2><h4>Version 1.2</h4>";
	global $wpdb;
	//checking whether wp_larsencalender is already set in database	
	$isThere = $wpdb->query("SHOW TABLES LIKE 'wp_larsenscalender'");
	if($isThere=='0'){																//if table does not exists			 
																					//create table in database
				$sql="CREATE TABLE wp_larsenscalender (
										    ID     			INT PRIMARY KEY,
    										name  			VARCHAR(100),
    										description 	TEXT,
    										day  			INT,
    										month 			INT,
											year			INT,
											hour			INT,
											minute			INT
													    );
				     ";
				
					 
			   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			   dbDelta($sql);
	}
	$LCUpdate12=$wpdb->query("ALTER TABLE wp_larsenscalender ADD url TEXT");	
	if( (get_option('oldDates')==1) || (get_option('oldDates')==3) ){
		killOldDates();
	}
	?>
    <div class="wrap">
	<table class="form-table"><tbody><tr><th scope="row" valign="top"><?php _e('Zur Zeit', 'laca');
	$countDates=countDates();
	if ( $countDates[0] > 1 ){
		_e(' befinden','laca');
	}else{
		_e(' befindet', 'laca');
	}
	_e(' sich im Kalender:', 'laca');
	?></th><td>
	<?php
		if ($countDates[0] == 0){
			echo "<strong>"; _e('keine', 'laca'); echo"</strong>"; _e(' Eintr&auml;ge', 'laca');
		}else{
			if  ( $countDates[0] == 1 ){
				echo "<strong>"; _e('ein', 'laca'); echo"</strong>"; _e(' Eintrag', 'laca');
			}else{
				echo "<strong>".$countDates[0] ."</strong>"; _e(' Eintr&auml;ge', 'laca');
			}
		}
		if ( (get_option('oldDates')==2) || (get_option('oldDates')==3) ){
			if ($countDates[1] == 1){
				if ($countDates[0] == 1){
					_e(' der nicht angezeigt wird, da er in der Vergangenheit liegt', 'laca');
				}else{
					_e(' wovon <strong>ein</strong> vergangener Termin nicht mehr angezeigt wird', 'laca');
				}
			}else{
				if ($countDates[1] > 1){
					_e(' wovon', 'laca'); echo" <strong>". $countDates[1] ."</strong>";
					_e (' vergangene Termine nicht mehr angezeigt werden', 'laca');
				}
			}
		}
	?>
    </td></tr><tr><th scope="row" valign="top"><?php _e('Die Termine:', 'laca');?></th><td>
	<?php
	
	if (get_option('LCorder')==1 ){
		$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender
									   ORDER BY year ASC, month ASC, day ASC, hour ASC, minute ASC");
	}else{
		$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender
									   ORDER BY year DESC, month DESC, day DESC, hour DESC, minute DESC");
	}
    
	foreach ($termine as $termin){
		if ( !(get_option('oldDates')==2) ){
			if ( get_option('LCinternational') == -1){
				echoDate(get_option('LCday'), get_option('LCmonth'), get_option('LCyear'), get_option('LCtime'), $termin);
			}else{
				echoDateInternational($termin, get_option('LCinternational'));
			}
			echo "<br/>";
		}else{
			if (!isOld($termin)){
				if ( get_option('LCinternational') == -1){
					echoDate(get_option('LCday'), get_option('LCmonth'), get_option('LCyear'), get_option('LCtime'), $termin);
				}else{
					echoDateInternational($termin, get_option('LCinternational'));
				}
				echo "<br/>";
			}	
		}
	}
	?>
    </td></tr>
    <?php
    if ( $countDates[1] > 0){
	echo"<tr><th scope=\"row\" valign=\"top\">"; _e('Vergangene Termine, die nicht mehr angezeigt werden:', 'laca');
	echo "</th><td>";
	foreach ($termine as $termin){
		if (isOld($termin)){
			echoDate(get_option('LCday'), get_option('LCmonth'), get_option('LCyear'), get_option('LCtime'), $termin);
		}
	}
	echo"</td></tr>";
    }
	?>
	</tbody></table>
	</div>
	<?php
}

//GENERAL FUNCTIONS
//how to print one event, PARAMETER must be a vector of all event-parameters (from database)
//here you can edit how to print out events in the admin menue

function countDates(){
	global $wpdb;
	$wholeNr = 0;
	$oldNr	= 0;
	$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender ORDER BY year ASC, month ASC, day ASC, hour ASC, minute ASC");
	foreach ($termine as $termin){
		$wholeNr++;
		if(get_option('oldDates') == 2){
			if(isOld($termin)){
				$oldNr++;
			}
		}
	}
	return array ( $wholeNr, $oldNr );
}

function echoDateInternational($termin, $international){
	$break = get_option('LCbreak');
	if ( $international==1){
		echo convertMonthToLongStringE($termin->month)." ".$termin->day;
		echoDateDayExtra($termin);		
		echo ", ". $termin->year;
		echo $break;
		echoTimeAndNameInternational($termin);
	}else{
		if( $international ==2) {
			echo $termin->day;
			echoDateDayExtra($termin);
			echo " ". convertMonthToLongStringE($termin->month). " ". $termin->year;
			echo $break;
			echoTimeAndNameInternational($termin);
		}else{
			if( $international ==3){
				echo $termin->day ." ". convertMonthToShortStringE($termin->month) ." ". $termin->year;
				echo $break;
				echoTimeAndNameInternational($termin);
			}
		}
	}
}

function echoDateDayExtra($termin){
		if( ($termin->day==1) ||  ($termin->day==21)  || ( $termin->day == 31) ){
			echo "st";
		}else{
			if ( ($termin->day == 2) || ( $termin->day==22 ) ){
				echo "nd";
			}else{
				if ( ($termin->day == 3 ) || ( $termin->day == 23 ) ){
					echo "rd";
				}else{
					echo "th";
				}
			}
		}
}

function echoTimeAndNameInternational($termin){
	$break = get_option('LCbreak');
	if ($termin->hour <=12){
		echo $termin->hour.".";
	}else{
		echo $termin->hour%12 .".";
	}
	if ( $termin->minute < 10) {echo "0";}
	echo $termin->minute;
	if ($termin->hour <=12){
		echo " am ";
	}else{
		echo " pm ";
	}
	echo $break;
	echo $termin->name;
}

function echoDate ($day, $month, $year, $time, $termin){
		$break = get_option('LCbreak');
		if ( $day == 1 ){
			if ( $termin->day < 10) {echo "0";}
			echo $termin->day.".";
			if ( $month >= 2 ){ echo " ";}
	 	}
		if ( $month == 1){
			if ( $termin->month < 10 ) { echo "0";}
			echo $termin->month.".";
		}else{
			if ( $month == 2){
				    echo convertMonthToLongString($termin->month)." ";
			}else{
				if ( $month == 3){
					echo convertMonthToShortString($termin->month)." ";
				}
			}
		}
		if ( $year == 1 ){
			echo $termin->year." ";
		}
		if ( ($day==1) || ($month == 1) || (year == 1) ){
			echo $break;		
		}
		if ( $time == 1){
			if ( $termin->hour < 10) {echo "0";}
			echo $termin->hour." Uhr";
		}else{
			if ( $time ==2){
				if ( $termin->hour < 10) {echo "0";}
				echo $termin->hour.":";
				if ( $termin->minute < 10) {echo "0";}
				echo $termin->minute." Uhr ";
			}
		}
		if ( $time > 0 ){
			echo $break;
		}
		echo " ".$termin->name;
}
function echoDateOneLine ($termin){
		echo " ".$termin->day.".".$termin->month.".".$termin->year.", ". $termin->hour.":";
		if($termin->minute < 10){ echo "0"; }
		echo $termin->minute."Uhr: ".$termin->name." ";
}

function isOld($termin){
	$extraTime = get_option('saveTimeOldDates') / 24;
	$nrEvent = $termin->year*366 +$termin->month*31 + $termin->day + $termin->hour/24 + $termin->minute/1440;
	$nrToday = date(Y)*366 + date(n)*31 + date(j) + date(G)/24 + date(i)/1440 - $extraTime;	
	return ($nrEvent <= $nrToday);
}

function isOldFront($termin){
	$nrEvent = $termin->year*365 +$termin->month*12 + $termin->day + $termin->hour/24 + $termin->minute/1440;
	$nrToday = date(Y)*365 + date(n)*12 + date(j) + date(G)/24 + date(i)/1440;
	return ($nrEvent <= $nrToday);
}

function killOldDates(){
	global $wpdb;
	$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender ORDER BY ID DESC");
	foreach ($termine as $termin) {
		if (isOld($termin)) {	
			//remove row!!!!!
			$wpdb->query("DELETE FROM wp_larsenscalender WHERE ID=$termin->ID");
		}
	}
}

//END GENERAL FUNCTIONS
// ADD DATE

function addDate(){
		?><div class="wrap"><div class="wrap"><h2><?php _e('F&uuml;gen Sie einen Eintrag hinzu', 'laca');?></h2></div><?php		
		if($_REQUEST['submit']){				// if you pressed the submit-button
			updateCalender();					// update the database
		}
		printFormular();
		?></div><?php
}

function updateCalender(){
	global $wpdb;
	$numbers = $wpdb->get_results("SELECT * FROM wp_larsenscalender ORDER BY ID DESC");
	foreach ($numbers as $number) {
		$lastID = $number->ID;
		break;
	}
	$lastID = $lastID + 1;
	$day = $_REQUEST['day'];
	$month = $_REQUEST['month'];
	$year = $_REQUEST['year'];
	$minute = $_REQUEST['minute'];
	$hour = $_REQUEST['hour'];
	$name = $_REQUEST['name'];
	$description = $_REQUEST['description'];
	$url = $_REQUEST['url'];
	
	if ( ! ($name=='')){
		echo "<br /><div class=\"wrap\"><div class=\"updated\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
		_e('Eintrag erfolgreich gespeichert.', 'laca'); echo"</div></div>";
		$wpdb->query("
		INSERT INTO wp_larsenscalender (ID, name, description, day, month, year, hour, minute, url)
		VALUES ($lastID, '$name', '$description', $day, $month, $year, $hour, $minute, '$url')");
	}else{
		echo "<br /><div class=\"wrap\"><div class=\"error\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
		_e('Der Eintrag konnte nicht hinzugef&uuml;gt werden. Sie haben keinen Titel f&uuml;r den Termin vergeben!', 'laca');
		echo "</strong></div></div>";
	}
}

function printFormular(){?>
	<div class="wrap">
    <table class="form-table"><tbody><tr><th scope="row" valign="top"><?php _e('Datum und Zeit:', 'laca');?></th><td>
    <form method="post">
    	<?php _e('Tag:', 'laca');?> <select name="day" id="day" value="<?php $day ?>">
        <?php for($i=1; $i<=31; $i++){echo "<option";
			if ( $i == date(j) ){echo " selected";}
		echo ">".$i."</option>";} ?>
    	</select>
        <?php _e('Monat', 'laca');?> <select name="month" id="month" value="<?php $month ?>">
        <?php for($i=1; $i<=12; $i++){echo "<option";
			if ( $i == date(n) ){echo " selected";}
			echo ">".$i."</option>";}?>
    	</select>
        <?php _e('Jahr:', 'laca');?> <select name="year" id="year" value="<?php $year ?>">
        <?php for($i=2008; $i<=2020; $i++){echo "<option";
			if ( $i == date(Y) ){echo " selected";}
			echo ">".$i."</option>";}?>
    	</select>
        <?php _e('Stunde:', 'laca');?><select name="hour" id="hour" value="<?php $hour ?>">
        <?php for($i=0; $i<=24; $i++){echo "<option";
			if ( $i == date(G) +1 ) {echo " selected";}
			echo ">".$i."</option>";}?>
    	</select>
        <?php _e('Minute:', 'laca');?><select name="minute" id="minute" value="<?php $minute ?>">
        <?php for($i=0; $i<=60; $i++){echo "<option>".$i."</option>";}?>
    	</select></td></tr><tr><th scope="row" valign="top"><?php _e('Titel:', 'laca');?></th><td>
	<input name="name" id="name" type="text" value="<?=$name;?>" size="60" maxlength="100"></td></tr>
    <tr><th scope="row" valign="top"><?php _e('URL (optional)', 'laca') ?></th>
    <td><input name="url" id="url" type="text" value="" size="60" maxlength="100"></td>
    </tr>
    <tr><th scope="row" valign="top"><?php _e('Eintrag hinzuf&uuml;gen', 'laca');?></th>
    <td>
        <input type="submit" value="<?php _e('hinzuf&uuml;gen', 'laca');?>" name="submit"></td></tr>
    </form>
    </tbody></table></div>
<?php }

// END ADD DATE
// REMOVE DATE
	
function removeDate(){
?><div class="wrap"><div class="wrap"><h2><?php _e('L&ouml;schen Sie ausgew&auml;hlte Termine', 'laca');?></h2></div><?php		
		if( ($_REQUEST['submitLoeschen']) && (isset($_REQUEST['loeschen']))){
			foreach ($_REQUEST['loeschen'] as $loesch){
				global $wpdb;
				$wpdb->query("DELETE FROM wp_larsenscalender WHERE ID=$loesch");
			}
			echo"<div class=\"wrap\"><br><div class=\"wrap\"><div class=\"updated\" style=\"padding-top:3px; padding-bottom:3px\"><strong>"; _e('L&ouml;schung erfolgreich!', 'laca'); echo"</strong></div></div>";
			printRemoveFormular();
		}else{
			printRemoveFormular();		
		}
	?></div><?php
}

function printRemoveFormular(){
			global $wpdb;
			if( (get_option('oldDates')==1) || (get_option('oldDates')==3) ){ killOldDates();}
			$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender ORDER BY
											 year ASC, month ASC, day ASC, hour ASC, minute ASC");
			if( ($termine) ) {
						echo "<div class=\"wrap\">";
						echo "<table class=\"form-table\">";
						echo "<tbody><tr><th scope=\"row\" valign=\"top\">"; _e('Zu l&ouml;schende Termine w&auml;hlen', 'laca');
						echo"</th><td>";
						echo "<form method=post>";
						foreach($termine as $termin){ 
									echo"<input type=\"checkbox\" name=\"loeschen[]\" value=\"".$termin->ID."\">";
									echo echoDateOneLine($termin);
									echo"<br />";
						}
						echo "</td></tr><tr><th scope=\"row\" valign=\"top\">";
						_e('Ausgew&auml;hlte Eintr&auml;ge l&ouml;schen', 'laca');
						echo"</th><td><input type=\"submit\" name=\"submitLoeschen\" value=\"";
						_e('L&ouml;schen', 'laca');
						echo "\">";
						echo "</form></td></tr></table></div>";
			}else{
						echo "<div class=\"wrap\"><H4>";
						_e('Es befinden sich zur Zeit keine Termine in der Datenbank', 'laca');
						echo"</H4></div>";
			}
}

//END REMOVE DATE
//EDIT

function editDate(){
?><div class="wrap"><div class="wrap"><h2><?php _e('Bearbeiten Sie Ihre Termine', 'laca');?></h2></div><?php		
		if( (get_option('oldDates')==1) || (get_option('oldDates')==3) ){ killOldDates();}
		if($_REQUEST['submit']){
				$id = $_REQUEST['id'];
				$day = $_REQUEST['day'];
				$month = $_REQUEST['month'];
				$year = $_REQUEST['year'];
				$minute = $_REQUEST['minute'];
				$hour = $_REQUEST['hour'];
				$name = $_REQUEST['name'];
				$description = $_REQUEST['description'];
				$url = $_REQUEST['url'];

				if ( ! ($name=='')){
					global $wpdb;
					$wpdb->query("UPDATE wp_larsenscalender SET day = $day, month = $month, year = $year, minute = $minute,
																  hour = $hour, name = '$name', description = '$description',
																  url = '$url'
									WHERE ID = $id ");
					echo "<br /><div class=\"wrap\"><div class=\"updated\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
					_e('&Auml;nderung erfolgreich gespeichert.', 'laca');
					echo"</div></div>";
				}else{
					echo "<br /><div class=\"wrap\"><div class=\"error\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
					_e('Der Eintrag konnte nicht ge&auml;ndert werden. Sie haben keinen Titel f&uuml;r den Termin vergeben! Bitte wiederholen Sie den Vorang.', 'laca');
					echo"</strong></div></div>";				}
	
		}
		printEditFormular();
	?></div><?php
}

function printEditFormular(){
	global $wpdb;
	if (($_REQUEST['choose'])&&(isset($_REQUEST['dateID']))){		
			$id = $_REQUEST['dateID'];
			$editFile = $wpdb->get_row("SELECT * FROM wp_larsenscalender WHERE ID=$id");
			?>		
		    <table class="form-table"><tbody><tr><th scope="row" valign="top"><?php _e('Datum und Zeit:', 'laca');?></th><td>
			<form method="post">
			<input type="hidden" name="id" id="id" value="<?php echo $editFile->ID;?>" />
            <?php _e('Tag:', 'laca');?><select name="day" id="day" value="<?php $day ?>">
			<?php for($i=1; $i<=31; $i++){echo "<option";
				if ( $i == $editFile->day ){echo " selected";}
			echo ">".$i."</option>";} ?>
			</select>
			<?php _e('Monat:', 'laca');?> <select name="month" id="month" value="<?php $month ?>">
			<?php for($i=1; $i<=12; $i++){echo "<option";
				if ( $i == $editFile->month ){echo " selected";}
				echo ">".$i."</option>";}?>
			</select>
			<?php _e('Jahr:', 'laca');?> <select name="year" id="year" value="<?php $year ?>">
			<?php for($i=2008; $i<=2020; $i++){echo "<option";
				if ( $i == $editFile->year ){echo " selected";}
				echo ">".$i."</option>";}?>
			</select>
			<?php _e('Stunde:', 'laca');?><select name="hour" id="hour" value="<?php $hour ?>">
			<?php for($i=0; $i<=24; $i++){echo "<option";
				if ( $i == $editFile->hour ) {echo " selected";}
				echo ">".$i."</option>";}?>
			</select>
			<?php _e('Minute:', 'laca');?>:<select name="minute" id="minute" value="<?php $minute ?>">
			<?php for($i=0; $i<=60; $i++){echo "<option";
				if ($i == $editFile->minute) { echo " selected";}
				echo ">".$i."</option>";}?>
			</select></td></tr><tr><th scope="row" valign="top"><?php _e('Titel:', 'laca');?></th><td>
			<?php $name = $editFile->name;
				  $description = $editFile->description;
				  $url = $editFile->url;?>
            <input name="name" id="name" type="text" value="<?php echo $name; ?>" size="60" maxlength="100"></td></tr>
            <tr>
                <th scope="row" valign="top"><?php _e('URL (optional)', 'laca') ?></th>
                <td><input name="url" id="url" type="text" value="<?php echo $url;?>" size="60" maxlength="100"></td>
            </tr>
            <tr><th scope="row" valign="top"><?php _e('&Auml;nderdung speichern', 'laca');?></th><td>
			<input type="submit" value="<?php _e('speichern', 'laca');?>" name="submit"></td></tr>
		</form></tbody></table>		
			<?php		
	}else{
		global $wpdb;
		$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender ORDER BY
			  						   year ASC, month ASC, day ASC, hour ASC, minute ASC");
		if ( !($termine) ){
			echo "<div class=\"wrap\"><H4>";
			_e('Es befinden sich zur Zeit keine Termine in der Datenbank', 'laca');
			echo"</H4></div>";
		}else{
			echo "<div class=\"wrap\"><table class=\"form-table\">";
			echo "<tbody><tr><th scope=\"row\" valign=\"top\">";
			_e('Termin w&auml;hlen:', 'laca');
			echo "</th>";
			echo "<td><form method=post>";
			foreach ( $termine as $termin ){
				echo "<input type=\"radio\" name=\"dateID\" value=\"".$termin->ID."\">";
				echo echoDateOneLine($termin);
				echo "<br />";
			}			
			echo "</td></tr><tr><th scope=\"row\" valign=\"top\">";
			_e('Augew&auml;hlten Eintrag bearbeiten', 'laca');
			echo "</th><td><input type=\"submit\" name=\"choose\" value=\"";
			_e('Bearbeiten', 'laca');
			echo "\" />";
			echo "</form></td>";
			echo "</tr></tbody></table></div>";	
		}
	}							
}

// END EDIT
//OPTIONS

function uninstallLC(){
	global $wpdb;
	$wpdb->query(" DROP TABLE wp_larsenscalender");
}

function larsenscalenderOptions(){
		echo "<div class=\"wrap\">";
		if ($_REQUEST['deinstall']){
			uninstallLC();
			echo "<br /><div class=\"wrap\"><div class=\"error\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
				_e('Sie haben den Kalender deinstalliert. Bitte deaktivieren Sie nun das Plugin!', 'laca');	
			echo"</div></div>";	
		}
	
		if ($_REQUEST['submitAnzeige']){
			if(   (!(((string)((int)$_REQUEST['howMany']))==(trim($_REQUEST['howMany'])))) || ( (int)$_REQUEST['howMany'] < 0 )     ){
				echo "<br /><div class=\"wrap\"><div class=\"error\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
				_e('Bitte geben Sie eine positive ganze Zahl f&uuml;r die Anzahl maximal anzuzeigender Termine ein!', 'laca');
				echo"</div></div>";	
			}else{	
			echo "<br /><div class=\"wrap\"><div class=\"updated\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
			_e('Anzeige-Optionen erfolgreich gespeichert.', 'laca');
			echo"</div></div>";
			}
		}	
		if ($_REQUEST['submit']){
			if( ((!(((string)((int)$_REQUEST['saveTimeOldDates']))==(trim($_REQUEST['saveTimeOldDates'])))) || ( (int)$_REQUEST['saveTimeOldDates'] < 0 )) && ($_REQUEST['oldDates']==3) ){
				echo "<br /><div class=\"wrap\"><div class=\"error\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
				_e('Speichern fehlgeschlagen! Bitte geben Sie eine ganze positive Zahl f&uuml;r die Extra-Zeit ein!', 'laca');
				echo"</div></div>";
			}else{
				updateLarsensCalenderOptions();
				echo "<br /><div class=\"wrap\"><div class=\"updated\" style=\"padding-top:3px; padding-bottom:3px\"><strong>";
				_e('Einstellungen erfolgreich gespeichert.', 'laca');
				echo"</div></div>";
			}
		}
		echo"<div class=\"wrap\"><h2>";
		_e('Einstellungen', 'laca');
		echo "</h2></div>";
		print optionsFormular();
		echo"<div class=\"wrap\"><h2>";
		_e('Anzeige-Optionen', 'laca');
		echo "</h2></div>";
		if ($_REQUEST['submitAnzeige']){
			updateLCO2();
		}
		print optionsFormular2();	
		echo"</div>";
		echo"<div class=\"wrap\"><h2><br />";
		_e('Deinstallieren', 'laca');
		echo"</h2></div>";
		echo"<div class=\"wrap\">";
		echo"<table class=\"form-table\"><tbody>";
		echo"<tr><th scope=\"row\" valign=\"top\">";
		_e('LarsensCalender deinstallieren', 'laca');
		echo "</th><td>";
		echo "<form method=\"post\">";
			echo "<input type=\"submit\" name=\"deinstall\" value=\"";
			_e('deinstallieren', 'laca');
			echo "\" />";
			_e('Bitte danach sofort in den plugin-Einstellungen Larsens Kalender deaktivieren', 'laca');
		echo "</form>
		</td>
		</tbody></table>
		</div>";
}

function optionsFormular(){?>
		<div class="wrap">
        <table class="form-table">
		 <tbody><tr>
			<th scope="row" valign="top">
            <?php _e('Alte Eintr&auml;ge im Kalender sollen so behandelt werden:', 'laca');?></th>
			<td>
		<form method="post">
			<input type="radio" name="oldDates" value="4"<?php if(get_option('oldDates')==4){
																	echo "checked=\"checked\"";
														  		}?>><?php _e('alte Eintr&auml;ge anzeigen', 'laca')?><br>
            <input type="radio" name="oldDates" value="1"<?php if(get_option('oldDates')==1){
																	echo "checked=\"checked\"";
														 			}?>><?php _e('alte Eintr&auml;ge automatisch l&ouml;schen', 'laca');?><br>
			<input type="radio" name="oldDates" value="2"<?php if(get_option('oldDates')==2){
																	echo "checked=\"checked\"";
														       }?> ><?php _e('alte Eintr&auml;ge nicht anzeigen', 'laca');?><br>
			<input type="radio" name="oldDates" value="3"<?php if(get_option('oldDates')==3){
																	echo "checked=\"checked\"";
															   }?>><?php _e('alte Eint&auml;ge erst nach unten angegebener Zeit l&ouml;schen:', 'laca');?></td></tr>
<tr><th scope="row" valign="top"><?php _e('Extra-Zeit in Stunden f&uuml;r vierte Option:', 'laca');?></th><td><input type="text" name="saveTimeOldDates" value="<? echo get_option('saveTimeOldDates');?> " /></td></tr>
            <?php
		echo "<tr><th scope=\"row\" valign=\"top\">";
		_e('Zur Zeit sind diese Einstellungen gesetzt:', 'laca');
		echo"</th><td>";
		if (get_option('oldDates')==4){
			_e('alte Eintr&auml;ge anzeigen', 'laca');
		}else{		
			if (get_option('oldDates')==1){
				 _e('alte Eintr&auml;ge automatisch l&ouml;schen', 'laca');
			}else{
				if(get_option('oldDates')==2){
					_e('alte Eintr&auml;ge nicht anzeigen', 'laca');
				}else{
					_e('alte Eintr&auml;ge werden noch ', 'laca');
					echo get_option('saveTimeOldDates');
					_e(' Stunden angezeigt', 'laca');
				}				
			}
			
		}
	?></td></tr><tr><th><?php _e('Einstellungen speichern', 'laca');?></th><td>
    <input type="submit" name="submit" value="<?php _e('speichern', 'laca');?>" />
		</form></td></tr></tbody></table></div><br /><?php
}

function updateLarsensCalenderOptions(){
	if ($_REQUEST['oldDates']){
			update_option('oldDates', $_REQUEST['oldDates']);
			if (!(get_option('oldDates')==3)){
				update_option('saveTimeOldDates', 0);
			}else{
				if ( $_REQUEST['saveTimeOldDates']){
					if ( !((int)$_REQUEST['saveTimeOldDates'] == 0) ){
						update_option('saveTimeOldDates', (int)$_REQUEST['saveTimeOldDates']);
					}
				}else{
					update_option('saveTimeOldDates', 0);
				}
			}
	}
	if( (get_option('oldDates')==1) || (get_option('oldDates')==3) ){
		killOldDates();
	}
}

function updateLCO2(){
	if ($_REQUEST['order']){
		update_option('LCorder', $_REQUEST['order']);
	}	
	if ($_REQUEST['break']){
		update_option('LCbreak', $_REQUEST['break']);
	}else{
		update_option('LCbreak', " ");
	}
	if ($_REQUEST['international']){
		update_option('LCinternational', $_REQUEST['international']);
	}	
	if ($_REQUEST['tag']){
		update_option('LCday', 1);
	}else{
		update_option('LCday', 0);
	}
	if ($_REQUEST['monat']){
		update_option('LCmonth', $_REQUEST['monat']);
	}
	if ($_REQUEST['jahr']){
		update_option('LCyear', 1);
	}else{
		update_option('LCyear', 0);
	}	
	if ($_REQUEST['uhrzeit']){
		update_option('LCtime', $_REQUEST['uhrzeit']);
	}
	if ($_REQUEST['howMany']){
		if ( (!((int)$_REQUEST['howMany']==0)) && ($_REQUEST['howMany']>0)){	
		update_option('LChowmany', (int)$_REQUEST['howMany']);
		}
	}else{
		update_option('LChowmany', 0);
	}
}

function optionsFormular2(){?> 
		<div class="wrap">
        <table class="form-table">
		 <tbody><tr>
     	 <th scope="row" valign="top"><?php _e('Internationales Format / Deutsches Format', 'laca');?></th><td>	
	
    <form method="post">
        <input type="radio" name="international" value="-1"<?php if(get_option('LCinternational')==-1){echo "checked=\"checked\"";}?>/>
		<?php _e('Deutsches Format verwenden (Dann die folgenden Optionen weiter unten setzen)', 'laca'); ?><br /><br /><?php _e('internationale Formate:', 'laca') ?><br/>
  <input type="radio" name="international" value="1"<?php if(get_option('LCinternational')==1){echo "checked=\"checked\"";}?>/>   <?php _e('October 5th, 2008 9.00 am', 'laca'); ?><br />
  <input type="radio" name="international" value="2"<?php if(get_option('LCinternational')==2){echo "checked=\"checked\"";}?>/>   <?php _e('5th October 2008 9.00 am', 'laca');?><br />        
  <input type="radio" name="international" value="3"<?php if(get_option('LCinternational')==3){echo "checked=\"checked\"";}?>/>   <?php _e('5 Oct 2008 9.00 am', 'laca');?>
        
        </td></tr>
             <tr>
            <th scope="row" valign="top"><?php _e('Tag anzeigen:', 'laca'); ?></th><td>
            <input type="checkbox" name="tag" <?php if(get_option('LCday')==1){
														echo "checked=\"checked\"";
													}?>/><?php _e('Tag im Datum anzeigen', 'laca');?></td></tr>
    		<tr><th scope="row" valign="top"><?php _e('Den Monat wie folgt anzeigen:', 'laca');?></th><td>
            <input type="radio" name="monat" value="-1" <?php if(get_option('LCmonth')==-1){																	echo "checked=\"checked\"";}?>/><?php _e('Monat nicht anzeigen', 'laca'); ?><br />
            <input type="radio" name="monat" value="1" <?php if(get_option('LCmonth')==1){																	echo "checked=\"checked\"";}?>/><?php _e('Monat als zahl anzeigen, z.B.: 03.09.2009', 'laca'); ?><br />
            <input type="radio" name="monat" value="2" <?php if(get_option('LCmonth')==2){																	echo "checked=\"checked\"";}?>/><?php _e('kompletten Mosnatsnamen anzeigen, z.B.: 03. September 2009', 'laca'); ?><br />
            <input type="radio" name="monat" value="3" <?php if(get_option('LCmonth')==3){																	echo "checked=\"checked\"";}?>/><?php _e('Monatsnamen abgekuerzt anzeigen, z.B.: 03. Sep 2009', 'laca'); ?></td></tr><tr>
           	<th scope="row" valign="top"><?php _e('Jahr anzeigen:', 'laca');?></th><td><input type="checkbox" name="jahr" <?php if(get_option('LCyear')==1){
														echo "checked=\"checked\"";
													 }?>/><?php _e('Jahr im Datum anzeigen', 'laca'); ?></td></tr><tr>
            <th scope="row" valign="top"><?php _e('Die Uhrzeit wie folgt anzeigen:', 'laca'); ?></th><td>
            <input type="radio" name="uhrzeit" value="-1" <?php if(get_option('LCtime')==-1){																	echo "checked=\"checked\"";}?>/><?php _e('Uhrzeit nicht anzeigen', 'laca'); ?><br />
            <input type="radio" name="uhrzeit" value="1" <?php if(get_option('LCtime')==1){																	echo "checked=\"checked\"";}?>/><?php _e('Minuten nicht mit anzeigen, z.B.: 21 Uhr', 'laca'); ?><br />
            <input type="radio" name="uhrzeit" value="2" <?php if(get_option('LCtime')==2){																	echo "checked=\"checked\"";}?>/><?php _e('Uhrzeit komplett anzeigen, z.B.: 21:30 Uhr', 'laca');?><br /></td></tr>
<tr><th scope="row" valign="top"><?php _e('Trennzeichen f&uuml;r Datum, Uhrzeit, Name w&atilde;hlen', 'laca');?></th><td>
<input type="text" name="break" value="<? echo get_option('LCbreak');?> " /><?php _e(' Standardeinstellung ist ein Leerzeichen.', 'laca'); ?> 
</td></tr>        
<tr><th scope="row" valign="top">
<?php _e('Wie sollen die Termine geordnet werden?', 'laca');?></th><td>
<input type="radio" name="order" value="1" <?php if(get_option('LCorder')==1){echo "checked=\"checked\"";}?>/>
<?php _e('&Auml;ltester Eintrag an der Spitze, neuester ganz unten', 'laca'); ?><br />
<input type="radio" name="order" value="2" <?php if(get_option('LCorder')==2){echo "checked=\"checked\"";}?>/>
<?php _e('&Auml;ltester Eintrag ganz unten, neuster ganz oben', 'laca'); ?><br /></td></tr>
<tr><th scope="row" valign="top"><?php _e('Maximale Anzahl anzuzeigender Termine:', 'laca'); ?></th><td><input type="text" name="howMany" value="<? echo get_option('LChowmany');?> " /> <?php _e('(Wenn alle Termine angezeigt werden sollen, bitte 0 eingeben)', 'laca');?></td></tr>            
            <tr>
    <?php echo "<th scope=\"row\" valign=\"top\">";
		_e('Zur Zeit sind diese Anzeige-Optionen gesetzt:', 'laca');
		echo"</th><td>";
		if (get_option('LCday')==1){
			_e('Tag im Datum anzeigen, ', 'laca');
		}else{		
			_e('Tag nicht im Datum anzeigen, ', 'laca');
		}
		if (get_option('LCmonth')==0){
			_e('Monat wird nicht angezeiegt, ', 'laca');
		}else{		
			if ( get_option('LCmonth') == 1 ){
				_e('Monat wird als Zahl angezeigt, ', 'laca');
			}else{
				if ( get_option('LCmonth') == 2 ){
					_e('kompletter Monatsname wird angezeigt, ', 'laca');
				}else{
					if ( get_option('LCmonth')==3){
						_e('Monatsname wird abgek&uuml;rzt angezeigt, ', 'laca');
					}
				}
			}
		} 
		if (get_option ('LCyear') == 1){
			_e('Jahr wird im Datum angezeigt, ', 'laca');
		}else{
			_e('Jahr wird nicht im Datum angezeigt, ', 'laca');
		}
		if (get_option ('LCtime')==-1){
			_e('Uhrzeit wird nicht angezeigt ', 'laca') ;
		}else{
			if (get_option ('LCtime')==1){
				_e('Minuten werden in der Uhrzeit nicht angezeigt', 'laca');
			}else{
				if (get_option ('LCtime')==2){
					_e('Uhrzeit wird komplett angezeigt', 'laca');
				}
			}
		}
		if (get_option ('LChowmany') == 0 ){
			_e(', alle Termine werden angezeigt', 'laca');
		}else{
			echo ", ". get_option('LChowmany');
			_e(' Termine werden maximal angezeigt', 'laca');
		}
		echo"</td></tr>";
		?>
        <tr><th scope="row" valign="top"><?php _e('Anzeige-Optionen speichern', 'laca'); ?></th><td><input type="submit" name="submitAnzeige" value="<?php _e('Anzeige-Optionen speichern', 'laca'); ?>" />
    </form></td></tr></tbody></table><?php
}

// END OPTIONS

// WIDGET STUFF

function echoDatesForWidget(){
            echo $before_widget; 
            echo $before_title . $after_title;
			$LCprint = larsensCalender();
			echo $LCprint;
			echo $after_widget;
}

function initLC(){
	$widget_ops = array('classname' => 'Larsen', 'description' => __( "Larsens und Nicks Kalender Version 0.2") );
	wp_register_sidebar_widget('LNC', __('Larsens Calender'), 'echoDatesForWidget', $widget_ops);
    wp_register_widget_control('LNC', __('Larsens Calender'), 'controlLCWidget' );
}

function controlLCWidget(){
	_e('Dieses Widget hat keine gesonderten Eingstellungen. Im Men&uuml; \"Kalender\" finden Sie die Einstellungen.', 'laca');
}

function convertMonthToLongString($int){
	if ( $int == 1) {return "Januar";}else{if ($int == 2){return "Februar";}else{if ($int == 3){return "M&auml;rt";}else{
	if ($int ==4){return "April";}else{if($int==5){return "Mai";}else{if($int == 6){return "Juni";}else{if ($int == 7){
	return "Juli";}else{if ($int ==8){return "August";}else{if ( $int == 9 ){return "September";}else{if ($int == 10){ return 		    "Oktober";}else{if ( $int == 11){return "November";}else{if ($int == 12){return "Dezember";}}}}}}}}}}}}
}
function convertMonthToShortString($int){
	if ( $int == 1) {return "Jan";}else{if ($int == 2){return "Feb";}else{if ($int == 3){return "M&auml;z";}else{
	if ($int ==4){return "Apr";}else{if($int==5){return "Mai";}else{if($int == 6){return "Jun";}else{if ($int == 7){
	return "Jul";}else{if ($int ==8){return "Aug";}else{if ( $int == 9 ){return "Sep";}else{if ($int == 10){ return 		    "Okt";}else{if ( $int == 11){return "Nov";}else{if ($int == 12){return "Dez";}}}}}}}}}}}}
}

function convertMonthToLongStringE($int){
	if ( $int == 1) {return "January";}else{if ($int == 2){return "February";}else{if ($int == 3){return "March";}else{
	if ($int ==4){return "April";}else{if($int==5){return "May";}else{if($int == 6){return "June";}else{if ($int == 7){
	return "July";}else{if ($int ==8){return "August";}else{if ( $int == 9 ){return "September";}else{if ($int == 10){ return 		    "October";}else{if ( $int == 11){return "November";}else{if ($int == 12){return "December";}}}}}}}}}}}}
}
function convertMonthToShortStringE($int){
	if ( $int == 1) {return "Jan";}else{if ($int == 2){return "Feb";}else{if ($int == 3){return "Mar";}else{
	if ($int ==4){return "Apr";}else{if($int==5){return "May";}else{if($int == 6){return "Jun";}else{if ($int == 7){
	return "Jul";}else{if ($int ==8){return "Aug";}else{if ( $int == 9 ){return "Sep";}else{if ($int == 10){ return 		    "Oct";}else{if ( $int == 11){return "Nov";}else{if ($int == 12){return "Dec";}}}}}}}}}}}}
}

add_action("plugins_loaded", "initLC");
// END WIDGET STUFF

//include update
include ('lcupdate.php');
//end
?>