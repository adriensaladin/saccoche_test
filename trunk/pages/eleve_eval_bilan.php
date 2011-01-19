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
$TITRE = "Résultats aux évaluations";
$VERSION_JS_FILE += 1;
?>

<?php
// Dates par défaut de début et de fin
$date_debut  = date("d/m/Y",mktime(0,0,0,date("m")-1,date("d"),date("Y"))); // Il y a 1 mois
$date_fin    = date("d/m/Y");
?>

<form action="" id="form"><fieldset>
	<label class="tab" for="f_periode">Période :</label>du <input id="f_date_debut" name="f_date_debut" size="9" type="text" value="<?php echo $date_debut ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q> au <input id="f_date_fin" name="f_date_fin" size="9" type="text" value="<?php echo $date_fin ?>" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q><br />
	<span class="tab"></span><input type="hidden" name="f_action" value="Afficher_evaluations" /><button id="actualiser" type="submit"><img alt="" src="./_img/bouton/actualiser.png" /> Actualiser l'affichage.</button><label id="ajax_msg">&nbsp;</label>
</fieldset></form>


<form id="zone_eval_choix" class="hide" action="">
	<hr />
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

<div id="zone_eval_detail" class="comp_view hide">
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
	<p />
</div>
