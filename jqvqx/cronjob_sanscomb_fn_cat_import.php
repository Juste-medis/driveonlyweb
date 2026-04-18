<?php
 
include('../config/config.inc.php');
include('../init.php');
$default_lang = 1;



//$data = Db::getInstance()->executeS('SELECT *  from catalogue2 where add_to_ps=0 limit 0,1');
$data = Db::getInstance()->executeS('SELECT * from  aa_import_table where ps_ids=0  order by id asc limit 0,1');
function generateCombinations(array $attributes)
{
    $combinations = array(array());
    foreach ($attributes as $options) {
        $new_combinations = array();
        foreach ($combinations as $combination) {
            foreach ($options as $option) {
                $new_combination = $combination;
                $new_combination[] = $option;
                $new_combinations[] = $new_combination;
            }
        }
        $combinations = $new_combinations;
    }
    return $combinations;
}

function cherche_group_att($group, $default_lang)
{
	$groups = AttributeGroupCore::getAttributesGroups($default_lang);
	$id_group=0;
	for($i=0; $i<count($groups);$i++)
	{
	if($groups[$i]['name']==$group)
		$id_group=$groups[$i]['id_attribute_group'];
	}
	if($id_group==0)
	{
		$attrib = new AttributeGroupCore();
		$attrib->name= [$default_lang =>$group];
		$attrib->public_name= [$default_lang =>$group];
		$attrib->group_type= "select";
		$attrib->add();
		$id_group = $attrib->id;
	}
	return($id_group);

}



 function cherche_att($attr,$id_attribute_group, $default_lang)
 {
	 $test=Attribute::getAttributes($default_lang, $not_null = false);
	 $taille_arr=0;
	for($i=0; $i<count($test);$i++)
	{
	if($test[$i]['name']==$attr and $test[$i]['id_attribute_group']==$id_attribute_group)
		$taille_arr=$test[$i]['id_attribute'];
	}
	
	return($taille_arr);
}


function cherche_mark($mark)
{


    $mark = Db::getInstance()->executeS('SELECT *  from '._DB_PREFIX_.'manufacturer where `name` like "%'.$mark.'%"');
    if(count($mark)==0)
    {
        return 0;
    }
    else
    {
        return $mark[0]['id_manufacturer'];
    }

}

