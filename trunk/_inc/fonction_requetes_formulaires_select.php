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
 * Ce fichier sert à construire des formulaires de type SELECT avec
 * 		function afficher_select()
 * A partir de diverses options et d'un tableau de données , prédéfini ou issu d'une requête avec
 * 		function DB_OPT_...()
 * Un cookie peut retenir des choix par défaut
 * 		function load_cookie_select()
 * 		function save_cookie_select()
 */

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Variables utilisées pouvant être initialisés lors d'une requête puis utilisées lors de la construction du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$GLOBALS['tab_select_option_first'] = array();
$GLOBALS['tab_select_optgroup']     = array();
$GLOBALS['select_option_selected']  = '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Tableaux prédéfinis
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_select_orientation   = array();
$tab_select_orientation[] = array('valeur'=>'portrait'  , 'texte'=>'Portrait (vertical)');
$tab_select_orientation[] = array('valeur'=>'landscape' , 'texte'=>'Paysage (horizontal)');

$tab_select_marge_min   = array();
$tab_select_marge_min[] = array('valeur'=>5  , 'texte'=>'5 mm');
$tab_select_marge_min[] = array('valeur'=>10 , 'texte'=>'10 mm');
$tab_select_marge_min[] = array('valeur'=>15 , 'texte'=>'15 mm');

$tab_select_couleur   = array();
$tab_select_couleur[] = array('valeur'=>'oui' , 'texte'=>'couleur');
$tab_select_couleur[] = array('valeur'=>'non' , 'texte'=>'noir et blanc');

$tab_select_cases_nb   = array();
$tab_select_cases_nb[] = array('valeur'=>1  , 'texte'=>'1 case');
$tab_select_cases_nb[] = array('valeur'=>2  , 'texte'=>'2 cases');
$tab_select_cases_nb[] = array('valeur'=>3  , 'texte'=>'3 cases');
$tab_select_cases_nb[] = array('valeur'=>4  , 'texte'=>'4 cases');
$tab_select_cases_nb[] = array('valeur'=>5  , 'texte'=>'5 cases');
$tab_select_cases_nb[] = array('valeur'=>6  , 'texte'=>'6 cases');
$tab_select_cases_nb[] = array('valeur'=>7  , 'texte'=>'7 cases');
$tab_select_cases_nb[] = array('valeur'=>8  , 'texte'=>'8 cases');
$tab_select_cases_nb[] = array('valeur'=>9  , 'texte'=>'9 cases');
$tab_select_cases_nb[] = array('valeur'=>10 , 'texte'=>'10 cases');

$tab_select_cases_size   = array();
$tab_select_cases_size[] = array('valeur'=>4  , 'texte'=>'4 mm');
$tab_select_cases_size[] = array('valeur'=>5  , 'texte'=>'5 mm');
$tab_select_cases_size[] = array('valeur'=>6  , 'texte'=>'6 mm');
$tab_select_cases_size[] = array('valeur'=>7  , 'texte'=>'7 mm');
$tab_select_cases_size[] = array('valeur'=>8  , 'texte'=>'8 mm');
$tab_select_cases_size[] = array('valeur'=>9  , 'texte'=>'9 mm');
$tab_select_cases_size[] = array('valeur'=>10 , 'texte'=>'10 mm');
$tab_select_cases_size[] = array('valeur'=>12 , 'texte'=>'12 mm');
$tab_select_cases_size[] = array('valeur'=>14 , 'texte'=>'14 mm');
$tab_select_cases_size[] = array('valeur'=>16 , 'texte'=>'16 mm');

$tab_select_remplissage   = array();
$tab_select_remplissage[] = array('valeur'=>'vide'  , 'texte'=>'fiche vierge de tout résultat');
$tab_select_remplissage[] = array('valeur'=>'plein' , 'texte'=>'fiche avec les notes des dernières évaluations');

/**
 * Charger un cookie avec des options de mise en page pdf
 * 
 * @param int $structure_id
 * @param int $user_id
 * @return array
 */

