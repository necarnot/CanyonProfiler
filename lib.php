<?php

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
	public $curColor = '000000';
	public $origCanyonStr = '';
	public $canyonStr = '';
	public $defaultVersion = 'fr2.1';
	public $neededDefs = array ();

	public $syntaxVersion = '';
	public $items = array ();

	public $layers = array (
		'base' => '',
		'water' => '',
		'vegetal' => '',
		'anchors' => '',
		'text' => '',
		'infos' => ''
	);

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

	public function appendToLayer($layer, $text) {
		$this->layers[$layer] .= $text;
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

	// On cherche à déterminer la largeur max et la hauteur max cumulées
	// afin de pouvoir ajuster la coupe aux dimensions de la page
	public function scale() {
		$curX = 0;
		$curY = 0;
		// On détermine maxHeight et maxWidth
		foreach($this->items as $item) {
			$curX += $item->width * $item->widthFactor;
			$curY += $item->height * $item->heightFactor;
			if (get_class($item) == 'CarriageReturn') {
				error_log('curX='.$curX.' curY='.$curY.' this->xOffset='.$this->xOffset.' this->yOffset='.$this->yOffset);
				$curX = 0;
				$curY = 0;
			}
			if ($curX > $this->maxWidth) { $this->maxWidth = $curX; }
			if ($curY > $this->maxHeight) { $this->maxHeight = $curY; }
			//echo '
			// str='.get_class($item).'|'.$item->width.'|'.$item->height.' itemWidthFactor='.$item->widthFactor. ' itemHeightFactor='.$item->heightFactor.' maxWidth='.$this->maxWidth.' maxHeight='.$this->maxHeight;
		}
		/*
		echo '

		// maxWidth='.$this->maxWidth.'
		'.'
		// maxHeight='.$this->maxHeight.'
		';
		*/

		$this->pageWidthPx -= $this->xOffset;
		$this->pageHeightPx -= $this->yOffset;
		$this->xScale = $this->pageWidthPx / $this->maxWidth;
		$this->yScale = $this->pageHeightPx / $this->maxHeight;
		// On remet en place les positions de départ
		$this->curX = $this->xOffset;
		$this->curY = $this->yOffset;

		$ratio = $this->xScale / $this->yScale;
		/*
		echo '

		// xScale='.$this->xScale.'

		// yScale='.$this->yScale.'

		// ratio='.$ratio.'
		';
		*/
		$minRatio = 0.5;
		$maxRatio = 2;
		// Dans les cas de dépassement de ratio, on force la correction
		if ($ratio < $minRatio) {
			echo '
			<!-- !!!!!!!!!! RATIO WARNING : < '. $minRatio .' !!!!!!!!!!! -->
			';
			$this->yScale = $this->xScale * 1.5;
		}
		if ($ratio > $maxRatio) {
			echo '
			<!-- !!!!!!!!!! RATIO WARNING : > '. $maxRatio .' !!!!!!!!!!! -->
			';
			$this->xScale = $this->yScale;
		}

		$ratio = $this->xScale / $this->yScale;
		/*
		echo '

		// xScale='.$this->xScale.'

		// yScale='.$this->yScale.'

		// ratio='.$ratio.'
		';
		*/

		foreach($this->items as $item) {
			$item->scale($this->xScale, $this->yScale);
		}
	}

	public function draw() {
		foreach($this->items as $item) {
			$item->draw($this);
		}
		foreach($this->layers as $layerName => $layerText) {
			if(isNullOrEmptyString($layerText)) {
				continue;
			}
			echo '
	<g id="'.$layerName.'"> '.$layerText.'
	</g>
			';
		}
	}

	public function getDefs() {
		if(empty($this->neededDefs)) {
			return;
		}
		echo '
	<defs>';
		foreach($this->neededDefs as $neededDef => $kickme) {
			$neededDef::getDef();
		}
		echo '
	</defs>';
	}
}


class Item {
	public $name;
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
		parent::__construct();
		$this->name = 'VerticalAngle';
		$this->heightFactor = 1;
		$this->widthFactor = 1;

		$this->height = $height;
		$this->symbolLetter = 'C';

		// TODO : Recalculer intelligement les maxwidths
		$this->width = ($angle == 0) ? 0 : $height / tan(deg2rad($angle));
	}

	public function draw(&$p) {
		$this->setDisplayedText($this->symbolLetter . $this->height);
		$yDisplayText = $p->curY + ($this->drawedHeight / 2) + ($p->fontHeight / 2);
		if (($yDisplayText - $p->fontHeight) < $p->curY) {
			$yDisplayText += $p->fontHeight;
		}
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m ' . $p->curX . ',' . $p->curY . ' ' . $this->drawedWidth . ',' . $this->drawedHeight . '" />');
		//TODO : Pour déterminer l'offset d'xDisplay, il suffirait de le calculer grace aux points haut et bas
		$p->displayText($this->displayedText, $p->curX, $yDisplayText, ($this->drawedWidth / 3)-5, 6, 'end');
		$p->curX += $this->drawedWidth;
		$p->curY += $this->drawedHeight;
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
		$p->appendToLayer('water','
		<path style="fill:none;stroke:#'. getWaterColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m ' . ($origCurX + $this->strokeWidth) . ',' . $origCurY . ' ' . max($this->drawedWidth,0) . ',' . ($this->drawedHeight * 1) . '" />');
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
		parent::__construct($height, 115.5);
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
		parent::__construct($height, 115.5);
		$this->name = 'Slightly Overhanging Vertical';
	}
}

