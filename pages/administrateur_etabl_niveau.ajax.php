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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action     = (isset($_POST['f_action']))   ? Clean::texte($_POST['f_action'])    : '';
$famille_id = (isset($_POST['f_famille']))  ? Clean::entier($_POST['f_famille'])  : 0 ;
$niveau_id  = (isset($_POST['f_niveau']))   ? Clean::entier($_POST['f_niveau'])   : 0 ;
$id         = (isset($_POST['f_id']))       ? Clean::entier($_POST['f_id'])       : 0;
$ref        = (isset($_POST['f_ref']))      ? Clean::ref($_POST['f_ref'])         : '';
$nom        = (isset($_POST['f_nom']))      ? Clean::texte($_POST['f_nom'])       : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher les niveaux d'une famille donnée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='recherche_niveau_famille') && $famille_id )
{
  $DB_TAB = DB_STRUCTURE_ADMINISTRATEUR::DB_lister_niveaux_famille($famille_id);
  foreach($DB_TAB as $DB_ROW)
  {
    $class = ($DB_ROW['niveau_actif']) ? 'ajouter_non' : 'ajouter' ;
    $title = ($DB_ROW['niveau_actif']) ? 'Niveau déjà choisi.' : 'Ajouter ce niveau.' ;
    echo'<li>'.html($DB_ROW['niveau_nom'].' ('.$DB_ROW['niveau_ref'].')').'<q id="add_'.$DB_ROW['niveau_id'].'" class="'.$class.'" title="'.$title.'"></q></li>';
  }
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un choix de niveau partagé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter_partage') && $niveau_id )
{
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_niveau_partage($niveau_id,1);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouveau niveau spécifique
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter_perso') && $ref && $nom )
{
  // Vérifier que la référence de la matière est disponible
  if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_niveau_reference($ref) )
  {
    exit('Erreur : référence déjà prise !');
  }
  // Insérer l'enregistrement
  $id = DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_niveau_specifique($ref,$nom);
  // Afficher le retour
  exit(']¤['.$id.']¤['.html($ref).']¤['.html($nom));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un niveau spécifique existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $id && $ref && $nom )
{
  // Vérifier que la référence du niveau est disponible
  if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_niveau_reference($ref,$id) )
  {
    exit('Erreur : référence déjà prise !');
  }
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_niveau_specifique($id,$ref,$nom);
  // Afficher le retour
  exit(']¤['.$id.']¤['.html($ref).']¤['.html($nom));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un niveau partagé || Supprimer un niveau spécifique existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

function retirer_ou_supprimer_niveau($id)
{
  if($id>ID_NIVEAU_PARTAGE_MAX)
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_niveau_specifique($id);
    // Log de l'action
    SACocheLog::ajouter('Suppression d\'un niveau spécifique (n°'.$id.').');
    SACocheLog::ajouter('Suppression des référentiels associés (niveau '.$id.').');
  }
  else
  {
    DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_niveau_partage($id,0);
    // Log de l'action
    SACocheLog::ajouter('Retrait d\'un niveau partagé (n°'.$id.').');
  }
}

if( ($action=='supprimer') && $id )
{
  retirer_ou_supprimer_niveau($id);
  // Afficher le retour
  exit(']¤['.$id);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un niveau
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $niveau_id )
{
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_niveau_partage($niveau_id,0);
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');
?>
