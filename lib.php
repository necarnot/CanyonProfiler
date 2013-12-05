<?php

function includeProfile($canyonStr) {
	$p = new Profile();
	$p->pageWidthPx -= $p->xEndOffset;
	$p->pageHeightPx -= $p->yEndOffset;
	$outDir = 'profiles';
	$outFile = 'outfile_' . uniqid() . '.svg';
	$curFileName = $outDir . '/' . $outFile;
	$p->fileHandle = fopen($curFileName, 'w+') or die("Can't open file:".$curFileName);
	$p->appendToFile('<?xml version="1.0" standalone="no"?>
	<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

	<svg width="' . $p->pageWidth . 'mm" height="' . $p->pageHeight . 'mm" version="1.1"
		xmlns="http://www.w3.org/2000/svg"
		xmlns:xlink="http://www.w3.org/1999/xlink">
	');
	$p->parse($canyonStr);
	$p->chainItems();
	$p->scale();

	$displayOrigCanyonStr = preg_replace('/&/', '&amp;', $p->origCanyonStr);
	$p->draw();
	$p->getDefs();
	$p->appendToFile('
	</svg>');
	fclose($p->fileHandle);
	if (1) {
		echo '
		<p style="font-size:8px"><b>Submitted :</b> '.$p->canyonName .' : '. $displayOrigCanyonStr.'<br/>
		<b>Parsed as :</b> '.$p->canyonName .' : '. $p->canyonStr.'</p>';
	}
	echo '
	<a href="'.$curFileName.'" style="display: block">
	<object data="'.$curFileName.'" style="pointer-events:none"></object>
	</a>

	';
}

// Function for basic field validation (present and neither empty nor only white space)
function isNullOrEmptyString($question){
	return (!isset($question) || trim($question)==='');
}

function getWaterColor() {
	return '0078FF';
}

function getAnchorColor() {
	return 'FF0000';
}

function getRandomColor() {
	mt_srand((double)microtime()*1000000);
	$c = '';
	while(strlen($c)<6){
		$c .= sprintf("%02X", mt_rand(0, 255));
	}
	return $c;
}

function plf ($chaine) {
	print $chaine . "\n";
}

function top() {
	plf('<?xml version="1.0" encoding="iso-8859-15"?>');
	plf('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
	plf('<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">');
	plf('  <head>');
	plf('    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />');
	plf('    <title>CanyonProfiler</title>');
	//plf('    <link rel="stylesheet" href="styles.css" />');
	plf('  </head>');
	plf('  <body>');
}

function bottom () {
	plf ('  </body>');
	plf ('</html>');
}

class Profile {
	public $fontHeight = 12;
	public $pageWidth = 297;
	public $pageHeight = 210;
	public $pageWidthPx = 1052.36;
	public $pageHeightPx = 744.09;
	public $xEndOffset = 40;
	public $yEndOffset = 40;
	public $xOffset = 20;
	public $yOffset = 90;
	public $maxWidth = 0;
	public $maxHeight = 0;
	public $xScale = 0;
	public $yScale = 0;
	public $curX = 0;
	public $curY = 0;
	public $minX = 9999999999;
	public $minY = 9999999999;
	public $maxX = 0;
	public $maxY = 0;
	public $curColor = '000000';
	public $origCanyonStr = '';
	public $canyonStr = '';
	public $defaultVersion = 'fr2.1';
	public $neededDefs = array ();

	public $syntaxVersion = '';
	public $items = array ();
	public $fileHandle;

	public $canyonName = '';

	public $allowedOptions = array('canyonName', 'fontHeight', 'belowBackground', 'aboveBackground');
	public $belowBackground = TRUE;
	public $aboveBackground = TRUE;

	public $layers = array (
		'base' => '',
		'water' => '',
		'vegetal' => '',
		'anchors' => '',
		'text' => '',
		'infos' => '',
		'overall' => '',
	);
	public $baseLayer = array (
		'direct' => array(),
		'reverse' => array(),
	);

	public function appendToFile($text) {
		fwrite($this->fileHandle, $text);
	}

	public function displayText($text, $xText, $yText, $xOffset, $yOffset, $align = 'start') {
		if (isNullOrEmptyString($text)) {
			return;
		}
		$xText += $xOffset;
		$yText += $yOffset;
		$this->appendToLayer('text','
		<g font-size="16" font-family="sans-serif" fill="black" stroke="none" text-anchor="'. $align .'">
			<text x="' . $xText . '" y="' . $yText . '">' . $text . '</text>
		</g>');
	}

	public function appendToLayer($layer, $text, $reverse = '') {
		if ($layer == 'base') {
			array_push($this->baseLayer['direct'], $text);
			array_push($this->baseLayer['reverse'], $reverse);
		} else {
			$this->layers[$layer] .= $text;
		}
	}

	public function getCurColor() {
		return $this->curColor;
	}

	public function parse($str) {
		$this->origCanyonStr = $str;
		$this->canyonStr = parsed($this, $str);
		if (isNullOrEmptyString($this->canyonStr)) {
			error_log ('Error: Empty string. CanyonStr='.$this->canyonStr);
			exit(-1);
		}
	}

	public function chainItems() {
		// Linking to the next item
		$nbItems = count($this->items);
		for ($i = 0; $i < ($nbItems-1); $i++) {
			//error_log('>chaining:i='.$i.',itemName='.$this->items[$i]->name);
			$this->items[$i]->nextItem = &$this->items[$i+1];
		}
		for ($i = ($nbItems-1); $i > 0 ; $i--) {
			//error_log('<chaining:i='.$i.',itemName='.$this->items[$i]->name);
			$this->items[$i]->prevItem = &$this->items[$i-1];
		}
	}

	// On cherche à déterminer la largeur max et la hauteur max cumulées
	// afin de pouvoir ajuster la coupe aux dimensions de la page
	public function scale() {
		$curX = 0;
		$curY = 0;
		$minX = 0;
		$minY = 0;
		$maxX = 0;
		$maxY = 0;
		// On détermine maxHeight et maxWidth
		foreach($this->items as $item) {
			//error_log('1 : '.get_class($item).' curX='.$curX.' width='.$item->width.' itemWidthFactor='.$item->widthFactor.' minX='.$minX);
			if (get_class($item) == 'CarriageReturn') {
				$curX = $minX;
			} else {
				$curX += $item->width * $item->widthFactor;
			}
			$curY += $item->height * $item->heightFactor;
			$minX = min($minX, $curX);
			$minY = min($minY, $curY);
			$maxX = max($maxX, $curX);
			$maxY = max($maxY, $curY);
			//error_log('2 : curX='.$curX.' minX='.$minX);
			//$this->appendToFile('
			//<!-- str='.get_class($item).'|'.$item->width.'|'.$item->height.' itemWidthFactor='.$item->widthFactor. ' itemHeightFactor='.$item->heightFactor.' minX='.$minX.' minY='.$minY.' maxX='.$maxX.' maxY='.$maxY.' -->');
		}
		$this->maxWidth = $maxX - $minX;
		$this->maxHeight = $maxY - $minY;

		$this->appendToFile('
		<!-- maxWidth='.$this->maxWidth.' '.' maxHeight='.$this->maxHeight.' -->');
		$this->pageWidthPx -= $this->xOffset;
		$this->pageHeightPx -= $this->yOffset;
		$this->xScale = $this->pageWidthPx / $this->maxWidth;
		$this->yScale = $this->pageHeightPx / $this->maxHeight;
		// On remet en place les positions de départ
		$this->curX = $this->xOffset - ($minX*$this->xScale);
		$this->curY = $this->yOffset - ($minY*$this->yScale);;

		$ratio = $this->xScale / $this->yScale;
		$this->appendToFile('
		<!-- xScale='.$this->xScale.' // yScale='.$this->yScale.' // ratio='.$ratio.' -->');
		$minRatio = 0.5;
		$maxRatio = 2;
		// Dans les cas de dépassement de ratio, on force la correction
		if ($ratio < $minRatio) {
			$this->appendToFile('
			<!-- !!!!!!!!!! RATIO WARNING : < '. $minRatio .' !!!!!!!!!!! --> ');
			$this->yScale = $this->xScale * 1.5;
		}
		if ($ratio > $maxRatio) {
			$this->appendToFile('
			<!-- !!!!!!!!!! RATIO WARNING : > '. $maxRatio .' !!!!!!!!!!! --> ');
			$this->xScale = $this->yScale * 2;
		}
		$ratio = $this->xScale / $this->yScale;
		$this->appendToFile('
		<!-- xScale='.$this->xScale.' // yScale='.$this->yScale.' // ratio='.$ratio.' -->');

		foreach($this->items as $item) {
			$item->scale($this->xScale, $this->yScale);
		}
	}

	public function draw() {
		// We request every item to draw its graphical part into its dedicated layer
		foreach($this->items as $item) {
			$item->draw($this);
		}

		// We draw every layer in the respective z order
		foreach($this->layers as $layerName => $layerText) {
			if ($layerName == 'base') {
				// For the base layer, we :
				// - keep the original path that will be drawn on top of this layer (last one)
				// - create a shape based on the original one, with a diagonal offset
				// - put a version of this box below the base path, and fill it with the below color shade
				// - put a version of this box above the base path, and fill it with the above color shade

				$layerText = '';
				$oppositeText = '';
				foreach($this->baseLayer['direct'] as $index => $text) {
					if(preg_match('/m\s*\d*\.*\d*,\d*\.*\d*\s*(.*)/',$text,$matches)) {
						if ($index == 0) {
							$layerText .= $text;
						} else {
							// If this path is not the first one : we remove the moveto command
							$layerText .= "\n\t\t\t" . $matches[1];
						}
						$opposite = $this->baseLayer['reverse'][$index];
						$oppositeText = $opposite . "\n\t\t\t" . $oppositeText;
					} else {
						// Used for carriage returns
						$layerText .= "\n\t\t\t" . $text;
					}
				}
				$belowText = '<path style="fill:#ae6a5a;fill-opacity:1;stroke:none;filter:url(#filterBelow)" d="m20,-20 ' . $layerText . ' l-40,40 ' . $oppositeText . ' z ';
				$aboveText = '<path style="fill:#b5b5b5;fill-opacity:1;stroke:none;filter:url(#filterAbove)" d="m-3,3 ' . $layerText . ' l80,-80 ' . $oppositeText . ' z ';
				$layerText = '<path style="fill:none;stroke:#000000;stroke-width:2px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1" d=" ' . $layerText;
				$layerText .= '" />';
				$belowText .= '" />';
				$aboveText .= '" />';

				if($this->belowBackground == FALSE) { $belowText = ''; }
				if($this->aboveBackground == FALSE) { $aboveText = ''; }

				// Final concatenation
				$layerText = "\n" . $belowText . "\n" . $aboveText . "\n" . $layerText;
			}
			if(isNullOrEmptyString($layerText)) {
				continue;
			}
			$this->appendToFile('
	<g id="'.$layerName.'"> '.$layerText.'
	</g>
			');
		}
	}

	public function getDefs() {
		//if(empty($this->neededDefs)) {
		//	return;
		//}
		$this->appendToFile('
	<defs>');
		foreach($this->neededDefs as $neededDef => $kickme) {
			$neededDef::getDef($this);
		}
		$this->appendToFile('
		    <filter id="filterBelow">
		      <feGaussianBlur stdDeviation="25" />
		    </filter>
		    <filter id="filterAbove">
		      <feGaussianBlur stdDeviation="10" />
		    </filter>
	</defs>');
	}

	public function setOptions($optionsStr) {
		$pairs = explode(',', $optionsStr);
		foreach($pairs as $pair) {
			$pair = trim($pair);
			$optParts = explode('=', $pair);
			if(count($optParts) != 2) { continue; }
			$key = trim($optParts[0]);
			$value = trim($optParts[1]);
			if(isNullOrEmptyString($key) || isNullOrEmptyString($value)) { continue; }
			if (in_array($key, $this->allowedOptions)) {
				$this->$key = $value;
				error_log('--------> Setting '.$key.'='.$value);
			}
		}
	}

}

class Item {
	public $name;
	public $prevItem;
	public $nextItem;
	public $height;
	public $width;
	public $heightFactor;
	public $widthFactor;
	public $drawedHeight;
	public $drawedWidth;
	public $displayedText;
	public $strokeWidth = 2;
	public $inStr = '';
	public $symbolLetter = '';

	function __construct() {
	}

	public function getNextItemClass() {
		if ( isset($this->nextItem) ) {
			return get_class($this->nextItem);
		}
		return null;
	}

	public function setInStr($str) {
		$this->inStr = $str;
	}

	// Input strings whose first letter is uppercase will be displayed. Others won't.
	public function setDisplayedText($text) {
		$this->displayedText = '';
		if (ctype_upper(substr($this->inStr,0,1))) {
			$this->displayedText = $text;
		}
	}

	public function scale($xScale, $yScale) {
		$this->drawedWidth  = $this->width  * $this->widthFactor  * $xScale;
		$this->drawedHeight = $this->height * $this->heightFactor * $yScale;
	}

	public function draw(&$p) {
	}
}

class VerticalAngle extends Item {
	function __construct($height, $angle) {
		$height = abs($height);
		$angle = abs($angle);
		parent::__construct();
		$this->name = 'VerticalAngle';
		$this->heightFactor = 1;
		$this->widthFactor = 1;

		$this->height = $height;
		$this->symbolLetter = 'C';

		$this->width = ($angle == 0) ? 0 : $height / tan(deg2rad($angle));
	}

	public function draw(&$p) {
		$this->setDisplayedText($this->symbolLetter . $this->height);
		$yDisplayText = $p->curY + ($this->drawedHeight / 2) + ($p->fontHeight / 2);
		if (($yDisplayText - $p->fontHeight) < $p->curY) {
			$yDisplayText += $p->fontHeight;
		}
		$p->appendToLayer('base',
		'm ' . $p->curX . ',' . $p->curY . ' l' . $this->drawedWidth . ',' . $this->drawedHeight,
		'l' . -$this->drawedWidth . ',' . -$this->drawedHeight);
		$p->displayText($this->displayedText, $p->curX, $yDisplayText, ($this->drawedWidth / 2) - ($p->xScale * 0.8), $this->drawedWidth * 0.09, 'end');
		$p->curX += $this->drawedWidth;
		$p->curY += $this->drawedHeight;
		$p->minX = min($p->minX, $p->curX);
	}
}

class WetAngle extends VerticalAngle {
	function __construct($height, $angle) {
		parent::__construct($height, $angle);
		$this->name = 'Wet Angle';
	}

	public function draw(&$p) {
		$origCurX = $p->curX;
		$origCurY = $p->curY;
		parent::draw($p);
		$offsetNextItem = 0;
		if ( preg_match('/.*Walk.*/', $this->getNextItemClass()) OR preg_match('/.*Rounded.*/', $this->getNextItemClass()) ) {
			$offsetNextItem = $this->strokeWidth;
		}
		$p->appendToLayer('water','
		<path style="fill:none;stroke:#'. getWaterColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m ' . ($origCurX + $this->strokeWidth) . ',' . $origCurY . ' ' . max($this->drawedWidth,0) . ',' . ($this->drawedHeight - $offsetNextItem) . '" />');
	}
}

class WetVertical extends WetAngle {
	function __construct($height) {
		parent::__construct($height, 0);
		$this->name = 'Wet vertical';
	}
}

class WetLeanedVertical extends WetAngle {
	function __construct($height) {
		parent::__construct($height, 22.5);
		$this->name = 'Wet Leaned Vertical';
	}
}

class WetObliqueVertical extends WetAngle {
	function __construct($height) {
		parent::__construct($height, 67.5);
		$this->name = 'Wet Oblique Vertical';
	}
}

class WetSlightOverhangingVertical extends WetAngle {
	function __construct($height) {
		parent::__construct($height, 112.5);
		$this->name = 'Wet Slight Overhanging Vertical';
	}
}

class WetOverhangingVertical extends WetAngle {
	function __construct($height) {
		parent::__construct($height, 157.5);
		$this->name = 'Wet Overhanging Vertical';
	}
}

class Vertical extends VerticalAngle {
	function __construct($height) {
		parent::__construct($height, 0);
		$this->name = 'Vertical';
	}
}

class LeanedVertical extends VerticalAngle {
	function __construct($height) {
		parent::__construct($height, 22.5);
		$this->name = 'Leaned Vertical';
	}
}

class ObliqueVertical extends VerticalAngle {
	function __construct($height) {
		parent::__construct($height, 67.5);
		$this->name = 'Oblique Vertical';
	}
}

class SlightOverhangingVertical extends VerticalAngle {
	function __construct($height) {
		parent::__construct($height, 112.5);
		$this->name = 'Slightly Overhanging Vertical';
	}
}

class OverhangingVertical extends VerticalAngle {
	function __construct($height) {
		parent::__construct($height, 157.5);
		$this->name = 'Overhanging Vertical';
	}
	public function draw(&$p) {
		parent::draw($p);
		$p->belowBackground = FALSE;
		$p->aboveBackground = FALSE;
	}
}

class Slide extends VerticalAngle {
	function __construct($height) {
		parent::__construct($height, 22.5);
		$this->name = 'Slide';
		$this->symbolLetter = 'T';
	}
}

class RoundedVertical extends Vertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Rounded vertical';
		$this->widthFactor = 0.2;
		$this->width = $height;
	}

	public function draw(&$p) {
		$this->setDisplayedText($this->symbolLetter . $this->height);
		$yDisplayText = $p->curY + ($this->drawedHeight / 2) + ($p->fontHeight / 1);
		// Arbitrarily specify the round width
		$curveWidth = $this->drawedWidth / 2;
		$p->appendToLayer('base',
		'm '. $p->curX .','. $p->curY
		.' c'. $curveWidth .',0'
		.' '. $curveWidth .','. $curveWidth
		.' '. $curveWidth .','. $curveWidth
		.' v'. ($this->drawedHeight - $curveWidth),
		'v'. -($this->drawedHeight - $curveWidth)
		.' c0,'. -$curveWidth
		.' '. -$curveWidth .','. -$curveWidth
		.' '. -$curveWidth .','. -$curveWidth);
		$p->displayText($this->displayedText, ($p->curX + $curveWidth), $yDisplayText, -5, 0, 'end');
		$p->curX += $curveWidth;
		$p->minX = min($p->minX, $p->curX);
		$p->curY += $this->drawedHeight;
	}
}

class WetRoundedVertical extends RoundedVertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Wet Rounded Vertical';
	}

	public function draw(&$p) {
		$origCurX = $p->curX;
		$origCurY = $p->curY;
		parent::draw($p);
		$offsetNextItem = 0;
		if ( preg_match('/.*Walk.*/', $this->getNextItemClass()) OR preg_match('/.*Rounded.*/', $this->getNextItemClass()) ) {
			$offsetNextItem = $this->strokeWidth;
		}
		// Arbitrarily specify the round width
		$curveWidth = ($this->drawedWidth / 2) + $this->strokeWidth;
		// TODO : Faire démarrer l'arrondi 2px plus loin si le previous est un vertical. Bon courage.
		$p->appendToLayer('water','
		<path style="fill:none;stroke:#'. getWaterColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $origCurX .','. ($origCurY - $this->strokeWidth)
		.' c '. $curveWidth .',0'
		.' '. $curveWidth .','. $curveWidth
		.' '. $curveWidth .','. $curveWidth
		.'l 0,'. ($this->drawedHeight - $curveWidth + $this->strokeWidth - $offsetNextItem) .'" />');
	}
}

class DownClimb extends Vertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'DownClimb';
		$this->symbolLetter = 'R';
	}
}

class RoundedDownClimb extends RoundedVertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Rounded downclimb';
		$this->symbolLetter = 'R';
	}
}

class LeanedDownClimb extends LeanedVertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Leaned downclimb';
		$this->symbolLetter = 'R';
	}
}

class ObliqueDownClimb extends ObliqueVertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Oblique downclimb';
		$this->symbolLetter = 'R';
	}
}

class SlightOverhangingDownClimb extends SlightOverhangingVertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'SlightOverhanging downclimb';
		$this->symbolLetter = 'R';
	}
}

