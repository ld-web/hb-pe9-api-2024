<?php

$client = curl_init("https://jsonplaceholder.typicode.com/users");

curl_setopt($client, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($client);

var_dump(json_decode($response, true));

curl_close($client);
