# Module Maintenance et inspection

## Environnement analysé

Le projet utilise Laravel 12, PHP 8.3, Pest 4, Blade, Alpine.js et Tailwind CSS 4. Les tables `maintenances`, `maintenance_reports` et `inspections` ainsi que leurs modèles, contrôleurs, vues, factories et seeders existaient avant l'ajout du score de santé.

Le dépôt ne contient actuellement ni modèle métier `App\Models\Equipment`, ni modèle `App\Models\Rental`. Un adaptateur `TechnicalPreviewEquipment` représente temporairement la table `equipment`, tandis que les pages publiques utilisent les objets statiques de `FrontDemo`. Le service de score détecte donc les tables facultatives avant de les interroger.

## Score de santé de l'équipement

Le service `App\Services\Maintenance\EquipmentHealthScoreService` produit quatre sous-scores bornés entre 0 et 100 :

```text
âge         = clamp(100 - âge_en_années × 5)
dommages    = clamp(100 - somme des pénalités de gravité)
maintenance = clamp(100 - jours depuis maintenance × 0,20)
locations   = clamp(100 - nombre de locations × 2)
```

Les pénalités de dommage par défaut sont 8 pour léger, 20 pour modéré et 40 pour grave. Tant que `inspections` ne possède pas de colonne de gravité, chaque inspection signalant un dommage reçoit la gravité par défaut `moderate`.

Le score final est une moyenne pondérée :

```text
score = âge × 20 % + dommages × 35 % + maintenance × 25 % + locations × 20 %
```

La moyenne est normalisée par la somme des poids, arrondie à l'entier le plus proche puis bornée entre 0 et 100. Les poids, pénalités et seuils sont configurables dans `config/maintenance.php` et surchargeables avec les variables `MAINTENANCE_HEALTH_*`.

Un équipement de moins de 30 jours sans maintenance conserve 100 pour ce sous-score. Après ce délai, l'absence totale de maintenance donne 0. L'absence de table `rentals` équivaut temporairement à zéro location.

Les badges suivent ces seuils : vert à partir de 75, orange de 40 à 74, rouge sous 40.

## Affichage

La fiche publique affiche `x-ui.health-score-badge`. Comme elle utilise encore des données statiques, le contrôleur fournit des métriques de démonstration dérivées de l'état `New`, `Good` ou `Fair`. Après fusion du vrai modèle Equipment, la fiche devra appeler `EquipmentHealthScoreService::score($equipment)`.

## Tests

```bash
php artisan test --filter=EquipmentHealthScoreServiceTest
php artisan test --filter=FrontOfficeTest
php artisan test
```

Les tests couvrent le cas nominal, un équipement neuf, un équipement très dégradé, les bornes 0/100, les seuils des badges et un équipement sans maintenance ni location.

## Checklist d'inspection par catégorie

Les modèles `InspectionChecklist` et `InspectionChecklistItem` définissent les contrôles propres à chaque catégorie. Chaque élément possède un indicateur `is_critical`. L'inspection référence le modèle utilisé et conserve un instantané JSON dans `checklist_results`, avec l'identifiant, le libellé, la criticité et le résultat de chaque élément.

`InspectionChecklistService` valide à nouveau l'ensemble des éléments et enregistre l'inspection dans une transaction. Pour chaque contrôle critique non validé, il crée un `Damage` grave dans cette même transaction et active automatiquement `damage_detected`. Un échec non critique ne crée aucun dommage. Lors d'une modification, les dommages issus de la checklist sont resynchronisés.

Le seeder `InspectionChecklistSeeder` crée des contrôles réalistes pour panneaux solaires, batteries et éoliennes. Il lit la table `categories` lorsqu'elle existe, sinon les trois catégories de démonstration de `FrontDemo`.

```bash
php artisan db:seed --class=InspectionChecklistSeeder
vendor/bin/phpunit tests/Unit/InspectionChecklistServiceTest.php --no-coverage --do-not-cache-result
vendor/bin/phpunit tests/Feature/InspectionChecklistFeatureTest.php --no-coverage --do-not-cache-result
```

## Photos avant/après

Les photos sont enregistrées sur le disque Laravel `public`, sous `maintenance/{maintenance_id}`. La table `maintenance_photos` conserve le type `before` ou `after`, le chemin, le nom original, le MIME et la taille. Seuls JPEG, PNG et WebP jusqu'à 5 Mio sont acceptés.

La suppression efface l'enregistrement et le fichier. La fiche maintenance propose deux formulaires d'upload et affiche un composant Alpine de comparaison par curseur dès qu'une photo avant et une photo après sont présentes.

Avant l'utilisation hors environnement de développement :

```bash
php artisan storage:link
vendor/bin/phpunit tests/Feature/MaintenancePhotoTest.php --no-coverage --do-not-cache-result
```

## Tableau de bord, PDF, timeline, QR et TCO

Le tableau de bord administrateur expose trois agrégations JSON sans N+1 : coûts mensuels, pannes par catégorie et délai moyen entre pannes. La page utilise Chart.js chargé dynamiquement. Les rapports inspection et maintenance sont produits par DomPDF et protégés par les policies techniques.

La timeline fusionne inspections et maintenances par équipement, en ordre décroissant. Le QR code SVG contient une URL signée valable 30 jours vers le formulaire d'inspection prérempli. Le TCO additionne les maintenances et lit les revenus depuis la source configurable suivante :

```text
MAINTENANCE_TCO_REVENUE_TABLE=rentals
MAINTENANCE_TCO_EQUIPMENT_COLUMN=equipment_id
MAINTENANCE_TCO_AMOUNT_COLUMN=total_amount
```

Le ratio coût/revenu vaut `null` lorsque le revenu est nul.

## Services IA

### Fonctions IA avancees

L'analyse photo accepte JPEG, PNG ou WebP jusqu'a 5 Mio. Le modele vision renvoie un JSON strict (`damage_detected`, `comments`) qui pre-remplit le formulaire et reste modifiable. La dictee utilise Web Speech API en `fr-FR`; son bouton est masque si le navigateur ne la prend pas en charge. L'endpoint audio optionnel accepte MP3, WAV, WebM et M4A jusqu'a 10 Mio.

Le score d'anomalie est `z = (cout - moyenne historique) / ecart-type historique`. Il exige trois valeurs historiques, ignore un ecart-type nul et alerte les administrateurs lorsque `|z| > 2`. Le seuil est configurable.

L'estimation calcule moyenne, mediane et fourchette min-max pour la meme categorie et gravite. Elle utilise ensuite l'historique global, puis les valeurs `MAINTENANCE_DEFAULT_REPAIR_*`.

Routes: `POST /technical/ai/analyze-damage`, `POST /technical/ai/transcribe`, `POST /technical/ai/estimate-repair`.

`AiClientInterface` isole le fournisseur externe. `OpenAiCompatibleClient` lit exclusivement sa clé depuis `MAINTENANCE_AI_API_KEY`; `FakeAiClient` est utilisé par les tests. La prédiction de panne est un système de règles local branché via `FailureRiskPredictorInterface`. La classification de gravité exige un JSON contenant `severity` et `repair_hours`, puis utilise un repli par mots-clés si l'appel échoue ou si le JSON est invalide.
