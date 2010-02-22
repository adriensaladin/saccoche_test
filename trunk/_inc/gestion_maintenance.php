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
 * Blocage des sites si maintenance, sauf pour le webmestre déjà identifié.
 * Nécessite que la session soit ouverte.
 * 
 */

if( MAINTENANCE && ($_SESSION['PROFIL']!='public') && (!$_SESSION['GOD']) )
{
	affich_message_exit($titre='Maintenance',$contenu='Site en cours de maintenance ! Réouverture dans quelques instants...');
}

?>