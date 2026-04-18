<?php

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}


$text = file_get_contents("https://www.tatechnix.de/tatechnix/gx/performance-teile/-138487/mini-139085/ii-generation/r56-r61/ta-technix-downpipe-mit-katalysator-passend-fuer-mini-citroen-peugeot.html");

	
$images = 	get_string_between($text, '<div class="swiper-slide" >', '<script type="text/mustache">');
$images2 = explode('<div class="swiper-slide-inside ">',$images);

for($i=1; $i<count($images2); $i++)
{
	$img = 	get_string_between($images2[$i], 'src="', '"');
	echo 'https://www.tatechnix.de/tatechnix/gx/'.$img.'<br>';
	
}
	

?>

