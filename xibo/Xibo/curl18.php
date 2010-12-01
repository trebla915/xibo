<?php
// curl8.php

$ch = curl_init();
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

// Optionally set a timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

curl_setopt($ch, CURLOPT_URL, 'http://www.amazon.com/exec/obidos/search-handle-form/002-0565257-5012066');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "url=index%3Dbooks&field-keywords=CURL");
$output = curl_exec($ch);
curl_close($ch);
print $output;
?>