class OverhangingDownClimb extends OverhangingVertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Overhanging downclimb';
		$this->symbolLetter = 'R';
	}
}

class Walk extends Item {
	function __construct($width) {
		$width = abs($width);
		parent::__construct();
		$this->name = 'Walk';
		$this->heightFactor = 0;
		$this->widthFactor = 1;

		$this->width = $width;
	}

	public function draw(&$p) {
		$p->appendToLayer('base',
		'm '. $p->curX .','. $p->curY .' h'. $this->drawedWidth,
		'h'. -$this->drawedWidth);
		$p->minX = min($p->minX, $p->curX);
		$p->curX += $this->drawedWidth;
	}
}

class WetWalk extends Walk {
	function __construct($width) {
		parent::__construct($width);
		$this->name = 'Wet Walk';
	}

	public function draw(&$p) {
		$origCurX = $p->curX;
		$origCurY = $p->curY;
		parent::draw($p);
		$p->appendToLayer('water','
		<path style="fill:none;stroke:#'. getWaterColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. ($origCurX + $this->strokeWidth) .','. ($origCurY - $this->strokeWidth) .' h'. ($this->drawedWidth - $this->strokeWidth) .'" />');
	}
}

class LongWalk extends Walk {
	function __construct($width) {
		parent::__construct($width);
		$this->name = 'Long walk';
		$this->heightFactor = 0.0005;
		$this->widthFactor = 0.0005;

		$this->displayedText = $width;
		$this->height = 500;

		// Arbitrarily specify the angle
		$this->width = 10;
	}

