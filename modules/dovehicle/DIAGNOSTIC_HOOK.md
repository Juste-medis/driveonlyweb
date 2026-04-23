# Guide de Diagnostic - Hook ActionProductFormBuilderModifier

## 📋 Problème Report

**Symptômes observés:**
- ❌ Hook `actionProductFormBuilderModifier` ne semble pas fonctionner
- ❌ Template `product_vehicle_tab.tpl` n'est pas visible
- ❌ Log `Utils::log($formBuilder)` ne s'affiche pas

## ✅ Corrections Appliquées

### 1. Hook `actionProductFormBuilderModifier` - Corrigé ✅

**Problème:**
```php
// AVANT: Tentative d'injection via $params['data']
$params['data']['dovehicle_panel'] = $this->context->smarty->fetch(...);
```
Cette approche ne fonctionne pas en PrestaShop 1.7 - `$params['data']` n'est pas utilisé pour injecter du contenu.

**Solution:**
```php
// APRÈS: Stocker le template dans un champ caché
$formBuilder->add('dovehicle_template_html', HiddenType::class, [
    'data' => $templateContent,
]);
```

### 2. Chargement des Assets - Nouveau Hook Ajouté ✅

Hook `actionAdminControllerSetMedia` crée pouir charger le JS et CSS:

```php
public function hookActionAdminControllerSetMedia(): void
{
    if (Tools::getValue('controller') !== 'AdminProducts') {
        return;
    }
    
    // Charger le script JS
    $this->context->controller->addJs(
        _MODULE_DIR_ . $this->name . '/views/js/admin/product_vehicle_tab.js'
    );
    
    // Charger le CSS
    $this->context->controller->addCss(
        _MODULE_DIR_ . $this->name . '/views/css/admin/product_vehicle_tab.css'
    );
    
    // Passer les variables AJAX au JavaScript
    Media::addJsDefL('dovehicleAjaxUrl', $this->context->link->getAdminLink('AdminDoVehicleAjax'));
    Media::addJsDefL('dovehicleToken', Tools::getAdminTokenLite('AdminDoVehicleAjax'));
}
```

### 3. Fichiers Créés ✅

| Fichier | Rôle |
|---------|------|
| `views/js/admin/product_vehicle_tab.js` | Injecte le template et gère les événements |
| `views/css/admin/product_vehicle_tab.css` | Stylise le tab DoVehicle |

### 4. Logs Améliorés ✅

Ajout de logs de diagnostic:
```php
Utils::log("HOOK actionProductFormBuilderModifier appelé pour produit ID: " . $idProduct);
Utils::log("Compats trouvées: " . count($existingCompats));
Utils::log("Hook hookActionProductFormBuilderModifier complété avec succès");
```

## 🔧 Configuration Requise

### IMPORTANT: Module Doit Être Réinstallé

Les nouveaux hooks (`actionAdminControllerSetMedia`) doivent être **enregistrés dans la base de données**.

**Actions:**

1. **Désinstaller le module** depuis le back-office:
   - Catalogue → Modules → Rechercher "dovehicle"
   - Cliquer "Désinstaller"

2. **Réinstaller le module:**
   - Cliquer "Installer"
   - Le hook `actionAdminControllerSetMedia` sera enregistré

3. **Vider le cache PrestaShop** (au cas où):
   - Paramètres → Paramètres avancés → Performance
   - Vider tout le cache

## 📝 Flux d'Exécution

```
1. Page produit charge → AdminProducts
2. Hook actionAdminControllerSetMedia s'exécute
3. JS/CSS du module sont chargés
4. Hook actionProductFormBuilderModifier s'exécute
   ├── Charge les données (marques, modèles, compat)
   ├── Ajoute champs JSON au FormBuilder
   ├── Rend le template product_vehicle_tab.tpl
   └── Stock le template dans champ caché 'dovehicle_template_html'
5. Page HTML se charge
6. JS de monopole s'exécute (DOMContentLoaded)
   ├── Récupère le template depuis le champ caché
   ├── Injecte le template dans le DOM
   └── Initialise les sélecteurs en cascade (événements)
```

## 🧪 Checklist de Diagnostic

### Étape 1: Vérifier que le hook est exécuté

Regarder le fichier log: `/modules/dovehicle/myprint.log`

**Vous devriez voir:**
```
[2026-04-23 15:30:20] HOOK actionProductFormBuilderModifier appelé pour produit ID: 123
[2026-04-23 15:30:20] Compats trouvées: 5
[2026-04-23 15:30:20] Hook hookActionProductFormBuilderModifier complété avec succès
[2026-04-23 15:30:21] Media loaded for admin product page
```

