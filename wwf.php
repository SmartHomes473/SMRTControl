<?php
 session_start();
 // MySQL Variables
 $SQLUsername = "root";
 $SQLPassword = "smarthouse";
 
 $SQLHost = "localhost";
 $WWFDB   = "wwfSample";
 
 
 // Weather
 $yahooUrl = "http://query.yahooapis.com/v1/public/yql?q="; 
 $yahooGetWoeId = "select * from geo.places where text=";
 $yahooGetForecast = "select * from weather.forecast where woeid=";
 
 function QueryYahoo($query){
 // Performs YQL query
 $query_url = $yahooUrl . urlencode(query);
 $session = curl_init($query_url);  
 curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
 $json = curl_exec($session);
 return json_decode($json);
}
 // URL ARGUMENTS:
 // event - Lets PHP know what function to perform
 // cities - array of city names user wishes to use.
 // success - array of numbers, lets web page show if location update was successful
 
 // Events
 //    Update Cities
 $event_UpdateCities = "updateCities";
 //    GetForcast
 $event_UpdateForecast = "updateForecast";
 
 // Connect to database.
 $database = mysql_connect($SQLHost,$SQLUsername,$SQLPassword);
 mysql_select_db($WWFDB,$database);
 
 If(isset($_GET['event'])) {
 $event = $_GET['event'];
 If($event == $event_UpdateCities && isset($_GET['cities']))
{
    $Cities = explode(+$_GET['cities']);
        for($i = 1; $i <=5;$i++)
               {
                // success array. 0 = no change, -1 is failed, 1 is success
                $success = array(0,0,0,0,0);
                $SetCity = $Cities[i];
                $CurCity = mysql_query("SELECT `Location` FROM `Weather` WHERE id=" . $i );
                If(SetCity != CurCity)
                { // Attempt to get new WoeID via yahoo.geo
                  $data = QueryYahoo($yahooGetWoeId . "'" . $SetCity . "'");
                  If(!is_null($data->query->results) && $data->count > 0)
                  { // Take first result. 
                    $city = $data->query->results->place[0];
                    $Cities[i] = $city->name .", ".$city->admin1->content;
                    mysql_query("UPDATE `Location`=\"".$Cities[i]."\",`WoeID=`".$city->woeid." FROM `Weather` WHERE id=" . $i );
                    $success[i] = 1;
                  }
                  else
                  { // Return to old value
                    $Cities[i] = $CurCity;
                    $success[i] = -1;
                  }
                }   
               }
            }
       If($event == $event_UpdateForecast ){}
} 
 else {
If(!isset($_GET['cities']))
{
	$Cities = array();
	$q1 = mysql_query("SELECT `Location`,`WoeID` FROM `Weather` WHERE 1");
	$row = mysql_fetch_array($q1);
        while($row )
	{
		$Cities[]  = $row['Location'];
		$row =mysql_fetch_array($q1);
		//print $row['Location'];
	}
	header('Location: wwf.php?cities=' . implode('+',$Cities));
}
else
{
	
	$Cities = explode('+',$_GET['cities']);
	$Cities[0] = 'yes';
}
 // Display website
 print '
 <html>
 <head>
 <title>Wall Weather Forcaster</title>
 </head>
 <font face = "verdana">
 <p><a href="/"> Home</a></p>
 <p><a href="wwf.php">Update Cities</p> 
 <br>
 <body>
 <p>
 <input type="text" name="c1" value='.$Cities[0].'>
 </p>
 <table style = "width:100%">';
 for($i = 1; $i <=5;$i++)
 {
    $CurCity = mysql_fetch_array(mysql_query("SELECT `Location`,`WoeID`
 FROM `Weather` WHERE id=" . $i ));
         print '<tr> 
	       <td>'.$i.'</td>
	       <td><input type=text name=c'.$i.' value="'.$CurCity['Location'].'"></td>
	       <td>'.$CurCity['WoeID'].'</td>
	       </tr>';
               
 }
 print '</table></body></font></html>';
 }
?>