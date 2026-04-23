# DoVehicle — Guide SEO, Navigation & Bonnes Pratiques
# Compatible PrestaShop 1.7.8.7

---

## 1. STRATÉGIE SEO : Catégories PS + Tables Véhicule

### Principe du double maillage

PrestaShop gère nativement les URLs de catégories avec métadonnées SEO
(meta_title, meta_description, meta_keywords, link_rewrite).
On CONSERVE les catégories PS pour le SEO et on les CROISE avec les tables véhicule.

```
Catégorie PS                     Table DoVehicle
─────────────────                ────────────────────────
/echappement/                    do_product_family (slug: echappement)
/volvo/                          ps_manufacturer (id: 12, name: Volvo)
/volvo/850/                      do_vehicle_model (id: 5, name: 850)
/volvo/850/echappement/          CROISEMENT : modèle 5 + famille 4
```

### Mapping recommandé

Créer des catégories PS "façade" par marque + famille dont le contenu
est alimenté dynamiquement via le module :

```php
// Exemple : page /pièces/volvo/echappement/
// URL canonique = catégorie PS id=42
// Produits affichés = compatRepo->findProductsByVehicleAndFamily(12, 4)
// Titre SEO = "Pièces échappement Volvo — X produits"
```

---

## 2. ÉVITER LES PRODUITS INVISIBLES

### Le problème
Un produit multi-compatible (ex : kit pour 3 motorisations différentes)
risque d'être absent des résultats si la navigation ne remonte pas la chaîne.

### Solution : requête inclusive à 3 niveaux

```sql
-- Un produit est VISIBLE si compatible avec la marque OU le modèle OU la motorisation
SELECT DISTINCT vc.id_product
FROM ps_do_product_vehicle_compat vc
INNER JOIN ps_product p ON p.id_product = vc.id_product AND p.active = 1
WHERE (
    vc.id_manufacturer      = :id_brand            -- compatible toute la marque
    OR vc.id_do_vehicle_model  = :id_model          -- compatible tout le modèle
    OR vc.id_do_vehicle_engine = :id_engine         -- compatible motorisation précise
)
```

### Règle de saisie BO (à documenter pour les opérateurs)
- Compat marque seule     → produit visible pour TOUS les véhicules de la marque
- Compat modèle seul      → produit visible pour TOUTES les motorisations du modèle
- Compat motorisation     → produit visible pour cette motorisation uniquement
- On peut cumuler : ex. kit universel Volvo = lier à `id_manufacturer = 12`
  puis ajouter une compat motorisation pour une version spécifique


---

## 3. GESTION DES URLS CANONIQUES

### Problème : URL dupliquée
Un produit apparaît dans la page /volvo/850/ ET dans /volvo/850/echappement/
→ Google voit deux URLs pour le même produit.

### Solution dans le hook displayHeader FO

```php
// Dans dovehicle.php hookDisplayHeader()
public function hookDisplayHeader(): string
{
    $controller = $this->context->controller;

    // Sur les pages de listing véhicule du module
    if ($controller instanceof DovehicleVehicleModuleFrontController) {
        $idManufacturer = (int) Tools::getValue('id_manufacturer');
        $idModel        = (int) Tools::getValue('id_model');
        $idEngine       = (int) Tools::getValue('id_engine');
        $idFamily       = (int) Tools::getValue('id_family');

        // Construire l'URL canonique : toujours avec tous les paramètres connus
        $canonical = $this->context->link->getModuleLink('dovehicle', 'vehicle', [
            'id_manufacturer' => $idManufacturer,
            'id_model'        => $idModel,
            'id_engine'       => $idEngine,
            'id_family'       => $idFamily ?: '',
        ]);

        // Injecter la balise canonical
        $this->context->smarty->assign('dovehicle_canonical', $canonical);
    }

    // ...
}
```

```smarty
{* Dans vehicle_selector.tpl ou layout.tpl du thème *}
{if isset($dovehicle_canonical)}
  <link rel="canonical" href="{$dovehicle_canonical|escape:'html'}">
{/if}
```

### Règle : 1 URL canonique par combinaison (marque + modèle + motorisation + famille)
Les pages sans motorisation précisée renvoient canonical vers la version avec motorisation.


---

## 4. REDIRECTIONS 301 (Lot F)

### Fichier de mapping CSV (à remplir lors de la migration)

Format recommandé :
```
ancienne_url,nouvelle_url,priorite
/category/echappement-volvo-850-turbo,/vehicule/12/5/23?id_family=4,haute
/product/kit-echappement-volvo-850-turbo-s100,/pièces/echappement-volvo-850-turbo,haute
```

### Implémentation dans .htaccess ou nginx

```apache
# .htaccess — Exemple pour quelques redirections critiques
# Générer le bloc complet depuis le fichier CSV avec un script PHP

RewriteEngine On

# Exemple : ancienne URL catégorie → nouvelle navigation véhicule
RewriteRule ^category/echappement-volvo-850-turbo/?$  /vehicule/12/5/23  [R=301,L]
RewriteRule ^category/look-covering-bmw/?$            /vehicule/3        [R=301,L]
```

