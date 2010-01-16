<?php
/**
 * @version $Id: etabl_resilier.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Résilier l'inscription de l'établissement";
?>

<p><span class="danger"> Si vous confirmez votre choix, alors toutes les données des élèves, professeurs, compétences, classes, etc. seront complètement effacées !</span></p>

<p>Si vous souhaitez simplement transmettre votre rôle d'administrateur, <?php echo mailto('thomas.crespin@sesamath.net','Changer d\'administrateur','contactez-moi'); ?> en m'indiquant les coordonnées de votre remplaçant.</p>

<form action=""><fieldset>
	<span class="tab"></span><input id="f_submit" type="submit" value="Résilier l'inscription." /><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
