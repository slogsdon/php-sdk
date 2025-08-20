<?php
if (!empty($_GET['details'])) {
    $details = json_decode(base64_decode($_GET['details']));
    print_r($details);
    exit;
}