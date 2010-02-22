<?php
/**
 * @version $Id: password.php 8 2009-10-30 20:56:02Z thomas $
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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Changer son mot de passe";
?>

<hr />

<?php
$fin = ($_SESSION['SSO']=='normal') ? 'oui' : 'non' ;
include('./pages_'.$DOSSIER.'/'.$FICHIER.'_'.$fin.'.php');
?>