	public function draw(&$p) {
		$longWalkHeight = 20 * $p->yScale;
		// Ugly, but needs advanced thinking of a better way...
		$longWalkHeight = 170;
		$longWalkAngle = 20;
		$longWalkWidth = 10;

		$p->appendToLayer('base',
		'm '. $p->curX .','. $p->curY .' h'. ($longWalkWidth*4.5),
		'h'. -($longWalkWidth*4.5));

		$p->curX += $longWalkWidth;

		// Un rectangle oblique blanc
		$p->appendToLayer('overall','
		<path style="fill:#FFFFFF;stroke:#FFFFFF;stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. ($p->curX-$longWalkAngle) .','. ($p->curY+($longWalkHeight/2)) .' '. $longWalkAngle*2 .','. -($longWalkHeight*1).'
		h '.$longWalkWidth.'
		l '. (-$longWalkAngle*2) .','. $longWalkHeight .' z" />');

		// Un premier trait oblique
		$p->appendToLayer('overall','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:8,4,2,4;stroke-dashoffset:0"
		d="m '. ($p->curX-$longWalkAngle) .','. ($p->curY+($longWalkHeight/2)) .' '. $longWalkAngle*2 .','. -($longWalkHeight*1).'" />');
		$p->curX += $longWalkWidth;

		// Un deuxième trait oblique
		$p->appendToLayer('overall','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:8,4,2,4;stroke-dashoffset:0"
		d="m '. ($p->curX-$longWalkAngle) .','. ($p->curY+($longWalkHeight/2)) .' '. $longWalkAngle*2 .','. -($longWalkHeight*1).'" />');

		$p->curX += ($longWalkWidth*2.5);
		$p->displayText(($this->displayedText . 'm'), ($p->curX-$longWalkAngle), ($p->curY+($longWalkHeight/2)), -5, 20, 'end');
	}
}

class Pool extends Item {
	function __construct($width) {
		$width = abs($width);
		parent::__construct();
		$this->name = 'Pool';
		$this->heightFactor = 0;
		$this->widthFactor = 1;

		$this->width = $width;
	}

	public function draw(&$p) {
		$depth = 2 * $p->yScale;
		$p->appendToLayer('base',
		'm '. $p->curX .','. $p->curY .' c0,'. $depth .' '. $this->drawedWidth / 2 .',0 '. $this->drawedWidth .',0',
		'c'.-($this->drawedWidth / 2).',0 '. -$depth .',0 '. -$this->drawedWidth .',0');
		$p->appendToLayer('water','
		<path style="fill:#'. getWaterColor() .';fill-opacity:1;stroke:none"
		d="m '. ($p->curX + ($this->strokeWidth/2)) .','. ($p->curY - ($this->strokeWidth/2)) .' c '.-$this->strokeWidth.','. $depth .' '. $this->drawedWidth / 2 .',0 '. $this->drawedWidth .',0" />');
		$p->curX += $this->drawedWidth;
	}
}

class LongPool extends Pool {
	function __construct($width) {
		parent::__construct($width);
		$this->name = 'Long pool';
	}
}

class DeepPool extends Pool {
	function __construct($width) {
		parent::__construct($width);
		$this->name = 'Deep pool';
	}
}

class Anchor extends Item {
	function __construct($text) {
		parent::__construct();
		$this->name = 'Anchor';
		$this->heightFactor = 0;
		$this->widthFactor = 0;

		$this->displayedText = $text;
	}

	public function draw(&$p) {
		$xAsOffset = 1.3 * $p->xScale;
		$yAsOffset = 1.3 * $p->yScale;
		$p->displayText($this->displayedText, ($p->curX + $xAsOffset), ($p->curY - $yAsOffset), 0, -7, 'middle');
	}
}

class SingleAnchor extends Anchor {
	function __construct($text) {
		parent::__construct($text);
		$this->name = 'Single anchor';
		$this->radius = 3;
	}

	public function draw(&$p) {
		$xAsOffset = 1.3 * $p->xScale;
		$yAsOffset = 0.9 * $p->yScale;
		$p->appendToLayer('anchors','
		<circle cx = "'. ($p->curX + $xAsOffset) .'" cy = "'. ($p->curY - $yAsOffset) .'" r = "'. $this->radius .'" fill = "#FFFFFF" fill-opacity = "1" stroke = "#'.getAnchorColor().'" stroke-width = "'. $this->strokeWidth .'px"/>');
		$p->displayText($this->displayedText, ($p->curX + $xAsOffset), ($p->curY - $yAsOffset), 5, 5, 'left');
	}
}

class DoubleAnchor extends SingleAnchor {
	function __construct($text) {
		parent::__construct($text);
		$this->name = 'Double anchor';
	}

	public function draw(&$p) {
		$xAsOffset = 1.0 * $p->xScale;
		$yAsOffset = 1.1 * $p->yScale;
		$p->appendToLayer('anchors','
		<circle cx = "'. ($p->curX + $xAsOffset) .'" cy = "'. ($p->curY - $yAsOffset) .'" r = "'. $this->radius .'" fill = "#FFFFFF" fill-opacity = "1" stroke = "#'.getAnchorColor().'" stroke-width = "'. $this->strokeWidth .'px"/>
		<circle cx = "'. ($p->curX + $xAsOffset + (3.5 * $this->radius)) .'" cy = "'. ($p->curY - $yAsOffset) .'" r = "'. $this->radius .'" fill = "#FFFFFF" fill-opacity = "1" stroke = "#'.getAnchorColor().'" stroke-width = "'. $this->strokeWidth .'px"/>');
		$p->displayText($this->displayedText, ($p->curX + $xAsOffset + (2 * $this->radius)), ($p->curY - $yAsOffset), 10, 5, 'left');
	}
}

class NaturalAnchor extends Anchor {
	function __construct($text) {
		parent::__construct('AN ' . $text);
		$this->name = 'Natural anchor';
	}

	public function draw(&$p) {
		$xAsOffset = 3.2 * $p->xScale;
		$yAsOffset = 2 * $p->yScale;
		$p->displayText($this->displayedText, ($p->curX + $xAsOffset), ($p->curY - $yAsOffset), -10, 5, 'start');
	}
}

class Vegetal extends Item {
	function __construct() {
		parent::__construct();
		$this->name = 'Vegetal';
		$this->heightFactor = 0;
		$this->widthFactor = 0;
	}
}

class PineTree extends Vegetal {
	function __construct() {
		parent::__construct();
		$this->name = 'Pinetree';
	}

	public function draw(&$p) {
		$p->neededDefs[get_class($this)] = 1;
		$xAsOffset = 0 * $p->xScale - 60;
		$yAsOffset = 0 * $p->yScale - 90 - ($this->strokeWidth / 2);
		$p->appendToLayer('vegetal', '
		<use xlink:href="#'.get_class($this).'" x="'.($p->curX + $xAsOffset).'" y="'.($p->curY + $yAsOffset).'"/>');
	}

	public static function getDef(&$p) {
		$p->appendToFile('
		<g id="'.get_called_class().'">
			<rect x="45" y="70" width="10" height="20" fill="peru"/>
			<polygon points="20,70 80,70 60,55 70,55 55,40 65,40 50,20 35,40 45,40 30,55 40,55" fill="forestgreen"/>
		</g>
		');
	}
}

class ExitPoint extends Item {
	function __construct($text) {
		parent::__construct();
		$this->name = 'Exit point';
		$this->displayedText = $text;
	}

	public function draw(&$p) {
		$p->neededDefs[get_class($this)] = 1;
		$xOffset = -20;
		$yOffset = -45;
		$p->appendToLayer('infos', '
		<use xlink:href="#'.get_class($this).'"  x="'.($p->curX + $xOffset).'" y="'.($p->curY + $yOffset).'"/>');
		$p->displayText($this->displayedText, ($p->curX + $xOffset + 32), ($p->curY + $yOffset), 0, 0, 'left');
	}

	public static function getDef(&$p) {
		$p->appendToFile('
		<g id="'.get_called_class().'">
			<path style="fill:#ff0000;fill-opacity:1;stroke:#000000;stroke-width:1.5;stroke-linecap:butt;stroke-linejoin:miter;
			stroke-miterlimit:4;stroke-opacity:1;stroke-dasharray:none" d="M 30.69,1.01 16.55,3.84 20.79,8.08 1,20.81 l 8.48,1.41 1.41,8.48 12.72,-19.79 4.24,4.24 z"/>
		</g>
		');
	}
}

class Span extends Item {
}

class CarriageReturn extends Item {
	function __construct($crOffset) {
		$crOffset = abs($crOffset);
		parent::__construct();
		$this->name = 'Carriage return';
		$this->heightFactor = 0;
		$this->widthFactor = 0;
		$this->crOffset = $crOffset;

	}

	public function draw(&$p) {
		$p->curX = $p->minX;
		$p->curY -= $this->crOffset;
		$p->appendToLayer('base','M '. $p->curX .','. $p->curY);
		$p->belowBackground = FALSE;
		$p->aboveBackground = FALSE;
	}
}

?>
