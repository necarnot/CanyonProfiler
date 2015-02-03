<?php

function _error_log($msg) {
	$trace = debug_backtrace();
	$callerFunctionName = $trace[1]["function"];
	error_log($callerFunctionName . ':' . $msg);
}

function isWiki() {
	global $conf;
	return (isset($conf) && defined('DOKU_INC'));
}

function promiseDirExist($dirName) {
	if (!is_dir($dirName)) {
		_error_log('Needed dir "'.$dirName.'" does not exist. Trying to mkdir.');
		if (!mkdir($dirName, 0755, true)) {
			die('Error : Could not create needed dir "'.$dirName.'"');
		}
	}
}

function getOutDir() {
	global $conf;
	$outDir = 'profiles';
	if (isWiki()) {
		_error_log(' ---- canyonStr='.$canyonStr.', DOKU_INC='.DOKU_INC.', DOKU_PLUGIN='.DOKU_PLUGIN.' ,$conf[mediadir]='.$conf['mediadir']);
		$outDir = $conf['mediadir'];
	}
	return $outDir;
}

function getFileName($canyonStr) {
	$outDir = getOutDir();
	promiseDirExist($outDir);
	$outFile = 'outfile_' . md5($canyonStr) . '.svg';
	$curFileName = $outDir . '/' . $outFile;

	// Si le fichier existe déjà, et s'il est récent, on ne le re-crée pas
	// Cache time : 2 days ("86400 * 2")
	$cache = true;
	$cache = false;
	$myCwd = getcwd();
	_error_log('curFileName='.$curFileName.', CWD='.$myCwd.', cache='.$cache);
	if ($cache && file_exists($curFileName)) {
		$stat = stat($curFileName);
		if ($stat
		 && $stat['size'] > 0
		 && $stat['mtime'] > (time() - (86400 * 2)) ) {
			if (isWiki()) {
				$curFileName = '/_media/'.$outFile;
			}
			_error_log(' :-) File found, submitted string is recent, file already created. Skipping generation. Returning:'.$curFileName);
			return $curFileName;
		} else {
			_error_log(' :-( Fstat prevents caching. Generating...');
		}
	} else {
		_error_log(' :-( File '.$curFileName.' does not exist. Generating...');
	}

	$p = new Profile();
	$p->fileHandle = fopen($curFileName, 'w+') or die("Can't open file:".$curFileName);
	$p->parse($canyonStr);
	$p->setPageDimensions();
	$p->appendToFile('<?xml version="1.0" standalone="no"?>
	<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

	<svg	viewBox="-30 -80 1150 860"
		width="100%" height="100%"
		preserveAspectRatio="none"
		version="1.1"
		xmlns="http://www.w3.org/2000/svg"
		xmlns:xlink="http://www.w3.org/1999/xlink">
	');
	//<svg width="' . $p->pageWidth . 'mm" height="' . $p->pageHeight . 'mm" version="1.1"
	$p->chainItems();
	$p->scale();

	$p->draw();
	$p->getDefs();
	$p->appendToFile('
	</svg>');
	fclose($p->fileHandle);
	if (isWiki()) {
		$curFileName = '/_media/'.$outFile;
	}
	_error_log('returning:'.$curFileName);
	return $curFileName;
}

function includeProfile($canyonStr) {
	$curFileName = getFileName($canyonStr);
	//if ($p->submittedHeader) {
	//	$displayOrigCanyonStr = preg_replace('/&/', '&amp;', $p->origCanyonStr);
	//	echo '
	//	<p style="font-size:8px"><b>Submitted :</b> '.$p->canyonName .' : '. $displayOrigCanyonStr.'<br/>
	//	<b>Parsed as :</b> '.$p->canyonName .' : '. $p->canyonStr.'</p>';
	//}

echo '
<div style="width:100%;height:100%">
  <object type="image/svg+xml" data="'.$curFileName.'"></object>
</div>';
}

// Cette fonction extrait d'un fichier SVG les defs et le <g>
// et l'empaquette dans un <g id="symbolName">.
// Cette fonction utilise la version mise en cache du fichier utilisé.
// Si la version en cache est absente, le fichier en cache est crée.
function setPackagedSymbol(&$item) {
	$symbolName = get_class($item);
	$symbolDir = isWiki() ? 'lib/plugins/canyonprofiler/symbols' : 'symbols';
	$symbolFile = $symbolDir . '/' . $symbolName . '.svg';
	$symbolCacheDir = $symbolDir . '/cache';
	promiseDirExist($symbolCacheDir);
	$symbolCacheFile = $symbolCacheDir . '/' . $symbolName . '.svg';
	if(!file_exists($symbolCacheFile)) {
		_error_log('Le fichier '.$symbolCacheFile." n'existe pas en cache");
		// Ici, on lance la construction du fichier en cache.
		$handle = fopen($symbolFile, 'r');
		if ($handle === FALSE) {
			_error_log('Unable to open source file : '.$symbolFile);
			return;
		}
		$handleCache = fopen($symbolCacheFile, 'w');
		if ($handleCache === FALSE) {
			_error_log('Unable to open cache file : '.$symbolCacheFile);
			return;
		}
		while (($buffer = fgets($handle)) !== false) {
			if($buffer == '\n'
			|| substr($buffer, 0, 6) == '<?xml '
			|| substr($buffer, 0, 4) == '<svg'
			|| substr($buffer, 0, 6) == '</svg>'
			) {
				continue;
			}
			if(fwrite($handleCache, $buffer) === FALSE) {
				_error_log('Error : Unable to create cache version of file '.$symbolFile);
			}
		}
		if (!feof($handle)) {
			echo "Erreur: stream_get_line() a échoué\n";
		}
		fclose($handle);
		fclose($handleCache);
	}

	//_error_log('symbolFile='.$symbolFile);
	$file = file_get_contents($symbolFile);
	preg_match ('/<svg\ .*width="(.*)".*/', $file, $matches);
	$symbolWidth = $matches[1];
	preg_match ('/<svg\ .*height="(.*)".*/', $file, $matches);
	$symbolHeight = $matches[1];
	//_error_log('symbolWidth='.$symbolWidth.', symbolHeight='.$symbolHeight);
	$item->width = $symbolWidth;
	$item->height = $symbolHeight;
	$item->def = file_get_contents($symbolCacheFile);
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

function plf ($chaine, $cr = 1) {
	print $chaine . str_repeat("\n", $cr);
}

function top () {
	plf('<?xml version="1.0" encoding="iso-8859-15"?>');
	plf('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
	plf('<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">');
	plf('  <head>');
	plf('    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />');
	plf('    <title>CanyonProfiler</title>');
	plf('  </head>');
	plf('  <body>');
}

function bottom () {
	plf ('  </body>');
	plf ('</html>', 0);
}

class Profile {
	public $baseFontHeight;
	public $pageWidth;
	public $pageHeight;
	public $pageWidthPx;
	public $pageHeightPx;
	public $xEndOffset;
	public $yEndOffset;
	public $xOffset;
	public $yOffset;
	public $maxWidth;
	public $maxHeight;
	public $xScale;
	public $yScale;
	public $curX;
	public $curY;
	public $minX;
	public $minY;
	public $maxX;
	public $maxY;
	public $baseStrokeWidth;
	public $curColor;
	public $origCanyonStr;
	public $canyonStr;
	public $defaultVersion;
	public $writtenDefs = array ();

	public $syntaxVersion;
	public $items = array ();
	public $fileHandle;

	public $canyonName;

	public $belowBackground;
	public $aboveBackground;
	public $belowBackgroundColor;
	public $aboveBackgroundColor;

	public $submittedHeader;

	public $layers = array ();
	public $baseLayer = array ();

	// TODO : Lors de la construction, pré-remplir les variables par les valeurs par défaut qu'on trouve dans les options
	public function __construct() {
		$this->baseFontHeight = 16 / 10.2;
		$this->pageWidth = 297;
		$this->pageHeight = 210;
		$this->pageWidthPx;
		$this->pageHeightPx;
		$this->xEndOffset = 40;
		$this->yEndOffset = 40;
		$this->xOffset = 20;
		$this->yOffset = 90;
		$this->maxWidth = 0;
		$this->maxHeight = 0;
		$this->xScale = 0;
		$this->yScale = 0;
		$this->curX = 0;
		$this->curY = 0;
		$this->minX = 9999999999;
		$this->minY = 9999999999;
		$this->maxX = 0;
		$this->maxY = 0;
		$this->baseStrokeWidth = 2;
		$this->curColor = '000000';
		$this->origCanyonStr = '';
		$this->canyonStr = '';
		$this->defaultVersion = 'fr2.1';
		$this->syntaxVersion = '';
		$this->fileHandle;
		$this->canyonName = '';
		$this->belowBackground = 1;
		$this->aboveBackground = 1;
		$this->belowBackgroundColor = 'ae6a5a';
		$this->aboveBackgroundColor = 'b5b5b5';
		$this->submittedHeader = 1;

		$this->layers = array (
			'base' => '',
			'water' => '',
			'symbols' => '',
			'anchors' => '',
			'text' => '',
			'infos' => '',
			'overall' => '',
		);
		$this->baseLayer = array (
			'direct' => array(),
			'reverse' => array(),
		);
	}
	
	public function setPageDimensions() {
		$this->pageWidthPx  = ($this->pageWidth  * 3.54) - $this->xEndOffset;
		$this->pageHeightPx = ($this->pageHeight * 3.54) - $this->yEndOffset;
	}

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
		<g font-size="'.$this->fontHeight.'" font-family="sans-serif" fill="white" stroke="none" text-anchor="'. $align .'" filter="url(#filterTextShadow)">
			<text x="' . $xText . '" y="' . $yText . '" fill="#ffffff">' . $text . '</text>
		</g>');

		$this->appendToLayer('text','
		<g font-size="'.$this->fontHeight.'" font-family="sans-serif" fill="black" stroke="none" text-anchor="'. $align .'">
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
			_error_log ('Error: Empty string. CanyonStr='.$this->canyonStr);
			exit(-1);
		}
	}

	// Le chaînage ne sert que pour la gestion de l'eau
	// On peut donc exclure du chaînage les éléments non joints (amarrages, texte, etc...)
	public function chainItems() {
		// Linking to the next item
		$nbItems = count($this->items);
		$curChainedItem = 0;
		for ($i = 0; $i < ($nbItems-1); $i++) {
			$nextItemClass = get_class($this->items[$i+1]);
			//_error_log('XXX :i='.$i.',itemName='.$this->items[$i]->name.',nextItemClass='.$nextItemClass);
			if ($this->items[$i+1]->isChainable) {
				$this->items[$curChainedItem]->nextItem = &$this->items[$i+1];
				//_error_log('02-CHAIN OK:i='.$i.',curChainedItem='.$curChainedItem.',itemName='.$this->items[$i]->name.',nextItemClass='.$nextItemClass);
				$curChainedItem = $i + 1;
			}
			//else {
			//	_error_log('03-chain ko:i='.$i.',curChainedItem='.$curChainedItem.',itemName='.$this->items[$i]->name.',nextItemClass='.$nextItemClass);
			//}
		}
		// Le chaînage arrière n'est pas encore employé, donc on le désactive pour l'instant
		//for ($i = ($nbItems-1); $i > 0 ; $i--) {
		//	//_error_log('<chaining:i='.$i.',itemName='.$this->items[$i]->name);
		//	$this->items[$i]->prevItem = &$this->items[$i-1];
		//}
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
			//_error_log('2 : curX='.$curX.' minX='.$minX);
			//$this->appendToFile('
			//<!-- str='.get_class($item).'|'.$item->width.'|'.$item->height.' itemWidthFactor='.$item->widthFactor. ' itemHeightFactor='.$item->heightFactor.' minX='.$minX.' minY='.$minY.' maxX='.$maxX.' maxY='.$maxY.' -->');
		}
		_error_log('3 : minX='.$minX.', maxX='.$maxX.', minY='.$minY.', maxY='.$maxY);
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
		<!-- beforeRatioAdjust xScale='.$this->xScale.' // yScale='.$this->yScale.' // ratio='.$ratio.' -->');
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
		<!-- afterRatioAdjust  xScale='.$this->xScale.' // yScale='.$this->yScale.' // ratio='.$ratio.' -->');

		foreach($this->items as $item) {
			$item->scale($this->xScale, $this->yScale);
		}

		// Scaling font height
		$this->fontHeight = $this->baseFontHeight * $this->xScale;
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
					if(preg_match('/m\s*-?\d*\.*\d*,-?\d*\.*\d*\s*(.*)/',$text,$matches)) {
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
				$belowText = "\t\t"
					. '<path style="fill:#'
					. $this->belowBackgroundColor
					. ';fill-opacity:1;stroke:none;filter:url(#filterBelow)" d="m20,-20 '
					. $layerText
					. ' l-40,40 '
					. $oppositeText
					. ' z ';
				$aboveText = "\t\t"
					. '<path style="fill:#'
					. $this->aboveBackgroundColor
					. ';fill-opacity:1;stroke:none;filter:url(#filterAbove)" d="m-3,3 '
					. $layerText
					. ' l80,-80 '
					. $oppositeText
					. ' z ';
				$layerText = "\t\t"
					. '<path style="fill:none;stroke:#000000;stroke-width:'.$this->baseStrokeWidth.'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1" d=" '
					. $layerText;
				$layerText .= '" />';
				$belowText .= '" />';
				$aboveText .= '" />';
				if(!$this->belowBackground) { $belowText = ''; }
				if(!$this->aboveBackground) { $aboveText = ''; }

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
		foreach($this->items as $item) {
			$className = get_class($item);
			if(!array_key_exists($className, $this->writtenDefs)) {
				$item->getDef($this);
			}
			$this->writtenDefs[$className] = 1;
		}
		$this->appendToFile('
		    <filter id="filterTextShadow">
		      <feGaussianBlur stdDeviation="2" />
		    </filter>
		    <filter id="filterBelow">
		      <feGaussianBlur stdDeviation="25" />
		    </filter>
		    <filter id="filterAbove">
		      <feGaussianBlur stdDeviation="10" />
		    </filter>
	</defs>');
	}

	public function setOptions($optionsStr) {
		$allowedOptions = getDefinedAllowedOptions();
		$pairs = explode(',', $optionsStr);
		foreach($pairs as $pair) {
			$pair = trim($pair);
			$optParts = explode('=', $pair);
			if(count($optParts) != 2) { continue; }
			$key = trim($optParts[0]);
			$value = trim($optParts[1]);
			if(isNullOrEmptyString($key) || isNullOrEmptyString($value)) { continue; }
			if (array_key_exists($key, $allowedOptions)) {
				$keyType = $allowedOptions[$key][0];
				$keyMinValue = $allowedOptions[$key][2];
				$keyMaxValue = $allowedOptions[$key][3];
				$keyDefault  = array_key_exists(4, $allowedOptions[$key]) ? $allowedOptions[$key][4] : '';
				switch ($keyType) {
					case 'str':
						if ((strlen($value) < $keyMinValue) || (strlen($value) > $keyMaxValue)) { continue; };
						break;
					case 'int':
					case 'bool':
						if (($value < $keyMinValue) || ($value > $keyMaxValue) || !is_int($value)) { continue; };
						break;
					default :
						continue;
				}
				$this->$key = $value;
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
	public $strokeWidth;
	public $inStr;
	public $symbolLetter;
	public $xOffset;
	public $yOffset;
	public $isChainable;

	function __construct() {
		// TODO : Il serait génial d'auto-sizer le strokeWidth selon le xScale.
		// Hélas, on ne le connait qu'_après_ avoir crée tous les items...
		$this->strokeWidth = 2;
		$this->inStr = '';
		$this->symbolLetter = '';
		$this->isChainable = false;
	}

	public function getDef(&$p) {
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
		$this->isChainable = true;

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

// TODO : Vérifier l'homogénéité du comportement du point de départ d'un WetAngle
// selon le type de l'item précédent (par exemple : marche vs longwalk)
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
		//$offsetNextItem = 0;
		// Using "max(...)" below because water can only fall verticaly, and can not (abscisse-)run backward (!)
		$p->appendToLayer('water','
		<path style="fill:none;stroke:#'. getWaterColor() .';stroke-width:'. $this->strokeWidth .'px;stroke-linecap:square;stroke-linejoin:miter;stroke-opacity:1"
		d="m ' . ($origCurX + $this->strokeWidth) . ',' . $origCurY . ' ' . max($this->drawedWidth-($this->strokeWidth/1.5),0) . ',' . ($this->drawedHeight - $offsetNextItem) . '" />');
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
		$p->belowBackground = 0;
		$p->aboveBackground = 0;
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
		$this->isChainable = true;
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
	public $baseLongWalkHeight;
	public $baseLongWalkAngle;
	public $baseLongWalkWidth;

	function __construct($width) {
		parent::__construct($width);
		$this->name = 'Long walk';
		$this->heightFactor = 0.0005;
		$this->widthFactor = 0.0005;

		$this->displayedText = $width;
		$this->height = 500;

		// Arbitrarily specify the width
		$this->width = 0;

		$this->baseLongWalkHeight = 170 / 12.7;
		$this->baseLongWalkAngle  =  20 / 12.7;
		$this->baseLongWalkWidth  =  10 / 12.7;
	}

	public function draw(&$p) {
		$longWalkHeight = $this->baseLongWalkHeight * $p->yScale;
		$longWalkAngle  = $this->baseLongWalkAngle  * $p->yScale;
		$longWalkWidth  = $this->baseLongWalkWidth  * $p->yScale;

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
		$factor= 10 * pow($p->xScale, -0.9);
		$xAsOffset = $factor * $p->xScale;
		$yAsOffset = $factor * $p->yScale;
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
		$factor= 10 * pow($p->xScale, -0.9);
		$xAsOffset = $factor * $p->xScale;
		$yAsOffset = $factor * $p->yScale;
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
		$factor= 10 * pow($p->xScale, -0.9);
		$xAsOffset = $factor * $p->xScale;
		$yAsOffset = $factor * $p->yScale;
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
		$factor= 12 * pow($p->xScale, -0.9);
		$xAsOffset = $factor * $p->xScale;
		$yAsOffset = $factor * $p->yScale;
		$p->displayText($this->displayedText, ($p->curX + $xAsOffset), ($p->curY - $yAsOffset), -10, 5, 'start');
	}
}

// TODO :
/*
- Déterminer un facteur d'échelle :
  - Soit s'accorder dès le dessin en vectoriel du symbole d'une référence
  - Soit attribuer à chaque fichier un facteur à stocker je ne sais où (dans un commentaire du fichier ?)
- Mettre à l'échelle chaque symbole (chaque xlink) en fonction des xScale et yScale
*/
class Symbol extends Item {
	public $def;
	public $symbolScale;

	function __construct($text) {
		parent::__construct();
		$this->heightFactor = 0;
		$this->widthFactor = 0;
		$this->name = get_class($this);
		//_error_log('class Symbol, $this->name='.$this->name);
		$this->displayedText = $text;
		//_error_log('class Symbol, $this->displayText='.$this->displayedText);
		setPackagedSymbol($this);
	}

	// On définit cette fonction pour permettre à chaque symbole
	// de pouvoir préciser leur propre méthode d'offset
	public function setXYOffset() {
		$this->xOffset = 0 - $this->drawedWidth;
		$this->yOffset = 0 - $this->drawedHeight;
	}

	public function draw(&$p) {
		// On flag cet indice de tableau pour éviter les répétitions d'insertion de def
		$p->neededDefs[get_class($this)] = 1;
		$this->setXYOffset($p);
		$p->appendToLayer('symbols', '
		<use xlink:href="#'.get_class($this).'" x="0" y="0" transform="
		translate('.($p->curX + $this->xOffset).','.($p->curY + $this->yOffset).')
		scale('.($this->symbolScale * $p->xScale).','.($this->symbolScale * $p->yScale).')
		"/>');
		$p->displayText($this->displayedText, $p->curX, ($p->curY + $this->yOffset), 0, 0, 'left');
	}

	public function getDef(&$p) {
		$p->appendToFile($this->def);
	}

	public function scale($xScale, $yScale) {
		//_error_log('Symbol::scale - width='.$this->width.' height='.$this->height.' xScale='.$xScale.' yScale='.$yScale);
		//$this->drawedWidth  = $this->width  * $this->symbolScale * $xScale;
		//$this->drawedHeight = $this->height * $this->symbolScale * $yScale;
		$this->drawedWidth  = $this->width  * $this->symbolScale;
		$this->drawedHeight = $this->height * $this->symbolScale;
	}
}

class Tree extends Symbol {
	function __construct($text) {
		parent::__construct($text);
	}

	public function setXYOffset() {
		$this->xOffset = 0 - ($this->drawedWidth / 1.7);
		$this->yOffset = 0 - $this->drawedHeight - ($this->strokeWidth / 2);
	}
}

class PineTree01 extends Tree {
	// symbolWidth=, symbolHeight=
	function __construct($text) {
		parent::__construct($text);
		$this->symbolScale = 0.18;
	}
}

class PineTree02 extends Tree {
	// symbolWidth=450, symbolHeight=730
	function __construct($text) {
		parent::__construct($text);
		$this->symbolScale = 0.018;
	}
}

class ExitPoint extends Symbol {
	// symbolWidth=, symbolHeight=
	function __construct($text) {
		parent::__construct($text);
		$this->symbolScale = 0.08;
	}

	public function setXYOffset() {
		$this->xOffset = 0 - ($this->drawedWidth * 1);
		$this->yOffset = 0 - ($this->drawedHeight * 1.2);
	}
}

class EntryPoint extends ExitPoint {
}

# Si la première lettre est en majuscule, on place le commentaire au dessus
class Comment extends Anchor {
	function __construct($text) {
		parent::__construct($text);
		$this->name = 'Comment';
	}

	public function draw(&$p) {
		$factor= 12 * pow($p->xScale, -0.9);
		$xAsOffset = $factor * $p->xScale;
		$yAsOffset = $factor * $p->yScale;
		if (ctype_upper(substr($this->inStr,0,1))) {
			$p->displayText($this->displayedText, ($p->curX + $xAsOffset), ($p->curY - $yAsOffset), -10, 5, 'start');
		} else {
			$p->displayText($this->displayedText, ($p->curX + $xAsOffset), ($p->curY + $yAsOffset), -10, 5, 'end');
		}
	}
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
		$p->belowBackground = 0;
		$p->aboveBackground = 0;
	}
}

?>
