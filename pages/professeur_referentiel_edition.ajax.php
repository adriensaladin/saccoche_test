<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['action']!='Voir')){exit('Action désactivée pour la démo...');}

$action      = (isset($_POST['action']))      ? Clean::texte($_POST['action'])       : '';
$contexte    = (isset($_POST['contexte']))    ? Clean::texte($_POST['contexte'])     : '';  // n1 | n2 | n3
$granulosite = (isset($_POST['granulosite'])) ? Clean::texte($_POST['granulosite'])  : '';  // referentiel | domaine | theme
$matiere_id  = (isset($_POST['matiere']))     ? Clean::entier($_POST['matiere'])     : 0;
$matiere_nom = (isset($_POST['matiere_nom'])) ? Clean::texte($_POST['matiere_nom'])  : '';
$element_id  = (isset($_POST['element']))     ? Clean::entier($_POST['element'])     : 0;
$element2_id = (isset($_POST['element2']))    ? Clean::entier($_POST['element2'])    : 0;
$parent_id   = (isset($_POST['parent']))      ? Clean::entier($_POST['parent'])      : 0;
$ordre       = (isset($_POST['ordre']))       ? Clean::entier($_POST['ordre'])       : -1;
$ref         = (isset($_POST['ref']))         ? Clean::texte($_POST['ref'])          : '';
$nom         = (isset($_POST['nom']))         ? Clean::texte($_POST['nom'])          : '';
$nom2        = (isset($_POST['nom2']))        ? Clean::texte($_POST['nom2'])         : '';
$coef        = (isset($_POST['coef']))        ? Clean::entier($_POST['coef'])        : -1;
$cart        = (isset($_POST['cart']))        ? Clean::entier($_POST['cart'])        : -1;
$socle_id    = (isset($_POST['socle']))       ? Clean::entier($_POST['socle'])       : -1;

$tab_id = (isset($_POST['tab_id'])) ? Clean::map_entier(explode(',',$_POST['tab_id'])) : array() ;
$tab_id = array_filter($tab_id,'positif');
$tab_id2 = (isset($_POST['tab_id2'])) ? Clean::map_entier(explode(',',$_POST['tab_id2'])) : array() ;
$tab_id2 = array_filter($tab_id2,'positif');

$tab_contexte    = array( 'n1'=>'domaine' , 'n2'=>'theme' , 'n3'=>'item' );
$tab_granulosite = array( 'referentiel' , 'domaine' , 'theme' );

