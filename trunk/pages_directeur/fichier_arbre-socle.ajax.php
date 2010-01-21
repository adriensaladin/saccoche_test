<?php
/**
 * @version $Id: fichier_arbre-socle.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$palier_id  = (isset($_POST['f_palier']))     ? clean_entier($_POST['f_palier'])    : 0;
$palier_nom = (isset($_POST['f_palier_nom'])) ? clean_texte($_POST['f_palier_nom']) : '';

$dossier_export = './__tmp/export/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV de l'arborescence des items d'une matière d'un prof
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($palier_id && $palier_nom)
{
	// Préparation de l'export CSV
	$separateur = ';';
	$export_csv  = 'PALIER'.$separateur.'PILIER'.$separateur.'SECTION'.$separateur.'ITEM'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<div id="zone_paliers">';

	$tab_pilier  = array();
	$tab_section = array();
	$tab_socle   = array();
	$pilier_id = 0;
	$DB_TAB = DB_select_arborescence_palier($palier_id);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		if($DB_ROW['livret_pilier_id']!=$pilier_id)
		{
			$pilier_id = $DB_ROW['livret_pilier_id'];
			$tab_pilier[$pilier_id] = $DB_ROW['livret_pilier_nom'];
			$section_id = 0;
			$socle_id   = 0;
		}
		if( (!is_null($DB_ROW['livret_section_id'])) && ($DB_ROW['livret_section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['livret_section_id'];
			$tab_section[$pilier_id][$section_id] = $DB_ROW['livret_pilier_ref'].'.'.$DB_ROW['livret_section_ordre'].' - '.$DB_ROW['livret_section_nom'];
		}
		if( (!is_null($DB_ROW['livret_socle_id'])) && ($DB_ROW['livret_socle_id']!=$socle_id) )
		{
			$socle_id = $DB_ROW['livret_socle_id'];
			$tab_socle[$pilier_id][$section_id][$socle_id] = $DB_ROW['livret_pilier_ref'].'.'.$DB_ROW['livret_section_ordre'].'.'.$DB_ROW['livret_socle_ordre'].' - '.$DB_ROW['livret_socle_nom'];
		}
	}
	$export_csv .= $palier_nom."\r\n";
	$export_html .= '<ul class="ul_m1">'."\r\n";
	$export_html .= '	<li class="li_m1"><span>'.html($palier_nom).'</span>'."\r\n";
	$export_html .= '		<ul class="ul_n1">'."\r\n";
	foreach($tab_pilier as $pilier_id => $pilier_nom)
	{
		$export_csv .= $separateur.$pilier_nom."\r\n";
		$export_html .= '			<li class="li_n1"><span>'.html($pilier_nom).'</span>'."\r\n";
		$export_html .= '				<ul class="ul_n2">'."\r\n";
		if(isset($tab_section[$pilier_id]))
		{
			foreach($tab_section[$pilier_id] as $section_id => $section_nom)
			{
				$export_csv .= $separateur.$separateur.$section_nom."\r\n";
				$export_html .= '					<li class="li_n2"><span>'.html($section_nom).'</span>'."\r\n";
				$export_html .= '						<ul class="ul_n3">'."\r\n";
				if(isset($tab_socle[$pilier_id][$section_id]))
				{
					foreach($tab_socle[$pilier_id][$section_id] as $socle_id => $socle_nom)
					{
						$export_csv .= $separateur.$separateur.$separateur.'"'.$socle_nom.'"'."\r\n";
						$export_html .= '							<li class="li_n3">'.html($socle_nom).'</li>'."\r\n";
					}
				}
				$export_html .= '						</ul>'."\r\n";
				$export_html .= '					</li>'."\r\n";
			}
		}
		$export_html .= '				</ul>'."\r\n";
		$export_html .= '			</li>'."\r\n";
	}
	$export_html .= '		</ul>'."\r\n";
	$export_html .= '	</li>'."\r\n";
	$export_html .= '</ul>'."\r\n";

	// Finalisation de l'export CSV (archivage dans un fichier zippé)
	$fnom = 'export_'.$_SESSION['STRUCTURE_ID'].'_'.$_SESSION['USER_ID'].'_arbre-socle_'.$palier_id.'_'.time();
	$zip = new ZipArchive();
	if ($zip->open($dossier.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fnom.'.csv',csv($export_csv));
		$zip->close();
	}
	// Finalisation de l'export HTML
	$export_html.= '</div>';

	// Affichage
	echo'<ul class="puce"><li><a class="lien_ext" href="'.$dossier.$fnom.'.zip">Récupérez l\'arborescence dans un fichier au format CSV.</a></li></ul><p />';
	echo $export_html;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
