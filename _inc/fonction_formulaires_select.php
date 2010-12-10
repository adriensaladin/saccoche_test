<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
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
$tab_select_marge_min[] = array('valeur'=>5  , 'texte'=>'marges de 5 mm');
$tab_select_marge_min[] = array('valeur'=>6  , 'texte'=>'marges de 6 mm');
$tab_select_marge_min[] = array('valeur'=>7  , 'texte'=>'marges de 7 mm');
$tab_select_marge_min[] = array('valeur'=>8  , 'texte'=>'marges de 8 mm');
$tab_select_marge_min[] = array('valeur'=>9  , 'texte'=>'marges de 9 mm');
$tab_select_marge_min[] = array('valeur'=>10 , 'texte'=>'marges de 10 mm');
$tab_select_marge_min[] = array('valeur'=>11 , 'texte'=>'marges de 11 mm');
$tab_select_marge_min[] = array('valeur'=>12 , 'texte'=>'marges de 12 mm');
$tab_select_marge_min[] = array('valeur'=>13 , 'texte'=>'marges de 13 mm');
$tab_select_marge_min[] = array('valeur'=>14 , 'texte'=>'marges de 14 mm');
$tab_select_marge_min[] = array('valeur'=>15 , 'texte'=>'marges de 15 mm');

$tab_select_pages_nb   = array();
$tab_select_pages_nb[] = array('valeur'=>'optimise' , 'texte'=>'nombre de pages optimisé');
$tab_select_pages_nb[] = array('valeur'=>'augmente' , 'texte'=>'nombre de pages augmenté');

$tab_select_couleur   = array();
$tab_select_couleur[] = array('valeur'=>'oui' , 'texte'=>'en couleurs');
$tab_select_couleur[] = array('valeur'=>'non' , 'texte'=>'en niveaux de gris');

$tab_select_legende   = array();
$tab_select_legende[] = array('valeur'=>'oui' , 'texte'=>'avec légende');
$tab_select_legende[] = array('valeur'=>'non' , 'texte'=>'sans légende');

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
$tab_select_cases_size[] = array('valeur'=>5  , 'texte'=>'5 mm');
$tab_select_cases_size[] = array('valeur'=>6  , 'texte'=>'6 mm');
$tab_select_cases_size[] = array('valeur'=>7  , 'texte'=>'7 mm');
$tab_select_cases_size[] = array('valeur'=>8  , 'texte'=>'8 mm');
$tab_select_cases_size[] = array('valeur'=>9  , 'texte'=>'9 mm');
$tab_select_cases_size[] = array('valeur'=>10 , 'texte'=>'10 mm');
$tab_select_cases_size[] = array('valeur'=>11 , 'texte'=>'11 mm');
$tab_select_cases_size[] = array('valeur'=>12 , 'texte'=>'12 mm');
$tab_select_cases_size[] = array('valeur'=>13 , 'texte'=>'13 mm');
$tab_select_cases_size[] = array('valeur'=>14 , 'texte'=>'14 mm');
$tab_select_cases_size[] = array('valeur'=>15 , 'texte'=>'15 mm');

$tab_select_remplissage   = array();
$tab_select_remplissage[] = array('valeur'=>'vide'  , 'texte'=>'sans indication des notes antérieures');
$tab_select_remplissage[] = array('valeur'=>'plein' , 'texte'=>'avec les notes des dernières évaluations');

$tab_select_cart_contenu   = array();
$tab_select_cart_contenu[] = array('valeur'=>'SANS_nom_SANS_result' , 'texte'=>'cartouche SANS les noms d\'élèves et SANS les résultats');
$tab_select_cart_contenu[] = array('valeur'=>'AVEC_nom_SANS_result' , 'texte'=>'cartouche AVEC les noms d\'élèves mais SANS les résultats');
$tab_select_cart_contenu[] = array('valeur'=>'AVEC_nom_AVEC_result' , 'texte'=>'cartouche AVEC les noms d\'élèves et AVEC les résultats (si saisis)');

$tab_select_cart_detail   = array();
$tab_select_cart_detail[] = array('valeur'=>'complet' , 'texte'=>'cartouche avec la dénomination complète de chaque item');
$tab_select_cart_detail[] = array('valeur'=>'minimal' , 'texte'=>'cartouche minimal avec uniquement les références des items');

