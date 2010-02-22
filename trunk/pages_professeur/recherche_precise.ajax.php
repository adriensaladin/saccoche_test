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
$etabl_id   = (isset($_POST['etabl']))   ? clean_entier($_POST['etabl'])   : 0;
$matiere_id = (isset($_POST['matiere'])) ? clean_entier($_POST['matiere']) : 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du détail d'un référentiel pour une matière et un établissement donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Voir') && $matiere_id && $etabl_id )
{
	// Affichage de la liste des items pour la matière et le niveau sélectionnés
	$DB_SQL = 'SELECT * FROM livret_competence_domaine ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_socle_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere_id AND livret_niveau_id IN('.$_SESSION['NIVEAUX'].') ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$etabl_id,':matiere_id'=>$matiere_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(!count($DB_TAB))
	{
		exit('Aucun référentiel partagé par cet établissement pour cette matière...');
	}
	$niveau_id = 0;
	$domaine_id = 0;
	$theme_id = 0;
	$competence_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if( (!is_null($DB_ROW['livret_niveau_id'])) && ($DB_ROW['livret_niveau_id']!=$niveau_id) )
		{
			// nouveau niveau
			$niveau_id = $DB_ROW['livret_niveau_id'];
			if($competence_id)
			{
				$competence_id = 0;
				echo'					</ul>'."\r\n";
				echo'				</li>'."\r\n";
			}
			if($theme_id)
			{
				$theme_id = 0;
				echo'			</ul>'."\r\n";
				echo'		</li>'."\r\n";
			}
			if($domaine_id)
			{
				$domaine_id = 0;
				echo'	</ul>'."\r\n";
				echo'</li>'."\r\n";
			}
			echo'<li class="li_m2"><span>'.html($DB_ROW['livret_niveau_nom']).'</span>'."\r\n";
			echo'	<ul class="ul_n1">'."\r\n";
		}
		if( (!is_null($DB_ROW['livret_domaine_id'])) && ($DB_ROW['livret_domaine_id']!=$domaine_id) )
		{
			// nouveau domaine
			$domaine_id = $DB_ROW['livret_domaine_id'];
			if($competence_id)
			{
				$competence_id = 0;
				echo'					</ul>'."\r\n";
				echo'				</li>'."\r\n";
			}
			if($theme_id)
			{
				$theme_id = 0;
				echo'			</ul>'."\r\n";
				echo'		</li>'."\r\n";
			}
			echo'		<li class="li_n1"><span>'.html($DB_ROW['livret_domaine_nom']).'</span>'."\r\n";
			echo'			<ul class="ul_n2">'."\r\n";
		}
		if( (!is_null($DB_ROW['livret_theme_id'])) && ($DB_ROW['livret_theme_id']!=$theme_id) )
		{
			// nouveau thème
			$theme_id = $DB_ROW['livret_theme_id'];
			if($competence_id)
			{
				$competence_id = 0;
				echo'					</ul>'."\r\n";
				echo'				</li>'."\r\n";
			}
			echo'				<li class="li_n2"><span>'.html($DB_ROW['livret_theme_nom']).'</span>'."\r\n";
			echo'					<ul class="ul_n3">'."\r\n";
		}
		if(!is_null($DB_ROW['livret_competence_id']))
		{
			// nouvel item
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
			echo'								<li class="li_n3"><b>'.$coef_texte.$socle_texte.$lien_texte.$texte_lien_avant.html($DB_ROW['livret_competence_nom']).$texte_lien_apres.'</b></li>'."\r\n";
		}
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