Si ces logs n'apparaissent pas:
- [ ] Réinstaller le module (voir section "Configuration Requise")
- [ ] Vérifier que le module est activé
- [ ] Vider le cache Symfony (`php bin/console cache:clear`)

### Étape 2: Vérifier que le template est injecté

1. Ouvrir la page produit en back-office
2. Faire un inspect (F12) → Elements
3. Chercher l'élément `<div id="dovehicle-container">`
4. Vérifier qu'il contient le template (marques, modèles, motorisations)

**Si le container n'existe pas:**
- [ ] Vérifier que le template `views/templates/hook/product_vehicle_tab.tpl` existe
- [ ] Vérifier qu'il n'y a pas d'erreur Smarty (regarder le log)
- [ ] Vérifier que les variables Smarty sont assignées correctement

### Étape 3: Vérifier que le JS fonctionne

1. Console (F12 → Console)
2. Vérifier les logs `[DoVehicle]`:
   ```
   [DoVehicle] product_vehicle_tab.js loaded
   [DoVehicle] Template content loaded, length: 2154
   [DoVehicle] Template injected after form-section
   [DoVehicle] Initializing...
   [DoVehicle] AJAX URL: /admin/?controller=AdminDoVehicleAjax&token=xxx
   [DoVehicle] Token: OK
   [DoVehicle] Initialization complete
   ```

Si les logs ne s'affichent pas:
- [ ] Vérifier que le fichier `views/js/admin/product_vehicle_tab.js` existe
- [ ] Vérifier qu'il est chargé (Network → XHR, chercher `product_vehicle_tab.js`)
- [ ] Vérifier qu'il n'y a pas d'erreur JavaScript dans la console

### Étape 4: Tester les sélecteurs

1. Sélectionner une marque → Les modèles doivent se charger
2. Sélectionner un modèle → Les motorisations doivent se charger
3. Sélectionner une motorisation → Le bouton "Ajouter" doit s'activer

**Si cela ne fonctionne pas:**
- [ ] Vérifier dans la console les logs `[DoVehicle] Fetching: ...`
- [ ] Vérifier dans Network → XHR les appels AJAX
- [ ] Vérifier que le contrôleur `AdminDoVehicleAjax` répond correctement

## 📊 Fichiers Modifiés

| Fichier | Changement |
|---------|-----------|
| `dovehicle.php` | Hook corrigé + nouveau hook `actionAdminControllerSetMedia` + logs |
| `views/js/admin/product_vehicle_tab.js` | Créé - Gère injection template et sélecteurs |
| `views/css/admin/product_vehicle_tab.css` | Créé - Styles du tab DoVehicle |

## 🚀 Prochaines Étapes

1. **Réinstaller le module** (OBLIGATOIRE)
2. **Vider le cache** 
3. **Tester sur une fiche produit**
4. **Consulter les logs** (`/modules/dovehicle/myprint.log`)
5. **Checker la console** pour les erreurs JS

## ⚠️ Problèmes Connus

### AJAX retourne une erreur 404

**Cause:** Le contrôleur `AdminDoVehicleAjax` est un contrôleur hérité BO.

**Solution:** Vérifier que `AdminDoVehicleAjax` existe dans:
```
controllers/admin/AdminDoVehicleAjax.php
```

Et qu'il répond correctement aux actions `getModels`, `getEngines`, `getProducts`.

### Variables Smarty non assignées

**Cause:** Les manufactures, modèles, moteurs ne sont pas fournis.

**Solution:** Vérifier que les repositories retournent des données:
```php
$manufacturers = $this->getManufacturerList();
// Doit retourner un SELECT depuis ps_manufacturer avec les marques disponibles
```

### Log ne s'affiche jamais

**Cause:** Le fichier log n'est pas accessible en écriture.

**Solution:** Vérifier les permissions:
```bash
ls -la /modules/dovehicle/myprint.log
# Doit avoir w (write) pour le serveur web
chmod 666 /modules/dovehicle/myprint.log
```

## 📞 Support

Si vous après avoir suivi cette checklist le problème persiste, vérifiez:
1. La console du navigateur (F12 → Console) pour les erreurs JavaScript
2. Le fichier log `/modules/dovehicle/myprint.log` pour les erreurs PHP
3. Les logs Symfony: `/var/log/prestashop.log` ou `/var/log/prod.log`
