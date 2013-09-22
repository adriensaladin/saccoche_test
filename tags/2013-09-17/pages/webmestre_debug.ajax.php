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

$action       = (isset($_POST['f_action']))      ? Clean::texte($_POST['f_action'])      : '';
$chemin_logs  = (isset($_POST['f_chemin_logs'])) ? Clean::texte($_POST['f_chemin_logs']) : '';
$fichier_logs = (isset($_POST['f_fichier']))     ? Clean::fichier($_POST['f_fichier'])   : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier les paramètres de debug
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='modifier_debug')
{
  $debug = 0;
  $tab_debug = array(
    'PHP'     =>   1,
    'PHPCAS'  =>   2,
    'SQL'     =>   4,
    'SESSION' =>   8,
    'POST'    =>  16,
    'GET'     =>  32,
    'FILES'   =>  64,
    'COOKIE'  => 128,
    'SERVER'  => 256,
    'CONST'   => 512,
  );
  foreach($tab_debug as $debug_mode => $debug_val)
  {
    $debug += (isset($_POST['f_debug_'.$debug_mode])) ? $debug_val : 0 ;
  }
  if($debug)
  {
    FileSystem::ecrire_fichier( CHEMIN_FICHIER_DEBUG_CONFIG , $debug );
  }
  else
  {
    unlink(CHEMIN_FICHIER_DEBUG_CONFIG);
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier le chemin des logs phpCAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier_chemin_phpCAS') && ($chemin_logs) )
{
  $chemin_logs = (substr($chemin_logs,-1)==DS) ? $chemin_logs : $chemin_logs.DS ;
  // Vérifier chemin valide
  if(!is_dir($chemin_logs))
  {
    exit('Chemin invalide (ce dossier n\'existe pas) !');
  }
  // Tester droits en écriture
  if( !FileSystem::ecrire_fichier_si_possible( $chemin_logs.'debugcas_test_ecriture.txt' , 'ok' ) )
  {
    exit('Droits en écriture dans ce dossier insuffisants !');
  }
  // ok
  unlink($chemin_logs.'debugcas_test_ecriture.txt');
  FileSystem::fabriquer_fichier_hebergeur_info( array('CHEMIN_LOGS_PHPCAS'=>$chemin_logs) );
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Effacer un fichier de logs de phpCAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $fichier_logs )
{
  unlink(CHEMIN_LOGS_PHPCAS.$fichier_logs.'.txt');
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Récupérer un fichier de logs de phpCAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='voir') && $fichier_logs )
{
  FileSystem::zip( CHEMIN_DOSSIER_EXPORT.$fichier_logs.'.zip' , $fichier_logs.'.txt' , file_get_contents(CHEMIN_LOGS_PHPCAS.$fichier_logs.'.txt') );
  exit('<ul class="puce"><li><a class="lien_ext" href="'.URL_DIR_EXPORT.$fichier_logs.'.zip'.'"><span class="file file_zip">Fichier de logs au format <em>zip</em>.</li></ul>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
