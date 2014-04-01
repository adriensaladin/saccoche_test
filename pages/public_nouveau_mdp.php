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
$TITRE = "Génération d'un nouveau mot de passe";

// Récupération du code
$code_mdp = (isset($_GET['code_mdp'])) ? Clean::texte($_GET['code_mdp']) : '';

if(!$code_mdp)
{
  exit_error( $TITRE /*titre*/ , 'Absence de code transmis dans l\'adresse.' /*contenu*/ );
}

// Vérification de la structure du code
list( $user_pass_key , $BASE ) = explode( 'g' , $code_mdp ) + array_fill(0,2,NULL) ; // Evite des NOTICE en initialisant les valeurs manquantes
$BASE = (int)$BASE;

if( (!$user_pass_key) || ( ($BASE==0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) )
{
  exit_error( $TITRE /*titre*/ , 'Le code transmis est incohérent (format inattendu).' /*contenu*/ );
}

// En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  $result = charger_parametres_mysql_supplementaires($BASE,FALSE);
  if(!$result)
  {
    exit_error( $TITRE /*titre*/ , 'Le code transmis est invalide ou périmé (base inexistante).' /*contenu*/ );
  }
}

// Récupération des données de l'utilisateur
$DB_ROW = DB_STRUCTURE_PUBLIC::DB_recuperer_user_for_new_mdp('user_pass_key',$user_pass_key);

if(empty($DB_ROW))
{
  exit_error( $TITRE /*titre*/ , 'Le code transmis est invalide ou périmé (absence de correspondance).' /*contenu*/ );
}

if( crypter_mdp($DB_ROW['user_id'].$DB_ROW['user_email'].$DB_ROW['user_password'].$DB_ROW['user_connexion_date']) != $user_pass_key )
{
  exit_error( $TITRE /*titre*/ , 'Le code transmis est périmé (incompatible avec les données actuelles).' /*contenu*/ );
}

// Prendre en compte la demande de changement de mdp
$newpass = fabriquer_mdp(); // On ne transmet pas de profil car necessite sinon une variable de session non définie à ce stade.

DB_STRUCTURE_PUBLIC::DB_modifier_user_password_or_key ($DB_ROW['user_id'] , crypter_mdp($newpass) /*user_password*/ , '' /*user_pass_key*/ );

// Affichage du résultat (confirmation + identifiants)
?>
<p><label class="valide">Nouveau mot de passe généré avec succès !</label></p>
<p>Veuillez noter vos identifiants de connexion :</p>
<form>
  <label class="tab">Nom d'utilisateur :</label><b><?php echo html($DB_ROW['user_login']); ?></b><br />
  <label class="tab">Mot de passe :</label><b><?php echo $newpass; ?></b>
</form>
<p><span class="astuce">Le code transmis étant à usage unique, il ne peut pas être utilisé de nouveau.</span></p>
<hr />
<p><span class="tab"></span><a href="./index.php">Retour en page d'accueil de SACoche.</a></p>
