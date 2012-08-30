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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['action']!='Voir')){exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['action']))     ? clean_texte($_POST['action'])     : '';
$contexte    = (isset($_POST['contexte']))   ? clean_texte($_POST['contexte'])   : '';	// n1 ou n2 ou n3
$matiere_id  = (isset($_POST['matiere']))    ? clean_entier($_POST['matiere'])   : 0;
$element_id  = (isset($_POST['element']))    ? clean_entier($_POST['element'])   : 0;
$element2_id = (isset($_POST['element2']))   ? clean_entier($_POST['element2'])  : 0;
$parent_id   = (isset($_POST['parent']))     ? clean_entier($_POST['parent'])    : 0;
$ordre       = (isset($_POST['ordre']))      ? clean_entier($_POST['ordre'])     : -1;
$ref         = (isset($_POST['ref']))        ? clean_texte($_POST['ref'])        : '';
$nom         = (isset($_POST['nom']))        ? clean_texte($_POST['nom'])        : '';
$coef        = (isset($_POST['coef']))       ? clean_entier($_POST['coef'])      : -1;
$cart        = (isset($_POST['cart']))       ? clean_entier($_POST['cart'])      : -1;
$socle_id    = (isset($_POST['socle']))      ? clean_entier($_POST['socle'])     : -1;

