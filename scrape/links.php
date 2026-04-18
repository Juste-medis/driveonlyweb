<?php
 
include('../config/config.inc.php');
include('../init.php');

$linkObj = new Link();
$productLink = $linkObj->getProductLink(379219);
echo  $productLink.'<br>';

exit;

$result = Db::getInstance()->executeS("SELECT id_product FROM `ps_product_lang` WHERE id_lang=1 and id_shop=1 ORDER BY id_product desc limit 0,100");

if($result)
{


    foreach ($result as $row)
  {
    $linkObj = new Link();
    $productLink = $linkObj->getProductLink($row['id_product']);
    echo  $productLink.'<br>';

  }
}