function load_cookie_select($structure_id,$user_id)
{
	$filename = './__tmp/cookie/etabl'.$structure_id.'_user'.$user_id.'.txt';
	if(is_file($filename))
	{
		$contenu = file_get_contents($filename);
		return @unserialize($contenu);
	}
	else
	{
		return array( 'orientation'=>'portrait' , 'marge_min'=>5 ,  'couleur'=>'oui' , 'cases_nb'=>5 , 'cases_largeur'=>5 , 'cases_hauteur'=>5 );
	}
}

/**
 * Sauver un cookie avec des options de mise en page pdf
 * 
 * @param int $structure_id
 * @param int $user_id
 * @return void
 */

 function save_cookie_select($structure_id,$user_id)
{
	global $orientation,$marge_min,$couleur,$cases_nb,$cases_largeur,$cases_hauteur;
	$tab_cookie = array('orientation'=>$orientation,'marge_min'=>$marge_min,'couleur'=>$couleur,'cases_nb'=>$cases_nb,'cases_largeur'=>$cases_largeur,'cases_hauteur'=>$cases_hauteur);
	file_put_contents('./__tmp/cookie/etabl'.$structure_id.'_user'.$user_id.'.txt',serialize($tab_cookie));
	/*
		Remarque : il y a un problème de serialize avec les type float : voir http://fr2.php.net/manual/fr/function.serialize.php#85988
		Dans ce cas il faut remplacer
		serialize($tab_cookie)
		par
		preg_replace( '/d:([0-9]+(\.[0-9]+)?([Ee][+-]?[0-9]+)?);/e', "'d:'.(round($1,9)).';'", serialize($tab_cookie) );
	*/
}

/**
 * Afficher un élément select de formulaire à partir d'un tableau de données et d'options
 * 
 * @param array       $DB_TAB       tableau des données [valeur texte]
 * @param string|bool $select_nom   chaine à utiliser pour l'id/nom du select, ou false si on retourne juste les options sans les encapsuler dans un select
 * @param string      $option_first 1ère option éventuelle [non] [oui] [val]
 * @param string|bool $selection    préselection éventuelle [false] [true] [val] [ou $...]
 * @param string      $optgroup     regroupement d'options éventuel [non] [oui]
 * @return string
 */

function afficher_select($DB_TAB,$select_nom,$option_first,$selection,$optgroup)
{
	// On commence par la 1ère option
	if($option_first==='non')
	{
		// ... sans option initiale
		$options = '';
	}
	elseif($option_first==='oui')
	{
		// ... avec une option initiale vierge
		$options = '<option value=""></option>';
	}
	elseif($option_first==='val')
	{
		// ... avec une option initiale dont le contenu est à récupérer
		list($option_valeur,$option_texte,$option_class) = $GLOBALS['tab_select_option_first'];
		$options = '<option value="'.$option_valeur.'" class="'.$option_class.'">'.html($option_texte).'</option>';
	}
	if(is_array($DB_TAB))
	{
		// On construit les options...
		if($optgroup==='non')
		{
			// ... classiquement, sans regroupements
			foreach($DB_TAB as $DB_ROW)
			{
				$class = (isset($DB_ROW['class'])) ? ' class="'.html($DB_ROW['class']).'"' : '';
				$options .= '<option value="'.$DB_ROW['valeur'].'"'.$class.'>'.html($DB_ROW['texte']).'</option>';
			}
		}
		elseif($optgroup==='oui')
		{
			// ... en regroupant par optgroup ; $optgroup est alors un tableau à 2 champs
			$tab_options = array();
			foreach($DB_TAB as $DB_ROW)
			{
				$class = (isset($DB_ROW['class'])) ? ' class="'.html($DB_ROW['class']).'"' : '';
				$tab_options[$DB_ROW['optgroup']][] = '<option value="'.$DB_ROW['valeur'].'"'.$class.'>'.html($DB_ROW['texte']).'</option>';
			}
			foreach($tab_options as $group_key => $tab_group_options)
			{
				$options .= '<optgroup label="'.$GLOBALS['tab_select_optgroup'][$group_key].'">'.implode('',$tab_group_options).'</optgroup>';
			}
		}
		// On sélectionne les options qu'il faut... (fait après le foreach précédent sinon c'est compliqué à gérer simultanément avec les groupes d'options éventuels
		if($selection===false)
		{
			// ... ne rien sélectionner
		}
		elseif($selection===true)
		{
			// ... tout sélectionner
			$options = str_replace('<option' , '<option selected="selected"' , $options);
		}
		else
		{
			// ... sélectionner une option ; soit $selection contient la valeur à sélectionner soit elle a été définie avant
			$selection = ($selection=='val') ? $GLOBALS['select_option_selected'] : $selection ;
			$options = str_replace('value="'.$selection.'"' , 'value="'.$selection.'" selected="selected"' , $options);
		}
	}
	// Si $DB_TAB n'est pas un tableau alors c'est une chaine avec un message d'erreur affichée sous la forme d'une option disable
	else
	{
		$options .= '<option value="" disabled="disabled">'.$DB_TAB.'</option>';
	}
	// On insère dans un select si demandé
	return ($select_nom) ? '<select id="'.$select_nom.'" name="'.$select_nom.'">'.$options.'</select>' : $options ;
}

