<?php
header('Content-Type: image/svg+xml');

include 'parser.php';

// TODO
// Scinder la coupe en n parties, retour à la ligne - Proposition Rémi Villalongue
// Décider de garder ou non la distinction Pi / To
// Coder un vrai parser


// 20130916
// Ajout du header
// Ajout du separateur dynamique
// Casse insensitive des symboles - Bug levé par Maxime Cassan - Max38
// Placement texte des plans inclinés -  Bug levé par Maxime Cassan - Max38
// Placement texte des toboggans -  Bug levé par Maxime Cassan - Max38
// Correction de la maxWidth si une marche longue termine la topo - Bug levé par Fr3d0
// Correction de l'espacement entre les doubles points dans les topos courtes - Bug levé par Fabien Mullet - Fabien

// 20130912
// Protection contre les valeurs négatives - Bug levé par Fabien Mullet
// Repositionnement des textes des ressauts et cascades arrondies
// Repositionnement des textes des amarrages
// Ajout des marches longues - Proposition Rémi Villalongue, assistance Fabien Mullet


// Tentative de compression de la chaîne
//function _encode_string_array ($stringArray) {
//	$s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray),9))), '+/=', '-_,');
//	return $s;
//}

//function _decode_string_array ($stringArray) {
//	$s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '-_,', '+/=')))));
//	return $s;
//}

//$secret = 'je suis une chaine tres longue que je dois compresser';
//$compacted = _encode_string_array($secret);
//$decompacted = _decode_string_array($compacted);
//error_log('origin='.$secret);
//error_log('compacted='.$compacted);
//error_log('decompacted='.$decompacted);

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
	//return;
	//$text = strtoupper($text);
	//$text = strtolower($text);
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

