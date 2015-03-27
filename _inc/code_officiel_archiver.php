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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupération des valeurs transmises
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
$DB_TAB = (!$is_sous_groupe) ? DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $classe_id , 'alpha' /*eleves_ordre*/ ) : DB_STRUCTURE_COMMUN::DB_lister_eleves_classe_et_groupe($classe_id,$groupe_id) ;
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

function nombre_de_lignes($texte)
{
  return ceil(mb_strlen($texte)/125);
}

// Quelques autres variables utiles communes.

$nb_eleves = count($tab_eleve_id);
$with_moyenne = ($BILAN_TYPE=='bulletin') && $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'] ;
$prof_nom = ($action=='imprimer_donnees_eleves_prof') ? $_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM'] : 'Équipe enseignante' ;
$tab_moyenne_exception_matieres = ( ($BILAN_TYPE!='bulletin') || !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES'] ) ? array() : explode(',',$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_EXCEPTION_MATIERES']) ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 1/6 imprimer_donnees_eleves_prof : Mes appréciations pour chaque élève et le groupe classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
        $note = ( ( !$DB_ROW['rubrique_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] ) || ( !$DB_ROW['eleve_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE'] ) || (in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres)) ) ? NULL : $DB_ROW['saisie_note'] ;
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
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  foreach($tab_saisie as $rubrique_id => $tab)
  {
    $archivage_tableau_PDF->appreciation_initialiser_eleves_prof( $nb_eleves , $nb_lignes_supplémentaires[$rubrique_id] , $with_moyenne );
    $archivage_tableau_PDF->appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - Appréciations de '.$prof_nom.' - '.$tab_rubriques[$rubrique_id] );
    // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
    foreach($tab_eleve_id as $eleve_id => $tab_eleve)
    {
      extract($tab_eleve);  // $eleve_nom $eleve_prenom
      if(isset($tab[$eleve_id]))
      {
        extract($tab[$eleve_id]);  // $note $appreciation
        $archivage_tableau_PDF->appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , $note , $appreciation , $with_moyenne , FALSE /*is_brevet*/ );
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 2/6 imprimer_donnees_eleves_collegues : Appréciations des collègues pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
      $note = ( ($tab_types[$BILAN_TYPE]['droit']!='BULLETIN') || (in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres)) || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) || ( !$DB_ROW['rubrique_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] ) ) ? NULL : $DB_ROW['saisie_note'] ;
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']] = array( 'rubrique_nom'=>$rubrique_nom , 'note'=>$note , 'tab_appreciation'=>array() );
      $nb_lignes_rubriques += 2;
    }
    if($DB_ROW['prof_id'])
    {
      // Les appréciations
      $texte = afficher_identite_initiale( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ).' - '.$DB_ROW['saisie_appreciation'];
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
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->appreciation_initialiser_eleves_collegues( $nb_eleves , $nb_lignes_rubriques );
  $archivage_tableau_PDF->appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - '.'Appréciations par élève' );
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    if(isset($tab_saisie[$eleve_id]))
    {
      foreach($tab_saisie[$eleve_id] as $rubrique_id => $tab)
      {
        extract($tab);  // $rubrique_nom $note $appreciation
        $archivage_tableau_PDF->appreciation_rubrique_eleves_collegues( $eleve_nom , $eleve_prenom , $rubrique_nom , $note , implode("\r\n",$tab_appreciation) , $with_moyenne );
        $eleve_nom = $eleve_prenom = '' ;
      }
    }
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 3/6 imprimer_donnees_classe_collegues : Appréciations des collègues sur le groupe classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
      $note = ( ($tab_types[$BILAN_TYPE]['droit']!='BULLETIN') || (in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres)) || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES']) || ( !$DB_ROW['rubrique_id'] && !$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] ) || (!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_CLASSE']) ) ? NULL : $DB_ROW['saisie_note'] ;
      $tab_saisie[$DB_ROW['rubrique_id']] = array( 'rubrique_nom'=>$rubrique_nom , 'note'=>$note , 'tab_appreciation'=>array() );
    }
    if($DB_ROW['prof_id'])
    {
      // Les appréciations
      $texte = afficher_identite_initiale( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ).' - '.$DB_ROW['saisie_appreciation'];
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
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->appreciation_initialiser_classe_collegues( $nb_eleves , $nb_rubriques , $nb_lignes_supplémentaires );
  $archivage_tableau_PDF->appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - '.'Appréciations du groupe classe' );
  foreach($tab_saisie as $rubrique_id => $tab)
  {
    extract($tab);  // $rubrique_nom $note $appreciation
    $archivage_tableau_PDF->appreciation_rubrique_classe_collegues( $rubrique_nom , $note , implode("\r\n",$tab_appreciation) , $with_moyenne );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 4/6 imprimer_donnees_eleves_syntheses : Appréciations de synthèse générale pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
      $texte = afficher_identite_initiale( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] ).' - '.$DB_ROW['saisie_appreciation'];
      $tab_saisie[$DB_ROW['eleve_id']]['appreciation'] = suppression_sauts_de_ligne($texte);
      $nb_lignes_supplémentaires += nombre_de_ligne_supplémentaires($texte);
    }
  }
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->appreciation_initialiser_eleves_syntheses( $nb_eleves , $nb_lignes_supplémentaires , $with_moyenne );
  $archivage_tableau_PDF->appreciation_intitule( $tab_types[$BILAN_TYPE]['titre'].' - '.$classe_nom.' - '.$periode_nom.' - '.'Synthèses générales' );
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
    $archivage_tableau_PDF->appreciation_rubrique_eleves_prof( $eleve_id , $eleve_nom , $eleve_prenom , $note , $appreciation , $with_moyenne , FALSE /*is_brevet*/ );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 5/6 imprimer_donnees_eleves_moyennes : Tableau des moyennes pour chaque élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_moyennes')
{
  if(!$_SESSION['OFFICIEL']['BULLETIN_MOYENNE_SCORES'])
  {
    exit('Les bulletins sont configurés sans notes !');
  }
  // Rechercher les notes enregistrées pour les élèves
  $tab_saisie = array();  // [eleve_id][rubrique_id] => array(note,appreciation);
  $tab_rubriques = array();
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_notes_eleves_periode( $periode_id , $liste_eleve_id , TRUE /*tri_matiere*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if( $DB_ROW['rubrique_id'] || $_SESSION['OFFICIEL']['BULLETIN_MOYENNE_GENERALE'] )
    {
      $tab_saisie[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']] = ( ($DB_ROW['saisie_note']!==NULL) && !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) ) ? (float)$DB_ROW['saisie_note'] : NULL ; // Remarque : un test isset() sur une valeur NULL renverra FALSE !!!
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
        $tab_saisie[0][$DB_ROW['rubrique_id']] = ( ($DB_ROW['saisie_note']!==NULL) && !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) ) ? (float)$DB_ROW['saisie_note'] : NULL ; // Remarque : un test isset() sur une valeur NULL renverra FALSE !!!
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
  // Fabrication d'un CSV en parallèle
  $nb_rubriques = count($tab_rubriques);
  $archivage_tableau_PDF = new PDF_archivage_tableau( FALSE /*officiel*/ , 'portrait' /*orientation*/ , 10 /*marge_gauche*/ , 10 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  $archivage_tableau_PDF->moyennes_initialiser( $nb_eleves , $nb_rubriques );
  $archivage_tableau_CSV = '';
  $separateur = ';';
  // 1ère ligne : intitulés, noms rubriques
  $archivage_tableau_PDF->moyennes_intitule( $classe_nom , $periode_nom , FALSE /*is_brevet*/ );
  $archivage_tableau_CSV .= '"'.$classe_nom.' | '.$periode_nom.'"';
  foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
  {
    $archivage_tableau_PDF->moyennes_reference_rubrique( $rubrique_id , $rubrique_nom );
    $archivage_tableau_CSV .= $separateur.'"'.$rubrique_nom.'"';
  }
  $archivage_tableau_CSV .= "\r\n";
  // ligne suivantes : élèves, notes
  // Pour avoir les élèves dans l'ordre alphabétique, il faut utiliser $tab_eleve_id.
  $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->marge_haut+$archivage_tableau_PDF->etiquette_hauteur );
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    extract($tab_eleve);  // $eleve_nom $eleve_prenom
    $archivage_tableau_PDF->moyennes_reference_eleve( $eleve_id , $eleve_nom.' '.$eleve_prenom );
    $archivage_tableau_CSV .= '"'.$eleve_nom.' '.$eleve_prenom.'"';
    foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
    {
      $note = (isset($tab_saisie[$eleve_id][$rubrique_id])) ? $tab_saisie[$eleve_id][$rubrique_id] : NULL ;
      $archivage_tableau_PDF->moyennes_note( $eleve_id , $rubrique_id , $note , FALSE /*is_brevet*/ );
      $archivage_tableau_CSV .= $separateur.'"'.str_replace('.',',',$note).'"'; // Remplacer le point décimal par une virgule pour le tableur.
    }
    $archivage_tableau_PDF->SetXY( $archivage_tableau_PDF->marge_gauche , $archivage_tableau_PDF->GetY()+$archivage_tableau_PDF->cases_hauteur );
    $archivage_tableau_CSV .= "\r\n";
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cas 6/6 imprimer_donnees_eleves_recapitulatif : Récapitulatif annuel des moyennes et appréciations par élève
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='imprimer_donnees_eleves_recapitulatif')
{
  // Rechercher et mémoriser les données enregistrées
  $tab_saisies   = array();  // [eleve_id][rubrique_id] => array(note[periode],appreciation[periode],professeur[id])
  $tab_periodes  = array();
  $tab_rubriques = array();
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_recuperer_bilan_officiel_saisies_eleves( $BILAN_TYPE , 0 /*periode_id*/ , $liste_eleve_id , 0 /*prof_id*/ , TRUE /*with_rubrique_nom*/ , TRUE /*with_periodes_avant*/ , FALSE /*only_synthese_generale*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    if($DB_ROW['rubrique_id']) // On laisse tomber d'éventuelles moyennes générales et les appréciations de synthèses, ce n'est pas dans les modèles de livret scolaire CAP ou Bac Pro
    {
      if($DB_ROW['prof_id'])
      {
        $tab_saisies[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['appreciation'][$DB_ROW['periode_ordre']] = suppression_sauts_de_ligne($DB_ROW['saisie_appreciation']);
        $tab_saisies[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['professeur'][$DB_ROW['prof_id']] = afficher_identite_initiale( $DB_ROW['user_nom'] , FALSE , $DB_ROW['user_prenom'] , TRUE , $DB_ROW['user_genre'] );
      }
      else if( ($DB_ROW['saisie_note']!==NULL) && !in_array($DB_ROW['rubrique_id'],$tab_moyenne_exception_matieres) ) // Remarque : un test isset() sur une valeur NULL renverra FALSE !!!
      {
        $tab_saisies[$DB_ROW['eleve_id']][$DB_ROW['rubrique_id']]['note'][$DB_ROW['periode_ordre']] = (float)$DB_ROW['saisie_note'];
      }
      $tab_periodes[$DB_ROW['periode_ordre']] = $DB_ROW['periode_nom'];
      $tab_rubriques[$DB_ROW['rubrique_id']] = $DB_ROW['rubrique_nom'];
    }
  }
  // Calcul des moyennes annuelles et de classe
  $tab_moyennes = array();  // [rubrique_id][eleve_id|0] => moyenne
  foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
  {
    foreach($tab_eleve_id as $eleve_id => $tab_eleve)
    {
      $tab_moyennes[$rubrique_id][$eleve_id] = isset($tab_saisies[$eleve_id][$rubrique_id]['note']) ? round( array_sum($tab_saisies[$eleve_id][$rubrique_id]['note']) / count($tab_saisies[$eleve_id][$rubrique_id]['note']) , 1 ) : NULL ;
    }
    $somme  = array_sum($tab_moyennes[$rubrique_id]);
    $nombre = count( array_filter($tab_moyennes[$rubrique_id],'non_vide') );
    $tab_moyennes[$rubrique_id][0] = ($nombre) ? round($somme/$nombre,1) : NULL ;
  }
  // Calcul du nb de lignes requises par élève
  // Regrouper note et appréciation, insérer le nom de la période dans l'appréciation
  $tab_nb_lignes = array();  // [eleve_id][rubrique_id] => nb
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    $nombre = 0;
    foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
    {
      $nb_lignes_premiere_colonne = isset($tab_saisies[$eleve_id][$rubrique_id]['professeur']) ? 1 + count($tab_saisies[$eleve_id][$rubrique_id]['professeur']) : 1 ;
      $nb_lignes_derniere_colonne = 0 ;
      foreach($tab_periodes as $periode_ordre => $periode_nom)
      {
        if(isset($tab_saisies[$eleve_id][$rubrique_id]['note'][$periode_ordre]))
        {
          $tab_saisies[$eleve_id][$rubrique_id]['appreciation'][$periode_ordre] = isset($tab_saisies[$eleve_id][$rubrique_id]['appreciation'][$periode_ordre]) ? number_format($tab_saisies[$eleve_id][$rubrique_id]['note'][$periode_ordre],1,',','').'/20 - '.$tab_saisies[$eleve_id][$rubrique_id]['appreciation'][$periode_ordre] : $tab_saisies[$eleve_id][$rubrique_id]['note'][$periode_ordre] ;
        }
        if(isset($tab_saisies[$eleve_id][$rubrique_id]['appreciation'][$periode_ordre]))
        {
          $tab_saisies[$eleve_id][$rubrique_id]['appreciation'][$periode_ordre] = $periode_nom.' - '.$tab_saisies[$eleve_id][$rubrique_id]['appreciation'][$periode_ordre];
          $nb_lignes_derniere_colonne += nombre_de_lignes($tab_saisies[$eleve_id][$rubrique_id]['appreciation'][$periode_ordre]);
        }
      }
      $tab_nb_lignes[$eleve_id][$rubrique_id] = max($nb_lignes_premiere_colonne,$nb_lignes_derniere_colonne);
    }
    $tab_nb_lignes[$eleve_id][0] = isset($tab_nb_lignes[$eleve_id]) ? array_sum($tab_nb_lignes[$eleve_id]) : 1 ;
  }
  // Bloc des coordonnées de l'établissement (code repris de [code_officiel_imprimer.php] )
  $tab_etabl_coords = array();
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'denomination'))
  {
    $tab_etabl_coords['denomination'] = $_SESSION['ETABLISSEMENT']['DENOMINATION'];
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'adresse'))
  {
    if($_SESSION['ETABLISSEMENT']['ADRESSE1']) { $tab_etabl_coords['adresse1'] = $_SESSION['ETABLISSEMENT']['ADRESSE1']; }
    if($_SESSION['ETABLISSEMENT']['ADRESSE2']) { $tab_etabl_coords['adresse2'] = $_SESSION['ETABLISSEMENT']['ADRESSE2']; }
    if($_SESSION['ETABLISSEMENT']['ADRESSE3']) { $tab_etabl_coords['adresse3'] = $_SESSION['ETABLISSEMENT']['ADRESSE3']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'telephone'))
  {
    if($_SESSION['ETABLISSEMENT']['TELEPHONE']) { $tab_etabl_coords['telephone'] = 'Tel : '.$_SESSION['ETABLISSEMENT']['TELEPHONE']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'fax'))
  {
    if($_SESSION['ETABLISSEMENT']['FAX']) { $tab_etabl_coords['fax'] = 'Fax : '.$_SESSION['ETABLISSEMENT']['FAX']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'courriel'))
  {
    if($_SESSION['ETABLISSEMENT']['COURRIEL']) { $tab_etabl_coords['courriel'] = 'Mel : '.$_SESSION['ETABLISSEMENT']['COURRIEL']; }
  }
  if(mb_substr_count($_SESSION['OFFICIEL']['INFOS_ETABLISSEMENT'],'url'))
  {
    if($_SESSION['ETABLISSEMENT']['URL']) { $tab_etabl_coords['url'] = 'Web : '.$_SESSION['ETABLISSEMENT']['URL']; }
  }
  // Indication de l'année scolaire (code repris de [code_officiel_imprimer.php] )
  $mois_actuel    = date('n');
  $annee_actuelle = date('Y');
  $mois_bascule   = $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'];
  $annee_affichee = 'Année scolaire ';
  if($mois_bascule==1)
  {
    $annee_affichee .= $annee_actuelle;
  }
  else if($mois_actuel < $mois_bascule)
  {
    $annee_affichee .= ($annee_actuelle-1).'/'.$annee_actuelle;
  }
  else
  {
    $annee_affichee .= $annee_actuelle.'/'.($annee_actuelle+1);
  }
  // Tag date heure initiales (code repris de [code_officiel_imprimer.php] )
  $tag_date_heure_initiales = date('d/m/Y H:i').' '.afficher_identite_initiale($_SESSION['USER_PRENOM'],TRUE,$_SESSION['USER_NOM'],TRUE);
  // Fabrication du PDF
  $archivage_tableau_PDF = new PDF_archivage_tableau( TRUE /*officiel*/ , 'portrait' /*orientation*/ , 5 /*marge_gauche*/ , 5 /*marge_droite*/ , 5 /*marge_haut*/ , 12 /*marge_bas*/ , 'non' /*couleur*/ );
  unset($tab_eleve_id[0]);
  $classe_effectif = count($tab_eleve_id);
  foreach($tab_eleve_id as $eleve_id => $tab_eleve)
  {
    $archivage_tableau_PDF->recapitulatif_initialiser( $tab_etabl_coords , $tab_eleve , $classe_nom , $classe_effectif , $annee_affichee , $tag_date_heure_initiales , $tab_nb_lignes[$eleve_id][0] );
    foreach($tab_rubriques as $rubrique_id => $rubrique_nom)
    {
      $tab_profs = isset($tab_saisies[$eleve_id][$rubrique_id]['professeur']) ? $tab_saisies[$eleve_id][$rubrique_id]['professeur'] : NULL ;
      $moyenne_eleve  = $tab_moyennes[$rubrique_id][$eleve_id] ;
      $moyenne_classe = $tab_moyennes[$rubrique_id][0] ;
      $tab_appreciations = isset($tab_saisies[$eleve_id][$rubrique_id]['appreciation']) ? $tab_saisies[$eleve_id][$rubrique_id]['appreciation'] : array() ;
      $archivage_tableau_PDF->recapitulatif_rubrique( $tab_nb_lignes[$eleve_id][$rubrique_id] , $rubrique_nom , $tab_profs , $moyenne_eleve , $moyenne_classe , $tab_appreciations );
    }
  }
  $periode_nom = 'Année Scolaire';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Enregistrement et affichage du retour.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier_export = 'saisies_'.$BILAN_TYPE.'_'.Clean::fichier($periode_nom).'_'.Clean::fichier($classe_nom).'_'.$action.'_'.fabriquer_fin_nom_fichier__date_et_alea();
FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$fichier_export.'.pdf' , $archivage_tableau_PDF );
echo'<a target="_blank" href="'.URL_DIR_EXPORT.$fichier_export.'.pdf"><span class="file file_pdf">'.$tab_actions[$action].' (format <em>pdf</em>).</span></a>';
// Et le csv éventuel
if($action=='imprimer_donnees_eleves_moyennes')
{
  FileSystem::ecrire_fichier( CHEMIN_DOSSIER_EXPORT.$fichier_export.'.csv' , To::csv($archivage_tableau_CSV) );
  echo'<br />'.NL.'<a target="_blank" href="./force_download.php?fichier='.$fichier_export.'.csv"><span class="file file_txt">'.$tab_actions[$action].' (format <em>csv</em>).</span></a>';
}
exit();

?>