# Hypothèses du module Maintenance et inspection

- Le rôle `inspector` n'existe pas dans `App\\Enums\\UserRole` et le parcours de sélection de rôle n'autorise que `buyer` et `owner`. Le module conserve donc les autorisations existantes : les administrateurs peuvent tout gérer et les propriétaires peuvent gérer uniquement les enregistrements liés à leur équipement. Ajouter un rôle inspecteur nécessite une décision produit et une mise à jour du parcours d'attribution des rôles.
- Les tables, modèles, factories, seeders, vues et routes du module existent déjà. Ils sont réutilisés et complétés sans recréer de schéma.
- Les tests demandés sont Pest. Dans l'environnement courant, `php artisan test` échoue avant le lancement des tests parce que le runtime fournit un chemin de travail Windows non accessible au processus enfant; `vendor/bin/pest` échoue car Pest ne résout pas le répertoire `tests/` dans ce contexte. Le résultat devra être confirmé depuis un environnement où le dépôt est monté sous le chemin Windows configuré.
- Le vrai modèle `Equipment` et le modèle `Rental` ne sont pas encore présents. Le score interroge les tables correspondantes lorsqu'elles existent et considère zéro location sinon.
- La table `inspections` ne contient pas encore de gravité. Chaque dommage existant reçoit donc la gravité configurable `moderate` jusqu'à l'ajout de cette donnée par la fonctionnalité checklist/dommages.
- La fiche publique d'équipement utilise `FrontDemo` et non la base. Son badge repose temporairement sur des métriques de démonstration cohérentes avec l'état affiché. La fiche devra appeler `EquipmentHealthScoreService::score()` après fusion du modèle Equipment.
- La table `categories` n'existe pas encore. `inspection_checklists.category_id` reste donc indexé sans clé étrangère et le seeder utilise les catégories de `FrontDemo`; la contrainte pourra être ajoutée après fusion du module Catégorie.
- Un échec de contrôle critique crée un dommage de gravité `severe`, car aucun référentiel métier de conversion criticité/gravité n'existe encore.
- En attendant le module Location de l'étudiant 4, le TCO suppose une table `rentals` avec `equipment_id` et `total_amount`. Ces trois noms sont configurables dans `config/maintenance.php`.
- L'utilisateur authentifié qui génère un PDF est affiché comme inspecteur/signataire, faute de colonne `inspector_id` dans les inspections et maintenances actuelles.
- Les reparations n'avaient pas de cout propre. La colonne nullable `damages.repair_cost` represente le cout final et alimente l'estimation IA-7.
- La categorie est deduite de `equipment.category_id`. Si elle est absente, les calculs utilisent l'historique du meme equipement ou le repli global.
- Les alertes de cout anormal sont envoyees par courriel, faute de table `notifications`.