$furonExpress = '/to3/va2/To5/va10/ma5/to4/va5/ma15/cv8/va10/cv5/va10/ma2/re1/ma2/re1/ma10/re1/va10/ma3';
$furonExpress = '/to3/va5/ma5/to4/va10/ma15/to3/va10/ma15/cv8/va10/to5/va10/ma2/re1/ma6/re1/ma10/re1/va10/ma3';
$furonExpress = '/to3/va5/ma5/to4/va10/ma15/to3/va10/ma15/ca8/va10/to5/va10/ma2/re1/ma6/re1/ma10/re1/va10/ma3';
$canyonStr = '/cv5/cv5';
$canyonVertical = '/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma10';
$canyonTresVertical = '/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma1/cv20/ma10';
$canyonHorizontal = '/ma20/cv1/ma20/cv1/ma20/cv1/ma20';
$canyonStr = $canyonHorizontal;
$canyonStr = $canyonTresVertical;
$canyonStr = '/ma2/cv2/ma2/RE2/ma2/va2/pi2.5/ma2/to2';
$canyonStr = '/cv10/ma5/cv5/ma2/cv5/va10/ma5';
$canyonStr = '/cv10/mA10/cv6/pi5/va2/cv15/to5';
$canyonStr = '/ma1/cv1/ma1/RE1/ma1/va1/pi1/ma1/to1';
$rouanne = '/ma2/re3/va3/ma10/re3/va3/re4/va3/re3/va3/re4/va3/ma10/ca50/va3/ma10/cv7/va3/cv4/va3/cv4/va3/cv7/va3/ma20/cv4/va3/to11/va3/ca9/va3/ma5';
$rouanne = '/ma2/re3/va3/ma10/re3/va3/re4/va3/re3/va3/re4/va3/ma40/ca10/va3/ma10/ca7/va3/ca4/va3/ca4/va3/ca7/va3/ma40/ca4/va3/to11/va3/ca9/va3/ma5';
$canyonStr = '/ma2/ca2/ma2/RE2/ma2/va2/ma2/pi2/ma2/to2';
$canyonStr = '/ma5/ca5/ma20/ca10/to7/va10/re1.5/ca7/va5/ma5';
$canyonStr = $canyonVertical;
$canyonStr = $rouanne;
$canyonStr = '/ma2/cv2/ma2/RE2/ma2/ca2/va2/ma2/pi2/ma2/to2';
$trefond = '/ma1/va3/ma3/to3/ma3/ra2/ma2/ra2/ma2/ra3/ma5/to1/va3/ma1/to2/va2/ma1/to3/va2/ma3/to2/va3/ma3/ra3/va2/ma4/ca3/va3/ma6/ca4/va3/ma8/cv10/va10/ma10/ra2/va6/ma5/re2/va4/ma5/va6/ma15/ra2/va4/ma6/cv7/va7/ma5/re1/ma5/re2/ma6/cv8/va6/ma3/va3/ma2';
// Vrai
$laire = '/ma4/va2/ma4/va4/ma3/to1/va2/ma6/ra5/va5/ma4/anrd/ca20/va4/ma5/ra3/ma2/admc/ma15/adrg/ca26/ra2/va3/ma4/va3/ma4/va3/ma4/ra3/va2/asrd/ca21/va5/an/ca13/va3/ml800/ra3/va2/ma5/va2/ma2/va3/asrg/ca15/va5/ma2/re3/va5/ml700/ad/ca28/va5/ma4/va3/ma5';
// Bidouillé
$laireB = '/ma4/va2/ma4/va4/ma3/to1/va2/ma6/re5/va5/ma4/anrd/cv20/va4/ma5/re3/ma7/adrg/cv16/re2/va3/ma4/va3/ma4/va3/ma4/re3/va2/asrd/cv21/va5/an/cv13/va3/ma2/re3/va2/ma5/va2/ma2/va3/asrg/cv15/va5/ma2/re3/va5/ml700/ma6/ad/cv28/va5/ma4/va3/ma5';
$laireB = '/ma4/va2/ma4/va4/ma3/to1/va2/ma6/re5/va5/ma4/anrd/ca20/va4/ma5/ra3/ma7/adrg/cv16/ra2/va3/ma4/va3/ma4/va3/ma4/re3/va2/asrd/cv21/va5/an/cv13/va3/ma2/re3/va2/ma5/va2/ma2/va3/asrg/cv-15/va5/ma2/re4/va5/ml700/ma6/ad/cv28/va5/ma4/va3/ma5';
$canyonName = 'Trefond-Pernaz';
$canyonName = 'Saut du Laïre';
$canyonName = 'Baranc del Norte';
$canyonStr = 'ma1';
$canyonStr = '/to3/va2/ma5/to5/va10/ma5/to4/va5/ma15/asrd/ca8/va10/ca5/va10/ma2/re1.2/ma2/re1/ma10/re1/va10/ma3.5';
$canyonStr = '/ma5/cv3/ma5/re3/ma5/re3/va5/ml800/va5/ma5/re2/ma5';
$canyonStr = '::ma5/cv3/ma5/re3/ma5/re3/va5/ml800/va5/ma5/re2/ma5';
$canyonStr = 'fr1.1:/ma5/cv3/ma5/re3/ma5/re3/va5/ml800/va5/ma5/re2/ma5';
$canyonStr = 'fr1.1:,ma5,cv3,ma5,re3,ma5,re3,va5,ml800,va5,ma5,re2,ma5';
$canyonStr = 'fr1.1:/ma5/adrd/ca5/va5/ma10/to3/cv2/va5/ma5/adrg/ca27';
$canyonStr = $furonExpress;
$canyonStr = $trefond;
$canyonStr = $laire;
$canyonStr = '/ma2/cv2/ma2/RE20/ma20/va2/ma5/pi2/ma20/to6/ma5';
$canyonStr = 'fr1.1:/ma5/adrd/ca5/va5/ma10/to3/va5/ma5/adrg/ca27';
$canyonStr = 'fr1:/ma5/ca5/va5/ml800/cv10/va5/re4/ma10/to3/va5/ma5/va4/ml500';
$canyonStr = 'fr1.1:/ma10/re4/ma5/re2/va3/ma15/re2/va5/cv5/va3/cv3/va10/re1/va2/va2/ma15/to3/va1/ma30/ca5/va10/ma30/re3/va5/to4/va5/to3/va5/ca12/va10/ma5/pi5/ml100/re3/va5/ml200/va50';
$canyonStr = 'fr1.1:/ma10/re4/mi5/re2/ma15';
$canyonStr = 'en1.1:/wa10/re4/wa5/we2/sl15/po5';
$canyonStr = 'fr1.1:/ma10/re4/mi5/ca2/to15/va6';
$canyonStr = 'fr1.1:/ma5/ca5/va5/ml800/cv10/va5/re4/ti20/ma10/to3/va5/ma5';

