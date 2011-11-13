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

$type_export = (isset($_POST['f_type']))        ? clean_texte($_POST['f_type'])        : '';
$groupe_id   = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : '';
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';
$matiere_id  = (isset($_POST['f_matiere']))     ? clean_entier($_POST['f_matiere'])    : 0;
$matiere_nom = (isset($_POST['f_matiere_nom'])) ? clean_texte($_POST['f_matiere_nom']) : '';
$palier_id   = (isset($_POST['f_palier']))      ? clean_entier($_POST['f_palier'])     : 0;
$palier_nom  = (isset($_POST['f_palier_nom']))  ? clean_texte($_POST['f_palier_nom'])  : '';

$tab_types = array('Classes'=>'classe' , 'Groupes'=>'groupe' , 'Besoins'=>'groupe');

$dossier_export = './__tmp/export/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV des données des élèves d'une classe
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($type_export=='listing_users') && $groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom )
{
	// Préparation de l'export CSV
	$separateur = ';';
	// ajout du préfixe 'ELEVE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
	$export_csv  = 'ELEVE_ID'.$separateur.'LOGIN'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'GROUPE'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<table><thead><tr><th>Id</th><th>Login</th><th>Nom</th><th>Prénom</th><th>Groupe</th></tr></thead><tbody>'."\r\n";
	// Récupérer les élèves de la classe ou du groupe
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_actifs_regroupement('eleve',$tab_types[$groupe_type],$groupe_id,'user_id,user_login,user_nom,user_prenom');
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$export_csv  .= $DB_ROW['user_id'].$separateur.$DB_ROW['user_login'].$separateur.$DB_ROW['user_nom'].$separateur.$DB_ROW['user_prenom'].$separateur.$groupe_nom."\r\n";
			$export_html .= '<tr><td>'.$DB_ROW['user_id'].'</td><td>'.html($DB_ROW['user_login']).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td>'.html($groupe_nom).'</td></tr>'."\r\n";
		}
	}

	// Finalisation de l'export CSV (archivage dans un fichier zippé)
	$fnom = 'export_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_listing-eleves_'.$groupe_id.'_'.time();
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($export_csv));
	$zip->close();
	// Finalisation de l'export HTML
	$export_html .= '</tbody></table>'."\r\n";

	// Affichage
	echo'<hr /><ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip"><span class="file file_zip">Récupérez le listing dans un fichier au format <em>csv</em> zippé.</span></a></li></ul><p />';
	echo $export_html;
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV des données des items d'une matière
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($type_export=='listing_matiere') && $matiere_id && $matiere_nom )
{
	Formulaire::save_choix('export_fichier');
	// Préparation de l'export CSV
	$separateur = ';';
	// ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
	$export_csv  = 'ITEM_ID'.$separateur.'MATIERE'.$separateur.'NIVEAU'.$separateur.'REFERENCE'.$separateur.'NOM'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<table><thead><tr><th>Id</th><th>Matière</th><th>Niveau</th><th>Référence</th><th>Nom</th></tr></thead><tbody>'."\r\n";

	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id=0,$only_socle=false,$only_item=true,$socle_nom=false);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$item_ref = $DB_ROW['matiere_ref'].'.'.$DB_ROW['niveau_ref'].'.'.$DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'];
			$export_csv .= $DB_ROW['item_id'].$separateur.$matiere_nom.$separateur.$DB_ROW['niveau_nom'].$separateur.$item_ref.$separateur.'"'.$DB_ROW['item_nom'].'"'."\r\n";
			$export_html .= '<tr><td>'.$DB_ROW['item_id'].'</td><td>'.html($matiere_nom).'</td><td>'.html($DB_ROW['niveau_nom']).'</td><td>'.html($item_ref).'</td><td>'.html($DB_ROW['item_nom']).'</td></tr>'."\r\n";
		}
	}

	// Finalisation de l'export CSV (archivage dans un fichier zippé)
	$fnom = 'export_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_listing-items_'.$matiere_id.'_'.time();
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($export_csv));
	$zip->close();
	// Finalisation de l'export HTML
	$export_html .= '</tbody></table>'."\r\n";

	// Affichage
	echo'<hr /><ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip"><span class="file file_zip">Récupérez le listing dans un fichier au format <em>csv</em> zippé.</span></a></li></ul><p />';
	echo $export_html;
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV de l'arborescence des items d'une matière
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($type_export=='arbre_matiere') && $matiere_id && $matiere_nom )
{
	Formulaire::save_choix('matiere');
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
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id=0,$only_socle=false,$only_item=false,$socle_nom=false);
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
	$export_csv .= $DB_ROW['matiere_ref'].' - '.$matiere_nom."\r\n";
	$export_html .= '<ul class="ul_m1">'."\r\n";
	$export_html .= '	<li class="li_m1"><span>'.html($DB_ROW['matiere_ref'].' - '.$matiere_nom).'</span>'."\r\n";
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
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($export_csv));
	$zip->close();
	// Finalisation de l'export HTML
	$export_html.= '</div>';

	// Affichage
	echo'<hr /><ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip"><span class="file file_zip">Récupérez l\'arborescence dans un fichier au format <em>csv</em> zippé.</span></a></li></ul><p />';
	echo $export_html;
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV de l'arborescence du socle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($type_export=='arbre_socle') && $palier_id && $palier_nom )
{
	Formulaire::save_choix('palier');
	// Préparation de l'export CSV
	$separateur = ';';
	$export_csv  = 'PALIER'.$separateur.'PILIER'.$separateur.'SECTION'.$separateur.'ITEM'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<div id="zone_paliers">';

	$tab_pilier  = array();
	$tab_section = array();
	$tab_entree  = array();
	$pilier_id = 0;
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence_palier($palier_id);
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['pilier_id']!=$pilier_id)
		{
			$pilier_id = $DB_ROW['pilier_id'];
			$tab_pilier[$pilier_id] = $DB_ROW['pilier_nom'];
			$section_id = 0;
			$entree_id  = 0;
		}
		if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['section_id'];
			$tab_section[$pilier_id][$section_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].' - '.$DB_ROW['section_nom'];
		}
		if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$entree_id) )
		{
			$entree_id = $DB_ROW['entree_id'];
			$tab_entree[$pilier_id][$section_id][$entree_id] = $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].'.'.$DB_ROW['entree_ordre'].' - '.$DB_ROW['entree_nom'];
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
				if(isset($tab_entree[$pilier_id][$section_id]))
				{
					foreach($tab_entree[$pilier_id][$section_id] as $entree_id => $socle_nom)
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
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($export_csv));
	$zip->close();
	// Finalisation de l'export HTML
	$export_html.= '</div>';

	// Affichage
	echo'<hr /><ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip"><span class="file file_zip">Récupérez l\'arborescence dans un fichier au format <em>csv</em> zippé.</span></a></li></ul><p />';
	echo $export_html;
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV des liens des matières rattachés aux liens du socle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($type_export=='jointure_socle_matiere') && $palier_id && $palier_nom )
{
	Formulaire::save_choix('palier');
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
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence_palier($palier_id);
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

	// Récupération des données des référentiels liés au socle
	$tab_jointure = array();
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_recuperer_associations_entrees_socle();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_jointure[$DB_ROW['entree_id']][] = $DB_ROW['matiere_ref'].'.'.$DB_ROW['niveau_ref'].'.'.$DB_ROW['item_ref'].' - '.$DB_ROW['item_nom'];
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
							foreach($tab_jointure[$socle_id] as $item_descriptif)
							{
								$export_csv .= $separateur.$separateur.$separateur.$separateur.'"'.$item_descriptif.'"'."\r\n";
								$export_html .= '									<li class="li_m2">'.html($item_descriptif).'</li>'."\r\n";
							}
							$export_html .= '								</ul>'."\r\n";
						}
						else
						{
							$export_csv .= $separateur.$separateur.$separateur.$separateur.'"AUCUN ITEM ASSOCIÉ"'."\r\n";
							$export_html .= '									<br /><label class="alerte"><span style="background-color:#EE7">Aucun item associé.</span></label>'."\r\n";
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
	$fnom = 'export_'.$_SESSION['BASE'].'_'.$_SESSION['USER_ID'].'_jointure_'.$palier_id.'_'.time();
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($export_csv));
	$zip->close();
	// Finalisation de l'export HTML
	$export_html.= '</div>';

	// Affichage
	echo'<hr /><ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip"><span class="file file_zip">Récupérez les associations dans un fichier au format <em>csv</em> zippé.</span></a></li></ul><p />';
	echo $export_html;
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas arriver jusque là.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
