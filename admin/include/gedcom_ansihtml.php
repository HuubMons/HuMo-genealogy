<?php
function ansihtml($text){

	/*
	192	À	208	Ð	224	à	240	ð
	193	Á	209	Ñ	225	á	241	ñ
	194	Â	210	Ò	226	â	242	ò
	195	Ã	211	Ó	227	ã	243	ó
	196	Ä	212	Ô	228	ä	244	ô
	197	Å	213	Õ	229	å	245	õ
	198	Æ	214	Ö	230	æ	246	ö
	199	Ç	215	×	231	ç	247	÷
	200	È	216	Ø	232	è	248	ø
	201	É	217	Ù	233	é	249	ù
	202	Ê	218	Ú	234	ê	250	ú
	203	Ë	219	Û	235	ë	251	û
	204	Ì	220	Ü	236	ì	252	ü
	205	Í	221	Ý	237	í	253	ý
	206	Î	222	Þ	238	î	254	þ
	207	Ï	223	ß	239	ï	255	ÿ
	*/

	$text = str_replace(chr(229),'å', $text); //å
	$text = str_replace(chr(197),'Å', $text); //Å
	$text = str_replace(chr(228),'ä', $text); //ä
	$text = str_replace(chr(196),'Ä', $text); //Ä
	$text = str_replace(chr(225),'á', $text); //á
	$text = str_replace(chr(193),'Á', $text); //Á
	$text = str_replace(chr(224),'à', $text); //à
	$text = str_replace(chr(192),'À', $text); //À
	$text = str_replace(chr(230),'æ', $text); //æ
	$text = str_replace(chr(198),'Æ', $text); //Æ
	$text = str_replace(chr(194),'Â', $text); //Â
	$text = str_replace(chr(226),'â', $text); //â
	$text = str_replace(chr(227),'ã', $text); //ã
	$text = str_replace(chr(195),'Ã', $text); //Ã
	$text = str_replace(chr(231),'ç', $text); //ç
	$text = str_replace(chr(199),'Ç', $text); //Ç
	$text = str_replace(chr(233),'é', $text); //é
	$text = str_replace(chr(201),'É', $text); //É
	$text = str_replace(chr(234),'ê', $text); //ê
	$text = str_replace(chr(202),'Ê', $text); //Ê
	$text = str_replace(chr(235),'ë', $text); //ë
	$text = str_replace(chr(203),'Ë', $text); //Ë
	$text = str_replace(chr(232),'è', $text); //è
	$text = str_replace(chr(200),'È', $text); //È
	$text = str_replace(chr(238),'î', $text); //î
	$text = str_replace(chr(206),'Î', $text); //Î
	$text = str_replace(chr(237),'í', $text); //í
	$text = str_replace(chr(205),'Í', $text); //Í
	$text = str_replace(chr(236),'ì', $text); //ì
	$text = str_replace(chr(204),'Ì', $text); //Ì
	$text = str_replace(chr(239),'ï', $text); //ï
	$text = str_replace(chr(207),'Ï', $text); //Ï
	$text = str_replace(chr(241),'ñ', $text); //ñ
	$text = str_replace(chr(209),'Ñ', $text); //Ñ
	$text = str_replace(chr(246),'ö', $text); //ö
	$text = str_replace(chr(214),'Ö', $text); //Ö
	$text = str_replace(chr(242),'ò', $text); //ò
	$text = str_replace(chr(210),'Ò', $text); //Ò
	$text = str_replace(chr(243),'ó', $text); //ó
	$text = str_replace(chr(211),'Ó', $text); //Ó
	$text = str_replace(chr(248),'ø', $text); //ø
	$text = str_replace(chr(216),'Ø', $text); //Ø
	$text = str_replace(chr(244),'ô', $text); //ô
	$text = str_replace(chr(212),'Ô', $text); //Ô
	$text = str_replace(chr(245),'õ', $text); //õ
	$text = str_replace(chr(213),'Õ', $text); //Õ
	$text = str_replace(chr(252),'ü', $text); //ü
	$text = str_replace(chr(220),'Ü', $text); //Ü
	$text = str_replace(chr(250),'ú', $text); //ú
	$text = str_replace(chr(218),'Ú', $text); //Ú
	$text = str_replace(chr(217),'Ù', $text); //Ù
	$text = str_replace(chr(249),'ù', $text); //ù
	$text = str_replace(chr(251),'û', $text); //û
	$text = str_replace(chr(219),'Û', $text); //Û
	$text = str_replace(chr(253),'ý', $text); //ý
	$text = str_replace(chr(221),'Ý', $text); //Ý
	$text = str_replace(chr(255),'ÿ', $text); //ÿ
	return $text;	
}
?>