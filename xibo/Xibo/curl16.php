<?php
//curl6.php

// populate the array containing the values to be posted.
$postfields = array();
$postfields['emergency'] = urlencode('0');
//$postfields['field2'] = urlencode('value2');

$ch = curl_init();

// Follow any Location headers
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

curl_setopt($ch, CURLOPT_URL, '128.195.18.82/xibo/curl17.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Alert cURL to the fact that we're doing a POST, and pass the associative array for POSTing.
curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

$output = curl_exec($ch);
curl_close($ch);
print $output;
?>