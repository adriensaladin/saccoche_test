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
if( ($_SESSION['SESAMATH_ID']==ID_DEMO) && (!in_array($_POST['f_action'],array('import_siecle','afficher_formulaire_manuel'))) ) {exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))  ? $_POST['f_action']                 : '';
$periode_id = (isset($_POST['f_periode'])) ? Clean::entier($_POST['f_periode']) : 0;
$groupe_id  = (isset($_POST['f_groupe']))  ? Clean::entier($_POST['f_groupe'])  : 0;
$datas      = (isset($_POST['f_data']))    ? Clean::texte($_POST['f_data'])     : '';

$test_sconet = (mb_strpos($action,'siecle')!==FALSE) ? TRUE : FALSE ;
$tab_extensions_autorisees = $test_sconet ? array('zip','xml') : array('txt','csv') ;
$extension_fichier_dest    = $test_sconet ? 'xml'              : 'txt' ;
$fichier_dest = 'absences_import_'.$_SESSION['BASE'].'_'.session_id().'.'.$extension_fichier_dest ;
$fichier_memo = 'absences_import_'.$_SESSION['BASE'].'_'.session_id().'_extraction.'.$extension_fichier_dest ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réception et analyse d'un fichier d'import issu de SIÈCLE
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import_siecle') && $periode_id )
{
  // Récupération du fichier (zip ou pas)
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_dest /*fichier_nom*/ , $tab_extensions_autorisees , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , 'SIECLE_exportAbsence.xml' /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    exit('Erreur : '.$result);
  }
  // Vérification du fichier
  $xml = @simplexml_load_file(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
  if($xml===FALSE)
  {
    exit('Erreur : le fichier transmis n\'est pas un XML valide !');
  }
  $uai = (string)$xml->PARAMETRES->UAJ;
  if(!$uai)
  {
    exit('Erreur : le fichier transmis ne comporte pas de numéro UAI !');
  }
  if($uai!=$_SESSION['WEBMESTRE_UAI'])
  {
    exit('Erreur : le fichier transmis est issu de l\'établissement '.$uai.' et non '.$_SESSION['WEBMESTRE_UAI'].' !');
  }
  $annee_scolaire     = (string)$xml->PARAMETRES->ANNEE_SCOLAIRE;
  $date_export        = (string)$xml->PARAMETRES->DATE_EXPORT;
  $periode_libelle    = (string)$xml->PERIODE->LIBELLE;
  $periode_date_debut = (string)$xml->PERIODE->DATE_DEBUT;
  $periode_date_fin   = (string)$xml->PERIODE->DATE_FIN;
  if( !$annee_scolaire || !$date_export || !$periode_libelle || !$periode_date_debut || !$periode_date_fin )
  {
    exit('Erreur : informations manquantes (année scolaire, période...) !');
  }
  // Récupération des données du fichier
  $tab_users_fichier = array();
  if($xml->eleve)
  {
    foreach ($xml->eleve as $eleve)
    {
      $tab_users_fichier[] = array(
        Clean::entier($eleve->attributes()->elenoet),
        Clean::nom(   $eleve->attributes()->nomEleve),
        Clean::prenom($eleve->attributes()->prenomEleve),
        Clean::entier($eleve->attributes()->nbAbs),
        Clean::entier($eleve->attributes()->nbNonJustif),
        Clean::entier($eleve->attributes()->nbRet),
      );
    }
  }
  $nb_eleves_trouves = count($tab_users_fichier,COUNT_NORMAL);
  if(!$nb_eleves_trouves)
  {
    exit('Erreur : aucun élève trouvé dans le fichier !');
  }
  // On enregistre
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_memo,serialize($tab_users_fichier));
  // On affiche la demande de confirmation
  exit('ok'.']¤['.html($date_export).']¤['.html($periode_libelle).']¤['.html($periode_date_debut).']¤['.html($periode_date_fin));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Réception et analyse d'un fichier d'import issu de GEPI
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import_gepi') && $periode_id )
{
  // Récupération du fichier
  $result = FileSystem::recuperer_upload( CHEMIN_DOSSIER_IMPORT /*fichier_chemin*/ , $fichier_dest /*fichier_nom*/ , $tab_extensions_autorisees /*tab_extensions_autorisees*/ , NULL /*tab_extensions_interdites*/ , NULL /*taille_maxi*/ , '' /*filename_in_zip*/ );
  if($result!==TRUE)
  {
    exit('Erreur : '.$result);
  }
  // Récupération des données du fichier
  $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_dest);
  $contenu = To::deleteBOM(To::utf8($contenu)); // Mettre en UTF-8 si besoin et retirer le BOM éventuel
  $tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
  $separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
  unset($tab_lignes[0]); // Supprimer la 1e ligne
  // Aanalyse et maj du contenu de la base
  $tab_users_fichier = array();
  foreach ($tab_lignes as $ligne_contenu)
  {
    $tab_elements = str_getcsv($ligne_contenu,$separateur);
    $tab_elements = array_slice($tab_elements,0,7);
    if(count($tab_elements)==7)
    {
      list($elenoet,$nom,$prenom,$classe,$nb_absence,$nb_absence_nj,$nb_retard) = $tab_elements;
      $tab_users_fichier[] = array(
        Clean::entier($elenoet),
        Clean::nom($nom),
        Clean::prenom($prenom),
        Clean::entier($nb_absence),
        Clean::entier($nb_absence_nj),
        Clean::entier($nb_retard),
      );
    }
  }
  $nb_eleves_trouves = count($tab_users_fichier,COUNT_NORMAL);
  if(!$nb_eleves_trouves)
  {
    exit('Erreur : aucun élève trouvé dans le fichier !');
  }
  // On enregistre
  FileSystem::ecrire_fichier(CHEMIN_DOSSIER_IMPORT.$fichier_memo,serialize($tab_users_fichier));
  // On affiche la demande de confirmation
  exit('ok'.']¤['.html($nb_eleves_trouves));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement d'un fichier d'import issu de SIÈCLE ou de GEPI
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( in_array($action,array('traitement_import_siecle','traitement_import_gepi')) && $periode_id )
{
  // Récupération des données déjà extraites du fichier
  if(!is_file(CHEMIN_DOSSIER_IMPORT.$fichier_memo))
  {
    exit('Erreur : le fichier '.CHEMIN_DOSSIER_IMPORT.$fichier_memo.' contenant les données à traiter est introuvable !');
  }
  $contenu = file_get_contents(CHEMIN_DOSSIER_IMPORT.$fichier_memo);
  $tab_users_fichier = @unserialize($contenu);
  if($tab_users_fichier===FALSE)
  {
    exit('Erreur : le fichier contenant les données à traiter est syntaxiquement incorrect !');
  }
  // Récupération des données de la base
  $tab_users_base = array();
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_users( 'eleve' , 2 /*actuels_et_anciens*/ , 'user_id,user_sconet_elenoet' /*liste_champs*/ , FALSE /*with_classe*/ , FALSE /*tri_statut*/ );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_users_base[(int)$DB_ROW['user_sconet_elenoet']] = (int)$DB_ROW['user_id'];
  }
  // Analyse et maj du contenu de la base
  $lignes_ok = '';
  $lignes_ko = '';
  foreach ($tab_users_fichier as $tab_donnees_eleve)
  {
    list($eleve_elenoet,$eleve_nom,$eleve_prenom,$nb_absence,$nb_absence_nj,$nb_retard) = $tab_donnees_eleve;
    if(isset($tab_users_base[$eleve_elenoet]))
    {
      $user_id = $tab_users_base[$eleve_elenoet];
      DB_STRUCTURE_OFFICIEL::DB_modifier_officiel_assiduite( $periode_id , $user_id , $nb_absence , $nb_absence_nj , $nb_retard , NULL /* nb_retard_nj */ );
      $lignes_ok .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td><td>'.$nb_absence.'</td><td>'.$nb_absence_nj.'</td><td>'.$nb_retard.'</td><td></td></tr>';
    }
    else
    {
      $lignes_ko .= '<tr><td>'.html($eleve_nom.' '.$eleve_prenom).'</td><td colspan="3" class="r">Numéro Sconet ("ELENOET") '.$eleve_elenoet.' non trouvé dans la base.</td></tr>';
    }
  }
  // affichage du retour
  exit($lignes_ok.$lignes_ko);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher le formulaire de saisie manuel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='afficher_formulaire_manuel') && $periode_id && $groupe_id )
{
  // liste des élèves
  $DB_TAB = DB_STRUCTURE_COMMUN::DB_lister_users_regroupement( 'eleve' , 1 /*statut*/ , 'classe' , $groupe_id );
  if(empty($DB_TAB))
  {
    exit('Aucun élève trouvé dans ce regroupement !');
  }
  $tab_eleves = array();
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_eleves[$DB_ROW['user_id']] = $DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'];
  }
  // liste des saisies
  $tab_assiduite = array();
  $DB_TAB = DB_STRUCTURE_OFFICIEL::DB_lister_officiel_assiduite( $periode_id , array_keys($tab_eleves) );
  foreach($DB_TAB as $DB_ROW)
  {
    $tab_assiduite[$DB_ROW['user_id']] = array(
      'absence'    => $DB_ROW['assiduite_absence'],
      'absence_nj' => $DB_ROW['assiduite_absence_nj'],
      'retard'     => $DB_ROW['assiduite_retard'],
      'retard_nj'  => $DB_ROW['assiduite_retard_nj'],
    );
  }
  // affichage du tableau
  $lignes = '';
  foreach($tab_eleves as $user_id => $user_nom_prenom)
  {
    if(isset($tab_assiduite[$user_id]))
    {
      $nb_absence    = is_null($tab_assiduite[$user_id]['absence'])    ? '' : (int)$tab_assiduite[$user_id]['absence'] ;
      $nb_absence_nj = is_null($tab_assiduite[$user_id]['absence_nj']) ? '' : (int)$tab_assiduite[$user_id]['absence_nj'] ;
      $nb_retard     = is_null($tab_assiduite[$user_id]['retard'])     ? '' : (int)$tab_assiduite[$user_id]['retard'] ;
      $nb_retard_nj  = is_null($tab_assiduite[$user_id]['retard_nj'])  ? '' : (int)$tab_assiduite[$user_id]['retard_nj'] ;
    }
    else
    {
      $nb_absence = $nb_absence_nj = $nb_retard = $nb_retard_nj = '' ;
    }
    $lignes .= '<tr id="tr_'.$user_id.'"><td>'.html($user_nom_prenom).'</td><td><input type="text" size="3" maxlength="3" id="td1_'.$user_id.'" value="'.$nb_absence.'" /></td><td><input type="text" size="3" maxlength="3" id="td2_'.$user_id.'" value="'.$nb_absence_nj.'" /></td><td><input type="text" size="3" maxlength="3" id="td3_'.$user_id.'" value="'.$nb_retard.'" /></td><td><input type="text" size="3" maxlength="3" id="td4_'.$user_id.'" value="'.$nb_retard_nj.'" /></td></tr>';
  }
  exit($lignes);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement de saisies manuelles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='enregistrer_saisies') && $periode_id && $datas )
{
  // Récupération des données saisies
  $tab_eleves = explode('_',$datas);
  foreach($tab_eleves as $eleves_infos)
  {
    list($user_id,$nb_absence,$nb_absence_nj,$nb_retard,$nb_retard_nj) = explode('.',$eleves_infos);
    $user_id       = (int)$user_id;
    $nb_absence    = ($nb_absence==='')    ? NULL : (int)$nb_absence ;
    $nb_absence_nj = ($nb_absence_nj==='') ? NULL : (int)$nb_absence_nj ;
    $nb_retard     = ($nb_retard==='')     ? NULL : (int)$nb_retard ;
    $nb_retard_nj  = ($nb_retard_nj==='')  ? NULL : (int)$nb_retard_nj ;
    DB_STRUCTURE_OFFICIEL::DB_modifier_officiel_assiduite( $periode_id , $user_id , $nb_absence , $nb_absence_nj , $nb_retard , $nb_retard_nj );
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  exit('Erreur : aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload());
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
