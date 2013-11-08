<?php
include_once 'syntaxes.php';
include_once 'parser.php';
include_once 'lib.php';

top();
$canyonName = '';
$canyonStr = $_POST['canyonStr'];
includeProfile($canyonStr);
bottom();
?>
