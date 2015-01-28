<link type="text/css" href="css/csi.css" rel="Stylesheet" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
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


function aasort (&$array, $key) 
{
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

//aasort($your_array,"order");



$script_name=basename(__FILE__);
require_once(dirname($script_name).DIRECTORY_SEPARATOR.'db_conn.inc');
$db=new SQLite3($dbfile,SQLITE3_OPEN_READONLY) or die("Unable to connect to database $dbfile");
if (!isset($_GET['kaltura_ver'])){
        $result=$db->query("select kaltura_version from csi_log order by kaltura_version desc limit 1");
        $res = $result->fetchArray(SQLITE3_ASSOC);
        $kaltura_ver=$res['kaltura_version'];
}else{
        $kaltura_ver=$_GET['kaltura_ver'];
}
$db=new SQLite3($dbfile,SQLITE3_OPEN_READONLY) or die("Unable to connect to database $dbfile");
$result=$db->query("select kaltura_version,failed,successful from success_rates order by kaltura_version");
//$result=$db->query("select success_rates.kaltura_version,success_rates.failed,success_rates.successful from success_rates INNER JOIN csi_log ON success_rates.kaltura_version=csi_log.kaltura_version order by csi_log.create_time");
$databary=array();
$databary [] = Array ("release", "test success %");
$version=Array();
while($rates_res = $result->fetchArray(SQLITE3_ASSOC)){
        $databary [] = Array ($rates_res['kaltura_version'],round($rates_res['successful']/($rates_res['successful']+$rates_res['failed'])*100));
        $versions[]=$rates_res['kaltura_version'];
        if ($rates_res['kaltura_version']===$kaltura_ver){
                $successfuln=$rates_res['successful'];
                $failedn=$rates_res['failed'];
        }
}
//aasort($databary,'kaltura_version');
//echo "<pre>";
//usort($databary,'version_compare');
//var_dump($databary);
//echo "</pre>";
$js_array = json_encode($databary);
?>
<script>
 google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(function(){ drawChart(<?php echo $js_array?>) });
</script>
<?php
echo "<title>CSI Kaltura - $kaltura_ver</title>
<h1 class=\"csi\">CSI Dashboard</h1>
<div id=\"chart_div\" style=\"width: 900px; height: 250px;\"></div>
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
echo '<h3 class=\"csi\">Overall test status:<h3>
<TABLE>
<TR>
    <TD><b>CLI client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsCLI"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsCLI.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>Java client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsJava"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsJava.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>PHP 5 client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>PHP 5_3 client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP53"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsPHP53.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>ZF client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsZF"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsZF.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>Ruby client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsRuby"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsRuby.svg?branch=master"></a></TD>
</TR>
<TR>
    <TD><b>NodeJS client libs:</b></TD><TD><a href="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsNodeJS"><img src="https://travis-ci.org/kaltura/KalturaGeneratedAPIClientsNodeJS.svg?branch=master"></a></TD>
</TR>
</TABLE>
';
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

