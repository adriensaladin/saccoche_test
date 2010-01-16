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

<p><span class="astuce">Entrer le mot de passe actuel, puis deux fois le nouveau mot de passe choisi.</span></p>

<form action=""><fieldset>
	<label class="tab" for="f_password0">Actuel :</label><input id="f_password0" name="f_password0" size="20" type="password" value="" /><br />
	<label class="tab" for="f_password1">Nouveau 1/2 :</label><input id="f_password1" name="f_password1" size="20" type="password" value="" /><br />
	<label class="tab" for="f_password2">Nouveau 2/2 :</label><input id="f_password2" name="f_password2" size="20" type="password" value="" /><br />
	<span class="tab"></span><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>
