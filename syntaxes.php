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
		'RoundedVertical'	=> array ('c'),		// Cascade
		'RoundedDownClimb'	=> array ('r'),		// Ressaut
		'Walk'			=> array ('m'),		// Marche
		'Pool'			=> array ('v'),		// Vasque
		'SingleAnchor'		=> array ('a'),		// Amarrage
		'PineTree'		=> array ('s'),		// Sapin
		'CarriageReturn'	=> array ('l'),		// retour à la _L_igne
		'Slide'			=> array ('t')		// Toboggan
	),
	// Thanks to Antilolo
	'es1.1' => array (
		'RoundedVertical'	=> array ('c'),		// Cascada
		'RoundedDownClimb'	=> array ('r'),		// Resalte
		'Walk'			=> array ('s'),		// Sendero
		'Pool'			=> array ('b'),		// Badina
		'SingleAnchor'		=> array ('a'),		// Anclaje
		'PineTree'		=> array ('p'),		// Pino
		'CarriageReturn'	=> array ('l'),		// retour à la _l_igne
		'Slide'			=> array ('t')		// Tobagan
	),
	// Thanks to Marie
	'it1.1' => array (
		'RoundedVertical'	=> array ('c'),		// Cascata
		'RoundedDownClimb'	=> array ('r'),		// Risalto
		'Walk'			=> array ('m'),		// Marcia
		'Pool'			=> array ('v'),		// Vasque / Pozze
		'SingleAnchor'		=> array ('a'),		// Armo
		'PineTree'		=> array ('s'),		// Sapin / Abete
		'CarriageReturn'	=> array ('l'),		// retour à la _l_igne
		'Slide'			=> array ('t')		// Toboga
	),
	'en1.1' => array (
		'RoundedVertical'	=> array ('w'),		// Waterfall
		'RoundedDownClimb'	=> array ('d'),		// Downclimb
		'Walk'			=> array ('t'),		// Track
		'Pool'			=> array ('p'),		// Pool
		'SingleAnchor'		=> array ('a'),		// Anchor
		'PineTree'		=> array ('v'),		// Vegetal - Pine tree
		'CarriageReturn'	=> array ('l'),		// retour à la _l_igne
		'Slide'			=> array ('s')		// Slide
	),
	'fr2.1' => array (
		'RoundedVertical'	=> array ('ca','cb'),	// Cascade arrondie
		'Vertical'		=> array ('cv'),	// Cascade verticale
		'RoundedDownClimb'	=> array ('ra'),	// Ressaut arrondi
		'Downclimb'		=> array ('re'),	// Ressaut
		'OverhangingVertical'	=> array ('cs','cd'),	// Cascade surplombante
		'Walk'			=> array ('ma','mi'),	// Marche
		'LongWalk'		=> array ('ml'),	// Marche longue
		'Pool'			=> array ('va'),	// Vasque
		'Slide'			=> array ('pi'),	// Plan incliné
		'SingleAnchor'		=> array ('as','au'),	// Amarrage simple
		'DoubleAnchor'		=> array ('ad','am'),	// Amarrage double
		'NaturalAnchor'		=> array ('an'),	// Amarrage naturel
		'PineTree'		=> array ('sa'),	// Sapin
		'CarriageReturn'	=> array ('cr', 'rl'),	// Carriage return, retour à la ligne
		'Slide'			=> array ('to')		// Toboggan
	),
	'en2.1' => array (
		'RoundedVertical'	=> array ('wr','we'),
		'Vertical'		=> array ('cv'),
		'RoundedDownClimb'	=> array ('ra'),
		'Downclimb'		=> array ('re'),
		'OverhangingVertical'	=> array ('cs','cd'),
		'Walk'			=> array ('wa'),
		'LongWalk'		=> array ('ml'),
		'Pool'			=> array ('po'),
		'Slide'			=> array ('pi'),
		'SingleAnchor'		=> array ('sb'),
		'DoubleAnchor'		=> array ('db'),
		'NaturalAnchor'		=> array ('an'),
		'PineTree'		=> array ('sa'),
		'CarriageReturn'	=> array ('cr'),		// Carriage return, retour à la ligne
		'Slide'			=> array ('sl')
	)
);

?>
