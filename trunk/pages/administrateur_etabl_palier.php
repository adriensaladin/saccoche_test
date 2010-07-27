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
$TITRE = "Choix des paliers du socle";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_paliers_socle">DOC : Gestion des paliers du socle</a></span></p>

<hr />

<form id="socle" action="">
	<table class="form">
		<thead>
			<tr><th class="nu"></th><th>Palier</th><th class="nu">&nbsp;</th></tr>
		</thead>
		<tbody>
			<?php
			// Cases à cocher
			$tab_check = explode(',',$_SESSION['PALIERS']);
			// Lister les matières partagées
			$DB_TAB = DB_STRUCTURE_lister_paliers_SACoche();
			foreach($DB_TAB as $DB_ROW)
			{
				// Afficher une ligne du tableau
				$checked = (in_array($DB_ROW['palier_id'],$tab_check)) ? ' checked="checked"' : '' ;
				echo'<tr>';
				echo	'<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$DB_ROW['palier_id'].'"'.$checked.' /></td>';
				echo	'<td class="label">'.html($DB_ROW['palier_nom']).'</td>';
				echo	'<td class="nu"><q class="voir" id="id_'.$DB_ROW['palier_id'].'" title="Voir le détail de ce palier du socle."></q></td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
	<p>
		<span class="tab"></span><button id="bouton_valider" type="button"><img alt="" src="./_img/bouton/parametre.png" /> Valider ce choix de paliers.</button><label id="ajax_msg">&nbsp;</label>
	</p>
</form>

<hr />

<div id="zone_paliers">
	<?php
	// Affichage de la liste des items du socle pour chaque palier
	$DB_TAB = DB_STRUCTURE_recuperer_arborescence_palier($palier_id=false);
	echo str_replace( '<li class="li_m1"' , '<li class="li_m1 hide"' , afficher_arborescence_socle_from_SQL($DB_TAB,$dynamique=true,$reference=false,$aff_input=false,$ids=false) );
	?>
</div>


