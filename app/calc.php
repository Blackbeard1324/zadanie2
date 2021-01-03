<?php
require_once dirname(__FILE__).'/../config.php';

// KONTROLER strony kalkulatora

// W kontrolerze niczego nie wysyła się do klienta.
// Wysłaniem odpowiedzi zajmie się odpowiedni widok.
// Parametry do widoku przekazujemy przez zmienne.

//ochrona kontrolera - poniższy skrypt przerwie przetwarzanie w tym punkcie gdy użytkownik jest niezalogowany
include _ROOT_PATH.'/app/security/check.php';

//pobranie parametrów
function getParams(&$kwota, &$lata, &$procent){
	$kwota = isset($_REQUEST['kwota']) ? $_REQUEST['kwota'] : null;
	$lata = isset($_REQUEST['lata']) ? $_REQUEST['lata'] : null;
	$procent = isset($_REQUEST['procent']) ? $_REQUEST['procent'] : null;
}

//walidacja parametrów z przygotowaniem zmiennych dla widoku
function validate(&$kwota, &$lata, &$procent, &$messages){
	// sprawdzenie, czy parametry zostały przekazane
	if ( ! (isset($kwota) && isset($lata) && isset($procent))) {
		// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
		// teraz zakładamy, ze nie jest to błąd. Po prostu nie wykonamy obliczeń
		return false;
	}

	// sprawdzenie, czy potrzebne wartości zostały przekazane
    if ($kwota == "") {
        $messages [] = 'Nie podano kwoty pożyczki';
    }
    if ($lata == "") {
        $messages [] = 'Nie podano lat spłacania pożyczki';
    }
    if ($procent == "") {
        $messages [] = 'Nie podano procentu kredytu';
    }

    if (empty($messages)) {

        if (!is_numeric($kwota)) {
            $messages [] = 'Kwota nie jest liczbą całkowitą';
        }

        if (!is_numeric($lata)) {
            $messages [] = 'Podany okres czasu nie jest liczbą całkowitą';
        }
        if (!is_numeric($procent)) {
            $messages [] = 'Podane oprocentowanie nie jest liczbą całkowitą';
        }

    }
	if (count ( $messages ) != 0) return false;
	else return true;
}

function process(&$kwota,&$lata,&$procent,&$messages,&$result){
	global $role;
	
	//konwersja parametrów na int
    $kwota = floatval($kwota);
    $lata = floatval($lata);
    $procent = floatval($procent);
	$rata = $kwota / ($lata * 12);
	$result = $rata + ($rata * ($procent / 100));

	//wykonanie operacji
	if(($kwota > 999999999 || $lata > 25) && $role != 'admin'){
		$result = 'Jedynie administrator może wykonać obliczenia dla kwoty większej od 1 miliarda lub ponad 25 lat kredytu';
	}
		
}

//definicja zmiennych kontrolera
$kwota = null;
$lata = null;
$procent = null;
$result = null;
$messages = array();

//pobierz parametry i wykonaj zadanie jeśli wszystko w porządku
getParams($kwota,$lata,$procent);
if ( validate($kwota,$lata,$procent,$messages) ) { // gdy brak błędów
	process($kwota,$lata,$procent,$messages,$result);
}

// Wywołanie widoku z przekazaniem zmiennych
// - zainicjowane zmienne ($messages,$kwota,$lata,$procent,$result)
//   będą dostępne w dołączonym skrypcie
include 'calc_view.php';