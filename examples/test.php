<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Common\Curl;
echo Curl::get('https://www.baidu.com/', null);
