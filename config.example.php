<?php
$DB_HOST = 'localhost';
$DB_NAME = 'db_name';
$DB_USER = 'db_user';
$DB_PASS = 'db_password';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_errno) {
    die('Không kết nối được database, vui lòng kiểm tra cấu hình');
}

$mysqli->set_charset('utf8mb4');
