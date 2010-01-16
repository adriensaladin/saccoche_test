<?php
/**
 * @version $Id: ajax.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Fichier appelé pour chaque appel ajax.
 * Passage en GET des paramètres pour savoir quelle page charger.
 * 
 */

// Atteste l'appel de cette page avant l'inclusion d'une autre
define('SACoche','ajax');

// Fonctions de redirections et Configuration serveur
require_once('./_inc/fonction_redirection.php');
require_once('./_inc/config_serveur.php');

// Paramètres transmis
$DOSSIER = (isset($_GET['dossier'])) ? $_GET['dossier'] : 'public';
$FICHIER = (isset($_GET['fichier'])) ? $_GET['fichier'] : 'index';
$PREFIXE = ($DOSSIER!='webmestre') ? '' : '__' ;

// Pour connexion base de données SACoche
if($FICHIER!='installation')
{
	$filename_php = './__mysql_config/serveur_sacoche_'.SERVEUR_TYPE.'.php';
	if(is_file($filename_php))
	{
		include_once($filename_php);
		require_once('./_inc/class.DB.config.sacoche.php');
	}
	else
	{
		affich_message_exit($titre='Paramètres BDD manquants',$contenu='Paramètres de connexion à la base de données manquants.');
	}
}

// Pour connexion base de données Sésamath2 (serveur Sésamath uniquement)
$filename_php = './__mysql_config/serveur_sesamath2_'.SERVEUR_TYPE.'.php';
if(is_file($filename_php))
{
	include_once($filename_php);
	require_once('./_inc/class.DB.config.sesamath2.php');
}
require_once('./_inc/class.DB.php');

// Fonctions
require_once('./_inc/fonction_clean.php');
require_once('./_inc/fonction_sessions.php');
require_once('./_inc/fonction_requetes_administration.php');
require_once('./_inc/fonction_requetes_formulaires_select.php');
require_once('./_inc/fonction_requetes_referentiel.php');
require_once('./_inc/fonction_requetes_gestion.php');
require_once('./_inc/fonction_affichage.php');

// Détermination du CHARSET d'en-tête
$test_xml = (strpos($_SERVER['HTTP_ACCEPT'],'/xml')) ? TRUE : FALSE;
$test_upload = ( (isset($_SERVER['CONTENT_TYPE'])) &&(strpos($_SERVER['CONTENT_TYPE'],'multipart/form-data')!==FALSE) ) ? TRUE : FALSE; // L'upload d'un fichier XML change le HTTP_ACCEPT, d'où ce second test
$format = ( $test_xml && !$test_upload ) ? 'text/xml' : 'text/html' ;
header('Content-Type: '.$format);header('Charset: utf-8');

// Ouverture de la session et gestion des droits d'accès
$PROFIL_REQUIS = $DOSSIER;
require_once('./_inc/gestion_sessions.php');

// Blocage des sites si maintenance
require_once('./_inc/gestion_maintenance.php');

// Arrêt s'il fallait seulement mettre la session à jour (la session d'un user connecté n'a pas été perdue si on arrive jusqu'ici)
if($FICHIER=='conserver_session_active')
{
	exit('ok');
}

// Chargement de la page concernée
$filename_php = './'.$PREFIXE.'pages_'.$DOSSIER.'/'.$FICHIER.'.ajax.php';
if(is_file($filename_php))
{
	include($filename_php);
}
else
{
	echo'Page "'.$filename_php.'" manquante.';
}
?>
