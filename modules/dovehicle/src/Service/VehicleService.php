<?php

/**
 * Service — Logique métier véhicule
 * Couche entre les controllers et les repositories
 */

declare(strict_types=1);

namespace DoVehicle\Service;

use DoVehicle\Repository\VehicleModelRepository;
use DoVehicle\Repository\VehicleEngineRepository;
use DoVehicle\Repository\ProductFamilyRepository;
use DoVehicle\Repository\ProductCompatibilityRepository;

class VehicleService
{
    private VehicleModelRepository        $modelRepo;
    private VehicleEngineRepository       $engineRepo;
    private ProductFamilyRepository       $familyRepo;
    private ProductCompatibilityRepository $compatRepo;

    public function __construct(
        VehicleModelRepository         $modelRepo,
        VehicleEngineRepository        $engineRepo,
        ProductFamilyRepository        $familyRepo,
        ProductCompatibilityRepository $compatRepo
    ) {
        $this->modelRepo  = $modelRepo;
        $this->engineRepo = $engineRepo;
        $this->familyRepo = $familyRepo;
        $this->compatRepo = $compatRepo;
    }

    // ─── AJAX BO : cascade Marque > Modèle > Motorisation ────────────────────

    /**
     * Retourne les modèles d'une marque (pour AJAX BO/FO)
     *
     * @param  int   $idManufacturer
     * @return array{success:bool, data:array}
     */
    public function getModelsForManufacturer(int $idManufacturer): array
    {
        if ($idManufacturer <= 0) {
            return ['success' => false, 'message' => 'ID marque invalide'];
        }

        $models = $this->modelRepo->findByManufacturer($idManufacturer, true);

        return [
            'success' => true,
            'data'    => array_map(fn(array $m): array => [
                'id'         => (int) $m['id_do_vehicle_model'],
                'name'       => $m['name'],
                'year_start' => $m['year_start'],
                'year_end'   => $m['year_end'],
            ], $models),
        ];
    }

    /**
     * Retourne les motorisations d'un modèle (pour AJAX BO/FO)
     *
     * @param  int   $idModel
     * @return array{success:bool, data:array}
     */
    public function getEnginesForModel(int $idModel): array
    {
        if ($idModel <= 0) {
            return ['success' => false, 'message' => 'ID modèle invalide'];
        }

        $engines = $this->engineRepo->findByModel($idModel, true);

        return [
            'success' => true,
            'data'    => array_map(fn(array $e): array => [
                'id'             => (int) $e['id_do_vehicle_engine'],
                'name'           => $e['name'],
                // 'fuel_type'      => $e['fuel_type'],
                // 'power_hp'       => $e['power_hp'],
                // 'displacement_cc'=> $e['displacement_cc'],
                'year_start'     => $e['year_start'],
                'year_end'       => $e['year_end'],
                // Libellé complet pour l'affichage
                'label'          => $this->buildEngineLabel($e),
            ], $engines),
        ];
    }

    // ─── FO : Recherche produits compatibles ─────────────────────────────────

    /**
     * Récupère les produits compatibles + les données PS nécessaires pour l'affichage
     *
     * @param  int      $idManufacturer
     * @param  int      $idModel
     * @param  int      $idEngine
     * @param  int|null $idFamily       Filtrer par famille (optionnel)
     * @param  int      $idLang
     * @param  int      $idShop
     * @param  int      $page
     * @param  int      $perPage
     * @return array{products:array, total:int}
     */
    public function getCompatibleProducts(
        int  $idManufacturer,
        int  $idModel  = 0,
        int  $idEngine = 0,
        ?int $idFamily = null,
        int  $idLang   = 1,
        int  $idShop   = 1,
        int  $page     = 1,
        int  $perPage  = 24
    ): array {
        // Récupérer les IDs produits compatibles
        if ($idFamily !== null) {
            $productIds = $this->compatRepo->findProductsByVehicleAndFamily(
                $idManufacturer,
                $idFamily,
                $idModel,
                $idEngine
            );
        } else {
            $productIds = $this->compatRepo->findProductsByVehicle(
                $idManufacturer,
                $idModel,
                $idEngine
            );
        }

        $total = count($productIds);

        if ($total === 0) {
            return ['products' => [], 'total' => 0];
        }

        // Pagination
        $offset    = ($page - 1) * $perPage;
        $pageIds   = array_slice($productIds, $offset, $perPage);

        // Charger les données PS pour chaque produit
        $products = $this->loadProductData($pageIds, $idLang, $idShop);

        return ['products' => $products, 'total' => $total];
    }

    /**
     * Charge les données PrestaShop d'une liste d'IDs produits
     * (image, prix, lien, description courte)
     *
     * @param  int[] $productIds
     * @param  int   $idLang
     * @param  int   $idShop
     * @return array
     */
    private function loadProductData(array $productIds, int $idLang, int $idShop): array
    {
        if (empty($productIds)) {
            return [];
        }

        $products = [];

        foreach ($productIds as $idProduct) {
            $product = new \Product($idProduct, false, $idLang, $idShop);

            if (!\Validate::isLoadedObject($product)) {
                continue;
            }

            $coverImage  = \Product::getCover($idProduct);
            $imageLink   = '';

            if ($coverImage) {
                $imageLink = \Context::getContext()->link->getImageLink(
                    $product->link_rewrite,
                    $coverImage['id_image'],
                    \ImageType::getFormattedName('home')
                );
            }

            $products[] = [
                'id_product'        => $idProduct,
                'name'              => $product->name,
                'description_short' => $product->description_short,
                'price'             => \Product::getPriceStatic($idProduct, true),
                'price_formatted'   => \Tools::displayPrice(
                    \Product::getPriceStatic($idProduct, true)
                ),
                'link'              => \Context::getContext()->link->getProductLink($product),
                'cover_image'       => $imageLink,
                'reference'         => $product->reference,
                'in_stock'          => $product->quantity > 0 || $product->out_of_stock == 1,
                'manufacturer_name' => \Manufacturer::getNameById((int) $product->id_manufacturer),
            ];
        }

        return $products;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Construit un libellé lisible pour une motorisation
     * Ex : "1.8 T 150ch — Essence — (1998-2004)"
     */
    private function buildEngineLabel(array $engine): string
    {
        $label = $engine['name'];

        if (!empty($engine['power_hp'])) {
            $label .= ' ' . $engine['power_hp'] . 'ch';
        }

        if (!empty($engine['fuel_type']) && $engine['fuel_type'] !== 'autre') {
            $fuelLabels = [
                'essence'    => 'Essence',
                'diesel'     => 'Diesel',
                'electrique' => 'Électrique',
                'hybride'    => 'Hybride',
                'gpl'        => 'GPL',
            ];
            $label .= ' — ' . ($fuelLabels[$engine['fuel_type']] ?? $engine['fuel_type']);
        }

        if (!empty($engine['year_start'])) {
            $yearEnd = !empty($engine['year_end']) ? $engine['year_end'] : '…';
            $label  .= ' (' . $engine['year_start'] . '–' . $yearEnd . ')';
        }

        return $label;
    }
}
