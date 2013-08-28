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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action']) : '';
$motif  = (isset($_POST['f_motif']))  ? Clean::texte($_POST['f_motif'])  : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Bloquer ou débloquer l'application
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='debloquer')
{
  ajouter_log_PHP( 'Maintenance' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::debloquer_application($_SESSION['USER_PROFIL_TYPE'],'0');
  exit('<label class="valide">Application accessible.</label>');
}

if($action=='bloquer')
{
  ajouter_log_PHP( 'Maintenance' /*log_objet*/ , 'Application fermée.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::bloquer_application($_SESSION['USER_PROFIL_TYPE'],'0',$motif);
  exit('<label class="erreur">Application fermée : '.html($motif).'</label>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérification des droits en écriture
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='verif_droits')
{
  $_SESSION['tmp'] = array();
  // Récupérer l'arborescence
  $dossier_install = '.';
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , TRUE /*with_first_dir*/ );
  // Pour l'affichage du retour
  $thead = '<tr><td colspan="2">Vérification des droits en écriture - '.date('d/m/Y H:i:s').'</td></tr>';
  $tbody = '';
  // Dossiers
  ksort($_SESSION['tmp']['dossier']);
  foreach($_SESSION['tmp']['dossier'] as $dossier => $tab)
  {
    $dossier = ($dossier) ? '.'.$dossier : '.'.DS ;
    $tbody .= (@is_writable($dossier)) ? '<tr><td class="v">Dossier accessible en écriture</td><td>'.$dossier.'</td></tr>' : '<tr><td class="r">Dossier aux droits insuffisants</td><td>'.$dossier.'</td></tr>' ;
  }
  // Fichiers
  ksort($_SESSION['tmp']['fichier']);
  foreach($_SESSION['tmp']['fichier'] as $fichier => $tab)
  {
    $fichier = '.'.$fichier;
    $tbody .= (@is_writable($fichier)) ? '<tr><td class="v">Fichier accessible en écriture</td><td>'.$fichier.'</td></tr>' : '<tr><td class="r">Fichier aux droits insuffisants</td><td>'.$fichier.'</td></tr>' ;
  }
  // Enregistrement du rapport ; extension PHP et non HTML pour éviter des pb de mise en cache.
  FileSystem::fabriquer_fichier_rapport( 'rapport_droits.php' , $thead , $tbody );
  exit('ok');
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
          FileSystem::supprimer_dossier($dossier_dir.$base_id);
          $tbody_pb .= '<tr class="r"><td>Dossier en trop (&rarr; supprimé)</td><td>'.$dossier_key.$base_id.'</td></tr>';
        }
      }
    }
  }
  // Enregistrement du rapport ; extension PHP et non HTML pour éviter des pb de mise en cache.
  FileSystem::fabriquer_fichier_rapport( 'rapport_verif_dir_etabl.php' , $thead , $tbody_pb.$tbody_ok );
  exit('ok');
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
    exit(']¤['.'pb'.']¤['.'La mise à jour de SACoche sur le serveur Sésamath doit s\'effectuer en déployant le SVN !');
  }
  if(is_file(CHEMIN_FICHIER_WS_LCS))
  {
    exit(']¤['.'pb'.']¤['.'La mise à jour du module LCS-SACoche doit s\'effectuer via le LCS !');
  }
  $contenu_zip = cURL::get_contents( SERVEUR_TELECHARGEMENT ,FALSE /*tab_post*/ , 90 /*timeout*/ );
  if(substr($contenu_zip,0,6)=='Erreur')
  {
    exit(']¤['.'pb'.']¤['.$contenu_zip);
  }
  FileSystem::ecrire_fichier($fichier_import,$contenu_zip);
  exit(']¤['.'ok'.']¤['."Décompression de l'archive&hellip;");
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
    exit(']¤['.'pb'.']¤['.'Fichiers impossibles à extraire ('.FileSystem::$tab_zip_error[$code_erreur].') !');
  }
  exit(']¤['.'ok'.']¤['."Analyse des fichiers et recensement des dossiers&hellip;");
}

