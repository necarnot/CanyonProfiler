<?php
header('Content-Type: image/svg+xml');

include 'syntaxes.php';
include 'parser.php';
include 'samples.php';
include 'lib.php';

// TODO
// Décider de garder ou non la distinction Pi / To

// Function for basic field validation (present and neither empty nor only white space
function isNullOrEmptyString($question){
	return (!isset($question) || trim($question)==='');
}

function randomColor() {
	return '000000';
	mt_srand((double)microtime()*1000000);
	$c = '';
	while(strlen($c)<6){
		$c .= sprintf("%02X", mt_rand(0, 255));
	}
	return $c;
}

function displayText($text, $xText, $yText, $xOffset, $yOffset, $align = 'start') {
	$xText += $xOffset;
	$yText += $yOffset;
	echo '
	<g font-size="16" font-family="sans-serif" fill="black" stroke="none" text-anchor="'. $align .'">
		<text x="' . $xText . '" y="' . $yText . '">' . $text . '</text>
	</g>';
}

$p = new Profile();
$p->pageWidthPx -= $p->xEndOffset;
$p->pageHeightPx -= $p->yEndOffset;

echo '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

<svg width="' . $p->pageWidth . 'mm" height="' . $p->pageHeight . 'mm" version="1.1"
	xmlns="http://www.w3.org/2000/svg"
	xmlns:xlink="http://www.w3.org/1999/xlink">

<defs>
	<g id="sapin">
		<rect x="45" y="70" width="10" height="20" fill="peru"/>
		<polygon points="20,70 80,70 60,55 70,55 55,40 65,40 50,20 35,40 45,40 30,55 40,55" fill="forestgreen"/>
	</g>
</defs>

';

echo '<!--';
var_dump($syntaxes);
var_dump($syntaxes2);
echo '-->';
$p->parse($canyonStr);

// Here was scaling code
$p->scale();


echo '
<g id="layer1">';
$displayOrigCanyonStr = preg_replace('/&/', '&amp;', $p->origCanyonStr);
//if (false) {
if (true) {
echo '
	<switch>
		<foreignObject x="10" y="0" width="'. $p->pageWidthPx .'" height="200">
			<p xmlns="http://www.w3.org/1999/xhtml" style="font-size:8px">Submitted : '.$canyonName .' : '. $displayOrigCanyonStr.'<br/>Parsed as : '.$canyonName .' : '. $p->canyonStr.'</p>
		</foreignObject>
		<text x="20" y="20">Your SVG viewer cannot display html.</text>
	</switch>
';
}

$p->draw();

echo '
</g>

</svg>';
?>
