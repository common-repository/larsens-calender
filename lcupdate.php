<?php
function echoLarsensCalender( $day, $month, $year, $time, $howmany, $order, $international){	
	global $wpdb;
	if( (get_option('oldDates')==1) || (get_option('oldDates')==3) ){ killOldDates();}
	if ( $order ==1 ){
		$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender
									   ORDER BY year ASC, month ASC, day ASC, hour ASC, minute ASC");
	}else{
		$termine = $wpdb->get_results("SELECT * FROM wp_larsenscalender
									   ORDER BY year DESC, month DESC, day DESC, hour DESC, minute DESC");
	}
	$j=0;
	$counter = 0;
	if ($howmany==0){ $howmany=-1;}
	include ('LCtable.php');
	$LCtable .= "<div align=\"center\">";
	$LCtable .= "<table class=\"LCtable\" cellspacing=\"0\" cellpadding=\"0\">
				  <colgroup>
    				<col width=\"".$LCw1."\">
    				<col width=\"".$LCw2."\">
    				<col width=\"".$LCw3."\">
  			</colgroup>";
	foreach ( $termine as $termin ){
		$LCdate = LCgetDate($termin, $day, $month, $year, $international);
		$LCtime = LCgetTime($termin, $time, $international);
		$LCname = LCgetName($termin, $j);
		if (get_option('oldDates') == 4){
			if ( isOld($termin) ){
				$LCtable .= "<tr>
								<td class=\"LCold\">".$LCdate."</td>
								<td class=\"LCold\">".$LCtime."</td>
								<td class=\"LCold\">".$LCname."</td>
							 </tr>
							 <tr>
								<td class=\"LCEmpty\"></td>
								<td class=\"LCEmpty\"></td>
								<td class=\"LCEmpty\"></td>
							 </tr>";
				$counter++;						 
			}else{
				$LCtable .= "<tr>		 	
							<td class=\"LC".($j%2+1)."\">".$LCdate."</td>
						 	<td class=\"LC".($j%2+1)."\">".$LCtime."</td>
						 	<td class=\"LC".($j%2+1)."\">".$LCname."</td>
						 </tr>
						 <tr>
						 	<td class=\"LCEmpty\"></td>
							<td class=\"LCEmpty\"></td>
							<td class=\"LCEmpty\"></td>
						 </tr>";
				$counter++;
				$j++;
			}
		}else{
			if ( get_option('oldDates') == 2 ){
				if ( !(isOld($termin))){	
								$LCtable .= "<tr>		 	
							<td class=\"LC".($j%2+1)."\">".$LCdate."</td>
						 	<td class=\"LC".($j%2+1)."\">".$LCtime."</td>
						 	<td class=\"LC".($j%2+1)."\">".$LCname."</td>
						 </tr>
						 <tr>
						 	<td class=\"LCEmpty\"></td>
							<td class=\"LCEmpty\"></td>
							<td class=\"LCEmpty\"></td>
						 </tr>";
				$counter++;
				$j++;
				}
			}else{
				if (get_option('oldDates') == 1){
					$LCtable .= "<tr>		 	
							<td class=\"LC".($j%2+1)."\">".$LCdate."</td>
						 	<td class=\"LC".($j%2+1)."\">".$LCtime."</td>
						 	<td class=\"LC".($j%2+1)."\">".$LCname."</td>
						 </tr>
						 <tr>
						 	<td class=\"LCEmpty\"></td>
							<td class=\"LCEmpty\"></td>
							<td class=\"LCEmpty\"></td>
						 </tr>";
					$counter++;
					$j++;
				}else{
					if (get_option('oldDates')==3){
						if( isOldFront($termin)){
							$LCtable .= "<tr>
								<td class=\"LCold\">".$LCdate."</td>
								<td class=\"LCold\">".$LCtime."</td>
								<td class=\"LCold\">".$LCname."</td>
							 </tr>
							 <tr>
								<td class=\"LCEmpty\"></td>
								<td class=\"LCEmpty\"></td>
								<td class=\"LCEmpty\"></td>
							 </tr>";
							$counter++;	
						}else{
							$LCtable .= "<tr>		 	
								<td class=\"LC".($j%2+1)."\">".$LCdate."</td>
								<td class=\"LC".($j%2+1)."\">".$LCtime."</td>
								<td class=\"LC".($j%2+1)."\">".$LCname."</td>
							 </tr>
							 <tr>
								<td class=\"LCEmpty\"></td>
								<td class=\"LCEmpty\"></td>
								<td class=\"LCEmpty\"></td>
							 </tr>";
							$counter++;
							$j++;
						}
					}
				}
			}
		}

		if ( $counter == $howmany){
			break;
		}		
	}
	$LCtable .= "</table></div>";
	//echo $LCtable;
	return $LCtable;
}

function LCgetDate($termin, $day, $month, $year, $international){
	if ( $international > 0 ){
			$LCdate = LCgetDateInternational($termin, $international);
	}else{
		if ( $day == 1 ){
			if ( $termin->day < 10) {$LCdate .= "0";}
			$LCdate .= $termin->day.".";
			if ( $month >= 2 ){ $LCdate .= " ";}
		}
		if ( $month == 1){
			if ( $termin->month < 10 ) { $LCdate .= "0";}
			$LCdate .= $termin->month.".";
		}else{
			if ( $month == 2){
				    $LCdate .= convertMonthToLongString($termin->month)." ";
			}else{
				if ( $month == 3){
					$LCdate .= convertMonthToShortString($termin->month)." ";
				}
			}
		}
		if ( $year == 1 ){
			$LCdate .= $termin->year." ";
		}
	}
	return $LCdate;
}	
	
function LCgetDateInternational($termin, $international){	
	if ( $international==1){
		$LCdate = convertMonthToLongStringE($termin->month)." ".$termin->day;
		$LCdate .= echoDateDayExtraUpdate($termin);		
		$LCdate .= ", ". $termin->year;
	}else{
		if( $international ==2) {
			$LCdate =  $termin->day;
			$LCdate .= echoDateDayExtraUpdate($termin);
			$LCdate .= " ". convertMonthToLongStringE($termin->month). " ". $termin->year;
		}else{
			if( $international ==3){
				$LCdate .= $termin->day ." ". convertMonthToShortStringE($termin->month) ." ". $termin->year;
			}
		}
	}
	return $LCdate;
}

function echoDateDayExtraUpdate($termin){
		if( ($termin->day==1) ||  ($termin->day==21)  || ( $termin->day == 31) ){
			return "st";
		}else{
			if ( ($termin->day == 2) || ( $termin->day==22 ) ){
				return "nd";
			}else{
				if ( ($termin->day == 3 ) || ( $termin->day == 23 ) ){
					return "rd";
				}else{
					return "th";
				}
			}
		}
}

function LCgetTime($termin, $time, $international){
	if ( $international >0 ){
		$LCgetTime = LCgetTimeInternational($termin);
	}else{
		if ( $time == 1){
			if ( $termin->hour < 10) {$LCgetTime .= "0";}
			$LCgetTime .= $termin->hour." Uhr";
		}else{
			if ( $time ==2){
				if ( $termin->hour < 10) {$LCgetTime .= "0";}
				$LCgetTime .= $termin->hour.":";
				if ( $termin->minute < 10) {$LCgetTime .= "0";}
				$LCgetTime .= $termin->minute." Uhr ";
			}
		}
	}
	return $LCgetTime;
}

function LCgetTimeInternational($termin){
	if ($termin->hour <=12){
			$LCgetTime = $termin->hour.".";
	}else{
			$LCgetTime = $termin->hour%12 .".";
	}
	if ( $termin->minute < 10) { $LCgetTime .= "0";}
	$LCgetTime .= $termin->minute;
	if ($termin->hour <=12){
		$LCgetTime .= " am ";
	}else{
			$LCgetTime .= " pm ";
	}
	return $LCgetTime;	
}

function LCgetName($termin, $j){
	$name = $termin->name;
	if ($termin->url!=""){
		if (isOldFront($termin)){
			$LClink = "LClinkOld";
		}else{
			if (($j%2+1)==1){
				$LClink = "LClink1";
			}else{
				$LClink = "LClink2";
			}
		}
		$name = "<a href=\"".$termin->url."\" class=\"".$LClink."\">".$termin->name."</a>";
	}
	return $name;
}	

add_shortcode('LarsensCalender', 'larsensCalender');

?>