$canyonStr = 'fr1.1:/ma5/ca5/va5/ml800/ma5/adrg/ca10/va5/ma5/sa/re4/ma10/sa/to3/va4/ml500';
// Syntaxe simplifiée version 0 - Français
$canyonStr = 'fr0.1:/m5/c5/v5/m8/m5/arg/c10/v5/m5/s/r4/m10/s/t3/v4/m5';
$canyonStr = 'fr0.1:/m5/c5/v5/m8/m5/arg/c10/v5/m5/s/r4/m10/s/t3/v4/m5';
$canyonStr = 'fr0.1:/m5/c5/v5/m8/m5/arg/c10/v5/m5/s/r4/m10/s/t3/v4/m5';
$canyonStr = 'es0.1:/s5/c5/b5/s8/s5/arg/c10/b5/s5/p/r4/s10/p/t3/b4/s5';
$canyonStr = $laire;

$origCanyonStr = $canyonStr;
$canyonStr = parsed($canyonStr);
if (! is_string($canyonStr)) {
	exit -1;
}
// Le tout premier caractère est le séparateur dynamique
$separator = substr($canyonStr, 0, 1);
$canyonStr = substr($canyonStr, 1);

error_log ('CanyonStr='.$canyonStr);

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
		$maxWidth += ($width * $value);
	}
	if (array_key_exists('height', $symbols[$item])) {
		$height = $symbols[$item]['height'];
		$maxHeight += ($height * $value);
	}
}

echo '

// maxWidth='.$maxWidth.'
'.'
// maxHeight='.$maxHeight.'
';
$xOffset = 20;
$yOffset = 80;
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

//if (false) {
if (true) {
echo '
<switch>
<foreignObject x="10" y="0" width="'. $pageWidthPx .'" height="200">
<p xmlns="http://www.w3.org/1999/xhtml">Submitted : '.$canyonName .' : '. $origCanyonStr.'</p>
<p xmlns="http://www.w3.org/1999/xhtml">Parsed as : '.$canyonName .' : '. $canyonStr.'</p>
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
			displayText(strtoupper($displayedText), ($curX + $xAsOffset), ($curY - $yAsOffset), 0, -7, 'middle');
			break;
		// Amarrage double
		case 'ad':
			$xAsOffset = 1.3 * $xScale;
			$yAsOffset = 1.3 * $yScale;
			$radius = $symbols[$item]['radius'];
			echo '
			<circle cx = "'. ($curX + $xAsOffset) .'" cy = "'. ($curY - $yAsOffset) .'" r = "'. $radius .'" fill = "none" stroke = "black" stroke-width = "'. $strokeWidth .'px"/>
			<circle cx = "'. ($curX + $xAsOffset + (4 * $radius)) .'" cy = "'. ($curY - $yAsOffset) .'" r = "'. $radius .'" fill = "none" stroke = "black" stroke-width = "'. $strokeWidth .'px"/>';
			displayText(strtoupper($displayedText), ($curX + $xAsOffset+ (2 * $radius)), ($curY - $yAsOffset), 0, -7, 'middle');
			break;
		// Amarrage naturel
		case 'an':
			$xAsOffset = 2 * $xScale;
			$yAsOffset = 2 * $yScale;
			displayText(strtoupper($displayedText), ($curX + $xAsOffset), ($curY - $yAsOffset), -10, 5, 'start');
			break;
		// Sapin
		case 'sa':
			$xAsOffset = 0 * $xScale - 60;
			$yAsOffset = 0 * $yScale - 90;
			//echo '<use xlink:href="#sapin" transform="translate('.($curX + $xAsOffset).','.($curY + $yAsOffset).')"/>';
			echo '<use xlink:href="#sapin" x="'.($curX + $xAsOffset).'" y="'.($curY + $yAsOffset).'"/>';
			break;
	}
}
echo '
</g>

</svg>';
?>