/**
 * Retourner un tableau [valeur texte] des matières de l'établissement (communes choisies ou spécifiques ajoutées)
 * 
 * @param int    $structure_id
 * @param string $listing_matieres_communes   id des matières communes séparées par des virgules
 * @param bool   $transversal                 inclure ou pas la matière tranversale à la liste
 * @return array|string
 */

function DB_OPT_matieres_etabl($structure_id,$listing_matieres_communes,$transversal)
{
	$DB_SQL = 'SELECT livret_matiere_id AS valeur, livret_matiere_nom AS texte FROM livret_matiere ';
	$DB_SQL.= ($listing_matieres_communes) ? 'WHERE livret_matiere_structure_id=:structure_id OR livret_matiere_id IN('.$listing_matieres_communes.') ' : 'WHERE livret_matiere_structure_id=:structure_id ';
	$DB_SQL.= ($listing_matieres_communes && !$transversal) ? 'AND livret_matiere_transversal=0 ' : '' ;
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucune matière n\'est rattachée à l\'établissement !' ;
}

/**
 * Retourner un tableau [valeur texte] des matières communes choisies par l'établissement
 * 
 * @param string $listing_matieres_communes   id des matières communes séparées par des virgules
 * @return array|string
 */

function DB_OPT_matieres_communes($listing_matieres_communes)
{
	if($listing_matieres_communes)
	{
		$DB_SQL = 'SELECT livret_matiere_id AS valeur, livret_matiere_nom AS texte FROM livret_matiere ';
		$DB_SQL.= 'WHERE livret_matiere_id IN('.$listing_matieres_communes.') ';
		$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , null);
		return $DB_TAB;
	}
	else
	{
		return 'Aucune matière commune n\'est rattachée à l\'établissement !';
	}
}

/**
 * Retourner un tableau [valeur texte] des matières du professeur identifié
 * 
 * @param int $structure_id
 * @param int $user_id
 * @return array|string
 */

function DB_OPT_matieres_professeur($structure_id,$user_id)
{
	$DB_SQL = 'SELECT livret_matiere_id AS valeur, livret_matiere_nom AS texte FROM livret_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Vous n\'êtes pas rattaché à une matière !' ;
}

/**
 * Retourner un tableau [valeur texte] des matières d'un élève identifié
 * 
 * @param int $structure_id
 * @param int $user_id
 * @return array|string
 */