/**
 * Charger un cookie avec des options de mise en page pdf
 * 
 * @param string $page
 * @return array
 */

function load_cookie_select($page)
{
	$filename = './__tmp/cookie/etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.$page.'.txt';
	if(is_file($filename))
	{
		$contenu = file_get_contents($filename);
		return @unserialize($contenu);
	}
	else
	{
		switch($page)
		{
			case 'releve_synthese' :
				return array( 'couleur'=>'oui' , 'legende'=>'oui' );
			case 'releve_items' :
				return array( 'orientation'=>'portrait' , 'couleur'=>'oui' , 'legende'=>'oui' , 'marge_min'=>5 , 'cases_nb'=>5 , 'cases_largeur'=>5 );
			case 'grille_referentiel' :
				return array( 'orientation'=>'portrait' , 'couleur'=>'oui' , 'legende'=>'oui' , 'marge_min'=>5 , 'cases_nb'=>3 , 'cases_largeur'=>5 );
			case 'cartouche' :
				return array( 'orientation'=>'portrait' , 'couleur'=>'oui' , 'marge_min'=>5 , 'cart_contenu'=>'AVEC_nom_SANS_result' , 'cart_detail'=>'complet' );
		}
	}
}

/**
 * Sauver un cookie avec des options de mise en page pdf
 * 
 * @param string $page
 * @return void
 */

 function save_cookie_select($page)
{
	switch($page)
	{
		case 'releve_synthese' :
			global $couleur,$legende;
			$tab_cookie = array('couleur'=>$couleur,'legende'=>$legende);
			break;
		case 'releve_items' :
		case 'grille_referentiel' :
			global $orientation,$couleur,$legende,$marge_min,$cases_nb,$cases_largeur;
			$tab_cookie = array('orientation'=>$orientation,'couleur'=>$couleur,'legende'=>$legende,'marge_min'=>$marge_min,'cases_nb'=>$cases_nb,'cases_largeur'=>$cases_largeur);
			break;
			case 'cartouche' :
			global $orientation,$couleur,$legende,$marge_min,$contenu,$detail;
			$tab_cookie = array('orientation'=>$orientation,'couleur'=>$couleur,'legende'=>$legende,'marge_min'=>$marge_min,'cart_contenu'=>$contenu,'cart_detail'=>$detail);
			break;
	}
	/*
		Remarque : il y a un problème de serialize avec les type float : voir http://fr2.php.net/manual/fr/function.serialize.php#85988
		Dans ce cas il faut remplacer
		serialize($tab_cookie)
		par
		preg_replace( '/d:([0-9]+(\.[0-9]+)?([Ee][+-]?[0-9]+)?);/e', "'d:'.(round($1,9)).';'", serialize($tab_cookie) );
	*/
	$filename = './__tmp/cookie/etabl'.$_SESSION['BASE'].'_user'.$_SESSION['USER_ID'].'_'.$page.'.txt';
	Ecrire_Fichier($filename,serialize($tab_cookie));
}

/**
 * Afficher un élément select de formulaire à partir d'un tableau de données et d'options
 * 
 * @param array             $DB_TAB       tableau des données [valeur texte]
 * @param string|bool       $select_nom   chaine à utiliser pour l'id/nom du select, ou false si on retourne juste les options sans les encapsuler dans un select
 * @param string            $option_first 1ère option éventuelle [non] [oui] [val]
 * @param string|bool|array $selection    préselection éventuelle [false] [true] [val] [ou $...] [ou array(...)]
 * @param string            $optgroup     regroupement d'options éventuel [non] [oui]
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
			// ... sélectionner une ou plusieurs option ; soit $selection contient la valeur / le tableau à sélectionner soit elle a été définie avant
			$selection = ($selection=='val') ? $GLOBALS['select_option_selected'] : $selection ;
			if(!is_array($selection))
			{
				$options = str_replace('value="'.$selection.'"' , 'value="'.$selection.'" selected="selected"' , $options);
			}
			else
			{
				foreach($selection as $selection_val)
				{
					$options = str_replace('value="'.$selection_val.'"' , 'value="'.$selection_val.'" selected="selected"' , $options);
				}
			}
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