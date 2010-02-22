<?php
/**
 * @version $Id$
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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {}

$action     = (isset($_POST['action']))  ? $_POST['action']                : '';
$matiere_id = (isset($_POST['matiere'])) ? clean_entier($_POST['matiere']) : 0;
$niveau_id  = (isset($_POST['niveau']))  ? clean_entier($_POST['niveau'])  : 0;
$donneur    = (isset($_POST['donneur'])) ? clean_texte($_POST['donneur']) : '';

if(mb_substr_count($donneur,'_')==1)
{
	list($prefixe,$donneur_id) = explode('_',$donneur);
	$donneur_id = clean_entier($donneur_id);
}
else
{
	$donneur_id = 0;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Lister les établissements partageant leur référentiel pour une matière et un niveau donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Lister') && $matiere_id && $niveau_id )
{
	$DB_SQL = 'SELECT * FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_structure USING (livret_structure_id) ';
	$DB_SQL.= 'WHERE livret_structure_id!='.ID_DEMO.' AND livret_matiere_id=:matiere_id AND livret_niveau_id=:niveau_id AND livret_referentiel_partage=:partage ';
	$DB_SQL.= 'ORDER BY livret_referentiel_succes DESC, geo_continent_ordre ASC, geo_pays_nom ASC, geo_departement_numero ASC, geo_commune_nom ASC';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':partage'=>'oui');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$texte = ($DB_ROW['geo_continent_ordre']>2) ? $DB_ROW['geo_pays_nom'].' | ' : $DB_ROW['geo_departement_numero'].' '.$DB_ROW['geo_departement_nom'].' | ';
			$texte.= $DB_ROW['geo_commune_nom'].' | ';
			$texte.= $DB_ROW['structure_type_ref'].' '.$DB_ROW['structure_nom'];
			echo'<li id="etabl_'.$DB_ROW['livret_structure_id'].'">'.$texte.' <img alt="star" src="./_img/star_bullet.png" /><sup>'.$DB_ROW['livret_referentiel_succes'].'</sup><q class="voir" title="Voir le détail de ce référentiel."></q></li>'."\r\n";
		}
	}
	else
	{
		echo'<li>Aucun référentiel partagé pour cette matière et ce niveau... Soyez le premier à créer et partager le votre !</li>'."\r\n";
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du détail d'un référentiel pour une matière et un niveau donnés (pour son bahut ou un bahut donneur)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Voir') && $matiere_id && $niveau_id && $donneur_id )
{
	$DB_TAB = DB_select_arborescence($donneur_id,$prof_id=0,$matiere_id,$niveau_id,$socle_nom=true);
	echo afficher_arborescence($DB_TAB,$dynamique=false,$reference=false,$aff_coef='image',$aff_socle='image',$aff_lien='click',$aff_input=false);
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
