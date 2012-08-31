<?php
require_once('config.php');

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);

if($link === false) {
  error_log('bad mysql connection');
}
$select = mysql_select_db(DB_NAME);

if($select === false) {
  error_log('bad db select');
}