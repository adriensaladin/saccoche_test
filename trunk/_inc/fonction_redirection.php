<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

function entete()
{
	header('Content-Type: text/html; charset='.CHARSET);
	echo'<?xml version="1.0" encoding="'.CHARSET.'"?>';
	echo'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo'<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">';
}

function affich_message_exit($titre,$contenu)
{
	if(SACoche=='index')
	{
		entete();
		echo'<head><title>SACoche » '.$titre.'</title><meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" /></head>';
		echo'<body style="background:#EAEAFF;font:15px sans-serif;color:#D00">';
		echo'<p>'.$contenu.'</p>';
		echo'<p><a href="./index.php">» Retour en page d\'accueil de SACoche.</a></p>';
		echo'</body>';
		echo'</html>';
	}
	else
	{
		echo $contenu;
	}
	exit();
}

function alert_redirection_exit($texte_alert,$adresse='index.php')
{
	if(SACoche=='index')
	{
		entete();
		echo'<head><title>SACoche » Redirection</title><meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" /></head>';
		echo'<body><script type="text/javascript">';
		if($texte_alert)
		{
			echo'alert("'.$texte_alert.'");';
		}
		echo'window.document.location.href="./'.$adresse.'"';
		echo'</script></body>';
		echo'</html>';
	}
	else
	{
		echo $texte_alert;	// utf8_encode() retiré
	}
	exit();
}
?>