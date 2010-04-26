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

/**
 * DB_select_arborescence
 * Retourner l'arborescence d'un référentiel (tableau issu de la requête SQL)
 * + pour une matière donnée / pour toutes les matières d'un professeur donné
 * + pour un niveau donné / pour tous les niveaux concernés
 * 
 * @param int  $prof_id      passer 0 pour une recherche sur une matière plutôt que sur toutes les matières d'un prof
 * @param int  $matiere_id   passer 0 pour une recherche sur toutes les matières d'un prof plutôt que sur une matière
 * @param int  $niveau_id    passer 0 pour une recherche sur tous les niveaux
 * @param bool $only_item    "true" pour ne retourner que les lignes d'items, "false" pour l'arborescence complète, sans forcément descendre jusqu'à l'items (valeurs NULL retournées).
 * @param bool $socle_nom    avec ou pas le nom des items du socle associés
 * @return array
 */

function DB_select_arborescence($prof_id,$matiere_id,$niveau_id,$only_item,$socle_nom)
{
	$select_socle_nom  = ($socle_nom)  ? 'entree_id,entree_nom ' : 'entree_id ' ;
	$join_user_matiere = ($prof_id)    ? 'LEFT JOIN sacoche_jointure_user_matiere USING (matiere_id) ' : '' ;
	$join_socle_item   = ($socle_nom)  ? 'LEFT JOIN sacoche_socle_entree USING (entree_id) ' : '' ;
	$where_user        = ($prof_id)    ? 'user_id=:user_id ' : '' ;
	$where_matiere     = ($matiere_id) ? 'matiere_id=:matiere_id ' : '' ;
	$where_niveau      = ($niveau_id)  ? 'AND niveau_id=:niveau_id ' : 'AND (niveau_id IN('.$_SESSION['NIVEAUX'].') OR palier_id IN('.$_SESSION['PALIERS'].')) ' ;
	$where_item        = ($only_item)  ? 'AND item_id IS NOT NULL ' : '' ;
	$order_matiere     = ($prof_id)    ? 'matiere_nom ASC, ' : '' ;
	$order_niveau      = (!$niveau_id) ? 'niveau_ordre ASC, ' : '' ;
	$DB_SQL = 'SELECT ';
	$DB_SQL.= 'matiere_id, matiere_ref, matiere_nom, ';
	$DB_SQL.= 'niveau_id, niveau_ref, niveau_nom, ';
	$DB_SQL.= 'domaine_id, domaine_ordre, domaine_ref, domaine_nom, ';
	$DB_SQL.= 'theme_id, theme_ordre, theme_nom, ';
	$DB_SQL.= 'item_id, item_ordre, item_nom, item_coef, item_lien, ';
	$DB_SQL.= $select_socle_nom;
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= $join_user_matiere;
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= $join_socle_item;
	$DB_SQL.= 'WHERE '.$where_user.$where_matiere.$where_niveau.$where_item;
	$DB_SQL.= 'ORDER BY '.$order_matiere.$order_niveau.'domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array();
	if($prof_id)    {$DB_VAR[':user_id']    = $prof_id;}
	if($matiere_id) {$DB_VAR[':matiere_id'] = $matiere_id;}
	if($niveau_id)  {$DB_VAR[':niveau_id']  = $niveau_id;}
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
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
		if($DB_ROW['matiere_id']!=$matiere_id)
		{
			$matiere_id = $DB_ROW['matiere_id'];
			$tab_matiere[$matiere_id] = ($reference) ? $DB_ROW['matiere_ref'].' - '.$DB_ROW['matiere_nom'] : $DB_ROW['matiere_nom'] ;
			$niveau_id     = 0;
			$domaine_id    = 0;
			$theme_id      = 0;
			$competence_id = 0;
		}
		if( (!is_null($DB_ROW['niveau_id'])) && ($DB_ROW['niveau_id']!=$niveau_id) )
		{
			$niveau_id = $DB_ROW['niveau_id'];
			$tab_niveau[$matiere_id][$niveau_id] = ($reference) ? $DB_ROW['niveau_ref'].' - '.$DB_ROW['niveau_nom'] : $DB_ROW['niveau_nom'];
		}
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$tab_domaine[$matiere_id][$niveau_id][$domaine_id] = ($reference) ? $DB_ROW['domaine_ref'].' - '.$DB_ROW['domaine_nom'] : $DB_ROW['domaine_nom'];
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$tab_theme[$matiere_id][$niveau_id][$domaine_id][$theme_id] = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].' - '.$DB_ROW['theme_nom'] : $DB_ROW['theme_nom'] ;
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$competence_id) )
		{
			$competence_id = $DB_ROW['item_id'];
			switch($aff_coef)
			{
				case 'texte' :	$coef_texte = '['.$DB_ROW['item_coef'].'] ';
												break;
				case 'image' :	$coef_texte = '<img src="./_img/x'.$DB_ROW['item_coef'].'.gif" title="Coefficient '.$DB_ROW['item_coef'].'." /> ';
			}
			switch($aff_socle)
			{
				case 'texte' :	$socle_texte = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
												break;
				case 'image' :	$socle_image = ($DB_ROW['entree_id']) ? 'on' : 'off' ;
												$socle_nom   = ($DB_ROW['entree_id']) ? html($DB_ROW['entree_nom']) : 'Hors-socle.' ;
												$socle_texte = '<img src="./_img/socle_'.$socle_image.'.png" title="'.$socle_nom.'" /> ';
			}
			switch($aff_lien)
			{
				case 'click' :	$lien_texte_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
												$lien_texte_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
				case 'image' :	$lien_image = ($DB_ROW['item_lien']) ? 'on' : 'off' ;
												$lien_nom   = ($DB_ROW['item_lien']) ? html($DB_ROW['item_lien']) : 'Absence de ressource.' ;
												$lien_texte = '<img src="./_img/link_'.$lien_image.'.png" title="'.$lien_nom.'" /> ';
			}
			if($aff_input)
			{
				$input_texte = '<input id="id_'.$competence_id.'" name="f_competences[]" type="checkbox" value="'.$competence_id.'" /> ';
				$label_texte_avant = '<label for="id_'.$competence_id.'">';
				$label_texte_apres = '</label>';
			}
			$competence_texte = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'].' - '.$DB_ROW['item_nom'] : $DB_ROW['item_nom'] ;
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

/**
 * exporter_referentiel_XML
 * Fabriquer un export XML d'un référentiel (pour partage sur serveur central) à partir d'une requête SQL transmise.
 * 
 * @param tab  $DB_TAB
 * @return string
 */

function exporter_referentiel_XML($DB_TAB)
{
	// Traiter le retour SQL : on remplit les tableaux suivants.
	$tab_domaine = array();
	$tab_theme   = array();
	$tab_item    = array();
	$domaine_id = 0;
	$theme_id   = 0;
	$item_id    = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$tab_domaine[$domaine_id] = array('ordre'=>$DB_ROW['domaine_ordre'],'ref'=>$DB_ROW['domaine_ref'],'nom'=>$DB_ROW['domaine_nom']);
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$tab_theme[$domaine_id][$theme_id] = array('ordre'=>$DB_ROW['theme_ordre'],'nom'=>$DB_ROW['theme_nom']);
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
		{
			$item_id = $DB_ROW['item_id'];
			$tab_item[$domaine_id][$theme_id][$item_id] = array('ordre'=>$DB_ROW['item_ordre'],'socle'=>$DB_ROW['entree_id'],'nom'=>$DB_ROW['item_nom'],'coef'=>$DB_ROW['item_coef'],'lien'=>$DB_ROW['item_lien']);
		}
	}
	// Fabrication de l'arbre XML
	$arbreXML = '<arbre id="SACoche">'."\r\n";
	if(count($tab_domaine))
	{
		foreach($tab_domaine as $domaine_id => $tab_domaine_info)
		{
			$arbreXML .= "\t".'<domaine ordre="'.$tab_domaine_info['ordre'].'" ref="'.$tab_domaine_info['ref'].'" nom="'.html($tab_domaine_info['nom']).'">'."\r\n";
			if(isset($tab_theme[$domaine_id]))
			{
				foreach($tab_theme[$domaine_id] as $theme_id => $tab_theme_info)
				{
					$arbreXML .= "\t\t".'<theme ordre="'.$tab_theme_info['ordre'].'" nom="'.html($tab_theme_info['nom']).'">'."\r\n";
					if(isset($tab_item[$domaine_id][$theme_id]))
					{
						foreach($tab_item[$domaine_id][$theme_id] as $item_id => $tab_item_info)
						{
							$arbreXML .= "\t\t\t".'<item ordre="'.$tab_item_info['ordre'].'" socle="'.$tab_item_info['socle'].'" nom="'.html($tab_item_info['nom']).'" coef="'.$tab_item_info['coef'].'" lien="'.html($tab_item_info['lien']).'" />'."\r\n";
						}
					}
					$arbreXML .= "\t\t".'</theme>'."\r\n";
				}
			}
			$arbreXML .= "\t".'</domaine>'."\r\n";
		}
	}
	$arbreXML .= '</arbre>'."\r\n";
	return $arbreXML;
}

