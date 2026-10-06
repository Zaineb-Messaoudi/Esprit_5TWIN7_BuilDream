# SolarShare — module Maintenance et état technique

## Étape 1 — dépendances à intégrer après le merge

Ce module ne crée pas les entités d'autres membres. Pour activer le CRUD et le seeder, il attend :

- une table `equipment` avec `id`, `owner_id` et `name`, et un modèle `App\Models\Equipment` ;
- une table `rentals` avec `id` et `equipment_id`, et un modèle `App\Models\Rental` seulement pour associer les inspections à une location ;
- `Rental::equipment()` pour limiter la liste de locations au propriétaire quand ce module est présent.

Si l'équipe choisit un autre nom de table ou de colonne, aligner les requêtes de ce module avant d'activer le CRUD. Les migrations techniques fonctionnent dès maintenant : `equipment_id` et `rental_id` restent des colonnes indexées tant que leurs tables parentes sont absentes. Après le merge d'Equipment, lancer `php artisan technical:link-foreign-keys` pour ajouter les contraintes disponibles ; relancer la commande après le merge de Rental pour ajouter la clé étrangère facultative.

Sur MySQL, une tentative de migration interrompue peut laisser une table `maintenances` vide sans index ni clé étrangère, alors que la migration reste « Pending ». La migration de ce module détecte et reprend ce cas sans effacer la table. `php artisan migrate --seed` lance `DatabaseSeeder`, pas `TechnicalSeeder` ; utiliser la commande `db:seed --class=TechnicalSeeder` ci-dessous pour les exemples techniques.

Ajouter dans `Equipment` (par le membre responsable de ce modèle) :

```php
public function maintenances(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Maintenance::class);
}

public function inspections(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Inspection::class);
}
```

Ajouter dans `Rental` :

```php
public function inspections(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Inspection::class);
}
```

Les trois autres relations (`Maintenance::equipment`, `Maintenance::report`, `MaintenanceReport::maintenance`, `Inspection::equipment`, `Inspection::rental`) sont déjà codées dans ce module.

## Étape 2 — migrations et modèles

Les fichiers générés correspondent aux commandes Artisan suivantes :

```bash
php artisan make:model Maintenance -mf
php artisan make:model MaintenanceReport -mf
php artisan make:model Inspection -mf
php artisan make:controller Technical/MaintenanceController --resource
php artisan make:controller Technical/MaintenanceReportController --resource
php artisan make:controller Technical/InspectionController --resource
php artisan make:seeder TechnicalSeeder
```

Les fichiers sont déjà créés : il ne faut pas relancer ces commandes dans cette branche. `maintenance_reports.maintenance_id` est unique pour garantir le lien 1–1. `inspections.rental_id` accepte `NULL`. Après liaison des clés étrangères, la suppression d'un équipement lié est refusée par la base ; supprimer une maintenance supprime son rapport par cascade.

## Étape 3 — routes et formulaires

- Front Office propriétaire : `/technical/maintenances`, `/technical/reports`, `/technical/inspections`.
- Les liens existants `/owner/maintenance` et `/my/inspections` redirigent vers ces listes réelles lorsque les modules Equipment et Rental sont intégrés. Avant cela, ils affichent un état d’attente sans fausses données.
- Back Office administrateur : mêmes ressources sous `/admin/technical/`.
- Chaque ressource possède les sept actions REST (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`).
- Les formulaires utilisent `old()` et affichent les erreurs de validation. Le bouton de suppression demande confirmation.
- Le propriétaire ne peut sélectionner ou ouvrir que les dossiers liés à ses équipements. L'administrateur voit tous les dossiers.
- Une inspection peut référencer une location seulement si cette location concerne l'équipement sélectionné.

## Étape 4 — exécution et données de démonstration

Les migrations techniques peuvent être lancées avant le merge :

```bash
php artisan migrate
```

Après intégration et migration du module Equipment, puis création de quelques équipements :

```bash
php artisan technical:link-foreign-keys
php artisan db:seed --class=TechnicalSeeder
```

Le seeder utilise des équipements réels présents dans la base et une location du même équipement quand elle existe. Il est idempotent pour ses dossiers d'exemple : le relancer ne crée pas de doublons. Il ne modifie pas `DatabaseSeeder` : son exécution est volontaire et indépendante. Les descriptions et dates de maintenance sont des exemples générés ; seuls les liens vers équipement et location viennent d'enregistrements réels.

### Prévisualisation locale dans MySQL, avant le module Equipment

Pour tester les pages dans le navigateur avant le merge, exécuter :

```bash
php artisan migrate
php artisan db:seed --class=TechnicalPreviewSeeder
php artisan technical:link-foreign-keys
php artisan serve
```

`TechnicalPreviewSeeder` crée uniquement en environnement local une table `equipment` minimale, marquée par la colonne `technical_preview`, trois équipements de test pour chaque compte propriétaire existant, puis des maintenances, rapports et inspections enregistrés dans la base. Il peut être relancé sans créer de doublons. Ce sont des **données de développement persistées**, et non les équipements du module de l'autre membre.

- Tu peux utiliser ton compte propriétaire habituel. Un autre compte de test est disponible : `technical-preview-owner@solarshare.test` / `TechnicalPreview2026!`.
- Pages Front Office : `/owner/maintenance`, `/my/inspections`, puis « Maintenance reports » dans la navigation du module.

**Avant d'exécuter la migration Equipment de l'autre membre**, supprimer cette fixture locale :

```bash
php artisan technical:preview-cleanup --force
```

La commande supprime seulement les équipements marqués comme fixture et leurs dossiers techniques associés ; elle refuse de supprimer une table comportant des équipements non marqués. Après le merge, migrer le vrai module Equipment, créer ses équipements, puis relancer `TechnicalSeeder` pour obtenir des données liées au nouveau schéma.

## Étape ultérieure — valeur ajoutée

À ta demande, le changement automatique du statut de l'équipement, l'historique des maintenances et le coût total ne sont pas encore implémentés. Le meilleur point d'intégration sera un service métier ou un observer, après accord sur les valeurs exactes du champ `equipment.status` avec l'autre membre de l'équipe.
