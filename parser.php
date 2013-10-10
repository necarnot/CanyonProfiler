<?php

// This function parses the user input string, and :
// - tries to detect the version of the format "fr0.1"
// - expects a colon ":"
// - expects a separator, that will be used to identify the next parts of the string
// According to the version, it tries to match the localized symbols to the internal
// symbols known by the program.
// This allows the use of an infinite number of translations, and inside each translation,
// it allows the use of aliases for each symbol.
// Between symbols, an attention must be payed to avoid duplicates amongst aliases.

function parsed($canyonStr) {
	// Every syntax has its own set of properties
	$syntaxProperties = array (
		'0.1' => array (
			'length' => 1
		),
		'1.1' => array (
			'length' => 2
		),
		'2.1' => array (
			'length' => 10
		)
	);
	$syntax = array (
		'fr0.1' => array (
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
		'es0.1' => array (
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
		'it0.1' => array (
			'ca' => array ('c'),		// Cascata
			'ra' => array ('r'),		// Risalto
			'ma' => array ('m'),		// Marcia
			'va' => array ('v'),		// Vasque / Pozze
			'as' => array ('a'),		// Armo
			'sa' => array ('s'),		// Sapin / Abete
			'cr' => array ('l'),		// retour à la _l_igne
			'to' => array ('t')		// Toboga
		),
		'en0.1' => array (
			'ca' => array ('w'),		// Waterfall
//			'ra' => array ('r'),		// Ressaut ???
			'ma' => array ('t'),		// Track ???
			'va' => array ('p'),		// Pool
			'as' => array ('a'),		// Anchor
//			'sa' => array ('p'),		// Pine tree
			'cr' => array ('l'),		// retour à la _l_igne
			'to' => array ('s')		// Slide
		),
		'fr1.1' => array (
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
		'en0.1' => array (
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

	$defaultVersion = 'fr1.1';
	// Protection basique contre les tentatives de piratage
	$canyonStr = htmlspecialchars($canyonStr);
	// On supprime les espaces de début et fin
	$canyonStr = trim($canyonStr);

	//
	// Détermination de la version de la chaîne
	//
	// Si la chaîne ne contient pas de marqueur d'en-tête, on lui force une valeur par défaut
	if (strpos($canyonStr, ':') === false) {
		// Si la chaîne est fournie sans version, on lui fournit la version par défaut
		$canyonStr = $defaultVersion.':'.$canyonStr;
		error_log ('Missing colon. Adding default version. Now, canyonStr='.$canyonStr);
	}
	// On limite le découpage à deux éléments (header | tout ce qui reste)
	$strs = explode(':', $canyonStr, 2);
	$strVersion = trim($strs[0]);
	// Si la chaîne est fournie sans version, on lui fournit la version par défaut
	if (isNullOrEmptyString($strVersion)) {
		$strVersion = $defaultVersion;
	}
	// If this version is not supported, we return an error
	if (!array_key_exists($strVersion, $syntax)) {
		error_log('Syntax '.$strVersion. ' is not supported');
		return -1;
	}
	// Shift by two-caracters to keep the version number
	$syntaxVersionNumber = substr($strVersion, 2);
	$syntaxLength = $syntaxProperties[$syntaxVersionNumber]['length'];
	// Once we're sure it's a supported syntax, we store all these aliases into a temp array
	$syntaxSymbols = $syntax[$strVersion];

	// The leftover is the description of the canyon
	$canyonStr = trim($strs[1]);
	// If there is no description at all, we return an error
	if (!$canyonStr) {
		error_log('200: Error: Empty description');
		return -1;
	}

	// Le tout premier caractère est le séparateur dynamique
	$separator = substr($canyonStr, 0, 1);
	error_log('210:separator='.$separator);
	$canyonStr = substr($canyonStr, 1);
	// Tableau des éléments fournis par l'utilisateur
	$inStrs = explode($separator, $canyonStr);
	// On initialise la chaîne interne, telle qu'on la traitera dans le script
	$outStr = '';
	// Pour chaque chaîne fournie par l'utilisateur
	foreach($inStrs as $inStr) {
		$item = strtolower(substr(trim($inStr), 0, $syntaxLength));
		$value = substr(trim($inStr), $syntaxLength);
		// Removing comments between parenthesis
		$value = preg_replace('/\(.*\)*/', '', $value);
		// On cherche dans chaque liste de symboles si on trouve la proposition
		$found = false;
		foreach($syntaxSymbols as $key => $aliases) {
			if (is_numeric(array_search($item, $aliases))) {
				$found = true;
				$outStr = $outStr . '/' . $key . $value;
				break;
			}
		}
	}
	return $outStr;
}
?>
