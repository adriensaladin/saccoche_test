<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
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
 * [./pages/releve_items_matiere.ajax.php]
 * [./pages/releve_items_selection.ajax.php]
 * [./pages/releve_items_professeur.php]
 * [./pages/releve_items_multimatiere.ajax.php]
 * [./pages/brevet_moyennes.ajax.php]
 * [./_inc/code_officiel_***.php]
 */

Erreur500::prevention_et_gestion_erreurs_fatales( TRUE /*memory*/ , FALSE /*time*/ );

/*
$type_individuel | $type_synthese | $type_bulletin
$releve_modele [ matiere | selection | multimatiere | professeur ]
*/

// Chemins d'enregistrement

$fichier_nom = ($make_action!='imprimer') ? 'releve_item_'.$releve_modele.'_'.Clean::fichier($groupe_nom).'_<REPLACE>_'.fabriquer_fin_nom_fichier__date_et_alea() : 'officiel_'.$BILAN_TYPE.'_'.Clean::fichier($groupe_nom).'_'.fabriquer_fin_nom_fichier__date_et_alea() ;

// Si pas grille générique et si notes demandées ou besoin pour colonne bilan ou besoin pour synthèse
$calcul_acquisitions = ( $type_synthese || $type_bulletin || $aff_etat_acquisition ) ? TRUE : FALSE ;

// Initialisation de tableaux

$tab_item_infos       = array();  // [item_id] => array(item_ref,item_nom,item_coef,item_cart,item_socle,item_lien,calcul_methode,calcul_limite,calcul_retroactif);
$tab_matiere_item     = array();  // [matiere_id][item_id] => item_nom
$tab_eleve_infos      = array();  // [eleve_id] => array(eleve_nom,eleve_prenom,date_naissance,eleve_id_gepi)
$tab_matiere          = array();  // [matiere_id] => matiere_nom
$tab_eval             = array();  // [eleve_id][matiere_id][item_id][devoir] OU [matiere_id][item_id][eleve_id][devoir] => array(note,date,info)

// Initialisation de variables

if( ($make_html) || ($make_pdf) )
{
  $tab_titre = array(
    'matiere'      => 'd\'items - '.$matiere_nom ,
    'multimatiere' => 'd\'items pluridisciplinaire' ,
    'professeur'   => 'd\'items restreint à '.afficher_identite_initiale($_SESSION['USER_NOM'],FALSE,$_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_GENRE']) ,
    'selection'    => 'd\'items sélectionnés' ,
  );
  $info_ponderation_complete = ($with_coef) ? '(pondérée)' : '(non pondérée)' ;
  $info_ponderation_courte   = ($with_coef) ? 'pondérée'   : 'simple' ;
  if(!$aff_coef)  { $texte_coef       = ''; }
  if(!$aff_socle) { $texte_socle      = ''; }
  if(!$aff_lien)  { $texte_lien_avant = ''; }
  if(!$aff_lien)  { $texte_lien_apres = ''; }
  if(!$highlight_id) { $texte_fluo_avant = ''; }
  if(!$highlight_id) { $texte_fluo_apres = ''; }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Période concernée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($periode_id==0)
{
  $date_mysql_debut = convert_date_french_to_mysql($date_debut);
  $date_mysql_fin   = convert_date_french_to_mysql($date_fin);
}
else
{
  $DB_ROW = DB_STRUCTURE_COMMUN::DB_recuperer_dates_periode($groupe_id,$periode_id);
  if(empty($DB_ROW))
  {
    exit('La classe et la période ne sont pas reliées !');
  }
  $date_mysql_debut = $DB_ROW['jointure_date_debut'];
  $date_mysql_fin   = $DB_ROW['jointure_date_fin'];
  $date_debut = convert_date_mysql_to_french($date_mysql_debut);
  $date_fin   = convert_date_mysql_to_french($date_mysql_fin);
}
if($date_mysql_debut>$date_mysql_fin)
{
  exit('La date de début est postérieure à la date de fin !');
}

$tab_precision_retroactif = array
(
  'auto'   => 'notes antérieures selon référentiels',
  'oui'    => 'avec notes antérieures',
  'non'    => 'sans notes antérieures',
  'annuel' => 'notes antérieures de l\'année scolaire',
);
$precision_socle = $only_socle ? ', restriction au socle' : '' ;
$texte_periode = 'Du '.$date_debut.' au '.$date_fin.' ('.$tab_precision_retroactif[$retroactif].$precision_socle.').';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des items travaillés durant la période choisie, pour les élèves selectionnés, pour la ou les matières ou les items indiqués ou le prof indiqué
// Récupération de la liste des matières travaillées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($releve_modele=='matiere')
{
  $tab_item_infos = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_bilan($liste_eleve,$matiere_id,$only_socle,$date_mysql_debut,$date_mysql_fin,$aff_domaine,$aff_theme) ;
  $tab_matiere[$matiere_id] = $matiere_nom;
}
elseif($releve_modele=='multimatiere')
{
  $matiere_id = -1;
  list($tab_item_infos,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_bilan($liste_eleve,$matiere_id,$only_socle,$date_mysql_debut,$date_mysql_fin,$aff_domaine,$aff_theme);
}
elseif($releve_modele=='selection')
{
  $tab_compet_liste = (isset($_POST['f_compet_liste'])) ? explode('_',$_POST['f_compet_liste']) : array() ;
  $tab_compet_liste = Clean::map_entier($tab_compet_liste);
  $liste_compet = implode(',',$tab_compet_liste);
  list($tab_item_infos,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_selection($liste_eleve,$liste_compet,$date_mysql_debut,$date_mysql_fin,$aff_domaine,$aff_theme);
  // Si les items sont issus de plusieurs matières, alors on les regroupe en une seule.
  if(count($tab_matiere)>1)
  {
    $matiere_id = 0;
    $tab_matiere = array( 0 => implode(' - ',$tab_matiere) );
  }
  else
  {
    list($matiere_id,$matiere_nom) = each($tab_matiere);
  }
}
elseif($releve_modele=='professeur')
{
  list($tab_item_infos,$tab_matiere) = DB_STRUCTURE_BILAN::DB_recuperer_arborescence_professeur($liste_eleve,$prof_id,$only_socle,$date_mysql_debut,$date_mysql_fin,$aff_domaine,$aff_theme);
  // Si les items sont issus de plusieurs matières, alors on les regroupe en une seule.
  if(count($tab_matiere)>1)
  {
    $matiere_id = 0;
    $tab_matiere = array( 0 => implode(' - ',$tab_matiere) );
  }
  else
  {
    list($matiere_id,$matiere_nom) = each($tab_matiere);
  }
}

$item_nb = count($tab_item_infos);
if( !$item_nb && !$make_officiel && !$make_brevet ) // Dans le cas d'un bilan officiel, ou d'une récupération pour une fiche brevet, où l'on regarde les élèves d'un groupe un à un, ce ne doit pas être bloquant.
{
  exit('Aucun item évalué sur la période '.$date_debut.' ~ '.$date_fin.' selon les paramètres choisis !');
}
$liste_item = implode( ',' , array_keys($tab_item_infos) );

// A ce stade : $matiere_id est un entier positif ou -1 si multimatières ou 0 si sélection d'items issus de plusieurs matières

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des élèves
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($_SESSION['USER_PROFIL_TYPE']=='eleve')
{
  $tab_eleve_infos[$_SESSION['USER_ID']] = array('eleve_nom'=>$_SESSION['USER_NOM'],'eleve_prenom'=>$_SESSION['USER_PRENOM'],'date_naissance'=>NULL,'eleve_id_gepi'=>$_SESSION['USER_ID_GEPI']);
}
else
{
  $eleves_ordre = ($groupe_type=='Classes') ? 'alpha' : $eleves_ordre ;
  $tab_eleve_infos = DB_STRUCTURE_BILAN::DB_lister_eleves_cibles( $liste_eleve , $eleves_ordre , TRUE /*with_gepi*/ , FALSE /*with_langue*/ , FALSE /*with_brevet_serie*/ );
  if(!is_array($tab_eleve_infos))
  {
    exit('Aucun élève trouvé correspondant aux identifiants transmis !');
  }
}
$eleve_nb = count( $tab_eleve_infos , COUNT_NORMAL );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération de la liste des résultats des évaluations associées à ces items donnés d'une ou plusieurs matieres, pour les élèves selectionnés, sur la période sélectionnée
// Attention, il faut éliminer certains items qui peuvent potentiellement apparaitre dans des relevés d'élèves alors qu'ils n'ont pas été interrogés sur la période considérée (mais un camarade oui).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_score_a_garder = array();
if($item_nb) // Peut valoir 0 dans le cas d'un bilan officiel où l'on regarde les élèves d'un groupe un à un (il ne faut pas qu'un élève sans rien soit bloquant).
{
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_date_last_eleves_items($liste_eleve,$liste_item);
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']] = ($DB_ROW['date_last']<$date_mysql_debut) ? FALSE : TRUE ;
  }
  $date_mysql_debut_annee_scolaire = jour_debut_annee_scolaire('mysql');
      if($retroactif=='non')    { $date_mysql_start = $date_mysql_debut; }
  elseif($retroactif=='annuel') { $date_mysql_start = $date_mysql_debut_annee_scolaire; }
  else                          { $date_mysql_start = FALSE; } // 'oui' | 'auto' ; en 'auto' il faut faire le tri après
  $onlyprof = ($releve_modele=='professeur') ? $prof_id : FALSE ;
  $DB_TAB = DB_STRUCTURE_BILAN::DB_lister_result_eleves_items($liste_eleve , $liste_item , $matiere_id , $date_mysql_start , $date_mysql_fin , $_SESSION['USER_PROFIL_TYPE'] , $onlyprof );
  foreach($DB_TAB as $DB_ROW)
  {
    if($tab_score_a_garder[$DB_ROW['eleve_id']][$DB_ROW['item_id']])
    {
      $retro_item = $tab_item_infos[$DB_ROW['item_id']][0]['calcul_retroactif'];
      if( ($retroactif!='auto') || ($retro_item=='oui') || (($retro_item=='non')&&($DB_ROW['date']>=$date_mysql_debut)) || (($retro_item=='annuel')&&($DB_ROW['date']>=$date_mysql_debut_annee_scolaire)) )
      {
        $tab_eval[$DB_ROW['eleve_id']][$DB_ROW['matiere_id']][$DB_ROW['item_id']][] = array('note'=>$DB_ROW['note'],'date'=>$DB_ROW['date'],'info'=>$DB_ROW['info']);
        $tab_matiere_item[$DB_ROW['matiere_id']][$DB_ROW['item_id']] = $tab_item_infos[$DB_ROW['item_id']][0]['item_nom'];
      }
    }
  }
}
if( !count($tab_eval) && !$make_officiel && !$make_brevet ) // Dans le cas d'un bilan officiel, ou d'une récupération pour une fiche brevet, où l'on regarde les élèves d'un groupe un à un, ce ne doit pas être bloquant.
{
  exit('Aucune évaluation trouvée sur la période '.$date_debut.' ~ '.$date_fin.' selon les paramètres choisis !');
}
$matiere_nb = count( $tab_matiere_item , COUNT_NORMAL ); // 1 si $matiere_id >= 0 précédemment, davantage uniquement si $matiere_id = -1

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
// Tableaux et variables pour mémoriser les infos ; dans cette partie on ne fait que les calculs (aucun affichage)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_score_eleve_item         = array();  // Retenir les scores / élève / matière / item
$tab_score_item_eleve         = array();  // Retenir les scores / item / élève
$tab_moyenne_scores_eleve     = array();  // Retenir la moyenne des scores d'acquisitions / matière / élève
$tab_moyenne_scores_item      = array();  // Retenir la moyenne des scores d'acquisitions / item
$tab_pourcentage_acquis_eleve = array();  // Retenir le pourcentage d'items acquis / matière / élève
$tab_pourcentage_acquis_item  = array();  // Retenir le pourcentage d'items acquis / item
$tab_infos_acquis_eleve       = array();  // Retenir les infos (nb A - VA - NA) à l'origine du tableau $tab_pourcentage_acquis_eleve / matière / élève
$tab_infos_acquis_item        = array();  // Retenir les infos (nb A - VA - NA) à l'origine du tableau $tab_pourcentage_acquis_item / item
$moyenne_moyenne_scores       = 0;  // moyenne des moyennes des scores d'acquisitions
$moyenne_pourcentage_acquis   = 0;  // moyenne des moyennes des pourcentages d'items acquis

/*
  On renseigne :
  $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id]
  $tab_score_item_eleve[$item_id][$eleve_id]
  $tab_moyenne_scores_eleve[$matiere_id][$eleve_id]
  $tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id]
  $tab_infos_acquis_eleve[$matiere_id][$eleve_id]
*/

// Pour la synthèse d'items de plusieurs matières (/ élève)
$tab_total = array();

if($calcul_acquisitions)
{
  // Pour chaque élève...
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    if( ($matiere_nb>1) && $type_synthese )
    {
      $tab_total[$eleve_id] = array
      (
        'somme_scores_coefs'   => 0 ,
        'somme_scores_simples' => 0 ,
        'nb_coefs'             => 0 ,
        'nb_scores'            => 0 ,
        'nb_acquis'            => 0 ,
        'nb_non_acquis'        => 0 ,
        'nb_voie_acquis'       => 0 ,
      );
    }
    // Si cet élève a été évalué...
    if(isset($tab_eval[$eleve_id]))
    {
      // Pour chaque matiere...
      foreach($tab_matiere as $matiere_id => $matiere_nom)
      {
        // Si cet élève a été évalué dans cette matière...
        if(isset($tab_eval[$eleve_id][$matiere_id]))
        {
          // Pour chaque item...
          foreach($tab_eval[$eleve_id][$matiere_id] as $item_id => $tab_devoirs)
          {
            extract($tab_item_infos[$item_id][0]);  // $item_ref $item_nom $item_coef $item_socle $item_lien $calcul_methode $calcul_limite
            // calcul du bilan de l'item
            $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] = calculer_score($tab_devoirs,$calcul_methode,$calcul_limite);
            $tab_score_item_eleve[$item_id][$eleve_id] = $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id];
          }
          // calcul des bilans des scores
          $tableau_score_filtre = array_filter($tab_score_eleve_item[$eleve_id][$matiere_id],'non_vide');
          $nb_scores = count( $tableau_score_filtre );
          // la moyenne peut être pondérée par des coefficients
          $somme_scores_ponderes = 0;
          $somme_coefs = 0;
          if($nb_scores)
          {
            foreach($tableau_score_filtre as $item_id => $item_score)
            {
              $somme_scores_ponderes += $item_score*$tab_item_infos[$item_id][0]['item_coef'];
              $somme_coefs += $tab_item_infos[$item_id][0]['item_coef'];
            }
            $somme_scores_simples = array_sum($tableau_score_filtre);
            if( ($matiere_nb>1) && $type_synthese )
            {
              // Total multimatières avec ou sans coef
              $tab_total[$eleve_id]['somme_scores_coefs']   += $somme_scores_ponderes;
              $tab_total[$eleve_id]['somme_scores_simples'] += $somme_scores_simples;
              $tab_total[$eleve_id]['nb_coefs']             += $somme_coefs;
              $tab_total[$eleve_id]['nb_scores']            += $nb_scores;
            }
          }
          // ... un pour la moyenne des pourcentages d'acquisition
          if($with_coef) { $tab_moyenne_scores_eleve[$matiere_id][$eleve_id] = ($somme_coefs) ? round($somme_scores_ponderes/$somme_coefs,0) : FALSE ; }
          else           { $tab_moyenne_scores_eleve[$matiere_id][$eleve_id] = ($nb_scores)   ? round($somme_scores_simples/$nb_scores,0)    : FALSE ; }
          // ... un pour le nombre d'items considérés acquis ou pas
          if($nb_scores)
          {
            $nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
            $nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
            $nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
            $tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] = round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0);
            $tab_infos_acquis_eleve[$matiere_id][$eleve_id]       = $nb_acquis.$_SESSION['ACQUIS_TEXTE']['A'].' '. $nb_voie_acquis.$_SESSION['ACQUIS_TEXTE']['VA'].' '. $nb_non_acquis.$_SESSION['ACQUIS_TEXTE']['NA'];
            if( ($matiere_nb>1) && $type_synthese )
            {
              // Total multimatières
              $tab_total[$eleve_id]['nb_acquis']      += $nb_acquis;
              $tab_total[$eleve_id]['nb_non_acquis']  += $nb_non_acquis;
              $tab_total[$eleve_id]['nb_voie_acquis'] += $nb_voie_acquis;
            }
          }
          else
          {
            $tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] = FALSE;
            $tab_infos_acquis_eleve[$matiere_id][$eleve_id]       = FALSE;
          }
        }
      }
      if( ($matiere_nb>1) && $type_synthese )
      {
        // On prend la matière 0 pour mettre les résultats toutes matières confondues
        if($with_coef) { $tab_moyenne_scores_eleve[0][$eleve_id] = ($tab_total[$eleve_id]['nb_coefs'])  ? round($tab_total[$eleve_id]['somme_scores_coefs']  /$tab_total[$eleve_id]['nb_coefs'] ,0) : FALSE ; }
        else           { $tab_moyenne_scores_eleve[0][$eleve_id] = ($tab_total[$eleve_id]['nb_scores']) ? round($tab_total[$eleve_id]['somme_scores_simples']/$tab_total[$eleve_id]['nb_scores'],0) : FALSE ; }
        $tab_pourcentage_acquis_eleve[0][$eleve_id] = ($tab_total[$eleve_id]['nb_scores']) ? round( 50 * ( ($tab_total[$eleve_id]['nb_acquis']*2 + $tab_total[$eleve_id]['nb_voie_acquis']) / $tab_total[$eleve_id]['nb_scores'] ) ,0) : FALSE ;
      }
    }
  }
}

