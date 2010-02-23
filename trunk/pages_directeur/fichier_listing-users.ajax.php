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

$groupe_id   = (isset($_POST['f_groupe']))      ? clean_entier($_POST['f_groupe'])     : 0;
$groupe_type = (isset($_POST['f_groupe_type'])) ? clean_texte($_POST['f_groupe_type']) : '';
$groupe_nom  = (isset($_POST['f_groupe_nom']))  ? clean_texte($_POST['f_groupe_nom'])  : '';

$tab_types = array('Classes'=>'classe' , 'Groupes'=>'groupe' , 'Besoins'=>'groupe');

$dossier_export = './__tmp/export/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV des données des élèves d'un prof
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($groupe_id && isset($tab_types[$groupe_type]) && $groupe_nom)
{
	// Préparation de l'export CSV
	$separateur = ';';
	// ajout du préfixe 'ELEVE_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/215591/fr). 
	$export_csv  = 'ELEVE_ID'.$separateur.'LOGIN'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'GROUPE'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<table><thead><tr><th>Id</th><th>Login</th><th>Nom</th><th>Prénom</th><th>Groupe</th></tr></thead><tbody>'."\r\n";

	// $DB_TAB = DB_OPT_eleves_regroupement($_SESSION['STRUCTURE_ID'],$groupe_type,$groupe_id,$statut=1);
	// Non utilisé car ne renvoie pas le détail des informations
	$DB_SQL = 'SELECT * FROM livret_user ';
	if($groupe_type=='Classes')
	{
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:statut AND livret_eleve_classe_id=:groupe_id ';
	}
	else
	{
		$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_user_id) ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:statut AND livret_groupe_id=:groupe_id ';
	}
	$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':profil'=>'eleve',':statut'=>1,':groupe_id'=>$groupe_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$export_csv  .= $DB_ROW['livret_user_id'].$separateur.$DB_ROW['livret_user_login'].$separateur.$DB_ROW['livret_user_nom'].$separateur.$DB_ROW['livret_user_prenom'].$separateur.$groupe_nom."\r\n";
			$export_html .= '<tr><td>'.$DB_ROW['livret_user_id'].'</td><td>'.html($DB_ROW['livret_user_login']).'</td><td>'.html($DB_ROW['livret_user_nom']).'</td><td>'.html($DB_ROW['livret_user_prenom']).'</td><td>'.html($groupe_nom).'</td></tr>'."\r\n";
		}
	}

	// Finalisation de l'export CSV (archivage dans un fichier zippé)
	$fnom = 'export_'.$_SESSION['STRUCTURE_ID'].'_'.$_SESSION['USER_ID'].'_listing-eleves_'.$groupe_id.'_'.time();
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