$tab_id = (isset($_POST['tab_id'])) ? array_map('clean_entier',explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
$tab_id2 = (isset($_POST['tab_id2'])) ? array_map('clean_entier',explode(',',$_POST['tab_id2'])) : array() ;
$tab_id2 = array_filter($tab_id2,'positif');

$tab_contexte = array( 'n1'=>'domaine' , 'n2'=>'theme' , 'n3'=>'item' );
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher les référentiels d'une matière
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Voir') && $matiere_id )
{
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id=0,$only_socle=false,$only_item=false,$socle_nom=true);
	$tab_niveau  = array();
	$tab_domaine = array();
	$tab_theme   = array();
	$tab_item    = array();
	$niveau_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if( (!is_null($DB_ROW['niveau_id'])) && ($DB_ROW['niveau_id']!=$niveau_id) )
		{
			$niveau_id = $DB_ROW['niveau_id'];
			$tab_niveau[$niveau_id] = $DB_ROW['niveau_nom'];
			$domaine_id = 0;
			$theme_id   = 0;
			$item_id    = 0;
		}
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$tab_domaine[$niveau_id][$domaine_id] = $DB_ROW['domaine_ref'].' - '.$DB_ROW['domaine_nom'];
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$tab_theme[$niveau_id][$domaine_id][$theme_id] = $DB_ROW['theme_nom'];
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
		{
			$item_id     = $DB_ROW['item_id'];
			$coef_texte  = '<img src="./_img/coef/'.sprintf("%02u",$DB_ROW['item_coef']).'.gif" alt="" title="Coefficient '.$DB_ROW['item_coef'].'." />';
			$cart_title  = ($DB_ROW['item_cart']) ? 'Demande possible.' : 'Demande interdite.' ;
			$cart_image  = ($DB_ROW['item_cart']) ? 'oui' : 'non' ;
			$cart_texte  = '<img src="./_img/etat/cart_'.$cart_image.'.png" title="'.$cart_title.'" />';
			$socle_image = ($DB_ROW['entree_id']) ? 'oui' : 'non' ;
			$socle_nom   = ($DB_ROW['entree_id']) ? html($DB_ROW['entree_nom']) : 'Hors-socle.' ;
			$socle_texte = '<img src="./_img/etat/socle_'.$socle_image.'.png" alt="" title="'.$socle_nom.'" lang="id_'.$DB_ROW['entree_id'].'" />';
			$lien_image  = ($DB_ROW['item_lien']) ? 'oui' : 'non' ;
			$lien_nom    = ($DB_ROW['item_lien']) ? html($DB_ROW['item_lien']) : 'Absence de ressource.' ;
			$lien_texte  = '<img src="./_img/etat/link_'.$lien_image.'.png" alt="" title="'.$lien_nom.'" />';
			$tab_item[$niveau_id][$domaine_id][$theme_id][$item_id] = $coef_texte.$cart_texte.$socle_texte.$lien_texte.html($DB_ROW['item_nom']);
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
	$images_item  = '';
	$images_item .= '<q class="n3_edit" lang="edit" title="Renommer, coefficienter, autoriser, lier cet item."></q>';
	$images_item .= '<q class="n3_add" lang="add" title="Ajouter un item à la suite (et renuméroter)."></q>';
	$images_item .= '<q class="n3_move" lang="move" title="Déplacer cet item (et renuméroter)."></q>';
	$images_item .= '<q class="n3_fus" lang="fus" title="Fusionner avec un autre item (et renuméroter)."></q>';
	$images_item .= '<q class="n3_del" lang="del" title="Supprimer cet item (et renuméroter)."></q>';
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
							if(isset($tab_item[$niveau_id][$domaine_id][$theme_id]))
							{
								foreach($tab_item[$niveau_id][$domaine_id][$theme_id] as $item_id => $item_nom)
								{
									echo'							<li class="li_n3" id="n3_'.$item_id.'"><b>'.$item_nom.'</b>'.$images_item.'</li>'."\r\n";
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
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ajouter un domaine / un thème / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='add') && (in_array($contexte,array('n1','n2','n3'))) && $matiere_id && $parent_id && ($ref || ($contexte!='n1')) && $nom && ($ordre!=-1) && ($socle_id!=-1) && ($coef!=-1) && ($cart!=-1) )
{
	switch($contexte)
	{
		case 'n1' : $element_id = DB_STRUCTURE_REFERENTIEL::DB_ajouter_referentiel_domaine($matiere_id,$parent_id /*niveau*/,$ordre,$ref,$nom); break;
		case 'n2' : $element_id = DB_STRUCTURE_REFERENTIEL::DB_ajouter_referentiel_theme($parent_id /*domaine*/,$ordre,$nom); break;
		case 'n3' : $element_id = DB_STRUCTURE_REFERENTIEL::DB_ajouter_referentiel_item($parent_id /*theme*/,$socle_id,$ordre,$nom,$coef,$cart); break;
	}
	// id des éléments suivants à renuméroter
	if(count($tab_id)) // id des éléments suivants à renuméroter
	{
		DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_elements($tab_contexte[$contexte],$tab_id,'+1');
	}
	exit($contexte.'_'.$element_id);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Renommer un domaine / un thème / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='edit') && (in_array($contexte,array('n1','n2','n3'))) && $element_id && ($ref || ($contexte!='n1')) && $nom && ($socle_id!=-1) && ($coef!=-1) && ($cart!=-1) )
{
	switch($contexte)
	{
		case 'n1' : $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_domaine($element_id /*domaine*/,$ref,$nom); break;
		case 'n2' : $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_theme($element_id /*theme*/,$nom); break;
		case 'n3' : $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_item($element_id /*item*/,$socle_id,$nom,$coef,$cart); break;
	}
	$message = ($test_modif) ? 'ok' : 'Contenu inchangé ou élément non trouvé !';
	exit($message);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Déplacer un domaine / un thème / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='move') && (in_array($contexte,array('n1','n2','n3'))) && $element_id && ($ordre!=-1) && $parent_id )
{
	switch($contexte)
	{
		case 'n1' : $test_move = DB_STRUCTURE_REFERENTIEL::DB_deplacer_referentiel_domaine($element_id /*domaine*/,$parent_id /*niveau*/,$ordre); break;
		case 'n2' : $test_move = DB_STRUCTURE_REFERENTIEL::DB_deplacer_referentiel_theme($element_id /*theme*/,$parent_id /*domaine*/,$ordre); break;
		case 'n3' : $test_move = DB_STRUCTURE_REFERENTIEL::DB_deplacer_referentiel_item($element_id /*item*/,$parent_id /*theme*/,$ordre); break;
	}
	if(!$test_move)
	{
		exit('Contenu inchangé ou élément non trouvé !');
	}
	if(count($tab_id)) // id des éléments suivants l'emplacement de départ à renuméroter
	{
		DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_elements($tab_contexte[$contexte],$tab_id,'-1');
	}
	if(count($tab_id2)) // id des éléments suivants l'emplacement d'arrivée à renuméroter
	{
		DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_elements($tab_contexte[$contexte],$tab_id2,'+1');
	}
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Supprimer un domaine (avec son contenu) / un thème (avec son contenu) / un item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='del') && (in_array($contexte,array('n1','n2','n3'))) && $element_id )
{
	switch($contexte)
	{
		case 'n1' : $test_delete = DB_STRUCTURE_REFERENTIEL::DB_supprimer_referentiel_domaine($element_id /*domaine*/); break;
		case 'n2' : $test_delete = DB_STRUCTURE_REFERENTIEL::DB_supprimer_referentiel_theme($element_id /*theme*/); break;
		case 'n3' : $test_delete = DB_STRUCTURE_REFERENTIEL::DB_supprimer_referentiel_item($element_id /*item*/); break;
	}
	if(!$test_delete)
	{
		exit('Élément non trouvé !');
	}
	if(count($tab_id)) // id des éléments suivants à renuméroter
	{
		DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_elements($tab_contexte[$contexte],$tab_id,'-1');
	}
	// Log de l'action
	ajouter_log_SACoche('Suppression d\'un élément de référentiel ('.$tab_contexte[$contexte].' / '.$element_id.').');
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Fusionner un item en l'absorbant par un 2nd item
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='fus') && $element_id && $element2_id )
{
	$test_delete = DB_STRUCTURE_REFERENTIEL::DB_supprimer_referentiel_item($element_id,FALSE /*with_notes*/);
	if(!$test_delete)
	{
		exit('Élément non trouvé !');
	}
	if(count($tab_id)) // id des éléments suivants à renuméroter
	{
		DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_elements('item',$tab_id,'-1');
	}
	// Mettre à jour les références vers l'item absorbant
	DB_STRUCTURE_REFERENTIEL::DB_fusionner_referentiel_items($element_id,$element2_id);
	// Log de l'action
	ajouter_log_SACoche('Fusion d\'éléments de référentiel (item / '.$element_id.' / '.$element2_id.').');
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
