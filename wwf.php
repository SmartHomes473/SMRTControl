<?php
session_start();
// MySQL Variables
$SQLUsername = "root";
$SQLPassword = "smarthouse";
 
$SQLHost = "localhost";
$WWFDB   = "wwfSample";
 
 
// Weather
$yahooURL = "http://query.yahooapis.com/v1/public/yql?q="; 
$yahooGetWoeId = "select * from geo.places where text=";
$yahooGetForecast = "select * from weather.forecast where woeid=";
 
function QueryYahoo($qin){
    // Performs YQL query
    $query_url = "http://query.yahooapis.com/v1/public/yql?q=" . urlencode( $qin )."&format=json";
    $session = curl_init($query_url);
    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
    $json = curl_exec($session);
    $ret = json_decode($json);
    return $ret;
}
// URL ARGUMENTS:
// event - Lets PHP know what function to perform
// cities - array of city names user wishes to use.
// success - array of numbers, lets web page show if location update was successful
 
// Events
//    Update Cities
$event_UpdateCities = "updateCities";
//    GetForcats
$event_UpdateForecast = "updateForecast";
 
// Connect to database.
$database = mysql_connect($SQLHost,$SQLUsername,$SQLPassword);
mysql_select_db($WWFDB,$database);
 
If(isset($_POST['updateCities']))//$event == $event_UpdateCities && isset($_GET['cities']))
{
    // success array. 0 = no change, -1 is failed, 1 is success
    $success = array(0,0,0,0,0);
    for($i = 1; $i <=5;$i++)
    { 
        $CurCity = mysql_fetch_array(mysql_query("SELECT `Location`,`WoeID` FROM `Weather` WHERE id=" . $i ))['Location'];
        $SetCity = $_POST['c'.$i];//$Cities[$i-1];
        If( (!empty($SetCity)) And ($SetCity != $CurCity))
        { // Attempt to get new WoeID via yahoo.geo
            $data = QueryYahoo($yahooGetWoeId . "'" . $SetCity . "'");
            print '$$$$$$';
            print !is_null($data->query->results);
            print '#';
            If(!is_null($data->query->results))
            { // Take first result. 
                if($data->query->count == 1)
                {
                    $city = $data->query->results->place;
                }
                else
                {
                    $city = $data->query->results->place['0'];
                }
                $Cities[i-1] = $city->name .", ".$city->admin1->content;
                print $Cities[i-1];
                print $city->woeid;
                $update = 'UPDATE `Weather` SET `Location`="'.$Cities[i-1].'",`WoeID`='.$city->woeid.' WHERE id='.$i;
//$update =               'UPDATE `Weather` SET `Location`="'.$Cities[i-1].'",`WoeID`='.$city->woeid." WHERE id=" . $i;
                print $update;
                mysql_query($update);
                $success[i] = 1;
                print 'success';
            }
            else
            { // Return to old value
                $Cities[i-1] = $CurCity;
                $success[i] = -1;
                print 'fail';
            }
        }
        print 'Done@';
    }
}

If(isset($_GET['event'])) {
    $event = $_GET['event'];
    If($event == $event_UpdateForecast ){}
} 
else {
/*
  If(!isset($_GET['cities']))
  {*/
	$Cities = array();
	$q1 = mysql_query("SELECT `Location`,`WoeID` FROM `Weather` WHERE 1");
	$row = mysql_fetch_array($q1);
    while($row )
	{
		$Cities[]  = $row['Location'];
		$row =mysql_fetch_array($q1);
	}
	//header('Location: wwf.php?cities=' . implode('@',$Cities));
/*}
  else
  {
  $Cities = explode('@',$_GET['cities']);
  }
*/
    // Display website
    print '
 <html>
 <head>
 <title>Wall Weather Forcaster</title>
 </head>
 <font face = "verdana">
 <p><a href="/"> Home</a></p>
 <br>
 <body>
 <p>
 </p>
 <table style = "width:100%">
 <form method="post" enctype="multipart/form-data" id="cityUpdate">
 <tr>
	<td>City #</td>
	<td>Location</td>
	<td>WoeID</td>	
	<td>High</td>
	<td>Low</td>	
	<td>Humidity</td>
	<td>Percipitation</td>

 </tr>
';
    for($i = 1; $i <=5;$i++)
    {
        $CurCity = mysql_fetch_array(mysql_query("SELECT `Location`,`WoeID`,`HighTemp`,`LowTemp`,`Humidity`,`PrecipChance`
 FROM `Weather` WHERE id=" . $i ));
        print '<tr> 
	       <td>'.$i.'</td>
	       <td><input type="text" name="c'. $i .'" placeholder="'.$Cities[$i-1].'"></td>
	       <td>' . $CurCity['WoeID'].'</td>
	       <td>' . $CurCity['HighTemp'] . '&deg;F</td>
	       <td>' . $CurCity['LowTemp'] . '&deg;F</td>
	       <td>' . $CurCity['Humidity'].'%</td>
	       <td>' . $CurCity['PrecipChance'].'%</td>
	       </tr>';
               
    }
    print '
 <tr>
 <input type="submit" name="updateCities"value="Update Cities">
 </tr>
 </form>
 </table>
 </body>
 </font>
 </html>';
}
?>