function notifications_referentiel_edition($matiere_id,$notification_contenu)
{
  $abonnement_ref = 'referentiel_edition';
  $listing_profs = DB_STRUCTURE_REFERENTIEL::DB_recuperer_autres_professeurs_matiere( $matiere_id, $_SESSION['USER_ID'] );
  if($listing_profs)
  {
    $listing_abonnes = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_listing_id( $abonnement_ref , $listing_profs );
    if($listing_abonnes)
    {
      $tab_abonnes = explode(',',$listing_abonnes);
      foreach($tab_abonnes as $abonne_id)
      {
        DB_STRUCTURE_NOTIFICATION::DB_modifier_log_attente( $abonne_id , $abonnement_ref , 0 , NULL , $notification_contenu , 'compléter' , FALSE /*sep*/ );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Lister des référentiels ou domaines ou thèmes auquel un prof a accès (pour un formulaire select)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='lister_options') && in_array($granulosite,$tab_granulosite) )
{
  $listing_id_matieres_autorisees = (isset($_POST['id_matieres'])) ? implode(',',Clean::map_entier(explode(',',$_POST['id_matieres']))) : '0' ;
  exit( HtmlForm::afficher_select( DB_STRUCTURE_REFERENTIEL::DB_OPT_lister_elements_referentiels_prof( $_SESSION['USER_ID'] , $granulosite , $listing_id_matieres_autorisees ) , FALSE /*select_nom*/ , '' /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/ ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les référentiels d'une matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='Voir') && $matiere_id )
{
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence( 0 /*prof_id*/ , $matiere_id , 0 /*niveau_id*/ , FALSE /*only_socle*/ , FALSE /*only_item*/ , TRUE /*socle_nom*/ );
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
      $socle_texte = '<img src="./_img/etat/socle_'.$socle_image.'.png" alt="" title="'.$socle_nom.'" data-id="'.$DB_ROW['entree_id'].'" />';
      $lien_image  = ($DB_ROW['item_lien']) ? 'oui' : 'non' ;
      $lien_nom    = ($DB_ROW['item_lien']) ? html($DB_ROW['item_lien']) : 'Absence de ressource.' ;
      $lien_texte  = '<img src="./_img/etat/link_'.$lien_image.'.png" alt="" title="'.$lien_nom.'" />';
      $tab_item[$niveau_id][$domaine_id][$theme_id][$item_id] = $coef_texte.$cart_texte.$socle_texte.$lien_texte.html($DB_ROW['item_nom']);
    }
  }
  $images_niveau  = '';
  $images_niveau .= '<q class="n1_add" data-action="add" title="Ajouter un domaine au début de ce niveau."></q>';
  $images_domaine  = '';
  $images_domaine .= '<q class="n1_edit" data-action="edit" title="Renommer ce domaine (avec sa référence)."></q>';
  $images_domaine .= '<q class="n1_add"  data-action="add"  title="Ajouter un domaine à la suite."></q>';
  $images_domaine .= '<q class="n1_move" data-action="move" title="Déplacer ce domaine."></q>';
  $images_domaine .= '<q class="n1_del"  data-action="del"  title="Supprimer ce domaine ainsi que tout son contenu."></q>';
  $images_domaine .= '<q class="n2_add"  data-action="add"  title="Ajouter un thème au début de ce domaine (et renuméroter)."></q>';
  $images_theme  = '';
  $images_theme .= '<q class="n2_edit" data-action="edit" title="Renommer ce thème."></q>';
  $images_theme .= '<q class="n2_add"  data-action="add"  title="Ajouter un thème à la suite (et renuméroter)."></q>';
  $images_theme .= '<q class="n2_move" data-action="move" title="Déplacer ce thème (et renuméroter)."></q>';
  $images_theme .= '<q class="n2_del"  data-action="del"  title="Supprimer ce thème ainsi que tout son contenu (et renuméroter)."></q>';
  $images_theme .= '<q class="n3_add"  data-action="add"  title="Ajouter un item au début de ce thème (et renuméroter)."></q>';
  $images_item  = '';
  $images_item .= '<q class="n3_edit" data-action="edit" title="Renommer, coefficienter, autoriser, lier cet item."></q>';
  $images_item .= '<q class="n3_add"  data-action="add"  title="Ajouter un item à la suite (et renuméroter)."></q>';
  $images_item .= '<q class="n3_move" data-action="move" title="Déplacer cet item (et renuméroter)."></q>';
  $images_item .= '<q class="n3_fus"  data-action="fus"  title="Fusionner avec un autre item (et renuméroter)."></q>';
  $images_item .= '<q class="n3_del"  data-action="del"  title="Supprimer cet item (et renuméroter)."></q>';
  echo'<ul class="ul_m1">'.NL;
  if(count($tab_niveau))
  {
    foreach($tab_niveau as $niveau_id => $niveau_nom)
    {
      echo  '<li class="li_m2" id="m2_'.$niveau_id.'"><span>'.html($niveau_nom).'</span>'.$images_niveau.NL;
      echo    '<ul class="ul_n1">'.NL;
      if(isset($tab_domaine[$niveau_id]))
      {
        foreach($tab_domaine[$niveau_id] as $domaine_id => $domaine_nom)
        {
          echo      '<li class="li_n1" id="n1_'.$domaine_id.'"><span>'.html($domaine_nom).'</span>'.$images_domaine.NL;
          echo        '<ul class="ul_n2">'.NL;
          if(isset($tab_theme[$niveau_id][$domaine_id]))
          {
            foreach($tab_theme[$niveau_id][$domaine_id] as $theme_id => $theme_nom)
            {
              echo          '<li class="li_n2" id="n2_'.$theme_id.'"><span>'.html($theme_nom).'</span>'.$images_theme.NL;
              echo            '<ul class="ul_n3">'.NL;
              if(isset($tab_item[$niveau_id][$domaine_id][$theme_id]))
              {
                foreach($tab_item[$niveau_id][$domaine_id][$theme_id] as $item_id => $item_nom)
                {
                  echo              '<li class="li_n3" id="n3_'.$item_id.'"><b>'.$item_nom.'</b>'.$images_item.'</li>'.NL;
                }
              }
              echo            '</ul>'.NL;
              echo          '</li>'.NL;
            }
          }
          echo        '</ul>'.NL;
          echo      '</li>'.NL;
        }
      }
      echo    '</ul>'.NL;
      echo  '</li>'.NL;
    }
  }
  echo'</ul>'.NL;
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un domaine / un thème / un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='add') && isset($tab_contexte[$contexte]) && $matiere_id && $matiere_nom && $parent_id && ($ref || ($contexte!='n1')) && $nom && ($ordre!=-1) && ($socle_id!=-1) && ($coef!=-1) && ($cart!=-1) )
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
    DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_liste_elements($tab_contexte[$contexte],$tab_id,'+1');
  }
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a ajouté dans le référentiel ['.$matiere_nom.'] :'."\r\n".$tab_contexte[$contexte].' "'.$nom.'"'."\r\n";
  notifications_referentiel_edition( $matiere_id , $notification_contenu );
  // Retour
  exit($contexte.'_'.$element_id);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Renommer un domaine / un thème / un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='edit') && isset($tab_contexte[$contexte]) && $matiere_id && $element_id && ($ref || ($contexte!='n1')) && $nom && $matiere_nom && ($socle_id!=-1) && ($coef!=-1) && ($cart!=-1) )
{
  switch($contexte)
  {
    case 'n1' : $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_domaine($element_id /*domaine*/,$ref,$nom); break;
    case 'n2' : $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_theme($element_id /*theme*/,$nom); break;
    case 'n3' : $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_item($element_id /*item*/,$socle_id,$nom,$coef,$cart); break;
  }
  if(!$test_modif)
  {
    exit('Contenu inchangé ou élément non trouvé !');
  }
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a modifié dans le référentiel ['.$matiere_nom.'] :'."\r\n".$tab_contexte[$contexte].' "'.$nom.'"'."\r\n";
  notifications_referentiel_edition( $matiere_id , $notification_contenu );
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer un domaine (avec son contenu) / un thème (avec son contenu) / un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='del') && isset($tab_contexte[$contexte]) && $matiere_id && $element_id && $matiere_nom && $nom )
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
    DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_liste_elements($tab_contexte[$contexte],$tab_id,'-1');
  }
  // Log de l'action
  SACocheLog::ajouter('Suppression d\'un élément de référentiel ('.$matiere_nom.' / '.$tab_contexte[$contexte].' / '.$nom.').');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé dans le référentiel ['.$matiere_nom.'] :'."\r\n".$tab_contexte[$contexte].' "'.$nom.'"'."\r\n";
  notifications_referentiel_edition( $matiere_id , $notification_contenu );
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_sensible($notification_contenu);
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Déplacer un domaine / un thème / un item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='move') && isset($tab_contexte[$contexte]) && $matiere_id && $element_id && ($ordre!=-1) && $parent_id && $matiere_nom && $nom )
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
    DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_liste_elements($tab_contexte[$contexte],$tab_id,'-1');
  }
  if(count($tab_id2)) // id des éléments suivants l'emplacement d'arrivée à renuméroter
  {
    DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_liste_elements($tab_contexte[$contexte],$tab_id2,'+1');
  }
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a déplacé dans le référentiel ['.$matiere_nom.'] :'."\r\n".$tab_contexte[$contexte].' "'.$nom.'"'."\r\n";
  notifications_referentiel_edition( $matiere_id , $notification_contenu );
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fusionner un item en l'absorbant par un 2nd item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='fus') && $element_id && $element2_id && $matiere_id && $matiere_nom && $nom && $nom2 )
{
  $test_delete = DB_STRUCTURE_REFERENTIEL::DB_supprimer_referentiel_item($element_id,FALSE /*with_notes*/);
  if(!$test_delete)
  {
    exit('Élément non trouvé !');
  }
  if(count($tab_id)) // id des éléments suivants à renuméroter
  {
    DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_liste_elements('item',$tab_id,'-1');
  }
  // Mettre à jour les références vers l'item absorbant
  DB_STRUCTURE_REFERENTIEL::DB_fusionner_referentiel_items($element_id,$element2_id);
  // Log de l'action
  SACocheLog::ajouter('Fusion d\'éléments de référentiel (item / '.$element_id.' / '.$element2_id.').');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a fusionné dans le référentiel ['.$matiere_nom.'] :'."\r\n".$nom.' -> '.$nom2."\r\n";
  notifications_referentiel_edition( $matiere_id , $notification_contenu );
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_sensible($notification_contenu);
  // Retour
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Actions complémentaires
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='action_complementaire')
{
  // Récupération des données
  $action_groupe       = (isset($_POST['select_action_groupe']))                     ? Clean::texte($_POST['select_action_groupe'])                     : '';
  $granulosite         = (isset($_POST['select_action_groupe_modifier_objet']))      ? Clean::texte($_POST['select_action_groupe_modifier_objet'])      : '';
  $modifier_id         = (isset($_POST['select_action_groupe_modifier_id']))         ? Clean::texte($_POST['select_action_groupe_modifier_id'])         : '';
  $modifier_coef       = (isset($_POST['select_action_groupe_modifier_coef']))       ? Clean::entier($_POST['select_action_groupe_modifier_coef'])      : -1;
  $modifier_cart       = (isset($_POST['select_action_groupe_modifier_cart']))       ? Clean::entier($_POST['select_action_groupe_modifier_cart'])      : -1;
  $deplacer_id_initial = (isset($_POST['select_action_groupe_deplacer_id_initial'])) ? Clean::texte($_POST['select_action_groupe_deplacer_id_initial']) : '';
  $deplacer_id_final   = (isset($_POST['select_action_groupe_deplacer_id_final']))   ? Clean::texte($_POST['select_action_groupe_deplacer_id_final'])   : '';
  $groupe_nom_initial  = (isset($_POST['groupe_nom_initial']))                       ? Clean::texte($_POST['groupe_nom_initial'])                       : '';
  $groupe_nom_final    = (isset($_POST['groupe_nom_final']))                         ? Clean::texte($_POST['groupe_nom_final'])                         : '';
  list($matiere_id        ,$parent_id        ,$objet_id        ,$objet_ordre        ) = Clean::map_entier(explode('_',$modifier_id))         + array(0,0,0,0);
  list($matiere_id_initial,$parent_id_initial,$objet_id_initial,$objet_ordre_initial) = Clean::map_entier(explode('_',$deplacer_id_initial)) + array(0,0,0,0);
  list($matiere_id_final  ,$parent_id_final  ,$objet_id_final  ,$objet_ordre_final  ) = Clean::map_entier(explode('_',$deplacer_id_final))   + array(0,0,0,0);
  // Vérification des données
  $tab_action_groupe   = array('modifier_coefficient','modifier_panier','deplacer_domaine','deplacer_theme');
  $test1 = ( ($action_groupe=='modifier_coefficient') && (in_array($granulosite,$tab_granulosite)) && ($matiere_id) && ($parent_id) && ($objet_id) && ($objet_ordre) && ($modifier_coef!=-1) ) ? TRUE : FALSE ;
  $test2 = ( ($action_groupe=='modifier_panier')      && (in_array($granulosite,$tab_granulosite)) && ($matiere_id) && ($objet_id) && ($objet_ordre) && ($modifier_cart!=-1) ) ? TRUE : FALSE ;
  $test3 = ( ($action_groupe=='deplacer_domaine')     && $matiere_id_initial && $parent_id_initial && $objet_id_initial && $objet_ordre_initial && $parent_id_final && $matiere_id_final && $objet_id_final && $objet_ordre_final && $groupe_nom_initial && $groupe_nom_final ) ? TRUE : FALSE ;
  $test4 = ( ($action_groupe=='deplacer_theme')       && $matiere_id_initial && $parent_id_initial && $objet_id_initial && $objet_ordre_initial && $parent_id_final && $matiere_id_final && $objet_id_final && $objet_ordre_final && $groupe_nom_initial && $groupe_nom_final ) ? TRUE : FALSE ;
  if( (!in_array($action_groupe,$tab_action_groupe)) || ( (!$test1) && (!$test2) && (!$test3) && (!$test4) ) )
  {
    exit('Erreur avec les données transmises !');
  }
  // cas 1/4 : modifier_coefficient
  if($action_groupe=='modifier_coefficient')
  {
    $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_items( $granulosite , $matiere_id , $objet_id , 'coef' , $modifier_coef );
    $message = ($test_modif) ? 'ok' : 'Contenu inchangé ou items non trouvés !';
    exit($message);
  }
  // cas 2/4 : modifier_panier
  if($action_groupe=='modifier_panier')
  {
    $test_modif = DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel_items( $granulosite , $matiere_id , $objet_id , 'cart' , $modifier_cart );
    $message = ($test_modif) ? 'ok' : 'Contenu inchangé ou items non trouvés !';
    exit($message);
  }
  // cas 3/4 : deplacer_domaine ; il pourra rester des associations items/matières obsolète dans la table sacoche_demande... ; il pourra y avoir des domaine_ref identiques...
  if($action_groupe=='deplacer_domaine')
  {
    $objet_ordre_final = DB_STRUCTURE_REFERENTIEL::DB_recuperer_domaine_ordre_max($matiere_id_final,$objet_id_final) + 1 ; // objet_id = niveau_id
    $test_move = DB_STRUCTURE_REFERENTIEL::DB_deplacer_referentiel_domaine($objet_id_initial /*domaine_id*/,$objet_id_final /*niveau_id*/,$objet_ordre_final /*domaine_ordre*/,$matiere_id_final /*matiere_id*/);
    if(!$test_move) { exit('Contenu inchangé ou élément non trouvé !'); }
    DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_domaines_suivants($matiere_id_initial /*matiere_id*/,$parent_id_initial /*niveau_id*/,$objet_ordre_initial /*ordre_id*/);
    // Notifications (rendues visibles ultérieurement)
    $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a déplacé un domaine vers un référentiel d\'une autre matière :'."\r\n".$groupe_nom_initial.' -> '.$groupe_nom_final."\r\n";
    notifications_referentiel_edition( $matiere_id_initial , $notification_contenu );
    exit('ok');
  }
  // cas 4/4 : deplacer_theme ; il pourra rester des associations items/matières obsolète dans la table sacoche_demande...
  if($action_groupe=='deplacer_theme')
  {
    $objet_ordre_final = DB_STRUCTURE_REFERENTIEL::DB_recuperer_theme_ordre_max($objet_id_final) + 1 ; // objet_id = domaine_id
    $test_move = DB_STRUCTURE_REFERENTIEL::DB_deplacer_referentiel_theme($objet_id_initial /*theme_id*/,$objet_id_final /*domaine_id*/,$objet_ordre_final /*theme_ordre*/);
    if(!$test_move) { exit('Contenu inchangé ou élément non trouvé !'); }
    DB_STRUCTURE_REFERENTIEL::DB_renumeroter_referentiel_themes_suivants($parent_id_initial /*domaine_id*/,$objet_ordre_initial /*ordre_id*/);
    // Notifications (rendues visibles ultérieurement)
    $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a déplacé un thème vers un domaine d\'une autre matière :'."\r\n".$groupe_nom_initial.' -> '.$groupe_nom_final."\r\n";
    notifications_referentiel_edition( $matiere_id_initial , $notification_contenu );
    exit('ok');
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
