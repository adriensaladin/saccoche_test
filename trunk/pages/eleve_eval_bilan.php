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
$TITRE = "Items et notes des évaluations";
$VERSION_JS_FILE += 2;
?>

<?php
// Fabrication des éléments select du formulaire
if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']!=1) )
{
	$class_form_eleve = 'show';
	$select_eleves = afficher_select($_SESSION['OPT_PARENT_ENFANTS'] , $select_nom=false , $option_first='oui' , $selection=false , $optgroup='non');
}
if( ($_SESSION['USER_PROFIL']=='parent') && ($_SESSION['NB_ENFANTS']==1) )
{
	$class_form_eleve = 'hide';
	$select_eleves = '<option value="'.$_SESSION['OPT_PARENT_ENFANTS'][0]['valeur'].'" selected>'.html($_SESSION['OPT_PARENT_ENFANTS'][0]['texte']).'</option>';
}
if($_SESSION['USER_PROFIL']=='eleve')
{
	$class_form_eleve = 'hide';
	$select_eleves = '<option value="'.$_SESSION['USER_ID'].'" selected>'.html($_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']).'</option>';
}
// Dates par défaut de début et de fin
$date_debut  = date("d/m/Y",mktime(0,0,0,date("m")-1,date("d"),date("Y"))); // 1 mois avant
$date_fin    = date("d/m/Y",mktime(0,0,0,date("m")+1,date("d"),date("Y"))); // 1 mois après
?>

<form action="" method="post" id="form"><fieldset>
	<div class="<?php echo $class_form_eleve ?>">
		<label class="tab" for="f_eleve">Élève(s) :</label><select id="f_eleve" name="f_eleve"><?php echo $select_eleves ?></select>
	</div>
	<label class="tab" for="f_periode">Période :</label>du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q> au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q><br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_evaluations" /><button id="actualiser" type="submit"><img alt="" src="./_img/bouton/actualiser.png" /> Actualiser l'affichage.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>


<form action="" method="post" id="zone_eval_choix" class="hide">
	<hr />
	<h2></h2>
	<table class="form">
		<thead>
			<tr>
				<th>Date</th>
				<th>Professeur</th>
				<th>Description</th>
				<th class="nu"></th>
			</tr>
		</thead>
		<tbody>
			<tr><td class="nu" colspan="4"></td></tr>
		</tbody>
	</table>
</form>

<div id="zone_eval_detail" class="vm_nug hide">
	<hr />
	<p id="titre_voir" class="ti b"></p>
	<table id="table_voir">
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
	<p />
</div>
