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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$action  = (isset($_POST['f_action']))  ? Clean::texte($_POST['f_action'])  : '' ;
$section = (isset($_POST['f_section'])) ? Clean::texte($_POST['f_section']) : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Saisir    : affichage des données d'un élève | enregistrement/suppression d'une appréciation ou d'une note | recalculer une note
// Examiner  : recherche des saisies manquantes (notes et appréciations)
// Consulter : affichage des données d'un élève (HTML)
// Imprimer  : affichage de la liste des élèves | étape d'impression PDF
// ////////////////////////////////////////////////////////////////////////////////////////////////////

  $tab_types = array
  (
    'releve'   => array( 'droit'=>'RELEVE'   , 'titre'=>'Relevé d\'évaluations' ) ,
    'bulletin' => array( 'droit'=>'BULLETIN' , 'titre'=>'Bulletin scolaire'     ) ,
    'palier1'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 1'  ) ,
    'palier2'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 2'  ) ,
    'palier3'  => array( 'droit'=>'SOCLE'    , 'titre'=>'Maîtrise du palier 3'  )
  );

if( in_array( $section , array('officiel_saisir','officiel_examiner','officiel_consulter','officiel_imprimer') ) )
{
  if( ($section=='officiel_consulter') && ($action=='imprimer') )
  {
    // Il s'agit d'un test d'impression d'un bilan non encore clos (on vérifiera quand même par la suite que les conditions sont respectées (état du bilan, droit de l'utilisateur)
    $section = 'officiel_imprimer';
    $_POST['f_objet'] = 'imprimer';
    $is_test_impression = TRUE;
  }
  require(CHEMIN_DOSSIER_INCLUDE.'fonction_bulletin.php');
  require(CHEMIN_DOSSIER_INCLUDE.'code_'.$section.'.php');
  exit(); // Normalement, on n'arrive pas jusque là.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Signaler une faute
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='signaler_faute') || ($action=='corriger_faute') )
{
  $_POST['f_action']='ajouter';
  require(CHEMIN_DOSSIER_PAGES.'compte_message.ajax.php');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer un archivage des saisies
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$tab_actions = array
(
  'imprimer_donnees_eleves_prof'      => 'Mes appréciations pour chaque élève et le groupe classe',
  'imprimer_donnees_eleves_collegues' => 'Appréciations des collègues pour chaque élève',
  'imprimer_donnees_classe_collegues' => 'Appréciations des collègues sur le groupe classe',
  'imprimer_donnees_eleves_syntheses' => 'Appréciations de synthèse générale pour chaque élève',
  'imprimer_donnees_eleves_moyennes'  => 'Tableau des moyennes pour chaque élève'
);

if( isset($tab_actions[$action]) )
{
  $BILAN_TYPE   = (isset($_POST['f_bilan_type']))   ? Clean::texte($_POST['f_bilan_type'])   : '';
  $periode_id   = (isset($_POST['f_periode']))      ? Clean::entier($_POST['f_periode'])     : 0;
  $classe_id    = (isset($_POST['f_classe']))       ? Clean::entier($_POST['f_classe'])      : 0;
  $groupe_id    = (isset($_POST['f_groupe']))       ? Clean::entier($_POST['f_groupe'])      : 0;

  // On vérifie les paramètres principaux

  if( (!isset($tab_types[$BILAN_TYPE])) || (!$periode_id) || (!$classe_id) )
  {
    exit('Erreur avec les données transmises !');
  }

  // On vérifie que le bilan est bien accessible et on récupère les infos associées

  $DB_ROW = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_infos($classe_id,$periode_id,$BILAN_TYPE);
  if(empty($DB_ROW))
  {
    exit('Association classe / période introuvable !');
  }
  $date_debut  = $DB_ROW['jointure_date_debut'];
  $date_fin    = $DB_ROW['jointure_date_fin'];
  $BILAN_ETAT  = $DB_ROW['officiel_'.$BILAN_TYPE];
  $periode_nom = $DB_ROW['periode_nom'];
  $classe_nom  = $DB_ROW['groupe_nom'];
  if(!$BILAN_ETAT)
  {
    exit('Bilan introuvable !');
  }

  // Récupérer la liste des élèves (on pourrait se faire transmettre les ids par l'envoi ajax, mais on a aussi besoin des noms-prénoms).

  $is_sous_groupe = ($groupe_id) ? TRUE : FALSE ;
  $DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
  if(empty($DB_TAB))
  {
    exit('Aucun élève trouvé dans ce regroupement !');
  }
  $tab_eleve_id = array( 0 => array( 'eleve_nom' => $classe_nom ,  'eleve_prenom' => '' ) );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleve_id[$DB_ROW['user_id']] = array( 'eleve_nom' => $DB_ROW['user_nom'] ,  'eleve_prenom' => $DB_ROW['user_prenom'] );
  }
  $liste_eleve_id = implode(',',array_keys($tab_eleve_id));

  // Fonctions utilisées.

  function suppression_sauts_de_ligne($texte)
  {
    $tab_bad = array( "\r\n" , "\r" , "\n" );
    $tab_bon = ' ';
    return str_replace( $tab_bad , $tab_bon , $texte );
  }

  function nombre_de_ligne_supplémentaires($texte)
  {
    return max( 2 , ceil(mb_strlen($texte)/125) ) - 2;
  }

  // Quelques autres variables utiles communes.

  $nb_eleves = count($tab_eleve_id);
  $with_moyenne = ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] ;
  $prof_nom = ($action=='imprimer_donnees_eleves_prof') ? $_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM'] : 'Équipe enseignante' ;

  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // Cas 1/5 imprimer_donnees_eleves_prof : Mes appréciations pour chaque élève et le groupe classe
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /

  if($action=='imprimer_donnees_eleves_prof')
  {
    // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour le prof concerné
    if($BILAN_TYPE=='bulletin')
    {
      $DB_TAB = array_merge
      (
        DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( $periode_id , $classe_id , $_SESSION['USER_ID'] , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ ),
        DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , $_SESSION['USER_ID'] , TRUE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ )
      );
    }
    else
    {
      $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , $_SESSION['USER_ID'] , TRUE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
    }
    // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par rubrique car on imprimera une page par rubrique avec tous les élèves de la classe
    $tab_saisie = array();  // [rubrique_id][eleve_id] => array(note,appreciation);
    $nb_lignes_supplémentaires = array(); // On compte 2 lignes par rubrique par élève, il peut falloir plus si l'appréciation est longue
    // La requête renvoie les appréciations du prof et les notes de toutes les rubriques.
    // Il ne faut prendre que les notes qui vont avec les appréciations, i.e. des rubriques du prof.
    // Ainsi, on commence dans une première boucle par lister les appréciations et les rubriques...
    $tab_rubriques = array();
    foreach($DB_TAB as $key => $DB_ROW)
    {
      if($DB_ROW['prof_id'])
      {
        if(!isset($tab_rubriques[$DB_ROW['rubrique_id']]))
        {
          $tab_rubriques[$DB_ROW['rubrique_id']] = ($DB_ROW['rubrique_id']) ? $DB_ROW['rubrique_nom'] : 'Synthèse générale' ;
          $nb_lignes_supplémentaires[$DB_ROW['rubrique_id']] = 0;
        }
        $tab_saisie[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']] = array( 'note'=>NULL , 'appreciation'=>suppression_sauts_de_ligne($DB_ROW['saisie_appreciation']) );
        $nb_lignes_supplémentaires[$DB_ROW['rubrique_id']] += nombre_de_ligne_supplémentaires($DB_ROW['saisie_appreciation']);
        unset($DB_TAB[$key]);
      }
    }
    // ( prévoir l'appréciation sur la classe même si elle n'est pas saisie )
    foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
    {
      if(!isset($tab_saisie[$rubrique_id][0]))
      {
        $tab_saisie[$rubrique_id][0] = array( 'note'=>NULL , 'appreciation'=>'' );
      }
    }
    // ... puis dans une seconde on ajoute les seules notes à garder.
    if( ($tab_types[$BILAN_TYPE]['droit']=='BULLETIN') && ($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) )
    {
      foreach($DB_TAB as $DB_ROW)
      {
        if(isset($tab_rubriques[$DB_ROW['rubrique_id']]))
        {
          $note = ( ( !$DB_ROW['rubrique_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] ) || ( !$DB_ROW['eleve_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'] ) ) ? NULL : $DB_ROW['saisie_note'] ;
          if(isset($tab_saisie[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]))
          {
            $tab_saisie[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']]['note'] = $note;
          }
          else
          {
            $tab_saisie[$DB_ROW['rubrique_id']][$DB_ROW['eleve_id']] = array( 'note'=>$note , 'appreciation'=>'' );
          }
        }
      }
    }
    // Fabrication du PDF
    $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
    foreach($tab_saisie as $rubrique_id => $tab)
    {
      $releve_PDF->tableau_appreciation_initialiser_eleves_prof( $nb_eleves , $nb_lignes_supplémentaires[$rubrique_id] , $with_moyenne );
      $releve_PDF->tableau_appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - Appréciations de '.$prof_nom.' - '.$tab_rubriques[$rubrique_id] );
      // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
      foreach($tab_eleve_id as $eleve_id => $tab_eleve)
      {
        extract($tab_eleve);  // $eleve_nom $eleve_prenom
        if(isset($tab[$eleve_id]))
        {
          extract($tab[$eleve_id]);  // $note $appreciation
          $releve_PDF->tableau_appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , $note , $appreciation , $with_moyenne );
        }
      }
    }
  }

  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // Cas 2/5 imprimer_donnees_eleves_collegues : Appréciations des collègues pour chaque élève
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /

  if($action=='imprimer_donnees_eleves_collegues')
  {
    // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour tous les collègues
    $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , TRUE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
    // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par élève
    $tab_saisie = array();  // [eleve_id][rubrique_id] => array(rubrique_nom,note,tab_appreciation);
    $nb_lignes_rubriques = 0; // On compte 2 lignes par élève par rubrique, il peut falloir plus si l'appréciation est longue
    foreach($DB_TAB as $DB_ROW)
    {
      if(!isset($tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]))
      {
        // Initialisation, dont la note pour le bulletin
        $rubrique_nom = ($DB_ROW['rubrique_nom']!==NULL) ? $DB_ROW['rubrique_nom'] : 'Synthèse générale' ;
        $note = ( ($tab_types[$BILAN_TYPE]['droit']!='BULLETIN') || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) || ( !$DB_ROW['rubrique_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] ) ) ? NULL : $DB_ROW['saisie_note'] ;
        $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']] = array( 'rubrique_nom'=>$rubrique_nom , 'note'=>$note , 'tab_appreciation'=>array() );
        $nb_lignes_rubriques += 2;
      }
      if($DB_ROW['prof_id'])
      {
        // Les appréciations
        $texte = $DB_ROW['prof_info'].' - '.$DB_ROW['saisie_appreciation'];
        $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['tab_appreciation'][] = suppression_sauts_de_ligne($texte);
        $nb_lignes_rubriques += nombre_de_ligne_supplémentaires($texte);
      }
    }
    // ( mettre les appréciations générales en dernier )
    foreach($tab_saisie as $eleve_id => $tab)
    {
      if(isset($tab[0]))
      {
        $tab_saisie[$eleve_id][] = array_shift($tab_saisie[$eleve_id]);
      }
    }
    // Fabrication du PDF
    $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
    $releve_PDF->tableau_appreciation_initialiser_eleves_collegues( $nb_eleves , $nb_lignes_rubriques );
    $releve_PDF->tableau_appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - '.'Appréciations par élève' );
    // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
    foreach($tab_eleve_id as $eleve_id => $tab_eleve)
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom
      if(isset($tab_saisie[$eleve_id]))
      {
        foreach($tab_saisie[$eleve_id] as $rubrique_id => $tab)
        {
          extract($tab);  // $rubrique_nom $note $appreciation
          $releve_PDF->tableau_appreciation_rubrique_eleves_collegues( $eleve_id , $eleve_nom , $eleve_prenom , $rubrique_nom , $note , implode("\r\n",$tab_appreciation) , $with_moyenne );
          $eleve_nom = $eleve_prenom = '' ;
        }
      }
    }
  }

  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // Cas 3/5 imprimer_donnees_classe_collegues : Appréciations des collègues sur le groupe classe
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /

  if($action=='imprimer_donnees_classe_collegues')
  {
    // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour tous les collègues
    $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( $periode_id , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
    // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par rubrique
    $tab_saisie = array();  // [rubrique_id] => array(rubrique_nom,note,tab_appreciation);
    $nb_lignes_supplémentaires = 0; // On compte 2 lignes par élève par rubrique, il peut falloir plus si l'appréciation est longue
    foreach($DB_TAB as $DB_ROW)
    {
      if(!isset($tab_saisie[$DB_ROW['rubrique_id']]))
      {
        // Initialisation, dont la note pour le bulletin
        $rubrique_nom = ($DB_ROW['rubrique_nom']!==NULL) ? $DB_ROW['rubrique_nom'] : 'Synthèse générale' ;
        $note = ( ($tab_types[$BILAN_TYPE]['droit']!='BULLETIN') || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) || ( !$DB_ROW['rubrique_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] ) || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ) ? NULL : $DB_ROW['saisie_note'] ;
        $tab_saisie[$DB_ROW['rubrique_id']] = array( 'rubrique_nom'=>$rubrique_nom , 'note'=>$note , 'tab_appreciation'=>array() );
      }
      if($DB_ROW['prof_id'])
      {
        // Les appréciations
        $texte = $DB_ROW['prof_info'].' - '.$DB_ROW['saisie_appreciation'];
        $tab_saisie[$DB_ROW['rubrique_id']]['tab_appreciation'][] = suppression_sauts_de_ligne($texte);
        $nb_lignes_supplémentaires += nombre_de_ligne_supplémentaires($texte);
      }
    }
    // ( mettre l'appréciation générale en dernier )
    if(isset($tab_saisie[0]))
    {
      $tab_saisie[] = array_shift($tab_saisie);
    }
    // Fabrication du PDF
    $nb_rubriques = count($tab_saisie);
    $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
    $releve_PDF->tableau_appreciation_initialiser_classe_collegues( $nb_eleves , $nb_rubriques , $nb_lignes_supplémentaires );
    $releve_PDF->tableau_appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - '.'Appréciations du groupe classe' );
    foreach($tab_saisie as $rubrique_id => $tab)
    {
      extract($tab);  // $rubrique_nom $note $appreciation
      $releve_PDF->tableau_appreciation_rubrique_classe_collegues( $rubrique_nom , $note , implode("\r\n",$tab_appreciation) , $with_moyenne );
    }
  }

  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // Cas 4/5 imprimer_donnees_eleves_syntheses : Appréciations de synthèse générale pour chaque élève
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /

  if($action=='imprimer_donnees_eleves_syntheses')
  {
    // Récupérer les saisies enregistrées pour le bilan officiel concerné, pour tous les collègues
    if($BILAN_TYPE=='bulletin')
    {
      $DB_TAB = array_merge
      (
        DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_classe( $periode_id , $classe_id , 0 /*prof_id*/ , FALSE /*with_periodes_avant*/ , TRUE /*only_synthese_generale*/ ),
        DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , TRUE /*only_synthese_generale*/ )
      );
    }
    else
    {
      $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , $periode_id , $liste_eleve_id , 0 /*prof_id*/ , FALSE /*with_rubrique_nom*/ , FALSE /*with_periodes_avant*/ , TRUE /*only_synthese_generale*/ );
    }
    // Répertorier les saisies dans le tableau $tab_saisie : c'est groupé par élève
    $tab_saisie = array();  // [eleve_id] => array(note,appreciation);
    $nb_lignes_supplémentaires = 0; // On compte 2 lignes par élève par rubrique, il peut falloir plus si l'appréciation est longue
    foreach($DB_TAB as $DB_ROW)
    {
      if(!isset($tab_saisie[$DB_ROW['eleve_id']]))
      {
        // Initialisation, dont la note pour le bulletin
        $note = ( ($tab_types[$BILAN_TYPE]['droit']!='BULLETIN') || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE']) || ( !$DB_ROW['eleve_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'] ) ) ? NULL : $DB_ROW['saisie_note'] ;
        $tab_saisie[$DB_ROW['eleve_id']] = array( 'note'=>$note , 'appreciation'=>'' );
      }
      if($DB_ROW['prof_id'])
      {
        // L'appréciation
        $texte = $DB_ROW['prof_info'].' - '.$DB_ROW['saisie_appreciation'];
        $tab_saisie[$DB_ROW['eleve_id']]['appreciation'] = suppression_sauts_de_ligne($texte);
        $nb_lignes_supplémentaires += nombre_de_ligne_supplémentaires($texte);
      }
    }
    // Fabrication du PDF
    $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
    $releve_PDF->tableau_appreciation_initialiser_eleves_syntheses( $nb_eleves , $nb_lignes_supplémentaires , $with_moyenne );
    $releve_PDF->tableau_appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - '.'Synthèses générales' );
    // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
    foreach($tab_eleve_id as $eleve_id => $tab_eleve)
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom
      if(isset($tab_saisie[$eleve_id]))
      {
        extract($tab_saisie[$eleve_id]);  // $note $appreciation
      }
      else
      {
        $note = NULL;
        $appreciation = '';
      }
      $releve_PDF->tableau_appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , $note , $appreciation , $with_moyenne );
    }
  }

  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /
  // Cas 5/5 imprimer_donnees_eleves_moyennes : Tableau des moyennes pour chaque élève
  // / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / / /

  if($action=='imprimer_donnees_eleves_moyennes')
  {
    if(!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
    {
      exit('Les bulletins sont configurés sans notes !');
    }
    // Rechercher les notes enregistrées pour les élèves
    $tab_saisie = array();  // [eleve_id][rubrique_id] => array(note,appreciation);
    $tab_rubriques = array();
    $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_notes_eleves( $periode_id , $liste_eleve_id , TRUE /*tri_matiere*/ );
    foreach($DB_TAB as $DB_ROW)
    {
      if( $DB_ROW['rubrique_id'] || $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] )
      {
        $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']] = ($DB_ROW['saisie_note']!==NULL) ? (float)$DB_ROW['saisie_note'] : NULL ; // Remarque : un test isset() sur une valeur NULL renverra FALSE !!!
        $tab_rubriques[$DB_ROW['rubrique_id']] = ($DB_ROW['rubrique_id']) ? $DB_ROW['rubrique_nom'] : 'Synthèse générale' ;
      }
    }
    // Rechercher les notes enregistrées pour la classe
    $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_notes_classe( $periode_id , $classe_id );
    if($_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
    {
      foreach($DB_TAB as $DB_ROW)
      {
        if( $DB_ROW['rubrique_id'] || $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] )
        {
          $tab_saisie[0][$DB_ROW['rubrique_id']] = ($DB_ROW['saisie_note']!==NULL) ? (float)$DB_ROW['saisie_note'] : NULL ; // Remarque : un test isset() sur une valeur NULL renverra FALSE !!!
        }
      }
    }
    // ( mettre l'appréciation générale en dernier )
    if(isset($tab_rubriques[0]))
    {
      unset($tab_rubriques[0]); // Pas de array_shift() ici sinon il renumérote et on perd les indices des matières
      $tab_rubriques[0] = 'Synthèse générale';
    }
    // ( mettre le groupe classe en dernier )
    if(!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'])
    {
      unset($tab_eleve_id[0]);
      $nb_eleves--;
    }
    else
    {
      unset($tab_eleve_id[0]); // Pas de array_shift() ici sinon il renumérote et on perd les indices des élèves
      $tab_eleve_id[0] = array( 'eleve_nom' => $classe_nom ,  'eleve_prenom' => '' );
    }
    // Fabrication du PDF ; on a besoin de tourner du texte à 90°
    $nb_rubriques = count($tab_rubriques);
    $releve_PDF = new PDF( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
    $releve_PDF->tableau_moyennes_initialiser( $nb_eleves , $nb_rubriques );
    // 1ère ligne : intitulés, noms rubriques
    $releve_PDF->tableau_moyennes_intitule( $classe_nom , $periode_nom );
    foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
    {
      $releve_PDF->tableau_moyennes_reference_rubrique( $rubrique_id , $rubrique_nom );
    }
    // ligne suivantes : élèves, notes
    // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
    $releve_PDF->SetXY( $releve_PDF->marge_gauche , $releve_PDF->marge_haut+$releve_PDF->etiquette_hauteur );
    foreach($tab_eleve_id as $eleve_id => $tab_eleve)
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom
      $releve_PDF->tableau_moyennes_reference_eleve( $eleve_id , $eleve_nom.' '.$eleve_prenom );
      foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
      {
        $note = (isset($tab_saisie[$eleve_id][$rubrique_id])) ? $tab_saisie[$eleve_id][$rubrique_id] : NULL ;
        $releve_PDF->tableau_moyennes_note( $eleve_id , $rubrique_id , $note );
      }
      $releve_PDF->SetXY($releve_PDF->marge_gauche , $releve_PDF->GetY()+$releve_PDF->cases_hauteur);
    }
  }

  // Enregistrement et affichage du retour.

  $fichier_export = 'saisies_'.$BILAN_TYPE.'_'.Clean::fichier($periode_nom).'_'.Clean::fichier($classe_nom).'_'.$action.'_'.fabriquer_fin_nom_fichier__date_et_alea().'.pdf';
  $releve_PDF->Output(CHEMIN_DOSSIER_EXPORT.$fichier_export,'F');
  exit('<a class="lien_ext" href="'.URL_DIR_EXPORT.$fichier_export.'"><span class="file file_pdf">'.$tab_actions[$action].' (format <em>pdf</em>).</span></a>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