function DB_OPT_matieres_eleve($structure_id,$user_id)
{
	// On commence par récupérer la classe et les groupes associés à l'élève
	// DB::query(SACOCHE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
	$DB_SQL = 'SELECT livret_eleve_classe_id, GROUP_CONCAT(DISTINCT livret_groupe_id SEPARATOR ",") AS livret_liste_groupe_id FROM livret_user ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
	$DB_SQL.= 'GROUP BY livret_user_id ';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id);
	$DB_ROW = DB::queryRow(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if( ($DB_ROW['livret_eleve_classe_id']==0) && (is_null($DB_ROW['livret_liste_groupe_id'])) )
	{
		// élève sans classe et sans groupe
		return 'Aucune classe et aucun groupe ne vous est affecté !';
	}
	else
	{
		if(is_null($DB_ROW['livret_liste_groupe_id']))
		{
			$liste_groupes = $DB_ROW['livret_eleve_classe_id'];
		}
		elseif($DB_ROW['livret_eleve_classe_id']==0)
		{
			$liste_groupes = $DB_ROW['livret_liste_groupe_id'];
		}
		else
		{
			$liste_groupes = $DB_ROW['livret_eleve_classe_id'].','.$DB_ROW['livret_liste_groupe_id'];
		}
		// Ensuite on récupère les matières des professeurs qui sont associés à la liste des groupes récupérés
		$DB_SQL = 'SELECT livret_matiere_id AS valeur, livret_matiere_nom AS texte FROM livret_user ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_user_id) ';
		$DB_SQL.= 'LEFT JOIN livret_jointure_user_matiere USING (livret_structure_id,livret_user_id) ';
		$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id IN('.$liste_groupes.') ';
		$DB_SQL.= 'GROUP BY livret_matiere_id ';
		$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
		$DB_VAR = array(':structure_id'=>$structure_id);
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		return count($DB_TAB) ? $DB_TAB : 'Vous n\'avez pas de professeur rattaché à une matière !' ;
	}
}

/**
 * Retourner un tableau [valeur texte] des matières d'une classe ou d'un groupe
 * 
 * @param int $structure_id
 * @param int $groupe_id     id de la classe ou du groupe
 * @return array|string
 */

function DB_OPT_matieres_groupe($structure_id,$groupe_id)
{
	// On récupère les matières des professeurs qui sont associés au groupe
	$DB_SQL = 'SELECT livret_matiere_id AS valeur, livret_matiere_nom AS texte FROM livret_jointure_user_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_user USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_matiere USING (livret_structure_id,livret_user_id) ';
	$DB_SQL.= 'LEFT JOIN livret_matiere USING (livret_matiere_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_id=:groupe_id AND livret_user_profil="professeur" ';
	$DB_SQL.= 'GROUP BY livret_matiere_id ';
	$DB_SQL.= 'ORDER BY livret_matiere_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':groupe_id'=>$groupe_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Il n\'y a pas de professeur du groupe rattaché à une matière !' ;
}

/**
 * Retourner un tableau [valeur texte] des niveaux de l'établissement
 * 
 * @param string $listing_niveaux   id des niveaux séparés par des virgules
 * @param string $listing_paliers   id des paliers séparés par des virgules
 * @return array|string
 */

function DB_OPT_niveaux_etabl($listing_niveaux,$listing_paliers)
{
	if($listing_niveaux)
	{
		$DB_SQL = 'SELECT livret_niveau_id AS valeur, livret_niveau_nom AS texte FROM livret_niveau ';
		$DB_SQL.= 'WHERE livret_niveau_id IN('.$listing_niveaux.') ';
		$DB_SQL.= ($listing_paliers) ? 'OR livret_palier_id IN('.$listing_paliers.') ' : '' ;
		$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , null);
		return $DB_TAB;
	}
	else
	{
		return 'Aucun niveau n\'est rattaché à l\'établissement !';
	}
}

/**
 * Retourner un tableau [valeur texte] des paliers du socle de l'établissement
 * 
 * @param string $listing_paliers   id des paliers séparés par des virgules
 * @return array|string
 */

function DB_OPT_paliers_etabl($listing_paliers)
{
	if($listing_paliers)
	{
		$DB_SQL = 'SELECT livret_palier_id AS valeur, livret_palier_nom AS texte FROM livret_socle_palier ';
		$DB_SQL.= 'WHERE livret_palier_id IN('.$listing_paliers.') ';
		$DB_SQL.= 'ORDER BY livret_palier_ordre ASC';
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , null);
		return $DB_TAB;
	}
	else
	{
		return 'Aucun palier du socle commun n\'est rattaché à l\'établissement !';
	}
}