/**
 * envoyer_referentiel_XML
 * Transmettre le XML d'un référentiel d'un serveur à un autre (en bidouillant...).
 * 
 * @param int    $structure_id
 * @param string $structure_key
 * @param int    $matiere_id
 * @param int    $niveau_id
 * @param string $arbreXML       si fourni vide, provoquera l'effacement du référentiel mis en partage
 * @return string                "ok" ou un message d'erreur
 */

function envoyer_referentiel_XML($structure_id,$structure_key,$matiere_id,$niveau_id,$arbreXML)
{
	/*
	<< Problème >>
	Attention, si on balance le xml tel quel en GET on obtient l'erreur "414 Request-URI Too Large : The requested URL's length exceeds the capacity limit for this server.".
	En ce qui concerne Apache (v2), cette limite est dans la constante DEFAULT_LIMIT_REQUEST_LINE et correspond à la taille maximale de la ligne de requête.
	Par défaut c’est 8190, ce qui si on retire les 14 caractères de « GET / HTTP/1.1″ nous donne exactement la limite observée empiriquement : 8176.
	La directive d'Apache LimitRequestLine permet de modifier cette valeur (http://httpd.apache.org/docs/2.0/mod/core.html#limitrequestline).
	Mais elle est inaccessible à PHP...
	<< Solution >>
	Lors de l'expérimentation, la longueur moyenne de $arbreXML était de 9195, avec un maximum à 22806.
	Les tests ont été effectués sur $arbreXML de longueur 17574 (donc assez lourd).
	Suite à une compression utilisant gzcompress() la longueur est descendue à 3414 (-80%).
	Mais pour obtenir des caractères transmissibles il a fallu utiliser base64_encode() et la longueur est remontée à 4552 (la doc indique +33% en moyenne).
	Enfin pour le passer dans l'URL il a fallu utiliser urlencode() et la longueur est devenue 4796.
	Au final on obtient 70%/75% de compression, ce qui permet normalement de résoudre ce problème !
	*/
	require_once('./_inc/class.httprequest.php');
	$tab_get = array();
	$tab_get[] = 'action=partager_referentiel';
	$tab_get[] = 'structure_id='.$structure_id;
	$tab_get[] = 'structure_key='.$structure_key;
	$tab_get[] = 'matiere_id='.$matiere_id;
	$tab_get[] = 'niveau_id='.$niveau_id;
	$tab_get[] = 'adresse_retour='.urlencode(SERVEUR_ADRESSE);
	if($arbreXML)
	{
		$tab_get[] = 'arbreXML='.urlencode( base64_encode( gzcompress($arbreXML,9) ) );
	}
	$requete_envoi   = new HTTPRequest('http://competences.sesamath.net/interconnexion.php?'.implode('&',$tab_get));
	$requete_reponse = $requete_envoi->DownloadToString();
	return $requete_reponse;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés par un élève pour la matière selectionnée, durant la période choisie
//	[./pages_eleve/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_eleve_periode_matiere($eleve_id,$matiere_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT item_id , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS competence_ref , ';
	$DB_SQL.= 'item_nom AS competence_nom , item_coef AS competence_coef , entree_id AS competence_socle , item_lien AS competence_lien , ';
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND matiere_id=:matiere '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY item_id ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':eleve_id'=>$eleve_id,':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés par des élèves selectionnés, pour la matière selectionnée, durant la période choisie
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_eleves_periode_matiere($liste_eleve_id,$matiere_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT item_id , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS competence_ref , ';
	$DB_SQL.= 'item_nom AS competence_nom , item_coef AS competence_coef , entree_id AS competence_socle , item_lien AS competence_lien , ';
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND matiere_id=:matiere '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY item_id ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':matiere'=>$matiere_id,':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Retourner l'arborescence des items travaillés et des matières concernées par des élèves selectionnés, durant la période choisie
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_arborescence_et_matieres_eleves_periode($liste_eleve_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT item_id , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS competence_ref , ';
	$DB_SQL.= 'item_nom AS competence_nom , item_coef AS competence_coef , entree_id AS competence_socle , item_lien AS competence_lien , ';
	$DB_SQL.= 'matiere_id , matiere_nom , ';
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'GROUP BY item_id ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_VAR = array(':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR , TRUE);
	$tab_matiere = array();
	foreach($DB_TAB as $competence_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			$tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
			unset($DB_TAB[$competence_id][$key]['matiere_id'],$DB_TAB[$competence_id][$key]['matiere_nom']);
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
	$DB_SQL = 'SELECT item_id , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS competence_ref , ';
	$DB_SQL.= 'item_nom AS competence_nom , item_coef AS competence_coef , entree_id AS competence_socle , item_lien AS competence_lien , ';
	$DB_SQL.= 'matiere_id , matiere_nom , ';
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_compet_id.') ';
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null , TRUE);
	$tab_matiere = array();
	foreach($DB_TAB as $competence_id => $tab)
	{
		foreach($tab as $key => $DB_ROW)
		{
			unset($DB_TAB[$competence_id][$key]['matiere_id'],$DB_TAB[$competence_id][$key]['matiere_nom']);
		}
		$tab_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
	}
	return array($DB_TAB,$tab_matiere);
}

/**
 * DB_select_arborescence_palier
 * 
 * @param int    $palier_id   facultatif : si non fourni, tous les paliers seront concernés
 * @return array
 */

function DB_select_arborescence_palier($palier_id=false)
{
	$DB_SQL = 'SELECT * FROM sacoche_socle_palier ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_pilier USING (palier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_section USING (pilier_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (section_id) ';
	$DB_VAR = array();
	if($palier_id)
	{
		$DB_SQL.= 'WHERE palier_id=:palier_id ';
		$DB_VAR[':palier_id'] = $palier_id;
	}
	$DB_SQL.= 'ORDER BY palier_ordre ASC, pilier_ordre ASC, section_ordre ASC, entree_ordre ASC';
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour un élève donné, pour des items donnés, sur une période donnée
//	[./pages_eleve/grille_niveau.ajax.php] [./pages_eleve/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleve($eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT item_id AS competence_id , ';
	$DB_SQL.= 'saisie_note AS note , saisie_date AS date , devoir_info AS info ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id=:eleve_id AND item_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	$DB_VAR = array(':eleve_id'=>$_SESSION['USER_ID'],':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items donnés d'une matiere donnée, sur une période donnée
//	[./pages_professeur/bilan_periode.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_matiere($liste_eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT eleve_id AS eleve_id , item_id AS competence_id , ';
	$DB_SQL.= 'saisie_note AS note , saisie_date AS date , devoir_info AS info ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	$DB_VAR = array(':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items donnés de plusieurs matieres, sur une période donnée
//	[./pages_professeur/bilan_pp_indiv.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_matieres($liste_eleve_id,$liste_competence_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT eleve_id AS eleve_id , matiere_id AS matiere_id , item_id AS competence_id , ';
	$DB_SQL.= 'saisie_note AS note , saisie_date AS date , devoir_info AS info ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_devoir USING (devoir_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND item_id IN('.$liste_competence_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	$DB_VAR = array(':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Retourner les résultats pour des élèves donnés, pour des items du socle donnés d'un certain palier
//	[./pages_prof/releve_socle.ajax.php]
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

function select_result_eleves_palier($liste_eleve_id,$liste_item_id,$date_mysql_debut,$date_mysql_fin)
{
	$sql_debut = ($date_mysql_debut) ? 'AND saisie_date>=:date_debut ' : '';
	$sql_fin   = ($date_mysql_fin)   ? 'AND saisie_date<=:date_fin '   : '';
	$DB_SQL = 'SELECT eleve_id AS eleve_id , entree_id AS socle_id , item_id AS competence_id , ';
	$DB_SQL.= 'saisie_note AS note , item_nom AS competence_nom , ';
	$DB_SQL.= 'CONCAT(matiere_ref,".",niveau_ref,".",domaine_ref,theme_ordre,item_ordre) AS competence_ref , ';
	$DB_SQL.= 'matiere_id , '; // Besoin pour l'élève s'il ajoute l'item aux demandes d'évaluations
	$DB_SQL.= 'referentiel_calcul_methode AS calcul_methode , referentiel_calcul_limite AS calcul_limite ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_socle_entree USING (entree_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_matiere USING (matiere_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'WHERE eleve_id IN('.$liste_eleve_id.') AND entree_id IN('.$liste_item_id.') '.$sql_debut.$sql_fin;
	$DB_SQL.= 'ORDER BY matiere_nom ASC, niveau_ordre ASC, domaine_ordre ASC, theme_ordre ASC, item_ordre ASC, saisie_date ASC';
	$DB_VAR = array(':date_debut'=>$date_mysql_debut,':date_fin'=>$date_mysql_fin);
	return DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

?>