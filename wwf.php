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

$images = array(
     0 => '',
     1 => '<img src="success.png" width="22" height="22"> ',
     -1 => '<img src="fail.png" width="22" height="22">' 
);
 
function QueryYahoo($qin){
    // Performs YQL query and return PHP object of JSON.
    $query_url = "http://query.yahooapis.com/v1/public/yql?q=" . urlencode( $qin )."&format=json";
    $session = curl_init($query_url);
    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
    $json = curl_exec($session);
    $ret = json_decode($json);
    return $ret;
}

// Connect to database.
$database = mysql_connect($SQLHost,$SQLUsername,$SQLPassword);
mysql_select_db($WWFDB,$database);

// Check for settings changes
if(isset($_POST['updateSettings']))
{
    
}

// Setup variables from database settings
$settings = mysql_fetch_array(mysql_query("SELECT * FROM `Settings` WHERE id=1"));
$updateForecast = False;

// Setup updateForcast if Perodic update
if($settings['updateMode'] % 1) 
    if( 0 > time() - ($settings['lastUpdate']+$settings['updateDelay']))
        $updateForcast = True;

// Check for updated cities
$CitySuccess = NULL;
If(isset($_POST['updateCities']))
{
    // sucess array. 0 = no change, -1 is failed, 1 is success
    $CitySuccess = array(0,0,0,0,0,0);
    //$SetCities = array('place');
    for($i = 1; $i <=5;$i++)
    { 
        $q = mysql_fetch_array(mysql_query("SELECT `Location`,`WoeID` FROM `Weather` WHERE id=" . $i ));
        $CurCity = $q['Location'];
        $SetCity = $_POST['c'.$i];//$Cities[$i-1];;
        $Cities[$i] = $CurCity;
        $WoeIDs[$i] = $q['WoeID'];
        //var_dump($Cities);
        //print '<br/>';
        //var_dump($WoeIDs);
        //print '<br/>';
        If( (!empty($SetCity)) And ($SetCity != $CurCity))
        { // Attempt to get new WoeID via yahoo.geo
//            $data = QueryYahoo($yahooGetWoeId . "'" . $SetCity . "'");
            //print yeah;
            $url = 'http://autocomplete.wunderground.com/aq?query='.urlencode($SetCity).'&format=json';
            //print '<br/>'.$url.'<br/>';
            $session = curl_init($url);
            curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
            $json = curl_exec($session);
            $data = json_decode($json);
            //print 'varff';
            //var_dump($data->RESULTS);//[0]->name;
            //print '<br/>';
            If(!empty($data->RESULTS))
            { // Take first result. 
                $j = 0;
                $city = $data->RESULTS[0];
                while(array_key_exists($j,$data->RESULTS) && $data->RESULTS[$j]->type != 'city')
                    $j++;
                if(array_key_exists($j,$data->RESULTS))
                    $city = $data->RESULTS[$j];
                //print $city->name;
/* yahoo               if($data->query->count == 1)
                {
                    $city = $data->query->results->place;
                }
                else
                {
                    $city = $data->query->results->place['0'];
                }
                $Cities[i] = $city->name .", ".$city->admin1->content;
*/
                $Cities[$i] = $city->name;
                $WoeIDs[$i] = $city->zmw;
                //print $city->zmw;
                $update = 'UPDATE `Weather` SET `Location`="'.$Cities[$i].'",`WoeID`="'.$WoeIDs[$i].'" WHERE id='.$i;
                mysql_query($update);
                $CitySuccess[$i] = 1;
                $updateForecast = True;
            }
            else
            { // Return to old value
                //     $Cities[$i] = $CurCity;
                $CitySuccess[$i] = -1;
            }
            //var_dump($Cities);
        }
        //var_dump($CurCity);
        //print '<br/>';
        //var_dump($WoeIDs);
        //print '<br/>';
/*
        else
        {
            
            $Cities[$i] = $CurCity;
        }//print 'Done@';*/
    }
}

