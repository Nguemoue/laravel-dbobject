# Laravel DBObject

**Laravel DBObject** est un package Laravel qui fournit un moyen simple de gérer les migrations versionnées d'objets de base de données tels que les **fonctions**, les **procédures stockées**, les **vues** et les **déclencheurs**. Il complète les migrations natives de Laravel en vous permettant d'inclure ces objets dans votre processus de contrôle de version et de déploiement.

Ce package est conçu pour les développeurs qui souhaitent conserver leur schéma de base de données, y compris ces objets, en synchronisation avec le code base de leur application.

## Installation

Vous pouvez installer le package via Composer :

```bash
composer require nguemoue/laravel-dbobject
```

## Configuration

Pour publier le fichier de configuration, exécutez la commande suivante :

```bash
php artisan vendor:publish --provider="Nguemoue\LaravelDbObject\LaravelDbObjectServiceProvider" --tag="db-objects-config"
```

Cela créera un fichier `config/db-objects.php` où vous pourrez configurer le package.

L'option de configuration la plus importante est `path`, qui spécifie le répertoire où vos fichiers d'objets de base de données sont stockés. Par défaut, il s'agit de `database/dbo`.

```php
// config/db-objects.php
return [
    'path' => 'database/dbo',
    // ...
];
```

## Utilisation

### Création d'un nouveau fichier DBObject

Pour créer un nouveau fichier DBObject, vous pouvez utiliser la commande `dbo:make` :

```bash
php artisan dbo:make my_new_function
```

Cela créera un nouveau fichier SQL dans le répertoire configuré (`database/dbo` par défaut). Vous pouvez ensuite ajouter votre code SQL à ce fichier.

Le fichier généré contiendra deux sections, séparées par `-- up:` et `-- down:`. La section `up` est pour le code qui crée l'objet de base de données, et la section `down` est pour le code qui le supprime.

```sql
-- up:
CREATE FUNCTION my_new_function() RETURNS INT
BEGIN
    RETURN 1;
END;

-- down:
DROP FUNCTION my_new_function;
```

### Exécution des migrations

Pour exécuter vos migrations DBObject, utilisez la commande `dbo:migrate` :

```bash
php artisan dbo:migrate
```

Cela exécutera la section `up` de tous les nouveaux fichiers DBObject qui n'ont pas encore été migrés.

### Annulation des migrations

Pour annuler le dernier lot de migrations, utilisez la commande `dbo:rollback` :

```bash
php artisan dbo:rollback
```

Vous pouvez également utiliser l'option `step` pour spécifier le nombre de lots à annuler :

```bash
php artisan dbo:rollback --step=5
```

### Autres commandes

Le package fournit plusieurs autres commandes pour vous aider à gérer vos objets de base de données :

*   `dbo:refresh` : Annulez toutes vos migrations, puis migrez-les à nouveau.
*   `dbo:redo` : Annulez la dernière migration, puis exécutez-la à nouveau.
*   `dbo:status` : Affichez l'état de chaque migration.

## Tests

```bash
composer test
```

## Changelog

Veuillez consulter le [CHANGELOG](CHANGELOG.md) pour plus d'informations sur les changements récents.

## Contribution

Veuillez consulter [CONTRIBUTING](.github/CONTRIBUTING.md) pour plus de détails.

## Vulnérabilités de sécurité

Veuillez consulter [notre politique de sécurité](../../security/policy) sur la manière de signaler les vulnérabilités de sécurité.

## Crédits

-   [Nguemoue](https://github.com/nguemoue)
-   [Tous les contributeurs](../../contributors)

## Licence

La licence MIT (MIT). Veuillez consulter le [fichier de licence](LICENSE.md) pour plus d'informations.