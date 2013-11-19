<?php
include_once 'syntaxes.php';
include_once 'parser.php';
include_once 'lib.php';

top();
$handle = @fopen('torture.dat', 'r');
if ($handle) {
	while (($buffer = fgets($handle)) !== false) {
		if(substr($buffer, 0, 1) == '#') {
			continue;
		}
		$canyonStr = rtrim($buffer,"\n");
		//error_log($canyonStr);
		includeProfile($canyonStr);
	}
	if (!feof($handle)) {
		echo "Erreur: stream_get_line() a échoué\n";
	}
	fclose($handle);
}
bottom();
?>
