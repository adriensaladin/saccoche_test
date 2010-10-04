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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Réglage des autorisations";
$VERSION_JS_FILE += 2;

$i_id = 0;	// Pour donner des ids aux checkbox et radio
?>

<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_autorisations">DOC : Réglage des autorisations</a></div>
<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__demandes_evaluations">DOC : Demandes d'évaluations</a></div>

<hr />

<h4>Demandes d'évaluations des élèves</h4>

<?php
$options = '';
for($nb_demandes=0 ; $nb_demandes<10 ; $nb_demandes++)
{
	$selected = ($nb_demandes==$_SESSION['DROIT_ELEVE_DEMANDES']) ? ' selected="selected"' : '' ;
	$texte = ($nb_demandes>0) ? ( ($nb_demandes>1) ? $nb_demandes.' demandes simultanées autorisées par matière' : '1 seule demande à la fois autorisée par matière' ) : 'Aucune demande autorisée (fonctionnalité desactivée).' ;
	$options .= '<option value="'.$nb_demandes.'"'.$selected.'>'.$texte.'</option>';
}
?>
<form id="form_eleve_demandes" action=""><fieldset>
	<label class="tab" for="f_eleve_demandes">Nombre maximal :</label><select id="f_eleve_demandes" name="f_eleve_demandes"><?php echo $options ?></select><br />
	<span class="tab"></span><button id="valider_eleve_demandes" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer cette valeur.</button><label id="ajax_msg_eleve_demandes">&nbsp;</label>
</fieldset></form>

<hr />

<h4>Profils autorisés à valider le socle</h4>

<form id="form_validation_socle" action="">
	<table>
		<?php
		$tab_profils = array( 'directeur'=>'directeurs' , 'professeur'=>'tous les professeurs' , 'profprincipal'=>'seulement les<br />professeurs principaux' , 'aucunprof'=>'aucun professeur' );
		$tab_objets  = array( 'droit_validation_entree'=>'validation des items du socle' , 'droit_validation_pilier'=>'validation des compétences du socle (ou piliers)' );
		// 1ère ligne
		echo'<thead><tr><th class="nu"></th>';
		foreach($tab_profils as $profil_key => $profil_txt)
		{
			echo'<th class="hc">'.$profil_txt.'</th>';
		}
		echo'</tr></thead>';
		// Les lignes avec boutons
		echo'<tbody>';
		foreach($tab_objets as $objet_key => $objet_txt)
		{
			echo'<tr><th>'.$objet_txt.'</th>';
			$tab_check = explode(',',$_SESSION[strtoupper($objet_key)]);
			foreach($tab_profils as $profil_key => $profil_txt)
			{
				$checked = (in_array($profil_key,$tab_check)) ? ' checked="checked"' : '' ;
				$type = ($profil_key=='directeur') ? 'checkbox' : 'radio' ;
				echo'<td class="hc"><input type="'.$type.'" name="'.$objet_key.'" value="'.$profil_key.'"'.$checked.' /></td>';
			}
			echo'</tr>';
		}
		echo'</tbody>';
		?>
	</table>
	<p>
		<span class="tab"></span>
		<button id="initialiser_validation_socle" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les droits par défaut.</button>
		<button id="valider_validation_socle" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces droits.</button>
		<label id="ajax_msg_validation_socle">&nbsp;</label>
	</p>
</form>

<hr />

<h4>Profils autorisés à consulter tous les référentiels de l'établissement</h4>

<form id="form_voir_referentiels" action=""><fieldset>
	<p><span class="tab"></span>
	<?php
	$tab_options = array( 'directeur'=>'Directeurs' , 'professeur'=>'Professeurs' , 'eleve'=>'Élèves' );
	$tab_check = explode(',',$_SESSION['DROIT_VOIR_REFERENTIELS']);
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="droit_voir_referentiels" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	?>
	</p>
	<span class="tab"></span>
	<button id="initialiser_voir_referentiels" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les droits par défaut.</button>
	<button id="valider_voir_referentiels" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces droits.</button>
	<label id="ajax_msg_voir_referentiels">&nbsp;</label>
</fieldset></form>

<hr />

<h4>Profils autorisés à voir les scores bilan des items</h4>

<form id="form_voir_score_bilan" action=""><fieldset>
	<p><span class="tab"></span>
	<?php
	$tab_options = array( 'directeur'=>'Directeurs' , 'professeur'=>'Professeurs' , 'eleve'=>'Élèves' );
	$tab_check = explode(',',$_SESSION['DROIT_VOIR_SCORE_BILAN']);
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="droit_voir_score_bilan" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	?>
	</p>
	<span class="tab"></span>
	<button id="initialiser_voir_score_bilan" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les droits par défaut.</button>
	<button id="valider_voir_score_bilan" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces droits.</button>
	<label id="ajax_msg_voir_score_bilan">&nbsp;</label>
</fieldset></form>

<hr />

<h4>Profils autorisés à modifier leur mot de passe</h4>

<form id="form_modifier_mdp" action=""><fieldset>
	<p><span class="tab"></span>
	<?php
	$tab_options = array( 'directeur'=>'Directeurs' , 'professeur'=>'Professeurs' , 'eleve'=>'Élèves' );
	$tab_check = explode(',',$_SESSION['DROIT_MODIFIER_MDP']);
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="droit_modifier_mdp" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	?>
	</p>
	<span class="tab"></span>
	<button id="initialiser_modifier_mdp" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les droits par défaut.</button>
	<button id="valider_modifier_mdp" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces droits.</button>
	<label id="ajax_msg_modifier_mdp">&nbsp;</label>
</fieldset></form>

<hr />

<h4>Environnement élève - Bilan sur une matière</h4>

<form id="form_eleve_bilans" action=""><fieldset>
	<?php
	$tab_options = array(
		'BilanMoyenneScore'=>'Affichage de la ligne avec la moyenne des scores d\'acquisitions.' ,
		'BilanPourcentageAcquis'=>'Affichage de la ligne avec le pourcentage d\'items acquis.' ,
		'BilanNoteSurVingt'=>'Ajout de la conversion en note sur 20.'
	);
	$tab_check = explode(',',$_SESSION['DROIT_ELEVE_BILANS']);
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<p><label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="droit_eleve_bilans" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label></p>'."\r\n";
	}
	?>
	<span class="tab"></span>
	<button id="initialiser_eleve_bilans" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les options par défaut.</button>
	<button id="valider_eleve_bilans" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ces options.</button>
	<label id="ajax_msg_eleve_bilans">&nbsp;</label>
</fieldset></form>

<hr />

<h4>Environnement élève - Attestation de socle</h4>

<form id="form_eleve_socle" action=""><fieldset>
	<?php
	$tab_options = array(
		'SocleAcces'=>'Accès à l\'attestation listant les résultats des évaluations par item du socle.' ,
		'SoclePourcentageAcquis'=>'Affichage des cases avec le pourcentage d\'items acquis.',
		'SocleEtatValidation'=>'Affichage des états de validation saisis par les professeurs.'
	);
	$tab_check = explode(',',$_SESSION['DROIT_ELEVE_SOCLE']);
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<p><label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="droit_eleve_socle" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label></p>'."\r\n";
	}
	?>
	<span class="tab"></span>
	<button id="initialiser_eleve_socle" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les options par défaut.</button>
	<button id="valider_eleve_socle" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ces options.</button>
	<label id="ajax_msg_eleve_socle">&nbsp;</label>
</fieldset></form>

<hr />

