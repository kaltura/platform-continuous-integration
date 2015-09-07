<?php
if ($argc < 2 ){
    echo 'Usage: ' . $argv[0]." <url/to/entry>\n";
    exit (1);
}
$entry_url=$argv[1];
require_once('voicebase_account.inc');
$url = VOICEBASE_ENDPOINT.'?version='.API_VERSION.'&apiKey='.API_KEY.'&password='.API_PASSWD.'&action=uploadMedia&externalID=';

$postfields = array("mediaUrl" => $entry_url );
$ch = curl_init();
$options = array(
    CURLOPT_URL => $url,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $postfields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER =>0,
); // cURL options
curl_setopt_array($ch, $options);
$result = json_decode(curl_exec($ch),true);
var_dump($result);
/*array(5) {
["requestStatus"]=>
string(7) "SUCCESS"
["statusMessage"]=>
string(38) "The request was processed successfully"
["mediaId"]=>
string(13) "55ed7c1078d43"
["externalId"]=>
string(0) ""
["fileUrl"]=>
string(93) "http://www.voicebase.com/autonotes/private_detail/9676570/hash=cpqcZmlsZpdsbJvImZRiaGPLSammxo"
}*/

if ($result['requestStatus']!=='SUCCESS'){
    echo $result['statusMessage'].".\nExiting\n";
    exit (2);
}

