<?php

/**
 * Contrôleur BO — Gestion des Familles et Sous-familles produit
 * Compatible PrestaShop 1.7.8.7
 */

declare(strict_types=1);
require_once dirname(__FILE__) . '/../../classes/DoProductFamily.php';


use DoVehicle\Repository\ProductFamilyRepository;

class AdminDoProductFamilyController extends ModuleAdminController
{
    /** @var ProductFamilyRepository */
    private $familyRepo;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table     = 'do_product_family';
        $this->lang      = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->module     = Module::getInstanceByName('dovehicle');
        $container = $this->context->controller->getContainer();

        $this->familyRepo =  $container->get('dovehicle.repository.product_family');

        $this->fields_list = [
            'id_do_product_family' => ['title' => $this->l('ID'),         'align' => 'center', 'width' => 40],
            'parent_name'          => ['title' => $this->l('Famille parente'), 'callback' => 'renderParent'],
            'name'                 => ['title' => $this->l('Nom')],
            'slug'                 => ['title' => $this->l('Slug SEO')],
            'position'             => ['title' => $this->l('Position'), 'align' => 'center', 'width' => 60],
            'active'               => [
                'title'  => $this->l('Actif'),
                'active' => 'status',
                'type'   => 'bool',
                'align'  => 'center',
            ],
        ];
    }

    public function renderParent(?string $value): string
    {
        return $value ? htmlspecialchars($value) : '<em class="text-muted">— Famille racine —</em>';
    }

    public function renderForm(): string
    {
        // Familles racines pour le select "parent"
        $roots = $this->familyRepo->findRootFamilies(false);
        $parentOptions = [['id_do_product_family' => '', 'name' => '— Famille racine (niveau 4) —']];
        foreach ($roots as $root) {
            $parentOptions[] = [
                'id_do_product_family' => $root['id_do_product_family'],
                'name'                 => $root['name'],
            ];
        }

        $this->fields_form = [
            'legend' => ['title' => $this->l('Famille / Sous-famille produit'), 'icon' => 'category'],
            'input'  => [
                [
                    'type'    => 'select',
                    'label'   => $this->l('Famille parente'),
                    'name'    => 'id_parent',
                    'hint'    => $this->l('Laisser vide pour créer une famille racine (niveau 4). Sélectionner une famille pour créer une sous-famille (niveau 5).'),
                    'options' => [
                        'query' => $parentOptions,
                        'id'    => 'id_do_product_family',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Nom'),
                    'name'     => 'name',
                    'required' => true,
                    'hint'     => $this->l('Ex : Échappement, Kit complet, Liaison au sol'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Slug SEO'),
                    'name'  => 'slug',
                    'hint'  => $this->l('Laissez vide pour génération automatique. Ex : echappement-kit-complet'),
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Description (optionnelle)'),
                    'name'  => 'description',
                    'rows'  => 3,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Position'),
                    'name'  => 'position',
                    'class' => 'fixed-width-sm',
                ],
                [
                    'type'   => 'switch',
                    'label'  => $this->l('Actif'),
                    'name'   => 'active',
                    'values' => [
                        ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Oui')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Non')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->l('Enregistrer')],
        ];

        $idFamily = (int) Tools::getValue('id_do_product_family', 0);
        if ($idFamily > 0) {
            $family = $this->familyRepo->findAllFlat(false);
            foreach ($family as $f) {
                if ((int) $f['id_do_product_family'] === $idFamily) {
                    $this->fields_value = [
                        'id_parent'   => $f['id_parent'],
                        'name'        => $f['name'],
                        'slug'        => $f['slug'],
                        'description' => $f['description'] ?? '',
                        'position'    => $f['position'],
                        'active'      => $f['active'],
                    ];
                    break;
                }
            }
        } else {
            $this->fields_value = ['active' => 1, 'position' => 0];
        }

        return parent::renderForm();
    }

    public function processSave(): bool
    {
        $idFamily  = (int) Tools::getValue('id_do_product_family', 0);
        $idParent  = (int) Tools::getValue('id_parent', 0) ?: null;
        $name      = pSQL(trim((string) Tools::getValue('name', '')));
        $slug      = pSQL(trim((string) Tools::getValue('slug', '')));
        $desc      = pSQL((string) Tools::getValue('description', ''));

        if (!$name) {
            $this->errors[] = $this->l('Le nom est obligatoire.');
            return false;
        }

        // Empêcher une famille d'être son propre parent
        if ($idParent && $idParent === $idFamily) {
            $this->errors[] = $this->l('Une famille ne peut pas être son propre parent.');
            return false;
        }

        $data = [
            'id_parent'   => $idParent,
            'name'        => $name,
            'slug'        => $slug,   // Repository génère le slug si vide
            'description' => $desc,
            'position'    => (int) Tools::getValue('position', 0),
            'active'      => (int) Tools::getValue('active', 1),
        ];

        if ($idFamily > 0) {
            $this->familyRepo->update($idFamily, $data);
        } else {
            $this->familyRepo->create($data);
        }

        $this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
        return true;
    }
}
