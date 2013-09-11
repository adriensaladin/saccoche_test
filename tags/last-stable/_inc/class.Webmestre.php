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
    // Supprimer les dossiers de fichiers temporaires par établissement
    foreach(FileSystem::$tab_dossier_tmp_structure as $dossier)
    {
      FileSystem::supprimer_dossier($dossier.'0');
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
    // Supprimer les dossiers de fichiers temporaires par établissement
    foreach(FileSystem::$tab_dossier_tmp_structure as $dossier)
    {
      FileSystem::supprimer_dossier($dossier.$BASE);
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
    $texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.','."\r\n\r\n";
    $texte.= 'Je viens de créer une base SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par "'.HEBERGEUR_DENOMINATION.'". Pour accéder au site sans avoir besoin de sélectionner l\'établissement, utiliser le lien suivant :'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n\r\n";
    $texte.= 'Vous êtes maintenant le contact de votre établissement pour cette installation de SACoche.'."\r\n".'Pour modifier l\'identité de la personne référente, il suffit de me communiquer ses coordonnées.'."\r\n\r\n";
    $texte.= 'Un premier compte administrateur a été créé. Pour se connecter comme administrateur, utiliser le lien'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n".'et entrer les identifiants'."\r\n".'nom d\'utilisateur :   '.$admin_login."\r\n".'mot de passe :   '.$admin_password."\r\n\r\n";
    $texte.= 'Ces identifiants sont modifiables depuis l\'espace d\'administration.'."\r\n".'Un administrateur peut déléguer son rôle en créant d\'autres administrateurs.'."\r\n\r\n";
    $texte.= 'Ce logiciel est mis à votre disposition gratuitement, mais sans garantie, conformément à la licence libre GNU GPL3.'."\r\n".'Les administrateurs et les professeurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
    $texte.= 'Merci de consulter la documentation disponible depuis le site du projet :'."\r\n".SERVEUR_PROJET."\r\n\r\n";
    $texte.= 'Vous y trouverez en particulier le guide d\'un administrateur de SACoche :'."\r\n".SERVEUR_GUIDE_ADMIN."\r\n\r\n";
    $texte.= 'Enfin, pour échanger autour de SACoche ou demander des informations complémentaires, vous disposez d\'une liste de discussions (inscription préalable requise) :'."\r\n".SERVEUR_CONTACT."\r\n\r\n";
    $texte.= 'Cordialement,'."\r\n".WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
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
    $texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.','."\r\n\r\n";
    $texte.= 'Je viens de générer un nouveau mot de passe pour '.$admin_prenom.' '.$admin_nom.', administrateur de SACoche pour l\'établissement "'.$denomination.'" sur le site hébergé par "'.HEBERGEUR_DENOMINATION.'".'."\r\n\r\n";
    $texte.= 'Pour se connecter, cet administrateur doit utiliser le lien'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n".'et entrer les identifiants'."\r\n".'nom d\'utilisateur :   '.$admin_login."\r\n".'mot de passe :   '.$admin_password."\r\n\r\n";
    $texte.= 'Ces identifiants sont modifiables depuis l\'espace d\'administration.'."\r\n".'Un administrateur peut déléguer son rôle en créant d\'autres administrateurs.'."\r\n\r\n";
    $texte.= 'Rappel : ce logiciel est mis à votre disposition gratuitement, mais sans garantie, conformément à la licence libre GNU GPL3.'."\r\n".'Les administrateurs et les professeurs sont responsables de toute conséquence d\'une mauvaise manipulation de leur part.'."\r\n\r\n";
    $texte.= 'Merci de consulter la documentation disponible depuis le site du projet :'."\r\n".SERVEUR_PROJET."\r\n\r\n";
    $texte.= 'Vous y trouverez en particulier le guide d\'un administrateur de SACoche :'."\r\n".SERVEUR_GUIDE_ADMIN."\r\n\r\n";
    $texte.= 'Enfin, pour échanger autour de SACoche ou demander des informations complémentaires, vous disposez d\'une liste de discussions (inscription préalable requise) :'."\r\n".SERVEUR_CONTACT."\r\n\r\n";
    $texte.= 'Cordialement,'."\r\n".WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n\r\n";
    return $texte;
  }

  /**
   * Fabriquer le contenu du courriel d'insription envoyé au partenaire ENT conventionné
   * 
   * @param string   $denomination
   * @param string   $nom
   * @param string   $prenom
   * @param string   $password
   * @param string   $url_dir_sacoche
   * @return string
   */
  public static function contenu_courriel_partenaire_ajout($denomination,$nom,$prenom,$password,$url_dir_sacoche)
  {
    $texte = 'A l\'attention de '.$prenom.' '.$nom.','."\r\n\r\n";
    $texte.= 'Dans le cadre de la convention signée, instaurant un partenariat entre "'.$denomination.'" et Sésamath, pour l\'hébergement sur ses serveurs de SACoche avec utilisation d\'un connecteur ENT, vous disposez d\'un compte de gestion.'."\r\n\r\n";
    $texte.= 'Pour s\'y connecter, utiliser le lien'."\r\n".$url_dir_sacoche.'?partenaire'."\r\n".'sélectionner "'.$denomination.'"'."\r\n".'et saisir ce mot de passe :   '.$password."\r\n\r\n";
    $texte.= 'Le mot de passe est modifiable depuis votre espace correspondant.'."\r\n\r\n";
    $texte.= 'Pour modifier l\'identité de la personne référente (nom, prénom ou courriel), il suffit de nous communiquer ses coordonnées.'."\r\n\r\n";
    $texte.= 'Enfin, pour échanger autour de SACoche ou demander des informations complémentaires, vous disposez d\'une liste de discussions (inscription préalable requise) :'."\r\n".SERVEUR_CONTACT."\r\n\r\n";
    $texte.= 'Cordialement,'."\r\n".WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n".'Responsable SACoche pour Sésamath'."\r\n\r\n";
    return $texte;
  }

  /**
   * Fabriquer le contenu du courriel d'insription envoyé au partenaire ENT conventionné
   * 
   * @param string   $denomination
   * @param string   $nom
   * @param string   $prenom
   * @param string   $password
   * @param string   $url_dir_sacoche
   * @return string
   */
  public static function contenu_courriel_partenaire_nouveau_mdp($denomination,$nom,$prenom,$password,$url_dir_sacoche)
  {
    $texte = 'A l\'attention de '.$prenom.' '.$nom.','."\r\n\r\n";
    $texte.= 'Nous venons de réinitialiser le mot de passe du compte de gestion (partenariat entre "'.$denomination.'" et Sésamath, pour l\'hébergement sur ses serveurs de SACoche avec utilisation d\'un connecteur ENT).'."\r\n\r\n";
    $texte.= 'Pour se connecter, utiliser le lien'."\r\n".$url_dir_sacoche.'?partenaire'."\r\n".'sélectionner "'.$denomination.'"'."\r\n".'et saisir ce mot de passe :   '.$password."\r\n\r\n";
    $texte.= 'Le mot de passe est modifiable depuis l\'espace correspondant.'."\r\n\r\n";
    $texte.= 'Pour modifier l\'identité de la personne référente (nom, prénom ou courriel), il suffit de nous communiquer ses coordonnées.'."\r\n\r\n";
    $texte.= 'Enfin, pour échanger autour de SACoche ou demander des informations complémentaires, vous disposez d\'une liste de discussions (inscription préalable requise) :'."\r\n".SERVEUR_CONTACT."\r\n\r\n";
    $texte.= 'Cordialement,'."\r\n".WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n".'Responsable SACoche pour Sésamath'."\r\n\r\n";
    return $texte;
  }

  /**
   * Fabriquer le contenu du courriel avec un nouveau mdp admin envoyé au contact de l'établissement
   * 
   * @param int      $base_id
   * @param string   $denomination
   * @param string   $contact_nom
   * @param string   $contact_prenom
   * @param string   $reference
   * @param string   $motif
   * @param bool     $with_activation
   * @param string   $convention_date_debut
   * @param string   $url_dir_sacoche
   * @return string
   */
  public static function contenu_courriel_convention_reception($base_id,$denomination,$contact_nom,$contact_prenom,$reference,$motif,$with_activation,$convention_date_debut,$url_dir_sacoche)
  {
    $objet = ($motif=='signature') ? 'réception du contrat signé' : 'perception de votre règlement' ;
    $texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.','."\r\n\r\n";
    $texte.= 'Nous accusons bonne '.$objet.' pour la convention du connecteur ENT de référence "'.$reference.'" concernant l\'établissement "'.$denomination.'".'."\r\n\r\n";
    if($with_activation)
    {
      $date_activation = ($convention_date_debut<TODAY_MYSQL) ? 'est activé dès aujourd\'hui ('.TODAY_FR.')' : 'sera activé le '.convert_date_mysql_to_french($convention_date_debut).' (changement d\'année scolaire)' ;
      $objet_autre = ($motif=='paiement') ? 'le contrat signé' : 'votre règlement' ;
      $texte.= 'Le connecteur '.$date_activation.'.'."\r\n".'Vous disposez d\'un mois à compter de cette date pour nous faire parvenir '.$objet_autre.'.'."\r\n".'Passé ce délai, le connecteur ENT est susceptible d\'être coupé.'."\r\n\r\n";
    }
    else
    {
      $date_activation = ($convention_date_debut<TODAY_MYSQL) ? 'est déjà activé depuis le '.convert_date_mysql_to_french($convention_date_debut).' (changement d\'année scolaire)' : 'sera activé le '.convert_date_mysql_to_french($convention_date_debut).' (changement d\'année scolaire)' ;
      $objet_autre = ($motif=='paiement') ? 'le contrat signé' : 'votre règlement' ;
      $texte.= 'Le connecteur '.$date_activation.'.'."\r\n".'Tout est en règle puisque vous nous avez déjà fait parvenir '.$objet_autre.'.'."\r\n\r\n";
    }
    if($motif=='paiement')
    {
      $texte.= 'Votre facture certifiée acquittée est disponible.'."\r\n";
    }
    $texte.= 'Vous avez accès aux documents associés en vous connectant comme administrateur puis en vous rendant dans le menu [Paramétrages établissement] [Mode d\'identification].'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n\r\n";
    $texte.= 'Nous vous remercions de votre confiance et de votre soutien.'."\r\n\r\n";
    $texte.= 'Cordialement,'."\r\n".WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n".'Responsable SACoche pour Sésamath'."\r\n\r\n";
    return $texte;
  }

  /**
   * Fabriquer le contenu du courriel avec un nouveau mdp admin envoyé au contact de l'établissement
   * 
   * @param int      $base_id
   * @param string   $denomination
   * @param string   $contact_nom
   * @param string   $contact_prenom
   * @param string   $reference
   * @param string   $motif
   * @param string   $url_dir_sacoche
   * @return string
   */
  public static function contenu_courriel_convention_coupure($base_id,$denomination,$contact_nom,$contact_prenom,$reference,$motif,$url_dir_sacoche)
  {
    $objet = ($motif=='signature') ? 'reçu le contrat signé' : 'perçu votre règlement' ;
    $texte = 'Bonjour '.$contact_prenom.' '.$contact_nom.','."\r\n\r\n";
    $texte.= 'Nous avons le regret de vous informer que votre connecteur ENT de référence "'.$reference.'" concernant l\'établissement "'.$denomination.'" a été coupé après votre période d\'essai faute d\'avoir '.$objet.'.'."\r\n\r\n";
    $texte.= 'Ce connecteur sera bien évidemment rétabli dès que vous aurez régularisé votre situation.'."\r\n\r\n";
    $texte.= 'Vous avez accès aux documents nécessaires en vous connectant comme administrateur puis en vous rendant dans le menu [Paramétrages établissement] [Mode d\'identification].'."\r\n".$url_dir_sacoche.'?id='.$base_id."\r\n\r\n";
    $texte.= 'N\'hésitez pas à nous contacter en cas de blocage ou si vous avez une question à ce sujet.'."\r\n\r\n";
    $texte.= 'Cordialement,'."\r\n".WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n".'Responsable SACoche pour Sésamath'."\r\n\r\n";
    return $texte;
  }

}
?>