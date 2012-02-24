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

class Formulaire {

private static $dossier_cookie = '';
private static $fichier_cookie = '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Tableaux prédéfinis
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

public static $tab_select_tri_objet = array(
	array('valeur'=>'eleve' , 'texte'=>'élèves en lignes (triés pour un item donné)') ,
	array('valeur'=>'item'  , 'texte'=>'items en lignes (triés pour un élève donné)')
);

public static $tab_select_tri_mode = array(
	array('valeur'=>'score' , 'texte'=>'tri par score d\'acquisition (pour un tri unique)') ,
	array('valeur'=>'etat'  , 'texte'=>'tri par état d\'acquisition (pour un tri multiple)')
);

public static $tab_select_orientation = array(
	array('valeur'=>'portrait'  , 'texte'=>'Portrait (vertical)') ,
	array('valeur'=>'landscape' , 'texte'=>'Paysage (horizontal)')
);

public static $tab_select_marge_min = array(
	array('valeur'=>5  , 'texte'=>'marges de 5 mm') ,
	array('valeur'=>6  , 'texte'=>'marges de 6 mm') ,
	array('valeur'=>7  , 'texte'=>'marges de 7 mm') ,
	array('valeur'=>8  , 'texte'=>'marges de 8 mm') ,
	array('valeur'=>9  , 'texte'=>'marges de 9 mm') ,
	array('valeur'=>10 , 'texte'=>'marges de 10 mm') ,
	array('valeur'=>11 , 'texte'=>'marges de 11 mm') ,
	array('valeur'=>12 , 'texte'=>'marges de 12 mm') ,
	array('valeur'=>13 , 'texte'=>'marges de 13 mm') ,
	array('valeur'=>14 , 'texte'=>'marges de 14 mm') ,
	array('valeur'=>15 , 'texte'=>'marges de 15 mm')
);

public static $tab_select_pages_nb = array(
	array('valeur'=>'optimise' , 'texte'=>'nombre de pages optimisé') ,
	array('valeur'=>'augmente' , 'texte'=>'nombre de pages augmenté')
);

public static $tab_select_couleur = array(
	array('valeur'=>'oui' , 'texte'=>'en couleurs') ,
	array('valeur'=>'non' , 'texte'=>'en niveaux de gris')
);

public static $tab_select_legende = array(
	array('valeur'=>'oui' , 'texte'=>'avec légende') ,
	array('valeur'=>'non' , 'texte'=>'sans légende')
);

public static $tab_select_cases_nb = array(
	array('valeur'=>1  , 'texte'=>'1 case') ,
	array('valeur'=>2  , 'texte'=>'2 cases') ,
	array('valeur'=>3  , 'texte'=>'3 cases') ,
	array('valeur'=>4  , 'texte'=>'4 cases') ,
	array('valeur'=>5  , 'texte'=>'5 cases') ,
	array('valeur'=>6  , 'texte'=>'6 cases') ,
	array('valeur'=>7  , 'texte'=>'7 cases') ,
	array('valeur'=>8  , 'texte'=>'8 cases') ,
	array('valeur'=>9  , 'texte'=>'9 cases') ,
	array('valeur'=>10 , 'texte'=>'10 cases') ,
	array('valeur'=>11 , 'texte'=>'11 cases') ,
	array('valeur'=>12 , 'texte'=>'12 cases') ,
	array('valeur'=>13 , 'texte'=>'13 cases') ,
	array('valeur'=>14 , 'texte'=>'14 cases') ,
	array('valeur'=>15 , 'texte'=>'15 cases')
);

public static $tab_select_cases_size = array(
	array('valeur'=>5  , 'texte'=>'5 mm') ,
	array('valeur'=>6  , 'texte'=>'6 mm') ,
	array('valeur'=>7  , 'texte'=>'7 mm') ,
	array('valeur'=>8  , 'texte'=>'8 mm') ,
	array('valeur'=>9  , 'texte'=>'9 mm') ,
	array('valeur'=>10 , 'texte'=>'10 mm') ,
	array('valeur'=>11 , 'texte'=>'11 mm') ,
	array('valeur'=>12 , 'texte'=>'12 mm') ,
	array('valeur'=>13 , 'texte'=>'13 mm') ,
	array('valeur'=>14 , 'texte'=>'14 mm') ,
	array('valeur'=>15 , 'texte'=>'15 mm')
);

public static $tab_select_remplissage = array(
	array('valeur'=>'vide'  , 'texte'=>'sans les dernières notes') ,
	array('valeur'=>'plein' , 'texte'=>'avec les dernières notes')
);

public static $tab_select_colonne_bilan = array(
	array('valeur'=>'non' , 'texte'=>'sans colonne bilan') ,
	array('valeur'=>'oui' , 'texte'=>'avec colonne bilan')
);

public static $tab_select_colonne_vide = array(
	array('valeur'=>0   , 'texte'=>'sans colonne supplémentaire') ,
	array('valeur'=>30  , 'texte'=>'avec une colonne vide de 1cm') ,
	array('valeur'=>30  , 'texte'=>'avec une colonne vide de 3cm') ,
	array('valeur'=>50  , 'texte'=>'avec une colonne vide de 5cm') ,
	array('valeur'=>70  , 'texte'=>'avec une colonne vide de 7cm') ,
	array('valeur'=>90  , 'texte'=>'avec une colonne vide de 9cm')
);

public static $tab_select_cart_contenu = array(
	array('valeur'=>'SANS_nom_SANS_result' , 'texte'=>'cartouche SANS les noms d\'élèves et SANS les résultats') ,
	array('valeur'=>'AVEC_nom_SANS_result' , 'texte'=>'cartouche AVEC les noms d\'élèves mais SANS les résultats') ,
	array('valeur'=>'AVEC_nom_AVEC_result' , 'texte'=>'cartouche AVEC les noms d\'élèves et AVEC les résultats (si saisis)')
);

public static $tab_select_cart_detail = array(
	array('valeur'=>'complet' , 'texte'=>'cartouche avec la dénomination complète de chaque item') ,
	array('valeur'=>'minimal' , 'texte'=>'cartouche minimal avec uniquement les références des items')
);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Variables utilisées pouvant être initialisés lors d'une requête puis utilisées lors de la construction du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

public static $tab_select_option_first = array();
public static $tab_select_optgroup     = array();
public static $select_option_selected  = '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	propriété...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

public static $tab_choix = array();

/**
 * Initialiser deux propriétés...
 * 
 * @param void
 * @return void
 */
private function init_variables()
{
	Formulaire::$dossier_cookie = './__tmp/cookie/'.$_SESSION['BASE'];
	Formulaire::$fichier_cookie = Formulaire::$dossier_cookie.'/user'.$_SESSION['USER_ID'].'.txt';
}

/**
 * Initialiser les choix d'un formulaire (certains choix sont présélectionnés ou imposés suivant les statuts).
 * Ce tableau sera ensuite surchargé avec les choix mémorisés éventuels (enregistré dans un fichier texte).
 * En cas d'ajout ultérieur d'une fonctionnalité, compléter cette fonction permet de ne pas générer d'erreur.
 * 
 * @param void
 * @return void
 */
private function init_tab_choix()
{
	Formulaire::init_variables();
	$check_type_individuel = (in_array($_SESSION['USER_PROFIL'],array('parent','eleve'))) ? 1 : 0 ;
	$check_bilan_MS        = ( (in_array($_SESSION['USER_PROFIL'],array('directeur','professeur'))) || (mb_substr_count($_SESSION['DROIT_BILAN_MOYENNE_SCORE'],$_SESSION['USER_PROFIL'])) ) ? 1 : 0 ;
	$check_bilan_PA        = ( (in_array($_SESSION['USER_PROFIL'],array('directeur','professeur'))) || (mb_substr_count($_SESSION['DROIT_BILAN_POURCENTAGE_ACQUIS'],$_SESSION['USER_PROFIL'])) ) ? 1 : 0 ;
	$check_conv_sur20      = (mb_substr_count($_SESSION['DROIT_BILAN_NOTE_SUR_VINGT'],$_SESSION['USER_PROFIL'])) ? 1 : 0 ;
	$check_aff_lien        = (in_array($_SESSION['USER_PROFIL'],array('parent','eleve'))) ? 1 : 0 ;
	Formulaire::$tab_choix = array(
		'matiere_id'=>0 ,
		'niveau_id'=>0 ,
		'palier_id'=>0 ,
		'orientation'=>'portrait' ,
		'couleur'=>'oui' ,
		'legende'=>'oui' , 
		'marge_min'=>5 ,
		'pages_nb'=>'optimise' ,
		'cart_contenu'=>'AVEC_nom_SANS_result' ,
		'cart_detail'=>'complet' ,
		'only_socle'=>0 ,
		'only_niveau'=>0 ,
		'aff_coef'=>0 ,
		'aff_socle'=>1 ,
		'aff_lien'=>$check_aff_lien ,
		'aff_domaine'=>0 ,
		'aff_theme'=>0 ,
		'cases_nb'=>4 ,
		'cases_largeur'=>5 ,
		'remplissage'=>'plein' ,
		'colonne_bilan'=>'oui' ,
		'colonne_vide'=>0 ,
		'type_generique'=>0 ,
		'type_individuel'=>$check_type_individuel ,
		'type_synthese'=>0 ,
		'type_bulletin'=>0 ,
		'aff_bilan_MS'=>$check_bilan_MS ,
		'aff_bilan_PA'=>$check_bilan_PA ,
		'aff_conv_sur20'=>$check_conv_sur20 ,
		'tableau_tri_objet'=>'eleve',
		'tableau_tri_mode'=>'score',
		'retroactif'=>'oui' ,
		'mode_synthese'=>'predefini' ,
		'aff_socle_PA'=>1 ,
		'aff_socle_EV'=>1 ,
		'type'=>'' ,
		'mode'=>'auto'
	);
}

/**
 * Charger les choix mémorisées d'un formulaire.
 * 
 * On commence par initialiser les valeurs (certains choix sont présélectionnés ou imposés suivant les statuts).
 * Puis on surcharge avec un choix mémorisé éventuel (enregistré dans un fichier texte.
 * 
 * @param void
 * @return void
 */

public function load_choix_memo()
{
	Formulaire::init_tab_choix();
	// Récupération du contenu du "cookie"
	if(is_file(Formulaire::$fichier_cookie))
	{
		$contenu = file_get_contents(Formulaire::$fichier_cookie);
		$tab_choix_cookie = @unserialize($contenu);
		if(is_array($tab_choix_cookie))
		{
			Formulaire::$tab_choix = array_merge( Formulaire::$tab_choix , $tab_choix_cookie );
		}
	}
}

/**
 * Enregistrer les choix mémorisées d'un formulaire.
 * 
 * @param string $page
 * @return void
 */

