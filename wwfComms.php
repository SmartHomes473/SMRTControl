<?php

$TransmitAcknowledge = 3;
$TransmitTimeout = 4;
$ReceievedPacket = 5;

session_start();
// MySQL Variables
$SQLUsername = "root";
$SQLPassword = "smarthouse";
 
$SQLHost = "localhost";
$WWFDB   = "wwfSample"; 

// Connect to database.
$database = mysql_connect($SQLHost,$SQLUsername,$SQLPassword);
mysql_select_db($WWFDB,$database);

// Get Comms data
$settings = mysql_fetch_array(mysql_query("SELECT * FROM `Settings` WHERE id=1"));
$CommData = mysql_fetch_array(mysql_query("SELECT * FROM `Communication`"));

print "Status: \t\t".$CommData['Status']."
Extended Status Length:\t".$CommData['ExStatusLength']."
Exteded Status Data: \t".$CommData['ExtendedStatus']."
";

switch ( $CommData['Status'] )
{
	case $TransmitAcknowledge:
		// TX Acknowledged
		// Process Acknowledge data. Extract data, Setup next packet or return to IDLE.
		print "Performing TransmitAcknowledge\n";
    	$Command = 'UPDATE `Communication` SET `Status`=0 WHERE 1';
   	 	mysql_query($Command);
		break;
	case $TransmitTimeout:
		// TX timed out
		// Last transmit timed out. Resend or forget it.
		print "Processing Transmit Timeout\n";
		break;
	case $ReceievedPacket:
		// RX Packet
		// Process recieved packet. Setup Acknowledge to send back.
		print "Processing Receieved Packet\n";
		break;
}