/**
 * Retourner un tableau [valeur texte liste_groupe_id] des niveaux de l'établissement pour un élève identifié
 * liste_groupe_id sert pour faire une recherche de l'id de la classe dedans afin de pouvoir préselectionner le niveau de la classe de l'élève
 * 
 * @param int    $structure_id
 * @param string $listing_niveaux   id des niveaux séparés par des virgules
 * @param string $eleve_classe_id   id de la classe de l'élève
 * @return array|string
 */

function DB_OPT_niveaux_eleve($structure_id,$listing_niveaux,$eleve_classe_id)
{
	if($listing_niveaux)
	{
		// DB::query(SACOCHE_BD_NAME , 'SET group_concat_max_len = ...'); // Pour lever si besoin une limitation de GROUP_CONCAT (group_concat_max_len est par défaut limité à une chaine de 1024 caractères).
		$DB_SQL = 'SELECT livret_niveau_id AS valeur, livret_niveau_nom AS texte, GROUP_CONCAT(livret_groupe_id SEPARATOR ",") AS liste_groupe_id FROM livret_niveau ';
		$DB_SQL.= 'LEFT JOIN livret_groupe USING (livret_niveau_id) ';
		$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_niveau_id IN('.$listing_niveaux.') ';
		$DB_SQL.= 'GROUP BY livret_niveau_id ';
		$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
		$DB_VAR = array(':structure_id'=>$structure_id);
		$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		// Tester la présence de la classe parmi la liste des id de groupes
		$search_valeur = ','.$eleve_classe_id.',';
		foreach($DB_TAB as $DB_ROW)
		{
			if(mb_substr_count(','.$DB_ROW['liste_groupe_id'].',',$search_valeur))
			{
				$GLOBALS['select_option_selected'] = $DB_ROW['valeur'];
			}
			unset($DB_ROW['liste_groupe_id']);
		}
		return $DB_TAB;
	}
	else
	{
		return 'Aucun niveau n\'est rattaché à l\'établissement !';
	}
}

/**
 * Retourner un tableau [valeur texte optgroup] des niveaux / classes / groupes d'un établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $structure_id
 * @return array|string
 */

function DB_OPT_regroupements_etabl($structure_id)
{
	// Options du select : catégorie "Divers"
	$DBTAB_divers = array();
	$DBTAB_divers[] = array('valeur'=>'d1','texte'=>'Élèves sans classe','optgroup'=>'divers');
	$DBTAB_divers[] = array('valeur'=>'d2','texte'=>'Tout l\'établissement','optgroup'=>'divers');
	// Options du select : catégorie "Niveaux" (contenant des classes ou des groupes)
	$DB_SQL = 'SELECT CONCAT("n",livret_niveau_id) AS valeur, livret_niveau_nom AS texte, "niveau" AS optgroup FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type ';
	$DB_SQL.= 'GROUP BY livret_niveau_id ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':type'=>'classe');
	$DB_TAB_niveau = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// Options du select : catégories "Classes" et "Groupes"
	$DB_SQL = 'SELECT CONCAT(LEFT(livret_groupe_type,1),livret_groupe_id) AS valeur, livret_groupe_nom AS texte, livret_groupe_type AS optgroup FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type IN (:type1,:type2) ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':type1'=>'classe',':type2'=>'groupe');
	$DB_TAB_classe_groupe = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	// On assemble tous ces tableaux à la suite
	$DB_TAB = array_merge($DBTAB_divers,$DB_TAB_niveau,$DB_TAB_classe_groupe);
	$GLOBALS['tab_select_optgroup'] = array('divers'=>'Divers','niveau'=>'Niveaux','classe'=>'Classes','groupe'=>'Groupes');
	return $DB_TAB ;

}

/**
 * Retourner un tableau [valeur texte optgroup] des groupes d'un établissement
 *
 * @param int $structure_id
 * @return array|string
 */