class OverhangingVertical extends VerticalAngle {
	function __construct($height) {
		parent::__construct($height, 157.5);
		$this->name = 'Overhanging Vertical';
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
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $p->curX .','. $p->curY
		.' c '. $curveWidth .',0 '
		.' '. $curveWidth .','. $curveWidth
		.' '. $curveWidth .','. $curveWidth
		.'l 0,'. ($this->drawedHeight - $curveWidth) .'" />');
		$p->displayText($this->displayedText, ($p->curX + $curveWidth), $yDisplayText, -5, 0, 'end');
		$p->curX += $curveWidth;
		$p->curY += $this->drawedHeight;
	}
}

class WetRoundedVertical extends RoundedVertical {
	function __construct($height) {
		parent::__construct($height, 0);
		$this->name = 'Wet Rounded Vertical';
	}

	public function draw(&$p) {
		$origCurX = $p->curX;
		$origCurY = $p->curY;
		parent::draw($p);
		// Arbitrarily specify the round width
		$curveWidth = ($this->drawedWidth / 2) + $this->strokeWidth;
		$p->appendToLayer('water','
		<path style="fill:none;stroke:#'. getWaterColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $origCurX .','. ($origCurY - $this->strokeWidth)
		.' c '. $curveWidth .',0 '
		.' '. $curveWidth .','. $curveWidth
		.' '. $curveWidth .','. $curveWidth
		.'l 0,'. ($this->drawedHeight - $curveWidth + $this->strokeWidth) .'" />');
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
		parent::__construct();
		$this->name = 'Walk';
		$this->heightFactor = 0;
		$this->widthFactor = 1;

		$this->width = $width;
	}

	public function draw(&$p) {
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $p->curX .','. $p->curY .' '. $this->drawedWidth .',0" />');
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
		d="m '. ($origCurX + $this->strokeWidth) .','. ($origCurY - $this->strokeWidth) .' '. ($this->drawedWidth - $this->strokeWidth) .',0" />');
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
		$longWalkHeight = 150;
		$longWalkAngle = 20;
		$longWalkWidth = 10;
		// Un petit trait horizontal qui précède
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $p->curX .','. $p->curY .' '. $longWalkWidth .',0" />');
		$p->curX += $longWalkWidth;
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:8,4,2,4;stroke-dashoffset:0"
		d="m '. ($p->curX-$longWalkAngle) .','. ($p->curY+($longWalkHeight/2)) .' '. $longWalkAngle*2 .','. -($longWalkHeight*1).'" />');
		$p->curX += $longWalkWidth;
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:8,4,2,4;stroke-dashoffset:0"
		d="m '. ($p->curX-$longWalkAngle) .','. ($p->curY+($longWalkHeight/2)) .' '. $longWalkAngle*2 .','. -($longWalkHeight*1).'" />');
		// Un petit trait horizontal qui suit
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $p->curX .','. $p->curY .' '. ($longWalkWidth*2.5) .',0" />');
		$p->curX += ($longWalkWidth*2.5);
		$p->displayText(($this->displayedText . 'm'), ($p->curX-$longWalkAngle), ($p->curY+($longWalkHeight/2)), -5, 20, 'end');
	}
}

class Pool extends Item {
	function __construct($width) {
		parent::__construct();
		$this->name = 'Pool';
		$this->heightFactor = 0;
		$this->widthFactor = 1;

		$this->width = $width;
	}

	public function draw(&$p) {
		$depth = 2 * $p->yScale;
		$p->appendToLayer('base','
		<path style="fill:none;stroke:#'. $p->getCurColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $p->curX .','. $p->curY .' c 0,'. $depth .' '. $this->drawedWidth / 2 .',0 '. $this->drawedWidth .',0" />');
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

	public static function getDef() {
		echo '
		<g id="'.get_called_class().'">
			<rect x="45" y="70" width="10" height="20" fill="peru"/>
			<polygon points="20,70 80,70 60,55 70,55 55,40 65,40 50,20 35,40 45,40 30,55 40,55" fill="forestgreen"/>
		</g>
		';
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

	public static function getDef() {
		echo '
		<g id="'.get_called_class().'">
			<path style="fill:#ff0000;fill-opacity:1;stroke:#000000;stroke-width:1.5;stroke-linecap:butt;stroke-linejoin:miter;
			stroke-miterlimit:4;stroke-opacity:1;stroke-dasharray:none" d="M 30.698485,1.0151594 16.556349,3.8435866 20.79899,8.0862273 1,20.814149 l 8.4852813,1.414214 1.4142137,8.485281 12.727922,-19.79899 4.242641,4.242641 z"/>
		</g>
		';
	}
}

class Span extends Item {
}

class CarriageReturn extends Item {
	function __construct($crOffset) {
		parent::__construct();
		$this->name = 'Carriage return';
		$this->heightFactor = 0;
		$this->widthFactor = 0;
		$this->crOffset = $crOffset;
	}

	public function draw(&$p) {
		$p->curX = $p->xOffset;
		$p->curY -= $this->crOffset;
	}
}

?>
