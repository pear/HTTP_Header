<?php
require_once 'HTTP/Header/Cache.php';
$h = &new HTTP_Header2_Cache(1, 'hour');
$h->sendHeaders();
echo date('r');
?>
