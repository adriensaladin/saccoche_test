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

class Webmestre
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Ajouter une structure (mode multi-structures)
   *
   * @param int    $base_id   Pour forcer l'id de la base de la structure ; normalement transmis à 0 (=> auto-increment), sauf dans un cadre de gestion interne à Sésamath
   * @param int    $geo_id
   * @param string $structure_uai
   * @param string $localisation
   * @param string $denomination
   * @param string $contact_nom
   * @param string $contact_prenom
   * @param string $contact_courriel
   * @param string $inscription_date   Pour forcer la date d'inscription, par exemple en cas de transfert de bases académiques (facultatif).
   * @return int
   */
  public static function ajouter_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel,$inscription_date=0)
  {
    // Insérer l'enregistrement d'une nouvelle structure dans la base du webmestre
    $base_id = DB_WEBMESTRE_WEBMESTRE::DB_ajouter_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel,$inscription_date);
    // Génération des paramètres de connexion à la base de données
    $BD_name = 'sac_base_'.$base_id; // Limité à 64 caractères (tranquille...)
    $BD_user = 'sac_user_'.$base_id; // Limité à 16 caractères (attention !)
    $BD_pass = fabriquer_mdp();
    // Créer le fichier de connexion de la base de données de la structure
    FileSystem::fabriquer_fichier_connexion_base($base_id,SACOCHE_WEBMESTRE_BD_HOST,SACOCHE_WEBMESTRE_BD_PORT,$BD_name,$BD_user,$BD_pass);
    // Créer la base de données d'une structure, un utilisateur MySQL, et lui attribuer ses droits.
    DB_WEBMESTRE_WEBMESTRE::DB_ajouter_base_structure_et_user_mysql($base_id,$BD_name,$BD_user,$BD_pass);
    /* Il reste à :
      + Lancer les requêtes pour installer et remplir les tables, éventuellement personnaliser certains paramètres de la structure
      + Insérer le compte administrateur dans la base de cette structure, éventuellement lui envoyer un courriel
      + Créer un dossier pour les les vignettes images
    */
    return $base_id;
  }

  /**
   * Supprimer une structure (mode mono-structures)
   *
   * @param void
   * @return void
   */
  public static function supprimer_mono_structure()
  {
    // Supprimer les tables de la base
    DB_STRUCTURE_WEBMESTRE::DB_supprimer_tables_structure();
    // Supprimer le fichier de connexion
    unlink(CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_structure.php');
    // Supprimer les dossiers de fichiers temporaires par établissement : vignettes verticales, flux RSS des demandes, cookies des choix de formulaires, sujets et corrigés de devoirs
    $tab_sous_dossier = array('badge','cookie','devoir','officiel','rss');
    foreach($tab_sous_dossier as $sous_dossier)
    {
      FileSystem::supprimer_dossier(CHEMIN_DOSSIER_TMP.$sous_dossier.DS.'0');
    }
    // Supprimer les éventuels fichiers de blocage
    LockAcces::supprimer_fichiers_blocage(0);
    // Log de l'action
    SACocheLog::ajouter('Résiliation de l\'inscription.');
  }

  /**
   * Supprimer une structure (mode multi-structures)
   *
   * @param int    $BASE 
   * @return void
   */
  public static function supprimer_multi_structure($BASE)
  {
    // Paramètres de connexion à la base de données
    $BD_name = 'sac_base_'.$BASE;
    $BD_user = 'sac_user_'.$BASE; // Limité à 16 caractères
    // Supprimer la base de données d'une structure, et son utilisateur MySQL une fois défait de ses droits.
    DB_WEBMESTRE_WEBMESTRE::DB_supprimer_base_structure_et_user_mysql($BD_name,$BD_user);
    // Supprimer le fichier de connexion
    unlink(CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php');
    // Retirer l'enregistrement d'une structure dans la base du webmestre
    DB_WEBMESTRE_WEBMESTRE::DB_supprimer_structure($BASE);
    // Supprimer les dossiers de fichiers temporaires par établissement : vignettes verticales, flux RSS des demandes, cookies des choix de formulaires, sujets et corrigés de devoirs
    $tab_sous_dossier = array('badge','cookie','devoir','officiel','rss');
    foreach($tab_sous_dossier as $sous_dossier)
    {
      FileSystem::supprimer_dossier(CHEMIN_DOSSIER_TMP.$sous_dossier.DS.$BASE);
    }
    // Supprimer les éventuels fichiers de blocage
    LockAcces::supprimer_fichiers_blocage($BASE);
    // Log de l'action
    SACocheLog::ajouter('Suppression de la structure n°'.$BASE.'.');
  }

  /**
   * Enregister le (nouveau) mot de passe du webmestre.
   * 
   * @param string $password_ancien
   * @param string $password_nouveau
   * @return string   'ok' | 'Le mot de passe actuel est incorrect !'
   */
  public static function modifier_mdp_webmestre($password_ancien,$password_nouveau)
  {
    // Tester si l'ancien mot de passe correspond à celui enregistré
    $password_ancien_crypte = crypter_mdp($password_ancien);
    if($password_ancien_crypte!=WEBMESTRE_PASSWORD_MD5)
    {
      return 'Le mot de passe actuel est incorrect !';
    }
    // Remplacer par le nouveau mot de passe
    $password_nouveau_crypte = crypter_mdp($password_nouveau);
    FileSystem::fabriquer_fichier_hebergeur_info( array('WEBMESTRE_PASSWORD_MD5'=>$password_nouveau_crypte) );
    return 'ok';
  }

  /**
   * Fabriquer le contenu du courriel d'insription envoyé au contact de l'établissement et 1er admin
   * 
   * @param int      $base_id
   * @param string   $denomination
   * @param string   $contact_nom
   * @param string   $contact_prenom
   * @param string   $admin_login
   * @param string   $admin_password
   * @param string   $url_dir_sacoche
   * @return string
   */
  public static function contenu_courriel_inscription($base_id,$denomination,$contact_nom,$contact_prenom,$admin_login,$admin_password,$url_dir_sacoche)
  {
    $texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
    $texte.= 'Je viens de créer une base SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par "'.HEBERGEUR_DENOMINATION.'". Pour accéder au site sans avoir besoin de sélectionner votre établissement, utilisez le lien suivant :'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n\r\n";
    $texte.= 'Vous êtes maintenant le contact de votre établissement pour cette installation de SACoche.'."\r\n".'Pour modifier l\'identité de la personne référente, il suffit de me communiquer ses coordonnées.'."\r\n\r\n";
    $texte.= 'Un premier compte administrateur a été créé. Pour se connecter comme administrateur, utiliser le lien'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n".'et entrer les identifiants'."\r\n".'nom d\'utilisateur :   '.$admin_login."\r\n".'mot de passe :   '.$admin_password."\r\n\r\n";
    $texte.= 'Ces identifiants sont modifiables depuis l\'espace d\'administration.'."\r\n".'Un administrateur peut déléguer son rôle en créant d\'autres administrateurs.'."\r\n\r\n";
    $texte.= 'Ce logiciel est mis à votre disposition gratuitement, mais sans garantie, conformément à la licence libre GNU GPL3.'."\r\n".'Les administrateurs et les professeurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
    $texte.= 'Merci de consulter la documentation disponible depuis le site du projet :'."\r\n".SERVEUR_PROJET."\r\n\r\n";
    $texte.= 'Vous y trouverez en particulier le guide d\'un administrateur de SACoche :'."\r\n".SERVEUR_GUIDE_ADMIN."\r\n\r\n";
    $texte.= 'Enfin, pour échanger autour de SACoche ou demander des informations complémentaires, vous disposez d\'une liste de discussions :'."\r\n".SERVEUR_CONTACT."\r\n\r\n";
    $texte.= 'Cordialement'."\r\n";
    $texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
    return $texte;
  }

  /**
   * Fabriquer le contenu du courriel avec un nouveau mdp admin envoyé au contact de l'établissement
   * 
   * @param int      $base_id
   * @param string   $denomination
   * @param string   $contact_nom
   * @param string   $contact_prenom
   * @param string   $admin_nom
   * @param string   $admin_prenom
   * @param string   $admin_login
   * @param string   $admin_password
   * @param string   $url_dir_sacoche
   * @return string
   */
  public static function contenu_courriel_nouveau_mdp($base_id,$denomination,$contact_nom,$contact_prenom,$admin_nom,$admin_prenom,$admin_login,$admin_password,$url_dir_sacoche)
  {
    $texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.'.'."\r\n\r\n";
    $texte.= 'Je viens de réinitialiser le mot de passe de '.$admin_prenom.' '.$admin_nom.', administrateur de SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par "'.HEBERGEUR_DENOMINATION.'".'."\r\n\r\n";
    $texte.= 'Pour se connecter, cet administrateur doit utiliser le lien'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n".'et entrer les identifiants'."\r\n".'nom d\'utilisateur :   '.$admin_login."\r\n".'mot de passe :   '.$admin_password."\r\n\r\n";
    $texte.= 'Ces identifiants sont modifiables depuis l\'espace d\'administration.'."\r\n".'Un administrateur peut déléguer son rôle en créant d\'autres administrateurs.'."\r\n\r\n";
    $texte.= 'Rappel : ce logiciel est mis à votre disposition gratuitement, mais sans garantie, conformément à la licence libre GNU GPL3.'."\r\n".'Les administrateurs et les professeurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
    $texte.= 'Merci de consulter la documentation disponible depuis le site du projet :'."\r\n".SERVEUR_PROJET."\r\n\r\n";
    $texte.= 'Vous y trouverez en particulier le guide d\'un administrateur de SACoche :'."\r\n".SERVEUR_GUIDE_ADMIN."\r\n\r\n";
    $texte.= 'Enfin, pour échanger autour de SACoche ou demander des informations complémentaires, vous disposez d\'une liste de discussions :'."\r\n".SERVEUR_CONTACT."\r\n\r\n";
    $texte.= 'Cordialement'."\r\n";
    $texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
    return $texte;
  }

}
?>