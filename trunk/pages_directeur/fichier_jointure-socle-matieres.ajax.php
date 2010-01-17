<?php
/**
 * @version $Id: fichier_jointure-socle-matieres.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$dossier = './__tmp/export/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV de l'arborescence des items d'une matière d'un prof
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($palier_id && $palier_nom)
{
	// Préparation de l'export CSV
	$separateur = ';';
	$export_csv  = 'PALIER SOCLE'.$separateur.'PILIER SOCLE'.$separateur.'SECTION SOCLE'.$separateur.'ITEM SOCLE'.$separateur.'ITEM MATIERE'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<div id="zone_paliers">';

	// Récupération des données du socle
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
			$tab_section[$pilier_id][$section_id] = $DB_ROW['livret_section_nom'];
		}
		if( (!is_null($DB_ROW['livret_socle_id'])) && ($DB_ROW['livret_socle_id']!=$socle_id) )
		{
			$socle_id = $DB_ROW['livret_socle_id'];
			$tab_socle[$pilier_id][$section_id][$socle_id] = $DB_ROW['livret_socle_nom'];
		}
	}

	// Récupération des données des référentiels liés au socle
	$tab_jointure = array();
	$DB_SQL = 'SELECT livret_socle_id , livret_competence_nom , livret_matiere_ref , ';
	$DB_SQL.= 'CONCAT(LEFT(livret_niveau_ref,1),livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref ';
	$DB_SQL.= 'FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_matiere USING (livret_structure_id,livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_socle_id>0 ';
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_jointure[$DB_ROW['livret_socle_id']][] = $DB_ROW['livret_matiere_ref'].' - '.$DB_ROW['competence_ref'].' - '.$DB_ROW['livret_competence_nom'];
	}

	// Elaboration de la sortie
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
						$export_html .= '							<li class="li_n3">'.html($socle_nom)."\r\n";
						if(isset($tab_jointure[$socle_id]))
						{
							$export_html .= '								<ul class="ul_m2">'."\r\n";
							foreach($tab_jointure[$socle_id] as $key => $competence_descriptif)
							{
								$export_csv .= $separateur.$separateur.$separateur.$separateur.'"'.$competence_descriptif.'"'."\r\n";
								$export_html .= '									<li class="li_m2">'.html($competence_descriptif).'</li>'."\r\n";
							}
							$export_html .= '								</ul>'."\r\n";
						}
						$export_html .= '							</li>'."\r\n";
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
	$fnom = 'export_'.$_SESSION['STRUCTURE_ID'].'_'.$_SESSION['USER_ID'].'_jointure_'.$palier_id.'_'.time();
	$zip = new ZipArchive();
	if ($zip->open($dossier.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fnom.'.csv',csv($export_csv));
		$zip->close();
	}
	// Finalisation de l'export HTML
	$export_html.= '</div>';

	// Affichage
	echo'<ul class="puce"><li><a class="lien_ext" href="'.$dossier.$fnom.'.zip">Récupérez les associations dans un fichier au format CSV.</a></li></ul><p />';
	echo $export_html;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
