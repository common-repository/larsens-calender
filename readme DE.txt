=== Plugin Name ===
Contributors: Lars Köster
Donate link: http://example.com/
Tags: Calender, Events, Kalender, Termine
Requires at least: 2.0.2
Tested up to: 2.6
Stable tag: 1.2

== Description ==
Dieser Kalender zeigt deine Termine und besitzt viele Einstellungs-
moeglichkeiten. Du kannst entscheiden, ob vergangene Termine automatisch
geloescht werden sollen oder noch einige Zeit angezeigt werden.

== Installation ==

1.  Lade den Ordner "larsen" in deinen wordpress-ordner `/wp-content/plugins/`
2.  Aktiviere das Plugin
3.  Oeffne im Sebmenu "Kalender"
4.1 Setze `[LarsensCalender]` auf eine deiner Seiten
4.2 oder fuege das Widget hinzu
5.  Schau ins Untermenue "Einstellungen", um veschiedene Optionen zu setzen.

Fuers Design:

editiere larsenscalender.css
LCold ist fuer vergangene Termine, die anderen beiden sind allgemein (Farbe kann abwechseln)
LCempty ist der Style fuer die Zeile, die den Abstand fuer die mit Terminen gefuellten Zeilen 
erzeugt (mit height: kann also ein Abstand erzeugt werden)

in der Datei LCtable.php kannst du die Breite der Spalten aendern.

Zum Deinstallieren klicke bei den Einstellungen ganz unten auf 'Deninstallieren'
und deaktiviere danach das plugin.

== Frequently Asked Questions ==

= Where can I find an international format for the date? =

In Calander's settings you will find `use international format`.

= I would like to add the calender in my sidebar in an different style than on my pages. How can i do it? =

print the function
	 echoLarsensCalender( $day, $month, $year, $time, $howmany, $order, $international)
					
	 $day	= 0		do not display day
	 		= 1		display day
	 &month = -1	do not display month
	 		= 1		month as integer
			= 2		display name of month
			= 3		display shorted name of month
	$year	= 0		do not display year
			= 1		display year
	$time   = 0		do not display time
			= 1		only display hours 
			= 2		display hours and minutes
	$howmany		maximum number of events that shall be displayed
	$order	= 1		oldest top, newest bottom
			= 2		oldest bottom, newest top
	$international
			=-1		display date as you set the options above
			=1		October 5th, 2008 9.00 am
			=2		5th October 2008 9.00 am
			=3		5 Oct 2008 9.00 am
			
e.g.: <?php print echoLarsensCalender ( 1, 1, 1, 2, 10, 2, -1); ?> causes 26.10.2008 14:00 Uhr 
      <?php print echoLarsensCalender ( 1, 1, 1, 2, 10, 2, 3); ?>  causes 26 Oct 2008 2.00 pm
		
= Can I change the desing of the calender? =

yeah, cou can. you're welcome to change larsenscalender.css that you find in the plugin directory.