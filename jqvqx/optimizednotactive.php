<?php

require('../config/config.inc.php');
require('../init.php');

$sql = 'SELECT * FROM `ps_image` WHERE id_product in (SELECT id_product FROM ps_product where active=0) and deleted=0';
//echo $sql;
$idCategories=array();

if ($results = Db::getInstance()->ExecuteS($sql))
{  // printf("Select a retourné %d lignes.\n", $result->num_rows);
    foreach ($results as $row)
	{

        $image = new Image($row['id_image']);
        $path = str_replace($row['id_image'],'','/img/p/'.$image->getExistingImgPath());
        echo "/var/www/vhosts/driveonly.fr/httpdocs/$path<br>";
        echo "cwebp -q 80 /var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg -o /var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg<br>";
        
        shell_exec("cwebp -q 80 /var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg -o /var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg");
        
        $sqlupdt = 'update ps_image set deleted=1 where id_image='.$row['id_image'];
        Db::getInstance()->Execute($sqlupdt);
        

    }
}


?>