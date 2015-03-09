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

/**
 * Code inclus commun aux pages
 * [./pages/releve_socle.ajax.php]
 * [./_inc/code_officiel_***.php]
 */

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

// Chemins d'enregistrement

$fichier_nom = ($make_action!='imprimer') ? 'releve_socle_detail_'.Clean::fichier(substr($palier_nom,0,strpos($palier_nom,' ('))).'_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea() : 'officiel_'.$BILAN_TYPE.'_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea() ;

// Tableau des langues

require(CHEMIN_DOSSIER_INCLUDE.'tableau_langues_socle.php');
$tab_item_pilier  = array(); // id de l'item => id du pilier

// Initialisation de tableaux

$tab_pilier       = array();  // [pilier_id] => pilier_nom;
$tab_section      = array();  // [pilier_id][section_id] => section_nom;
$tab_socle        = array();  // [section_id][socle_id] => socle_nom;
$tab_entree_id    = array();  // [i] => entree_id
$tab_eleve_infos  = array();  // [eleve_id] => array(eleve_nom,eleve_prenom,date_naissance,eleve_langue)
$tab_eval         = array();  // [eleve_id][socle_id][item_id][]['note'] => note
$tab_item         = array();  // [item_id] => array(item_ref,item_nom,item_cart,matiere_id,calcul_methode,calcul_limite);
$tab_user_entree  = array();  // [eleve_id][entree_id] => array(etat,date,info);
$tab_user_pilier  = array();  // [eleve_id][pilier_id] => array(etat,date,info);

// Initialisation de variables

$test_affichage_Pourcentage = ($groupe_id && count($tab_eleve_id) && $aff_socle_PA) ? TRUE : FALSE;
$test_affichage_Validation  = ($groupe_id && count($tab_eleve_id) && $aff_socle_EV) ? TRUE : FALSE;

$memo_demande  = (count($tab_pilier_id)>1) ? 'palier' : 'pilier' ;
$liste_eleve   = implode(',',$tab_eleve_id);

