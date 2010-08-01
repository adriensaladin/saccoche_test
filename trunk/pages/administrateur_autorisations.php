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
?>

<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_autorisations">DOC : Réglage des autorisations</a></div>
<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=environnement_generalites__demandes_evaluations">DOC : Demandes d'évaluations</a></div>

<hr />

<h4>Profils autorisés à valider le socle</h4>

<form id="form_profils" action="">
	<table>
	<?php
	$tab_profils = array( 'directeur'=>'directeurs' , 'professeur'=>'tous les professeurs' , 'profprincipal'=>'seulement les<br />professeurs principaux' , 'aucunprof'=>'aucun professeur' );
	$tab_objets  = array( 'profil_validation_entree'=>'validation des items du socle' , 'profil_validation_pilier'=>'validation des compétences du socle (ou piliers)' );
	// 1ère ligne
	echo'<thead><tr><th class="nu"></th>';
	foreach($tab_profils as $profil_key => $profil_txt)
	{
		echo'<th class="hc">'.$profil_txt.'</th>';
	}
	echo'</tr></thead>';
	// Les lignes avec checkbox
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
		<button id="initialiser_defaut" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Remettre les droits par défaut.</button>
		<button id="bouton_valider_profils" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Enregistrer ces droits.</button>
		<label id="ajax_msg_profils">&nbsp;</label>
	</p>
</form>

<hr />

<h4>Demandes d'évaluations des élèves</h4>

<?php
$options = '';
for($nb_demandes=0 ; $nb_demandes<10 ; $nb_demandes++)
{
	$selected = ($nb_demandes==$_SESSION['ELEVE_DEMANDES']) ? ' selected="selected"' : '' ;
	$texte = ($nb_demandes>0) ? ( ($nb_demandes>1) ? $nb_demandes.' demandes simultanées autorisées par matière' : '1 seule demande à la fois autorisée par matière' ) : 'Aucune demande autorisée (fonctionnalité desactivée).' ;
	$options .= '<option value="'.$nb_demandes.'"'.$selected.'>'.$texte.'</option>';
}
?>
<form id="form_demandes" action=""><fieldset>
	<label class="tab" for="f_demandes">Nombre maximal :</label><select id="f_demandes" name="f_demandes"><?php echo $options ?></select><br />
	<span class="tab"></span><button id="bouton_valider_demandes" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider cette valeur.</button><label id="ajax_msg_demandes">&nbsp;</label>
</fieldset></form>

<hr />

<h4>Options de l'environnement élève</h4>

<form id="form_options" action=""><fieldset>
	<?php
	$tab_options = array(
		'BilanMoyenneScore'=>'Bilan sur une matière : ligne avec la moyenne des scores d\'acquisitions.' ,
		'BilanPourcentageAcquis'=>'Bilan sur une matière : ligne avec le pourcentage d\'items acquis.' ,
		'SoclePourcentageAcquis'=>'Attestation de socle : cases avec le pourcentage d\'items acquis.',
		'SocleEtatValidation'=>'Attestation de socle : états de validation des items et des compétences.'
	);
	$tab_check = explode(',',$_SESSION['ELEVE_OPTIONS']);
	$i_id = 0;	// Pour donner des ids aux checkbox et radio
	foreach($tab_options as $option_code => $option_txt)
	{
		$i_id++;
		$checked = (in_array($option_code,$tab_check)) ? ' checked="checked"' : '' ;
		echo'<label for="input_'.$i_id.'"><input type="checkbox" id="input_'.$i_id.'" name="eleve_options" value="'.$option_code.'"'.$checked.' /> '.$option_txt.'</label><p />'."\r\n";
	}
	?>
	<span class="tab"></span><button id="bouton_valider_options" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ces options.</button><label id="ajax_msg_options">&nbsp;</label>
</fieldset></form>

<hr />


