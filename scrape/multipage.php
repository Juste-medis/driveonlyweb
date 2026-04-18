<?php

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

$categories =  [
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138427_LUFTFAHRWERKE-luftfahrwerke.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138543_Luftfahrwerk-Kits-luftfahrwerk-kits.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c167779_Luftfahrwerk-Kits-mit-Air-Management--167779.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138544_Luftfahrwerk-Einsteigerkits--138544.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138545_Antiwank-Kit--138545.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138552_Fittinge---Luftleitungen---Schlaeuche--138552.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138553_Kompressoren--138553.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c7487_FAHRWERKSTECHNIK-fahrwerkstechnik.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c148008_937--148008.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c161538_S--161538.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138439_VERSTELLSCHLUeSSEL--138439.html"
];

foreach ($categories as $cat_url){
	echo "<h2>Catégories : $cat_url</h2>";
	
	$categorie =file_get_contents($cat_url);

	$produits = explode('<div class="inside">',$categorie);

	for($i=3;$i<count($produits)-1;$i++)
	{
		$lien_prod = get_string_between($produits[$i],'<a href="','"');
		echo "<strong>Produit :</strong> $lien_prod.'<hr>";
	
	if (!$lien_prod) continue;
	
	$text = file_get_contents($lien_prod);
	if (!$text) {
		echo "Impossible de charger $lien_prod<hr>";
		continue;
	}

	
	$images = 	get_string_between($text, '<div class="swiper-slide" >', '<script type="text/mustache">');
	$images2 = explode('<div class="swiper-slide-inside ">',$text);

	for($j=1; $j<count($images2); $j++)
	{	

		$img = 	get_string_between($images2[$j], 'src="', '"');
		if(strpos($img,'popup_images'))
		{
		
		echo 'https://www.tatechnix.de/tatechnix/gx/'.$img.'<br>';
		}
}
	
	

	echo '<hr>';
}

}





	

?>

