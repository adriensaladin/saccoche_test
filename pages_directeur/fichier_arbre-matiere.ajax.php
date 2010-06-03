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

$matiere_id  = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$matiere_nom = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';

$dossier_export = './__tmp/export/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV de l'arborescence des items d'une matière
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($matiere_id && $matiere_nom)
{
	// Préparation de l'export CSV
	$separateur = ';';
	// ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
	$export_csv  = 'MATIERE'.$separateur.'NIVEAU'.$separateur.'DOMAINE'.$separateur.'THEME'.$separateur.'ITEM'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<div id="zone_compet">';

	$tab_niveau  = array();
	$tab_domaine = array();
	$tab_theme   = array();
	$tab_item    = array();
	$niveau_id = 0;
	$DB_TAB = DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id=0,$only_item=false,$socle_nom=false);
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['niveau_id']!=$niveau_id)
		{
			$niveau_id = $DB_ROW['niveau_id'];
			$tab_niveau[$niveau_id] = $DB_ROW['niveau_ref'].' - '.$DB_ROW['niveau_nom'];
			$domaine_id = 0;
			$theme_id   = 0;
			$item_id    = 0;
		}
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$tab_domaine[$niveau_id][$domaine_id] = $DB_ROW['domaine_ref'].' - '.$DB_ROW['domaine_nom'];
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$tab_theme[$niveau_id][$domaine_id][$theme_id] = $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].' - '.$DB_ROW['theme_nom'];
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
		{
			$item_id = $DB_ROW['item_id'];
			$tab_item[$niveau_id][$domaine_id][$theme_id][$item_id] = $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'].' - '.$DB_ROW['item_nom'];
		}
	}
	$matiere = isset($DB_ROW['matiere_ref']) ? $DB_ROW['matiere_ref'].' - '.$matiere_nom : $matiere_nom ;
	$export_csv .= $matiere."\r\n";
	$export_html .= '<ul class="ul_m1">'."\r\n";
	$export_html .= '	<li class="li_m1"><span>'.html($matiere).'</span>'."\r\n";
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
						if(isset($tab_item[$niveau_id][$domaine_id][$theme_id]))
						{
							foreach($tab_item[$niveau_id][$domaine_id][$theme_id] as $item_id => $item_nom)
							{
								$export_csv .= $separateur.$separateur.$separateur.$separateur.'"'.$item_nom.'"'."\r\n";
								$export_html .= '									<li class="li_n3">'.html($item_nom).'</li>'."\r\n";
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
	$fnom = 'export_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_arbre-matiere_'.$matiere_id.'_'.time();
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
