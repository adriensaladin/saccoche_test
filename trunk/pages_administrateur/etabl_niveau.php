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
$TITRE = "Choix des niveaux";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__gestion_niveaux">DOC : Gestion des niveaux</a></span></p>

<hr />

<form id="niveau" action="">
	<table class="form check">
		<thead>
			<tr><th class="nu"></th><th>Codage</th><th>Dénomination</th></tr>
		</thead>
		<tbody>
			<?php
			// Cases à cocher
			$tab_check_niveaux = explode(',',$_SESSION['NIVEAUX']);
			$tab_check_paliers = explode(',',$_SESSION['PALIERS']);
			// Lister les niveaux
			$DB_TAB = DB_STRUCTURE_lister_niveaux_SACoche();
			foreach($DB_TAB as $DB_ROW)
			{
				$checked  = ( (in_array($DB_ROW['niveau_id'],$tab_check_niveaux)) || (in_array($DB_ROW['palier_id'],$tab_check_paliers)) ) ? ' checked="checked"' : '' ;
				$disabled = ($DB_ROW['palier_id']) ? ' disabled="disabled"' : '' ;
				$tr_class = ($DB_ROW['palier_id']) ? ' class="new"' : '' ;
				$td_label = ($DB_ROW['palier_id']) ? '' : ' class="label"' ;
				$indic    = ($DB_ROW['palier_id']) ? ' <b>[automatique]</b>' : '' ;
				echo'<tr'.$tr_class.'>';
				echo'	<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$DB_ROW['niveau_id'].'"'.$disabled.$checked.' /></td>';
				echo'	<td'.$td_label.'>'.html($DB_ROW['niveau_ref']).'</td>';
				echo'	<td'.$td_label.'>'.html($DB_ROW['niveau_nom']).$indic.'</td>';
				echo'</tr>';
			}
			?>
		</tbody>
	</table>
	<p>
		<input id="f_submit" type="button" value="Valider ce choix de niveaux." /> <label id="ajax_msg">&nbsp;</label>
	</p>
</form>
