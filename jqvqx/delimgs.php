<?php

require('../config/config.inc.php');
require('../init.php');

$sql = 'SELECT id_image FROM `ps_image` WHERE `id_product` not in (SELECT `id_product` from ps_product) and deleted=0  order by id_image limit 0,10000';
//echo $sql;
$idCategories=array();

if ($results = Db::getInstance()->ExecuteS($sql))
{  // printf("Select a retourné %d lignes.\n", $result->num_rows);
    foreach ($results as $row)
	{

        $image = new Image($row['id_image']);
        $path = str_replace($row['id_image'],'','/img/p/'.$image->getExistingImgPath());
        echo "/var/www/vhosts/driveonly.fr/httpdocs/$path<br>";
        echo "rm -rf /var/www/vhosts/driveonly.fr/httpdocs/$path*.jpg<br>";
        echo "rm -rf /var/www/vhosts/driveonly.fr/httpdocs/$path*.webp";
        shell_exec("rm -rf /var/www/vhosts/driveonly.fr/httpdocs/$path*.jpg");
        shell_exec("rm -rf /var/www/vhosts/driveonly.fr/httpdocs/$path*.webp");
        $sqlupdt = 'update ps_image set deleted=1 where id_image='.$row['id_image'];
        Db::getInstance()->Execute($sqlupdt);
        

    }
}


?>