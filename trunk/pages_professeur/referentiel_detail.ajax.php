<?php
/**
 * @version $Id: referentiel_detail.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_POST['action']!='Voir')){exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['action']))   ? clean_texte($_POST['action'])    : '';
$contexte    = (isset($_POST['contexte'])) ? clean_texte($_POST['contexte'])  : '';	// n1 ou n2 ou n3
$matiere_id  = (isset($_POST['matiere']))  ? clean_entier($_POST['matiere'])  : 0;
$element_id  = (isset($_POST['element']))  ? clean_entier($_POST['element'])  : 0;
$element2_id = (isset($_POST['element2'])) ? clean_entier($_POST['element2']) : 0;
$parent_id   = (isset($_POST['parent']))   ? clean_entier($_POST['parent'])   : 0;
$ordre       = (isset($_POST['ordre']))    ? clean_entier($_POST['ordre'])    : -1;
$ref         = (isset($_POST['ref']))      ? clean_texte($_POST['ref'])       : '';
$nom         = (isset($_POST['nom']))      ? clean_texte($_POST['nom'])       : '';
$coef        = (isset($_POST['coef']))     ? clean_entier($_POST['coef'])     : -1;
$lien        = (isset($_POST['lien']))     ? clean_texte($_POST['lien'])      : '';
$socle_id    = (isset($_POST['socle']))    ? clean_entier($_POST['socle'])    : -1;

function positif($n) {return($n);}
$tab_id = (isset($_POST['tab_id'])) ? array_map('clean_entier',explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
$tab_id2 = (isset($_POST['tab_id2'])) ? array_map('clean_entier',explode(',',$_POST['tab_id2'])) : array() ;
$tab_id2 = array_filter($tab_id2,'positif');

if( ($action=='Voir') && $matiere_id )
{
	// Affichage du référentiel pour la matière sélectionnée
	$DB_SQL = 'SELECT * FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_domaine USING (livret_structure_id,livret_matiere_id,livret_niveau_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
	$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
	$DB_SQL.= 'LEFT JOIN livret_socle_item USING (livret_socle_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_matiere_id=:matiere_id AND (livret_niveau_id IN('.$_SESSION['NIVEAUX'].') OR livret_palier_id IN('.$_SESSION['PALIERS'].')) ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_domaine_ordre ASC, livret_theme_ordre ASC, livret_competence_ordre ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere_id'=>$matiere_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$tab_niveau     = array();
	$tab_domaine    = array();
	$tab_theme      = array();
	$tab_competence = array();
	$niveau_id = 0;
	foreach($DB_TAB as $key => $DB_ROW)
	{
		if( (!is_null($DB_ROW['livret_niveau_id'])) && ($DB_ROW['livret_niveau_id']!=$niveau_id) )
		{
			$niveau_id = $DB_ROW['livret_niveau_id'];
			$tab_niveau[$niveau_id] = $DB_ROW['livret_niveau_nom'];
			$domaine_id    = 0;
			$theme_id      = 0;
			$competence_id = 0;
		}
		if( (!is_null($DB_ROW['livret_domaine_id'])) && ($DB_ROW['livret_domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['livret_domaine_id'];
			$tab_domaine[$niveau_id][$domaine_id] = $DB_ROW['livret_domaine_ref'].' - '.$DB_ROW['livret_domaine_nom'];
		}
		if( (!is_null($DB_ROW['livret_theme_id'])) && ($DB_ROW['livret_theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['livret_theme_id'];
			$tab_theme[$niveau_id][$domaine_id][$theme_id] = $DB_ROW['livret_theme_nom'];
		}
		if( (!is_null($DB_ROW['livret_competence_id'])) && ($DB_ROW['livret_competence_id']!=$competence_id) )
		{
			$competence_id = $DB_ROW['livret_competence_id'];
			$coef_texte    = '<img src="./_img/x'.$DB_ROW['livret_competence_coef'].'.gif" alt="" title="Coefficient '.$DB_ROW['livret_competence_coef'].'." />';
			$socle_image   = ($DB_ROW['livret_socle_id']==0) ? 'off' : 'on' ;
			$socle_nom     = ($DB_ROW['livret_socle_id']==0) ? 'Hors-socle.' : html($DB_ROW['livret_socle_nom']) ;
			$socle_texte   = '<img src="./_img/socle_'.$socle_image.'.png" alt="" title="'.$socle_nom.'" lang="id_'.$DB_ROW['livret_socle_id'].'" />';
			$lien_image    = ($DB_ROW['livret_competence_lien']=='') ? 'off' : 'on' ;
			$lien_nom      = ($DB_ROW['livret_competence_lien']=='') ? 'Absence de ressource.' : html($DB_ROW['livret_competence_lien']) ;
			$lien_texte    = '<img src="./_img/link_'.$lien_image.'.png" alt="" title="'.$lien_nom.'" />';
			$tab_competence[$niveau_id][$domaine_id][$theme_id][$competence_id] = $coef_texte.$socle_texte.$lien_texte.html($DB_ROW['livret_competence_nom']);
		}
	}
	// Attention : envoyer des balises vides sous la forme <q ... /> plante jquery 1.4 (ça marchait avec la 1.3.2).
	$images_niveau  = '';
	$images_niveau .= '<q class="n1_add" lang="add" title="Ajouter un domaine au début de ce niveau."></q>';
	$images_domaine  = '';
	$images_domaine .= '<q class="n1_edit" lang="edit" title="Renommer ce domaine (avec sa référence)."></q>';
	$images_domaine .= '<q class="n1_add" lang="add" title="Ajouter un domaine à la suite."></q>';
	$images_domaine .= '<q class="n1_move" lang="move" title="Déplacer ce domaine."></q>';
	$images_domaine .= '<q class="n1_del" lang="del" title="Supprimer ce domaine ainsi que tout son contenu."></q>';
	$images_domaine .= '<q class="n2_add" lang="add" title="Ajouter un thème au début de ce domaine (et renuméroter)."></q>';
	$images_theme  = '';
	$images_theme .= '<q class="n2_edit" lang="edit" title="Renommer ce thème."></q>';
	$images_theme .= '<q class="n2_add" lang="add" title="Ajouter un thème à la suite (et renuméroter)."></q>';
	$images_theme .= '<q class="n2_move" lang="move" title="Déplacer ce thème (et renuméroter)."></q>';
	$images_theme .= '<q class="n2_del" lang="del" title="Supprimer ce thème ainsi que tout son contenu (et renuméroter)."></q>';
	$images_theme .= '<q class="n3_add" lang="add" title="Ajouter un item au début de ce thème (et renuméroter)."></q>';
	$images_competence  = '';
	$images_competence .= '<q class="n3_edit" lang="edit" title="Renommer, coefficienter, lier cet item."></q>';
	$images_competence .= '<q class="n3_add" lang="add" title="Ajouter un item à la suite (et renuméroter)."></q>';
	$images_competence .= '<q class="n3_move" lang="move" title="Déplacer cet item (et renuméroter)."></q>';
	$images_competence .= '<q class="n3_fus" lang="fus" title="Fusionner avec un autre item (et renuméroter)."></q>';
	$images_competence .= '<q class="n3_del" lang="del" title="Supprimer cet item (et renuméroter)."></q>';
	echo'<ul class="ul_m1">'."\r\n";
	if(count($tab_niveau))
	{
		foreach($tab_niveau as $niveau_id => $niveau_nom)
		{
			echo'	<li class="li_m2" id="m2_'.$niveau_id.'"><span>'.html($niveau_nom).'</span>'.$images_niveau."\r\n";
			echo'		<ul class="ul_n1">'."\r\n";
			if(isset($tab_domaine[$niveau_id]))
			{
				foreach($tab_domaine[$niveau_id] as $domaine_id => $domaine_nom)
				{
					echo'			<li class="li_n1" id="n1_'.$domaine_id.'"><span>'.html($domaine_nom).'</span>'.$images_domaine."\r\n";
					echo'				<ul class="ul_n2">'."\r\n";
					if(isset($tab_theme[$niveau_id][$domaine_id]))
					{
						foreach($tab_theme[$niveau_id][$domaine_id] as $theme_id => $theme_nom)
						{
							echo'					<li class="li_n2" id="n2_'.$theme_id.'"><span>'.html($theme_nom).'</span>'.$images_theme."\r\n";
							echo'						<ul class="ul_n3">'."\r\n";
							if(isset($tab_competence[$niveau_id][$domaine_id][$theme_id]))
							{
								foreach($tab_competence[$niveau_id][$domaine_id][$theme_id] as $competence_id => $competence_nom)
								{
									echo'							<li class="li_n3" id="n3_'.$competence_id.'"><b>'.$competence_nom.'</b>'.$images_competence.'</li>'."\r\n";
								}
							}
							echo'						</ul>'."\r\n";
							echo'					</li>'."\r\n";
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ajouter un domaine / un thème / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='add') && (in_array($contexte,array('n1','n2','n3'))) && $matiere_id && $parent_id && ($ref || ($contexte!='n1')) && $nom && ($ordre!=-1) && ($socle_id!=-1) && ($coef!=-1) )
{
	// exécution !
	if($contexte=='n1')	// domaine
	{
		$DB_SQL = 'INSERT INTO livret_competence_domaine(livret_structure_id,livret_matiere_id,livret_niveau_id,livret_domaine_ref,livret_domaine_nom,livret_domaine_ordre) ';
		$DB_SQL.= 'VALUES(:structure_id,:matiere,:niveau,:ref,:nom,:ordre)';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere'=>$matiere_id,':niveau'=>$parent_id,':ref'=>$ref,':nom'=>$nom,':ordre'=>$ordre);
	}
	elseif($contexte=='n2')	// thème
	{
		$DB_SQL = 'INSERT INTO livret_competence_theme(livret_structure_id,livret_domaine_id,livret_theme_nom,livret_theme_ordre) ';
		$DB_SQL.= 'VALUES(:structure_id,:domaine,:nom,:ordre)';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':domaine'=>$parent_id,':nom'=>$nom,':ordre'=>$ordre);
	}
	elseif($contexte=='n3')	// item
	{
		$DB_SQL = 'INSERT INTO livret_competence_item(livret_structure_id,livret_theme_id,livret_socle_id,livret_competence_nom,livret_competence_ordre,livret_competence_coef,livret_competence_lien) ';
		$DB_SQL.= 'VALUES(:structure_id,:theme,:socle,:nom,:ordre,:coef,:lien)';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':theme'=>$parent_id,':socle'=>$socle_id,':nom'=>$nom,':ordre'=>$ordre,':coef'=>$coef,':lien'=>$lien);
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$element_id = DB::getLastOid(SACOCHE_BD_NAME);
	// Décaler les autres éléments de l'élément parent concerné
	if(count($tab_id))
	{
		if($contexte=='n1')	// domaine
		{
			$DB_SQL = 'UPDATE livret_competence_domaine ';
			$DB_SQL.= 'SET livret_domaine_ordre=livret_domaine_ordre+1 ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_domaine_id IN('.implode(',',$tab_id).') ';
		}
		elseif($contexte=='n2')	// thème
		{
			$DB_SQL = 'UPDATE livret_competence_theme ';
			$DB_SQL.= 'SET livret_theme_ordre=livret_theme_ordre+1 ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_theme_id IN('.implode(',',$tab_id).') ';
		}
		elseif($contexte=='n3')	// item
		{
			$DB_SQL = 'UPDATE livret_competence_item ';
			$DB_SQL.= 'SET livret_competence_ordre=livret_competence_ordre+1 ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id IN('.implode(',',$tab_id).') ';
		}
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// retour
	echo $contexte.'_'.$element_id;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Renommer un domaine / un thème / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='edit') && (in_array($contexte,array('n1','n2','n3'))) && $element_id && ($ref || ($contexte!='n1')) && $nom && ($socle_id!=-1) && ($coef!=-1) )
{
	// exécution !
	if($contexte=='n1')	// domaine
	{
		$DB_SQL = 'UPDATE livret_competence_domaine ';
		$DB_SQL.= 'SET livret_domaine_ref=:ref, livret_domaine_nom=:nom ';
		$DB_SQL.= 'WHERE livret_domaine_id=:element_id AND livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':element_id'=>$element_id,':ref'=>$ref,':nom'=>$nom);
	}
	elseif($contexte=='n2')	// thème
	{
		$DB_SQL = 'UPDATE livret_competence_theme ';
		$DB_SQL.= 'SET livret_theme_nom=:nom ';
		$DB_SQL.= 'WHERE livret_theme_id=:element_id AND livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':element_id'=>$element_id,':nom'=>$nom);
	}
	elseif($contexte=='n3')	// item
	{
		$DB_SQL = 'UPDATE livret_competence_item ';
		$DB_SQL.= 'SET livret_socle_id=:socle, livret_competence_nom=:nom, livret_competence_coef=:coef, livret_competence_lien=:lien ';
		$DB_SQL.= 'WHERE livret_competence_id=:element_id AND livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':element_id'=>$element_id,':socle'=>$socle_id,':nom'=>$nom,':coef'=>$coef,':lien'=>$lien);
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$test_modif = DB::rowCount(SACOCHE_BD_NAME);
	// retour
	echo ($test_modif) ? 'ok' : 'Contenu inchangé ou élément non trouvé !';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Déplacer un domaine / un thème / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='move') && (in_array($contexte,array('n1','n2','n3'))) && $element_id && ($ordre!=-1) && $parent_id )
{
	// exécution !
	if($contexte=='n1')	// domaine
	{
		$DB_SQL = 'UPDATE livret_competence_domaine ';
		$DB_SQL.= 'SET livret_niveau_id=:parent_id, livret_domaine_ordre=:ordre ';
		$DB_SQL.= 'WHERE livret_domaine_id=:element_id AND livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
	}
	elseif($contexte=='n2')	// thème
	{
		$DB_SQL = 'UPDATE livret_competence_theme ';
		$DB_SQL.= 'SET livret_domaine_id=:parent_id, livret_theme_ordre=:ordre ';
		$DB_SQL.= 'WHERE livret_theme_id=:element_id AND livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
	}
	elseif($contexte=='n3')	// item
	{
		$DB_SQL = 'UPDATE livret_competence_item ';
		$DB_SQL.= 'SET livret_theme_id=:parent_id, livret_competence_ordre=:ordre ';
		$DB_SQL.= 'WHERE livret_competence_id=:element_id AND livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
	}
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':element_id'=>$element_id,':parent_id'=>$parent_id,':ordre'=>$ordre);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$test_move = DB::rowCount(SACOCHE_BD_NAME);
	if(!$test_move)
	{
		echo'Contenu inchangé ou élément non trouvé !';
	}
	else
	{
		// Décaler les autres éléments de l'élément de départ parent concerné
		if(count($tab_id))
		{
			if($contexte=='n1')	// domaine
			{
				$DB_SQL = 'UPDATE livret_competence_domaine ';
				$DB_SQL.= 'SET livret_domaine_ordre=livret_domaine_ordre-1 ';
				$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_domaine_id IN('.implode(',',$tab_id).') ';
			}
			elseif($contexte=='n2')	// thème
			{
				$DB_SQL = 'UPDATE livret_competence_theme ';
				$DB_SQL.= 'SET livret_theme_ordre=livret_theme_ordre-1 ';
				$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_theme_id IN('.implode(',',$tab_id).') ';
			}
			elseif($contexte=='n3')	// item
			{
				$DB_SQL = 'UPDATE livret_competence_item ';
				$DB_SQL.= 'SET livret_competence_ordre=livret_competence_ordre-1 ';
				$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id IN('.implode(',',$tab_id).') ';
			}
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
		// Décaler les autres éléments de l'élément d'arrivée parent concerné
		if(count($tab_id2))
		{
			if($contexte=='n1')	// domaine
			{
				$DB_SQL = 'UPDATE livret_competence_domaine ';
				$DB_SQL.= 'SET livret_domaine_ordre=livret_domaine_ordre+1 ';
				$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_domaine_id IN('.implode(',',$tab_id2).') ';
			}
			elseif($contexte=='n2')	// thème
			{
				$DB_SQL = 'UPDATE livret_competence_theme ';
				$DB_SQL.= 'SET livret_theme_ordre=livret_theme_ordre+1 ';
				$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_theme_id IN('.implode(',',$tab_id2).') ';
			}
			elseif($contexte=='n3')	// item
			{
				$DB_SQL = 'UPDATE livret_competence_item ';
				$DB_SQL.= 'SET livret_competence_ordre=livret_competence_ordre+1 ';
				$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id IN('.implode(',',$tab_id2).') ';
			}
			$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
		// retour
		echo'ok';
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Supprimer un domaine (avec son contenu) / un thème (avec son contenu) / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='del') && (in_array($contexte,array('n1','n2','n3'))) && $element_id )
{
	// exécution !
	if($contexte=='n1')	// domaine
	{
		$DB_SQL = 'DELETE livret_competence_domaine, livret_competence_theme, livret_competence_item, livret_jointure_evaluation_competence, livret_jointure_user_competence ';
		$DB_SQL.= 'FROM livret_competence_domaine ';
		$DB_SQL.= 'LEFT JOIN livret_competence_theme USING (livret_structure_id,livret_domaine_id) ';
		$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_evaluation_competence USING (livret_structure_id,livret_competence_id) ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_user_competence USING (livret_structure_id,livret_competence_id) ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_domaine_id=:domaine_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':domaine_id'=>$element_id);
	}
	elseif($contexte=='n2')	// thème
	{
		$DB_SQL = 'DELETE livret_competence_theme, livret_competence_item, livret_jointure_evaluation_competence, livret_jointure_user_competence ';
		$DB_SQL.= 'FROM livret_competence_theme ';
		$DB_SQL.= 'LEFT JOIN livret_competence_item USING (livret_structure_id,livret_theme_id) ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_evaluation_competence USING (livret_structure_id,livret_competence_id) ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_user_competence USING (livret_structure_id,livret_competence_id) ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_theme_id=:theme_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':theme_id'=>$element_id);
	}
	elseif($contexte=='n3')	// item
	{
		$DB_SQL = 'DELETE livret_competence_item, livret_jointure_evaluation_competence, livret_jointure_user_competence ';
		$DB_SQL.= 'FROM livret_competence_item ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_evaluation_competence USING (livret_structure_id,livret_competence_id) ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_user_competence USING (livret_structure_id,livret_competence_id) ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:competence_id';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':competence_id'=>$element_id);
	}
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$test_delete = DB::rowCount(SACOCHE_BD_NAME);	// Est censé renvoyé le nb de lignes supprimées ; à cause du multi-tables curieusement ça renvoie 2, même pour un item non lié
	// Décaler les autres éléments de l'élément parent concerné
	if( ($test_delete) && (count($tab_id)) )
	{
		if($contexte=='n1')	// domaine
		{
			$DB_SQL = 'UPDATE livret_competence_domaine ';
			$DB_SQL.= 'SET livret_domaine_ordre=livret_domaine_ordre-1 ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_domaine_id IN('.implode(',',$tab_id).') ';
		}
		elseif($contexte=='n2')	// thème
		{
			$DB_SQL = 'UPDATE livret_competence_theme ';
			$DB_SQL.= 'SET livret_theme_ordre=livret_theme_ordre-1 ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_theme_id IN('.implode(',',$tab_id).') ';
		}
		elseif($contexte=='n3')	// item
		{
			$DB_SQL = 'UPDATE livret_competence_item ';
			$DB_SQL.= 'SET livret_competence_ordre=livret_competence_ordre-1 ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id IN('.implode(',',$tab_id).') ';
		}
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// retour
	echo ($test_delete) ? 'ok' : 'Élément non trouvé !';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Fusionner un item en l'absorbant par un 2nd item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='fus') && $element_id && $element2_id )
{
	// Supprimer l'item à fusionner
	$DB_SQL = 'DELETE FROM livret_competence_item ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:competence_id';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':competence_id'=>$element_id);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$test_delete = DB::rowCount(SACOCHE_BD_NAME);
	// Décaler les autres éléments de l'élément parent concerné
	if( ($test_delete) && (count($tab_id)) )
	{
		$DB_SQL = 'UPDATE livret_competence_item ';
		$DB_SQL.= 'SET livret_competence_ordre=livret_competence_ordre-1 ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id IN('.implode(',',$tab_id).') ';
		$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	}
	// Mettre à jour les références vers l'item absorbant
	// Dans le cas où les deux items ont été évalués dans une même évaluation, on est obligé de supprimer l'un des scores
	// On doit donc commencer par chercher les conflits possibles de clefs multiples pour éviter un erreur lors de l'UPDATE
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':element_id'=>$element_id,':element2_id'=>$element2_id);
	// Pour livret_jointure_evaluation_competence
	$DB_SQL = 'SELECT livret_evaluation_id ';
	$DB_SQL.= 'FROM livret_jointure_evaluation_competence ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:element_id';
	$TAB1 = array_keys(DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE));
	$DB_SQL = 'SELECT livret_evaluation_id ';
	$DB_SQL.= 'FROM livret_jointure_evaluation_competence ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:element2_id';
	$TAB2 = array_keys(DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE));
	$tab_conflit = array_intersect($TAB1,$TAB2);
	if(count($tab_conflit))
	{
		$DB_SQL = 'DELETE FROM livret_jointure_evaluation_competence ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_evaluation_id=:evaluation_id AND livret_competence_id=:element_id ';
		$DB_SQL.= 'LIMIT 1 ';
		foreach($tab_conflit as $livret_evaluation_id)
		{
			$DB_VAR[':evaluation_id'] = $livret_evaluation_id;
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	$DB_SQL = 'UPDATE livret_jointure_evaluation_competence ';
	$DB_SQL.= 'SET livret_competence_id=:element2_id ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:element_id';
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Pour livret_jointure_user_competence
	$DB_SQL = 'SELECT CONCAT(livret_eleve_id,"x",livret_evaluation_id) AS clefs ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:element_id';
	$TAB1 = array_keys(DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE));
	$DB_SQL = 'SELECT CONCAT(livret_eleve_id,"x",livret_evaluation_id) AS clefs ';
	$DB_SQL.= 'FROM livret_jointure_user_competence ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:element2_id';
	$TAB2 = array_keys(DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR , TRUE));
	$tab_conflit = array_intersect($TAB1,$TAB2);
	if(count($tab_conflit))
	{
		$DB_SQL = 'DELETE FROM livret_jointure_user_competence ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_eleve_id=:eleve_id AND livret_evaluation_id=:evaluation_id AND livret_competence_id=:element_id ';
		foreach($tab_conflit as $ids)
		{
			list($livret_eleve_id,$livret_evaluation_id) = explode('x',$ids);
			$DB_VAR[':eleve_id']      = $livret_eleve_id;
			$DB_VAR[':evaluation_id'] = $livret_evaluation_id;
			DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	$DB_SQL = 'UPDATE livret_jointure_user_competence ';
	$DB_SQL.= 'SET livret_competence_id=:element2_id ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_competence_id=:element_id';
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// retour
	echo ($test_delete) ? 'ok' : 'Élément non trouvé !';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
