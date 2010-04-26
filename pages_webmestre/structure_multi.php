<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$TITRE = "Établissements : Ajouter / Éditer / Retirer";

// Élément de formulaire "f_geo" pour le choix d'une zone géographique
$options_geo = '';
$DB_TAB = DB_lister_zones();
foreach($DB_TAB as $DB_ROW)
{
	$options_geo .= '<option value="'.$DB_ROW['geo_id'].'">'.html($DB_ROW['geo_nom']).'</option>';
}
?>

<script type="text/javascript">
	// <![CDATA[
	var options_geo="<?php echo str_replace('"','\"',$options_geo); ?>";
	// ]]>
</script>

<form action="">
	<table class="bilan_synthese">
		<thead>
			<tr>
				<th>Id</th>
				<th>Zone géo</th>
				<th>Structure Localisation</th>
				<th>Structure Dénomination</th>
				<th>n°UAI</th>
				<th>Contact Nom</th>
				<th>Contact Prénom</th>
				<th>Contact Courriel</th>
				<th class="nu"><q class="ajouter" title="Ajouter un établissement."></q></th>
			</tr>
		</thead>
		<tbody>
			<?php
			// Lister les structures
			$DB_TAB = DB_lister_structures();
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				echo'<tr id="id_'.$DB_ROW['sacoche_base'].'">';
				echo	'<td>'.$DB_ROW['sacoche_base'].'</td>';
				echo	'<td><i>'.sprintf("%02u",$DB_ROW['geo_ordre']).'</i>'.html($DB_ROW['geo_nom']).'</td>';
				echo	'<td>'.html($DB_ROW['structure_localisation']).'</td>';
				echo	'<td>'.html($DB_ROW['structure_denomination']).'</td>';
				echo	'<td>'.html($DB_ROW['structure_uai']).'</td>';
				echo	'<td>'.html($DB_ROW['contact_nom']).'</td>';
				echo	'<td>'.html($DB_ROW['contact_prenom']).'</td>';
				echo	'<td>'.html($DB_ROW['contact_courriel']).'</td>';
				echo	'<td class="nu">';
				echo		'<q class="modifier" title="Modifier cet établissement."></q>';
				echo		'<q class="supprimer" title="Supprimer cet établissement."></q>';
				echo	'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
</form>
<p />
