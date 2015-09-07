<?php 
if ($argc < 5){
    echo 'Usage: ' . $argv[0]." <service URL> <partner ID> <admin secret> <entry ID> <job ID>\n";
    exit (1);
}
$service_url=$argv[1];
$partner_id=$argv[2];
$admin_secret=$argv[3];
$entry_id=$argv[4];
$job_id=$argv[5];
require_once('cielo24.inc');
require_once('/opt/kaltura/web/content/clientlibs/php5/KalturaClient.php');
$config = new KalturaConfiguration($partner_id);
$config->serviceUrl = $service_url;
$client = new KalturaClient($config);
$expiry = null;
$privileges = null;
$userId = null;
$type = KalturaSessionType::ADMIN;
//echo "$admin_secret, $userId, $type, $partner_id, $expiry, $privileges\n";exit;
$ks = $client->session->start($admin_secret, $userId, $type, $partner_id, $expiry, $privileges);
$client->setKs($ks);
$callback_url=urlencode("$service_url/api_v3/index.php/service/integration_integration/action/notify/id/$job_id/ks/$ks");
$filter = new KalturaAssetFilter();
$filter->advancedSearch = null;
$filter->entryIdEqual = $entry_id;
$filter->tagsLike = 'source';
$pager = null;
$result = $client->flavorAsset->listAction($filter, $pager);
$mediaUrl = urlencode($client->flavorAsset->geturl($result->objects[0]->id, null, null, null)); 

$url = CIELO24_ENDPOINT.'/account/login?v=1&username='.CIELO24_USER.'&password='.CIELO24_PASSWD;
$ch = curl_init();
$options = array(
    CURLOPT_URL => $url,
//    CURLOPT_POST => 1,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER =>0,
    // CURLOPT_VERBOSE=>true
); 
curl_setopt_array($ch, $options);
$result = json_decode(curl_exec($ch),true);

var_dump($result);

$appToken = $result['ApiToken'];

$url = CIELO24_ENDPOINT.'/job/new?v=1&api_token='.$appToken.'&language=en&external_id='.$entry_id;
curl_setopt($ch, CURLOPT_URL, $url);

$result = json_decode(curl_exec($ch),true);
var_dump($result);
$jobId = $result['JobId'];
$jobCreationTaskId = $result['TaskId'];
$url = CIELO24_ENDPOINT.'/job/add_media?v=1&api_token='.$appToken.'&job_id='.$jobId.'&media_url='.$mediaUrl;
curl_setopt($ch, CURLOPT_URL, $url);

$result = json_decode(curl_exec($ch),true);
var_dump($result);
echo "media url is $mediaUrl" . PHP_EOL;
echo "uploading media:" . PHP_EOL;
$url = CIELO24_ENDPOINT.'/job/perform_transcription?v=1&api_token='.$appToken.'&job_id='.$jobId.'&transcription_fidelity='.MECHANICAL.'&priority=ECONOMY&callback_url='.$callback_url;
var_dump($url);
curl_setopt($ch, CURLOPT_URL, $url);
$result = json_decode(curl_exec($ch),true);
var_dump($result);


curl_close($ch);

