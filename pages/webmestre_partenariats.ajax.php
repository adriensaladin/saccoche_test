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

$action        = (isset($_POST['f_action']))       ? Clean::texte($_POST['f_action'])       : '';
$partenaire_id = (isset($_POST['f_id']))           ? Clean::entier($_POST['f_id'])          : 0;
$denomination  = (isset($_POST['f_denomination'])) ? Clean::texte($_POST['f_denomination']) : '';
$nom           = (isset($_POST['f_nom']))          ? Clean::nom($_POST['f_nom'])            : '';
$prenom        = (isset($_POST['f_prenom']))       ? Clean::prenom($_POST['f_prenom'])      : '';
$courriel      = (isset($_POST['f_courriel']))     ? Clean::courriel($_POST['f_courriel'])  : '';
$connecteurs   = (isset($_POST['f_connecteurs']))  ? Clean::texte($_POST['f_connecteurs'])  : '';

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter un nouveau partenaire conventionné
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='ajouter') && $denomination && $nom && $prenom && $courriel && $connecteurs )
{
  // Vérifier le domaine du serveur mail (hébergement Sésamath donc serveur ouvert sur l'extérieur).
  $mail_domaine = tester_domaine_courriel_valide($courriel);
  if($mail_domaine!==TRUE)
  {
    exit('Erreur avec le domaine "'.$mail_domaine.'" !');
  }
  // Verifier que la liste des connecteurs commence et se termine par une virgule (corriger sinon)
  $connecteurs = (mb_substr($connecteurs,0,1)==',') ? $connecteurs : ','.$connecteurs ;
  $connecteurs = (mb_substr($connecteurs,-1) ==',') ? $connecteurs : $connecteurs.',' ;
  // Générer un mdp aléatoire
  $password = fabriquer_mdp();
  // Insérer l'enregistrement
  $partenaire_id = DB_WEBMESTRE_WEBMESTRE::DB_ajouter_partenaire_conventionne( $denomination , $nom , $prenom , $courriel , crypter_mdp($password) , $connecteurs );
  // Envoyer un courriel
  $texte = Webmestre::contenu_courriel_partenaire_ajout( $denomination , $nom , $prenom , $password , URL_DIR_SACOCHE );
  $courriel_bilan = Sesamail::mail( $courriel , 'Création compte partenaire ENT' , $texte );
  if(!$courriel_bilan)
  {
    exit('Erreur lors de l\'envoi du courriel !');
  }
  // Afficher le retour
  echo'<tr id="id_'.$partenaire_id.'" class="new">';
  echo  '<td>'.$partenaire_id.'</td>';
  echo  '<td>'.html($denomination).'</td>';
  echo  '<td>'.html($nom).'</td>';
  echo  '<td>'.html($prenom).'</td>';
  echo  '<td>'.html($courriel).'</td>';
  echo  '<td>'.html($connecteurs).'</td>';
  echo  '<td class="nu">';
  echo    '<q class="modifier" title="Modifier ce partenaire."></q>';
  echo    '<q class="initialiser_mdp" title="Générer un nouveau mdp pour ce partenaire."></q>';
  echo    '<q class="supprimer" title="Retirer ce partenaire."></q>';
  echo  '</td>';
  echo'</tr>';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Modifier un partenaire conventionné existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='modifier') && $partenaire_id && $denomination && $nom && $prenom && $courriel && $connecteurs )
{
  // Vérifier le domaine du serveur mail (hébergement Sésamath donc serveur ouvert sur l'extérieur).
  $mail_domaine = tester_domaine_courriel_valide($courriel);
  if($mail_domaine!==TRUE)
  {
    exit('Erreur avec le domaine "'.$mail_domaine.'" !');
  }
  // Verifier que la liste des connecteurs commence et se termine par une virgule (corriger sinon)
  $connecteurs = (mb_substr($connecteurs,0,1)==',') ? $connecteurs : ','.$connecteurs ;
  $connecteurs = (mb_substr($connecteurs,-1) ==',') ? $connecteurs : $connecteurs.',' ;
  // Mettre à jour l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_modifier_partenaire_conventionne( $partenaire_id , $denomination , $nom , $prenom , $courriel , $connecteurs );
  // Afficher le retour
  echo'<td>'.$partenaire_id.'</td>';
  echo'<td>'.html($denomination).'</td>';
  echo'<td>'.html($nom).'</td>';
  echo'<td>'.html($prenom).'</td>';
  echo'<td>'.html($courriel).'</td>';
  echo'<td>'.html($connecteurs).'</td>';
  echo'<td class="nu">';
  echo  '<q class="modifier" title="Modifier ce partenaire."></q>';
  echo  '<q class="initialiser_mdp" title="Générer un nouveau mdp pour ce partenaire."></q>';
  echo  '<q class="supprimer" title="Retirer ce partenaire."></q>';
  echo'</td>';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Générer un nouveau mdp d'un partenaire conventionné et lui envoyer par courriel
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='initialiser_mdp') && $partenaire_id && $denomination && $nom && $prenom && $courriel )
{
  // Générer un nouveau mdp
  $password = fabriquer_mdp();
  // Mettre à jour l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_modifier_partenaire_conventionne_mdp($partenaire_id,crypter_mdp($password));
  // Envoyer un courriel
  $courriel_contenu = Webmestre::contenu_courriel_partenaire_nouveau_mdp( $denomination , $nom , $prenom , $password , URL_DIR_SACOCHE );
  $courriel_bilan = Sesamail::mail( $courriel , 'Modification mdp compte partenaire ENT' , $courriel_contenu );
  if(!$courriel_bilan)
  {
    exit('Erreur lors de l\'envoi du courriel !');
  }
  // On affiche le retour
  echo'<ok>';
  echo'Le mot de passe de<BR />'.html($prenom.' '.$nom).',<BR />partenaire conventionné<BR />"'.html($denomination).'",<BR />vient d\'être réinitialisé.<BR /><BR />';
  echo'Les nouveaux identifiants<BR />ont été envoyés<BR />à son adresse de courriel<BR />'.html($courriel).'.';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Retirer un partenaire conventionné existant
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='supprimer') && $partenaire_id )
{
  // Supprimer l'enregistrement
  DB_WEBMESTRE_WEBMESTRE::DB_supprimer_partenaire_conventionne($partenaire_id);
  // Afficher le retour
  exit('<td>ok</td>');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là !
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
