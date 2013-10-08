<?php
header('Content-Type: image/svg+xml');

include 'parser.php';
include 'samples.php';

// TODO
// Décider de garder ou non la distinction Pi / To
// Intégrer la longueur variable de la syntaxe dans le calcul des maxWidth et maxHeight

// Function for basic field validation (present and neither empty nor only white space
function isNullOrEmptyString($question){
	return (!isset($question) || trim($question)==='');
}

function randomColor() {
	mt_srand((double)microtime()*1000000);
	$c = '';
	while(strlen($c)<6){
		$c .= sprintf("%02X", mt_rand(0, 255));
	}
	$c = '000000';
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

$fontHeight = 12;
$pageWidth = 297;
$pageHeight = 210;
$pageWidthPx = 1052.36;
$pageHeightPx = 744.09;
$xEndOffset = 40;
$yEndOffset = 40;
$pageWidthPx -= $xEndOffset;
$pageHeightPx -= $yEndOffset;

$symbols = array (
	'ca' => array (
		'name' => 'Cascade arrondie',
		'height' => 1,
		'width' => 0.2,
		'displayedText' => 'C',
		'strokeWidth' => 2
	),
	'cv' => array (
		'name' => 'Cascade verticale',
		'height' => 1,
		'width' => 0,
		'displayedText' => 'C',
		'strokeWidth' => 2
	),
	'ra' => array (
		'name' => 'Ressaut arrondi',
		'height' => 1,
		'width' => 0.2,
		'displayedText' => 'R',
		'strokeWidth' => 2
	),
	're' => array (
		'name' => 'Ressaut',
		'height' => 1,
		'width' => 0,
		'displayedText' => 'R',
		'strokeWidth' => 2
	),
	'cs' => array (
		'name' => 'Cascade surplombante',
		'height' => 1,
		'width' => -1,
		'displayedText' => 'C',
		'strokeWidth' => 2
	),
	'ma' => array (
		'name' => 'Marche courte',
		'height' => 0,
		'width' => 1,
		'strokeWidth' => 2
	),
	'ml' => array (
		'name' => 'Marche longue',
		'height' => 0.0005,
		'width' => 0.0005,
		'displayedText' => '',
		'strokeWidth' => 2
	),
	'va' => array (
		'name' => 'Vasque',
		'height' => 0,
		'width' => 1,
		'strokeWidth' => 2
	),
	'pi' => array (
		'name' => 'Plan incliné',
		'height' => 1,
		'width' => 0.5,
		'displayedText' => 'Pi',
		'strokeWidth' => 2
	),
	'as' => array (
		'name' => 'Amarrage simple',
		'height' => 0,
		'width' => 0,
		'displayedText' => '',
		'strokeWidth' => 2,
		'radius' => 3
	),
	'ad' => array (
		'name' => 'Amarrage double',
		'height' => 0,
		'width' => 0,
		'displayedText' => '',
		'strokeWidth' => 2,
		'radius' => 3
	),
	'an' => array (
		'name' => 'Amarrage naturel',
		'height' => 0,
		'width' => 0,
		'displayedText' => 'AN '
	),
	'sa' => array (
		'name' => 'Sapin',
		'height' => 0,
		'width' => 0
	),
	'cr' => array (
		'name' => 'Retour à la ligne',
		'height' => 0,
		'width' => 0
	),
	'to' => array (
		'name' => 'Toboggan',
		'height' => 1,
		'width' => 1.5,
		'displayedText' => 'T',
		'strokeWidth' => 2
	)
);

echo '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

<svg width="' . $pageWidth . 'mm" height="' . $pageHeight . 'mm" version="1.1"
xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
xmlns="http://www.w3.org/2000/svg"
xmlns:xlink="http://www.w3.org/1999/xlink">

<defs>
	<g id="sapin">
		<rect x="45" y="70" width="10" height="20" fill="peru"/>
		<polygon points="20,70 80,70 60,55 70,55 55,40 65,40 50,20 35,40 45,40 30,55 40,55" fill="forestgreen"/>
	</g>
</defs>

';

$origCanyonStr = $canyonStr;
$canyonStr = parsed($canyonStr);
if (isNullOrEmptyString($canyonStr)) {
	error_log ('Error: Empty string. CanyonStr='.$canyonStr);
	exit -1;
}

// Le tout premier caractère est le séparateur dynamique
$separator = substr($canyonStr, 0, 1);
$canyonStr = substr($canyonStr, 1);

error_log ('110:CanyonStr='.$canyonStr);

// On cherche à déterminer la largeur max et la hauteur max cumulées
// afin de pouvoir ajuster la coupe aux dimensions de la page

$curX = 0;
$curY = 0;

// On détermine maxHeight et maxWidth
$strs = explode($separator, $canyonStr);
$maxWidth = 0;
$maxHeight = 0;
foreach($strs as $str) {
	$item = strtolower(substr($str, 0, 2));
	$value = substr($str, 2);
	if (! is_numeric($value)) { $value = 0; }
	$value = abs($value);
	if (array_key_exists('width', $symbols[$item])) {
		$width = $symbols[$item]['width'];
		$curX += ($width * $value);
	}
	if (array_key_exists('height', $symbols[$item])) {
		$height = $symbols[$item]['height'];
		$curY += ($height * $value);
	}
	if ($item == 'cr') {
		$curX = 0;
		$curY = 0;
	}
	if ($curX > $maxWidth) { $maxWidth = $curX; }
	if ($curY > $maxHeight) { $maxHeight = $curY; }
}
$curX = 0;
$curY = 0;
echo '

// maxWidth='.$maxWidth.'
'.'
// maxHeight='.$maxHeight.'
';
$xOffset = 20;
$yOffset = 90;
$pageWidthPx -= $xOffset;
$pageHeightPx -= $yOffset;
$xScale = $pageWidthPx / $maxWidth;
$yScale = $pageHeightPx / $maxHeight;
$curX = $xOffset;
$curY = $yOffset;

$ratio = $xScale / $yScale;
echo '

// xScale='.$xScale.'

// yScale='.$yScale.'

// ratio='.$ratio.'
';

$minRatio = 0.5;
$maxRatio = 2;
// Dans les cas de dépassement de ratio, on force la correction
if ($ratio < $minRatio) {
	echo '
	<!-- !!!!!!!!!! RATIO WARNING : < '. $minRatio .' !!!!!!!!!!! -->
	';
	$yScale = $xScale * 1.5;
}
if ($ratio > $maxRatio) {
	echo '
	<!-- !!!!!!!!!! RATIO WARNING : > '. $maxRatio .' !!!!!!!!!!! -->
	';
	$xScale = $yScale;
}

$ratio = $xScale / $yScale;
echo '

// xScale='.$xScale.'

// yScale='.$yScale.'

// ratio='.$ratio.'
';

echo '
<g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1">';
$displayOrigCanyonStr = preg_replace('/&/', '&amp;', $origCanyonStr);
//if (false) {
if (true) {
echo '
<switch>
<foreignObject x="10" y="0" width="'. $pageWidthPx .'" height="200">
<p xmlns="http://www.w3.org/1999/xhtml" style="font-size:8px">Submitted : '.$canyonName .' : '. $displayOrigCanyonStr.'<br/>Parsed as : '.$canyonName .' : '. $canyonStr.'</p>
</foreignObject>

<text x="20" y="20">Your SVG viewer cannot display html.</text>
</switch>
';
}

foreach($strs as $str) {
	$item = strtolower(substr($str, 0, 2));
	if (array_key_exists('strokeWidth', $symbols[$item])) {
		$strokeWidth = $symbols[$item]['strokeWidth'];
	}
	$value = substr($str, 2);
	$displayedText = '';
	if (array_key_exists('displayedText', $symbols[$item])) {
		$displayedText =  $symbols[$item]['displayedText'] . $value;
	}
	if (is_numeric($value)) {
		$value = abs($value);
	} else {
		$value = 0;
	}
	switch ($item) {
		// Cascade ou Ressaut : sans seuil, juste purement vertical
		case 'cv':
		case 're':
			$bottom = $value * $yScale;
			$yDisplayText = $curY + ($bottom / 2);
			if (($yDisplayText - $fontHeight) < $curY) {
				$yDisplayText += $fontHeight;
			}
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m '. $curX .','. $curY .' 0,'. $bottom .'"
			id="path3311" inkscape:connector-curvature="0" />';
			displayText($displayedText, $curX, $yDisplayText, -5, 0, 'end');
			$curY += $bottom;
			break;
		// Cascade arrondie, avec seuil plongeant
		case 'ca':
		case 'ra':
			$width = $symbols[$item]['width'] * $xScale * 5;
			$bottom = $value * $yScale;
			$yDisplayText = $curY + ($bottom / 2);
			if (($yDisplayText - $fontHeight) < $curY) {
				$yDisplayText += $fontHeight;
			}
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m '. $curX .','. $curY 
			.' c '. $width .',0 '
			.' '. $width .','. $width 
			.' '. $width .','. $width 
			.'l 0,'. ($bottom - $width) .'"
			id="seuil3311" inkscape:connector-curvature="0" />';
			displayText($displayedText, ($curX+$width), $yDisplayText, -5, 0, 'end');
			$curX += $width;
			$curY += $bottom;
			break;
		// Marche courte
		case 'ma':
			$width = $symbols[$item]['width'] * $value * $xScale;
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m '. $curX .','. $curY .' '. $width .',0"
			id="path3311" inkscape:connector-curvature="0" />';
			//displayText($displayedText, $curX, $curY, 10, 20);
			$curX += $width;
			break;
		// Marche longue
		case 'ml':
			$width = $symbols[$item]['width'] * $value * $xScale;
			$longWalkHeight = 150;
			$longWalkAngle = 20;
			$longWalkWidth = 10;
			$width = $longWalkWidth;
			// Un petit trait horizontal qui précède
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m '. $curX .','. $curY .' '. $longWalkWidth .',0"
			id="path3311" inkscape:connector-curvature="0" />';
			$curX += $longWalkWidth;
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:8,4,2,4;stroke-dashoffset:0"
			d="m '. ($curX-$longWalkAngle) .','. ($curY+($longWalkHeight/2)) .' '. $longWalkAngle*2 .','. -($longWalkHeight*1).'"
			id="path3311" inkscape:connector-curvature="0" />';
			$curX += $longWalkWidth;
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:8,4,2,4;stroke-dashoffset:0"
			d="m '. ($curX-$longWalkAngle) .','. ($curY+($longWalkHeight/2)) .' '. $longWalkAngle*2 .','. -($longWalkHeight*1).'"
			id="path3311" inkscape:connector-curvature="0" />';
			// Un petit trait horizontal qui suit
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m '. $curX .','. $curY .' '. ($longWalkWidth*2.5) .',0"
			id="path3311" inkscape:connector-curvature="0" />';
			$curX += ($longWalkWidth*2.5);
			displayText(($displayedText . 'm'), ($curX-$longWalkAngle), ($curY+($longWalkHeight/2)), -5, 20, 'end');
			break;
		// Vasque
		case 'va':
			$width = $symbols[$item]['width'] * $value * $xScale;
			$depth = 2 * $yScale;
			echo '
			<path style="fill:#0077FF;fill-opacity:1;stroke:none"
			d="m '. $curX .','. $curY .' c 0,'. $depth .' '. $width / 2 .',0 '. $width .',0"
			id="path3223-1" inkscape:connector-curvature="0" />
			<path style="fill:none;stroke:#000000;stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m '. $curX .','. $curY .' c 0,'. $depth .' '. $width / 2 .',0 '. $width .',0"
			id="path3311" inkscape:connector-curvature="0" />
			';
			//displayText($displayedText, $curX, $curY, 10, -10);
			$curX += $width;
			break;
		// Plan incliné
		case 'pi':
			$width = $symbols[$item]['width'] * $xScale;
			$bottom = $symbols[$item]['height'] * $value * $yScale;
			$yDisplayText = $curY + ($bottom / 2);
			if (($yDisplayText - $fontHeight) < $curY) {
				$yDisplayText += $fontHeight;
			}
			echo '
			<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m '. $curX .','. $curY .' '. $width .','. $bottom .'"
			id="path3311" inkscape:connector-curvature="0" />';
			displayText($displayedText, $curX, $yDisplayText, -5, 6, 'end');
			$curX += $width;
			$curY += $bottom;
			break;
		// Toboggan
		case 'to':
			$width = $symbols[$item]['width'] * $value * $xScale;
			$bottom = $symbols[$item]['height'] * $value * $yScale;
			$yDisplayText = $curY + ($bottom / 2);
			if (($yDisplayText - $fontHeight) < $curY) {
				$yDisplayText += $fontHeight;
			}
			echo '
			<path style="fill:none;stroke:#' . randomColor() . ';stroke-width:'. $strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
			d="m ' . $curX . ',' . $curY . ' ' . $width . ',' . $bottom . '"
			id="path3311" inkscape:connector-curvature="0" />';
			displayText($displayedText, $curX, $yDisplayText, ($width/3), 6, 'end');
			$curX += $width;
			$curY += $bottom;
			break;
		// Amarrage simple
		case 'as':
			$xAsOffset = 1.3 * $xScale;
			$yAsOffset = 1.3 * $yScale;
			$radius = $symbols[$item]['radius'];
			echo '
			<circle cx = "'. ($curX + $xAsOffset) .'" cy = "'. ($curY - $yAsOffset) .'" r = "'. $radius .'" fill = "#ffffff" fill-opacity = "1" stroke = "black" stroke-width = "'. $strokeWidth .'px"/>';
			displayText($displayedText, ($curX + $xAsOffset), ($curY - $yAsOffset), 0, -7, 'middle');
			break;
		// Amarrage double
		case 'ad':
			$xAsOffset = 1.3 * $xScale;
			$yAsOffset = 1.3 * $yScale;
			$radius = $symbols[$item]['radius'];
			echo '
			<circle cx = "'. ($curX + $xAsOffset) .'" cy = "'. ($curY - $yAsOffset) .'" r = "'. $radius .'" fill = "none" stroke = "black" stroke-width = "'. $strokeWidth .'px"/>
			<circle cx = "'. ($curX + $xAsOffset + (4 * $radius)) .'" cy = "'. ($curY - $yAsOffset) .'" r = "'. $radius .'" fill = "none" stroke = "black" stroke-width = "'. $strokeWidth .'px"/>';
			displayText($displayedText, ($curX + $xAsOffset+ (2 * $radius)), ($curY - $yAsOffset), 0, -7, 'middle');
			break;
		// Amarrage naturel
		case 'an':
			$xAsOffset = 3.2 * $xScale;
			$yAsOffset = 2 * $yScale;
			displayText($displayedText, ($curX + $xAsOffset), ($curY - $yAsOffset), -10, 5, 'start');
			break;
		// Sapin
		case 'sa':
			$xAsOffset = 0 * $xScale - 60;
			$yAsOffset = 0 * $yScale - 90;
			//echo '<use xlink:href="#sapin" transform="translate('.($curX + $xAsOffset).','.($curY + $yAsOffset).')"/>';
			echo '<use xlink:href="#sapin" x="'.($curX + $xAsOffset).'" y="'.($curY + $yAsOffset).'"/>';
			break;
		// Carriage return
		case 'cr':
			$curX = $xOffset;
			$curY -= $value;
			break;
	}
}
echo '
</g>

</svg>';
?>
