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
function GetCityWeather($cities){
	$TxData = '';
	foreach($cities as $id)
	{
    	$row = mysql_fetch_array(mysql_query("SELECT `id`, `Location`, `condition`,`HighTemp`, `LowTemp`, `Humidity`, `PrecipChance` FROM `Weather` WHERE id=" . $id));
    	$location = $row['Location'];
        $cond = $row['condition'];
        $high = $row['HighTemp'];
        $low = $row['LowTemp'];
        $humidity = $row['Humidity'];
        $pop = $row['PrecipChance'];
		$TxData .='w;'.$id.';'.$location.';'.$cond.';'.$high.';'.$low.';'.$humidity.';'.$pop.'#';
	}
	return $TxData;
}

# Process stuff
switch ( $CommData['Status'] )
{
	case $TransmitAcknowledge:
		// TX Acknowledged
		// Process Acknowledge data. Extract data, Setup next packet or return to IDLE.

		print "Performing TransmitAcknowledge\n";
    	$query = 'UPDATE `Communication` SET `Status`=0 WHERE 1';
   	 	mysql_query($query);
		break;
	case $TransmitTimeout:
		// TX timed out
		// Last transmit timed out. Resend or forget it.
		$row = mysql_fetch_array(mysql_query("SELECT `TransmitCount` FROM `commsPHP` WHERE 1"));

    	$query = 'UPDATE `Communication` SET `Status`=1 WHERE 1';
    	$query = 'UPDATE `commsPHP` SET `TransmitCount`=1 WHERE 1';
		print "Processing Transmit Timeout\n";
		break;
	case $ReceievedPacket:
		// RX Packet
		// Process recieved packet. Setup Acknowledge to send back.
		print "Processing Receieved Packet\n";
		$Data = $CommData['ExtendedStatus'];
		$TxData = '';
        // Add timestamp
        date_default_timezone_set('America/Detroit');
        $TxData .= 't;'.date('g;i;A').'#';
		$index = 0;
		while($index < strlen($Data))
		{
			$command = $Data[$index];
			$index +=1;
			switch($command)
			{
				case "w" :

				break;
				case "f" :
					$TxData .= $TxData.GetCityWeather([1,2,3,4,5]);
				break;
			}
		}
    	$query = 'UPDATE `Communication` SET `Status`=6, `ExStatusLength`='.strlen($TxData).', `ExtendedStatus`="'.$TxData.'" WHERE 1';
   	 	mysql_query($query);
		break;
}