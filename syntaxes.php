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
		'ca' => array ('c'),		// Cascade
		'ra' => array ('r'),		// Ressaut
		'ma' => array ('m'),		// Marche
		'va' => array ('v'),		// Vasque
		'as' => array ('a'),		// Amarrage
		'sa' => array ('s'),		// Sapin
		'cr' => array ('l'),		// retour à la _L_igne
		'to' => array ('t')		// Toboggan
	),
	// Thanks to Antilolo
	'es1.1' => array (
		'ca' => array ('c'),		// Cascada
		'ra' => array ('r'),		// Resalte
		'ma' => array ('s'),		// Sendero
		'va' => array ('b'),		// Badina
		'as' => array ('a'),		// Anclaje
		'sa' => array ('p'),		// Pino
		'cr' => array ('l'),		// retour à la _l_igne
		'to' => array ('t')		// Tobagan
	),
	// Thanks to Marie
	'it1.1' => array (
		'ca' => array ('c'),		// Cascata
		'ra' => array ('r'),		// Risalto
		'ma' => array ('m'),		// Marcia
		'va' => array ('v'),		// Vasque / Pozze
		'as' => array ('a'),		// Armo
		'sa' => array ('s'),		// Sapin / Abete
		'cr' => array ('l'),		// retour à la _l_igne
		'to' => array ('t')		// Toboga
	),
	'en1.1' => array (
		'ca' => array ('w'),		// Waterfall
//			'ra' => array ('r'),		// Ressaut ???
		'ma' => array ('t'),		// Track ???
		'va' => array ('p'),		// Pool
		'as' => array ('a'),		// Anchor
//			'sa' => array ('p'),		// Pine tree
		'cr' => array ('l'),		// retour à la _l_igne
		'to' => array ('s')		// Slide
	),
	'fr2.1' => array (
		'ca' => array ('ca','cb'),	// Cascade arrondie
		'cv' => array ('cv'),		// Cascade verticale
		'ra' => array ('ra'),		// Ressaut arrondi
		're' => array ('re'),		// Ressaut
		'cs' => array ('cs','cd'),	// Cascade surplombante
		'ma' => array ('ma','mi'),	// Marche
		'ml' => array ('ml'),		// Marche longue
		'va' => array ('va'),		// Vasque
		'pi' => array ('pi'),		// Plan incliné
		'as' => array ('as','au'),	// Amarrage simple
		'ad' => array ('ad','am'),	// Amarrage double
		'an' => array ('an'),		// Amarrage naturel
		'sa' => array ('sa'),		// Sapin
		'cr' => array ('cr', 'rl'),	// Carriage return, retour à la ligne
		'to' => array ('to')		// Toboggan
	),
	'en2.1' => array (
		'ca' => array ('wr','we'),
		'cv' => array ('cv'),
		'ra' => array ('ra'),
		're' => array ('re'),
		'cs' => array ('cs','cd'),
		'ma' => array ('wa'),
		'ml' => array ('ml'),
		'va' => array ('po'),
		'pi' => array ('pi'),
		'as' => array ('sb'),
		'ad' => array ('db'),
		'an' => array ('an'),
		'sa' => array ('sa'),
		'cr' => array ('cr'),		// Carriage return, retour à la ligne
		'to' => array ('sl')
	)
);

?>
