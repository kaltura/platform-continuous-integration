<link type="text/css" href="css/csi.css" rel="Stylesheet" />
 <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.js"></script>
<html>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
function drawChart(releases) 
{
   var data = google.visualization.arrayToDataTable(releases);

        var options = {
//          title: 'Core Releases',
          vAxis: { 
viewWindowMode:'explicit',
    viewWindow: {
        max:110,
        min:0
    }
}

        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);

}

function initialize(arr) 
{
        // The URL of the spreadsheet to source data from.
        var query = new google.visualization.Query(
            'https://spreadsheets.google.com/pub?key=pCQbetd-CptF0r8qmCOlZGg');
        query.send(function(){ draw(arr)});
}

function draw(arr) 
{
        var geoData = google.visualization.arrayToDataTable(                                                                                  	arr
	);                      
        var geoView = new google.visualization.DataView(geoData);                                                                             geoView.setColumns([0, 1]);      
        var table =
            new google.visualization.Table(document.getElementById('table_div'));
        table.draw(geoData, {showRowNumber: false});
}

</script>
</head>
<body class="csi">

<?php

function version_compare1($ao, $bo)
{

    $a=$ao[0];
    $b=$bo[0];
    $a = explode(".", $a); //Split version into pieces and remove trailing .0
    $b = explode(".", $b); //Split version into pieces and remove trailing .0
    foreach ($a as $depth => $aVal){ //Iterate over each piece of A
        if (isset($b[$depth])){ //If B matches A to this depth, compare the values
            if ($aVal > $b[$depth]) return 1; //Return A > B
            else if ($aVal < $b[$depth]) return -1; //Return B > A
            //An equal result is inconclusive at this point
        }else{ //If B does not match A to this depth, then A comes after B in sort order
            return 1; //so return A > B
        }
    }
    //At this point, we know that to the depth that A and B extend to, they are equivalent.
    //Either the loop ended because A is shorter than B, or both are equal.
    return (count($a) < count($b)) ? -1 : 0;
} 





$script_name=basename(__FILE__);
require_once(dirname($script_name).DIRECTORY_SEPARATOR.'db_conn.inc');
$db=new SQLite3($dbfile,SQLITE3_OPEN_READONLY) or die("Unable to connect to database $dbfile");
$db=new SQLite3($dbfile,SQLITE3_OPEN_READONLY) or die("Unable to connect to database $dbfile");
$result=$db->query("select kaltura_version,failed,successful from success_rates order by kaltura_version");
$databary=array();
$version=Array();
while($rates_res = $result->fetchArray(SQLITE3_ASSOC)){
	error_log(print_r($rates_res,true),3,'/tmp/log');
        $databary [] = Array ($rates_res['kaltura_version'],round($rates_res['successful']/($rates_res['successful']+$rates_res['failed'])*100));
        $versions[]=$rates_res['kaltura_version'];

}
usort($versions,'version_compare');
if (!isset($_GET['kaltura_ver'])){
	$kaltura_ver=$versions[count($versions) -1];
}else{
        $kaltura_ver=$_GET['kaltura_ver'];
}
$result=$db->query("select kaltura_version,failed,successful from success_rates where kaltura_version='$kaltura_ver' order by kaltura_version");
while($res = $result->fetchArray(SQLITE3_ASSOC)){
                $successfuln=$res['successful'];
                $failedn=$res['failed'];
}
echo "<pre>";
usort($databary,'version_compare1');
//var_dump($databary);
array_unshift($databary,Array ("release", "test success %"));
echo "</pre>";
$js_array = json_encode($databary);
?>
<script>
 google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(function(){ drawChart(<?php echo $js_array?>) });
</script>
<?php
echo "<title>CSI Kaltura - $kaltura_ver</title>
<h1 class=\"csi\">CSI Dashboard</h1>
<div id=\"chart_div\" style=\"width: 1500px; height: 250px;\"></div>
<h3 class=\"csi\">Release: $kaltura_ver </h3>

<div id=\"jump\" name=\"jump\">
<form method=\"GET\" action=\"$script_name\">
<fieldset style=\"width: 10%; height:8%; display: inline-block;\">
<legend>Jump to version</legend>
<label for=\"version\">Ver:</label>
<select id=\"kaltura_ver\" name=\"kaltura_ver\" tabindex=\"10\" onchange=\"this.form.submit()\">
<option value=''></option>";
foreach ($versions as $version){ 
        echo "<option value='$version'>$version</option>";
}

echo "
</select>

</fieldset>
</form>
</div>
<b><font color=green>Passed tests: $successfuln</font>, <font color=red>failed tests: $failedn</font>.</b><br><br>";
$result=$db->query("select * from csi_log where kaltura_version='$kaltura_ver' order by rc desc");

$data=array();
$data []= array("Hostname","Name","Result","Run time","RC","Duration");
while($res = $result->fetchArray(SQLITE3_ASSOC)){
        $data[]=array($res['host_name'],$res['test_name'],$res['test_result'],date('M-d-Y',$res['create_time']),$res['rc'],$res['exec_duration']);
}
$arr=json_encode($data);
$db->close();
echo '<h3 class=\"csi\">Current Travis CI build status:<h3>
<TABLE>
<TR>
    <!--TD><b>CLI client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsCLI"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsCLI.svg?branch='.$kaltura_ver.'"></a></TD-->
</TR>
<TR>
    <TD><b>Java client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsJava"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsJava.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>PHP 5 client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>PHP 5_3 client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP53"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP53.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>ZF client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsZF"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsZF.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>Ruby client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsRuby"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsRuby.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>NodeJS client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsNodeJS"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsNodeJS.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>Python client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPython"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPython.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>C# client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsCsharp"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsCsharp.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>Objective C client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsObjectiveC"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsObjectiveC.svg?branch='.$kaltura_ver.'"></a></TD>
</TR>
<TR>
    <TD><b>Nginx VOD module:</b></TD><TD><a href="https://travis-ci.org/kaltura/nginx-vod-module"><img src="https://travis-ci.org/kaltura/nginx-vod-module.svg?branch=master"></a></TD>
</TR>
<TR>
<TR>
    <TD><b>Player SDK iOS:</b></TD><TD><a href="https://travis-ci.org/kaltura/player-sdk-native-ios"><img src="https://travis-ci.org/kaltura/player-sdk-native-ios.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>Player SDK Android:</b></TD><TD><a href="https://travis-ci.org/kaltura/player-sdk-native-android"><img src="https://travis-ci.org/kaltura/player-sdk-native-android.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>Nginx Secure Token module:</b></TD><TD><a href="https://travis-ci.org/kaltura/nginx-secure-token-module"><img src="https://travis-ci.org/kaltura/nginx-secure-token-module.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>Nginx Parallel module:</b></TD><TD><a href="https://travis-ci.org/kaltura/nginx-parallel-module"><img src="https://travis-ci.org/kaltura/nginx-parallel-module.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>Nginx Akamai Token Validate module:</b></TD><TD><a href="https://travis-ci.org/kaltura/nginx-akamai-token-validate-module"><img src="https://travis-ci.org/kaltura/nginx-akamai-token-validate-module.svg?branch=master"></a></TD>
</TR>
</TABLE>
<h3 class=\"csi\">Overall test status:<h3>';
?>
<script>
      google.load('visualization', '1', {'packages': ['table','map']});
      google.setOnLoadCallback(function(){ initialize(<?php echo $arr?>)});
</script>
    <table >
      <tr>
        <td>
          <div id="table_div"></div>
        </td>
      </tr>
    </table>
<?php
?>

</table>
</body>
</html>

