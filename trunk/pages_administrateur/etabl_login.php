<?php
/**
 * @version $Id: etabl_login.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Format des noms d'utilisateurs";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_format_logins">DOC : Gestion du format des noms d'utilisateurs</a></span></p>

<hr />

<form action=""><fieldset>
	<label class="tab" for="f_login_professeur">Format professeur :</label><input id="f_login_professeur" name="f_login_professeur" value="<?php echo $_SESSION['MODELE_PROF']; ?>" size="20" maxlength="20" /><br />
	<label class="tab" for="f_login_eleve">Format élève :</label><input id="f_login_eleve" name="f_login_eleve" value="<?php echo $_SESSION['MODELE_ELEVE']; ?>" size="20" maxlength="20" /><br />
	<span class="tab"></span><input id="f_submit" type="button" value="Valider." /><label id="ajax_msg">&nbsp;</label>
</fieldset></form>
