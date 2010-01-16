<?php
/**
 * @version $Id: referentiel_view.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$ref = (isset($_POST['ref'])) ? $_POST['ref'] : '';

if(mb_substr_count($ref,'_')!=2)
{
	exit('Erreur avec les données transmises !');
}

list($action,$matiere_id,$niveau_id) = explode('_',$ref);
$matiere_id  = clean_entier($matiere_id);
$niveau_id   = clean_entier($niveau_id);

if( ($action=='Voir') && $matiere_id && $niveau_id )
{
	// Affichage du bilan de la liste des items pour la matière et le niveau sélectionnés
	$DB_SQL = 'SELECT * FROM livret_competence_domaine ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id)';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_socle_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere AND livret_niveau_id=:niveau ';
	$DB_SQL.= 'ORDER BY livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere'=>$matiere_id,':niveau'=>$niveau_id);
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
			$coef_texte    = '<img src="./_img/x'.$DB_ROW['livret_competence_coef'].'.gif" title="Coefficient '.$DB_ROW['livret_competence_coef'].'." /> ';
			$socle_image   = ($DB_ROW['livret_socle_id']==0) ? 'off' : 'on' ;
			$socle_nom     = ($DB_ROW['livret_socle_id']==0) ? 'Hors-socle.' : html($DB_ROW['livret_socle_nom']) ;
			$socle_texte   = '<img src="./_img/socle_'.$socle_image.'.png" title="'.$socle_nom.'" /> ';
			$lien_image    = ($DB_ROW['livret_competence_lien']=='') ? 'off' : 'on' ;
			$lien_nom      = ($DB_ROW['livret_competence_lien']=='') ? 'Absence de ressource.' : html($DB_ROW['livret_competence_lien']) ;
			$lien_texte    = '<img src="./_img/link_'.$lien_image.'.png" title="'.$lien_nom.'" /> ';
			$tab_competence[$domaine_id][$theme_id][$competence_id] = $coef_texte.$socle_texte.$lien_texte.html($DB_ROW['livret_competence_nom']);
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
							echo'					<li class="li_n3">'.$competence_texte.'</li>'."\r\n";
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
