<?php
include_once 'syntaxes.php';
include_once 'parser.php';
include_once 'samples.php';
include_once 'lib.php';

$p = new Profile();
$p->pageWidthPx -= $p->xEndOffset;
$p->pageHeightPx -= $p->yEndOffset;
//$canyonName = '';
//$canyonStr = $_POST['canyonStr'];
$p->parse($canyonStr);
$p->chainItems();
$p->scale();

$outDir = 'profiles';
$outFile = 'outfile_' . uniqid() . '.svg';
$curFileName = $outDir . '/' . $outFile;
$curFileHandle = fopen($curFileName, 'w+') or die("Can't open file:".$curFileName);

appendToFile('<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

<svg width="' . $p->pageWidth . 'mm" height="' . $p->pageHeight . 'mm" version="1.1"
	xmlns="http://www.w3.org/2000/svg"
	xmlns:xlink="http://www.w3.org/1999/xlink">
');

$displayOrigCanyonStr = preg_replace('/&/', '&amp;', $p->origCanyonStr);
$p->draw();
$p->getDefs();

appendToFile('
</svg>');

fclose($curFileHandle);

top();
if (1) {
	echo '
	<p style="font-size:8px"><b>Submitted :</b> '.$canyonName .' : '. $displayOrigCanyonStr.'<br/>
	<b>Parsed as :</b> '.$canyonName .' : '. $p->canyonStr.'</p>';
}
echo '
<a href="'.$curFileName.'" style="display: block">
<object data="'.$curFileName.'" style="pointer-events:none"></object>
</a>

';

bottom();

?>
