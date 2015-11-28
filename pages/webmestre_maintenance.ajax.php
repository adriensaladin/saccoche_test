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

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '' ;
$motif  = (isset($_POST['f_motif']))  ? Clean::texte($_POST['f_motif'])  : '' ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Bloquer ou débloquer l'application
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='bloquer')
{
  ajouter_log_PHP( 'Maintenance' /*log_objet*/ , 'Application fermée.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::bloquer_application($_SESSION['USER_PROFIL_TYPE'],'0',$motif);
  Json::end( TRUE , '<label class="erreur">Application fermée : '.html($motif).'</label>' );
}

if($action=='debloquer')
{
  ajouter_log_PHP( 'Maintenance' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::debloquer_application($_SESSION['USER_PROFIL_TYPE'],'0');
  Json::end( TRUE , '<label class="valide">Application accessible.</label>' );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des dossiers additionnels par établissement
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='verif_dir_etabl')
{
  // Récupérer les ids des structures
  $tab_bases = array_keys( DB_WEBMESTRE_WEBMESTRE::DB_lister_structures_id() );
  // Récupérer les dossiers additionnels par établissement
  $tab_dossiers = array();
  foreach(FileSystem::$tab_dossier_tmp_structure as $dossier_key => $dossier_dir)
  {
    $tab_dossiers[$dossier_dir] = array_fill_keys ( FileSystem::lister_contenu_dossier($dossier_dir) , TRUE );
    unset($tab_dossiers[$dossier_dir]['index.htm']);
    ksort($tab_dossiers[$dossier_dir],SORT_NATURAL);
  }
  // Pour l'affichage du retour
  $thead = '<tr><td colspan="2">Vérification des dossiers additionnels par établissement - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody_ok = '';
  $tbody_pb = '';
  // On parcourt les dossiers devant exister : ok ou création.
  foreach($tab_bases as $base_id)
  {
    foreach(FileSystem::$tab_dossier_tmp_structure as $dossier_key => $dossier_dir)
    {
      if(isset($tab_dossiers[$dossier_dir][$base_id]))
      {
        $tbody_ok .= '<tr class="v"><td>Dossier présent</td><td>'.$dossier_key.$base_id.'</td></tr>';
        unset($tab_dossiers[$dossier_dir][$base_id]);
      }
      else
      {
        FileSystem::creer_dossier($dossier_dir.$base_id);
        FileSystem::ecrire_fichier($dossier_dir.$base_id.DS.'index.htm','Circulez, il n\'y a rien à voir par ici !');
        $tbody_pb .= '<tr class="r"><td>Dossier manquant (&rarr; ajouté)</td><td>'.$dossier_key.$base_id.'</td></tr>';
      }
    }
  }
  // Il reste éventuellement les dossiers en trop.
  foreach(FileSystem::$tab_dossier_tmp_structure as $dossier_key => $dossier_dir)
  {
    if(count($tab_dossiers[$dossier_dir]))
    {
      foreach($tab_dossiers[$dossier_dir] as $base_id => $tab)
      {
        if(isset($tab_dossiers[$dossier_dir][$base_id]))
        {
          if(is_dir($dossier_dir.$base_id))
          {
            FileSystem::supprimer_dossier($dossier_dir.$base_id);
            $tbody_pb .= '<tr class="r"><td>Dossier en trop (&rarr; supprimé)</td><td>'.$dossier_key.$base_id.'</td></tr>';
          }
          // Normalement, ne devrait pas, mais suite à un bug, des fichiers se sont retrouvés créés...
          if(is_file($dossier_dir.$base_id))
          {
            FileSystem::supprimer_fichier($dossier_dir.$base_id);
            $tbody_pb .= '<tr class="r"><td>Fichier en trop (&rarr; supprimé)</td><td>'.$dossier_key.$base_id.'</td></tr>';
          }
        }
      }
    }
  }
  // Enregistrement du rapport
  $fichier_nom = 'rapport_verif_dir_etabl_'.$_SESSION['BASE'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $fichier_nom , $thead , $tbody_pb.$tbody_ok );
  Json::end( TRUE , URL_DIR_EXPORT.$fichier_nom );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mise à jour automatique des fichiers
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier_import  = CHEMIN_DOSSIER_IMPORT.'telechargement.zip';
$dossier_dezip   = CHEMIN_DOSSIER_IMPORT.'SACoche'.DS;
$dossier_install = CHEMIN_DOSSIER_SACOCHE;

//
// 1. Récupération de l'archive <em>ZIP</em>...
//
if($action=='maj_etape1')
{
  if(IS_HEBERGEMENT_SESAMATH)
  {
    Json::end( FALSE , 'La mise à jour de SACoche sur le serveur Sésamath doit s\'effectuer en déployant le SVN !' );
  }
  if(is_file(CHEMIN_FICHIER_WS_LCS))
  {
    Json::end( FALSE , 'La mise à jour du module LCS-SACoche doit s\'effectuer via le LCS !' );
  }
  $contenu_zip = cURL::get_contents( SERVEUR_TELECHARGEMENT ,FALSE /*tab_post*/ , 90 /*timeout*/ );
  if(substr($contenu_zip,0,6)=='Erreur')
  {
    Json::end( FALSE , $contenu_zip );
  }
  FileSystem::ecrire_fichier($fichier_import,$contenu_zip);
  Json::end( TRUE , 'Décompression de l\'archive&hellip;' );
}

//
// 2. Décompression de l'archive...
//
if($action=='maj_etape2')
{
  if(is_dir($dossier_dezip))
  {
    FileSystem::supprimer_dossier($dossier_dezip);
  }
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( $fichier_import , CHEMIN_DOSSIER_IMPORT , TRUE /*use_ZipArchive*/ );
  if($code_erreur)
  {
    Json::end( FALSE , 'Fichiers impossibles à extraire ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }
  Json::end( TRUE , 'Analyse des fichiers et recensement des dossiers&hellip;' );
}

//
// 3. Analyse des fichiers et recensement des dossiers... (après initialisation de la session temporaire)
//
if($action=='maj_etape3')
{
  $_SESSION['tmp'] = array();
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , FALSE /*with_first_dir*/ );
  FileSystem::analyser_dossier( $dossier_dezip   , strlen($dossier_dezip)   , 'apres' , FALSE /*with_first_dir*/ );
  Json::end( TRUE , 'Analyse et répercussion des modifications&hellip;' );
}

//
// 4. Analyse et répercussion des modifications... (tout en bloquant l'appli)
//
if($action=='maj_etape4')
{
  $thead = '<tr><td colspan="2">Mise à jour automatique - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = '';
  // Bloquer l'application
  ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application fermée.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::bloquer_application('automate','0','Mise à jour des fichiers en cours.');
  // Dossiers : ordre croissant pour commencer par ceux les moins imbriqués : obligatoire pour l'ajout, et pour la suppression on teste si pas déjà supprimé.
  ksort($_SESSION['tmp']['dossier']);
  foreach($_SESSION['tmp']['dossier'] as $dossier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      // Dossier inchangé (cas le plus fréquent donc testé en premier).
    }
    elseif(!isset($tab['avant']))
    {
      // Dossier à ajouter
      $tbody .= '<tr><td class="v">Dossier ajouté</td><td>'.$dossier.'</td></tr>';
      if( !FileSystem::creer_dossier($dossier_install.$dossier) )
      {
        ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
        LockAcces::debloquer_application('automate','0');
        Json::end( FALSE , 'Dossier "'.$dossier.'" non créé ou inaccessible en écriture !' );
      }
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      // Dossier à supprimer
      $tbody .= '<tr><td class="r">Dossier supprimé</td><td>'.$dossier.'</td></tr>';
      if(is_dir($dossier_install.$dossier))
      {
        FileSystem::supprimer_dossier($dossier_install.$dossier);
      }
    }
  }
  // Fichiers : ordre décroissant pour avoir VERSION.txt en dernier (majuscules avant dans la table ASCII).
  krsort($_SESSION['tmp']['fichier']);
  foreach($_SESSION['tmp']['fichier'] as $fichier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      if( ($tab['avant']!=$tab['apres']) && (substr($fichier,-9)!='.htaccess') )
      {
        // Fichier changé => maj (si le .htaccess a été changé, c'est sans doute volontaire, ne pas y toucher)
        if( !copy( $dossier_dezip.$fichier , $dossier_install.$fichier ) )
        {
          ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
          LockAcces::debloquer_application('automate','0');
          Json::end( FALSE , 'Erreur lors de l\'écriture du fichier "'.$fichier.'" !' );
        }
        $tbody .= '<tr><td class="b">Fichier modifié</td><td>'.$fichier.'</td></tr>';
      }
    }
    elseif( (!isset($tab['avant'])) && (substr($fichier,-9)!='.htaccess') )
    {
      // Fichier à ajouter (si le .htaccess n'y est pas, c'est sans doute volontaire, ne pas l'y remettre)
      if( !copy( $dossier_dezip.$fichier , $dossier_install.$fichier ) )
      {
        ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
        LockAcces::debloquer_application('automate','0');
        Json::end( FALSE , 'Erreur lors de l\'écriture du fichier "'.$fichier.'" !' );
      }
      $tbody .= '<tr><td class="v">Fichier ajouté</td><td>'.$fichier.'</td></tr>';
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      // Fichier à supprimer
      FileSystem::supprimer_fichier($dossier_install.$fichier , TRUE /*verif_exist*/ );
      $tbody .= '<tr><td class="r">Fichier supprimé</td><td>'.$fichier.'</td></tr>';
    }
  }
  // Débloquer l'application
  ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::debloquer_application('automate','0');
  // Enregistrement du rapport
  $_SESSION['tmp']['rapport_filename'] = 'rapport_maj_'.$_SESSION['BASE'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $_SESSION['tmp']['rapport_filename'] , $thead , $tbody );
  Json::end( TRUE , 'Rapport des modifications apportées et nettoyage&hellip;' );
}

//
// 5. Nettoyage...
//
if($action=='maj_etape5')
{
  FileSystem::supprimer_dossier($dossier_dezip);
  $fichier_chemin = URL_DIR_EXPORT.$_SESSION['tmp']['rapport_filename'];
  unset($_SESSION['tmp']);
  Json::end( TRUE , array( 'version' => VERSION_PROG , 'fichier' => $fichier_chemin ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des fichiers de l'application en place
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$fichier_import  = CHEMIN_DOSSIER_IMPORT.'verification.zip';
$dossier_dezip   = CHEMIN_DOSSIER_IMPORT.'SACoche'.DS;
$dossier_install = '.'.DS;

//
// 1. Récupération de l'archive <em>ZIP</em>...
//
if($action=='verif_file_appli_etape1')
{
  $tab_post = array();
  $tab_post['verification'] = 1;
  $tab_post['version'] = VERSION_PROG;
  $contenu_zip = cURL::get_contents( SERVEUR_TELECHARGEMENT , $tab_post , 60 /*timeout*/ );
  if(substr($contenu_zip,0,6)=='Erreur')
  {
    Json::end( FALSE , $contenu_zip );
  }
  FileSystem::ecrire_fichier($fichier_import,$contenu_zip);
  Json::end( TRUE , 'Décompression de l\'archive&hellip;' );
}

//
// 2. Décompression de l'archive...
//
if($action=='verif_file_appli_etape2')
{
  if(is_dir($dossier_dezip))
  {
    FileSystem::supprimer_dossier($dossier_dezip);
  }
  // Dezipper dans le dossier temporaire
  $code_erreur = FileSystem::unzip( $fichier_import , CHEMIN_DOSSIER_IMPORT , TRUE /*use_ZipArchive*/ );
  if($code_erreur)
  {
    Json::end( FALSE , 'Fichiers impossibles à extraire ('.FileSystem::$tab_zip_error[$code_erreur].') !' );
  }
  Json::end( TRUE , 'Analyse des fichiers et recensement des dossiers&hellip;' );
}

//
// 3. Analyse des fichiers et recensement des dossiers... (après initialisation de la session temporaire)
//
if($action=='verif_file_appli_etape3')
{
  $_SESSION['tmp'] = array();
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , FALSE /*with_first_dir*/ );
  FileSystem::analyser_dossier( $dossier_dezip   , strlen($dossier_dezip)   , 'apres' , FALSE /*with_first_dir*/ , FALSE );
  Json::end( TRUE , 'Comparaison des données&hellip;' );
}

//
// 4. Comparaison des données...
//
if($action=='verif_file_appli_etape4')
{
  $thead = '<tr><td colspan="2">Vérification des fichiers de l\'application en place - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody_ok = '';
  $tbody_pb = '';
  // Dossiers : ordre croissant pour commencer par ceux les moins imbriqués : obligatoire pour l'ajout, et pour la suppression on teste si pas déjà supprimé.
  ksort($_SESSION['tmp']['dossier']);
  foreach($_SESSION['tmp']['dossier'] as $dossier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      // Dossier inchangé (cas le plus fréquent donc testé en premier).
      $tbody_ok .= '<tr class="v"><td>Dossier présent</td><td>'.$dossier.'</td></tr>';
    }
    elseif(!isset($tab['avant']))
    {
      // Dossier manquant
      $tbody_pb .= '<tr class="r"><td>Dossier manquant</td><td>'.$dossier.'</td></tr>';
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      // Dossier en trop
      $tbody_pb .= '<tr class="r"><td>Dossier en trop</td><td>'.$dossier.'</td></tr>';
    }
  }
  // Fichiers : ordre décroissant pour avoir VERSION.txt en dernier (majuscules avant dans la table ASCII).
  krsort($_SESSION['tmp']['fichier']);
  foreach($_SESSION['tmp']['fichier'] as $fichier => $tab)
  {
    if( (isset($tab['avant'])) && (isset($tab['apres'])) )
    {
      if( ($tab['avant']==$tab['apres']) || (substr($fichier,-9)=='.htaccess') )
      {
        // Fichier identique (si le .htaccess a été changé, c'est sans doute volontaire, ne pas y toucher)
        $tbody_ok .= '<tr class="v"><td>Fichier identique</td><td>'.$fichier.'</td></tr>';
      }
      else
      {
        // Fichier différent
        $tbody_pb .= '<tr class="r"><td>Fichier différent</td><td>'.$fichier.'</td></tr>';
      }
    }
    elseif( (!isset($tab['avant'])) && (substr($fichier,-9)!='.htaccess') )
    {
      // Fichier manquant
      $tbody_pb .= '<tr class="r"><td>Fichier manquant</td><td>'.$fichier.'</td></tr>';
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      $tbody_pb .= '<tr class="r"><td>Fichier en trop</td><td>'.$fichier.'</td></tr>';
    }
  }
  // Enregistrement du rapport
  $_SESSION['tmp']['rapport_filename'] = 'rapport_verif_file_appli_'.$_SESSION['BASE'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.html';
  FileSystem::fabriquer_fichier_rapport( $_SESSION['tmp']['rapport_filename'] , $thead , $tbody_pb.$tbody_ok );
  Json::end( TRUE , 'Rapport des différences trouvées et nettoyage&hellip;' );
}

//
// 5. Nettoyage...
//
if($action=='verif_file_appli_etape5')
{
  FileSystem::supprimer_dossier($dossier_dezip);
  $fichier_chemin = URL_DIR_EXPORT.$_SESSION['tmp']['rapport_filename'];
  unset($_SESSION['tmp']);
  Json::end( TRUE , $fichier_chemin );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Étape de mise à jour forcée des bases des établissements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$step = (isset($_POST['step'])) ? Clean::entier($_POST['step']) : 0 ;  // Numéro de l'étape

if( ($action=='maj_bases_etabl') && $step )
{
  // 1. Liste des bases
  if($step==1)
  {
    // Récupérer les ids des structures
    $_SESSION['tmp'] = array(
      'base_id' => array_keys( DB_WEBMESTRE_WEBMESTRE::DB_lister_structures_id() ),
      'rapport' => array(),
    );
    Json::end( TRUE , 'continuer' );
  }
  // n. Étape suivante
  elseif(!empty($_SESSION['tmp']['base_id']))
  {
    $base_id = current($_SESSION['tmp']['base_id']);
    charger_parametres_mysql_supplementaires($base_id);
    $version_base = DB_STRUCTURE_MAJ_BASE::DB_version_base();
    if(empty($_SESSION['tmp']['rapport'][$base_id]))
    {
      $_SESSION['tmp']['rapport'][$base_id] = $version_base;
    }
    // base déjà à jour
    if($_SESSION['tmp']['rapport'][$base_id] == VERSION_BASE_STRUCTURE)
    {
      array_shift($_SESSION['tmp']['base_id']);
      Json::end( TRUE , 'continuer' );
    }
    // on lance la maj "classique"
    if($version_base != VERSION_BASE_STRUCTURE)
    {
      $maj_classique = TRUE;
      // Bloquer l'application
      LockAcces::bloquer_application('automate',$base_id,'Mise à jour de la base en cours.');
      // Lancer une mise à jour de la base
      DB_STRUCTURE_MAJ_BASE::DB_maj_base($version_base);
    }
    else
    {
      $maj_classique = FALSE;
    }
    // test si cela nécessite une mise à jour complémentaire
    $_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'] = DB_STRUCTURE_MAJ_BASE::DB_version_base_maj_complementaire();
    if(!$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'])
    {
      // Débloquer l'application
      LockAcces::debloquer_application('automate',$base_id);
      array_shift($_SESSION['tmp']['base_id']);
      Json::end( TRUE , 'continuer' );
    }
    elseif($maj_classique)
    {
      // on fera la maj complémentaire au prochain coup
      Json::end( TRUE , 'continuer' );
    }
    else
    {
      // on lance une étape de la maj complémentaire
      DB_STRUCTURE_MAJ_BASE::DB_maj_base_complement();
      if(!$_SESSION['VERSION_BASE_MAJ_COMPLEMENTAIRE'])
      {
        LockAcces::debloquer_application('automate',$base_id);
        array_shift($_SESSION['tmp']['base_id']);
      }
      Json::end( TRUE , 'continuer' );
    }
  }
  // n. Dernière étape
  else
  {
    // Rapport
    $thead = '<tr><td>Mise à jour forcée des bases - '.date('d/m/Y H:i:s').'</td></tr>';
    $tbody = '';
    foreach($_SESSION['tmp']['rapport'] as $base_id => $version_base)
    {
      $tbody .= ($version_base==VERSION_BASE_STRUCTURE) ? '<tr><td class="b">Base n°'.$base_id.' déjà à jour.</td></tr>' : '<tr><td class="v">Base n°'.$base_id.' mise à jour depuis la version '.$version_base.'.</td></tr>' ;
    }
    // Enregistrement du rapport
    $fichier_rapport = 'rapport_maj_bases_'.$_SESSION['BASE'].'_'.fabriquer_fin_nom_fichier__date_et_alea().'.html';
    FileSystem::fabriquer_fichier_rapport( $fichier_rapport , $thead , $tbody );
    $fichier_chemin = URL_DIR_EXPORT.$fichier_rapport;
    // retour
    unset($_SESSION['tmp']);
    Json::end( TRUE , $fichier_chemin );
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

Json::end( FALSE , 'Erreur avec les données transmises !' );

?>
