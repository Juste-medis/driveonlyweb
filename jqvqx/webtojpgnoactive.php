<?php

require('../config/config.inc.php');
require('../init.php');

$sql = 'SELECT * FROM `ps_image` WHERE id_product in (SELECT id_product FROM ps_product where active=0) and deleted=1 and jpg is null ORDER BY id_image';
//$sql = 'SELECT * FROM `ps_image` WHERE id_image=21787';
//echo $sql;
$idCategories=array();

if ($results = Db::getInstance()->ExecuteS($sql))
{  // printf("Select a retourné %d lignes.\n", $result->num_rows);
    foreach ($results as $row)
	{

        $image = new Image($row['id_image']);
        $path = str_replace($row['id_image'],'','/img/p/'.$image->getExistingImgPath());
        echo "<hr>".$row['id_product']."</hr>";
        echo "/var/www/vhosts/driveonly.fr/httpdocs/$path<br>";
       // echo "cwebp -q 80 /var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg -o /var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg<br>";
       
       $sqlupdt = 'update ps_image set jpg=-2 where id_image='.$row['id_image'];
       Db::getInstance()->Execute($sqlupdt);
            $im = imagecreatefromwebp("/var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg");


        
       
        // Convert it to a jpeg file with 100% quality
        try
        {
        if(imagejpeg($im, "/var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image'].".jpg", 100))
        {
            echo "OK";
            shell_exec("rm -rf /var/www/vhosts/driveonly.fr/httpdocs/$path".$row['id_image']."-*.jpg");
           
            $sqlupdt = 'update ps_image set jpg=1 where id_image='.$row['id_image'];
            Db::getInstance()->Execute($sqlupdt);
            imagedestroy($im);

        }
        else
        {
            $sqlupdt = 'update ps_image set jpg=-1 where id_image='.$row['id_image'];
            Db::getInstance()->Execute($sqlupdt);
            echo "No";
            imagedestroy($im);
        }
    }
    catch(Exception $e)
         {
            echo "NOOOOOO";
            $sqlupdt = 'update ps_image set jpg=-2 where id_image='.$row['id_image'];
            Db::getInstance()->Execute($sqlupdt);
         }

      
        

    }
}


?>