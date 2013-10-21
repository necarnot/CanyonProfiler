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
	public $origCanyonStr = '';
	public $canyonStr = '';
	public $defaultVersion = 'fr1.1';

	public $syntaxVersion = '';
	public $items = array ();

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
			$curX += $item->getWidth();
			$curY += $item->getHeight();
			//if ($item == 'cr') {
			//	$curX = 0;
			//	$curY = 0;
			//}
			if ($curX > $this->maxWidth) { $this->maxWidth = $curX; }
			if ($curY > $this->maxHeight) { $this->maxHeight = $curY; }
		}
		echo '

		// maxWidth='.$this->maxWidth.'
		'.'
		// maxHeight='.$this->maxHeight.'
		';
	}

	public function draw() {
		foreach($this->items as $item) {
			$item->draw();
		}
	}
}


class Item {
	public $name;
	public $height;
	public $width;
	public $heightFactor;
	public $widthFactor;
	public $displayedText;
	public $strokeWidth = 2;

	function __construct() {
	}

	public function getWidth() {
		return ($this->width * $this->widthFactor);
	}

	public function getHeight() {
		return ($this->height * $this->heightFactor);
	}

	public function draw() {
	}
}

class Vertical extends Item {
	function __construct($height) {
		parent::__construct();
		$this->name = 'Vertical';
		$this->heightFactor = 1;
		$this->widthFactor = 0;

		$this->height = $height;
		$this->displayedText = 'C' . $height;
	}
}

class OverhangingVertical extends Vertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Overhanging Vertical';
		$this->widthFactor = -1;
	}
}

class Slide extends Vertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Slide';
		$this->widthFactor = 1.5;

		$this->displayedText = 'T' . $height;
	}
}

class RoundedVertical extends Vertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Rounded vertical';
		$this->widthFactor = 0.2;
	}
}

class DownClimb extends Vertical {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'DownClimb';
		$this->displayedText = 'R' . $height;
	}
}

class RoundedDownClimb extends DownClimb {
	function __construct($height) {
		parent::__construct($height);
		$this->name = 'Rounded downclimb';
		$this->widthFactor = 0.2;
		$this->displayedText = 'R' . $height;
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

	public function draw() {
		$curWidth = $this->width * $xScale;
		echo '
		<path style="fill:none;stroke:#'. randomColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m '. $this->curX .','. $this->curY .' '. $curWidth .',0"
		id="path3311" inkscape:connector-curvature="0" />';
		$this->curX += $curWidth;
	}
}

class LongWalk extends Walk {
	function __construct($width) {
		parent::__construct($width);
		$this->name = 'Long walk';
		$this->heightFactor = 0.0005;
		$this->widthFactor = 0.0005;

		$this->width = $width;
		$this->displayedText = $width;
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

		$this->radius = 3;
		$this->displayedText = $text;
	}
}

class SingleAnchor extends Anchor {
	function __construct($text) {
		parent::__construct($text);
		$this->name = 'Single anchor';
	}
}

class DoubleAnchor extends SingleAnchor {
	function __construct($text) {
		parent::__construct($text);
		$this->name = 'Double anchor';
	}
}

class NaturalAnchor extends SingleAnchor {
	function __construct($text) {
		parent::__construct($text);
		$this->name = 'Natural anchor';
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
}

class Span extends Item {
}

class CarriageReturn extends Item {
	function __construct($offset) {
		parent::__construct();
		$this->name = 'Carriage return';
		$this->heightFactor = 0;
		$this->widthFactor = 0;
	}
}

?>
