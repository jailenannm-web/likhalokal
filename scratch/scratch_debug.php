<?php
require 'bootstrap.php';
$bs = db()->query("SELECT id, business_name, business_type, status FROM businesses WHERE status = 'approved'")->fetchAll();
echo "--- APPROVED BUSINESSES ---\n";
foreach ($bs as $b) {
    echo "ID: " . $b['id'] . " | Name: " . $b['business_name'] . " | Type: " . $b['business_type'] . "\n";
}
