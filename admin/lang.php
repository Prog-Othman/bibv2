<?php


$lang = 'fr'; // langue par default

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

$langFile = ROOT_PATH . "/lang/{$lang}.php";

if (!file_exists($langFile)) {
    $langFile = ROOT_PATH . "/lang/fr.php"; 
}

$translations = include($langFile);

function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}