/*
  On renseigne (uniquement pour dans certains cas) :
  $tab_moyenne_scores_item[$item_id]
  $tab_pourcentage_acquis_item[$item_id]
  $tab_infos_acquis_item[$item_id]
*/

if( $type_synthese || ($releve_individuel_format=='item') )
{
  foreach($tab_matiere_item as $matiere_id=>$tab_item)  // Pour chaque item...
  {
    foreach($tab_item as $item_id=>$item_nom)
    {
      $tableau_score_filtre = isset($tab_score_item_eleve[$item_id]) ? array_filter($tab_score_item_eleve[$item_id],'non_vide') : array() ; // Test pour éviter de rares "array_filter() expects parameter 1 to be array, null given"
      $nb_scores = count( $tableau_score_filtre );
      if($nb_scores)
      {
        $somme_scores = array_sum($tableau_score_filtre);
        $nb_acquis      = count( array_filter($tableau_score_filtre,'test_A') );
        $nb_non_acquis  = count( array_filter($tableau_score_filtre,'test_NA') );
        $nb_voie_acquis = $nb_scores - $nb_acquis - $nb_non_acquis;
        $tab_moyenne_scores_item[$item_id]     = round($somme_scores/$nb_scores,0);
        $tab_pourcentage_acquis_item[$item_id] = round( 50 * ( ($nb_acquis*2 + $nb_voie_acquis) / $nb_scores ) ,0);
        $tab_infos_acquis_item[$item_id]       =  $nb_acquis.$_SESSION['ACQUIS_TEXTE']['A'].' '. $nb_voie_acquis.$_SESSION['ACQUIS_TEXTE']['VA'].' '. $nb_non_acquis.$_SESSION['ACQUIS_TEXTE']['NA'];
      }
      else
      {
        $tab_moyenne_scores_item[$item_id]     = FALSE;
        $tab_pourcentage_acquis_item[$item_id] = FALSE;
        $tab_infos_acquis_item[$item_id]       = FALSE;
      }
    }
  }
}

/*
  On renseigne (utile pour le tableau de synthèse et le bulletin) :
  $moyenne_moyenne_scores
  $moyenne_pourcentage_acquis
*/
/*
  on pourrait calculer de 2 façons chacune des deux valeurs...
  pour la moyenne des moyennes obtenues par élève : c'est simple car les coefs ont déjà été pris en compte dans le calcul pour chaque élève
  pour la moyenne des moyennes obtenues par item : c'est compliqué car il faudrait repondérer par les coefs éventuels de chaque item
  donc la 1ère technique a été retenue, à défaut d'essayer de calculer les deux et d'en faire la moyenne ;-)
*/

