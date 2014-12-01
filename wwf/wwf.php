<?php
session_start();
// MySQL Variables
$SQLUsername = "root";
$SQLPassword = "smarthouse";
 
$SQLHost = "localhost";
$WWFDB   = "wwfSample"; 

$TxData = '';

// html for image usage
$images = array(
     0 => '',
     1 => '<img src="success.png" width="22" height="22"> ',
     -1 => '<img src="fail.png" width="22" height="22">' 
);

// Connect to database.
$database = mysql_connect($SQLHost,$SQLUsername,$SQLPassword);
mysql_select_db($WWFDB,$database);

// Check for settings changes
if(isset($_POST['updateSettings']))
{
  mysql_query("UPDATE `Settings` SET `updateDelay`=".$_POST['delay'].",`degreeMode`=".$_POST['degree']." WHERE id=1");
  $TxData .= 's;'.$_POST['delay'].';'.$_POST['degree'].'#';
}

// Setup variables from database settings
$settings = mysql_fetch_array(mysql_query("SELECT * FROM `Settings` WHERE id=1"));

$updateForecast = False;
// Check for updated cities
$CitySuccess = NULL;
If(isset($_POST['updateCities']))
{
    // City change sucess array. 0 = no change, -1 is failed, 1 is success
    $CitySuccess = array(0,0,0,0,0,0);

    for($i = 1; $i <=5;$i++)
    { // Get new city and check if query is needed
        $q = mysql_fetch_array(mysql_query("SELECT `Location`,`WoeID` FROM `Weather` WHERE id=" . $i ));
        $CurCity = $q['Location'];
        $SetCity = $_POST['c'.$i];//$Cities[$i-1];;
        $Cities[$i] = $CurCity;
        $WoeIDs[$i] = $q['WoeID'];

        If(((!empty($SetCity)) And ($SetCity != $CurCity)))
        { // Attempt to get new zwm/city name from underground weather
            // Send Query with input city name
            $url = 'http://autocomplete.wunderground.com/aq?query='.urlencode($SetCity).'&format=json';
            $session = curl_init($url);
            curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
            $json = curl_exec($session);

            // Take JSON text into data structure
            $data = json_decode($json);

            If(!empty($data->RESULTS))
            { // Parse Data and upload to database
                // Take first result that is a city, If no cities listed take first result. 
                $j = 0;
                $city = $data->RESULTS[0];
                while(array_key_exists($j,$data->RESULTS) && $data->RESULTS[$j]->type != 'city')
                    $j++;
                if(array_key_exists($j,$data->RESULTS))
                    $city = $data->RESULTS[$j];

                // Update Database
                $Cities[$i] = $city->name;
                $WoeIDs[$i] = $city->zmw;
                $update = 'UPDATE `Weather` SET `Location`="'.$Cities[$i].'",`WoeID`="'.$WoeIDs[$i].'" WHERE id='.$i;
                mysql_query($update);

                // Set Success and set to update forcast
                $CitySuccess[$i] = 1;
                $updateForecast = True;
            }
            else
            { // No results, let user know they failed
                $CitySuccess[$i] = -1;
            }
        }
    }
}


