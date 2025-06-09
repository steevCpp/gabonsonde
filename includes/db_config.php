<?php
// Configuration de la connexion à la base de données PostgreSQL

// Hôte de la base de données (généralement 'localhost' en développement)
define('DB_HOST', 'localhost');

// Port de la base de données PostgreSQL (5432 est le port par défaut)
define('DB_PORT', '5432');

// Nom de la base de données
define('DB_NAME', 'gabonsondedb'); // À remplacer par le nom réel de votre BD

// Nom d'utilisateur pour la connexion à la base de données
define('DB_USER', 'gabonsondeuser'); // À remplacer par votre utilisateur PostgreSQL

// Mot de passe pour la connexion à la base de données
define('DB_PASSWORD', 'gabonsondemdp'); // À remplacer par votre mot de passe PostgreSQL

// Options de connexion supplémentaires (facultatif)
// Exemple: define('DB_CONNECT_OPTIONS', 'sslmode=require');
// Pour pg_connect, les options sont généralement incluses dans la chaîne de connexion.

?>
