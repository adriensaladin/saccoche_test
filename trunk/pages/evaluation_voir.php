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
$TITRE = "Liste des évaluations";
?>

<?php
// Fabrication des éléments select du formulaire
if($_SESSION['USER_PROFIL']=='directeur')
{
	$tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_classes_groupes_etabl();
	$of_g = 'oui'; $sel_g = false; $class_form_groupe = 'show'; $class_form_eleve = 'hide';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}
if($_SESSION['USER_PROFIL']=='professeur')
{
	$tab_groupes  = DB_STRUCTURE_COMMUN::DB_OPT_groupes_professeur($_SESSION['USER_ID']);
	$of_g = 'oui'; $sel_g = false; $class_form_groupe = 'show'; $class_form_eleve = 'hide';
	$select_eleves = '<option></option>'; // maj en ajax suivant le choix du groupe
}

if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
	$tab_groupes  = array();
	$of_g = 'non'; $sel_g = false; $class_form_groupe = 'hide'; $class_form_eleve = 'show';
	$select_eleves = Formulaire::afficher_select($_SESSION['OPT_PARENT_ENFANTS'] , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='non');
}
if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
	$tab_groupes  = array();
	$of_g = 'non'; $sel_g = false; $class_form_groupe = 'hide'; $class_form_eleve = 'hide';
	$select_eleves = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$tab_groupes  = array();
	$of_g = 'non'; $sel_g = false; $class_form_groupe = 'hide'; $class_form_eleve = 'hide';
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
}
$select_groupe = Formulaire::afficher_select($tab_groupes , $select_nom='f_groupe' , $option_first=$of_g , $selection=$sel_g , $optgroup='oui'); // optgroup à oui y compris pour les élèves (formulaire invisible) car recherche du type de groupe dans le js
// Dates par défaut de début et de fin
$date_debut = date("d/m/Y",mktime(0,0,0,date("m")-2,date("d"),date("Y"))); // 2 mois avant
$date_fin   = date("d/m/Y",mktime(0,0,0,date("m")+4,date("d"),date("Y"))); // 4 mois après

$bouton_valider_autoeval = ($_SESSION['USER_PROFIL']=='eleve') ? '<button id="Enregistrer_saisie" type="button" class="valider">Enregistrer les saisies</button>' : '<button type="button" class="valider" disabled>Réservé à l\'élève.</button>' ;
?>

<script type="text/javascript">
	var tab_dates = new Array();
</script>

<form action="#" method="post" id="form"><fieldset>
	<div class="<?php echo $class_form_groupe ?>">
		<label class="tab" for="f_groupe">Classe / groupe :</label><?php echo $select_groupe ?><label id="ajax_maj">&nbsp;</label>
	</div>
	<div class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_eleve">Élève :</label><select id="f_eleve" name="f_eleve"><?php echo $select_eleves ?></select>
	</div>
	<label class="tab">Période :</label>du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q> au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q><br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_evaluations" /><button id="actualiser" type="submit" class="actualiser">Actualiser l'affichage.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>


<form action="#" method="post" id="zone_eval_choix" class="hide">
	<hr />
	<h2></h2>
	<table class="form hsort">
		<thead>
			<tr>
				<th>Date</th>
				<th>Professeur</th>
				<th>Description</th>
				<th>Docs</th>
				<th class="nu"></th>
			</tr>
		</thead>
		<tbody>
			<tr><td class="nu" colspan="4"></td></tr>
		</tbody>
	</table>
</form>

<div id="zone_eval_voir" class="hide">
	<div id="titre_voir" class="ti b"></span></div>
	<p class="ti"><button id="fermer_zone_voir" type="button" class="retourner">Retour</button> <label id="msg_voir"></label></p>
	<table id="table_voir" class="hsort">
		<thead>
			<tr>
				<th>Ref.</th>
				<th>Nom de l'item</th>
				<th>Note à<br />ce devoir</th>
				<th>Score<br />cumulé</th>
			</tr>
		</thead>
		<tbody>
			<tr><td class="nu" colspan="4"></td></tr>
		</tbody>
	</table>
	<?php echo affich_legende_html($note_Lomer=TRUE,$etat_bilan=TRUE); ?>
</div>

<form action="#" method="post" id="zone_eval_saisir" class="hide" onsubmit="return false">
	<div id="titre_saisir" class="ti b"></div>
	<p>Auto-évaluation possible jusqu'au <span id="report_date" class="b"></span> (les notes peuvent ensuite être modifiées par le professeur).</p>
	<table id="table_saisir" class="vm_nug">
		<thead>
			<tr>
				<th colspan="5">Note</th>
				<th>Item</th>
			</tr>
		</thead>
		<tbody>
			<tr><td class="nu" colspan="6"></td></tr>
		</tbody>
	</table>
	<p class="ti"><?php echo $bouton_valider_autoeval ?><input type="hidden" name="f_devoir" id="f_devoir" value="" /> <button id="fermer_zone_saisir" type="button" class="retourner">Retour</button><label id="msg_saisir"></label></p>
	<?php echo affich_legende_html($note_Lomer=TRUE,$etat_bilan=FALSE); ?>
</form>