if( ($make_html) || ($make_pdf) )
{
  if(!$aff_coef)  { $texte_coef       = ''; }
  if(!$aff_socle) { $texte_socle      = ''; }
  if(!$aff_lien)  { $texte_lien_avant = ''; }
  if(!$aff_lien)  { $texte_lien_apres = ''; }
  $toggle_class = ($aff_start) ? 'toggle_moins' : 'toggle_plus' ;
  $toggle_etat  = ($aff_start) ? '' : ' class="hide"' ;
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des items du socle pour le ou les piliers sélectionné(s)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$DB_TAB = ($memo_demande=='pilier') ? DB_STRUCTURE_SOCLE::DB_recuperer_arborescence_pilier($tab_pilier_id[0]) : DB_STRUCTURE_SOCLE::DB_recuperer_arborescence_piliers(implode(',',$tab_pilier_id)) ;
if(empty($DB_TAB))
{
  exit('Aucun item référencé pour cette partie du socle commun !');
}
$pilier_id  = 0;
$section_id = 0;
$socle_id   = 0;
foreach($DB_TAB as $DB_ROW)
{
  if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
  {
    $pilier_id  = $DB_ROW['pilier_id'];
    $tab_pilier[$pilier_id] = $DB_ROW['pilier_nom'];
  }
  if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
  {
    $section_id  = $DB_ROW['section_id'];
    $tab_section[$pilier_id][$section_id] = $DB_ROW['section_nom'];
  }
  if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$socle_id) )
  {
    $socle_id = $DB_ROW['entree_id'];
    $tab_socle[$section_id][$socle_id] = $DB_ROW['entree_nom'];
    $tab_entree_id[] = $socle_id;
    if($mode=='auto')
    {
      $tab_item_pilier[$socle_id] = $pilier_id;
    }
  }
}
$listing_entree_id = implode(',',$tab_entree_id);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves (si demandé)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_eleve_infos[$_SESSION['USER_ID']] = array(
    'eleve_nom'      => $_SESSION['USER_NOM'],
    'eleve_prenom'   => $_SESSION['USER_PRENOM'],
    'eleve_genre'    => $_SESSION['USER_GENRE'],
    'date_naissance' => $_SESSION['USER_NAISSANCE_DATE'],
    'eleve_langue'   => $_SESSION['ELEVE_LANGUE'],
  );
}
elseif($groupe_id && count($tab_eleve_id))
{
  $eleves_ordre = ($groupe_type=='Classes') ? 'alpha' : $eleves_ordre ;
  $tab_eleve_infos = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve , $eleves_ordre , FALSE /*with_gepi*/ , TRUE /*with_langue*/ , FALSE /*with_brevet_serie*/ );
}
else
{
  $tab_eleve_infos[0] = array(
    'eleve_nom'      => '',
    'eleve_prenom'   => '',
    'eleve_genre'    => 'I',
    'date_naissance' => NULL,
    'eleve_langue'   => 0,
  );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des résultats (si pas fiche générique)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($groupe_id && count($tab_eleve_id))
{
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_palier_sans_infos_items($liste_eleve , $listing_entree_id , $_SESSION['USER_PROFIL_TYPE']);
  foreach($DB_TAB as $DB_ROW)
  {
    $test_comptabilise = ($mode=='auto') ? ( !in_array($tab_item_pilier[$DB_ROW['socle_id']],$tab_langue_piliers) || in_array($DB_ROW['matiere_id'],$tab_langues[$tab_eleve_infos[$DB_ROW['eleve_id']]['eleve_langue']]['tab_matiere_id']) ) : in_array($DB_ROW['matiere_id'],$tab_matiere_id) ;
    if($test_comptabilise)
    {
      $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['socle_id']][$DB_ROW['item_id']][]['note'] = $DB_ROW['note'];
      $tab_item[$DB_ROW['item_id']] = TRUE;
    }
  }
  if(count($tab_item))
  {
    $listing_item_id = implode(',',array_keys($tab_item));
    $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_infos_items( $listing_item_id , TRUE /*detail*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      $tab_item[$DB_ROW['item_id']] = array(
        'item_ref'            => $DB_ROW['item_ref'],
        'item_nom'            => $DB_ROW['item_nom'],
        'item_coef'           => $DB_ROW['item_coef'],
        'item_cart'           => $DB_ROW['item_cart'],
        'item_socle'          => $DB_ROW['socle_id'],
        'item_lien'           => $DB_ROW['item_lien'],
        'matiere_id'          => $DB_ROW['matiere_id'],
        'matiere_nb_demandes' => $DB_ROW['matiere_nb_demandes'],
        'calcul_methode'      => $DB_ROW['calcul_methode'],
        'calcul_limite'       => $DB_ROW['calcul_limite'],
      );
    }
  }
}
else
{
  // Dans le cas contraire (fiche générique), afficher toute la grille
  $only_presence = FALSE;
}

// Ce tableau ne sert plus
unset($tab_item_pilier);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des validations (si demandé)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($test_affichage_Validation)
{
  // On commence par remplir tout le tableau des items pour ne pas avoir ensuite à tester tout le temps si le champ existe
  foreach($tab_eleve_id as $eleve_id)
  {
    foreach($tab_entree_id as $entree_id)
    {
      $tab_user_entree[$eleve_id][$entree_id] = array('etat'=>2,'date'=>'','info'=>'');
    }
  }
  //Maintenant on complète avec les valeurs de la base
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_entree($liste_eleve,$listing_entree_id,$domaine_id=0,$pilier_id=0,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les entrées
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_user_entree[$DB_ROW['user_id']][$DB_ROW['entree_id']] = array('etat'=>$DB_ROW['validation_entree_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_entree_date']),'info'=>$DB_ROW['validation_entree_info']);
  }
  // On commence par remplir tout le tableau des piliers pour ne pas avoir ensuite à tester tout le temps si le champ existe
  foreach($tab_eleve_id as $eleve_id)
  {
    foreach($tab_pilier as $pilier_id => $pilier_nom)
    {
      $tab_user_pilier[$eleve_id][$pilier_id] = array('etat'=>2,'date'=>'','info'=>'');
    }
  }
  //Maintenant on complète avec les valeurs de la base
  $listing_pilier_id = implode(',',array_keys($tab_pilier));
  $DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_jointure_user_pilier($liste_eleve,$listing_pilier_id,$palier_id=0); // en fait on connait aussi le palier mais la requête est plus simple (pas de jointure) avec les piliers
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_user_pilier[$DB_ROW['user_id']][$DB_ROW['pilier_id']] = array('etat'=>$DB_ROW['validation_pilier_etat'],'date'=>convert_date_mysql_to_french($DB_ROW['validation_pilier_date']),'info'=>$DB_ROW['validation_pilier_info']);
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
/* 
 * Libérer de la place mémoire car les scripts de bilans sont assez gourmands.
 * Supprimer $DB_TAB ne fonctionne pas si on ne force pas auparavant la fermeture de la connexion.
 * SebR devrait peut-être envisager d'ajouter une méthode qui libère cette mémoire, si c'est possible...
 */
// ////////////////////////////////////////////////////////////////////////////////////////////////////

DB::close(SACOCHE_STRUCTURE_BD_NAME);
unset($DB_TAB);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du bilan relatif au socle, en HTML et PDF => Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_etat = array('A'=>'v','VA'=>'o','NA'=>'r');
$tab_init_compet = array('A'=>0,'VA'=>0,'NA'=>0,'nb'=>0);
// $tab_score_pilier_eleve  = array();  // [pilier_id][eleve_id] => array(A,VA,NA,nb,%)  // Retenir le nb d'items acquis ou pas / pilier / élève
// $tab_score_section_eleve = array();  // [section_id][eleve_id] => array(A,VA,NA,nb,%) // Retenir le nb d'items acquis ou pas / section / élève
$tab_score_socle_eleve   = array();  // [socle_id][eleve_id] => array(A,VA,NA,nb,%)   // Retenir le nb d'items acquis ou pas / item / élève
$tab_infos_socle_eleve   = array();  // [socle_id][eleve_id] => array()               // Retenir les infos sur les items travaillés et leurs scores / item du socle / élève

// Pour chaque élève...
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  // Pour chaque pilier...
  if(count($tab_pilier))
  {
    foreach($tab_pilier as $pilier_id => $pilier_nom)
    {
      // $tab_score_pilier_eleve[$pilier_id][$eleve_id] = $tab_init_compet;
      // Pour chaque section...
      if(isset($tab_section[$pilier_id]))
      {
        foreach($tab_section[$pilier_id] as $section_id => $section_nom)
        {
          // $tab_score_section_eleve[$section_id][$eleve_id] = $tab_init_compet;
          // Pour chaque item du socle...
          if(isset($tab_socle[$section_id]))
          {
            foreach($tab_socle[$section_id] as $socle_id => $socle_nom)
            {
              $tab_score_socle_eleve[$socle_id][$eleve_id] = $tab_init_compet;
              $tab_infos_socle_eleve[$socle_id][$eleve_id] = array();
              // Pour chaque item associé à cet item du socle, ayant été évalué pour cet élève...
              if(isset($tab_eval[$eleve_id][$socle_id]))
              {
                foreach($tab_eval[$eleve_id][$socle_id] as $item_id => $tab_devoirs)
                {
                  extract($tab_item[$item_id]);  // $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien $matiere_id $matiere_nb_demandes $calcul_methode $calcul_limite
                  // calcul du bilan de l'item
                  $score = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
                  if($score!==FALSE)
                  {
                    // on détermine si elle est acquise ou pas
                    $indice = test_A($score) ? 'A' : ( test_NA($score) ? 'NA' : 'VA' ) ;
                    // le détail HTML
                    if($make_html)
                    {
                      if($aff_coef)
                      {
                        $texte_coef = '['.$item_coef.'] ';
                      }
                      if($aff_socle)
                      {
                        $texte_socle = ($item_socle) ? '[S] ' : '[–] ';
                      }
                      if($aff_lien)
                      {
                        $texte_lien_avant = ($item_lien) ? '<a target="_blank" href="'.html($item_lien).'">' : '';
                        $texte_lien_apres = ($item_lien) ? '</a>' : '';
                      }
                      if($_SESSION['USER_PROFIL_TYPE']!='eleve') { $texte_demande_eval = ''; }
                      elseif(!$matiere_nb_demandes)              { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour les items de cette matière."></q>'; }
                      elseif(!$item_cart)                        { $texte_demande_eval = '<q class="demander_non" title="Pas de demande autorisée pour cet item précis."></q>'; }
                      else                                       { $texte_demande_eval = '<q class="demander_add" id="demande_'.$matiere_id.'_'.$item_id.'_'.$score.'" title="Ajouter aux demandes d\'évaluations."></q>'; }
                      $tab_infos_socle_eleve[$socle_id][$eleve_id][] = '<span class="pourcentage '.$tab_etat[$indice].'">'.$score.'%</span> '.$texte_coef.$texte_socle.$texte_lien_avant.html($item_ref.' - '.$item_nom).$texte_lien_apres.$texte_demande_eval;
                    }
                    // on enregistre les infos
                    $tab_score_socle_eleve[$socle_id][$eleve_id][$indice]++;
                    $tab_score_socle_eleve[$socle_id][$eleve_id]['nb']++;
                    // $tab_score_section_eleve[$section_id][$eleve_id][$indice]++;
                    // $tab_score_section_eleve[$section_id][$eleve_id]['nb']++;
                    // $tab_score_pilier_eleve[$pilier_id][$eleve_id][$indice]++;
                    // $tab_score_pilier_eleve[$pilier_id][$eleve_id]['nb']++;
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}

// On calcule les états d'acquisition à partir des A / VA / NA

if($test_affichage_Pourcentage)
{
  // Pour les piliers
  // foreach($tab_score_pilier_eleve as $pilier_id=>$tab_pilier_eleve)
  // {
  //   foreach($tab_pilier_eleve as $eleve_id=>$tab_scores)
  //   {
  //     $tab_score_pilier_eleve[$pilier_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : FALSE ;
  //   }
  // }
  // Pour les sections
  // foreach($tab_score_section_eleve as $section_id=>$tab_section_eleve)
  // {
  //   foreach($tab_section_eleve as $eleve_id=>$tab_scores)
  //   {
  //     $tab_score_section_eleve[$section_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : FALSE ;
  //   }
  // }
  // Pour les items du socle
  foreach($tab_score_socle_eleve as $socle_id=>$tab_socle_eleve)
  {
    foreach($tab_socle_eleve as $eleve_id=>$tab_scores)
    {
      $tab_score_socle_eleve[$socle_id][$eleve_id]['%'] = ($tab_scores['nb']) ? round( 50 * ( ($tab_scores['A']*2 + $tab_scores['VA']) / $tab_scores['nb'] ) ,0) : FALSE ;
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Restriction de l'affichage aux seuls éléments évalués ou validés (si demandé)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_contenu_presence = array( 'pilier'=>array() , 'section'=>array() , 'item'=>array() );
if($only_presence)
{
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    if(count($tab_pilier))
    {
      foreach($tab_pilier as $pilier_id => $pilier_nom)
      {
        if( ($test_affichage_Validation) && ($tab_user_pilier[$eleve_id][$pilier_id]['etat']!=2) )
        {
          $tab_contenu_presence['pilier'][$eleve_id][$pilier_id]   = TRUE;
        }
        if(isset($tab_section[$pilier_id]))
        {
          foreach($tab_section[$pilier_id] as $section_id => $section_nom)
          {
            if(isset($tab_socle[$section_id]))
            {
              foreach($tab_socle[$section_id] as $socle_id => $socle_nom)
              {
                if( ($tab_score_socle_eleve[$socle_id][$eleve_id]['nb']) || ( ($test_affichage_Validation) && ($tab_user_entree[$eleve_id][$socle_id]['etat']!=2) ) )
                {
                  $tab_contenu_presence['pilier'][$eleve_id][$pilier_id]               = TRUE;
                  $tab_contenu_presence['section'][$eleve_id][$pilier_id][$section_id] = TRUE;
                  $tab_contenu_presence['item'][$eleve_id][$pilier_id][$socle_id]      = TRUE;
                }
              }
            }
          }
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Compter le nombre de lignes à afficher par élève par pilier
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_nb_lignes = array();
$tab_nb_lignes_par_pilier = array();
$nb_lignes_appreciation_intermediaire_par_prof_hors_intitule = ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE_LONGUEUR']<250)   ? 1   : 2 ;
$nb_lignes_appreciation_generale_avec_intitule = ( $make_officiel && $_SESSION['OFFICIEL']['SOCLE_APPRECIATION_GENERALE_LONGUEUR'] ) ? 1+6 : 0 ;
$nb_lignes_assiduite                           = ( $make_officiel && ($affichage_assiduite) )                                        ? 1.3 : 0 ;
$nb_lignes_prof_principal                      = ( $make_officiel && ($affichage_prof_principal) )                                   ? 1.3 : 0 ;
$nb_lignes_supplementaires                     = ( $make_officiel && $_SESSION['OFFICIEL']['SOCLE_LIGNE_SUPPLEMENTAIRE'] )           ? 1.3 : 0 ;
$nb_lignes_legendes                            = ($legende=='oui') ? 0.5 + (2*$test_affichage_Pourcentage) + ($test_affichage_Validation) : 0 ;

foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  if(count($tab_pilier))
  {
    foreach($tab_pilier as $pilier_id => $pilier_nom)
    {
      if($only_presence)
      {
        $is_pilier   = (isset($tab_contenu_presence['pilier'][$eleve_id][$pilier_id]))  ? 1                                                              : 0 ;
        $nb_sections = (isset($tab_contenu_presence['section'][$eleve_id][$pilier_id])) ? count($tab_contenu_presence['section'][$eleve_id][$pilier_id]) : 0 ;
        $nb_items    = (isset($tab_contenu_presence['item'][$eleve_id][$pilier_id]))    ? count($tab_contenu_presence['item'][$eleve_id][$pilier_id])    : 0 ;
        $tab_nb_lignes[$eleve_id][$pilier_id] = $is_pilier + $nb_sections + $nb_items + 1 ;
      }
      else
      {
        if($key==0)
        {
          $tab_nb_lignes_par_pilier[$pilier_id] = 1;
          if(isset($tab_section[$pilier_id]))
          {
            foreach($tab_section[$pilier_id] as $section_id => $section_nom)
            {
              $tab_nb_lignes_par_pilier[$pilier_id]++;
              if(isset($tab_socle[$section_id]))
              {
                foreach($tab_socle[$section_id] as $socle_id => $socle_nom)
                {
                  $tab_nb_lignes_par_pilier[$pilier_id]++;
                }
              }
            }
          }
          $tab_nb_lignes_par_pilier[$pilier_id] += 1; // marge au dessus
        }
        $tab_nb_lignes[$eleve_id][$pilier_id] = $tab_nb_lignes_par_pilier[$pilier_id] + 1 ;
      }
      if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE_LONGUEUR']) && (isset($tab_saisie[$eleve_id][$pilier_id])) )
      {
        $tab_nb_lignes[$eleve_id][$pilier_id] += ($nb_lignes_appreciation_intermediaire_par_prof_hors_intitule * count($tab_saisie[$eleve_id][$pilier_id]) ) + 1 ; // + 1 pour "Appréciation / Conseils pour progresser"
      }
    }
  }
}

// Calcul des totaux une unique fois par élève
$tab_nb_lignes_total_eleve = array();
foreach($tab_nb_lignes as $eleve_id => $tab)
{
  $tab_nb_lignes_total_eleve[$eleve_id] = array_sum($tab);
}

// Nombre de boucles par élève (entre 1 et 3 pour les bilans officiels, dans ce cas $tab_destinataires[] est déjà complété ; une seule dans les autres cas).
if(!isset($tab_destinataires))
{
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    $tab_destinataires[$eleve_id][0] = TRUE ;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du bilan relatif au socle, en HTML et PDF => Production et mise en page
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_direct = ( ( ( in_array($_SESSION['USER_PROFIL_TYPE'],array('eleve','parent')) ) && (SACoche!='webservices') ) || ($make_officiel) ) ? TRUE : FALSE ;

$titre1 = ($mode=='manuel') ? 'Relevé de maîtrise du socle commun [matières resteintes]' : 'Relevé de maîtrise du socle commun' ;
$titre2 = ($memo_demande=='palier') ? $palier_nom : $palier_nom.' – '.mb_substr($pilier_nom,0,mb_strpos($pilier_nom,'–')) ;
if($make_html)
{
  $bouton_print_appr = ($make_officiel)                     ? ' <button id="archiver_imprimer" type="button" class="imprimer">Archiver / Imprimer des données</button>'           : '' ;
  $bouton_print_test = (!empty($is_bouton_test_impression)) ? ' <button id="simuler_impression" type="button" class="imprimer">Simuler l\'impression finale de ce bilan</button>' : '' ;
  $bouton_import_csv = ($make_action=='saisir')             ? ' <button id="saisir_deport" type="button" class="fichier_export">Saisie déportée</button>'                         : '' ;
  $releve_HTML  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>'.NL;
  $releve_HTML .= $affichage_direct ? '' : '<h1>'.html($titre1).'</h1>'.NL;
  $releve_HTML .= $affichage_direct ? '' : '<h2>'.html($titre2).'</h2>'.NL;
  $releve_HTML .= '<div class="astuce">Cliquer sur <span class="toggle_plus"></span> / <span class="toggle_moins"></span> pour afficher / masquer le détail.'.$bouton_print_appr.$bouton_print_test.$bouton_import_csv.'</div>'.NL;
  $separation = (count($tab_eleve_infos)>1) ? '<hr />'.NL : '' ;
  // Légende identique pour tous les élèves car pas de codes de notation donc pas de codages spéciaux.
  $legende_html = ($legende=='oui') ? Html::legende( FALSE /*codes_notation*/ , FALSE /*anciennete_notation*/ , FALSE /*score_bilan*/ , $test_affichage_Pourcentage /*etat_acquisition*/ , $test_affichage_Pourcentage /*pourcentage_acquis*/ , $test_affichage_Validation /*etat_validation*/ , $make_officiel , TRUE /*force_nb*/  ) : '' ;
}
if($make_pdf)
{
  // Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
  $releve_PDF = new PDF_socle_releve( $make_officiel , 'portrait' /*orientation*/ , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $fond , $legende , !empty($is_test_impression) /*filigrane*/ );
  $releve_PDF->initialiser($test_affichage_Pourcentage,$test_affichage_Validation);
  $break  = ($memo_demande=='palier') ? FALSE : TRUE ;
}

// Pour chaque élève...
foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
{
  extract($tab_eleve);  // $eleve_nom $eleve_prenom $date_naissance $eleve_genre $eleve_langue
  $date_naissance = ($date_naissance) ? convert_date_mysql_to_french($date_naissance) : '' ;
  if($make_officiel)
  {
    // Quelques variables récupérées ici car pose pb si placé dans la boucle par destinataire
    $is_appreciation_generale_enregistree = (isset($tab_saisie[$eleve_id][0])) ? TRUE : FALSE ;
    list($prof_id_appreciation_generale,$tab_appreciation_generale) = ($is_appreciation_generale_enregistree) ? each($tab_saisie[$eleve_id][0]) : array( 0 , array('prof_info'=>'','appreciation'=>'') ) ;
  }
  foreach($tab_destinataires[$eleve_id] as $numero_tirage => $tab_adresse)
  {
    // On met le document au nom de l'élève, ou on établit un document générique
    if($make_pdf)
    {
      if( ($make_officiel) && ($couleur=='non') )
      {
        // Le réglage ne semble pertinent que pour les exemplaires que l'établissement destine à l'impression.
        // L'exemplaire archivé est une copie destinée à être consultée et sa lecture est bien plus agréable en couleur.
        $couleur_tirage = ($numero_tirage==0) ? 'oui' : 'non' ;
        $releve_PDF->__set('couleur',$couleur_tirage);
      }
      $eleve_nb_lignes  = $tab_nb_lignes_total_eleve[$eleve_id] + $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite + $nb_lignes_prof_principal + $nb_lignes_supplementaires;
      $tab_infos_entete = (!$make_officiel) ? array($titre1,$titre2) : array($tab_etabl_coords,$tab_etabl_logo,$etabl_coords__bloc_hauteur,$tab_bloc_titres,$tab_adresse,$tag_date_heure_initiales,$eleve_genre,$date_naissance) ;
      $releve_PDF->entete( $tab_infos_entete , $break , $eleve_id , $eleve_nom , $eleve_prenom , $eleve_nb_lignes );
    }
    if($make_html)
    {
      if(!$make_officiel)
      {
        $releve_HTML .= ($eleve_id) ? $separation.'<h2>'.html($eleve_nom).' '.html($eleve_prenom).'</h2>'.NL : '<hr />'.NL.'<h2>Attestation générique</h2>'.NL ;
      }
      $releve_HTML .= '<table class="bilan">'.NL;
    }
    // Pour chaque pilier...
    if(count($tab_pilier))
    {
      foreach($tab_pilier as $pilier_id => $pilier_nom)
      {
        if( !$only_presence || isset($tab_contenu_presence['pilier'][$eleve_id][$pilier_id]) )
        {
          if( ($make_html) || ($make_pdf) )
          {
            $drapeau_langue = (in_array($pilier_id,$tab_langue_piliers)) ? $eleve_langue : 0 ;
            if($make_html)
            {
              $case_score = $test_affichage_Pourcentage ? '<th class="nu"></th>' : '' ;
              $case_valid = $test_affichage_Validation ? Html::td_validation( 'th' , $tab_user_pilier[$eleve_id][$pilier_id] , $detail=TRUE ) : '' ;
              $image_langue = ($drapeau_langue) ? ' <img src="./_img/drapeau/'.$drapeau_langue.'.gif" alt="" title="'.$tab_langues[$drapeau_langue]['texte'].'" />' : '' ;
              $releve_HTML .= '<tr>'.$case_score.'<th>'.html($pilier_nom).$image_langue.'</th>'.$case_valid.'<th class="nu"></th></tr>'.NL;
            }
            if($make_pdf)
            {
              $tab_pilier_validation = $test_affichage_Validation ? $tab_user_pilier[$eleve_id][$pilier_id] : array() ;
              $releve_PDF->pilier( $pilier_nom , $tab_nb_lignes[$eleve_id][$pilier_id] , $test_affichage_Validation , $tab_pilier_validation , $drapeau_langue );
            }
            // Pour chaque section...
            if(isset($tab_section[$pilier_id]))
            {
              foreach($tab_section[$pilier_id] as $section_id => $section_nom)
              {
                if( !$only_presence || isset($tab_contenu_presence['section'][$eleve_id][$pilier_id][$section_id]) )
                {
                  if($make_html)
                  {
                    $case_score = $test_affichage_Pourcentage ? '<th class="nu"></th>' : '' ;
                    $case_valid = '<th class="nu"></th>' ;
                    $releve_HTML .= '<tr>'.$case_score.'<th colspan="2">'.html($section_nom).'</th>'.$case_valid.'</tr>'.NL;
                  }
                  if($make_pdf)
                  {
                    $releve_PDF->section($section_nom);
                  }
                  // Pour chaque item du socle...
                  if(isset($tab_socle[$section_id]))
                  {
                    foreach($tab_socle[$section_id] as $socle_id => $socle_nom)
                    {
                      if( !$only_presence || isset($tab_contenu_presence['item'][$eleve_id][$pilier_id][$socle_id]) )
                      {
                        if($make_pdf)
                        {
                          $tab_item_pourcentage = $test_affichage_Pourcentage ? $tab_score_socle_eleve[$socle_id][$eleve_id] : array() ;
                          $tab_item_validation  = $test_affichage_Validation ? $tab_user_entree[$eleve_id][$socle_id] : array() ;
                          $releve_PDF->item($socle_nom,$test_affichage_Pourcentage,$tab_item_pourcentage,$test_affichage_Validation,$tab_item_validation);
                        }
                        if($make_html)
                        {
                          $socle_nom  = html($socle_nom);
                          $socle_nom  = (mb_strlen($socle_nom)<160) ? $socle_nom : mb_substr($socle_nom,0,150).' [...] <img src="./_img/bulle_aide.png" width="16" height="16" alt="" title="'.html($socle_nom).'" />'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
                          if( $tab_infos_socle_eleve[$socle_id][$eleve_id] )
                          {
                            $lien_toggle = '<a href="#toggle" class="'.$toggle_class.'" title="Voir / masquer le détail des items associés." id="to_'.$socle_id.'_'.$eleve_id.'"></a> ';
                            $div_competences = '<div id="'.$socle_id.'_'.$eleve_id.'"'.$toggle_etat.'>'.'<div>'.implode('</div><div>',$tab_infos_socle_eleve[$socle_id][$eleve_id]).'</div>'.'</div>';
                          }
                          else
                          {
                            $lien_toggle = '<span class="toggle_none"></span> ';
                            $div_competences = '';
                          }
                          $case_score = $test_affichage_Pourcentage ? Html::td_pourcentage( 'td' , $tab_score_socle_eleve[$socle_id][$eleve_id] , TRUE /*detail*/ , FALSE /*largeur*/ ) : '' ;
                          $case_valid = $test_affichage_Validation ? Html::td_validation( 'td' , $tab_user_entree[$eleve_id][$socle_id] , $detail=TRUE ) : '' ;
                          $releve_HTML .= '<tr>'.$case_score.'<td colspan="2">'.$lien_toggle.$socle_nom.$div_competences.'</td>'.$case_valid.'</tr>'.NL;
                        }
                      }
                    }
                  }
                }
              }
            }
            if( ($make_html) && ($make_officiel) && ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE_LONGUEUR']) )
            {
              $case_score = $test_affichage_Pourcentage ? '<th class="nu"></th>' : '' ;
              $case_valid = $test_affichage_Validation  ? '<th class="nu"></th>' : '' ;
              // État de maîtrise du socle - Info saisies périodes antérieures
              if(isset($tab_saisie_avant[$eleve_id][$pilier_id]))
              {
                $tab_periode_liens  = array();
                $tab_periode_textes = array();
                foreach($tab_saisie_avant[$eleve_id][$pilier_id] as $periode_ordre => $tab_prof)
                {
                  $tab_ligne = array();
                  foreach($tab_prof as $prof_id => $tab)
                  {
                    extract($tab);  // $periode_nom_avant $prof_info $appreciation $note
                    $tab_ligne[$prof_id] = html('['.$prof_info.'] '.$appreciation);
                  }
                  $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_'.$pilier_id.'_'.$periode_ordre.'"></a> '.html($periode_nom_avant);
                  $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_'.$pilier_id.'_'.$periode_ordre.'" class="appreciation bordertop hide">'.'<b>'.$periode_nom_avant.' :'.'</b>'.'<br />'.implode('<br />',$tab_ligne).'</div>';
                }
                $releve_HTML .= '<tr>'.$case_score.'<td colspan="2" class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</td>'.$case_valid.'</tr>'.NL;
              }
              // État de maîtrise du socle - Appréciations intermédiaires (HTML)
              if(isset($tab_saisie[$eleve_id][$pilier_id]))
              {
                foreach($tab_saisie[$eleve_id][$pilier_id] as $prof_id => $tab)
                {
                  extract($tab);  // $prof_info $appreciation $note
                  $actions = '';
                  if( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') && ($prof_id==$_SESSION['USER_ID']) )
                  {
                    $actions .= ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>';
                  }
                  elseif(in_array($BILAN_ETAT,array('2rubrique','3synthese')))
                  {
                    if($prof_id!=$_SESSION['USER_ID']) { $actions .= ' <button type="button" class="signaler">Signaler une faute</button>'; }
                    if($droit_corriger_appreciation)   { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
                  }
                  $releve_HTML .= '<tr id="appr_'.$pilier_id.'_'.$prof_id.'">'.$case_score.'<td colspan="2" class="now"><div class="notnow">'.html($prof_info).$actions.'</div><div class="appreciation">'.html($appreciation).'</div></td>'.$case_valid.'</tr>'.NL;
                }
              }
              if( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') )
              {
                if(!isset($tab_saisie[$eleve_id][$pilier_id][$_SESSION['USER_ID']]))
                {
                  $releve_HTML .= '<tr id="appr_'.$pilier_id.'_'.$_SESSION['USER_ID'].'">'.$case_score.'<td colspan="2" class="now"><div class="hc"><button type="button" class="ajouter">Ajouter une appréciation.</button></div></td>'.$case_valid.'</tr>'.NL;
                }
              }
            }
            if($make_html)
            {
              $releve_HTML .= '<tr><td class="nu"></td><td class="nu"></td><td class="nu"></td><td class="nu"></td></tr>'.NL; // En 4 cellules pour résoudre un pb de bordures sous Chrome
            }
          }
          // Examen de présence des appréciations intermédiaires
          if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE_LONGUEUR']) && (!isset($tab_saisie[$eleve_id][$pilier_id])) )
          {
            $tab_resultat_examen[$pilier_nom][] = 'Absence d\'appréciation pour '.html($eleve_nom.' '.$eleve_prenom);
          }
          // Impression des appréciations intermédiaires (PDF)
          if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_RUBRIQUE_LONGUEUR']) && (isset($tab_saisie[$eleve_id][$pilier_id])) )
          {
            $releve_PDF->appreciation_rubrique( $tab_saisie[$eleve_id][$pilier_id] );
          }
        }
      }
      // État de maîtrise du socle - Synthèse générale
      if( ($make_officiel) && ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_GENERALE_LONGUEUR']) && ( ($BILAN_ETAT=='3synthese') || ($make_action=='consulter') ) )
      {
        if($make_html)
        {
          $case_score = $test_affichage_Pourcentage ? '<th class="nu"></th>' : '' ;
          $case_valid = $test_affichage_Validation  ? '<th class="nu"></th>' : '' ;
          $releve_HTML .= '<tr>'.$case_score.'<th colspan="2">Synthèse générale</th>'.$case_valid.'</tr>'.NL;
          // État de maîtrise du socle - Info saisies périodes antérieures
          if(isset($tab_saisie_avant[$eleve_id][0]))
          {
            $tab_periode_liens  = array();
            $tab_periode_textes = array();
            foreach($tab_saisie_avant[$eleve_id][0] as $periode_ordre => $tab_prof)
            {
              $tab_ligne = array();
              foreach($tab_prof as $prof_id => $tab)
              {
                extract($tab);  // $periode_nom_avant $prof_info $appreciation $note
                $tab_ligne[$prof_id] = html('['.$prof_info.'] '.$appreciation);
              }
              $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_'.'0'.'_'.$periode_ordre.'"></a> '.html($periode_nom_avant);
              $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_'.'0'.'_'.$periode_ordre.'" class="appreciation bordertop hide">'.'<b>'.$periode_nom_avant.' :'.'</b>'.'<br />'.implode('<br />',$tab_ligne).'</div>';
            }
            $releve_HTML .= '<tr>'.$case_score.'<td colspan="2" class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</td>'.$case_valid.'</tr>'.NL;
          }
          // État de maîtrise du socle - Appréciation générale
          if($is_appreciation_generale_enregistree)
          {
            extract($tab_appreciation_generale);  // $prof_info $appreciation $note
            $actions = '';
            if( ($BILAN_ETAT=='3synthese') && ($make_action=='saisir') ) // Pas de test ($prof_id_appreciation_generale==$_SESSION['USER_ID']) car l'appréciation générale est unique avec saisie partagée.
            {
              $actions .= ' <button type="button" class="modifier">Modifier</button> <button type="button" class="supprimer">Supprimer</button>';
            }
            elseif(in_array($BILAN_ETAT,array('2rubrique','3synthese')))
            {
              if($prof_id_appreciation_generale!=$_SESSION['USER_ID']) { $actions .= ' <button type="button" class="signaler">Signaler une faute</button>'; }
              if($droit_corriger_appreciation)                         { $actions .= ' <button type="button" class="corriger">Corriger une faute</button>'; }
            }
            $releve_HTML .= '<tr id="appr_0_'.$prof_id_appreciation_generale.'">'.$case_score.'<td colspan="2" class="now"><div class="notnow">'.html($prof_info).$actions.'</div><div class="appreciation">'.html($appreciation).'</div></td>'.$case_valid.'</tr>'.NL;
          }
          elseif( ($BILAN_ETAT=='3synthese') && ($make_action=='saisir') )
          {
            $releve_HTML .= '<tr id="appr_0_'.$_SESSION['USER_ID'].'">'.$case_score.'<td colspan="2" class="now"><div class="hc"><button type="button" class="ajouter">Ajouter l\'appréciation générale.</button></div></td>'.$case_valid.'</tr>'.NL;
          }
        }
      }
      // Examen de présence de l'appréciation générale
      if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_GENERALE_LONGUEUR']) && (in_array(0,$tab_rubrique_id)) && (!$is_appreciation_generale_enregistree) )
      {
        $tab_resultat_examen['Synthèse générale'][] = 'Absence d\'appréciation générale pour '.html($eleve_nom.' '.$eleve_prenom);
      }
      // Impression de l'appréciation générale
      if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['SOCLE_APPRECIATION_GENERALE_LONGUEUR']) )
      {
        if($is_appreciation_generale_enregistree)
        {
          if( (($numero_tirage==0)&&($_SESSION['OFFICIEL']['ARCHIVE_RETRAIT_TAMPON_SIGNATURE'])) || ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='sans') || ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='tampon') && (!$tab_signature[0]) ) || ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature') && (!$tab_signature[$prof_id_appreciation_generale]) ) || ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature_ou_tampon') && (!$tab_signature[0]) && (!$tab_signature[$prof_id_appreciation_generale]) ) )
          {
            $tab_image_tampon_signature = NULL;
          }
          else
          {
            $tab_image_tampon_signature = ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature') || ( ($_SESSION['OFFICIEL']['TAMPON_SIGNATURE']=='signature_ou_tampon') && $tab_signature[$prof_id_appreciation_generale]) ) ? $tab_signature[$prof_id_appreciation_generale] : $tab_signature[0] ;
          }
        }
        else
        {
          $tab_image_tampon_signature = ( (($numero_tirage>0)||(!$_SESSION['OFFICIEL']['ARCHIVE_RETRAIT_TAMPON_SIGNATURE'])) && (in_array($_SESSION['OFFICIEL']['TAMPON_SIGNATURE'],array('tampon','signature_ou_tampon'))) ) ? $tab_signature[0] : NULL;
        }
        $releve_PDF->appreciation_generale( $prof_id_appreciation_generale , $tab_appreciation_generale , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite+$nb_lignes_prof_principal+$nb_lignes_supplementaires+$nb_lignes_legendes );
      }
    }
    if($make_html)
    {
      $releve_HTML .= '</table>'.NL;
    }
    $tab_pdf_lignes_additionnelles = array();
    // État de maîtrise du socle - Absences et retard
    if( ($make_officiel) && ($affichage_assiduite) )
    {
      $texte_assiduite = texte_ligne_assiduite($tab_assiduite[$eleve_id]);
      if($make_html)
      {
        $releve_HTML .= '<div class="i">'.$texte_assiduite.'</div>'.NL;
      }
      elseif($make_action=='imprimer')
      {
        $tab_pdf_lignes_additionnelles[] = $texte_assiduite;
      }
    }
    // État de maîtrise du socle - Professeurs principaux
    if( ($make_officiel) && ($affichage_prof_principal) )
    {
      if($make_html)
      {
        $releve_HTML .= '<div class="i">'.$texte_prof_principal.'</div>'.NL;
      }
      elseif($make_action=='imprimer')
      {
        $tab_pdf_lignes_additionnelles[] = $texte_prof_principal;
      }
    }
    // État de maîtrise du socle - Ligne additionnelle
    if( ($make_action=='imprimer') && ($nb_lignes_supplementaires) )
    {
      $tab_pdf_lignes_additionnelles[] = $_SESSION['OFFICIEL']['SOCLE_LIGNE_SUPPLEMENTAIRE'];
    }
    if(count($tab_pdf_lignes_additionnelles))
    {
      $releve_PDF->afficher_lignes_additionnelles($tab_pdf_lignes_additionnelles);
    }
    // État de maîtrise du socle - Date de naissance
    if( ($make_officiel) && ($date_naissance) && ( ($make_html) || ($make_graph) ) )
    {
      $releve_HTML .= '<div class="i">'.texte_ligne_naissance($date_naissance).'</div>'.NL;
    }
    // État de maîtrise du socle - Légende
    if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
    {
      if($make_html) { $releve_HTML .= $legende_html; }
      if($make_pdf)  { $releve_PDF->legende($test_affichage_Pourcentage,$test_affichage_Validation); }
    }
    // Indiquer a posteriori le nombre de pages par élève
    if($make_pdf)
    {
      $page_nb = $releve_PDF->reporter_page_nb();
      if( !empty($page_parite) && ($page_nb%2) )
      {
        $releve_PDF->ajouter_page_blanche();
      }
    }
    // Mémorisation des pages de début et de fin pour chaque élève pour découpe et archivage ultérieur
    if($make_action=='imprimer')
    {
      $page_debut = (isset($page_fin)) ? $page_fin+1 : 1 ;
      $page_fin   = $releve_PDF->page;
      $page_nombre = $page_fin - $page_debut + 1;
      $tab_pages_decoupe_pdf[$eleve_id][$numero_tirage] = array( $eleve_nom.' '.$eleve_prenom , $page_debut.'-'.$page_fin , $page_nombre );
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On enregistre les sorties HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($make_html) { FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.html' , $releve_HTML ); }
if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_nom.'.pdf'  , $releve_PDF  ); }

?>