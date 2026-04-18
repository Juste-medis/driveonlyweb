<?php
// Include PrestaShop's configuration file

require('../config/config.inc.php');
require('../init.php');
$sql = 'SELECT DISTINCT id_product FROM `ps_image` WHERE jpg=1 order by id_product';

if ($results = Db::getInstance()->ExecuteS($sql))
{  // printf("Select a retourné %d lignes.\n", $result->num_rows);
    foreach ($results as $row)
	{
// Set the product ID for which to regenerate thumbnails
$productId = $row['id_product']; // Replace YOUR_PRODUCT_ID with the actual product ID

// Load the product
$product = new Product((int)$productId);

// Load the product's images
$images = $product->getImages((int)Configuration::get('PS_LANG_DEFAULT'));

// Regenerate thumbnails for each image of the product
foreach ($images as $image) {
    // Create new Image object
    $imageObj = new Image((int)$image['id_image']);

    // Define the types of images to regenerate (you can modify this array with the types you want)
    $imageTypes = ImageType::getImagesTypes('products');
    
    // Regenerate thumbnails for each image type
    foreach ($imageTypes as $imageType) {
        $newImagePath = _PS_PROD_IMG_DIR_.$imageObj->getExistingImgPath().'-'.$imageType['name'].'.'.$imageObj->image_format;

        // Resize image
        if (!ImageManager::resize(
            _PS_PROD_IMG_DIR_.$imageObj->getExistingImgPath().'.'.$imageObj->image_format,
            $newImagePath,
            (int)$imageType['width'],
            (int)$imageType['height']
        )) {
            // Handle errors
            echo 'Error regenerating thumbnail for image ID ' . $image['id_image'] . ' for type ' . $imageType['name'];
            continue;
        }
        
      
    }
}

    }
}
?>