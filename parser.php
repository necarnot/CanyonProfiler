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

function parsed($p, $canyonStr) {
	// Tidying the user-provided string
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
		$canyonStr = $p->defaultVersion.':'.$canyonStr;
		error_log ('Missing colon. Adding default version. Now, canyonStr='.$canyonStr);
	}
	// On limite le découpage à deux éléments (header | tout ce qui reste)
	$strs = explode(':', $canyonStr, 2);
	$strVersion = trim($strs[0]);
	// Si la chaîne est fournie sans version, on lui fournit la version par défaut
	if (isNullOrEmptyString($strVersion)) {
		$strVersion = $p->defaultVersion;
	}
	// If this version is not supported, we return an error
	global $syntaxes;
	if (!array_key_exists($strVersion, $syntaxes)) {
		error_log('Syntax '.$strVersion. ' is not supported');
		return -1;
	}
	$p->syntaxVersion = $strVersion;
	// Shift by two-caracters to keep the version number
	$syntaxVersionNumber = substr($strVersion, 2);
	global $syntaxesProperties;
	$syntaxLength = $syntaxesProperties[$syntaxVersionNumber]['length'];
	// Once we're sure it's a supported syntax, we store all these aliases into a temp array
	$syntaxSymbols = $syntaxes[$strVersion];

	// The leftover is the description of the canyon
	$canyonStr = trim($strs[1]);
	// If there is no description at all, we return an error
	if (!$canyonStr) {
		error_log('200: Error: Empty description');
		return -1;
	}

	// Le tout premier caractère est le séparateur dynamique
	$p->separator = substr($canyonStr, 0, 1);
	// error_log('210:separator=_'.$p->separator.'_');
	# On enlève le tout premier séparateur
	$canyonStr = substr($canyonStr, 1);
	// Tableau des éléments fournis par l'utilisateur
	$inStrs = explode($p->separator, $canyonStr);
	// On initialise la chaîne interne, telle qu'on la traitera dans le script
	$outStr = '';
	// Pour chaque chaîne fournie par l'utilisateur
	foreach($inStrs as $inStr) {
		$inStr = trim($inStr);
		$item = strtolower(substr($inStr, 0, $syntaxLength));
		$value = substr($inStr, $syntaxLength);
		// Removing comments between parenthesis
		$value = preg_replace('/\(.*\)*/', '', $value);
		// On cherche dans chaque liste de symboles si on trouve la proposition
		foreach($syntaxSymbols as $key => $aliases) {
			if (array_key_exists($item, $aliases)) {
				$outStr = $outStr . $p->separator . $key . $value;
				$tmpItem = new $key($value);
				$tmpItem->setInStr($inStr);
				array_push($p->items, $tmpItem);
				break;
			}
		}
	}
	# On enlève le tout premier séparateur
	$outStr = substr($outStr, 1);
	return $outStr;
}
?>
