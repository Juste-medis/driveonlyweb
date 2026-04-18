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
    "https://www.tatechnix.de/tatechnix/gx/?cat=c148008_937--148008.html",
    "https://www.tatechnix.de/tatechnix/gx/?cat=c161538_S--161538.html",
    "https://www.tatechnix.de/tatechnix/gx/?cat=c138439_VERSTELLSCHLUeSSEL--138439.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c161445_937-937-161445.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c161538_S--161538.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c161949_S-s-161949.html",
	"https://www.tatechnix.de/tatechnix/gx/?cat=c138439_VERSTELLSCHLUeSSEL--138439.html",
    "https://www.tatechnix.de/tatechnix/gx/?cat=c166944_B9--Typ-7-----b9-typ-7.html"
];

foreach ($categories as $cat_url){
    echo "<h2>Catégories : $cat_url</h2>";
    
    $categorie = file_get_contents($cat_url);

    $produits = explode('<div class="inside">', $categorie);

    for($i = 3; $i < count($produits) - 1; $i++)
    {
        $lien_prod = get_string_between($produits[$i], '<a href="', '"');
        echo "<strong>Produit :</strong> $lien_prod.'<hr>";
    
        if (!$lien_prod) continue;
    
        $text = file_get_contents($lien_prod);
        if (!$text) {
            echo "Impossible de charger $lien_prod<hr>";
            continue;
        }

        $images = get_string_between($text, '<div class="swiper-slide" >', '<script type="text/mustache">');
        $images2 = explode('<div class="swiper-slide-inside ">',$text);

        for($j = 1; $j < count($images2); $j++)
        {    
            $img = get_string_between($images2[$j], 'src="', '"');
            if (strpos($img, 'popup_images'))
            {
                echo 'https://www.tatechnix.de/tatechnix/gx/' . $img . '<br>';
            }
        }

        echo '<hr>';
    }
}

?>
