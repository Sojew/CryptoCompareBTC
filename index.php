<?php

namespace cryptocompare;

use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets;
use Google\Service\Testing\GoogleAuto;
use Google_Client;

require_once __DIR__.'/config.php';
require_once __DIR__ . '/vendor/autoload.php';


$currentTime = date('d.m.Y H:i:s');


$cUrl = curl_init();

curl_setopt($cUrl, CURLOPT_URL, $cryptoCompareUrl);
curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($cUrl);

if($e = curl_error($cUrl)) {
    echo $e;
} else {
    $cryptoCompareData = json_decode($data,true);
}
curl_close($cUrl);  // Array ( [RUB] => f [USD] => x [EUR] => y [UAH] => z)

//$spreadsheeId = sanitize_text_field(trim($_POST['google_spreadsheet_id']));
// if(
//   !$spreadsheet_id ||
//   strlen($spreadsheet_id) < 8
// ) throw new \Exception("!!!");

$credentialsPath = __DIR__.'/google-account-credentials.json';
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Sheets::SPREADSHEETS);
$service = new Sheets($client);


$parsedExchangeRates = [
    [$currentTime,$cryptoCompareData["RUB"],$cryptoCompareData["USD"],$cryptoCompareData["EUR"], $cryptoCompareData["UAH"]]
];

$requestBody = new ValueRange([ 'values' => $parsedExchangeRates ]);
$result = $service->spreadsheets_values->append($spreadsheetId, "Sheet1", $requestBody, ['valueInputOption' => 'RAW' ], [ "insertDataOption" => "INSERT_ROWS" ]);

printf("%d more exchange rates has been appended!", $result->getUpdates()->getUpdatedCells());

?>