function add_mark($mark,$lang)
{
    $mark = Db::getInstance()->executeS('Insert into '._DB_PREFIX_.'manufacturer(`name`,`active`) values("'.$mark.'",1)');
    $id= (int)Db::getInstance()->Insert_ID();

    $q = Db::getInstance()->executeS('Insert into '._DB_PREFIX_.'manufacturer_shop(`id_manufacturer`,`id_shop`) values('.$id.',1)');
    $q = Db::getInstance()->executeS('Insert into '._DB_PREFIX_.'manufacturer_lang(`id_manufacturer`,`id_lang`) values('.$id.','.$lang.')');
    return $id;
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

 
 function add_att_com($id_product, $combinationAttributes,$prix,$min_qte)
 {
	 
			$product = new Product( $id_product );
           
            $idProductAttribute = $product->addProductAttribute(
                (float)$prix, //price 
                (float)0, //weight
                0,        //unit_impact
                null ,    //ecotax
                100,   //quantity
                "",       //id_images
                strval("") , //reference
                strval(""), //suppliers
                strval(""), //ean13
                NULL, //default 
                NULL,  //location
                NULL,  //upc
                $min_qte,  //upc
                NULL,  //upc
                NULL,  //upc
                NULL,  //upc
                NULL //upc
                );
                 $product->addAttributeCombinaison($idProductAttribute,                     $combinationAttributes);     
 }
 
	
	
	for($p=0;$p<count($data);$p++)
	{
		$models = array();
		$categs = explode('|', $data[$p]['Categories']);
		$categs_id=array();
		//356
		for($cat=2; $cat <count($categs)-1; $cat++)
		{
			$id_categ = DB::getInstance()->getRow("SELECT id_category FROM " ._DB_PREFIX_. "category_lang WHERE `name` = '".trim(str_replace("'", "''", $categs[$cat]))."' and id_category>=26348");
			echo "SELECT id_category FROM " ._DB_PREFIX_. "category_lang WHERE `name` = '".trim(str_replace("'", "''", $categs[$cat]))."' and id_category>=26348<hr>";
			 if (!empty($id_categ)) {
			 	
			 	$categs_id[]=$id_categ['id_category'];
			 }
			 else
			 {
			 	$category = new Category;
				$category->active = 1;
				if($cat==1)
				{
				$category->id_parent = 26348;
				}
				else
				{
					$category->id_parent=$categs_id[$cat-3];
				}
				$category->name = [$default_lang => trim(str_replace('&amp;', 'et', $categs[$cat]))];
				$category->link_rewrite = array((int)$default_lang => Tools::link_rewrite( $categs[$cat]));
				$category->add();
				$categs_id[]=$category->id;

			 }

		}

        $categs_id=array(25355);
		var_dump($categs_id);
		$product_created = array();
		
		
	// New product 
	$product = new Product();

	$titresub = substr($data[$p]['titre2'], 0,127);
	echo "<br>titre : ".$data[$p]['titre2']."<br>";
	$product->name = [$default_lang => htmlspecialchars_decode($titresub)];
	$product->id_shop_default = 1;
	$product->reference =$data[$p]['reference'];
	//echo htmlspecialchars_decode($data[$p]['titre']);
	//exit;
	$product->id_tax_rules_group = 1;
	//echo $data[$p]['titre'];
	$product->link_rewrite = array((int)$default_lang => Tools::link_rewrite($titresub));
	$prixx = (float)(trim(str_replace('?','',str_replace(',','.', $data[$p]['prix']))));
	var_dump($prixx);
	$product->price = $prixx;

	$description_short=$data[$p]['description_short'];

	$product->description_short = [$default_lang => $description_short];
	
	$description= $data[$p]['description'];
	$product->description = [$default_lang => $description];
	
	$product->quantity = 70;
	
	//$product->reference=$data[$p]['reference'];
	 
	$product->id_category = $categs_id[count($categs_id)-1];
	$product->id_category_default = $categs_id[count($categs_id)-1];
	
//	$product->minimal_quantity= (int) $data[$p]['min_qte'];	
	
	if($product->add())
	{
		$product->addToCategories($categs_id[count($categs_id)-1]);
		$product_created[]=$product->id;
		
		
		StockAvailable::setQuantity((int)$product->id, 0, $product->quantity, 2);
		
		//echo "Produit ajouté en id =".$product->id."<br>";
	//$result= Db::getInstance()->execute('update catalogue2 set  add_to_ps='.(int)$product->id.' where id='.$data[$p]['id']);
		
		
		
		
		
	}

	/*$arrayattr = explode('|', $data[$p]['declinaisons_fr']);
	$arrayattr_val = explode('|', $data[$p]['declinaisons_values']);
	$combinationAttributes=array();
	for($a=0;$a<count($arrayattr);$a++)
	{

		$attrib = new AttributeGroupCore(cherche_group_att($arrayattr[$a], $default_lang));
		$dec_values= explode(',', $arrayattr_val[$a]);
		echo json_encode($dec_values);
		$combinationval=array();
		for($b=0;$b<count($dec_values);$b++)
		{
			
		if(cherche_att($dec_values[$b],$attrib->id,$default_lang)==0)
		{
		$valueAtt= new AttributeCore();
		$valueAtt->id_attribute_group=$attrib->id;
		$valueAtt->name=[$default_lang => $dec_values[$b]];
		$valueAtt->add();
	}
	else 
	{
	$valueAtt= new AttributeCore(cherche_att($dec_values[$b],$attrib->id,$default_lang));
	}
	$combinationval[]=$valueAtt->id;
	
	}
	$combinationAttributes[$arrayattr[$a]]=$combinationval;

	}

	$combinationAttributes=generateCombinations($combinationAttributes);

	foreach ($combinationAttributes as $value) {
		var_dump($value);
		add_att_com($product->id, $value,0,(int) $data[$p]['min_qte']);
	}
	
*/
	
	
	
	
	
$images_lien=$data[$p]['photo'].'|';
$tab_img=explode('|',$images_lien);
for($i=0;$i<count($tab_img)-1;$i++)
{

$image = new Image();
$image->id_product = $product->id;
$image->position = Image::getHighestPosition($product->id) + $i;

$urlimg=$tab_img[$i];
if($i==0)
{
	
$image->cover = true;
}

else
{

	$image->cover = false;
}

$image->add();

copyImg((int)$product->id, (int)$image->id, $urlimg, 'products', true);
}





Db::getInstance()->execute('update aa_import_table set  ps_ids="'.implode(',', $product_created).'" where id='.$data[$p]['id']);
echo 'update catalogue_strongflex_pid set  ps_ids='.implode(',', $product_created).' where idd='.$data[$p]['idd'];
echo "<hr>".count($models)." products created <hr>";
	}
	
/* fonctions issues du core */

function copyImg($id_entity, $id_image = null, $url, $entity = 'products', $regenerate = true)
{
$tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
$watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

switch ($entity) {
default:
case 'products':
$image_obj = new Image($id_image);
$path = $image_obj->getPathForCreation();
break;
case 'categories':
$path = _PS_CAT_IMG_DIR_ . (int)$id_entity;
break;
case 'manufacturers':
$path = _PS_MANU_IMG_DIR_ . (int)$id_entity;
break;
case 'suppliers':
$path = _PS_SUPP_IMG_DIR_ . (int)$id_entity;
break;
}

$url = urldecode(trim($url));
$parced_url = parse_url($url);

if (isset($parced_url['path'])) { 
$uri = ltrim($parced_url['path'], '/');
$parts = explode('/', $uri);
foreach ($parts as &$part) {
$part = rawurlencode($part);
}
unset($part);
$parced_url['path'] = '/' . implode('/', $parts);
}

if (isset($parced_url['query'])) {
$query_parts = array();
parse_str($parced_url['query'], $query_parts);
$parced_url['query'] = http_build_query($query_parts);
}

if (!function_exists('http_build_url')) {
require_once(_PS_TOOL_DIR_ . 'http_build_url/http_build_url.php');
}

$url = http_build_url('', $parced_url);

$orig_tmpfile = $tmpfile;

if (Tools::copy($url, $tmpfile)) {
// Evaluate the memory required to resize the image: if it's too much, you can't resize it.
if (!ImageManager::checkImageMemoryLimit($tmpfile)) {
@unlink($tmpfile);
return false;
}

$tgt_width = $tgt_height = 0;
$src_width = $src_height = 0;
$error = 0;
ImageManager::resize($tmpfile, $path . '.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5,
$src_width, $src_height);
$images_types = ImageType::getImagesTypes($entity, true);

if ($regenerate) {
$previous_path = null;
$path_infos = array();
$path_infos[] = array($tgt_width, $tgt_height, $path . '.jpg');
foreach ($images_types as $image_type) {
$tmpfile = get_best_path($image_type['width'], $image_type['height'], $path_infos);

if (ImageManager::resize($tmpfile, $path . '-' . stripslashes($image_type['name']) . '.jpg', $image_type['width'],
$image_type['height'], 'jpg', false, $error, $tgt_width, $tgt_height, 5,
$src_width, $src_height)) {
// the last image should not be added in the candidate list if it's bigger than the original image
if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
$path_infos[] = array($tgt_width, $tgt_height, $path . '-' . stripslashes($image_type['name']) . '.jpg');
}
if ($entity == 'products') {
if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '.jpg')) {
unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '.jpg');
}
if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '_' . 2 . '.jpg')) {
unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int)$id_entity . '_' . 2 . '.jpg');
}
}
}
if (in_array($image_type['id_image_type'], $watermark_types)) {
Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
}
}
}
} else {
@unlink($orig_tmpfile);
return false;
}
unlink($orig_tmpfile);
return true;
}

function get_best_path($tgt_width, $tgt_height, $path_infos)
{
$path_infos = array_reverse($path_infos);
$path = '';
foreach ($path_infos as $path_info) {
list($width, $height, $path) = $path_info;
if ($width >= $tgt_width && $height >= $tgt_height) {
return $path;
}
}
return $path;
}



	?>