if( $type_synthese || $type_bulletin )
{
  if($item_nb) // Peut valoir 0 dans le cas d'une récupération pour une fiche brevet (il ne faut pas qu'un élève sans rien soit bloquant).
  {
    // $moyenne_moyenne_scores
    $somme  = array_sum($tab_moyenne_scores_eleve[$matiere_id]);
    $nombre = count( array_filter($tab_moyenne_scores_eleve[$matiere_id],'non_vide') );
    $moyenne_moyenne_scores = ($nombre) ? round($somme/$nombre,0) : FALSE ;
    // $moyenne_pourcentage_acquis
    $somme  = array_sum($tab_pourcentage_acquis_eleve[$matiere_id]);
    $nombre = count( array_filter($tab_pourcentage_acquis_eleve[$matiere_id],'non_vide') );
    $moyenne_pourcentage_acquis = ($nombre) ? round($somme/$nombre,0) : FALSE ;
  }
  else
  {
    $moyenne_moyenne_scores     = FALSE ;
    $moyenne_pourcentage_acquis = FALSE ;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Compter le nombre de lignes à afficher par élève par matière
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $type_individuel && ($releve_individuel_format=='eleve') )
{
  $nb_lignes_appreciation_intermediaire_par_prof_hors_intitule = ( $make_officiel && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE']<250) ) ? 1   : 2 ;
  $nb_lignes_appreciation_generale_avec_intitule               = ( $make_officiel && $_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE'] )       ? 1+6 : 0 ;
  $nb_lignes_assiduite                                         = ( $make_officiel && ($affichage_assiduite) )                                      ? 1.3 : 0 ;
  $nb_lignes_prof_principal                                    = ( $make_officiel && ($affichage_prof_principal) )                                 ? 1.3 : 0 ;
  $nb_lignes_supplementaires                                   = ( $make_officiel && $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE'] )        ? 1.3 : 0 ;
  $nb_lignes_legendes                                          = ($legende=='oui') ? 0.5 + 1 + ($retroactif!='non') + ($aff_etat_acquisition) : 0 ;
  $nb_lignes_matiere_intitule_et_marge                         = 1.5 ;
  $nb_lignes_matiere_synthese                                  = $aff_moyenne_scores + $aff_pourcentage_acquis ;
  // Usage d'un tableau intermédiaire pour dénombrer
  $tab_nb_lignes = array();
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    foreach($tab_matiere as $matiere_id => $matiere_nom)
    {
      if(isset($tab_eval[$eleve_id][$matiere_id])) // $tab_eval[] utilisé plutôt que $tab_score_eleve_item[] au cas où $calcul_acquisitions=FALSE
      {
        $tab_nb_lignes[$eleve_id][$matiere_id] = $nb_lignes_matiere_intitule_et_marge + count($tab_eval[$eleve_id][$matiere_id],COUNT_NORMAL) + $nb_lignes_matiere_synthese ;
        if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE']) && (isset($tab_saisie[$eleve_id][$matiere_id])) )
        {
          $tab_nb_lignes[$eleve_id][$matiere_id] += ($nb_lignes_appreciation_intermediaire_par_prof_hors_intitule * count($tab_saisie[$eleve_id][$matiere_id]) ) + 1 ; // + 1 pour "Appréciation / Conseils pour progresser"
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
  // plus besoin de ce tableau intermédiaire
  unset($tab_nb_lignes);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Compter le nombre de lignes à afficher par item
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $type_individuel && ($releve_individuel_format=='item') )
{
  $nb_lignes_item_intitule_et_marge = 1.5 ;
  $nb_lignes_item_synthese          = $aff_moyenne_scores + $aff_pourcentage_acquis ;
  $tab_nb_lignes_total_item = array();
  // On commence par recenser les items
  foreach($tab_matiere as $matiere_id => $matiere_nom)
  {
    if(isset($tab_matiere_item[$matiere_id]))
    {
      foreach($tab_matiere_item[$matiere_id] as $item_id => $item_nom)
      {
        $tab_nb_lignes_total_item[$item_id] = $nb_lignes_item_intitule_et_marge + $nb_lignes_item_synthese ;
      }
    }
  }
  // Puis on passe en revue les notes pour avoir le nombre d'élève par item
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    foreach($tab_matiere as $matiere_id => $matiere_nom)
    {
      if(isset($tab_eval[$eleve_id][$matiere_id])) // $tab_eval[] utilisé plutôt que $tab_score_eleve_item[] au cas où $calcul_acquisitions=FALSE
      {
        foreach($tab_eval[$eleve_id][$matiere_id] as $item_id => $tab)
        {
          $tab_nb_lignes_total_item[$item_id] += 1;
        }
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Nombre de boucles par élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Entre 1 et 3 pour les bilans officiels, dans ce cas $tab_destinataires[] est déjà complété ; une seule dans les autres cas.
if( ($releve_individuel_format=='eleve') && !isset($tab_destinataires) )
{
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    $tab_destinataires[$eleve_id][0] = TRUE ;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du bilan individuel, disciplinaire ou transdisciplinaire, en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$affichage_direct   = ( ( ( in_array($_SESSION['USER_PROFIL_TYPE'],array('eleve','parent')) ) && (SACoche!='webservices') ) || ($make_officiel) ) ? TRUE : FALSE ;
$affichage_checkbox = ( $type_synthese && ($_SESSION['USER_PROFIL_TYPE']=='professeur') && (SACoche!='webservices') )                             ? TRUE : FALSE ;

if($type_individuel)
{
  $jour_debut_annee_scolaire = jour_debut_annee_scolaire('mysql'); // Date de fin de l'année scolaire précédente
  if($make_html)
  {
    $bouton_print_appr = ($make_officiel)                     ? ' <button id="archiver_imprimer" type="button" class="imprimer">Archiver / Imprimer des données</button>'           : '' ;
    $bouton_print_test = (!empty($is_bouton_test_impression)) ? ' <button id="simuler_impression" type="button" class="imprimer">Simuler l\'impression finale de ce bilan</button>' : '' ;
    $bouton_import_csv = ($make_action=='saisir')             ? ' <button id="saisir_deport" type="button" class="fichier_export">Saisie déportée</button>'                         : '' ;
    $releve_HTML_individuel  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>'.NL;
    $releve_HTML_individuel .= $affichage_direct ? '' : '<h1>Bilan '.$tab_titre[$releve_modele].'</h1>'.NL;
    $releve_HTML_individuel .= $affichage_direct ? '' : '<h2>'.html($texte_periode).'</h2>'.NL;
    $releve_HTML_individuel .= ($bouton_print_appr || $bouton_print_test || $bouton_import_csv) ? '<div class="ti">'.$bouton_print_appr.$bouton_print_test.$bouton_import_csv.'</div>'.NL : '' ;
    $bilan_colspan = $cases_nb + 2 ;
    $separation = (count($tab_eleve_infos)>1) ? '<hr class="breakafter" />'.NL : '' ;
    $releve_HTML_individuel_javascript = '';
  }
  if($make_pdf)
  {
    // Appel de la classe et définition de qqs variables supplémentaires pour la mise en page PDF
    $releve_PDF = new PDF( $make_officiel , $orientation , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , $legende , !empty($is_test_impression) /*filigrane*/ );
  }
  /*
   * ********************************
   * Cas d'une présentation par élève
   * ********************************
   * Usage le plus courante, le seul envisagé et disponible pendant des années.
   * Un bilan officiel est toujours présenté par élève.
   */
  if($releve_individuel_format=='eleve')
  {
    if($make_pdf)
    {
      $lignes_nb = ($releve_modele!='multimatiere') ? array_sum($tab_nb_lignes_total_eleve) : 0 ;
      $aff_anciennete_notation = ($retroactif!='non') ? TRUE : FALSE ;
      $releve_PDF->bilan_item_individuel_initialiser( $releve_modele , $releve_individuel_format , $aff_etat_acquisition , $aff_anciennete_notation , $cases_nb , $cases_largeur , $lignes_nb , $eleve_nb , $pages_nb );
    }
    // Pour chaque élève...
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom $date_naissance $eleve_id_gepi
      $date_naissance = ($date_naissance) ? convert_date_mysql_to_french($date_naissance) : '' ;
      if($make_officiel)
      {
        // Quelques variables récupérées ici car pose pb si placé dans la boucle par destinataire
        $is_appreciation_generale_enregistree = (isset($tab_saisie[$eleve_id][0])) ? TRUE : FALSE ;
        list($prof_id_appreciation_generale,$tab_appreciation_generale) = ($is_appreciation_generale_enregistree) ? each($tab_saisie[$eleve_id][0]) : array( 0 , array('prof_info'=>'','appreciation'=>'') ) ;
      }
      foreach($tab_destinataires[$eleve_id] as $numero_tirage => $tab_adresse)
      {
        // Si cet élève a été évalué...
        if(isset($tab_eval[$eleve_id]))
        {
          // Intitulé
          if($make_html) { $releve_HTML_individuel .= (!$make_officiel) ? $separation.'<h2>'.html($groupe_nom).' - '.html($eleve_nom).' '.html($eleve_prenom).'</h2>'.NL : '' ; }
          if($make_pdf)
          {
            if( ($make_officiel) && ($couleur=='non') )
            {
              // Le réglage ne semble pertinent que pour les exemplaires que l'établissement destine à l'impression.
              // L'exemplaire archivé est une copie destinée à être consultée et sa lecture c'est bien plus agréable en couleur.
              $couleur_tirage = ($numero_tirage==0) ? 'oui' : 'non' ;
              $releve_PDF->__set('couleur',$couleur_tirage);
            }
            $eleve_nb_lignes  = $tab_nb_lignes_total_eleve[$eleve_id] + $nb_lignes_appreciation_generale_avec_intitule + $nb_lignes_assiduite + $nb_lignes_prof_principal + $nb_lignes_supplementaires;
            $tab_infos_entete = (!$make_officiel) ? array( $tab_titre[$releve_modele] , $texte_periode , $groupe_nom ) : array($tab_etabl_coords,$tab_etabl_logo,$etabl_coords__bloc_hauteur,$tab_bloc_titres,$tab_adresse,$tag_date_heure_initiales,$date_naissance) ;
            $releve_PDF->bilan_item_individuel_entete_format_eleve( $pages_nb , $tab_infos_entete , $eleve_nom , $eleve_prenom , $eleve_nb_lignes );
          }
          // Pour chaque matiere...
          foreach($tab_matiere as $matiere_id => $matiere_nom)
          {
            if( (!$make_officiel) || (($make_action=='saisir')&&($BILAN_ETAT=='2rubrique')&&(in_array($matiere_id,$tab_matiere_id))) || (($make_action=='saisir')&&($BILAN_ETAT=='3synthese')) || (($make_action=='examiner')&&(in_array($matiere_id,$tab_matiere_id))) || ($make_action=='consulter') || ($make_action=='imprimer') )
            {
              // Si cet élève a été évalué dans cette matière...
              if(isset($tab_eval[$eleve_id][$matiere_id]))
              {
                if( ($make_html) || ($make_pdf) )
                {
                  if( ($make_pdf) && ($releve_modele!='matiere') )
                  {
                    $item_matiere_nb = count($tab_eval[$eleve_id][$matiere_id]);
                    $releve_PDF->bilan_item_individuel_transdisciplinaire_ligne_matiere( $matiere_nom , $item_matiere_nb+$aff_moyenne_scores+$aff_pourcentage_acquis /*lignes_nb*/ );
                  }
                  if($make_html)
                  {
                    $releve_HTML_individuel .= '<h3>'.html($matiere_nom).'</h3>'.NL;
                    // On passe au tableau
                    $releve_HTML_table_head = '<thead><tr><th>Ref.</th><th>Nom de l\'item</th>';
                    if($cases_nb)
                    {
                      for($num_case=0;$num_case<$cases_nb;$num_case++)
                      {
                        $releve_HTML_table_head .= '<th></th>';  // Pas de colspan sinon pb avec le tri
                      }
                    }
                    $releve_HTML_table_head .= ($aff_etat_acquisition) ? '<th>score</th>' : '' ;
                    $releve_HTML_table_head .= '</tr></thead>'.NL;
                    $releve_HTML_table_body = '<tbody>'.NL;
                  }
                  // Pour chaque item...
                  foreach($tab_eval[$eleve_id][$matiere_id] as $item_id => $tab_devoirs)
                  {
                    extract($tab_item_infos[$item_id][0]);  // $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien $calcul_methode $calcul_limite $calcul_retroactif
                    // cases référence et nom
                    if($aff_coef)
                    {
                      $texte_coef = '['.$item_coef.'] ';
                    }
                    if($aff_socle)
                    {
                      $texte_socle = ($item_socle) ? '[S] ' : '[–] ';
                    }
                    if($make_html)
                    {
                      if($aff_lien)
                      {
                        $texte_lien_avant = ($item_lien) ? '<a target="_blank" href="'.html($item_lien).'">' : '';
                        $texte_lien_apres = ($item_lien) ? '</a>' : '';
                      }
                      if($highlight_id)
                      {
                        $texte_fluo_avant = ($highlight_id!=$item_id) ? '' : '<span class="fluo">';
                        $texte_fluo_apres = ($highlight_id!=$item_id) ? '' : '</span>';
                      }
                      $texte_demande_eval = ($_SESSION['USER_PROFIL_TYPE']!='eleve') ? '' : ( ($item_cart) ? '<q class="demander_add" id="demande_'.$matiere_id.'_'.$item_id.'_'.$tab_score_eleve_item[$eleve_id][$matiere_id][$item_id].'" title="Ajouter aux demandes d\'évaluations."></q>' : '<q class="demander_non" title="Demande interdite."></q>' ) ;
                      $releve_HTML_table_body .= '<tr><td>'.$item_ref.'</td><td>'.$texte_coef.$texte_socle.$texte_lien_avant.$texte_fluo_avant.html($item_nom).$texte_fluo_apres.$texte_lien_apres.$texte_demande_eval.'</td>';
                    }
                    if($make_pdf)
                    {
                      $releve_PDF->bilan_item_individuel_debut_ligne_item( $item_ref , $texte_coef.$texte_socle.$item_nom );
                    }
                    // cases d'évaluations
                    $devoirs_nb = count($tab_devoirs);
                    // on passe en revue les cases disponibles et on remplit en fonction des évaluations disponibles
                    if($cases_nb)
                    {
                      $decalage = $devoirs_nb - $cases_nb;
                      for($i=0;$i<$cases_nb;$i++)
                      {
                        // on doit remplir une case
                        if($decalage<0)
                        {
                          // il y a moins d'évaluations que de cases à remplir : on met un score dispo ou une case blanche si plus de score dispo
                          if($i<$devoirs_nb)
                          {
                            extract($tab_devoirs[$i]);  // $note $date $info
                            $pdf_bg = ''; $td_class = '';
                            if($date<$jour_debut_annee_scolaire)
                            {
                              $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_year' : '' ;
                              $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_year"' : '' ;
                            }
                            elseif($date<$date_mysql_debut)
                            {
                              $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_date' : '' ;
                              $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_date"' : '' ;
                            }
                            if($make_html) { $releve_HTML_table_body .= '<td'.$td_class.'>'.Html::note($note,$date,$info,TRUE).'</td>'; }
                            if($make_pdf)  { $releve_PDF->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ , $pdf_bg ); }
                          }
                          else
                          {
                            if($make_html) { $releve_HTML_table_body .= '<td>&nbsp;</td>'; }
                            if($make_pdf)  { $releve_PDF->afficher_note_lomer( '' /*note*/ , 1 /*border*/ , 0 /*br*/ ); }
                          }
                        }
                        // il y a plus d'évaluations que de cases à remplir : on ne prend que les dernières (décalage d'indice)
                        else
                        {
                          extract($tab_devoirs[$i+$decalage]);  // $note $date $info
                          $pdf_bg = ''; $td_class = '';
                          if($date<$jour_debut_annee_scolaire)
                          {
                            $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_year' : '' ;
                            $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_year"' : '' ;
                          }
                          elseif($date<$date_mysql_debut)
                          {
                            $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_date' : '' ;
                            $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_date"' : '' ;
                          }
                          if($make_html) { $releve_HTML_table_body .= '<td'.$td_class.'>'.Html::note($note,$date,$info,TRUE).'</td>'; }
                          if($make_pdf)  { $releve_PDF->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ , $pdf_bg ); }
                        }
                      }
                    }
                    // affichage du bilan de l'item
                    if($aff_etat_acquisition)
                    {
                      if($make_html) { $releve_HTML_table_body .= Html::td_score($tab_score_eleve_item[$eleve_id][$matiere_id][$item_id],'score','' /*pourcent*/ ,'' /*checkbox_val*/ ,$make_officiel).'</tr>'.NL; }
                      if($make_pdf)  { $releve_PDF->afficher_score_bilan( $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] , 1 /*br*/ ); }
                    }
                    else
                    {
                      if($make_html) { $releve_HTML_table_body .= '</tr>'.NL; }
                      if($make_pdf)  { $releve_PDF->SetXY( $releve_PDF->marge_gauche , $releve_PDF->GetY()+$releve_PDF->cases_hauteur ); }
                    }
                  }
                  if($make_html)
                  {
                    $releve_HTML_table_body .= '</tbody>'.NL;
                    $releve_HTML_table_foot = '';
                  }
                  // affichage des bilans des scores
                  if($aff_etat_acquisition)
                  {
                    // ... un pour la moyenne des pourcentages d'acquisition
                    if( $aff_moyenne_scores )
                    {
                      if($tab_moyenne_scores_eleve[$matiere_id][$eleve_id] !== FALSE)
                      {
                        $texte_bilan  = $tab_moyenne_scores_eleve[$matiere_id][$eleve_id].'%';
                        $texte_bilan .= ($conversion_sur_20) ? ' soit '.sprintf("%04.1f",$tab_moyenne_scores_eleve[$matiere_id][$eleve_id]/5).'/20' : '' ;
                      }
                      else
                      {
                        $texte_bilan = '---';
                      }
                      if($make_html) { $releve_HTML_table_foot .= '<tr><td class="nu">&nbsp;</td><td colspan="'.$bilan_colspan.'">Moyenne '.$info_ponderation_complete.' des scores d\'acquisitions : '.$texte_bilan.'</td></tr>'.NL; }
                      if($make_pdf)  { $releve_PDF->bilan_item_individuel_ligne_synthese('Moyenne '.$info_ponderation_complete.' des scores d\'acquisitions : '.$texte_bilan); }
                    }
                    // ... un pour le nombre d'items considérés acquis ou pas
                    if( $aff_pourcentage_acquis )
                    {
                      if($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] !== FALSE)
                      {
                        $texte_bilan  = '('.$tab_infos_acquis_eleve[$matiere_id][$eleve_id].') : '.$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id].'%';
                        $texte_bilan .= ($conversion_sur_20) ? ' soit '.sprintf("%04.1f",$tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id]/5).'/20' : '' ;
                      }
                      else
                      {
                        $texte_bilan = '---';
                      }
                      if($make_html) { $releve_HTML_table_foot .= '<tr><td class="nu">&nbsp;</td><td colspan="'.$bilan_colspan.'">Pourcentage d\'items acquis '.$texte_bilan.'</td></tr>'.NL; }
                      if($make_pdf)  { $releve_PDF->bilan_item_individuel_ligne_synthese('Pourcentage d\'items acquis '.$texte_bilan); }
                    }
                  }
                  if($make_html)
                  {
                    $releve_HTML_table_foot = ($releve_HTML_table_foot) ? '<tfoot>'.NL.$releve_HTML_table_foot.'</tfoot>'.NL : '';
                    $releve_HTML_individuel .= '<table id="table'.$eleve_id.'x'.$matiere_id.'" class="bilan hsort">'.NL.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body.'</table>'.NL;
                    $releve_HTML_individuel_javascript .= '$("#table'.$eleve_id.'x'.$matiere_id.'").tablesorter();';
                  }
                  if( ($make_html) && ($make_officiel) && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE']) )
                  {
                    // Relevé de notes - Info saisies périodes antérieures
                    $appreciations_avant = '';
                    if(isset($tab_saisie_avant[$eleve_id][$matiere_id]))
                    {
                      $tab_periode_liens  = array();
                      $tab_periode_textes = array();
                      foreach($tab_saisie_avant[$eleve_id][$matiere_id] as $periode_ordre => $tab_prof)
                      {
                        $tab_ligne = array();
                        foreach($tab_prof as $prof_id => $tab)
                        {
                          extract($tab);  // $periode_nom_avant $prof_info $appreciation $note
                          $tab_ligne[$prof_id] = html('['.$prof_info.'] '.$appreciation);
                        }
                        $tab_periode_liens[]  = '<a href="#toggle" class="toggle_plus" title="Voir / masquer les informations de cette période." id="to_avant_'.$eleve_id.'_'.$matiere_id.'_'.$periode_ordre.'"></a> '.html($periode_nom_avant);
                        $tab_periode_textes[] = '<div id="avant_'.$eleve_id.'_'.$matiere_id.'_'.$periode_ordre.'" class="appreciation bordertop hide">'.'<b>'.$periode_nom_avant.' :'.'</b>'.'<br />'.implode('<br />',$tab_ligne).'</div>';
                      }
                      $appreciations_avant = '<tr><td class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</td></tr>'.NL;
                    }
                    // Relevé de notes - Appréciations intermédiaires (HTML)
                    $appreciations = '';
                    if(isset($tab_saisie[$eleve_id][$matiere_id]))
                    {
                      foreach($tab_saisie[$eleve_id][$matiere_id] as $prof_id => $tab)
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
                        $appreciations .= '<tr id="appr_'.$matiere_id.'_'.$prof_id.'"><td class="now"><div class="notnow">'.html($prof_info).$actions.'</div><div class="appreciation">'.html($appreciation).'</div></td></tr>'.NL;
                      }
                    }
                    if( ($BILAN_ETAT=='2rubrique') && ($make_action=='saisir') )
                    {
                      if(!isset($tab_saisie[$eleve_id][$matiere_id][$_SESSION['USER_ID']]))
                      {
                        $appreciations .= '<tr id="appr_'.$matiere_id.'_'.$_SESSION['USER_ID'].'"><td class="now"><div class="hc"><button type="button" class="ajouter">Ajouter une appréciation.</button></div></td></tr>'.NL;
                      }
                    }
                    $releve_HTML_individuel .= ($appreciations_avant || $appreciations) ? '<table style="width:900px" class="bilan"><tbody>'.NL.$appreciations_avant.$appreciations.'</tbody></table>'.NL : '' ;
                  }
                }
                // Examen de présence des appréciations intermédiaires
                if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE']) && (!isset($tab_saisie[$eleve_id][$matiere_id])) )
                {
                  $tab_resultat_examen[$matiere_nom][] = 'Absence d\'appréciation pour '.html($eleve_nom.' '.$eleve_prenom);
                }
                // Impression des appréciations intermédiaires (PDF)
                if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_RUBRIQUE']) && (isset($tab_saisie[$eleve_id][$matiere_id])) )
                {
                  $releve_PDF->bilan_item_individuel_appreciation_rubrique( $tab_saisie[$eleve_id][$matiere_id] );
                }
              }
            }
          }
          // Relevé de notes - Synthèse générale
          if( ($make_officiel) && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE']) && ( ($BILAN_ETAT=='3synthese') || ($make_action=='consulter') ) )
          {
            if($make_html)
            {
              $releve_HTML_individuel .= '<h3>Synthèse générale</h3>'.NL.'<table style="width:900px" class="bilan"><tbody>'.NL;
              // Relevé de notes - Info saisies périodes antérieures
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
                $releve_HTML_individuel .= '<tr><td class="avant">'.implode('&nbsp;&nbsp;&nbsp;',$tab_periode_liens).implode('',$tab_periode_textes).'</td></tr>'.NL;
              }
              // Relevé de notes - Appréciation générale
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
                $releve_HTML_individuel .= '<tr id="appr_0_'.$prof_id_appreciation_generale.'"><td class="now"><div class="notnow">'.html($prof_info).$actions.'</div><div class="appreciation">'.html($appreciation).'</div></td></tr>'.NL;
              }
              elseif( ($BILAN_ETAT=='3synthese') && ($make_action=='saisir') )
              {
                $releve_HTML_individuel .= '<tr id="appr_0_'.$_SESSION['USER_ID'].'"><td class="now"><div class="hc"><button type="button" class="ajouter">Ajouter l\'appréciation générale.</button></div></td></tr>'.NL;
              }
              $releve_HTML_individuel .= '</tbody></table>'.NL;
            }
          }
          // Examen de présence de l'appréciation générale
          if( ($make_action=='examiner') && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE']) && (in_array(0,$tab_rubrique_id)) && (!$is_appreciation_generale_enregistree) )
          {
            $tab_resultat_examen['Synthèse générale'][] = 'Absence d\'appréciation générale pour '.html($eleve_nom.' '.$eleve_prenom);
          }
          // Impression de l'appréciation générale
          if( ($make_action=='imprimer') && ($_SESSION['OFFICIEL']['RELEVE_APPRECIATION_GENERALE']) )
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
            $releve_PDF->bilan_item_individuel_appreciation_generale( $prof_id_appreciation_generale , $tab_appreciation_generale , $tab_image_tampon_signature , $nb_lignes_appreciation_generale_avec_intitule , $nb_lignes_assiduite+$nb_lignes_prof_principal+$nb_lignes_supplementaires+$nb_lignes_legendes );
          }
          $tab_pdf_lignes_additionnelles = array();
          // Relevé de notes - Absences et retard
          if( ($make_officiel) && ($affichage_assiduite) )
          {
            $texte_assiduite = texte_ligne_assiduite($tab_assiduite[$eleve_id]);
            if($make_html)
            {
              $releve_HTML_individuel .= '<div class="i">'.$texte_assiduite.'</div>'.NL;
            }
            elseif($make_action=='imprimer')
            {
              $tab_pdf_lignes_additionnelles[] = $texte_assiduite;
            }
          }
          // Relevé de notes - Professeurs principaux
          if( ($make_officiel) && ($affichage_prof_principal) )
          {
            if($make_html)
            {
              $releve_HTML_individuel .= '<div class="i">'.$texte_prof_principal.'</div>'.NL;
            }
            elseif($make_action=='imprimer')
            {
              $tab_pdf_lignes_additionnelles[] = $texte_prof_principal;
            }
          }
          // Relevé de notes - Ligne additionnelle
          if( ($make_action=='imprimer') && ($nb_lignes_supplementaires) )
          {
            $tab_pdf_lignes_additionnelles[] = $_SESSION['OFFICIEL']['RELEVE_LIGNE_SUPPLEMENTAIRE'];
          }
          if(count($tab_pdf_lignes_additionnelles))
          {
            $releve_PDF->afficher_lignes_additionnelles($tab_pdf_lignes_additionnelles);
          }
          // Relevé de notes - Date de naissance
          if( ($make_officiel) && ($date_naissance) && ( ($make_html) || ($make_graph) ) )
          {
            $releve_HTML_individuel .= '<div class="i">'.texte_ligne_naissance($date_naissance).'</div>'.NL;
          }
          // Relevé de notes - Légende
          if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
          {
            if($make_html) { $releve_HTML_individuel .= Html::legende( TRUE /*codes_notation*/ , ($retroactif!='non') /*anciennete_notation*/ , $aff_etat_acquisition /*score_bilan*/ , FALSE /*etat_acquisition*/ , FALSE /*pourcentage_acquis*/ , FALSE /*etat_validation*/ , $make_officiel ); }
            if($make_pdf)  { $releve_PDF->bilan_item_individuel_legende(); }
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
    }
  }
  /*
   * *******************************
   * Cas d'une présentation par item
   * *******************************
   * Usage marginal (nouvelle fonctionnalité août 2014).
   * Un bilan officiel n'est jamais présenté par item.
   */
  elseif($releve_individuel_format=='item')
  {
    if($make_pdf)
    {
      $lignes_nb = array_sum($tab_nb_lignes_total_item) + ($matiere_nb*1.5) ;
      $aff_anciennete_notation = ($retroactif!='non') ? TRUE : FALSE ;
      $releve_PDF->bilan_item_individuel_initialiser( $releve_modele , $releve_individuel_format , $aff_etat_acquisition , $aff_anciennete_notation , $cases_nb , $cases_largeur , $lignes_nb , $item_nb , $pages_nb );
      $releve_PDF->bilan_item_individuel_entete_format_item( $tab_titre[$releve_modele] , $texte_periode , $groupe_nom );
    }
    // Pour chaque matiere...
    foreach($tab_matiere as $matiere_id => $matiere_nom)
    {
      if(isset($tab_matiere_item[$matiere_id]))
      {
        if($make_html) { $releve_HTML_individuel .= $separation.'<h2>'.html($groupe_nom.' - '.$matiere_nom).'</h2>'.NL; }
        // Pour chaque item...
        foreach($tab_matiere_item[$matiere_id] as $item_id => $item_nom)
        {
          extract($tab_item_infos[$item_id][0]);  // $item_ref $item_nom $item_coef $item_cart $item_socle $item_lien $calcul_methode $calcul_limite $calcul_retroactif
          if($aff_coef)
          {
            $texte_coef = '['.$item_coef.'] ';
          }
          if($aff_socle)
          {
            $texte_socle = ($item_socle) ? '[S] ' : '[–] ';
          }
          if($make_html)
          {
            if($aff_lien)
            {
              $texte_lien_avant = ($item_lien) ? '<a target="_blank" href="'.html($item_lien).'">' : '';
              $texte_lien_apres = ($item_lien) ? '</a>' : '';
            }
            if($highlight_id)
            {
              $texte_fluo_avant = ($highlight_id!=$item_id) ? '' : '<span class="fluo">';
              $texte_fluo_apres = ($highlight_id!=$item_id) ? '' : '</span>';
            }
            $texte_demande_eval = ''; // sans objet car un élève ne peut pas passer par ici
          }
          // Intitulé
          if($make_pdf)
          {
            $releve_PDF->bilan_item_individuel_format_item_ligne_item( $item_ref.' - '.$texte_coef.$texte_socle.$item_nom , $tab_nb_lignes_total_item[$item_id] /*lignes_nb*/ );
          }
          if($make_html)
          {
            $releve_HTML_individuel .= $separation.'<h3>'.$item_ref.' - '.$texte_coef.$texte_socle.$texte_lien_avant.$texte_fluo_avant.html($item_nom).$texte_fluo_apres.$texte_lien_apres.$texte_demande_eval.'</h3>'.NL;
            // On passe au tableau
            $releve_HTML_table_head = '<thead><tr><th>Élève</th>';
            if($cases_nb)
            {
              for($num_case=0;$num_case<$cases_nb;$num_case++)
              {
                $releve_HTML_table_head .= '<th></th>';  // Pas de colspan sinon pb avec le tri
              }
            }
            $releve_HTML_table_head .= ($aff_etat_acquisition) ? '<th>score</th>' : '' ;
            $releve_HTML_table_head .= '</tr></thead>'.NL;
            $releve_HTML_table_body = '<tbody>'.NL;
          }
          // Pour chaque élève...
          foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
          {
            // Si cet élève a été évalué sur cet item...
            if(isset($tab_eval[$eleve_id][$matiere_id][$item_id]))
            {
              extract($tab_eleve);  // $eleve_nom $eleve_prenom $date_naissance $eleve_id_gepi
              $releve_HTML_table_body .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td>';
              if($make_pdf)
              {
                $releve_PDF->bilan_item_individuel_debut_ligne_eleve($eleve_nom.' '.$eleve_prenom);
              }
              // cases d'évaluations
              $tab_devoirs = $tab_eval[$eleve_id][$matiere_id][$item_id];
              $devoirs_nb = count($tab_devoirs);
              // on passe en revue les cases disponibles et on remplit en fonction des évaluations disponibles
              if($cases_nb)
              {
                $decalage = $devoirs_nb - $cases_nb;
                for($i=0;$i<$cases_nb;$i++)
                {
                  // on doit remplir une case
                  if($decalage<0)
                  {
                    // il y a moins d'évaluations que de cases à remplir : on met un score dispo ou une case blanche si plus de score dispo
                    if($i<$devoirs_nb)
                    {
                      extract($tab_devoirs[$i]);  // $note $date $info
                      $pdf_bg = ''; $td_class = '';
                      if($date<$jour_debut_annee_scolaire)
                      {
                        $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_year' : '' ;
                        $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_year"' : '' ;
                      }
                      elseif($date<$date_mysql_debut)
                      {
                        $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_date' : '' ;
                        $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_date"' : '' ;
                      }
                      if($make_html) { $releve_HTML_table_body .= '<td'.$td_class.'>'.Html::note($note,$date,$info,TRUE).'</td>'; }
                      if($make_pdf)  { $releve_PDF->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ , $pdf_bg ); }
                    }
                    else
                    {
                      if($make_html) { $releve_HTML_table_body .= '<td>&nbsp;</td>'; }
                      if($make_pdf)  { $releve_PDF->afficher_note_lomer( '' /*note*/ , 1 /*border*/ , 0 /*br*/ ); }
                    }
                  }
                  // il y a plus d'évaluations que de cases à remplir : on ne prend que les dernières (décalage d'indice)
                  else
                  {
                    extract($tab_devoirs[$i+$decalage]);  // $note $date $info
                    $pdf_bg = ''; $td_class = '';
                    if($date<$jour_debut_annee_scolaire)
                    {
                      $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_year' : '' ;
                      $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_year"' : '' ;
                    }
                    elseif($date<$date_mysql_debut)
                    {
                      $pdf_bg = ( (!$_SESSION['USER_DALTONISME']) || ($couleur=='non') ) ? 'prev_date' : '' ;
                      $td_class = (!$_SESSION['USER_DALTONISME']) ? ' class="prev_date"' : '' ;
                    }
                    if($make_html) { $releve_HTML_table_body .= '<td'.$td_class.'>'.Html::note($note,$date,$info,TRUE).'</td>'; }
                    if($make_pdf)  { $releve_PDF->afficher_note_lomer( $note , 1 /*border*/ , 0 /*br*/ , $pdf_bg ); }
                  }
                }
              }
              // affichage du bilan de l'item
              if($aff_etat_acquisition)
              {
                if($make_html) { $releve_HTML_table_body .= Html::td_score($tab_score_eleve_item[$eleve_id][$matiere_id][$item_id],'score','' /*pourcent*/ ,'' /*checkbox_val*/ ,$make_officiel).'</tr>'.NL; }
                if($make_pdf)  { $releve_PDF->afficher_score_bilan( $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] , 1 /*br*/ ); }
              }
              else
              {
                if($make_html) { $releve_HTML_table_body .= '</tr>'.NL; }
                if($make_pdf)  { $releve_PDF->SetXY( $releve_PDF->marge_gauche , $releve_PDF->GetY()+$releve_PDF->cases_hauteur ); }
              }
            }
          }
          if($make_html)
          {
            $releve_HTML_table_body .= '</tbody>'.NL;
            $releve_HTML_table_foot = '';
          }
          // affichage des bilans des scores
          if($aff_etat_acquisition)
          {
            // ... un pour la moyenne des pourcentages d'acquisition
            if( $aff_moyenne_scores )
            {
              if($tab_moyenne_scores_item[$item_id] !== FALSE)
              {
                $texte_bilan  = $tab_moyenne_scores_item[$item_id].'%';
                $texte_bilan .= ($conversion_sur_20) ? ' soit '.sprintf("%04.1f",$tab_moyenne_scores_item[$item_id]/5).'/20' : '' ;
              }
              else
              {
                $texte_bilan = '---';
              }
              if($make_html) { $releve_HTML_table_foot .= '<tr><td colspan="'.$bilan_colspan.'">Moyenne des scores d\'acquisitions : '.$texte_bilan.'</td></tr>'.NL; } // indication de pondération inopportune
              if($make_pdf)  { $releve_PDF->bilan_item_individuel_ligne_synthese('Moyenne des scores d\'acquisitions : '.$texte_bilan); } // indication de pondération inopportune
            }
            // ... un pour le nombre d'items considérés acquis ou pas
            if( $aff_pourcentage_acquis )
            {
              if($tab_pourcentage_acquis_item[$item_id] !== FALSE)
              {
                $texte_bilan  = '('.$tab_infos_acquis_item[$item_id].') : '.$tab_pourcentage_acquis_item[$item_id].'%';
                $texte_bilan .= ($conversion_sur_20) ? ' soit '.sprintf("%04.1f",$tab_pourcentage_acquis_item[$item_id]/5).'/20' : '' ;
              }
              else
              {
                $texte_bilan = '---';
              }
              if($make_html) { $releve_HTML_table_foot .= '<tr><td colspan="'.$bilan_colspan.'">Pourcentage d\'items acquis '.$texte_bilan.'</td></tr>'.NL; }
              if($make_pdf)  { $releve_PDF->bilan_item_individuel_ligne_synthese('Pourcentage d\'items acquis '.$texte_bilan); }
            }
          }
          if($make_html)
          {
            $releve_HTML_table_foot = ($releve_HTML_table_foot) ? '<tfoot>'.NL.$releve_HTML_table_foot.'</tfoot>'.NL : '';
            $releve_HTML_individuel .= '<table id="table'.$matiere_id.'x'.$item_id.'" class="bilan hsort">'.NL.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body.'</table>'.NL;
            $releve_HTML_individuel_javascript .= '$("#table'.$matiere_id.'x'.$item_id.'").tablesorter();';
          }
        }
        // Relevé de notes - Légende
        if( ( ($make_html) || ($make_pdf) ) && ($legende=='oui') )
        {
          if($make_html) { $releve_HTML_individuel .= Html::legende( TRUE /*codes_notation*/ , ($retroactif!='non') /*anciennete_notation*/ , $aff_etat_acquisition /*score_bilan*/ , FALSE /*etat_acquisition*/ , FALSE /*pourcentage_acquis*/ , FALSE /*etat_validation*/ , $make_officiel ); }
          if($make_pdf)  { $releve_PDF->bilan_item_individuel_legende(); }
        }
      }
    }
  }
  // Ajout du javascript en fin de fichier
  $releve_HTML_individuel .= '<script type="text/javascript">'.$releve_HTML_individuel_javascript.'</script>'.NL;
  // On enregistre les sorties HTML et PDF
  if($make_html) { FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.html' , $releve_HTML_individuel ); }
  if($make_pdf)  { FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','individuel',$fichier_nom).'.pdf'  , $releve_PDF ); }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration de la synthèse collective en HTML et PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($type_synthese)
{
  $matiere_et_groupe = ($releve_modele=='matiere') ? $matiere_nom.' - '.$groupe_nom : $groupe_nom ;
  $releve_HTML_synthese  = $affichage_direct ? '' : '<style type="text/css">'.$_SESSION['CSS'].'</style>'.NL;
  $releve_HTML_synthese .= $affichage_direct ? '' : '<h1>Bilan '.$tab_titre[$releve_modele].'</h1>'.NL;
  $releve_HTML_synthese .= '<h2>'.html($matiere_et_groupe).'</h2>'.NL;
  if($texte_periode)
  {
    $releve_HTML_synthese .= '<h2>'.html($texte_periode).'</h2>'.NL;
  }
  // Appel de la classe et redéfinition de qqs variables supplémentaires pour la mise en page PDF
  // On définit l'orientation la plus adaptée
  $orientation = ( ( ($eleve_nb>$item_nb) && ($tableau_synthese_format=='eleve') ) || ( ($item_nb>$eleve_nb) && ($tableau_synthese_format=='item') ) ) ? 'portrait' : 'landscape' ;
  $releve_PDF = new PDF( $make_officiel , $orientation , $marge_gauche , $marge_droite , $marge_haut , $marge_bas , $couleur , 'oui' /*legende*/ );
  $releve_PDF->bilan_periode_synthese_initialiser($eleve_nb,$item_nb,$tableau_synthese_format);
  $releve_PDF->bilan_periode_synthese_entete($tab_titre[$releve_modele],$matiere_et_groupe,$texte_periode);
  // 1ère ligne
  $releve_PDF->Cell($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , '' , 0 , 0 , 'C' , FALSE , '');
  $releve_PDF->choisir_couleur_fond('gris_clair');
  $th = ($tableau_synthese_format=='eleve') ? 'Elève' : 'Item' ;
  $releve_HTML_table_head = '<thead><tr><th>'.$th.'</th>';
  if($tableau_synthese_format=='eleve')
  {
    foreach($tab_matiere_item as $matiere_id=>$tab_item)  // Pour chaque item...
    {
      foreach($tab_item as $item_id=>$item_nom)
      {
        $releve_PDF->VertCellFit($releve_PDF->cases_largeur, $releve_PDF->etiquette_hauteur, To::pdf($tab_item_infos[$item_id][0]['item_ref']), 1 /*border*/, 0 /*br*/, TRUE /*fill*/);
        $releve_HTML_table_head .= '<th title="'.html(html($tab_item_infos[$item_id][0]['item_nom'])).'"><img alt="'.html($tab_item_infos[$item_id][0]['item_ref']).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($tab_item_infos[$item_id][0]['item_ref']).'&amp;size=8" /></th>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
      }
    }
  }
  else
  {
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom $date_naissance $eleve_id_gepi
      $releve_PDF->VertCellFit($releve_PDF->cases_largeur, $releve_PDF->etiquette_hauteur, To::pdf($eleve_nom.' '.$eleve_prenom), 1 /*border*/, 0 /*br*/, TRUE /*fill*/);
      $releve_HTML_table_head .= '<th><img alt="'.html($eleve_nom.' '.$eleve_prenom).'" src="./_img/php/etiquette.php?dossier='.$_SESSION['BASE'].'&amp;nom='.urlencode($eleve_nom).'&amp;prenom='.urlencode($eleve_prenom).'&amp;size=8" /></th>';
    }
  }
  $releve_PDF->SetX( $releve_PDF->GetX()+2 );
  $releve_PDF->choisir_couleur_fond('gris_moyen');
  $releve_PDF->Cell($releve_PDF->cases_largeur , $releve_PDF->etiquette_hauteur , '[ * ]'  , 1 , 0 , 'C' , TRUE , '');
  $releve_PDF->Cell($releve_PDF->cases_largeur , $releve_PDF->etiquette_hauteur , '[ ** ]' , 1 , 1 , 'C' , TRUE , '');
  $checkbox_vide = ($affichage_checkbox) ? '<th class="nu">&nbsp;</th>' : '' ;
  $releve_HTML_table_head .= '<th class="nu">&nbsp;</th><th>[ * ]</th><th>[ ** ]</th>'.$checkbox_vide.'</tr></thead>'.NL;
  // lignes suivantes
  $releve_HTML_table_body = '';
  if($tableau_synthese_format=='eleve')
  {
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom $eleve_id_gepi
      $releve_PDF->choisir_couleur_fond('gris_clair');
      $releve_PDF->CellFit($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , To::pdf($eleve_nom.' '.$eleve_prenom) , 1 , 0 , 'L' , TRUE , '');
      $releve_HTML_table_body .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td>';
      foreach($tab_matiere_item as $matiere_id=>$tab_item)  // Pour chaque item...
      {
        foreach($tab_item as $item_id=>$item_nom)
        {
          $score = (isset($tab_score_eleve_item[$eleve_id][$matiere_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] : FALSE ;
          $releve_PDF->afficher_score_bilan( $score , 0 /*br*/ );
          $checkbox_val = ($affichage_checkbox) ? $eleve_id.'x'.$item_id : '' ;
          $releve_HTML_table_body .= Html::td_score($score,$tableau_tri_mode,'',$checkbox_val);
        }
      }
      if($matiere_nb>1)
      {
        $matiere_id = 0; // C'est l'indice choisi pour stocker les infos dans le cas d'une synthèse d'items issus de plusieurs matières
      }
      $valeur1 = (isset($tab_moyenne_scores_eleve[$matiere_id][$eleve_id])) ? $tab_moyenne_scores_eleve[$matiere_id][$eleve_id] : FALSE ;
      $valeur2 = (isset($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id])) ? $tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] : FALSE ;
      $releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,FALSE,TRUE);
      $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></td>' : '' ;
      $releve_HTML_table_body .= '<td class="nu">&nbsp;</td>'.Html::td_score($valeur1,$tableau_tri_mode,'%').Html::td_score($valeur2,$tableau_tri_mode,'%').$checkbox.'</tr>'.NL;
    }
  }
  else
  {
    foreach($tab_matiere_item as $matiere_id=>$tab_item)  // Pour chaque item...
    {
      foreach($tab_item as $item_id=>$item_nom)
      {
        $releve_PDF->choisir_couleur_fond('gris_clair');
        $releve_PDF->CellFit($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , To::pdf($tab_item_infos[$item_id][0]['item_ref']) , 1 , 0 , 'L' , TRUE , '');
        $releve_HTML_table_body .= '<tr><td title="'.html(html($tab_item_infos[$item_id][0]['item_nom'])).'">'.html($tab_item_infos[$item_id][0]['item_ref']).'</td>'; // Volontairement 2 html() pour le title sinon &lt;* est pris comme une balise html par l'infobulle.
        foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
        {
          $score = (isset($tab_score_eleve_item[$eleve_id][$matiere_id][$item_id])) ? $tab_score_eleve_item[$eleve_id][$matiere_id][$item_id] : FALSE ;
          $releve_PDF->afficher_score_bilan( $score , 0 /*br*/ );
          $checkbox_val = ($affichage_checkbox) ? $eleve_id.'x'.$item_id : '' ;
          $releve_HTML_table_body .= Html::td_score($score,$tableau_tri_mode,'',$checkbox_val);
        }
        $valeur1 = $tab_moyenne_scores_item[$item_id];
        $valeur2 = $tab_pourcentage_acquis_item[$item_id];
        $releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,FALSE,TRUE);
        $checkbox = ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_item[]" value="'.$item_id.'" /></td>' : '' ;
        $releve_HTML_table_body .= '<td class="nu">&nbsp;</td>'.Html::td_score($valeur1,$tableau_tri_mode,'%').Html::td_score($valeur2,$tableau_tri_mode,'%').$checkbox.'</tr>'.NL;
      }
    }
  }
  $releve_HTML_table_body = '<tbody>'.NL.$releve_HTML_table_body.'</tbody>'.NL;
  // dernière ligne (doublée)
  $memo_y = $releve_PDF->GetY()+2;
  $releve_PDF->SetY( $memo_y );
  $releve_PDF->choisir_couleur_fond('gris_moyen');
  $releve_PDF->Cell($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , To::pdf('moy. scores '.$info_ponderation_courte.' [*]') , 1 , 2 , 'C' , TRUE , '');
  $releve_PDF->Cell($releve_PDF->intitule_largeur , $releve_PDF->cases_hauteur , To::pdf('% items acquis [**]') , 1 , 0 , 'C' , TRUE , '');
  $releve_HTML_table_foot1 = '<tr><th>moy. scores '.$info_ponderation_courte.' [*]</th>';
  $releve_HTML_table_foot2 = '<tr><th>% items acquis [**]</th>';
  $checkbox = ($affichage_checkbox) ? '<tr><th class="nu">&nbsp;</th>' : '' ;
  $memo_x = $releve_PDF->GetX();
  $releve_PDF->SetXY($memo_x,$memo_y);
  if($tableau_synthese_format=='eleve')
  {
    foreach($tab_matiere_item as $matiere_id=>$tab_item)  // Pour chaque item...
    {
      foreach($tab_item as $item_id=>$item_nom)
      {
        $valeur1 = $tab_moyenne_scores_item[$item_id];
        $valeur2 = $tab_pourcentage_acquis_item[$item_id];
        $releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,TRUE,FALSE);
        $releve_HTML_table_foot1 .= Html::td_score($valeur1,'score','%');
        $releve_HTML_table_foot2 .= Html::td_score($valeur2,'score','%');
        $checkbox .= ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_item[]" value="'.$item_id.'" /></td>' : '' ;
      }
    }
  }
  else
  {
    foreach($tab_eleve_infos as $eleve_id => $tab_eleve)  // Pour chaque élève...
    {
      $valeur1 = (isset($tab_moyenne_scores_eleve[$matiere_id][$eleve_id])) ? $tab_moyenne_scores_eleve[$matiere_id][$eleve_id] : FALSE ;
      $valeur2 = (isset($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id])) ? $tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] : FALSE ;
      $releve_PDF->bilan_periode_synthese_pourcentages($valeur1,$valeur2,TRUE,FALSE);
      $releve_HTML_table_foot1 .= Html::td_score($valeur1,'score','%');
      $releve_HTML_table_foot2 .= Html::td_score($valeur2,'score','%');
      $checkbox .= ($affichage_checkbox) ? '<td class="nu"><input type="checkbox" name="id_user[]" value="'.$eleve_id.'" /></td>' : '' ;
    }
  }
  // les deux dernières cases (moyenne des moyennes)
  $colspan  = ($tableau_synthese_format=='eleve') ? $item_nb : $eleve_nb ;
  $last_col = ($affichage_checkbox) ? '<td class="nu"></td>' : '' ;
  $releve_PDF->bilan_periode_synthese_pourcentages($moyenne_moyenne_scores,$moyenne_pourcentage_acquis,TRUE,TRUE);
  $releve_HTML_table_foot1 .= '<th class="nu">&nbsp;</th>'.Html::td_score($moyenne_moyenne_scores,'score','%').'<th class="nu">&nbsp;</th>'.$checkbox_vide.'</tr>'.NL;
  $releve_HTML_table_foot2 .= '<th class="nu">&nbsp;</th><th class="nu">&nbsp;</th>'.Html::td_score($moyenne_pourcentage_acquis,'score','%').$checkbox_vide.'</tr>'.NL;
  $checkbox .= ($affichage_checkbox) ? '<th class="nu">&nbsp;</th><th class="nu">&nbsp;</th><th class="nu">&nbsp;</th>'.$checkbox_vide.'</tr>'.NL : '' ;
  $releve_HTML_table_foot = '<tfoot>'.NL.'<tr class="vide"><td class="nu" colspan="'.$colspan.'">&nbsp;</td><td class="nu"></td><td class="nu" colspan="2"></td>'.$last_col.'</tr>'.NL.$releve_HTML_table_foot1.$releve_HTML_table_foot2.$checkbox.'</tfoot>'.NL;
  // pour la sortie HTML, on peut placer les tableaux de synthèse au début
  $num_hide = ($tableau_synthese_format=='eleve') ? $item_nb+1 : $eleve_nb+1 ;
  $num_hide_add = ($affichage_checkbox) ? ','.($num_hide+3).':{sorter:false}' : '' ;
  $releve_HTML_synthese .= '<hr />'.NL.'<h2>SYNTHESE (selon l\'objet et le mode de tri choisis)</h2>'.NL;
  $releve_HTML_synthese .= ($affichage_checkbox) ? '<form id="form_synthese" action="#" method="post">'.NL : '' ;
  $releve_HTML_synthese .= '<table id="table_s" class="bilan_synthese vsort">'.NL.$releve_HTML_table_head.$releve_HTML_table_foot.$releve_HTML_table_body.'</table>'.NL;
  $releve_HTML_synthese .= ($affichage_checkbox) ? Html::afficher_formulaire_synthese_exploitation('complet').'</form>'.NL : '';
  $releve_HTML_synthese .= '<script type="text/javascript">$("#table_s").tablesorter({ headers:{'.$num_hide.':{sorter:false}'.$num_hide_add.'} });</script>'.NL; // Non placé dans le fichier js car mettre une variable à la place d'une valeur pour $num_hide ne fonctionne pas
  // On enregistre les sorties HTML et PDF
  FileSystem::ecrire_fichier(    CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.html' , $releve_HTML_synthese );
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','synthese',$fichier_nom).'.pdf'  , $releve_PDF );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Elaboration du bulletin (moyenne et/ou appréciation) en HTML + CSV pour GEPI + Formulaire pour report prof
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( $type_bulletin && $make_html )
{
  $tab_bulletin_input = array();
  $bulletin_form = $bulletin_periode = $bulletin_alerte = '' ;
  if($_SESSION['USER_PROFIL_TYPE']=='professeur')
  {
    if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
    {
      // Attention : $groupe_id peut être un identifiant de groupe et non de classe, auquel cas les élèves peuvent être issus de différentes classes dont les états des bulletins sont différents...
      $DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_periodes_bulletins_saisies_ouvertes($liste_eleve);
      $nb_periodes_ouvertes = !empty($DB_TAB) ? count($DB_TAB) : 0 ;
      if($nb_periodes_ouvertes==1)
      {
        $bulletin_periode = '['.html($DB_TAB[0]['periode_nom']).']<input type="hidden" id="f_periode_eleves" name="f_periode_eleves" value="'.$DB_TAB[0]['periode_id'].'_'.$DB_TAB[0]['eleves_listing'].'" />' ;
      }
      elseif($nb_periodes_ouvertes>1)
      {
        
        foreach($DB_TAB as $DB_ROW)
        {
          $selected = ($DB_ROW['periode_id']==$periode_id) ? ' selected' : '' ;
          $bulletin_periode .= '<option value="'.$DB_ROW['periode_id'].'_'.$DB_ROW['eleves_listing'].'"'.$selected.'>'.html($DB_ROW['periode_nom']).'</option>';
        }
        $bulletin_periode = '<select id="f_periode_eleves" name="f_periode_eleves">'.$bulletin_periode.'</select>';
      }
      else
      {
        $bulletin_form = '<li>Report forcé vers un bulletin sans objet : pas de bulletin scolaire ouvert pour ce regroupement.</li>';
      }
    }
    else
    {
      $bulletin_form = '<li>Report forcé vers un bulletin sans objet : les bulletins scolaires sont configurés sans moyenne.</li>';
    }
  }
  $bulletin_body = '';
  $bulletin_csv_entete = 'GEPI_IDENTIFIANT;NOTE;APPRECIATION'."\r\n";  // Ajout du préfixe 'GEPI_' pour éviter un bug avec M$ Excel « SYLK : Format de fichier non valide » (http://support.microsoft.com/kb/323626/fr)
  $tab_bulletin_csv_gepi = array_fill_keys( array('note_appreciation','note','appreciation_PA','appreciation_MS') , $bulletin_csv_entete );
  // Pour chaque élève...
  foreach($tab_eleve_infos as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom $eleve_id_gepi
    // Si cet élève a été évalué...
    if(isset($tab_eval[$eleve_id]))
    {
      $note            = ($tab_moyenne_scores_eleve[$matiere_id][$eleve_id]     !== FALSE) ? sprintf("%04.1f",$tab_moyenne_scores_eleve[$matiere_id][$eleve_id]/5)                                                           : '-' ;
      $appreciation_PA = ($tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id] !== FALSE) ? $tab_pourcentage_acquis_eleve[$matiere_id][$eleve_id].'% d\'items acquis ('.$tab_infos_acquis_eleve[$matiere_id][$eleve_id].')' : '-' ;
      $appreciation_MS = ($tab_moyenne_scores_eleve[$matiere_id][$eleve_id]     !== FALSE) ? ( ($conversion_sur_20) ? 'Moyenne des scores : '.$tab_moyenne_scores_eleve[$matiere_id][$eleve_id].'%'.' soit '.str_replace('.',',',sprintf("%04.1f",$tab_moyenne_scores_eleve[$matiere_id][$eleve_id]/5)).'/20.' : 'Moyenne des scores de '.$tab_moyenne_scores_eleve[$matiere_id][$eleve_id].'%.' ) : '-' ;
      $bulletin_body  .= '<tr><th>'.html($eleve_nom.' '.$eleve_prenom).'</th><td>'.$note.'</td><td>'.$appreciation_PA.'</td></tr>'.NL;
      $note            = str_replace('.',',',$note); // Pour GEPI je remplace le point décimal par une virgule sinon le tableur convertit en date...
      $tab_bulletin_csv_gepi['note_appreciation'] .= $eleve_id_gepi.';'.$note.';'.$appreciation_PA."\r\n";
      $tab_bulletin_csv_gepi['note']              .= $eleve_id_gepi.';'.$note."\r\n";
      $tab_bulletin_csv_gepi['appreciation_PA']   .= $eleve_id_gepi.';'.''   .';'.$appreciation_PA."\r\n";
      $tab_bulletin_csv_gepi['appreciation_MS']   .= $eleve_id_gepi.';'.''   .';'.$appreciation_MS."\r\n";
      if( ($bulletin_periode) && ($tab_moyenne_scores_eleve[$matiere_id][$eleve_id] !== FALSE) )
      {
        $tab_bulletin_input[] = $eleve_id.'_'.($tab_moyenne_scores_eleve[$matiere_id][$eleve_id]/5);
      }
    }
  }
  if($bulletin_periode)
  {
    if(count($tab_bulletin_input))
    {
      if($releve_modele=='matiere')
      {
        $bulletin_matiere = '['.html($matiere_nom).']<input type="hidden" id="f_rubrique" name="f_rubrique" value="'.$matiere_id.'" />';
      }
      else
      {
        $bulletin_matiere = Form::afficher_select(DB_STRUCTURE_COMMUN::DB_OPT_matieres_professeur($_SESSION['USER_ID']) , 'f_rubrique' /*select_nom*/ , FALSE /*option_first*/ , FALSE /*selection*/ , '' /*optgroup*/);
      }
      $bulletin_form = '<li><form id="form_report_bulletin"><fieldset><button id="bouton_report" type="button" class="eclair">Report forcé</button> vers le bulletin <em>SACoche</em> '.$bulletin_periode.'<input type="hidden" id="f_eleves_moyennes" name="f_eleves_moyennes" value="'.implode('x',$tab_bulletin_input).'" /> '.$bulletin_matiere.'</fieldset></form><label id="ajax_msg_report"></label></li>';
      $bulletin_alerte = '<div class="danger">Un report forcé interrompt le report automatique des moyennes pour le bulletin et la matière concernée.</div>' ;
    }
    else
    {
      $bulletin_form = '<li>Report forcé vers un bulletin sans objet : aucune moyenne chiffrée n\'a pu être produite.</li>';
    }
  }
  $bulletin_head  = '<thead><tr><th>Elève</th><th>Moyenne '.$info_ponderation_complete.' sur 20<br />(des scores d\'acquisitions)</th><th>Élément d\'appréciation<br />(pourcentage d\'items acquis)</th></tr></thead>'.NL;
  $bulletin_body  = '<tbody>'.NL.$bulletin_body.'</tbody>'.NL;
  $bulletin_foot  = '<tfoot><tr><th>Moyenne '.$info_ponderation_complete.' sur 20</th><th>'.sprintf("%04.1f",$moyenne_moyenne_scores/5).'</th><th>'.$moyenne_pourcentage_acquis.'% d\'items acquis</th></tr></tfoot>'.NL;
  $bulletin_html  = '<h1>Bilan disciplinaire</h1>';
  $bulletin_html .= '<h2>'.html($matiere_nom.' - '.$groupe_nom).'</h2>';
  $bulletin_html .= '<h2>'.$texte_periode.'</h2>';
  $bulletin_html .= '<h2>Tableau de notes sur 20</h2>';
  $bulletin_html .= '<table id="export20" class="hsort">'.NL.$bulletin_head.$bulletin_foot.$bulletin_body.'</table>'.NL;
  $bulletin_html .= '<script type="text/javascript">$("#export20").tablesorter({ headers:{2:{sorter:false}} });</script>'.NL;
  // On enregistre la sortie HTML et CSV
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','bulletin',$fichier_nom).'.html',$bulletin_html);
  foreach($tab_bulletin_csv_gepi as $type_donnees => $bulletin_csv_gepi_contenu)
  {
    FileSystem::ecrire_fichier(CHEMIN_DOSSIER_EXPORT.str_replace('<REPLACE>','bulletin_'.$type_donnees,$fichier_nom).'.csv',utf8_decode($bulletin_csv_gepi_contenu));
  }
}

?>