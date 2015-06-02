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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? $_POST['f_action']              : '';
$mode   = (isset($_POST['f_mode']))   ? $_POST['f_mode']                : '';
$STEP   = (isset($_POST['f_step']))   ? Clean::entier($_POST['f_step']) : 0;

$tab_action = array(
  'sconet_professeurs_directeurs_oui' => array('sconet'     ,'professeur'),
  'sconet_eleves_oui'                 => array('sconet'     ,'eleve'     ),
  'sconet_parents_oui'                => array('sconet'     ,'parent'    ),
  'base_eleves_eleves'                => array('base_eleves','eleve'     ),
  'base_eleves_parents'               => array('base_eleves','parent'    ),
  'tableur_professeurs_directeurs'    => array('tableur'    ,'professeur'),
  'tableur_eleves'                    => array('tableur'    ,'eleve'     ),
  'tableur_parents'                   => array('tableur'    ,'parent'    ),
);

$tab_step = array(
  10 => "Récupération du fichier (tous les cas)",
  20 => "Extraction des données (tous les cas)",
  31 => "Analyse des données des classes (sconet_professeurs_directeurs | sconet_eleves | base_eleves_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  32 => "Traitement des actions à effectuer sur les classes (sconet_professeurs_directeurs | sconet_eleves | base_eleves_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  41 => "Analyse des données des groupes (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  42 => "Traitement des actions à effectuer sur les groupes (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  51 => "Analyse des données des utilisateurs (tous les cas)",
  52 => "Traitement des actions à effectuer sur les utilisateurs (tous les cas)",
  53 => "Récupérer les identifiants des nouveaux utilisateurs (tous les cas)",
  61 => "Modification d'affectations éventuelles (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  62 => "Traitement des ajouts d'affectations éventuelles (sconet_professeurs_directeurs | sconet_eleves | tableur_professeurs_directeurs | tableur_eleves)",
  71 => "Adresses des parents (sconet_parents | base_eleves_parents | tableur_parents)",
  72 => "Traitement des ajouts/modifications d'adresses éventuelles (sconet_parents | base_eleves_parents | tableur_parents)",
  81 => "Liens de responsabilités des parents (sconet_parents | base_eleves_parents | tableur_parents)",
  82 => "Traitement des liens de responsabilités des parents (sconet_parents | base_eleves_parents | tableur_parents)",
  90 => "Nettoyage des fichiers temporaires (tous les cas)",
);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Il se peut que rien n'ait été récupéré à cause de l'upload d'un fichier trop lourd
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if(empty($_POST))
{
  exit('Erreur : aucune donnée reçue ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload());
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Vérifications / Initialisations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( !isset($tab_action[$action]) || !isset($tab_step[$STEP]) )
{
  exit('Erreur avec les données transmises !');
}

list( $import_origine , $import_profil ) = $tab_action[$action];

$tab_extensions_autorisees = ($import_origine=='sconet') ? array('zip','xml') : array('txt','csv') ;
$extension_fichier_dest    = ($import_origine=='sconet') ? 'xml'              : 'txt' ;
$fichier_dest = 'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'.'.$extension_fichier_dest ;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Quelques fonctions utiles
// ////////////////////////////////////////////////////////////////////////////////////////////////////

function load_fichier($nom)
{
  global $import_origine,$import_profil;
  $fnom = CHEMIN_DOSSIER_IMPORT.'import_'.$import_origine.'_'.$import_profil.'_'.$_SESSION['BASE'].'_'.session_id().'_'.$nom.'.txt';
  if(!is_file($fnom))
  {
    exit('Erreur : le fichier contenant les données à traiter est introuvable !');
  }
  $contenu = file_get_contents($fnom);
  $tableau = @unserialize($contenu);
  if($tableau===FALSE)
  {
    exit('Erreur : le fichier contenant les données à traiter est syntaxiquement incorrect !');
  }
  return $tableau;
}

function afficher_etapes($import_origine,$import_profil)
{
  $puces = '<ul id="step">'.NL;
  switch($import_origine.'+'.$import_profil)
  {
    case  'sconet+professeur' :
    case 'tableur+professeur' :
    case  'sconet+eleve'      :
    case 'tableur+eleve'      :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step4">Étape 4 - Groupes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step5">Étape 5 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step6">Étape 6 - Affectations (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 7 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
    case      'sconet+parent' :
    case 'base_eleves+parent' :
    case     'tableur+parent' :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step5">Étape 3 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step7">Étape 4 - Adresses (ajouts / modifications)</li>'.NL;
      $puces .= '<li id="step8">Étape 5 - Responsabilités (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 6 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
    case  'base_eleves+eleve' :
      $puces .= '<li id="step1">Étape 1 - Récupération du fichier</li>'.NL;
      $puces .= '<li id="step2">Étape 2 - Extraction des données</li>'.NL;
      $puces .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step5">Étape 4 - Utilisateurs (ajouts / modifications / suppressions)</li>'.NL;
      $puces .= '<li id="step9">Étape 5 - Nettoyage des fichiers temporaires</li>'.NL;
      break;
  }
  $puces .= '</ul>'.NL;
  return $puces;
}

function aff_champ($profil,$type,$val)
{
  if($type!='genre')
  {
    return html($val);
  }
  else if($profil=='eleve')
  {
    return Html::$tab_genre['enfant'][$val];
  }
  else
  {
    return Html::$tab_genre['adulte'][$val];
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Le fichier étant très gros (>175Ko) on l'a découpé en morceaux plus digestes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

require(CHEMIN_DOSSIER_PAGES.$PAGE.'.ajax.step'.$STEP.'.php');
exit();

?>
