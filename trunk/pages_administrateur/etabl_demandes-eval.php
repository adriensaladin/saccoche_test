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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Demandes d'évaluations";
?>

<p><span class="manuel"><a class="pop_up" href="./aide.php?fichier=demandes_evaluations">DOC : Demandes d'évaluations.</a></span></p>

<?php
$options = '';
for($nb_demandes=0 ; $nb_demandes<10 ; $nb_demandes++)
{
	$selected = ($nb_demandes==$_SESSION['ELEVE_DEMANDES']) ? ' selected="selected"' : '' ;
	$texte = ($nb_demandes>0) ? ( ($nb_demandes>1) ? $nb_demandes.' demandes simultanées autorisées par matière' : '1 seule demande à la fois autorisée par matière' ) : 'Aucune demande autorisée (fonctionnalité desactivée).' ;
	$options .= '<option value="'.$nb_demandes.'"'.$selected.'>'.$texte.'</option>';
}
?>

<form id="delai" action=""><fieldset>
	<label class="tab" for="f_demandes">Nombre maximal :</label><select id="f_demandes" name="f_demandes"><?php echo $options ?></select><br />
	<span class="tab"></span><input id="f_submit" type="button" value="Valider ce choix." /><label id="ajax_msg">&nbsp;</label><br />
</fieldset></form>