function DB_OPT_groupes_etabl($structure_id)
{
	$DB_SQL = 'SELECT livret_groupe_id AS valeur, livret_groupe_nom AS texte FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':type'=>'groupe');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucun groupe n\'est enregistré !' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des classes / groupes d'un professeur identifié
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $structure_id
 * @param int $user_id
 * @return array|string
 */

function DB_OPT_groupes_professeur($structure_id,$user_id)
{
	$GLOBALS['tab_select_option_first'] = array(0,'Fiche générique','');
	$DB_SQL = 'SELECT livret_groupe_id AS valeur, livret_groupe_nom AS texte, livret_groupe_type AS optgroup FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_groupe_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND ( livret_user_id=:user_id OR livret_groupe_prof_id=:user_id ) AND livret_groupe_type!=:type4 ';
	$DB_SQL.= 'GROUP BY livret_groupe_id '; // indispensable pour les groupes de besoin, sinon autant de lignes que de membres du groupe
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':type4'=>'eval');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$GLOBALS['tab_select_optgroup'] = array('classe'=>'Classes','groupe'=>'Groupes','besoin'=>'Besoins');
	return count($DB_TAB) ? $DB_TAB : 'Aucune classe et aucun groupe ne vous sont affectés !' ;
}

/**
 * Retourner un tableau [valeur texte] des groupes de besoin d'un professeur identifié
 * 
 * @param int $structure_id
 * @param int $user_id
 * @return array|string
 */

function DB_OPT_besoins_professeur($structure_id,$user_id)
{
	$DB_SQL = 'SELECT livret_groupe_id AS valeur, livret_groupe_nom AS texte FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_prof_id=:user_id AND livret_groupe_type=:type ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':type'=>'besoin');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Vous n\'avez aucun groupe de besoin enregistré !' ;
}

/**
 * Retourner un tableau [valeur texte] des classes de l'établissement
 *
 * @param int $structure_id
 * @return array|string
 */

function DB_OPT_classes_etabl($structure_id)
{
	$DB_SQL = 'SELECT livret_groupe_id AS valeur, CONCAT(livret_groupe_nom," (",livret_groupe_ref,")") AS texte FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type=:type ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':type'=>'classe');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucune classe n\'est enregistrée !' ;
}

/**
 * Retourner un tableau [valeur texte optgroup] des classes / groupes de l'établissement
 * optgroup sert à pouvoir regrouper les options
 *
 * @param int $structure_id
 * @return array|string
 */

function DB_OPT_classes_groupes_etabl($structure_id)
{
	$GLOBALS['tab_select_option_first'] = array(0,'Fiche générique','');
	$DB_SQL = 'SELECT livret_groupe_id AS valeur, livret_groupe_nom AS texte, livret_groupe_type AS optgroup FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_groupe_type IN (:type1,:type2) ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':type1'=>'classe',':type2'=>'groupe');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$GLOBALS['tab_select_optgroup'] = array('classe'=>'Classes','groupe'=>'Groupes');
	return count($DB_TAB) ? $DB_TAB : 'Aucune classe et aucun groupe ne sont enregistrés !' ;
}

/**
 * Retourner un tableau [valeur texte] des classes où un professeur identifié est professeur principal
 * 
 * @param int $structure_id
 * @param int $user_id
 * @return array|string
 */

function DB_OPT_classes_prof_principal($structure_id,$user_id)
{
	$DB_SQL = 'SELECT livret_groupe_id AS valeur, livret_groupe_nom AS texte FROM livret_groupe ';
	$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_groupe_id) ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND ( livret_user_id=:user_id OR livret_groupe_prof_id=:user_id ) AND livret_groupe_type=:type1 AND livret_jointure_pp=:pp ';
	$DB_SQL.= 'GROUP BY livret_groupe_id ';
	$DB_SQL.= 'ORDER BY livret_niveau_ordre ASC, livret_groupe_nom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':user_id'=>$user_id,':type1'=>'classe',':pp'=>1);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Vous n\'êtes professeur principal d\'aucune classe !' ;
}

/**
 * Retourner un tableau [valeur texte] des périodes de l'établissement, indépendamment rattachements aux classes
 *
 * @param int $structure_id
 * @return array|string
 */

function DB_OPT_periodes_etabl($structure_id)
{
	$GLOBALS['tab_select_option_first'] = array(0,'Personnalisée','');
	$DB_SQL = 'SELECT livret_periode_id AS valeur, livret_periode_nom AS texte FROM livret_periode ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_SQL.= 'ORDER BY livret_periode_ordre ASC';
	$DB_VAR = array(':structure_id'=>$structure_id);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucune période n\'est enregistrée !' ;
}