if(!isset($Cities) || !isset($WoeIDs))
{
    $Cities = array();
    $WoeIDs = array();
    $q1 = mysql_query("SELECT `id`,`Location`,`WoeID` FROM `Weather` WHERE 1");
    $row = mysql_fetch_array($q1);
    while($row )
    {
        $Cities[$row['id']]  = $row['Location'];
        $WoeIDs[$row['id']]  = $row['WoeID'];
        $row =mysql_fetch_array($q1);
    }
}
If($updateForecast != False)
{
    foreach($WoeIDs as $id => $woeid)
    {
        //print $yahooGetForecast.$woeid;
        $data = QueryYahoo($yahooGetForecast.$woeid);
        // Weather channel specific id
        //http://www.wunderground.com/q/zmw:94125.1.99999
        // print $WoeIDs[$id];
        $session = curl_init('http://api.wunderground.com/api/baac712bb6c0b326/forecast/q/zmw:'.$WoeIDs[$id].'.json');
        curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
        $json = curl_exec($session);
        $data = json_decode($json);
        
        //var_dump($data);//[0]-> name;
        if(!isset($data->forecast->error))
        {
            $fcast = $data->forecast->simpleforecast->forecastday[0];
            //var_dump($fcast);
            $high = $fcast->high->fahrenheit;
            $low = $fcast->low->fahrenheit;
            $text = $fcast->conditions;
            $humidity = $fcast->avehumidity;
            $pop = $fcast->pop;
            /*$humidity = $data->query->results->channel->atmosphere->humidity;
            $high     = $data->query->results->channel->item->forecast[0]->high;
            $low      = $data->query->results->channel->item->forecast[0]->low;
            $text     = $data->query->results->channel->item->forecast[0]->text;
            */
            //print '<br/>UPDATE `Weather` SET `condition`="'.$text.'",`HighTemp`='.$high.',`LowTemp`='.$low.',`Humidity`='.$humidity.',`PrecipChance`='.$pop.' WHERE id='.$id; 
            //exit();
            mysql_query('UPDATE `Weather` SET `condition`="'.$text.'",`HighTemp`='.$high.',`LowTemp`='.$low.',`Humidity`='.$humidity.',`PrecipChance`='.$pop.' WHERE id='.$id);
        }
        //exit();
    }
}
/*
  If(!isset($_GET['cities']))
  {*/
//header('Location: wwf.php?cities=' . implode('@',$Cities));
/*}
  else
  {
  $Cities = explode('@',$_GET['cities']);
  }
*/
//var_dump($images[$CitySuccess[1]]);
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
 <hr>
 <table style = "width:100%">
 <form method="post" enctype="multipart/form-data" id="cityUpdate">
 <tr>
	<td>City #</td>
	<td>Location</td>
	<td>WoeID</td>	
    <td>Condidtion</td>
	<td>High</td>
	<td>Low</td>	
	<td>Humidity</td>
	<td>Percipitation</td>

 </tr>
';
for($i = 1; $i <=5;$i++)
{
    $CurCity = mysql_fetch_array(mysql_query("SELECT `Location`,`WoeID`,`HighTemp`,`condition`,`LowTemp`,`Humidity`,`PrecipChance`
 FROM `Weather` WHERE id=" . $i ));
    print '<tr> 
	       <td>'.$i.'</td>
	       <td><input type="text" name="c'. $i .'" placeholder="'.$Cities[$i].'" ';
    if(isset($CitySuccess))
    {    
        if($CitySuccess[$i] == -1)
            print 'value="'. $_POST['c'.$i].'" ';
        print '>';
        print $images[$CitySuccess[$i]];
    }
    else
        print '>';

	print '</td>
           <td>' . $CurCity['WoeID'].'</td>
           <td>' . $CurCity['condition'].'</td>
	       <td>' . $CurCity['HighTemp'] . '&deg;F</td>
	       <td>' . $CurCity['LowTemp'] . '&deg;F</td>
	       <td>' . $CurCity['Humidity'].'%</td>
	       <td>' . $CurCity['PrecipChance'].'%</td>
	       </tr>';
               
}
print '
 <tr>
 <td></td>
 <td><input type="submit" name="updateCities" value="Update Cities"></td>
 </tr>
 </form>
 </table>
 <hr>
 <table>
 <form method="post" enctype="multipart/form-data" id="settingsUpdate">
 <tr>
 <td>Settings</td>
 <td>Value</td>
 </tr>
';
//print gettype($settings);
foreach( $settings as $key => $value)
{
    if('string' ==gettype($key))
    print '
 <tr>
 <td>'.$key.' </td>
 <td><input type="text" name="'.$key.'" placeholder="'.$value.'"></td>
 </tr>
';
}
print '
 <tr><td></td><td><input type="submit" name="updateSettings" value="Update Settings"></td></tr>
 </table>
 </body>
 </font>
 </html>';
?>