 public function save_choix($page)
{
	switch($page)
	{
		case 'cartouche' :
			global $orientation,$couleur,$legende,$marge_min,$cart_contenu,$cart_detail;
			$tab_choix_new = compact('orientation','couleur','legende','marge_min','cart_contenu','cart_detail');
			break;
		case 'grille_referentiel' :
			global $matiere_id,$niveau_id,$type_generique,$type_individuel,$type_synthese,$tableau_tri_objet,$tableau_tri_mode,$only_socle,$aff_coef,$aff_socle,$aff_lien,$cases_nb,$cases_largeur,$remplissage,$colonne_bilan,$colonne_vide,$orientation,$couleur,$legende,$marge_min;
			$tab_choix_new = compact('matiere_id','niveau_id','type_generique','type_individuel','type_synthese','only_socle','aff_coef','aff_socle','aff_lien','cases_nb','cases_largeur','remplissage','colonne_bilan','colonne_vide','orientation','couleur','legende','marge_min');
			break;
		case 'items_matiere' :
			global $matiere_id,$type_individuel,$type_synthese,$type_bulletin,$aff_bilan_MS,$aff_bilan_PA,$aff_conv_sur20,$tableau_tri_objet,$tableau_tri_mode,$retroactif,$only_socle,$aff_coef,$aff_socle,$aff_lien,$aff_domaine,$aff_theme,$cases_nb,$cases_largeur,$orientation,$couleur,$legende,$marge_min,$pages_nb;
			$tab_choix_new = compact('matiere_id','type_individuel','type_synthese','type_bulletin','aff_bilan_MS','aff_bilan_PA','aff_conv_sur20','tableau_tri_objet','tableau_tri_mode','retroactif','only_socle','aff_coef','aff_socle','aff_lien','aff_domaine','aff_theme','cases_nb','cases_largeur','orientation','couleur','legende','marge_min','pages_nb');
			break;
		case 'items_selection' :
			global $type_individuel,$type_synthese,$aff_bilan_MS,$aff_bilan_PA,$aff_conv_sur20,$tableau_tri_objet,$tableau_tri_mode,$retroactif,$aff_coef,$aff_socle,$aff_lien,$aff_domaine,$aff_theme,$cases_nb,$cases_largeur,$orientation,$couleur,$legende,$marge_min,$pages_nb;
			$tab_choix_new = compact('type_individuel','type_synthese','aff_bilan_MS','aff_bilan_PA','aff_conv_sur20','tableau_tri_objet','tableau_tri_mode','retroactif','aff_coef','aff_socle','aff_lien','aff_domaine','aff_theme','cases_nb','cases_largeur','orientation','couleur','legende','marge_min','pages_nb');
			break;
		case 'items_multimatiere' :
			global $aff_bilan_MS,$aff_bilan_PA,$aff_conv_sur20,$retroactif,$only_socle,$aff_coef,$aff_socle,$aff_lien,$aff_domaine,$aff_theme,$cases_nb,$cases_largeur,$orientation,$couleur,$legende,$marge_min,$pages_nb;
			$tab_choix_new = compact('aff_bilan_MS','aff_bilan_PA','aff_conv_sur20','retroactif','only_socle','aff_coef','aff_socle','aff_lien','aff_domaine','aff_theme','cases_nb','cases_largeur','orientation','couleur','legende','marge_min','pages_nb');
			break;
		case 'synthese_matiere' :
			global $matiere_id,$mode_synthese,$retroactif,$only_socle,$only_niveau,$aff_coef,$aff_socle,$aff_lien,$couleur,$legende;
			$tab_choix_new = compact('matiere_id','mode_synthese','retroactif','only_socle','only_niveau','aff_coef','aff_socle','aff_lien','couleur','legende');
			break;
		case 'synthese_multimatiere' :
			global $retroactif,$only_socle,$only_niveau,$aff_coef,$aff_socle,$aff_lien,$couleur,$legende;
			$tab_choix_new = compact('retroactif','only_socle','only_niveau','aff_coef','aff_socle','aff_lien','couleur','legende');
			break;
		case 'releve_socle' :
			global $palier_id,$aff_coef,$aff_socle,$aff_lien,$aff_socle_PA,$aff_socle_EV,$mode;
			$tab_choix_new = compact('palier_id','aff_coef','aff_socle','aff_lien','aff_socle_PA','aff_socle_EV','mode');
		case 'synthese_socle' :
			global $palier_id,$type,$mode;
			$tab_choix_new = compact('palier_id','type','mode');
			break;
		case 'matiere' :
			global $matiere_id;
			$tab_choix_new = compact('matiere_id');
			break;
		case 'palier' :
			global $palier_id;
			$tab_choix_new = compact('palier_id');
			break;
		case 'validation_socle_item' :
			global $palier_id,$mode;
			$tab_choix_new = compact('palier_id','mode');
			break;
		default :
			$tab_choix_new = array();
	}
	// Récupération du contenu du "cookie", surchargé avec les choix effectués
	Formulaire::load_choix_memo();
	Formulaire::$tab_choix = array_merge( Formulaire::$tab_choix , $tab_choix_new );
	// Si le dossier n'existe pas, on le créé (possible car au début tous les Cookies des établissements étaient dans un même dossier commun).
	if(!is_dir(Formulaire::$dossier_cookie))
	{
		Creer_Dossier(Formulaire::$dossier_cookie);
		Ecrire_Fichier(Formulaire::$dossier_cookie.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	}
	/*
		Remarque : il y a un problème de serialize avec les type float : voir http://fr2.php.net/manual/fr/function.serialize.php#85988
		Dans ce cas il faut remplacer
		serialize(Formulaire::$tab_choix)
		par
		preg_replace( '/d:([0-9]+(\.[0-9]+)?([Ee][+-]?[0-9]+)?);/e', "'d:'.(round($1,9)).';'", serialize(Formulaire::$tab_choix) );
	*/
	Ecrire_Fichier(Formulaire::$fichier_cookie,serialize(Formulaire::$tab_choix));
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
public function afficher_select($DB_TAB,$select_nom,$option_first,$selection,$optgroup)
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
		list($option_valeur,$option_texte,$option_class) = Formulaire::$tab_select_option_first;
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
				$options .= '<optgroup label="'.html(Formulaire::$tab_select_optgroup[$group_key]).'">'.implode('',$tab_group_options).'</optgroup>';
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
			$options = str_replace('<option' , '<option selected' , $options);
		}
		else
		{
			// ... sélectionner une ou plusieurs option ; soit $selection contient la valeur / le tableau à sélectionner soit elle a été définie avant
			$selection = ($selection=='val') ? Formulaire::$select_option_selected : $selection ;
			if(!is_array($selection))
			{
				$options = str_replace('value="'.$selection.'"' , 'value="'.$selection.'" selected' , $options);
			}
			else
			{
				foreach($selection as $selection_val)
				{
					$options = str_replace('value="'.$selection_val.'"' , 'value="'.$selection_val.'" selected' , $options);
				}
			}
		}
	}
	// Si $DB_TAB n'est pas un tableau alors c'est une chaine avec un message d'erreur affichée sous la forme d'une option disable
	else
	{
		$options .= '<option value="" disabled>'.$DB_TAB.'</option>';
	}
	// On insère dans un select si demandé
	return ($select_nom) ? '<select id="'.$select_nom.'" name="'.$select_nom.'">'.$options.'</select>' : $options ;
}

}
?>