/**
 * Retourner un tableau [valeur texte] des professeurs de l'établissement
 * 
 * @param int $structure_id
 * @return array|string
 */

function DB_OPT_professeurs_etabl($structure_id)
{
	$DB_SQL = 'SELECT livret_user_id AS valeur, CONCAT(livret_user_nom," ",livret_user_prenom) AS texte FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:statut ';
	$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'professeur',':statut'=>1);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	return count($DB_TAB) ? $DB_TAB : 'Aucun professeur n\'est enregistré !' ;
}

/**
 * Retourner un tableau [valeur texte] des professeurs et directeurs de l'établissement
 * optgroup sert à pouvoir regrouper les options
 * 
 * @param int $structure_id
 * @param int $user_statut   statut des utilisateurs (1 pour actif, 0 pour inactif)
 * @return array|string
 */

function DB_OPT_professeurs_directeurs_etabl($structure_id,$user_statut)
{
	$DB_SQL = 'SELECT livret_user_id AS valeur, CONCAT(livret_user_nom," ",livret_user_prenom) AS texte, livret_user_profil AS optgroup FROM livret_user ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil IN(:profil1,:profil2) AND livret_user_statut=:user_statut ';
	$DB_SQL.= 'ORDER BY livret_user_profil DESC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$structure_id,':profil1'=>'professeur',':profil2'=>'directeur',':user_statut'=>$user_statut);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$GLOBALS['tab_select_optgroup'] = array('directeur'=>'Directeurs','professeur'=>'Professeurs');
	$mot = ($user_statut) ? 'enregistré' : 'désactivé' ;
	return count($DB_TAB) ? $DB_TAB : 'Aucun professeur ou directeur n\'est '.$mot.' !' ;
}

/**
 * Retourner un tableau [valeur texte] des élèves d'un regroupement préselectionné
 * 
 * @param int    $structure_id
 * @param string $groupe_type   valeur parmi [sdf] [all] [niveau] [classe] [groupe] [besoin] 
 * @param int    $user_statut   statut des utilisateurs (1 pour actif, 0 pour inactif)
 * @return array|string
 */

function DB_OPT_eleves_regroupement($structure_id,$groupe_type,$groupe_id,$user_statut)
{
	$DB_SQL = 'SELECT livret_user_id AS valeur, CONCAT(livret_user_nom," ",livret_user_prenom) AS texte FROM livret_user ';
	switch ($groupe_type)
	{
		case 'sdf' :	// On veut les élèves non affectés dans une classe
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:user_statut AND livret_eleve_classe_id=:classe ';
			$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve',':user_statut'=>$user_statut,':classe'=>0);
			break;
		case 'all' :	// On veut tous les élèves de l'établissement
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:user_statut ';
			$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve',':user_statut'=>$user_statut);
			break;
		case 'niveau' :	// On veut tous les élèves d'un niveau
			$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id ';
			$DB_SQL.= 'WHERE livret_user.livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:user_statut AND livret_niveau_id=:niveau ';
			$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve',':user_statut'=>$user_statut,':niveau'=>$groupe_id);
			break;
		case 'classe' :	// On veut tous les élèves d'une classe (on utilise "livret_eleve_classe_id" de "livret_user")
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:user_statut AND livret_eleve_classe_id=:classe ';
			$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve',':user_statut'=>$user_statut,':classe'=>$groupe_id);
			break;
		case 'groupe' :	// On veut tous les élèves d'un groupe (on utilise la jointure de "livret_jointure_user_groupe")
		case 'besoin' :	// On veut tous les élèves d'un groupe de besoin (on utilise la jointure de "livret_jointure_user_groupe")
			$DB_SQL.= 'LEFT JOIN livret_jointure_user_groupe USING (livret_structure_id,livret_user_id) ';
			$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_profil=:profil AND livret_user_statut=:user_statut AND livret_groupe_id=:groupe ';
			$DB_VAR = array(':structure_id'=>$structure_id,':profil'=>'eleve',':user_statut'=>$user_statut,':groupe'=>$groupe_id);
			break;
	}
	$DB_SQL.= 'ORDER BY livret_user_nom ASC, livret_user_prenom ASC';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$mot = ($user_statut) ? 'enregistré' : 'désactivé' ;
	return count($DB_TAB) ? $DB_TAB : 'Aucun élève de ce regroupement n\'est '.$mot.' !' ;
}

