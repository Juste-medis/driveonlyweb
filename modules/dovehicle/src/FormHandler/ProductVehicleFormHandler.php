<?php
/**
 * FormHandler — Sauvegarde des données véhicule depuis la fiche produit BO
 */

declare(strict_types=1);

namespace DoVehicle\FormHandler;

use DoVehicle\Repository\ProductCompatibilityRepository;
use DoVehicle\Repository\ProductFamilyRepository;

class ProductVehicleFormHandler
{
    private ProductCompatibilityRepository $compatRepo;
    private ProductFamilyRepository        $familyRepo;

    public function __construct(
        ProductCompatibilityRepository $compatRepo,
        ProductFamilyRepository        $familyRepo
    ) {
        $this->compatRepo = $compatRepo;
        $this->familyRepo = $familyRepo;
    }

    /**
     * Sauvegarde les compatibilités véhicule d'un produit
     *
     * @param int   $idProduct  ID du produit PrestaShop
     * @param array $compats    Liste des compatibilités (depuis JSON BO)
     *
     * Structure attendue pour chaque compat :
     * [
     *   'id_compat'            => 0,         // 0 = nouvelle ligne
     *   'id_manufacturer'      => 12,
     *   'id_do_vehicle_model'  => 5,
     *   'id_do_vehicle_engine' => 23,
     *   'note'                 => 'Coupé uniquement',
     * ]
     */
    public function saveCompatibilities(int $idProduct, array $compats): void
    {
        if ($idProduct <= 0) {
            return;
        }

        // Délégation au repository qui gère le diff insert/update/delete
        $this->compatRepo->syncCompatibilities($idProduct, $compats);
    }

    /**
     * Sauvegarde les liaisons famille/sous-famille d'un produit
     *
     * @param int   $idProduct  ID du produit PrestaShop
     * @param int[] $familyIds  IDs des familles sélectionnées
     */
    public function saveFamilyLinks(int $idProduct, array $familyIds): void
    {
        if ($idProduct <= 0) {
            return;
        } 
        // Filtrer et caster en entiers
        $cleanIds = array_filter(
            array_map('intval', $familyIds),
            fn(int $id): bool => $id > 0
        );

        $this->familyRepo->syncProductFamilies($idProduct, array_values($cleanIds));
    }
}
