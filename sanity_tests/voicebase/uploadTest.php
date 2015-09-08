<?php
if ($argc < 4){
    echo 'Usage: ' . $argv[0]." <service URL> <partner ID> <admin secret> <entry ID>\n";
    exit (1);
}
$service_url=$argv[1];
$partner_id=$argv[2];
$admin_secret=$argv[3];
$entry_id=$argv[4];
require_once('/opt/kaltura/web/content/clientlibs/php5/KalturaClient.php');
$config = new KalturaConfiguration($partner_id);
$config->serviceUrl = $service_url;
$client = new KalturaClient($config);
$expiry = null;
$privileges = null;
$userId = null;
$type = KalturaSessionType::ADMIN;
$ks = $client->session->start($admin_secret, $userId, $type, $partner_id, $expiry, $privileges);
$client->setKs($ks);
$filter = new KalturaAssetFilter();
$filter->entryIdEqual = $entry_id;
$filter->tagsLike = 'source';
$pager = null;
$result = $client->flavorAsset->listAction($filter, $pager);
$mediaUrl = $client->flavorAsset->geturl($result->objects[0]->id, null, null, null); 
require_once('voicebase_account.inc');
$url = VOICEBASE_ENDPOINT.'?version='.API_VERSION.'&apiKey='.API_KEY.'&password='.API_PASSWD.'&action=uploadMedia&externalID='.$entry_id;

$postfields = array("mediaUrl" => $mediaUrl );
$ch = curl_init();
$options = array(
    CURLOPT_URL => $url,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $postfields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER =>0,
); 
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

