<?php
// Every syntax has its own set of properties
$syntaxesProperties = array (
	'1.1' => array (
		'length' => 1
	),
	'2.1' => array (
		'length' => 2
	),
	'3.1' => array (
		'length' => 10
	)
);

$syntaxes = array (
	'fr1.1' => array (
		'RoundedVertical'	=> array ('c' => 'Cascade'),
		'RoundedDownClimb'	=> array ('r' => 'Ressaut'),
		'Walk'			=> array ('m' => 'Marche'),
		'Pool'			=> array ('v' => 'Vasque'),
		'SingleAnchor'		=> array ('a' => 'Amarrage'),
		'PineTree02'		=> array ('s' => 'Sapin'),
		'CarriageReturn'	=> array ('l' => 'retour à la _L_igne'),
		'Slide'			=> array ('t' => 'Toboggan')
	),
	// Thanks to Antilolo
	'es1.1' => array (
		'RoundedVertical'	=> array ('c' => 'Cascada'),
		'RoundedDownClimb'	=> array ('r' => 'Resalte'),
		'Walk'			=> array ('s' => 'Sendero'),
		'Pool'			=> array ('b' => 'Badina'),
		'SingleAnchor'		=> array ('a' => 'Anclaje'),
		'PineTree02'		=> array ('p' => 'Pino'),
		'CarriageReturn'	=> array ('l' => 'retour à la _l_igne'),
		'Slide'			=> array ('t' => 'Tobagan')
	),
	// Thanks to Marie
	'it1.1' => array (
		'RoundedVertical'	=> array ('c' => 'Cascata'),
		'RoundedDownClimb'	=> array ('r' => 'Risalto'),
		'Walk'			=> array ('m' => 'Marcia'),
		'Pool'			=> array ('v' => 'Vasque / Pozze'),
		'SingleAnchor'		=> array ('a' => 'Armo'),
		'PineTree02'		=> array ('s' => 'Sapin / Abete'),
		'CarriageReturn'	=> array ('l' => 'retour à la _l_igne'),
		'Slide'			=> array ('t' => 'Toboga')
	),
	'en1.1' => array (
		'RoundedVertical'	=> array ('w' => 'Waterfall'),
		'RoundedDownClimb'	=> array ('d' => 'Downclimb'),
		'Walk'			=> array ('t' => 'Track'),
		'Pool'			=> array ('p' => 'Pool'),
		'SingleAnchor'		=> array ('a' => 'Anchor'),
		'PineTree02'		=> array ('v' => 'Vegetal - Pine tree'),
		'CarriageReturn'	=> array ('l' => 'retour à la _l_igne'),
		'Slide'			=> array ('s' => 'Slide')
	),
	'fr2.1' => array (
		'Option'			=> array ('op' => 'Option'),
		'Vertical'			=> array ('vv' => 'Verticale verticale', 'vq' => 'Verticale quelconque'),
		'WetVertical'			=> array ('cv' => 'Cascade verticale'),
		'RoundedVertical'		=> array ('vr' => 'Verticale ronde'),
		'WetRoundedVertical'		=> array ('ca' => 'Cascade arrondie'),
		'LeanedVertical'		=> array ('vi' => 'Verticale inclinée', 'pi' => 'Plan incliné'),
		'WetLeanedVertical'		=> array ('ci' => 'Cascade inclinée'),
		'ObliqueVertical'		=> array ('vo' => 'Verticale oblique'),
		'WetObliqueVertical'		=> array ('co' => 'Cascade oblique'),
		'SlightOverhangingVertical'	=> array ('vd' => 'Verticale déversante'),
		'WetSlightOverhangingVertical'	=> array ('cd' => 'Cascade déversante'),
		'OverhangingVertical'		=> array ('vs' => 'Verticale surplombante'),
		'WetOverhangingVertical'	=> array ('cs' => 'Cascade surplombante'),
		'Slide'				=> array ('to' => 'Toboggan'),
		'Downclimb'			=> array ('re' => 'Ressaut', 'rv' => 'Ressaut vertical', 'rq' => 'Ressaut quelconque'),
		'RoundedDownClimb'		=> array ('ra' => 'Ressaut arrondi'),
		'LeanedDownClimb'		=> array ('ri' => 'Ressaut incliné'),
		'ObliqueDownClimb'		=> array ('ro' => 'Ressaut oblique'),
		'SlightOverhangingDownClimb'	=> array ('rd' => 'Ressaut déversant'),
		'OverhangingDownClimb'		=> array ('rs' => 'Ressaut surplombant'),
		'Walk'				=> array ('ma' => 'Marche', 'mi' => 'Marche intermédiaire'),
		'WetWalk'			=> array ('mr' => 'Marche en rivière'),
		'LongWalk'			=> array ('ml' => 'Marche longue'),
		'Pool'				=> array ('va' => 'Vasque'),
		'SingleAnchor'			=> array ('as' => 'Amarrage simple', 'au' => 'Amarrage unique'),
		'DoubleAnchor'			=> array ('ad' => 'Amarrage double', 'am' => 'Amarrage multiple'),
		'NaturalAnchor'			=> array ('an' => 'Amarrage naturel'),
		'PineTree01'			=> array ('sa' => 'Sapin'),
		'PineTree02'			=> array ('sr' => 'Sapin rond'),
		'ExitPoint'			=> array ('ex' => 'Exit', 'ec' => 'Echappatoire'),
		'EntryPoint'			=> array ('en' => 'Entrée'),
		'CarriageReturn'		=> array ('cr' => 'Carriage return', 'rl' => 'retour à la ligne'),
	),
	'en2.1' => array (
		'RoundedVertical'	=> array ('rv' => 'Rounded vertical'),
		'Vertical'		=> array ('vd' => 'Vertical drop'),
		'RoundedDownClimb'	=> array ('rd' => 'Rounded Downclimb'),
		'Downclimb'		=> array ('dc' => 'Downclimb'),
		//'OverhangingVertical'	=> array ('ov' => 'Overhanging vertical', 'od' => 'Overhanging drop'),
		'Walk'			=> array ('wa' => 'Walk'),
		'LongWalk'		=> array ('ml' => 'Long walk'),
		'Pool'			=> array ('po' => 'Pool'),
		'Slide'			=> array ('sl' => 'Slide'),
		'SingleAnchor'		=> array ('sa' => 'Single anchor'),
		'DoubleAnchor'		=> array ('da' => 'Double anchor'),
		'NaturalAnchor'		=> array ('na' => 'Natural anchor'),
		'PineTree02'		=> array ('pt' => 'Pine tree'),
		'CarriageReturn'	=> array ('cr' => 'Carriage return')
	)
);
/*

Vertical (sans eau)	Cascade (avec eau)	Ressaut
VQ			CQ			RQ
VV	VA		CV	CA		RV	RA
VI			CI			RI
VO			CO			RO
VD			CD			RD
VS			CS			RS
VB			CB			RB
*/

$allowedOptions = array (
	'canyonName' => array (
		'str', 'Nom du canyon',
		0, 255,
	),
	'fontHeight' => array (
		'int', 'Taille de la police',
		0, 100,
		16,
	),
	'belowBackground' => array (
		'bool', 'Présence du fond inférieur',
		0, 1,
		1,
	),
	'aboveBackground' => array (
		'bool', 'Présence du fond supérieur',
		0, 1,
		1,
	),
	'belowBackgroundColor' => array (
		'str', 'Couleur du fond inférieur',
		6, 6,
		'ae6a5a',
	),
	'aboveBackgroundColor' => array (
		'str', 'Couleur du fond supérieur',
		6, 6,
		'b5b5b5',
	),
	'pageWidth' => array (
		'int', 'Largeur de la page',
		0, 9999999,
		297,
	),
	'pageHeight' => array (
		'int', 'Hauteur de la page',
		0, 9999999,
		210,
	),
	'submittedHeader' => array (
		'bool', 'Affichage de la chaîne soumise',
		0, 1,
		0,
	),
);
?>
