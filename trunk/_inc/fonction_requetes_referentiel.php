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

/**
 * DB_select_arborescence
 * Retourner l'arborescence d'un référentiel (tableau issu de la requête SQL)
 * + pour une matière donnée / pour toutes les matières d'un professeur donné
 * + pour un niveau donné / pour tous les niveaux concernés
 * 
 * @param int  $structure_id
 * @param int  $prof_id      passer 0 pour une recherche sur une matière plutôt que sur toutes les matières d'un prof
 * @param int  $matiere_id   passer 0 pour une recherche sur toutes les matières d'un prof plutôt que sur une matière
 * @param int  $niveau_id    passer 0 pour une recherche sur tous les niveaux
 * @param bool $socle_nom    avec ou pas le nom des items du socle associés
 * @return array
 */

function DB_select_arborescence($structure_id,$prof_id,$matiere_id,$niveau_id,$socle_nom)
{
	$select_socle_nom  = ($socle_nom)  ? 'livret_socle_id,livret_socle_nom ' : 'livret_socle_id ' ;
	$join_user_matiere = ($prof_id)    ? 'LEFT JOIN livret_jointure_user_matiere USING (livret_structure_id,livret_matiere_id) ' : '' ;
	$join_socle_item   = ($socle_nom)  ? 'LEFT JOIN livret_socle_item USING (livret_socle_id) ' : '' ;
	$where_user        = ($prof_id)    ? 'AND livret_user_id=:user_id ' : '' ;
	$where_matiere     = ($matiere_id) ? 'AND livret_matiere_id=:matiere_id ' : '' ;
	$where_niveau      = ($niveau_id)  ? 'AND livret_niveau_id=:niveau_id ' : '' ;
	$order_matiere     = ($prof_id)    ? 'livret_matiere_nom ASC, ' : '' ;
	$order_niveau      = (!$niveau_id) ? 'livret_niveau_ordre ASC, ' : '' ;
	$DB_SQL = 'SELECT ';
	$DB_SQL.= 'livret_matiere_id, livret_matiere_ref, livret_matiere_nom, ';
	$DB_SQL.= 'livret_niveau_id, livret_niveau_ref, livret_niveau_nom, ';
	$DB_SQL.= 'livret_domaine_id, livret_domaine_ref, livret_domaine_nom, livret_domaine_ordre, ';
	$DB_SQL.= 'livret_theme_id, livret_theme_ordre, livret_theme_nom, ';
	$DB_SQL.= 'livret_competence_id, livret_competence_ordre, livret_competence_nom, livret_competence_coef, livret_competence_lien, ';
	$DB_SQL.= $select_socle_nom;
	$DB_SQL.= 'FROM livret_referentiel ';
	$DB_SQL.= $join_user_matiere;
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= $join_socle_item;
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id '.$where_user.$where_matiere.$where_niveau;
	$DB_SQL.= 'ORDER BY '.$order_matiere.$order_niveau.'livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$structure_id);
	if($prof_id)    {$DB_VAR[':user_id']    = $prof_id;}
	if($matiere_id) {$DB_VAR[':matiere_id'] = $matiere_id;}
	if($niveau_id)  {$DB_VAR[':niveau_id']  = $niveau_id;}
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * afficher_arborescence
 * Retourner une liste ordonnée à afficher à partir d'une requête SQL transmise.
 * 
 * @param tab  $DB_TAB
 * @param bool        $dynamique   arborescence cliquable ou pas (plier/replier)
 * @param bool        $reference   afficher ou pas les références
 * @param bool|string $aff_coef    false | 'texte' | 'image' : affichage des coefficients des items
 * @param bool|string $aff_socle   false | 'texte' | 'image' : affichage de la liaison au socle
 * @param bool|string $aff_lien    false | 'image' | 'click' : affichage des ressources de remédiation
 * @param bool        $aff_input   affichage ou pas des input checkbox avec label
 * @return string
 */

function afficher_arborescence($DB_TAB,$dynamique,$reference,$aff_coef,$aff_socle,$aff_lien,$aff_input)
{
	$input_texte = '';
	$coef_texte  = '';
	$socle_texte = '';
	$lien_texte  = '';
	$lien_texte_avant = '';
	$lien_texte_apres = '';
	$label_texte_avant = '';
	$label_texte_apres = '';
	// Traiter le retour SQL : on remplit les tableaux suivants.
	$tab_matiere    = array();
	$tab_niveau     = array();
	$tab_domaine    = array();
	$tab_theme      = array();
	$tab_competence = array();
	$matiere_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['livret_matiere_id']!=$matiere_id)
		{
			$matiere_id = $DB_ROW['livret_matiere_id'];
			$tab_matiere[$matiere_id] = ($reference) ? $DB_ROW['livret_matiere_ref'].' - '.$DB_ROW['livret_matiere_nom'] : $DB_ROW['livret_matiere_nom'] ;
			$niveau_id     = 0;
			$domaine_id    = 0;
			$theme_id      = 0;
			$competence_id = 0;
		}
		if( (!is_null($DB_ROW['livret_niveau_id'])) && ($DB_ROW['livret_niveau_id']!=$niveau_id) )
		{
			$niveau_id = $DB_ROW['livret_niveau_id'];
			$tab_niveau[$matiere_id][$niveau_id] = ($reference) ? $DB_ROW['livret_niveau_ref'].' - '.$DB_ROW['livret_niveau_nom'] : $DB_ROW['livret_niveau_nom'];
		}
		if( (!is_null($DB_ROW['livret_domaine_id'])) && ($DB_ROW['livret_domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['livret_domaine_id'];
			$tab_domaine[$matiere_id][$niveau_id][$domaine_id] = ($reference) ? $DB_ROW['livret_domaine_ref'].' - '.$DB_ROW['livret_domaine_nom'] : $DB_ROW['livret_domaine_nom'];
		}
		if( (!is_null($DB_ROW['livret_theme_id'])) && ($DB_ROW['livret_theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['livret_theme_id'];
			$tab_theme[$matiere_id][$niveau_id][$domaine_id][$theme_id] = ($reference) ? $DB_ROW['livret_domaine_ref'].$DB_ROW['livret_theme_ordre'].' - '.$DB_ROW['livret_theme_nom'] : $DB_ROW['livret_theme_nom'] ;
		}
		if( (!is_null($DB_ROW['livret_competence_id'])) && ($DB_ROW['livret_competence_id']!=$competence_id) )
		{
			$competence_id = $DB_ROW['livret_competence_id'];
			switch($aff_coef)
			{
				case 'texte' :	$coef_texte = '['.$DB_ROW['livret_competence_coef'].'] ';
												break;
				case 'image' :	$coef_texte = '<img src="./_img/x'.$DB_ROW['livret_competence_coef'].'.gif" title="Coefficient '.$DB_ROW['livret_competence_coef'].'." /> ';
			}
			switch($aff_socle)
			{
				case 'texte' :	$socle_texte = ($DB_ROW['livret_socle_id']) ? '[S] ' : '[–] ';
												break;
				case 'image' :	$socle_image = ($DB_ROW['livret_socle_id']) ? 'on' : 'off' ;
												$socle_nom   = ($DB_ROW['livret_socle_id']) ? html($DB_ROW['livret_socle_nom']) : 'Hors-socle.' ;
												$socle_texte = '<img src="./_img/socle_'.$socle_image.'.png" title="'.$socle_nom.'" /> ';
			}
			switch($aff_lien)
			{
				case 'click' :	$lien_texte_avant = ($DB_ROW['livret_competence_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['livret_competence_lien']).'">' : '';
												$lien_texte_apres = ($DB_ROW['livret_competence_lien']) ? '</a>' : '';
				case 'image' :	$lien_image = ($DB_ROW['livret_competence_lien']) ? 'on' : 'off' ;
												$lien_nom   = ($DB_ROW['livret_competence_lien']) ? html($DB_ROW['livret_competence_lien']) : 'Absence de ressource.' ;
												$lien_texte = '<img src="./_img/link_'.$lien_image.'.png" title="'.$lien_nom.'" /> ';
			}
			if($aff_input)
			{
				$input_texte = '<input id="id_'.$competence_id.'" name="f_competences[]" type="checkbox" value="'.$competence_id.'" /> ';
				$label_texte_avant = '<label for="id_'.$competence_id.'">';
				$label_texte_apres = '</label>';
			}
			$competence_texte = ($reference) ? $DB_ROW['livret_domaine_ref'].$DB_ROW['livret_theme_ordre'].$DB_ROW['livret_competence_ordre'].' - '.$DB_ROW['livret_competence_nom'] : $DB_ROW['livret_competence_nom'] ;
			$tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id][$competence_id] = $input_texte.$label_texte_avant.$coef_texte.$socle_texte.$lien_texte.$lien_texte_avant.html($competence_texte).$lien_texte_apres.$label_texte_apres;
		}
	}
	// Affichage de l'arborescence
	$span_avant = ($dynamique) ? '<span>' : '' ;
	$span_apres = ($dynamique) ? '</span>' : '' ;
	$retour = '<ul class="ul_m1">'."\r\n";
	if(count($tab_matiere))
	{
		foreach($tab_matiere as $matiere_id => $matiere_texte)
		{
			$retour .= '<li class="li_m1">'.$span_avant.html($matiere_texte).$span_apres."\r\n";
			$retour .= '<ul class="ul_m2">'."\r\n";
			if(isset($tab_niveau[$matiere_id]))
			{
				foreach($tab_niveau[$matiere_id] as $niveau_id => $niveau_texte)
				{
					$retour .= '<li class="li_m2">'.$span_avant.html($niveau_texte).$span_apres."\r\n";
					$retour .= '<ul class="ul_n1">'."\r\n";
					if(isset($tab_domaine[$matiere_id][$niveau_id]))
					{
						foreach($tab_domaine[$matiere_id][$niveau_id] as $domaine_id => $domaine_texte)
						{
							$retour .= '<li class="li_n1">'.$span_avant.html($domaine_texte).$span_apres."\r\n";
							$retour .= '<ul class="ul_n2">'."\r\n";
							if(isset($tab_theme[$matiere_id][$niveau_id][$domaine_id]))
							{
								foreach($tab_theme[$matiere_id][$niveau_id][$domaine_id] as $theme_id => $theme_texte)
								{
									$retour .= '<li class="li_n2">'.$span_avant.html($theme_texte).$span_apres."\r\n";
									$retour .= '<ul class="ul_n3">'."\r\n";
									if(isset($tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id]))
									{
										foreach($tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id] as $competence_id => $competence_texte)
										{
											$retour .= '<li class="li_n3">'.$competence_texte.'</li>'."\r\n";
										}
									}
									$retour .= '</ul>'."\r\n";
									$retour .= '</li>'."\r\n";
								}
							}
							$retour .= '</ul>'."\r\n";
							$retour .= '</li>'."\r\n";
						}
					}
					$retour .= '</ul>'."\r\n";
					$retour .= '</li>'."\r\n";
				}
			}
			$retour .= '</ul>'."\r\n";
			$retour .= '</li>'."\r\n";
		}
	}
	$retour .= '</ul>'."\r\n";
	return $retour;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés par un élève pour la matière selectionnée, durant la période choisie
//	[./pages_eleve/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_eleve_periode_matiere($eleve_id,$matiere_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien , ';
	$DB_SQL.= 'livret_referentiel_calcul_methode AS calcul_methode , livret_referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_referentiel USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id=:eleve_id AND livret_matiere_id=:matiere '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':eleve_id'=>$eleve_id,':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés par des élèves selectionnés, pour la matière selectionnée, durant la période choisie
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_eleves_periode_matiere($liste_eleve_id,$matiere_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien , ';
	$DB_SQL.= 'livret_referentiel_calcul_methode AS calcul_methode , livret_referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_referentiel USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_matiere_id=:matiere '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés et des matières concernées par des élèves selectionnés, durant la période choisie
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_et_matieres_eleves_periode($liste_eleve_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien , ';
	$DB_SQL.= 'livret_matiere_id , livret_matiere_nom , ';
	$DB_SQL.= 'livret_referentiel_calcul_methode AS calcul_methode , livret_referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_referentiel USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY livret_competence_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$tab_matiere = array();
	foreach($DB_TAB as $competence_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			$tab_matiere[$DB_ROW['livret_matiere_id']] = $DB_ROW['livret_matiere_nom'];
			unset($DB_TAB[$competence_id][$key]['livret_matiere_id'],$DB_TAB[$competence_id][$key]['livret_matiere_nom']);
		}
	}
	return array($DB_TAB,$tab_matiere);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés et des matières concernées par des élèves selectionnés, pour les items choisis !
//	[./pages_professeur/releve_selection.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_et_matieres_eleves_competence($liste_eleve_id,$liste_compet_id)
{
	$DB_SQL = 'SELECT livret_competence_id , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_competence_nom AS competence_nom , livret_competence_coef AS competence_coef , livret_socle_id AS competence_socle , livret_competence_lien AS competence_lien , ';
	$DB_SQL.= 'livret_matiere_id , livret_matiere_nom , ';
	$DB_SQL.= 'livret_referentiel_calcul_methode AS calcul_methode , livret_referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_referentiel USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_competence_id IN('.$liste_compet_id.') ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$tab_matiere = array();
	foreach($DB_TAB as $competence_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			unset($DB_TAB[$competence_id][$key]['livret_matiere_id'],$DB_TAB[$competence_id][$key]['livret_matiere_nom']);
		}
		$tab_matiere[$DB_ROW['livret_matiere_id']] = $DB_ROW['livret_matiere_nom'];
	}
	return array($DB_TAB,$tab_matiere);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour un élève donné, pour des items donnés, sur une période donnée
//	[./pages_eleve/grille_niveau.ajax.php] [./pages_eleve/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleve($eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_saisie_note AS note , livret_saisie_date AS date , livret_devoir_info AS info ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_devoir USING (livret_structure_id,livret_devoir_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id=:eleve_id AND livret_competence_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_saisie_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':eleve_id'=>$_SESSION['USER_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items donnés d'une matiere donnée, sur une période donnée
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_matiere($liste_eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_eleve_id AS eleve_id , livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_saisie_note AS note , livret_saisie_date AS date , livret_devoir_info AS info ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_devoir USING (livret_structure_id,livret_devoir_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_competence_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_saisie_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items donnés de plusieurs matieres, sur une période donnée
//	[./pages_professeur/bilan_pp_indiv.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_matieres($liste_eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_eleve_id AS eleve_id , livret_matiere_id AS matiere_id , livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_saisie_note AS note , livret_saisie_date AS date , livret_devoir_info AS info ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_devoir USING (livret_structure_id,livret_devoir_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_competence_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_saisie_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items du socle donnés d'un certain palier
//	[./pages_prof/releve_socle.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_palier($liste_eleve_id,$liste_item_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND livret_saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND livret_saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT livret_eleve_id AS eleve_id , livret_socle_id AS socle_id , livret_competence_id AS competence_id , ';
	$DB_SQL.= 'livret_saisie_note AS note , livret_competence_nom AS competence_nom , ';
	$DB_SQL.= 'CONCAT(livret_matiere_ref,".",livret_niveau_ref,".",livret_domaine_ref,livret_theme_ordre,livret_competence_ordre) AS competence_ref , ';
	$DB_SQL.= 'livret_matiere_id , '; // Besoin pour l'élève s'il ajoute l'item aux demandes d'évaluations
	$DB_SQL.= 'livret_referentiel_calcul_methode AS calcul_methode , livret_referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM livret_saisie ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_competence_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_socle_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_referentiel USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id IN('.$liste_eleve_id.') AND livret_socle_id IN('.$liste_item_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC, livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC, livret_saisie_date ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
}

?>