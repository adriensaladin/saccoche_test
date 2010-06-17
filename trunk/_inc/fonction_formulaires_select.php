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
				$options .= '<optgroup label="'.html($GLOBALS['tab_select_optgroup'][$group_key]).'">'.implode('',$tab_group_options).'</optgroup>';
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

?>