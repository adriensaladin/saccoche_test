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
//	Export CSV des données des items d'une matière d'un prof
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($matiere_id && $matiere_nom)
{
	// Préparation de l'export CSV
	$separateur = ';';
	// ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr). 
	$export_csv  = 'ITEM_ID'.$separateur.'MATIERE'.$separateur.'NIVEAU'.$separateur.'REFERENCE'.$separateur.'NOM'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<table><thead><tr><th>Id</th><th>Matière</th><th>Niveau</th><th>Référence</th><th>Nom</th></tr></thead><tbody>'."\r\n";

	$DB_TAB = DB_select_arborescence($prof_id=0,$matiere_id,$niveau_id=0,$only_item=true,$socle_nom=false);
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
	if ($zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fnom.'.csv',csv($export_csv));
		$zip->close();
	}
	// Finalisation de l'export HTML
	$export_html .= '</tbody></table>'."\r\n";

	// Affichage
	echo'<ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip">Récupérez le listing dans un fichier au format CSV.</a></li></ul><p />';
	echo $export_html;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
