<?php
/**
 * @version $Id: config_serveur.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Paramétrage PHP et initialisations.
 * 
 */

// MAINTENANCE : Mettre à 1 en cas de maintenance : seul le webmestre SACoche pourra continuer à surfer.
define('MAINTENANCE',0);

// FILE_CSS et FILE_JS : pour éviter les problèmes de mise en cache, modifier le nom du fichier lors d'une mise à jour
// VERSION_JS : pour éviter le problème de mise en cache du js de la page d'accueil principale
define('FILE_CSS','style21.css');
define('FILE_JS','script21.js');
$VERSION_JS = '';

// ID_DEMO : id de l'établissement de démonstration
// 0 pose des pbs, et il faut prendre un id disponible dans Sésaprof
// Attention, on ne peut pas changer cette valeur à la légère, il faut aussi modifier les entrées correspondantes dans la BDD...
define('ID_DEMO',9999);

// ID_MATIERE_TRANSVERSALE : id de la matière transversale dans la table "livret_matiere"
// Ne pas changer cette valeur !
define('ID_MATIERE_TRANSVERSALE',99);

// $GLOBALS['TAB_ID_NIVEAUX_PALIERS'] : tableau des id des niveaux des paliers dans la table "livret_niveau"
// Ne pas changer ces valeurs !
$GLOBALS['TAB_ID_NIVEAUX_PALIERS'] = array(46,47,48,49);

// CHARSET : "iso-8859-1" ou "utf-8" suivant l'encodage utilisé ; ajouter si besoin "AddDefaultCharset ..." dans le fichier .htaccess
define('CHARSET','utf-8');

// SERVEUR_TYPE : Serveur local de développement (LOCAL) ou serveur en ligne de production (PROD)
$serveur = ($_SERVER['SERVER_NAME']=='localhost') ? 'LOCAL' : 'PROD';
define('SERVEUR_TYPE',$serveur);

// Vérifie la version de PHP
$version_mini = '5.1';
if(version_compare(PHP_VERSION,$version_mini,'<'))
{
	affich_message_exit($titre='PHP trop ancien',$contenu='Version de PHP utilisée sur ce serveur : '.PHP_VERSION.'<br />Version de PHP requise au minimum : '.$version_mini);
}

// Vérifie la présence des modules nécessaires
$extensions_chargees = get_loaded_extensions();
$extensions_requises = array('session','mysql','dom','gd','mbstring','PDO','pdo_mysql','zip');
$extensions_manquantes = array_diff($extensions_requises,$extensions_chargees);
if(count($extensions_manquantes))
{
	affich_message_exit($titre='PHP incomplet',$contenu='Les modules PHP suivants sont manquants : '.implode($extensions_manquantes,' '));
}

// Fixe le niveau de rapport d'erreurs PHP
if(SERVEUR_TYPE == 'PROD')
{
	// Rapporte toutes les erreurs à part les E_NOTICE ; c'est la configuration par défaut de php.ini.
	ini_set('error_reporting',E_ALL ^ E_NOTICE);
}
else
{
	// Rapporte toutes les erreurs PHP sur le serveur local
	ini_set('error_reporting',E_ALL);
}

// Définit le décalage horaire par défaut de toutes les fonctions date/heure 
@date_default_timezone_set('Europe/Paris');

// Ne pas échapper les appostrophes pour Get/Post/Cookie
ini_set('magic_quotes_gpc',0);
ini_set('magic_quotes_sybase',0);

// Ne pas enregistrer les variables Environment/GET/POST/Cookie/Server comme des variables globales.
// register_globals ne peut pas être définit durant le traitement avec "ini_set"...
// ini_set(register_globals,0);

// Durée de vie des données (session...) sur le serveur, en nombre de secondes.
ini_set('session.gc_maxlifetime',3000);
// Le module doit utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur.
// Protection contre les attaques qui utilisent des identifiants de sessions dans les URL.
ini_set('session.use_trans_sid', 0); 
ini_set('session.use_only_cookies',1);

// N'autorise pas les balises courtes d'ouverture de PHP (et possibilité d'utiliser XML sans passer par echo).
ini_set('short_open_tag',0);

// Désactive le mode de compatibilité avec le Zend Engine 1 (PHP 4).
// Sinon l'utilisation de "simplexml_load_string()" ou "DOMDocument" (par exemples) provoquent des erreurs fatales, + incompatibilité avec classe PDO.
ini_set('zend.ze1_compatibility_mode',0);

// Modifie l'encodage interne pour les fonctions mb_* (manipulation de chaînes de caractères multi-octets)
mb_internal_encoding(CHARSET);

?>