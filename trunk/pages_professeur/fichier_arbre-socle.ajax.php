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
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['pilier_id']!=$pilier_id)
		{
			$pilier_id = $DB_ROW['pilier_id'];
			$tab_pilier[$pilier_id] = $DB_ROW['pilier_nom'];
			$section_id = 0;
			$socle_id   = 0;
		}
		if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['section_id'];
			$tab_section[$pilier_id][$section_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].' - '.$DB_ROW['section_nom'];
		}
		if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
		{
			$socle_id = $DB_ROW['entree_id'];
			$tab_socle[$pilier_id][$section_id][$socle_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].'.'.$DB_ROW['entree_ordre'].' - '.$DB_ROW['entree_nom'];
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
	$fnom = 'export_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_arbre-socle_'.$palier_id.'_'.time();
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
