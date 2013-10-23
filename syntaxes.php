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
		'RoundedVertical'	=> array ('ca','cb'),	// Cascade arrondie, cascade bombée
		'Vertical'		=> array ('cv'),	// Cascade verticale
		'RoundedDownClimb'	=> array ('ra'),	// Ressaut arrondi
		'Downclimb'		=> array ('re'),	// Ressaut
		'OverhangingVertical'	=> array ('cs','cd'),	// Cascade surplombante, cascade déversante
		'Walk'			=> array ('ma','mi'),	// Marche, marche intermédiaire
		'LongWalk'		=> array ('ml'),	// Marche longue
		'Pool'			=> array ('va'),	// Vasque
		'Slide'			=> array ('pi'),	// Plan incliné
		'SingleAnchor'		=> array ('as','au'),	// Amarrage simple, amarrage unique
		'DoubleAnchor'		=> array ('ad','am'),	// Amarrage double, amarrage multiple
		'NaturalAnchor'		=> array ('an'),	// Amarrage naturel
		'PineTree'		=> array ('sa'),	// Sapin
		'CarriageReturn'	=> array ('cr','rl'),	// Carriage return, retour à la ligne
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
		'CarriageReturn'	=> array ('cr'),	// Carriage return, retour à la ligne
		'Slide'			=> array ('sl')
	)
);

$syntaxes2 = array (
	'fr1.1' => array (
		'RoundedVertical'	=> array ('c' => 'Cascade'),
		'RoundedDownClimb'	=> array ('r' => 'Ressaut'),
		'Walk'			=> array ('m' => 'Marche'),
		'Pool'			=> array ('v' => 'Vasque'),
		'SingleAnchor'		=> array ('a' => 'Amarrage'),
		'PineTree'		=> array ('s' => 'Sapin'),
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
		'PineTree'		=> array ('p' => 'Pino'),
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
		'PineTree'		=> array ('s' => 'Sapin / Abete'),
		'CarriageReturn'	=> array ('l' => 'retour à la _l_igne'),
		'Slide'			=> array ('t' => 'Toboga')
	),
	'en1.1' => array (
		'RoundedVertical'	=> array ('w' => 'Waterfall'),
		'RoundedDownClimb'	=> array ('d' => 'Downclimb'),
		'Walk'			=> array ('t' => 'Track'),
		'Pool'			=> array ('p' => 'Pool'),
		'SingleAnchor'		=> array ('a' => 'Anchor'),
		'PineTree'		=> array ('v' => 'Vegetal - Pine tree'),
		'CarriageReturn'	=> array ('l' => 'retour à la _l_igne'),
		'Slide'			=> array ('s' => 'Slide')
	),
	'fr2.1' => array (
		'RoundedVertical'	=> array ('ca' => 'Cascade arrondie', 'cb' => 'Cascade bombée'),
		'Vertical'		=> array ('cv' => 'Cascade verticale'),
		'RoundedDownClimb'	=> array ('ra' => 'Ressaut arrondi'),
		'Downclimb'		=> array ('re' => 'Ressaut'),
		'OverhangingVertical'	=> array ('cs' => 'Cascade surplombante', 'cd' => 'Cascade déversante'),
		'Walk'			=> array ('ma' => 'Marche', 'mi' => 'Marche intermédiaire'),
		'LongWalk'		=> array ('ml' => 'Marche longue'),
		'Pool'			=> array ('va' => 'Vasque'),
		'Slide'			=> array ('pi' => 'Plan incliné'),
		'SingleAnchor'		=> array ('as' => 'Amarrage simple', 'au' => 'Amarrage unique'),
		'DoubleAnchor'		=> array ('ad' => 'Amarrage double', 'am' => 'Amarrage multiple'),
		'NaturalAnchor'		=> array ('an' => 'Amarrage naturel'),
		'PineTree'		=> array ('sa' => 'Sapin'),
		'CarriageReturn'	=> array ('cr' => 'Carriage return', 'rl' => 'retour à la ligne'),
		'Slide'			=> array ('to' => 'Toboggan')
	),
	'en2.1' => array (
		'RoundedVertical'	=> array ('rv' => 'Rounded vertical'),
		'Vertical'		=> array ('vd' => 'Vertical drop'),
		'RoundedDownClimb'	=> array ('rd' => 'Rounded Downclimb'),
		'Downclimb'		=> array ('dc' => 'Downclimb'),
		'OverhangingVertical'	=> array ('ov' => 'Overhanging vertical', 'od' => 'Overhanging drop'),
		'Walk'			=> array ('wa' => 'Walk'),
		'LongWalk'		=> array ('ml' => 'Long walk'),
		'Pool'			=> array ('po' => 'Pool'),
		'Slide'			=> array ('sl' => 'Slide'),
		'SingleAnchor'		=> array ('sa' => 'Single anchor'),
		'DoubleAnchor'		=> array ('da' => 'Double anchor'),
		'NaturalAnchor'		=> array ('na' => 'Natural anchor'),
		'PineTree'		=> array ('pt' => 'Pine tree'),
		'CarriageReturn'	=> array ('cr' => 'Carriage return')
	)
);


?>
