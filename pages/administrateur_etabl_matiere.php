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
$TITRE = "Matières utilisées";
?>

<div class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_matieres">DOC : Matières utilisées</a></div>

<hr />

<h2>Matières partagées</h2>

<form id="partage" action="">
	<table class="form">
		<thead>
			<tr><th class="nu"></th><th>Référence</th><th>Nom complet</th></tr>
		</thead>
		<tbody>
			<?php
			// Cases à cocher
			$tab_check = explode(',',$_SESSION['MATIERES']);
			// Lister les matières partagées
			$DB_TAB = DB_STRUCTURE_lister_matieres_partagees_SACoche();
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				$checked  = (in_array($DB_ROW['matiere_id'],$tab_check)) ? ' checked="checked"' : '' ;
				$disabled = ($DB_ROW['matiere_transversal']) ? ' disabled="disabled"' : '' ;
				$tr_class = ($DB_ROW['matiere_transversal']) ? ' class="new"' : '' ;
				$td_label = ($DB_ROW['matiere_transversal']) ? '' : ' class="label"' ;
				$indic    = ($DB_ROW['matiere_transversal']) ? ' <b>[obligatoire]</b>' : '' ;
				echo'<tr'.$tr_class.'>';
				echo	'<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$DB_ROW['matiere_id'].'"'.$disabled.$checked.' /></td>';
				echo	'<td'.$td_label.'>'.html($DB_ROW['matiere_ref']).'</td>';
				echo	'<td'.$td_label.'>'.html($DB_ROW['matiere_nom']).$indic.'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
	<p>
		<span class="tab"></span><button id="bouton_valider" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ce choix de matières.</button><label id="ajax_msg_partage">&nbsp;</label>
	</p>
</form>

<hr />

<h2>Matières spécifiques</h2>

<form id="perso" action="">
	<table class="form">
		<thead>
			<tr>
				<th>Référence</th>
				<th>Nom complet</th>
				<th class="nu"><q class="ajouter" title="Ajouter une matière."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les matières spécifiques
			$DB_TAB = DB_STRUCTURE_lister_matieres_specifiques();
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['matiere_id'].'">';
				echo	'<td>'.html($DB_ROW['matiere_ref']).'</td>';
				echo	'<td>'.html($DB_ROW['matiere_nom']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier cette matière."></q>';
				echo		'<q class="supprimer" title="Supprimer cette matière."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>

<p>&nbsp;</p>
