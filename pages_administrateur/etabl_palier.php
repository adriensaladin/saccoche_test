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
		<input id="f_submit" type="button" value="Valider ce choix de paliers." /> <label id="ajax_msg">&nbsp;</label>
	</p>
</form>

<hr />

<div id="zone_paliers">
	<?php
	// Affichage de la liste des items du socle pour chaque palier
	$tab_palier  = array();
	$tab_pilier  = array();
	$tab_section = array();
	$tab_socle   = array();
	$palier_id = 0;
	$affich_socle = '';
	$DB_TAB = DB_STRUCTURE_recuperer_arborescence_palier();
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['palier_id']!=$palier_id)
		{
			$palier_id = $DB_ROW['palier_id'];
			$tab_palier[$palier_id] = $DB_ROW['palier_nom'];
			$pilier_id  = 0;
			$section_id = 0;
			$socle_id   = 0;
		}
		if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
		{
			$pilier_id = $DB_ROW['pilier_id'];
			$tab_pilier[$palier_id][$pilier_id] = $DB_ROW['pilier_nom'];
		}
		if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['section_id'];
			$tab_section[$palier_id][$pilier_id][$section_id] = $DB_ROW['section_nom'];
		}
		if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
		{
			$socle_id = $DB_ROW['entree_id'];
			$tab_socle[$palier_id][$pilier_id][$section_id][$socle_id] = $DB_ROW['entree_nom'];
		}
	}
	$affich_socle .= '<ul class="ul_m1">'."\r\n";
	foreach($tab_palier as $palier_id => $palier_nom)
	{
		$affich_socle .= '	<li class="li_m1 hide" id="palier_'.$palier_id.'"><span>'.html($palier_nom).'</span>'."\r\n";
		$affich_socle .= '		<ul class="ul_n1">'."\r\n";
		if(isset($tab_pilier[$palier_id]))
		{
			foreach($tab_pilier[$palier_id] as $pilier_id => $pilier_nom)
			{
				$affich_socle .= '			<li class="li_n1"><span>'.html($pilier_nom).'</span>'."\r\n";
				$affich_socle .= '				<ul class="ul_n2">'."\r\n";
				if(count($tab_section[$palier_id][$pilier_id]))
				{
					foreach($tab_section[$palier_id][$pilier_id] as $section_id => $section_nom)
					{
						$affich_socle .= '					<li class="li_n2"><span>'.html($section_nom).'</span>'."\r\n";
						$affich_socle .= '						<ul class="ul_n3">'."\r\n";
						if(count($tab_socle[$palier_id][$pilier_id][$section_id]))
						{
							foreach($tab_socle[$palier_id][$pilier_id][$section_id] as $socle_id => $socle_nom)
							{
								$affich_socle .= '							<li class="li_n3">'.html($socle_nom).'</li>'."\r\n";
								
							}
						}
						$affich_socle .= '						</ul>'."\r\n";
						$affich_socle .= '					</li>'."\r\n";
					}
				}
				$affich_socle .= '				</ul>'."\r\n";
				$affich_socle .= '			</li>'."\r\n";
			}
		}
		$affich_socle .= '		</ul>'."\r\n";
		$affich_socle .= '	</li>'."\r\n";
	}
	$affich_socle .= '</ul>'."\r\n";
	echo $affich_socle;
	?>
</div>


