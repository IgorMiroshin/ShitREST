<?php
header("Content-type: application/json; charset=utf-8");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once $_SERVER["DOCUMENT_ROOT"] . "/v1/class/RestMuseum.php";

$url = $_SERVER["REQUEST_URI"];
$get = $_GET;
$post = $_POST;

$dataClass = new RestMuseum($url, $get, $post);
$result = $dataClass->GetData();

echo json_encode($result, JSON_UNESCAPED_UNICODE);