//
// 3. Analyse des fichiers et recensement des dossiers... (après initialisation de la session temporaire)
//
if($action=='maj_etape3')
{
  $_SESSION['tmp'] = array();
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , FALSE /*with_first_dir*/ );
  FileSystem::analyser_dossier( $dossier_dezip   , strlen($dossier_dezip)   , 'apres' , FALSE /*with_first_dir*/ );
  exit(']¤['.'ok'.']¤['."Analyse et répercussion des modifications&hellip;");
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
  LockAcces::bloquer_application($_SESSION['USER_PROFIL_TYPE'],'0','Mise à jour des fichiers en cours.');
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
        LockAcces::debloquer_application($_SESSION['USER_PROFIL_TYPE'],'0');
        exit(']¤['.'pb'.']¤['."Dossier ".$dossier." non créé ou inaccessible en écriture !");
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
          LockAcces::debloquer_application($_SESSION['USER_PROFIL_TYPE'],'0');
          exit(']¤['.'pb'.']¤['."Erreur lors de l'écriture du fichier ".$fichier." !");
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
        LockAcces::debloquer_application($_SESSION['USER_PROFIL_TYPE'],'0');
        exit(']¤['.'pb'.']¤['."Erreur lors de l'écriture du fichier ".$fichier." !");
      }
      $tbody .= '<tr><td class="v">Fichier ajouté</td><td>'.$fichier.'</td></tr>';
    }
    elseif(!isset($tab['apres'])) // (forcément)
    {
      // Fichier à supprimer
      if(is_file($dossier_install.$fichier))
      {
        unlink($dossier_install.$fichier);
      }
      $tbody .= '<tr><td class="r">Fichier supprimé</td><td>'.$fichier.'</td></tr>';
    }
  }
  // Débloquer l'application
  ajouter_log_PHP( 'Mise à jour des fichiers' /*log_objet*/ , 'Application accessible.' /*log_contenu*/ , __FILE__ /*log_fichier*/ , __LINE__ /*log_ligne*/ , FALSE /*only_sesamath*/ );
  LockAcces::debloquer_application($_SESSION['USER_PROFIL_TYPE'],'0');
  // Enregistrement du rapport ; extension PHP et non HTML pour éviter des pb de mise en cache.
  FileSystem::fabriquer_fichier_rapport( 'rapport_maj.php' , $thead , $tbody );
  exit(']¤['.'ok'.']¤['.'Rapport des modifications apportées et nettoyage&hellip;');
}

//
// 5. Nettoyage...
//
if($action=='maj_etape5')
{
  unset($_SESSION['tmp']);
  FileSystem::supprimer_dossier($dossier_dezip);
  exit(']¤['.'ok'.']¤['.VERSION_PROG);
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
    exit(']¤['.'pb'.']¤['.$contenu_zip);
  }
  FileSystem::ecrire_fichier($fichier_import,$contenu_zip);
  exit(']¤['.'ok'.']¤['."Décompression de l'archive&hellip;");
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
    exit(']¤['.'pb'.']¤['.'Fichiers impossibles à extraire ('.FileSystem::$tab_zip_error[$code_erreur].') !');
  }
  exit(']¤['.'ok'.']¤['."Analyse des fichiers et recensement des dossiers&hellip;");
}

//
// 3. Analyse des fichiers et recensement des dossiers... (après initialisation de la session temporaire)
//
if($action=='verif_file_appli_etape3')
{
  $_SESSION['tmp'] = array();
  FileSystem::analyser_dossier( $dossier_install , strlen($dossier_install) , 'avant' , FALSE /*with_first_dir*/ );
  FileSystem::analyser_dossier( $dossier_dezip   , strlen($dossier_dezip)   , 'apres' , FALSE /*with_first_dir*/ , FALSE );
  exit(']¤['.'ok'.']¤['."Comparaison des données&hellip;");
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
  // Enregistrement du rapport ; extension PHP et non HTML pour éviter des pb de mise en cache.
  FileSystem::fabriquer_fichier_rapport( 'rapport_verif_file_appli.php' , $thead , $tbody_pb.$tbody_ok );
  exit(']¤['.'ok'.']¤['.'Rapport des différences trouvées et nettoyage&hellip;');
}

//
// 5. Nettoyage...
//
if($action=='verif_file_appli_etape5')
{
  unset($_SESSION['tmp']);
  FileSystem::supprimer_dossier($dossier_dezip);
  exit(']¤['.'ok'.']¤['.VERSION_PROG);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
