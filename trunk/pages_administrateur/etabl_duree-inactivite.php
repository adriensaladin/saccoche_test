<?php
/**
 * @version $Id: etabl_duree-inactivite.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Délai avant déconnexion";
?>

<p><span class="manuel"><a class="pop_up" href="./aide.php?fichier=delai_inactivite_deconnexion_automatique">DOC : Délai d'inactivité et déconnexion automatique.</a></span></p>

<?php
$options = '';
for($delai=10 ; $delai<100 ; $delai+=10)
{
	$selected = ($delai==$_SESSION['DUREE_INACTIVITE']) ? ' selected="selected"' : '' ;
	$options .= '<option value="'.$delai.'"'.$selected.'>'.$delai.' minutes</option>';
}
?>

<form id="delai" action=""><fieldset>
	<label class="tab" for="f_delai">Délai :</label><select id="f_delai" name="f_delai"><?php echo $options ?></select><br />
	<span class="tab"></span><input id="f_submit" type="button" value="Valider ce délai." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>