### Script PHP de génération du bloc RewriteRule

```php
// scripts/generate_htaccess_redirects.php
$mappings = array_map('str_getcsv', file('mapping_301.csv'));
foreach ($mappings as $row) {
    if (count($row) < 2) continue;
    [$old, $new] = $row;
    $old = preg_quote(ltrim($old, '/'), '#');
    echo "RewriteRule ^{$old}/?$ {$new} [R=301,L]\n";
}
```

---

## 5. RECOMMANDATIONS PERFORMANCE & INDEX SQL

### Index créés par install.sql
```sql
-- do_vehicle_model
KEY idx_manufacturer (id_manufacturer)  -- jointure ps_manufacturer
KEY idx_active       (active)           -- filtre actif=1

-- do_vehicle_engine
KEY idx_model        (id_do_vehicle_model)  -- cascade modèle→moteur
KEY idx_active       (active)

-- do_product_vehicle_compat
KEY idx_product      (id_product)          -- lookup par produit (fiche BO)
KEY idx_manufacturer (id_manufacturer)     -- filtre marque seule
KEY idx_model        (id_do_vehicle_model)
KEY idx_engine       (id_do_vehicle_engine)
UNIQUE uq_product_compat (id_product, id_manufacturer, id_do_vehicle_model, id_do_vehicle_engine)
-- ↑ empêche les doublons ET sert d'index composite pour les requêtes FO

-- do_product_family_link
KEY idx_family       (id_do_product_family)  -- "produits de la famille X"
```

### Index supplémentaires conseillés selon le volume

```sql
-- Si +10 000 produits compatibles : index composite pour la requête FO principale
ALTER TABLE ps_do_product_vehicle_compat
  ADD KEY idx_brand_model_engine (id_manufacturer, id_do_vehicle_model, id_do_vehicle_engine);

-- Si recherche fréquente par slug (pages SEO famille)
-- Déjà couvert par UNIQUE KEY uq_slug sur do_product_family
```

---

## 6. PIÈGES À ÉVITER

### Piège 1 : invalidation du cache Smarty
Après modification du template product_vehicle_tab.tpl, vider :
`var/cache/dev/` et `var/cache/prod/` dans l'admin PS ou via CLI.

### Piège 2 : hook actionProductFormBuilderModifier et Symfony Form
PS 1.7.8.7 utilise le FormBuilder Symfony pour la fiche produit.
Les champs ajoutés via `$formBuilder->add()` doivent être de type
`HiddenType` (pas de rendu auto) — le rendu visuel est fait dans le template Smarty.
Ne jamais ajouter des champs `TextType` ou `ChoiceType` directement dans le FormBuilder
car le rendu du panneau essentials ne les affiche pas nativement.

### Piège 3 : données orphelines à la suppression produit
Les FK avec ON DELETE CASCADE dans install.sql gèrent automatiquement
la suppression des compatibilités et liaisons famille quand un produit est supprimé.
Vérifier que MySQL respecte les FK (InnoDB obligatoire, pas MyISAM).

### Piège 4 : conflit de token AJAX BO
Le token `Tools::getAdminTokenLite('AdminDoVehicleAjax')` change à chaque session.
Il doit être injecté dans la page via le template Smarty (variable `dovehicle_token`)
et passé dans chaque requête AJAX. Ne jamais le stocker en dur dans le JS.

### Piège 5 : select2 ou chosen.js dans le BO PS 1.7
Le BO PS 1.7 utilise select2 sur certains selects. Pour forcer le rechargement
d'un select après remplissage AJAX, déclencher l'event `change.select2` :
```javascript
$('#dov-select-model').trigger('change.select2');
```

### Piège 6 : produits sans famille définie
Un produit sans famille liée n'apparaît dans aucun filtre famille.
Il reste accessible via la recherche par véhicule seul.
Mettre en place une règle BO : tout produit doit avoir ≥ 1 famille.
Requête de contrôle :
```sql
SELECT p.id_product, pl.name
FROM ps_product p
LEFT JOIN ps_do_product_family_link fl ON fl.id_product = p.id_product
INNER JOIN ps_product_lang pl ON pl.id_product = p.id_product AND pl.id_lang = 1
WHERE fl.id_product IS NULL AND p.active = 1;
```

---

## 7. CHECKLIST DÉPLOIEMENT PROD

```
[ ] 1. Valider sur DEV : navigation complète, filtres, fiches produit
[ ] 2. Exporter mapping URLs depuis DEV (CSV)
[ ] 3. Uploader le module en PROD via FTP
[ ] 4. Installer le module (crée les tables + onglets BO)
[ ] 5. Importer les données (modèles, motorisations, familles) via les écrans BO
[ ] 6. Exécuter le rattachement produits (script bulk si volume important)
[ ] 7. Mettre en place les redirections 301 (.htaccess)
[ ] 8. Vider le cache PS (BO > Paramètres avancés > Performance)
[ ] 9. Crawler avec Screaming Frog : 0 erreur 404, vérifier canonical
[ ] 10. Vérifier Google Search Console sous 48h après déploiement
```
