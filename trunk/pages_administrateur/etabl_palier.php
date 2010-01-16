<?php
/**
 * @version $Id: etabl_palier.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "Choix des paliers du socle";
?>

<p class="hc"><span class="manuel"><a class="pop_up" href="./aide.php?fichier=gestion_paliers_socle">DOC : Gestion des paliers du socle</a></span></p>

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
			$DB_TAB = lister_paliers_SACoche();
			foreach($DB_TAB as $key => $DB_ROW)
			{
				// Afficher une ligne du tableau
				$checked = (in_array($DB_ROW['livret_palier_id'],$tab_check)) ? ' checked="checked"' : '' ;
				echo'<tr>';
				echo	'<td class="nu"><input type="checkbox" name="f_tab_id" value="'.$DB_ROW['livret_palier_id'].'"'.$checked.' /></td>';
				echo	'<td class="label">'.html($DB_ROW['livret_palier_nom']).'</td>';
				echo	'<td class="nu"><q class="voir" id="id_'.$DB_ROW['livret_palier_id'].'" title="Voir le détail de ce palier du socle."></q></td>';
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
	$DB_TAB = select_arborescence_palier();
	foreach($DB_TAB as $key => $DB_ROW)
	{
		if($DB_ROW['livret_palier_id']!=$palier_id)
		{
			$palier_id = $DB_ROW['livret_palier_id'];
			$tab_palier[$palier_id] = $DB_ROW['livret_palier_nom'];
			$pilier_id  = 0;
			$section_id = 0;
			$socle_id   = 0;
		}
		if( (!is_null($DB_ROW['livret_pilier_id'])) && ($DB_ROW['livret_pilier_id']!=$pilier_id) )
		{
			$pilier_id = $DB_ROW['livret_pilier_id'];
			$tab_pilier[$palier_id][$pilier_id] = $DB_ROW['livret_pilier_nom'];
		}
		if( (!is_null($DB_ROW['livret_section_id'])) && ($DB_ROW['livret_section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['livret_section_id'];
			$tab_section[$palier_id][$pilier_id][$section_id] = $DB_ROW['livret_section_nom'];
		}
		if( (!is_null($DB_ROW['livret_socle_id'])) && ($DB_ROW['livret_socle_id']!=$socle_id) )
		{
			$socle_id = $DB_ROW['livret_socle_id'];
			$tab_socle[$palier_id][$pilier_id][$section_id][$socle_id] = $DB_ROW['livret_socle_nom'];
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