if(!isset($Cities) || !isset($WoeIDs))
{ // Fetch cities and id's if not done already
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
{ // Update each city's forcast
    foreach($WoeIDs as $id => $woeid)
    {
        if($CitySuccess[$id] == 1)
        {
            // Query Weather Underground for city's forecast
            $session = curl_init('http://api.wunderground.com/api/baac712bb6c0b326/forecast/q/zmw:'.$WoeIDs[$id].'.json');
            curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
            $json = curl_exec($session);
            $data = json_decode($json);
            
            if(!isset($data->forecast->error))
            { // If no error, Parse weather information and update forecast
                $TxData .= "w;";
                $fcast = $data->forecast->simpleforecast->forecastday[0];
                $high = $fcast->high->fahrenheit;
                $low = $fcast->low->fahrenheit;
                $text = $fcast->conditions;
                $humidity = $fcast->avehumidity;
                $pop = $fcast->pop;
                mysql_query('UPDATE `Weather` SET `condition`="'.$text.'",`HighTemp`='.$high.',`LowTemp`='.$low.',`Humidity`='.$humidity.',`PrecipChance`='.$pop.' WHERE id='.$id);
                $TxData .= $id.';'.$Cities[$id].';'.$text.';'.$high.';'.$low.';'.$humidity.';'.$pop.'#';
            }
        }
    }
}
// Update WWF if necessary
if($TxData != '')
{

    $row = mysql_fetch_array(mysql_query("SELECT `Status` FROM `Communication` WHERE 1"));
    print 'Status '.($row['Status'] == 0);
    if($row['Status'] == 0)
    {
        $Command = 'UPDATE `Communication` SET `Status`=1, `ExStatusLength`='.strlen($TxData).', `ExtendedStatus`="'.$TxData.'" WHERE 1';
        mysql_query($Command);
    }
}
// Display website
print '
 <html style="height:900px">
 <style> 
    ::-webkit-input-placeholder {
        color : black;
    }
    input {
        font-size:large;
    }
 </style>
 <head>
 <body>
 <p style="text-align:center"><font size = 7 >Weather Forecast</font></p>
 <table cellpadding ="5" align="center" style = "font-size:large">
 <form method="post" enctype="multipart/form-data" id="cityUpdate">
 <tr>
	<td>Location</td>
    <td style="text-align:center">Condidtion</td>
	<td style="text-align:center">  High </td>
	<td style="text-align:center">  Low  </td>	
	<td style="text-align:center">Humidity</td>
	<td style="text-align:center">Chance of Precip</td>

 </tr>
';
for($i = 1; $i <=5;$i++)
{
    $CurCity = mysql_fetch_array(mysql_query("SELECT `Location`,`WoeID`,`HighTemp`,`condition`,`LowTemp`,`Humidity`,`PrecipChance`
 FROM `Weather` WHERE id=" . $i ));
    print '<tr> 
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
           <td>' . $CurCity['condition'].'</td>
	       <td style="text-align:center">' . $CurCity['HighTemp'] . '&deg;F</td>
	       <td style="text-align:center">' . $CurCity['LowTemp'] . '&deg;F</td>
	       <td style="text-align:center">' . $CurCity['Humidity'].'%</td>
	       <td style="text-align:center">' . $CurCity['PrecipChance'].'%</td>
	       </tr>';
               
}
print '
 <tr>
 <td><input type="submit" name="updateCities" value="Update Cities"></td>
 </tr>
 </form>
 </table>
 <script type="text/javascript">
 function showDelay(newValue)
 {
   document.getElementById("textDelay").innerHTML=newValue;
 } 
 </script>
 <p style="text-align:center"><font size = 7 > Settings </font></p>
 <table cellpadding="5" align="center" style = "font-size:large">
 <form method="post" enctype="multipart/form-data" id="settingsUpdate">
 <tr>
 <td>Setting</td>
 <td style="text-align:center">Value</td>
 </tr>
';
//print gettype($settings);
print'
<tr>
<td>Update Delay</td>
<td style="text-align:center"> <span id=textDelay>'.($settings['updateDelay']).'</span> Hour(s) </td></tr><tr><td></td><td> <input type="range" name="delay" min="1" max="24" step="1" value="'.($settings['updateDelay']).'" onchange="showDelay(this.value)"></td>
</tr>
<tr>
<td>Temperature Scale</td>
<td><input type="radio" name="degree" value="0"';
if($settings['degreeMode'] == 0)
    print 'checked="checked"';
print '>Farienheit</td>
</tr>
<tr><td></td><td><input type="radio" name="degree" value="1"';
if($settings['degreeMode'] == 1)
    print 'checked="checked"';
print '>Celcius</td>
<tr><td></td><td><input type="radio" name="degree" value="2"';
if($settings['degreeMode'] == 2)
    print 'checked="checked"';
print '>Kelvin</td>
<tr><td><input type="submit" name="updateSettings" value="Update Settings"></td></tr>
</table>
</body>
</html>';
?>