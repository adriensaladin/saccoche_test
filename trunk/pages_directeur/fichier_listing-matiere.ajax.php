<?php
/**
 * @version $Id: fichier_listing-matiere.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
//	Export CSV des données des items d'une matière d'un prof
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($matiere_id && $matiere_nom)
{
	// Préparation de l'export CSV
	$separateur = ';';
	// ajout du préfixe 'ITEM_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/215591/fr). 
	$export_csv  = 'ITEM_ID'.$separateur.'MATIERE'.$separateur.'NIVEAU'.$separateur.'REFERENCE'.$separateur.'NOM'."\r\n\r\n";
	// Préparation de l'export HTML
	$export_html = '<table><thead><tr><th>Id</th><th>Matière</th><th>Niveau</th><th>Référence</th><th>Nom</th></tr></thead><tbody>'."\r\n";

	$DB_SQL = 'SELECT * FROM livret_competence_item ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere_id ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_id'=>$matiere_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$item_ref = $DB_ROW['livret_matiere_ref'].'.'.$DB_ROW['livret_niveau_ref'].'.'.$DB_ROW['livret_domaine_ref'].$DB_ROW['livret_theme_ordre'].$DB_ROW['livret_competence_ordre'];
			$export_csv .= $DB_ROW['livret_competence_id'].$separateur.$matiere_nom.$separateur.$DB_ROW['livret_niveau_nom'].$separateur.$item_ref.$separateur.'"'.$DB_ROW['livret_competence_nom'].'"'."\r\n";
			$export_html .= '<tr><td>'.$DB_ROW['livret_competence_id'].'</td><td>'.html($matiere_nom).'</td><td>'.html($DB_ROW['livret_niveau_nom']).'</td><td>'.html($item_ref).'</td><td>'.html($DB_ROW['livret_competence_nom']).'</td></tr>'."\r\n";
		}
	}

	// Finalisation de l'export CSV (archivage dans un fichier zippé)
	$fnom = 'export_'.$_SESSION['STRUCTURE_ID'].'_'.$_SESSION['USER_ID'].'_listing-items_'.$matiere_id.'_'.time();
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
