# StorySharing, l'application pour partager des histoires

Ce répertoire à été réalisé dans le cadre d'un projet pour l'ECV Digital. Ce projet est connecté à une base de données MySQL et utilise le framework Symfony avec Twig en template.

## Requis

Composer

### Installation des modules

Pour installer les modules, à la racine du répertoire, il faut taper la commande suivante :

```bash
composer install
```

> Cette commande téléchargera le dossier `vendor` contenant les modules nécessaires.

Ensuite, il faut remplir le fichier .env :

```bash
DATABASE_URL=
DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

### Migration

Pour la récupération de la base de données, il faut taper la commande suivante  :

```bash
 php bin/console doctrine:migrations:migrate
```

## Lancement du serveur

Il ne reste qu'une chose à faire, lancer le serveur avec cette commande :

```bash
 symfony serve
```

Vous pouvez maintenant visiter le projet et apprécier (ou non) le travail fourni !