<?php
include_once 'syntaxes.php';
include_once 'parser.php';
include_once 'lib.php';

top();
$handle = @fopen('torture.dat', 'r');
if ($handle) {
	while (($buffer = stream_get_line($handle,65535,"\n")) !== false) {
		if(substr($buffer, 0, 1) == '#') {
			continue;
		}
		$canyonStr = $buffer;
		includeProfile($canyonStr);
	}
	if (!feof($handle)) {
		echo "Erreur: stream_get_line() a échoué\n";
	}
	fclose($handle);
}
bottom();
?>
