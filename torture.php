<?php
include_once 'syntaxes.php';
include_once 'parser.php';
include_once 'lib.php';

top();
$handle = fopen('torture.dat', 'r');
if ($handle) {
	while (($buffer = fgets($handle)) !== false) {
		if($buffer == '\n' || substr($buffer, 0, 1) == '#') {
			continue;
		}
		$canyonStr = rtrim($buffer,"\n");
		if(isNullOrEmptyString($canyonStr)) {
			continue;
		}
		includeProfile($canyonStr);
	}
	if (!feof($handle)) {
		echo "Erreur: stream_get_line() a échoué\n";
	}
	fclose($handle);
}
bottom();
?>
