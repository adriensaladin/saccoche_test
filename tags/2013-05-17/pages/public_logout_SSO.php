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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations.
// Cette page est appelée par SACoche et dans ce cas c'est "base" qui est transmis.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

$BASE = 0;
if(HEBERGEUR_INSTALLATION=='multi-structures')
{
  // Lecture d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
  $BASE = (isset($_COOKIE[COOKIE_STRUCTURE])) ? Clean::entier($_COOKIE[COOKIE_STRUCTURE]) : 0 ;
  // Test si id d'établissement transmis dans l'URL ; historiquement "id" si connexion normale et "base" si connexion SSO
  $BASE = (isset($_GET['id']))   ? Clean::entier($_GET['id'])   : $BASE ;
  $BASE = (isset($_GET['base'])) ? Clean::entier($_GET['base']) : $BASE ;
  // Test si UAI d'établissement transmis dans l'URL
  $BASE = (isset($_GET['uai'])) ? DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_id_base_for_UAI(Clean::uai($_GET['uai'])) : $BASE ;
  if(!$BASE)
  {
    if(isset($_GET['uai']))
    {
      exit_error( 'Paramètre incorrect' /*titre*/ , 'Le numéro UAI transmis n\'est pas référencé sur cette installation de SACoche : vérifiez son exactitude et si cet établissement est bien inscrit sur ce serveur.' /*contenu*/ );
    }
    else
    {
      exit_error( 'Donnée manquante' /*titre*/ , 'Référence de base manquante (le paramètre "base" ou "id" n\'a pas été transmis en GET ou n\'est pas un entier et n\'a pas non plus été trouvé dans un Cookie).' /*contenu*/ );
    }
  }
  charger_parametres_mysql_supplementaires($BASE);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Connexion à la base pour charger les paramètres du SSO demandé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Mettre à jour la base si nécessaire
maj_base_structure_si_besoin($BASE);

$DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"connexion_mode","cas_serveur_host","cas_serveur_port","cas_serveur_root","cas_serveur_url_login","cas_serveur_url_logout","cas_serveur_url_validate","gepi_url","gepi_rne","gepi_certificat_empreinte"'); // A compléter
foreach($DB_TAB as $DB_ROW)
{
  ${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
}
if($connexion_mode=='normal')
{
  exit_error( 'Configuration manquante' /*titre*/ , 'Etablissement non paramétré par l\'administrateur pour utiliser un service d\'authentification externe.<br />Un administrateur doit renseigner cette configuration dans le menu [Paramétrages][Mode&nbsp;d\'identification].' /*contenu*/ );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Déconnexion avec le protocole CAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($connexion_mode=='cas')
{
  // Pour tester, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
  if (DEBUG_PHPCAS)
  {
    $fichier_nom_debut = 'debugcas_'.$BASE;
    $fichier_nom_fin   = fabriquer_fin_nom_fichier__pseudo_alea($fichier_nom_debut);
    phpCAS::setDebug(CHEMIN_LOGS_PHPCAS.$fichier_nom_debut.'_'.$fichier_nom_fin.'.txt');
  }
  // Initialiser la connexion avec CAS  ; le premier argument est la version du protocole CAS ; le dernier argument indique qu'on utilise la session existante
  phpCAS::client(CAS_VERSION_2_0, $cas_serveur_host, (int)$cas_serveur_port, $cas_serveur_root, FALSE);
  phpCAS::setLang(PHPCAS_LANG_FRENCH);
  // On indique qu'il n'y a pas de validation du certificat SSL à faire
  phpCAS::setNoCasServerValidation();
  // Surcharge éventuelle des URL
  if ($cas_serveur_url_login)    { phpCAS::setServerLoginURL($cas_serveur_url_login); }
  if ($cas_serveur_url_logout)   { phpCAS::setServerLogoutURL($cas_serveur_url_logout); }
  if ($cas_serveur_url_validate) { phpCAS::setServerServiceValidateURL($cas_serveur_url_validate); }
  // Gestion du single sign-out
  phpCAS::handleLogoutRequests(FALSE);
  // Appliquer un proxy si défini par le webmestre ; voir url_get_contents() pour les commentaires.
  if( (defined('SERVEUR_PROXY_USED')) && (SERVEUR_PROXY_USED) )
  {
    phpCAS::setExtraCurlOption(CURLOPT_PROXY     , SERVEUR_PROXY_NAME);
    phpCAS::setExtraCurlOption(CURLOPT_PROXYPORT , (int)SERVEUR_PROXY_PORT);
    phpCAS::setExtraCurlOption(CURLOPT_PROXYTYPE , constant(SERVEUR_PROXY_TYPE));
    if(SERVEUR_PROXY_AUTH_USED)
    {
      phpCAS::setExtraCurlOption(CURLOPT_PROXYAUTH    , constant(SERVEUR_PROXY_AUTH_METHOD));
      phpCAS::setExtraCurlOption(CURLOPT_PROXYUSERPWD , SERVEUR_PROXY_AUTH_USER.':'.SERVEUR_PROXY_AUTH_PASS);
    }
  }
  // Déconnexion de CAS
  phpCAS::logout();
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Déconnexion avec Shibboleth
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($connexion_mode=='shibboleth')
{
  /*
  Pour le moment, on a acté avec le Catice qu'une déconnexion depuis une application entrainera seulement une déconnexion de cette application.
  Seule une déconnexion depuis Argos lancera le SLO (single sign out).
  Juste pour info, nous faisons le SLO par un appel à un url dont le path est /Shibboleth.sso/Logout
  Donc on ne rentre pas dans l'application (argos), c'est Shibboleth qui reçoit cette requête.
  Il envoie un message xml/soap (contenant l'ID Shibboleth) à une opération soap d'argos (implémentée pour l'occasion),
  qui déclenche la suppression du fichier de session php,
  puis envoie une réponse xml à shibboleth,
  qui ensuite demande à l'IdP de tuer la session en cours.
  */
  // Redirection mise en dure ici pour l'instant, tant que ça ne concerne que Bordeaux...
  // Remarque : le code 307 peut causer des soucis ; le code 302 semble mieux. http://fr.wikipedia.org/wiki/Liste_des_codes_HTTP
  header('Status: 302 Found', TRUE, 302);
  header('Location: https://ent2d.ac-bordeaux.fr/Shibboleth.sso/Logout');
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Déconnexion de GEPI avec le protocole SAML
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($connexion_mode=='gepi')
{
  // Mise en session d'informations dont SimpleSAMLphp a besoin ; utiliser des constantes ne va pas car Gepi fait un appel à SimpleSAMLphp en court-circuitant SACoche pour vérifier la légitimité de l'appel.
  $_SESSION['SACoche-SimpleSAMLphp'] = array(
    'GEPI_URL'                  => $gepi_url,
    'GEPI_RNE'                  => $gepi_rne,
    'GEPI_CERTIFICAT_EMPREINTE' => $gepi_certificat_empreinte,
    'SIMPLESAMLPHP_BASEURLPATH' => substr($_SERVER['SCRIPT_NAME'],1,-9).'_lib/SimpleSAMLphp/www/',
    'WEBMESTRE_NOM'             => WEBMESTRE_NOM,
    'WEBMESTRE_PRENOM'          => WEBMESTRE_PRENOM,
    'WEBMESTRE_COURRIEL'        => WEBMESTRE_COURRIEL
  );
  // Initialiser la classe
  $auth = new SimpleSAML_Auth_Simple('distant-gepi-saml');
  // Déconnexion de GEPI
  if ($auth->isAuthenticated())
  {
    $auth->logout();
    exit();
  }
  elseif(isset($_SESSION['SimpleSAMLphp_SESSION']))
  {
    // On revient très probablement de la déconnexion de GEPI (en effet, au contraire de CAS, la page de déconnexion distante renvoie vers l'application au lieu de marquer un arrêt).
    unset($_SESSION['SimpleSAMLphp_SESSION']);
    exit_error( 'Deconnexion de Gepi' /*titre*/ , 'Déconnexion du service d\'authentification Gepi effectuée.<br />Fermez votre navigateur par sécurité.' /*contenu*/ );
  }
  else
  {
    // Bizarre... a priori on n'était pas connecté à GEPI... appel direct ?
    exit_error( 'Deconnexion de Gepi' /*titre*/ , 'Votre authentification sur Gepi n\'a pas été retrouvée.<br />Fermez votre navigateur par sécurité pour être certain d\'en être déconnecté.' /*contenu*/ );
  }
}

?>
