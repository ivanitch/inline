<?php

declare(strict_types=1);

// Develop
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require dirname(__DIR__) . '/vendor/autoload.php';


$conn = \Inline\Connection::getInstance(
    require_once dirname(__DIR__) . '/config/db.php'
);

$query = new \Inline\Query($conn);

dump($query);