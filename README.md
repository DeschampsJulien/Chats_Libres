# Chats Libres

Projet Symfony local pour travailler avec XAMPP, MySQL/phpMyAdmin, du routage et des vues Twig.

## Prerequis

- PHP 8.2 via XAMPP
- Apache et MySQL lances dans XAMPP Control Panel
- Composer

## Configuration MySQL locale

1. Ouvrir phpMyAdmin sur `http://localhost/phpmyadmin`
2. Creer une base nommee `chats_libres`
3. Utiliser l'utilisateur `root`
4. Laisser le mot de passe vide si vous utilisez la config XAMPP par defaut

## Variables d'environnement

Le projet est configure pour utiliser MySQL en local :

`DATABASE_URL="mysql://root:@127.0.0.1:3306/chats_libres?serverVersion=10.4.32-MariaDB&charset=utf8mb4"`

Si votre mot de passe MySQL n'est pas vide, adaptez cette valeur dans `.env.local`.

## Commandes utiles

- Installer les dependances : `composer install`
- Lancer le serveur Symfony : `symfony serve -d`
- Ou avec PHP : `php -S 127.0.0.1:8000 -t public`
- Voir les routes : `php bin/console debug:router`
- Creer la base : `php bin/console doctrine:database:create`
- Creer une migration : `php bin/console make:migration`
- Executer les migrations : `php bin/console doctrine:migrations:migrate`

## Premiere page

- URL : `http://127.0.0.1:8000/`
- Controleur : `src/Controller/HomeController.php`
- Vue Twig : `templates/home/index.html.twig`
# Chats_Libres