/**
 * Retourner un tableau [valeur texte optgroup class] des structures (choix d'établissements en page d'accueil)
 * le continent sert à pouvoir regrouper les options
 * 
 * @param void
 * @return array|string
 */

function DB_OPT_structures_sacoche()
{
	$GLOBALS['tab_select_option_first'] = array(ID_DEMO,'Établissement de démonstration','normal');
	$DB_SQL = 'SELECT * FROM livret_structure ';
	$DB_SQL.= 'WHERE livret_structure_id!='.ID_DEMO.' ';
	$DB_SQL.= 'ORDER BY geo_continent_ordre ASC, geo_pays_nom ASC, geo_departement_numero ASC, geo_commune_nom ASC';
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , null);
	if(count($DB_TAB))
	{
		$tab_retour_champs = array();
		foreach($DB_TAB as $DB_ROW)
		{
			$texte = ($DB_ROW['geo_continent_ordre']>2) ? $DB_ROW['geo_pays_nom'].' | ' : $DB_ROW['geo_departement_numero'].' '.$DB_ROW['geo_departement_nom'].' | ';
			$texte.= $DB_ROW['geo_commune_nom'].' | ';
			$texte.= $DB_ROW['structure_type_ref'].' '.$DB_ROW['structure_nom'];
			$GLOBALS['tab_select_optgroup'][$DB_ROW['geo_continent_ordre']] = $DB_ROW['geo_continent_nom'];
			$tab_retour_champs[] = array('valeur'=>$DB_ROW['livret_structure_id'],'texte'=>$texte,'optgroup'=>$DB_ROW['geo_continent_ordre'],'class'=>$DB_ROW['livret_structure_sso']);
		}
		return $tab_retour_champs;
	}
	else
	{
		return 'Aucun autre établissement n\'est enregistré !';
	}
}

/**
 * Retourner un tableau [valeur texte optgroup] des structures qui partagent au moins un référentiel
 * le continent sert à pouvoir regrouper les options
 * 
 * @param string $listing_niveaux   id des niveaux séparés par des virgules
 * @return array|string
 */

function DB_OPT_structures_partage($listing_niveaux)
{
	$DB_SQL = 'SELECT * FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_structure USING (livret_structure_id) ';
	$DB_SQL.= 'WHERE livret_structure_id!='.ID_DEMO.' AND livret_niveau_id IN('.$listing_niveaux.') AND livret_referentiel_partage=:partage ';
	$DB_SQL.= 'GROUP BY livret_structure_id ';
	$DB_SQL.= 'ORDER BY geo_continent_ordre ASC, geo_pays_nom ASC, geo_departement_numero ASC, geo_commune_nom ASC';
	$DB_VAR = array(':partage'=>'oui');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		$tab_retour_champs = array();
		foreach($DB_TAB as $DB_ROW)
		{
			$texte = ($DB_ROW['geo_continent_ordre']>2) ? $DB_ROW['geo_pays_nom'].' | ' : $DB_ROW['geo_departement_numero'].' '.$DB_ROW['geo_departement_nom'].' | ';
			$texte.= $DB_ROW['geo_commune_nom'].' | ';
			$texte.= $DB_ROW['structure_type_ref'].' '.$DB_ROW['structure_nom'];
			$GLOBALS['tab_select_optgroup'][$DB_ROW['geo_continent_ordre']] = $DB_ROW['geo_continent_nom'];
			$tab_retour_champs[] = array('valeur'=>$DB_ROW['livret_structure_id'],'texte'=>$texte,'optgroup'=>$DB_ROW['geo_continent_ordre']);
		}
		return $tab_retour_champs;
	}
	else
	{
		return 'Aucun établissement ne partage de référentiel !';
	}
}

?>