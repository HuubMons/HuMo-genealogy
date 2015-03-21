<?php
function anselhtml($text){
	// 232    ä
	$search=chr(232).'a'; $text = str_replace($search,'&auml;', $text); //ä
	$search=chr(232).'A'; $text = str_replace($search,'&Auml;', $text); //Ä
	$search=chr(232).'e'; $text = str_replace($search,'&euml;', $text); //ë
	$search=chr(232).'E'; $text = str_replace($search,'&Euml;', $text); //Ë
	$search=chr(232).'i'; $text = str_replace($search,'&iuml;', $text); //ï
	$search=chr(232).'I'; $text = str_replace($search,'&Iuml;', $text); //Ï
	$search=chr(232).'o'; $text = str_replace($search,'&ouml;', $text); //ö
	$search=chr(232).'O'; $text = str_replace($search,'&Ouml;', $text); //Ö
	$search=chr(232).'u'; $text = str_replace($search,'&uuml;', $text); //ü
	$search=chr(232).'U'; $text = str_replace($search,'&Uuml;', $text); //Ü

	// 224    à
	$search=chr(224).'a'; $text = str_replace($search,'&agrave;', $text); //à
	$search=chr(224).'A'; $text = str_replace($search,'&Agrave;', $text); //À
	$search=chr(224).'e'; $text = str_replace($search,'&egrave;', $text); //è
	$search=chr(224).'E'; $text = str_replace($search,'&Egrave;', $text); //È
	$search=chr(224).'i'; $text = str_replace($search,'&igrave;', $text); //ì
	$search=chr(224).'I'; $text = str_replace($search,'&Igrave;', $text); //Ì
	$search=chr(224).'o'; $text = str_replace($search,'&ograve;', $text); //ò
	$search=chr(224).'O'; $text = str_replace($search,'&Ograve;', $text); //Ò
	$search=chr(224).'u'; $text = str_replace($search,'&ugrave;', $text); //ù
	$search=chr(224).'U'; $text = str_replace($search,'&Ugrave;', $text); //Ù

	// 225    á
	$search=chr(225).'a'; $text = str_replace($search,'&aacute;', $text); //á
	$search=chr(225).'A'; $text = str_replace($search,'&Aacute;', $text); //Á
	$search=chr(225).'e'; $text = str_replace($search,'&eacute;', $text); //é
	$search=chr(225).'E'; $text = str_replace($search,'&Eacute;', $text); //É
	$search=chr(225).'i'; $text = str_replace($search,'&iacute;', $text); //í
	$search=chr(225).'I'; $text = str_replace($search,'&Iacute;', $text); //Í
	$search=chr(225).'o'; $text = str_replace($search,'&oacute;', $text); //ó
	$search=chr(225).'O'; $text = str_replace($search,'&Oacute;', $text); //Ó
	$search=chr(225).'u'; $text = str_replace($search,'&uacute;', $text); //ú
	$search=chr(225).'U'; $text = str_replace($search,'&Uacute;', $text); //Ú

	// 228    ~
	$search=chr(228).'a'; $text = str_replace($search,'&atilde;', $text); //ã
	$search=chr(228).'A'; $text = str_replace($search,'&Atilde;', $text); //Ã
	//$search=chr(228).'e'; $text = str_replace($search,'&etilde;', $text); //~e
	//$search=chr(228).'E'; $text = str_replace($search,'&Etilde;', $text); //~E
	//$search=chr(228).'i'; $text = str_replace($search,'&itilde;', $text); //~i
	//$search=chr(228).'I'; $text = str_replace($search,'&Itilde;', $text); //~I
	$search=chr(228).'o'; $text = str_replace($search,'&otilde;', $text); //õ
	$search=chr(228).'O'; $text = str_replace($search,'&Otilde;', $text); //Õ
	//$search=chr(228).'u'; $text = str_replace($search,'&utilde;', $text); //~u
	//$search=chr(228).'U'; $text = str_replace($search,'&Utilde;', $text); //~U

	// 234    ê
	$search=chr(234).'a'; $text = str_replace($search,'&acirc;', $text); //â
	$search=chr(234).'A'; $text = str_replace($search,'&Acirc;', $text); //Â
	$search=chr(234).'e'; $text = str_replace($search,'&ecirc;', $text); //ê
	$search=chr(234).'E'; $text = str_replace($search,'&Ecirc;', $text); //Ê
	$search=chr(234).'i'; $text = str_replace($search,'&icirc;', $text); //î
	$search=chr(234).'I'; $text = str_replace($search,'&Icirc;', $text); //Î
	$search=chr(234).'o'; $text = str_replace($search,'&ocirc;', $text); //ô
	$search=chr(234).'O'; $text = str_replace($search,'&Ocirc;', $text); //Ô
	$search=chr(234).'u'; $text = str_replace($search,'&ucirc;', $text); //û
	$search=chr(234).'U'; $text = str_replace($search,'&Ucirc;', $text); //Û

	return $text;
}
?>