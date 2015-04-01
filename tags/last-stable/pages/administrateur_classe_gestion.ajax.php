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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? Clean::texte($_POST['f_action'])  : '';
$id     = (isset($_POST['f_id']))     ? Clean::entier($_POST['f_id'])     : 0;
$niveau = (isset($_POST['f_niveau'])) ? Clean::entier($_POST['f_niveau']) : 0;
$ref    = (isset($_POST['f_ref']))    ? Clean::ref($_POST['f_ref'])       : '';
$nom    = (isset($_POST['f_nom']))    ? Clean::texte($_POST['f_nom'])     : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une nouvelle classe
// ////////////////////////////////////////////////////////////////////////////////////////////////////
if( ($action=='ajouter') && $niveau && $ref && $nom )
{
  // Vérifier que la référence de la classe est disponible
  if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_classe_reference($ref) )
  {
    exit('Erreur : référence de classe déjà existante !');
  }
  // Insérer l'enregistrement
  $groupe_id = DB_STRUCTURE_ADMINISTRATEUR::DB_ajouter_groupe_par_admin('classe',$ref,$nom,$niveau);
  // Afficher le retour
  echo'<tr id="id_'.$groupe_id.'" class="new">';
  echo  '<td>{{NIVEAU_NOM}}</td>';
  echo  '<td>'.html($ref).'</td>';
  echo  '<td>'.html($nom).'</td>';
  echo  '<td class="nu">';
  echo    '<q class="modifier" title="Modifier cette classe."></q>';
  echo    '<q class="supprimer" title="Supprimer cette classe."></q>';
  echo  '</td>';
  echo'</tr>';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier une classe existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////
else if( ($action=='modifier') && $id && $niveau && $ref && $nom )
{
  // Vérifier que la référence de la classe est disponible
  if( DB_STRUCTURE_ADMINISTRATEUR::DB_tester_classe_reference($ref,$id) )
  {
    exit('Erreur : référence déjà existante !');
  }
  // Mettre à jour l'enregistrement
  DB_STRUCTURE_ADMINISTRATEUR::DB_modifier_groupe_par_admin($id,$ref,$nom,$niveau);
  // Afficher le retour
  echo'<td>{{NIVEAU_NOM}}</td>';
  echo'<td>'.html($ref).'</td>';
  echo'<td>'.html($nom).'</td>';
  echo'<td class="nu">';
  echo  '<q class="modifier" title="Modifier cette classe."></q>';
  echo  '<q class="supprimer" title="Supprimer cette classe."></q>';
  echo'</td>';
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Supprimer une classe existante
// ////////////////////////////////////////////////////////////////////////////////////////////////////
else if( ($action=='supprimer') && $id && $nom )
{
  // Effacer l'enregistrement
  DB_STRUCTURE_ADMINISTRATEUR::DB_supprimer_groupe_par_admin( $id , 'classe' , TRUE /*with_devoir*/ );
  // Log de l'action
  SACocheLog::ajouter('Suppression de la classe "'.$nom.'" (n°'.$id.'), et donc des devoirs associés.');
  // Notifications (rendues visibles ultérieurement)
  $notification_contenu = date('d-m-Y H:i:s').' '.$_SESSION['USER_PRENOM'].' '.$_SESSION['USER_NOM'].' a supprimé la classe "'.$nom.'" (n°'.$id.'), et donc les devoirs associés.'."\r\n";
  DB_STRUCTURE_NOTIFICATION::enregistrer_action_admin( $notification_contenu , $_SESSION['USER_ID'] );
  // Afficher le retour
  echo'<td>ok</td>';
}

else
{
  echo'Erreur avec les données transmises !';
}
?>
