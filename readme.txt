=== Plugin Name ===
Contributors: Lars Koester
Donate link: http://larsmachtpraktikum.stil-etage.de/?page_id=3
Tags: Calender, Events, Kalender, Termine
Requires at least: 2.5
Tested up to: 2.6
Stable tag: 1.2

This calender allows you to manage and display your events.
There are a lot of setting options; German, British and American format styles for output.
You can decide how past events shall be managed.
You can delete them automatically or save them for some time.

== Description ==

This calender allows you to manage and display your events.
There are a lot of setting options; German, British and American format styles for output.
You can decide how past events shall be managed.
You can delete them automatically or save them for some time.

== Installation ==

1.  Upload the "larsen" directory and its contents to the `/wp-content/plugins/` directory
2.  Activate the plugin through the 'Plugins' menu in WordPress
3.  In your admin-menu open the submenu `calender`
4. Place `[LarsensCalender]` to your page(s)
5. or add the widget
6.  Have a look to the submenu `settings` to change the behaviour of the events

add your styles to: larsenscalender.css
the class LCOld is for events of the past the two other one are general classes.
LCempty is the row that produce the free space between the rows with content

you can change the width of the rows in: LCtable.php

to uninstall click on the deinstall-butten that you find at the bottom of the
settings page. then just deactivate the plugin.

== Frequently Asked Questions ==

= Where can I find an international format for the date? =

In Calander's settings you will find `use international format`.

= I would like to add the calender in my sidebar in an different style than on my pages. How can i do it? =

print the follwing function in your php code:

echoLarsensCalender( $day, $month, $year, $time, $howmany, $order, $international);
					
	 $day	  0		do not display day
	 		  1		display day
	 &month   -1	do not display month
	 		  1		month as integer
			  2		display name of month
			  3		display shorted name of month
	$year	  0		do not display year
			  1		display year
	$time     0		do not display time
			  1		only display hours 
			  2		display hours and minutes
	$howmany		maximum number of events that shall be displayed
	$order	  1		oldest top, newest bottom
			  2		oldest bottom, newest top
	$international
			 -1		display date as you set the options above
			 1		October 5th, 2008 9.00 am
			 2		5th October 2008 9.00 am
		     3		5 Oct 2008 9.00 am
			
e.g.: `<?php print echoLarsensCalender ( 1, 1, 1, 2, 10, 2, -1); ?>` causes 26.10.2008 14:00 Uhr 
		
= Can I change the design of the calender? =

yeah, cou can. you're welcome to change larsenscalender.css that you find in the plugin directory.

== Screenshots ==

1. an exapample of the calender with styled css
2. setting menue