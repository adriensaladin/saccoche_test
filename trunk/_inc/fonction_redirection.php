<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

function entete()
{
	header('Content-Type: text/html');header('Charset: utf-8');
	echo'<?xml version="1.0" encoding="utf-8"?>';
	echo'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo'<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">';
}

function affich_message_exit($titre,$contenu)
{
	if(SACoche=='index')
	{
		entete();
		echo'<head><title>Évaluation par compétences - '.$titre.'</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
		echo'<body>'.$contenu.'</body>';
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
		echo'<head><title>Évaluation par compétences - Redirection</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
		echo'<body><script type="text/javascript">';
		if($texte_alert)
		{
			echo'alert("'.utf8_encode($texte_alert).'");';
		}
		echo'window.document.location.href="./'.$adresse.'"';
		echo'</script></body>';
		echo'</html>';
	}
	else
	{
		echo utf8_encode($texte_alert);
	}
	exit();
}
?>