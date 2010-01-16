<?php
/**
 * @version $Id: recherche_generale.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
		foreach($DB_TAB as $key => $DB_ROW)
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
	$DB_SQL = 'SELECT * FROM livret_competence_domaine ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id)';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_socle_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere AND livret_niveau_id=:niveau ';
	$DB_SQL.= 'ORDER BY livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$donneur_id,':matiere'=>$matiere_id,':niveau'=>$niveau_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$tab_domaine    = array();
	$tab_theme      = array();
	$tab_competence = array();
	$domaine_id = 0;
	foreach($DB_TAB as $key => $DB_ROW)
	{
		if( (!is_null($DB_ROW['livret_domaine_id'])) && ($DB_ROW['livret_domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['livret_domaine_id'];
			$tab_domaine[$domaine_id] = $DB_ROW['livret_domaine_nom'];
			$theme_id      = 0;
			$competence_id = 0;
		}
		if( (!is_null($DB_ROW['livret_theme_id'])) && ($DB_ROW['livret_theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['livret_theme_id'];
			$tab_theme[$domaine_id][$theme_id] = $DB_ROW['livret_theme_nom'];
		}
		if( (!is_null($DB_ROW['livret_competence_id'])) && ($DB_ROW['livret_competence_id']!=$competence_id) )
		{
			$competence_id = $DB_ROW['livret_competence_id'];
			$coef_texte    = '<img src="./_img/x'.$DB_ROW['livret_competence_coef'].'.gif" alt="" title="Coef '.$DB_ROW['livret_competence_coef'].'" />';
			$socle_image   = ($DB_ROW['livret_socle_id']==0) ? 'off' : 'on' ;
			$socle_nom     = ($DB_ROW['livret_socle_id']==0) ? 'Hors-socle.' : html($DB_ROW['livret_socle_nom']) ;
			$socle_texte   = '<img src="./_img/socle_'.$socle_image.'.png" alt="" title="'.$socle_nom.'" />';
			$lien_image    = ($DB_ROW['livret_competence_lien']=='') ? 'off' : 'on' ;
			$lien_nom      = ($DB_ROW['livret_competence_lien']=='') ? 'Absence de ressource.' : html($DB_ROW['livret_competence_lien']) ;
			$lien_texte    = '<img src="./_img/link_'.$lien_image.'.png" alt="" title="'.$lien_nom.'" />';
			$texte_lien_avant = ($DB_ROW['livret_competence_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['livret_competence_lien']).'">' : '';
			$texte_lien_apres = ($DB_ROW['livret_competence_lien']) ? '</a>' : '';
			$tab_competence[$domaine_id][$theme_id][$competence_id] = $coef_texte.$socle_texte.$lien_texte.$texte_lien_avant.html($DB_ROW['livret_competence_nom']).$texte_lien_apres;
		}
	}
	echo'<ul class="ul_n1">'."\r\n";
	if(count($tab_domaine))
	{
		foreach($tab_domaine as $domaine_id => $domaine_nom)
		{
			echo'	<li class="li_n1">'.html($domaine_nom)."\r\n";
			echo'		<ul class="ul_n2">'."\r\n";
			if(isset($tab_theme[$domaine_id]))
			{
				foreach($tab_theme[$domaine_id] as $theme_id => $theme_nom)
				{
					echo'			<li class="li_n2">'.html($theme_nom)."\r\n";
					echo'				<ul class="ul_n3">'."\r\n";
					if(isset($tab_competence[$domaine_id][$theme_id]))
					{
						foreach($tab_competence[$domaine_id][$theme_id] as $competence_id => $competence_texte)
						{
							echo'					<li class="li_n3"><b>'.$competence_texte.'</b></li>'."\r\n";
						}
					}
					echo'				</ul>'."\r\n";
					echo'			</li>'."\r\n";
				}
			}
			echo'		</ul>'."\r\n";
			echo'	</li>'."\r\n";
		}
	}
	echo'</ul>'."\r\n";
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
