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
if(!isset($BILAN_TYPE)) {exit('Ce fichier ne peut être appelé directement !');}

$tab_types = array
(
  'releve'   => array( 'droit'=>'RELEVE'   , 'doc'=>'officiel_releve_evaluations' , 'titre_page'=>html(Lang::_("Relevé d'évaluations")) , 'titre'=>"Relevé d'évaluations" , 'modif_rubrique'=>'les appréciations par matière' ) ,
  'bulletin' => array( 'droit'=>'BULLETIN' , 'doc'=>'officiel_bulletin_scolaire'  , 'titre_page'=>html(Lang::_("Bulletin scolaire"))    , 'titre'=>"Bulletin scolaire"    , 'modif_rubrique'=>'les notes et appréciations par matière' ) ,
  'palier1'  => array( 'droit'=>'SOCLE'    , 'doc'=>'officiel_maitrise_palier'    , 'titre_page'=>html(Lang::_("Maîtrise du palier 1")) , 'titre'=>"Maîtrise du palier 1" , 'modif_rubrique'=>'les appréciations par compétence' ) ,
  'palier2'  => array( 'droit'=>'SOCLE'    , 'doc'=>'officiel_maitrise_palier'    , 'titre_page'=>html(Lang::_("Maîtrise du palier 2")) , 'titre'=>"Maîtrise du palier 2" , 'modif_rubrique'=>'les appréciations par compétence' ) ,
  'palier3'  => array( 'droit'=>'SOCLE'    , 'doc'=>'officiel_maitrise_palier'    , 'titre_page'=>html(Lang::_("Maîtrise du palier 3")) , 'titre'=>"Maîtrise du palier 3" , 'modif_rubrique'=>'les appréciations par compétence' ) ,
);

$TITRE = $tab_types[$BILAN_TYPE]['titre_page'];

// Indication des profils pouvant modifier le statut d'un bilan
$profils_modifier_statut = 'administrateurs (de l\'établissement)<br />'.afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'],'br');
// Indication des profils ayant accès à l'appréciation générale
$profils_appreciation_generale = afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'],'br');
// Indication des profils ayant accès à l'impression PDF
$profils_impression_pdf = 'administrateurs (de l\'établissement)<br />'.afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'],'br');
// Indication des profils ayant accès aux copies des impressions PDF
$profils_archives_pdf = 'administrateurs (de l\'établissement)<br />'.afficher_profils_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_VOIR_ARCHIVE'],'br');

// Droit de modifier le statut d'un bilan (dans le cas PP, restera à affiner classe par classe...).
$affichage_formulaire_statut = ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT']) ;

$tab_etats = array
(
  '0absence'  => 'indéfini',
  '1vide'     => 'Vide (fermé)',
  '2rubrique' => 'Saisies Profs',
  '3mixte'    => 'Saisies Mixtes',
  '4synthese' => 'Saisie Synthèse',
  '5complet'  => 'Complet (fermé)',
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération et traitement des données postées, si formulaire soumis
// Pas de passage par la page ajax.php => protection contre attaques type CSRF ajoutée ici
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($affichage_formulaire_statut) && ($_SESSION['SESAMATH_ID']!=ID_DEMO) )
{
  $tab_ids  = (isset($_POST['listing_ids']))  ? explode(',',$_POST['listing_ids']) : array() ;
  $new_etat = (isset($_POST['etat']))         ? Clean::texte($_POST['etat'])       : '' ;
  $discret  = (isset($_POST['mode_discret'])) ? TRUE                               : FALSE ;
  if( count($tab_ids) && isset($tab_etats[$new_etat]) )
  {
    Session::verifier_jeton_anti_CSRF($PAGE);
    // Concernant les notifications, on liste déjà s'il y a des utilisateurs qui s'y seraient abonnés
    $abonnement_ref = 'bilan_officiel_statut';
    $abonnes_nb = 0;
    if( !$discret && in_array($new_etat,array('2rubrique','3mixte','4synthese')) )
    {
      $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_destinataires_avec_informations( $abonnement_ref );
      $abonnes_nb = count($DB_TAB);
      if($abonnes_nb)
      {
        $tab_abonnes = array();
        $tab_profils = array();
        // On récupère les infos au passage
        foreach($DB_TAB as $DB_ROW)
        {
          $notification_statut = ( (COURRIEL_NOTIFICATION=='oui') && ($DB_ROW['jointure_mode']=='courriel') && $DB_ROW['user_email'] ) ? 'envoyée' : 'consultable' ;
          $tab_abonnes[$DB_ROW['user_id']] = array(
            'statut'   => $notification_statut,
            'mailto'   => $DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].' <'.$DB_ROW['user_email'].'>',
            'courriel' => $DB_ROW['user_email'],
            'contenu'  => '',
          );
          $tab_profils[$DB_ROW['user_profil_type']][] = $DB_ROW['user_id'];
        }
        // Récupération du nom des classes (sans fignoler)
        $tab_classes = array();
        $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_classes();
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_classes[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
        }
        // Récupération du nom des périodes (sans fignoler)
        $tab_periodes = array();
        $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_periodes();
        foreach($DB_TAB as $DB_ROW)
        {
          $tab_periodes[$DB_ROW['periode_id']] = $DB_ROW['periode_nom'];
        }
        // Récupération des profs ou directeurs par classe
        $tab_profs_par_classe = array();
        if(!empty($tab_profils['directeur']))
        {
          // Les directeurs sont rattachés à toutes les classes
          foreach($tab_classes as $classe_id => $classe_nom)
          {
            $tab_profs_par_classe[$classe_id] = $tab_profils['directeur'];
          }
        }
        if(!empty($tab_profils['professeur']))
        {
          // Les professeurs ne sont rattachés qu'à certaines classes
          $listing_profs_id   = implode(',',$tab_profils['professeur']);
          $listing_groupes_id = implode(',',array_keys($tab_classes));
          $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_jointure_professeurs_groupes($listing_profs_id,$listing_groupes_id);
          foreach($DB_TAB as $DB_ROW)
          {
            $tab_profs_par_classe[$DB_ROW['groupe_id']][] = $DB_ROW['user_id'];
          }
        }
      }
    }
    // On passe au traitement des données reçues
    $champ = 'officiel_'.$BILAN_TYPE;
    $sql_etat = ($new_etat!='0absence') ? $new_etat : '' ;
    $auteur = afficher_identite_initiale($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']);
    foreach($tab_ids as $ids)
    {
      list( $classe_id , $periode_id ) = explode('p',substr($ids,1));
      if( (int)$classe_id && (int)$periode_id )
      {
        $is_modif = DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_bilan_officiel( $classe_id , $periode_id , $champ , $sql_etat );
        if( $is_modif && $abonnes_nb && isset($tab_profs_par_classe[$classe_id]) )
        {
          $texte = 'Statut ['.$tab_etats[$new_etat].'] appliqué par '.$auteur.' à ['.$tab_types[$BILAN_TYPE]['titre'].'] ['.$tab_periodes[$periode_id].'] ['.$tab_classes[$classe_id].'].'."\r\n";
          foreach($tab_profs_par_classe[$classe_id] as $user_id)
          {
            $tab_abonnes[$user_id]['contenu'] .= $texte;
          }
        }
      }
    }
    // On termine par le log et l'envoi des notifications
    if($abonnes_nb)
    {
      foreach($tab_abonnes as $user_id => $tab)
      {
        if($tab['contenu'])
        {
          DB_STRUCTURE_NOTIFICATION::DB_ajouter_log_visible( $user_id , $abonnement_ref , $tab['statut'] , $tab['contenu'] );
          if($tab['statut']=='envoyée')
          {
            $tab['contenu'] .= Sesamail::texte_pied_courriel( array('no_reply','notif_individuelle','signature') , $tab['courriel'] );
            $courriel_bilan = Sesamail::mail( $tab['mailto'] , 'Notification - Bilan officiel, étape de saisie' , $tab['contenu'] , $tab['mailto'] );
          }
        }
      }
    }
  }
}

