<?php
if ($argc < 5){
    echo 'Usage: ' . $argv[0]." <service URL> <partner ID> <admin secret> <entry ID> </path/to/transcript/file>\n";
    exit (1);
}
$service_url=$argv[1];
$partner_id=$argv[2];
$admin_secret=$argv[3];
$entry_id=$argv[4];
$transcript_file=$argv[5];
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
//$callback_url=urlencode("$service_url/api_v3/index.php/service/integration_integration/action/notify/id/$job_id/ks/$ks");
$url = VOICEBASE_ENDPOINT.'?version='.API_VERSION.'&apiKey='.API_KEY.'&password='.API_PASSWD.'&action=uploadMedia&externalID='.$entry_id ;//&machineReadyCallBack='.$callback_url;
$ch = curl_init();
$postfields = array("mediaUrl" => $mediaUrl,
    "transcript" => '@'.$transcript_file,
    'transcriptType' => 'human');
$options = array(
    CURLOPT_URL => $url,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $postfields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER =>0,
    //CURLOPT_VERBOSE=>true
); 

curl_setopt_array($ch, $options);
$result = json_decode(curl_exec($ch),true);
var_dump($result);
curl_close($ch);
