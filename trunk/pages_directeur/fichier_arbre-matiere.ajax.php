<?php
/**
 * @version $Id: fichier_arbre-matiere.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$matiere_id  = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$matiere_nom = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';

$dossier_export = './__tmp/export/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV de l'arborescence des items d'une matière d'un prof
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($matiere_id && $matiere_nom)
{
	// Préparation de l'export CSV
	$separateur = ';';
	// ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/215591/fr). 
	$export_csv  = 'MATIERE'.$separateur.'NIVEAU'.$separateur.'DOMAINE'.$separateur.'THEME'.$separateur.'ITEM'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<div id="zone_compet">';

	$tab_niveau     = array();
	$tab_domaine    = array();
	$tab_theme      = array();
	$tab_competence = array();
	$niveau_id = 0;
	$DB_TAB = select_arborescence_matiere($matiere_id);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		if($DB_ROW['livret_niveau_id']!=$niveau_id)
		{
			$niveau_id = $DB_ROW['livret_niveau_id'];
			$tab_niveau[$niveau_id] = $DB_ROW['livret_niveau_ref'].' - '.$DB_ROW['livret_niveau_nom'];
			$domaine_id    = 0;
			$theme_id      = 0;
			$competence_id = 0;
		}
		if( (!is_null($DB_ROW['livret_domaine_id'])) && ($DB_ROW['livret_domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['livret_domaine_id'];
			$tab_domaine[$niveau_id][$domaine_id] = $DB_ROW['livret_domaine_ref'].' - '.$DB_ROW['livret_domaine_nom'];
		}
		if( (!is_null($DB_ROW['livret_theme_id'])) && ($DB_ROW['livret_theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['livret_theme_id'];
			$tab_theme[$niveau_id][$domaine_id][$theme_id] = $DB_ROW['livret_domaine_ref'].$DB_ROW['livret_theme_ordre'].' - '.$DB_ROW['livret_theme_nom'];
		}
		if( (!is_null($DB_ROW['livret_competence_id'])) && ($DB_ROW['livret_competence_id']!=$competence_id) )
		{
			$competence_id = $DB_ROW['livret_competence_id'];
			$tab_competence[$niveau_id][$domaine_id][$theme_id][$competence_id] = $DB_ROW['livret_domaine_ref'].$DB_ROW['livret_theme_ordre'].$DB_ROW['livret_competence_ordre'].' - '.$DB_ROW['livret_competence_nom'];
		}
	}
	$export_csv .= $DB_ROW['livret_matiere_ref'].' - '.$matiere_nom."\r\n";
	$export_html .= '<ul class="ul_m1">'."\r\n";
	$export_html .= '	<li class="li_m1"><span>'.html($DB_ROW['livret_matiere_ref'].' - '.$matiere_nom).'</span>'."\r\n";
	$export_html .= '		<ul class="ul_m2">'."\r\n";
	foreach($tab_niveau as $niveau_id => $niveau_nom)
	{
		$export_csv .= $separateur.$niveau_nom."\r\n";
		$export_html .= '			<li class="li_m2"><span>'.html($niveau_nom).'</span>'."\r\n";
		$export_html .= '				<ul class="ul_n1">'."\r\n";
		if(isset($tab_domaine[$niveau_id]))
		{
			foreach($tab_domaine[$niveau_id] as $domaine_id => $domaine_nom)
			{
				$export_csv .= $separateur.$separateur.$domaine_nom."\r\n";
				$export_html .= '					<li class="li_n1"><span>'.html($domaine_nom).'</span>'."\r\n";
				$export_html .= '						<ul class="ul_n2">'."\r\n";
				if(isset($tab_theme[$niveau_id][$domaine_id]))
				{
					foreach($tab_theme[$niveau_id][$domaine_id] as $theme_id => $theme_nom)
					{
						$export_csv .= $separateur.$separateur.$separateur.$theme_nom."\r\n";
						$export_html .= '							<li class="li_n2"><span>'.html($theme_nom).'</span>'."\r\n";
						$export_html .= '								<ul class="ul_n3">'."\r\n";
						if(isset($tab_competence[$niveau_id][$domaine_id][$theme_id]))
						{
							foreach($tab_competence[$niveau_id][$domaine_id][$theme_id] as $competence_id => $competence_nom)
							{
								$export_csv .= $separateur.$separateur.$separateur.$separateur.'"'.$competence_nom.'"'."\r\n";
								$export_html .= '									<li class="li_n3">'.html($competence_nom).'</li>'."\r\n";
							}
						}
						$export_html .= '								</ul>'."\r\n";
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
	$fnom = 'export_'.$_SESSION['STRUCTURE_ID'].'_'.$_SESSION['USER_ID'].'_arbre-matiere_'.$matiere_id.'_'.time();
	$zip = new ZipArchive();
	if ($zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fnom.'.csv',csv($export_csv));
		$zip->close();
	}
	// Finalisation de l'export HTML
	$export_html.= '</div>';

	// Affichage
	echo'<ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip">Récupérez l\'arborescence dans un fichier au format CSV.</a></li></ul><p />';
	echo $export_html;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