// Puce avertissement mode de synthèse non configuré
$li = '';
if($BILAN_TYPE=='bulletin')
{
  $li = '<li><span class="astuce">Un administrateur ou un directeur doit indiquer le type de synthèse adapté suivant chaque référentiel (<span class="manuel"><a class="pop_up" href="'.SERVEUR_DOCUMENTAIRE.'?fichier=releves_bilans__reglages_syntheses_bilans#toggle_type_synthese">DOC</a></span>).</span></li>'.NL;
  $nb_inconnu = DB_STRUCTURE_BILAN::DB_compter_modes_synthese_inconnu();
  $s = ($nb_inconnu>1) ? 's' : '' ;
  $li .= ($nb_inconnu) ? '<li><label class="alerte">Il y a '.$nb_inconnu.' référentiel'.$s.' <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="'.str_replace('§BR§','<br />',html(html(DB_STRUCTURE_BILAN::DB_recuperer_modes_synthese_inconnu()))).'" /> dont le format de synthèse est inconnu (donc non pris en compte).</label></li>'.NL : '<li><label class="valide">Tous les référentiels ont un format de synthèse prédéfini.</label></li>'.NL ; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
}

// Javascript
Layout::add( 'js_inline_before' , 'var USER_ID               = '.$_SESSION['USER_ID'].';' );
Layout::add( 'js_inline_before' , 'var TODAY_FR              = "'.TODAY_FR.'";' );
Layout::add( 'js_inline_before' , 'var BILAN_TYPE            = "'.$BILAN_TYPE.'";' );
Layout::add( 'js_inline_before' , 'var CONVERSION_SUR_20     = '.$_SESSION['OFFICIEL']['BULLETIN_CONVERSION_SUR_20'].';' );
Layout::add( 'js_inline_before' , 'var BACKGROUND_NA         = "'.$_SESSION['BACKGROUND_NA'].'";' );
Layout::add( 'js_inline_before' , 'var BACKGROUND_VA         = "'.$_SESSION['BACKGROUND_VA'].'";' );
Layout::add( 'js_inline_before' , 'var BACKGROUND_A          = "'.$_SESSION['BACKGROUND_A'].'";' );
Layout::add( 'js_inline_before' , 'var URL_IMPORT            = "'.URL_DIR_IMPORT.'";' );
Layout::add( 'js_inline_before' , 'var APP_RUBRIQUE_LONGUEUR = '.$_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_LONGUEUR'].';' );
Layout::add( 'js_inline_before' , 'var APP_GENERALE_LONGUEUR = '.$_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_LONGUEUR'].';' );
Layout::add( 'js_inline_before' , 'var APP_RUBRIQUE_REPORT   = '.$_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_REPORT'].';' );
Layout::add( 'js_inline_before' , 'var APP_GENERALE_REPORT   = '.$_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_REPORT'].';' );
Layout::add( 'js_inline_before' , '// <![CDATA[' );
Layout::add( 'js_inline_before' , 'var APP_RUBRIQUE_MODELE   = "'.str_replace(array("\r\n","\r","\n"),array('\r\n','\r','\n'),html($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_MODELE'])).'";' );
Layout::add( 'js_inline_before' , 'var APP_GENERALE_MODELE   = "'.str_replace(array("\r\n","\r","\n"),array('\r\n','\r','\n'),html($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_MODELE'])).'";' );
Layout::add( 'js_inline_before' , '// ]]>' );
?>

<ul class="puce">
  <li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__<?php echo $tab_types[$BILAN_TYPE]['doc'] ?>">DOC : Bilan officiel &rarr; <?php echo $tab_types[$BILAN_TYPE]['titre'] ?></a></span></li>
  <li><span class="astuce"><?php echo($affichage_formulaire_statut) ? 'Vous pouvez utiliser l\'outil d\'<a href="./index.php?page=compte_message">affichage de messages en page d\'accueil</a> pour informer les professeurs de l\'ouverture à la saisie.' : '<a title="'.$profils_modifier_statut.'" href="#">Profils pouvant modifier le statut d\'un bilan.</a>' ; ?></span></li>
  <?php echo $li ?>
</ul>

<div id="cadre_photo"><button id="voir_photo" type="button" class="voir_photo">Photo</button></div>

<hr />

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des classes de l'établissement.
// Utile pour les profils administrateurs / directeurs, et requis concernant les professeurs pour une recherche s'il est affecté à des groupes.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_classes_etabl(FALSE /*with_ref*/);

$tab_classe_etabl = array(); // tableau temporaire avec les noms des classes de l'établissement
if(is_array($DB_TAB))
{
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_classe_etabl[$DB_ROW['valeur']] = $DB_ROW['texte'];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des classes accessibles à l'utilisateur.
// Indiquer celles potentiellement accessibles à l'utilisateur pour l'appréciation générale.
// Indiquer celles potentiellement accessibles à l'utilisateur pour l'impression PDF.
//
// Pour les administrateurs et les directeurs, ce sont les classes de l'établissement.
// Mais attention, les bilans ne sont définis que sur les classes, pas sur des groupes (car il ne peut y avoir qu'un type de bilan par élève / période).
// Alors quand les professeurs sont associés à des groupes, il faut chercher de quelle(s) classe(s) proviennent les élèves et proposer autant de choix partiels... sur ces classes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_classe = array(); // tableau important avec les droits [classe_id][0|groupe_id]
$tab_groupe = array(); // tableau temporaire avec les noms des groupes du prof
$tab_options_classes = array(); // Pour un futur formulaire select

// Préparation du tableau avec les cellules à afficher
$tab_affich = array(); // [classe_id_groupe_id][periode_id] (ligne colonne) ; les indices [check] sont ceux des checkbox multiples ; les indices [title] sont ceux des intitulés
$tab_affich['check']['check'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['check']['title'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['title']['check'] = ($affichage_formulaire_statut) ? '<td class="nu"></td>' : '' ;
$tab_affich['title']['title'] = '<td class="nu"></td>' ;

if($_SESSION['USER_PROFIL_TYPE']!='professeur') // administrateur | directeur
{
  $droit_modifier_statut       = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT'])       );
  $droit_appreciation_generale = ( ($_SESSION['USER_PROFIL_TYPE']=='directeur')      && test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE']) );
  $droit_impression_pdf        = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF'])        );
  $droit_voir_archives_pdf     = ( ($_SESSION['USER_PROFIL_TYPE']=='administrateur') || test_user_droit_specifique($_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_VOIR_ARCHIVE'])          );
  foreach($tab_classe_etabl as $classe_id => $classe_nom)
  {
    $tab_classe[$classe_id][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' , 'droit_voir_archives_pdf' );
    $tab_affich[$classe_id.'_0']['check'] = '<th class="nu"><q id="id_deb1_g'.$classe_id.'p" class="cocher_tout" title="Tout cocher."></q><q id="id_deb2_g'.$classe_id.'p" class="cocher_rien" title="Tout décocher."></q></th>' ;
    $tab_affich[$classe_id.'_0']['title'] = '<th id="groupe_'.$classe_id.'_0">'.html($classe_nom).'</th>' ;
    $tab_options_classes[$classe_id.'_0'] = '<option value="'.$classe_id.'_0">'.html($classe_nom).'</option>';
  }
}
else // professeur
{
  $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_groupes_professeur($_SESSION['USER_ID'],$_SESSION['USER_JOIN_GROUPES']);
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['groupe_type']=='classe')
    {
      // Pour les classes, RAS
      $droit_modifier_statut       = test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_MODIFIER_STATUT']       , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
      $droit_appreciation_generale = test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'] , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
      $droit_impression_pdf        = test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF']        , $DB_ROW['jointure_pp'] /*matiere_coord_or_groupe_pp_connu*/ , 0 /*matiere_id_or_groupe_id_a_tester*/ );
      $droit_voir_archives_pdf     = test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_VOIR_ARCHIVE']);
      $tab_classe[$DB_ROW['groupe_id']][0] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' );
      $tab_affich[$DB_ROW['groupe_id'].'_0']['check'] = ($affichage_formulaire_statut) ? ( ($droit_modifier_statut) ? '<th class="nu"><q id="id_deb1_g'.$DB_ROW['groupe_id'].'p" class="cocher_tout" title="Tout cocher."></q><q id="id_deb2_g'.$DB_ROW['groupe_id'].'p" class="cocher_rien" title="Tout décocher."></q></th>' : '<th class="nu"></th>' ) : '' ;
      $tab_affich[$DB_ROW['groupe_id'].'_0']['title'] = '<th id="groupe_'.$DB_ROW['groupe_id'].'_0">'.html($DB_ROW['groupe_nom']).'</th>' ;
      $tab_options_classes[$DB_ROW['groupe_id'].'_0'] = '<option value="'.$DB_ROW['groupe_id'].'_0">'.html($DB_ROW['groupe_nom']).'</option>';
    }
    else
    {
      // Pour les groupes, il faudra récupérer les classes dont sont issues les élèves
      $tab_groupe[$DB_ROW['groupe_id']] = html($DB_ROW['groupe_nom']);
    }
  }
  if(count($tab_groupe))
  {
    // On récupère les classes dont sont issues les élèves des groupes et on complète $tab_classe
    $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_classes_eleves_from_groupes( implode(',',array_keys($tab_groupe)) );
    foreach($tab_groupe as $groupe_id => $groupe_nom)
    {
      if(isset($DB_TAB[$groupe_id]))
      {
        foreach($DB_TAB[$groupe_id] as $tab)
        {
          $classe_id = $tab['eleve_classe_id'];
          $droit_modifier_statut       = FALSE ;
          $droit_appreciation_generale = test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE'] , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );
          $droit_impression_pdf        = test_user_droit_specifique( $_SESSION['DROIT_OFFICIEL_'.$tab_types[$BILAN_TYPE]['droit'].'_IMPRESSION_PDF']        , NULL /*matiere_coord_or_groupe_pp_connu*/ , $classe_id /*matiere_id_or_groupe_id_a_tester*/ );
          $tab_classe[$classe_id][$groupe_id] = compact( 'droit_modifier_statut' , 'droit_appreciation_generale' , 'droit_impression_pdf' );
          $tab_affich[$classe_id.'_'.$groupe_id]['check'] =  ($affichage_formulaire_statut) ? '<th class="nu"></th>' : '' ;
          $tab_affich[$classe_id.'_'.$groupe_id]['title'] = '<th id="groupe_'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id]).'<br />'.html($groupe_nom).'</th>' ;
          $tab_options_classes[$classe_id.'_'.$groupe_id] = '<option value="'.$classe_id.'_'.$groupe_id.'">'.html($tab_classe_etabl[$classe_id].' - '.$groupe_nom).'</option>';
        }
      }
    }
  }
}

if(!count($tab_classe))
{
  echo'<p><label class="erreur">Aucune classe ni aucun groupe associé à votre compte !</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des périodes, dans l'ordre choisi par l'admin.
// Initialiser au passage les cellules du tableau à afficher
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_periodes();
if(empty($DB_TAB))
{
  echo'<p><label class="erreur">Aucune période n\'a été configurée par les administrateurs !</label></p>'.NL;
  return; // Ne pas exécuter la suite de ce fichier inclus.
}


$tab_ligne_id = array_keys($tab_affich);
unset($tab_ligne_id[0],$tab_ligne_id[1]);
foreach($DB_TAB as $DB_ROW)
{
  $tab_affich['check'][$DB_ROW['periode_id']] = ($affichage_formulaire_statut) ? '<th class="nu"><q id="id_fin1_p'.$DB_ROW['periode_id'].'" class="cocher_tout" title="Tout cocher."></q><q id="id_fin2_p'.$DB_ROW['periode_id'].'" class="cocher_rien" title="Tout décocher."></q></th>' : '' ;
  $tab_affich['title'][$DB_ROW['periode_id']] = '<th class="hc" id="periode_'.$DB_ROW['periode_id'].'">'.html($DB_ROW['periode_nom']).'</th>' ;
  foreach($tab_ligne_id as $ligne_id)
  {
    $tab_affich[$ligne_id][$DB_ROW['periode_id']] = '<td class="hc">-</td>' ;
    
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer la liste des jointures classes / périodes.
// Pour les groupes, on prend les dates de classes dont les élèves sont issus.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Javascript : tableau utilisé pour désactiver des options d'un select.
Layout::add( 'js_inline_before' , 'var tab_disabled = new Array();' );
Layout::add( 'js_inline_before' , 'tab_disabled["examiner"] = new Array();' );
Layout::add( 'js_inline_before' , 'tab_disabled["imprimer"] = new Array();' );
Layout::add( 'js_inline_before' , 'tab_disabled["voir_pdf"] = new Array();' );

$listing_classes_id = implode(',',array_keys($tab_classe));
$DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_jointure_groupe_periode($listing_classes_id);
foreach($DB_TAB as $DB_ROW)
{
  $classe_id = $DB_ROW['groupe_id'];
  $etat = ($DB_ROW['officiel_'.$BILAN_TYPE]) ? $DB_ROW['officiel_'.$BILAN_TYPE] : '0absence' ;
  // dates
  $date_affich_debut = convert_date_mysql_to_french($DB_ROW['jointure_date_debut']);
  $date_affich_fin   = convert_date_mysql_to_french($DB_ROW['jointure_date_fin']);
  $affich_dates = (($BILAN_TYPE=='releve')||($BILAN_TYPE=='bulletin')) ? $date_affich_debut.' ~ '.$date_affich_fin : 'au '.$date_affich_fin.' (indicatif)' ;
  // État
  $affich_etat = '<span class="off_etat '.substr($etat,1).'"><span>'.$tab_etats[$etat].'</span></span>';
  // images action : vérification
  if($etat=='2rubrique')
  {
    $icone_verification = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_LONGUEUR']) ? '<q class="detailler" title="Rechercher les saisies manquantes."></q>' : '<q class="detailler_non" title="Recherche de saisies manquantes sans objet car bilan configuré sans saisie intermédiaire."></q>' ;
  }
  elseif(in_array($etat,array('3mixte','4synthese')))
  {
    $icone_verification = ( ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_LONGUEUR']) || ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_LONGUEUR']) ) ? '<q class="detailler" title="Rechercher les saisies manquantes."></q>' : '<q class="detailler_non" title="Recherche de saisies manquantes sans objet car bilan configuré sans saisie intermédiaire ni de synthèse."></q>' ;
  }
  else
  {
    $icone_verification = '<q class="detailler_non" title="La recherche de saisies manquantes est sans objet lorsque l\'accès en saisie est fermé."></q>';
  }
  // images action : consultation contenu en cours d'élaboration (bilans HTML)
  if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
  {
    if($etat=='1vide')
    {
      $icone_voir_html = '<q class="voir_non" title="Consultation du contenu sans objet (bilan déclaré vide)."></q>';
    }
    elseif( ($etat=='5complet') && ($tab_types[$BILAN_TYPE]['droit']=='SOCLE') )
    {
      $icone_voir_html = '<q class="voir_non" title="Consultation du contenu inopportun (bilan finalisé : utiliser les archives PDF)."></q>';
    }
    else
    {
      $icone_voir_html = '<q class="voir" title="Consulter le contenu (format HTML)."></q>';
    }
  }
  else
  {
    $icone_voir_html = '';
  }
  // images action : consultation contenu finalisé (bilans PDF)
  if(!$droit_voir_archives_pdf)
  {
    $icone_voir_pdf = '<q class="voir_archive_non" title="Accès restreint aux copies des impressions PDF :<br />'.$profils_archives_pdf.'."></q>';
  }
  elseif($etat!='5complet')
  {
    $icone_voir_pdf = '<q class="voir_archive_non" title="Consultation du bilan imprimé sans objet (bilan déclaré non finalisé)."></q>';
  }
  else
  {
    $icone_voir_pdf = '<q class="voir_archive" title="Consulter une copie du bilan imprimé finalisé (format PDF)."></q>';
  }
  // Il n'y a pas que la ligne de la classe, il y a les lignes des groupes dont des élèves font partie de la classe
  // Les images action de saisie et d'impression dépendent du groupe
  foreach($tab_classe[$classe_id] as $groupe_id=> $tab_droits)
  {
    // checkbox de gestion
    if( ($affichage_formulaire_statut) && ($tab_droits['droit_modifier_statut']) )
    {
      $id = 'g'.$classe_id.'p'.$DB_ROW['periode_id'];
      $label_avant = '<label for="'.$id.'">' ;
      $checkbox    = ' <input id="'.$id.'" name="'.$id.'" type="checkbox" />';
      $label_apres = '</label>' ;
    }
    else
    {
      $label_avant = $checkbox = $label_apres = '' ;
    }
    // images action : saisie
    if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
    {
      if(in_array($etat,array('2rubrique','3mixte')))
      {
        $icone_saisie = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? ( ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_LONGUEUR']) ? '<q class="modifier" title="Saisir '.$tab_types[$BILAN_TYPE]['modif_rubrique'].'."></q>' : '<q class="modifier_non" title="Bilan configuré sans saisie intermédiaire."></q>' ) : '<q class="modifier_non" title="Accès réservé aux professeurs."></q>' ;
      }
      else
      {
        $icone_saisie = '<q class="modifier_non" title="Accès fermé aux saisies intermédiaires."></q>';
      }
    }
    else
    {
      $icone_saisie = '';
    }
    // images action : tamponner
    if($_SESSION['USER_PROFIL_TYPE']!='administrateur')
    {
      if(in_array($etat,array('3mixte','4synthese')))
      {
        $icone_tampon = ($tab_droits['droit_appreciation_generale']) ? ( ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_LONGUEUR']) ? '<q class="tamponner" title="Saisir l\'appréciation générale."></q>' : '<q class="tamponner_non" title="Bilan configuré sans saisie de synthèse."></q>' ) : '<q class="tamponner_non" title="Accès restreint à la saisie de l\'appréciation générale :<br />'.$profils_appreciation_generale.'."></q>' ;
      }
      else
      {
        $icone_tampon = '<q class="tamponner_non" title="Accès fermé à la saisie de synthèse."></q>';
      }
    }
    else
    {
      $icone_tampon = '';
    }
    // images action : impression
    if($tab_droits['droit_impression_pdf'])
    {
      $icone_impression = ($etat=='5complet') ? '<q class="imprimer" title="Imprimer le bilan (PDF)."></q>' : '<q class="imprimer_non" title="L\'impression est possible une fois le bilan déclaré complet."></q>' ;
    }
    else
    {
      $icone_impression = '<q class="imprimer_non" title="Accès restreint à l\'impression PDF :<br />'.$profils_impression_pdf.'."></q>';
    }
    if($etat!='0absence')
    {
      $tab_affich[$classe_id.'_'.$groupe_id][$DB_ROW['periode_id']] = '<td id="cgp_'.$classe_id.'_'.$groupe_id.'_'.$DB_ROW['periode_id'].'" class="hc notnow">'.$label_avant.$affich_dates.'<br />'.$affich_etat.$checkbox.$label_apres.'<br />'.$icone_saisie.$icone_tampon.$icone_verification.$icone_voir_html.$icone_impression.$icone_voir_pdf.'</td>';
    }
    elseif($checkbox!='')
    {
      $tab_affich[$classe_id.'_'.$groupe_id][$DB_ROW['periode_id']] = '<td class="hc notnow">'.$label_avant.$affich_dates.'<br />'.$affich_etat.$checkbox.$label_apres.'</td>';
    }
    else
    {
      $tab_affich[$classe_id.'_'.$groupe_id][$DB_ROW['periode_id']] = '<td class="hc notnow">'.$affich_dates.'<br />'.$affich_etat.'</td>';
    }
    // tableau javascript pour desactiver ce qui est inaccessible
    $disabled_examiner = strpos($icone_verification,'detailler_non') ? 'true' : 'false' ;
    $disabled_imprimer = strpos($icone_impression  ,'imprimer_non')  ? 'true' : 'false' ;
    $disabled_voir_pdf = strpos($icone_voir_pdf    ,'archive_non')   ? 'true' : 'false' ;
    Layout::add( 'js_inline_before' , 'tab_disabled["examiner"]["'.$classe_id.'_'.$groupe_id.'_'.$DB_ROW['periode_id'].'"]='.$disabled_examiner.';' );
    Layout::add( 'js_inline_before' , 'tab_disabled["imprimer"]["'.$classe_id.'_'.$groupe_id.'_'.$DB_ROW['periode_id'].'"]='.$disabled_imprimer.';' );
    Layout::add( 'js_inline_before' , 'tab_disabled["voir_pdf"]["'.$classe_id.'_'.$groupe_id.'_'.$DB_ROW['periode_id'].'"]='.$disabled_voir_pdf.';' );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du tableau.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

echo'<table id="table_accueil"><thead>'.NL;
foreach($tab_affich as $ligne_id => $tab_colonne)
{
  echo ( ($ligne_id!='check') || ($affichage_formulaire_statut) ) ? '<tr>'.implode('',$tab_colonne).'</tr>'.NL : '' ;
  echo ($ligne_id=='title') ? '</thead><tbody>'.NL : '' ;
}
echo'</tbody></table>'.NL;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affichage du formulaire pour modifier les états d'accès.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($affichage_formulaire_statut)
{
  $tab_radio = array();
  foreach($tab_etats as $etat_id => $etat_text)
  {
    $tab_radio[] = '<label for="etat_'.$etat_id.'"><input id="etat_'.$etat_id.'" name="etat" type="radio" value="'.$etat_id.'" /> <span class="off_etat '.substr($etat_id,1).'"><span>'.$etat_text.'</span></span></label>';
  }
  echo'
    <form action="#" method="post" id="cadre_statut">
      <h3>Accès / Statut : <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Pour les cases cochées du tableau (classes uniquement)." /></h3>
      <div>'.implode('</div><div>',$tab_radio).'</div>
      <p><label for="mode_discret"><input id="mode_discret" name="mode_discret" type="checkbox" value="1" /> Mode discret <img alt="" src="./_img/bulle_aide.png" width="16" height="16" title="Cocher pour éviter l\'envoi de notifications aux abonnés." /></label></p>
      <p><input id="listing_ids" name="listing_ids" type="hidden" value="" /><input id="csrf" name="csrf" type="hidden" value="" /><button id="bouton_valider" type="button" class="valider">Valider</button><label id="ajax_msg_gestion">&nbsp;</label></p>
    </form>
  ';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire de choix des matières ou des piliers pour une recherche de saisies manquantes. -> zone_chx_rubriques
// Paramètres supplémentaires envoyés pour éviter d'avoir à les retrouver à chaque fois. -> form_hidden
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$form_hidden = '';
$tab_checkbox_rubriques = array();
$disabled = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_RUBRIQUE_LONGUEUR']) ? '' : ' disabled' ;
if($tab_types[$BILAN_TYPE]['droit']=='SOCLE')
{
  // Lister les piliers du palier concerné
  $palier_id = (int)substr($BILAN_TYPE,-1);
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_OPT_piliers($palier_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_checkbox_rubriques[$DB_ROW['valeur']] = '<input type="checkbox" name="f_rubrique[]" id="rubrique_'.$DB_ROW['valeur'].'" value="'.$DB_ROW['valeur'].'" checked'.$disabled.' /><label for="rubrique_'.$DB_ROW['valeur'].'"> '.html($DB_ROW['texte']).'</label><br />';
  }
  $listing_piliers_id = implode(',',array_keys($tab_checkbox_rubriques));
  $form_hidden .= '<input type="hidden" id="f_listing_piliers" name="f_listing_piliers" value="'.$listing_piliers_id.'" />';
  $commentaire_selection = ($_SESSION['OFFICIEL']['SOCLE_ONLY_PRESENCE']) ? '<div class="astuce">La recherche sera dans tous les cas aussi restreinte aux seules compétences matières ayant fait l\'objet d\'une évaluation ou d\'une validation.</div>' : '' ;
}
elseif(($BILAN_TYPE=='releve')||($BILAN_TYPE=='bulletin'))
{
  // Lister les matières rattachées au prof
  $listing_matieres_id = ($_SESSION['USER_PROFIL_TYPE']=='professeur') ? DB_STRUCTURE_COMMUN::DB_recuperer_matieres_professeur($_SESSION['USER_ID']) : '' ;
  $form_hidden .= '<input type="hidden" id="f_listing_matieres" name="f_listing_matieres" value="'.$listing_matieres_id.'" />';
  $tab_matieres_id = explode(',',$listing_matieres_id);
  // Lister les matières de l'établissement
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_matieres_etablissement( TRUE /*order_by_name*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $checked = ( ($_SESSION['USER_PROFIL_TYPE']!='professeur') || in_array($DB_ROW['matiere_id'],$tab_matieres_id) ) ? ' checked' : '' ;
    $tab_checkbox_rubriques[$DB_ROW['matiere_id']] = '<label for="rubrique_'.$DB_ROW['matiere_id'].'"><input type="checkbox" name="f_rubrique[]" id="rubrique_'.$DB_ROW['matiere_id'].'" value="'.$DB_ROW['matiere_id'].'"'.$checked.$disabled.' /> '.html($DB_ROW['matiere_nom']).'</label><br />';
  }
  $commentaire_selection = '<div class="astuce">La recherche sera dans tous les cas aussi restreinte aux matières evaluées au cours de la période.</div>';
}
// Choix de vérifier ou pas l'appréciation générale ; le test (in_array($etat,array('3mixte','4synthese'))) dépend de chaque classe...
$disabled = ($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_LONGUEUR']) ? '' : ' disabled' ;
$tab_checkbox_rubriques[0] = '<label for="rubrique_0"><input type="checkbox" name="f_rubrique[]" id="rubrique_0"'.$disabled.' value="0" /> <i>Appréciation de synthèse générale</i></label><br />';
// Présenter les rubriques en colonnes de hauteur raisonnables
$tab_checkbox_rubriques    = array_values($tab_checkbox_rubriques);
$nb_rubriques              = count($tab_checkbox_rubriques);
$nb_rubriques_maxi_par_col = ($tab_types[$BILAN_TYPE]['droit']=='SOCLE') ? $nb_rubriques : 10 ;
$nb_cols                   = floor(($nb_rubriques-1)/$nb_rubriques_maxi_par_col)+1;
$nb_rubriques_par_col      = ceil($nb_rubriques/$nb_cols);
$tab_div = array_fill(0,$nb_cols,'');
foreach($tab_checkbox_rubriques as $i => $contenu)
{
  $tab_div[floor($i/$nb_rubriques_par_col)] .= $contenu;
}
?>

<form action="#" method="post" id="zone_chx_rubriques" class="hide">
  <h2>Rechercher des saisies manquantes</h2>
  <?php echo $commentaire_selection ?>
  <p><a href="#zone_chx_rubriques" id="rubrique_check_all" class="cocher_tout">Toutes</a>&nbsp;&nbsp;&nbsp;<a href="#zone_chx_rubriques" id="rubrique_uncheck_all" class="cocher_rien">Aucune</a></p>
  <div class="prof_liste"><?php echo implode('</div><div class="prof_liste">',$tab_div) ?></div>
  <p style="clear:both"><span class="tab"></span><button id="lancer_recherche" type="button" class="rechercher">Lancer la recherche</button> <button id="fermer_zone_chx_rubriques" type="button" class="annuler">Annuler</button><label id="ajax_msg_recherche">&nbsp;</label></p>
</form>

<form action="#" method="post" id="form_hidden" class="hide">
  <div>
    <?php echo $form_hidden ?>
    <input type="hidden" id="f_objet" name="f_objet" value="" />
    <input type="hidden" id="f_listing_rubriques" name="f_listing_rubriques" value="" />
    <input type="hidden" id="f_listing_eleves" name="f_listing_eleves" value="" />
    <input type="hidden" id="f_mode" name="f_mode" value="texte" />
    <input type="hidden" id="f_parite" name="f_parite" value="" />
  </div>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaires utilisés pour les opérations ultérieures sur les bilans.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div id="zone_action_eleve">
</div>

<div id="zone_action_classe" class="hide">
  <h2>Recherche de saisies manquantes | Imprimer le bilan (PDF)</h2>
  <form action="#" method="post" id="form_choix_classe"><div><b id="report_periode">Période :</b> <button id="go_precedent_classe" type="button" class="go_precedent">Précédent</button> <select id="go_selection_classe" name="go_selection_classe" class="b"><?php echo implode('',$tab_options_classes) ?></select> <button id="go_suivant_classe" type="button" class="go_suivant">Suivant</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_action_classe" type="button" class="retourner">Retour</button></div></form>
  <hr />
  <div id="zone_resultat_classe"></div>
  <div id="zone_imprimer" class="hide">
    <form action="#" method="post" id="form_choix_eleves">
      <table id="table_action" class="form t9">
        <thead>
          <tr>
            <th class="nu"><q class="cocher_tout" title="Tout cocher."></q><q class="cocher_rien" title="Tout décocher."></q></th>
            <th>Élèves</th>
            <th class="hc">Généré</th>
          </tr>
        </thead>
        <tbody>
          <tr><td class="nu" colspan="3"></td></tr>
        </tbody>
      </table>
    </form>
    <p class="ti">
      <label for="check_parite"><input id="check_parite" type="checkbox" checked /> Insérer des pages blanches si nécessaire pour forcer un nombre de pages pair par bilan (utile pour une impression recto-verso en série).</label>
    </p>
    <p class="ti">
      <button id="valider_imprimer" type="button" class="valider">Lancer l'impression</button><label id="ajax_msg_imprimer">&nbsp;</label>
    </p>
  </div>
  <div id="zone_voir_archive" class="hide">
    <p>
      <span class="astuce">Ces bilans ne sont que des copies, laissées à disposition pour information jusqu'à la fin de l'année scolaire.</span><br />
      <span class="danger">Les originaux doivent être archivés par la personne ayant effectué l'impression PDF.</span>
    </p>
    <table class="t9">
      <thead>
        <tr>
          <th>Élèves</th>
          <th class="hc">Généré</th>
          <th class="hc">Visualisation élève</th>
          <th class="hc">Visualisation parent</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="nu" colspan="4"></td></tr>
      </tbody>
    </table>
    <p class="ti">
      <label id="ajax_msg_voir_archive">&nbsp;</label>
    </p>
  </div>
</div>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire pour afficher le résultat de l'analyse d'un fichier CSV et demander confirmation.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
Layout::add( 'css_inline' , '.insert{color:green}.update{color:red}.idem{color:grey}' ); // Pour le rapport d'analyse
?>

<form action="#" method="post" id="zone_action_deport" class="hide" onsubmit="return false">
  <h2>Saisie déportée</h2>
  <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_professeur__evaluations_saisie_deportee">DOC : Saisie déportée.</a></span></p>
  <ul class="puce">
    <li><a id="export_file_saisie_deportee" target="_blank" href=""><span class="file file_txt">Récupérer un fichier vierge à compléter pour une saisie déportée (format <em>csv</em>).</span></a></li>
    <li><button id="import_file" type="button" class="fichier_import">Envoyer un fichier d'appréciations complété (format <em>csv</em>).</button></li>
  </ul>
  <p class="ti">
    <label id="msg_import">&nbsp;</label>
  </p>
</form>

<form action="#" method="post" id="zone_action_import" class="hide" onsubmit="return false">
  <h2>Analyse des données à importer</h2>
  <p class="astuce">Les informations <span class="insert">en vert seront ajoutées</span>, <span class="update">celles en rouge modifiées</span>, et <span class="idem">celles en gris inchangées</span>.</p>
  <table id="table_import_analyse" class="t9">
    <thead>
      <tr><td class="nu" colspan="3"></td></tr>
    </thead>
    <tbody>
      <tr><td class="nu" colspan="3"></td></tr>
    </tbody>
  </table>
  <p class="ti">
    <input type="hidden" value="" name="f_import_info" id="f_import_info" /><button id="valider_importer" type="button" class="valider">Confirmer</button>&nbsp;&nbsp;&nbsp;<button id="fermer_zone_importer" type="button" class="annuler">Annuler / Retour</button><label id="ajax_msg_importer">&nbsp;</label>
  </p>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Formulaire pour signaler ou corriger une faute dans une appréciation.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<form action="#" method="post" id="zone_signaler_corriger" class="hide" onsubmit="return false">
  <h2>Signaler | Corriger une faute</h2>
  <div id="section_corriger">
  </div>
  <div id="section_signaler">
    <div>
      <input type="hidden" value="" name="f_destinataire_id" id="f_destinataire_id" />
      <input type="hidden" value="signaler_faute|corriger_faute" name="f_action" id="f_action" />
      <label for="f_message_contenu" class="tab">Message informatif :</label><textarea name="f_message_contenu" id="f_message_contenu" rows="5" cols="100"></textarea><br />
      <span class="tab"></span><label id="f_message_contenu_reste"></label>
    </div>
  </div>
  <p>
    <span class="tab"></span><button id="valider_signaler_corriger" type="button" class="valider">Valider</button>&nbsp;&nbsp;&nbsp;<button id="annuler_signaler_corriger" type="button" class="annuler">Annuler / Retour</button><label id="ajax_msg_signaler_corriger">&nbsp;</label>
  </p>
</form>

<?php
// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Liens pour archiver / imprimer des saisies.
// ////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<div id="zone_archiver_imprimer" class="hide">
  <h2>Archiver / Imprimer des données</h2>
  <p><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=releves_bilans__officiel_imprimer_saisies">DOC : Imprimer tableaux notes / appréciations.</a></span></p>
  <p class="noprint">Afin de préserver l'environnement, n'imprimer que si nécessaire !</p>
  <ul class="puce">
    <?php if($BILAN_TYPE=='bulletin'): ?>
      <li><button id="imprimer_donnees_eleves_prof" type="button" class="imprimer">Archiver / Imprimer</button> mes appréciations pour chaque élève et le groupe classe.</li>
      <li><button id="imprimer_donnees_eleves_collegues" type="button" class="imprimer">Archiver / Imprimer</button> les appréciations des collègues pour chaque élève.</li>
      <li><button id="imprimer_donnees_classe_collegues" type="button" class="imprimer">Archiver / Imprimer</button> les appréciations des collègues sur le groupe classe.</li>
      <?php if($_SESSION['OFFICIEL']['BULLETIN_APPRECIATION_GENERALE_LONGUEUR']): ?>
        <li><button id="imprimer_donnees_eleves_syntheses" type="button" class="imprimer">Archiver / Imprimer</button> les appréciations de synthèse générale pour chaque élève.</li>
      <?php endif; ?>
      <?php if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']): ?>
        <li><button id="imprimer_donnees_eleves_moyennes" type="button" class="imprimer">Archiver / Imprimer</button> le tableau des moyennes pour chaque élève.</li>
        <li><button id="imprimer_donnees_eleves_recapitulatif" type="button" class="imprimer">Archiver / Imprimer</button> un récapitulatif annuel des moyennes et appréciations par élève.</li>
      <?php endif; ?>
    <?php else: ?>
      <li><button id="imprimer_donnees_eleves_prof" type="button" class="imprimer">Archiver / Imprimer</button> mes appréciations pour chaque élève.</li>
      <li><button id="imprimer_donnees_eleves_collegues" type="button" class="imprimer">Archiver / Imprimer</button> les appréciations des collègues pour chaque élève.</li>
      <?php if($_SESSION['OFFICIEL'][$tab_types[$BILAN_TYPE]['droit'].'_APPRECIATION_GENERALE_LONGUEUR']): ?>
        <li><button id="imprimer_donnees_eleves_syntheses" type="button" class="imprimer">Archiver / Imprimer</button> les appréciations de synthèse générale pour chaque élève.</li>
      <?php endif; ?>
    <?php endif; ?>
  </ul>
  <hr />
  <p><label id="ajax_msg_archiver_imprimer">&nbsp;</label></p>
</div>


