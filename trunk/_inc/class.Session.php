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


/**
 * Classe pour gérer les sessions.
 * La session est transmise via le cookie "$_COOKIE[SESSION_NOM]".
 */
class Session
{

  // //////////////////////////////////////////////////
  // Attributs
  // //////////////////////////////////////////////////

  public  static $_sso_redirect = FALSE;

  // //////////////////////////////////////////////////
  // Méthodes privées (internes)
  // //////////////////////////////////////////////////

  /**
   * Paramétrage de la session appelé avant son ouverture.
   * Y a pas de constructeur statique en PHP...
   *
   * @param void
   * @return void
   */
  private static function param()
  {
    session_name(SESSION_NOM);
    session_cache_limiter('nocache');
  }

  /*
   * Ouvrir une session existante
   * 
   * @param void
   * @return bool
   */
  private static function open_old()
  {
    Session::param();
    $ID = $_COOKIE[SESSION_NOM];
    session_id($ID);
    return session_start();
  }

  /*
   * Renvoyer l'IP
   * 
   * @param void
   * @return string
   */
	private static function get_IP()
	{
		/**
		 * Si PHP est derrière un reverse proxy ou un load balancer, REMOTE_ADDR peut contenir l'ip du proxy.
		 * Normalement, le proxy doit renseigner HTTP_X_REAL_IP, ou HTTP_X_FORWARDED_FOR.
		 * Au CITIC, REMOTE_ADDR est correct, et chez OVH aussi (avec apache derrière nginx ou pas).
		 */
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_X_REAL_IP']))
		{
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'];
			// on s'arrête à la 1re virgule si on en trouve une
			$tab_forwarded = preg_split("/,| /",$forwarded);
			$ip_candidate = $tab_forwarded[0];
			$ip_composantes = explode('.', $ip_candidate);
			// On vérifie que ça ressemble à une ip (au cas où on aurait une chaîne bizarre ou vide on garde notre REMOTE_ADDR)
			if (count($ip_composantes) == 4 && min($ip_composantes) >=0 && max($ip_composantes) < 256 && max($ip_composantes) > 0)
			{
				$ip = $ip_candidate;
			}
		}
		// petit truc pour s'assurer d'avoir une IP valide
		return ($ip == long2ip(ip2long($ip))) ? $ip : '' ;
	}

  /*
   * Renvoyer une clef associée au navigateur et à la session en cours
   * 
   * @param void
   * @return void
   */
	private static function get_UserAgent()
	{
		if(isset($_SERVER['HTTP_USER_AGENT']))
		{
			// Eviter que l'activation de FireBug + FirePHP n'oblige à se réidentifier.
			$pos_FirePHP = strpos($_SERVER['HTTP_USER_AGENT'],' FirePHP');
			$user_agent = ($pos_FirePHP===FALSE) ? $_SERVER['HTTP_USER_AGENT'] : substr($_SERVER['HTTP_USER_AGENT'],0,$pos_FirePHP) ;
		}
		else
		{
			$user_agent = '';
		}
		return $user_agent;
	}

  /*
   * Renvoyer une clef associée au navigateur, à l'adresse IP et à la session en cours
   * 
   * @param void
   * @return void
   */
	private static function session_key()
	{
		return md5( Session::get_IP() . Session::get_UserAgent() . session_id() );
	}

  /*
   * Initialiser une session ouverte
   * 
   * @param void
   * @return void
   */
  private static function init()
  {
    $_SESSION = array();
    // Clef pour éviter les vols de session
    $_SESSION['SESSION_KEY']      = Session::session_key();
    // Numéro de la base
    $_SESSION['BASE']             = 0;
    // Données associées à l'utilisateur.
    $_SESSION['USER_PROFIL']      = 'public';  // public / webmestre / administrateur / directeur / professeur / eleve
    $_SESSION['USER_ID']          = 0;
    $_SESSION['USER_NOM']         = '-';
    $_SESSION['USER_PRENOM']      = '-';
    // Données associées à l'établissement.
    $_SESSION['SESAMATH_ID']      = 0;
    $_SESSION['CONNEXION_MODE']   = 'normal';
    $_SESSION['DUREE_INACTIVITE'] = 30;
  }

  /*
   * Rediriger vers l'authentification SSO si détecté, ou afficher une page HTML avec un message explicatif et un lien pour retourner en page d'accueil (si AJAX, renvoyer juste un message).
   * 
   * @param void
   * @return void | exit !
   */
  private static function exit_sauf_SSO()
  {
    $test_get = ( (isset($_GET['sso'])) && ( (isset($_GET['base'])) || (isset($_GET['id'])) || (isset($_GET['uai'])) || (HEBERGEUR_INSTALLATION=='mono-structure') ) ) ? TRUE : FALSE ;
    $test_cookie = ( ( (isset($_COOKIE[COOKIE_STRUCTURE])) || (HEBERGEUR_INSTALLATION=='mono-structure') ) && (isset($_COOKIE[COOKIE_AUTHMODE])) && ($_COOKIE[COOKIE_AUTHMODE]!='normal') ) ? TRUE : FALSE ;
    // si html
    if(SACoche=='index')
    {
      if( $test_get || $test_cookie )
      {
        // La redirection SSO se fera plus tard, une fois les paramètres MySQL chargés, le test de blocage de l'accès effectué, etc.
        Session::$_sso_redirect = TRUE;
      }
      else
      {
        exit_error( 'Authentification manquante' /*titre*/ , 'Session perdue, expirée, incompatible ou non enregistrée (inactivité, disque plein, chemin invalide, &hellip;).<br />Veuillez vous (re)-connecter.' /*contenu*/ );
      }
    }
    // si ajax
    else
    {
      echo ( $test_get || $test_cookie ) ? 'Session perdue / expirée / incompatible. Veuillez actualiser la page.' : 'Session perdue / expirée / incompatible. Veuillez vous reconnecter.' ;
      exit();
    }
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /*
   * Ouvrir une nouvelle session
   * Appelé une fois depuis /webservices/argos_parent.php
   * 
   * @param void
   * @return bool
   */
  public static function open_new()
  {
    Session::param();
    // Utiliser l'option préfixe ou entropie de uniqid() insère un '.'
    // qui peut provoquer une erreur disant que les seuls caractères autorisés sont a-z, A-Z, 0-9 et -
    $ID = uniqid().md5('grain_de_sable'.mt_rand());
    session_id($ID);
    return session_start();
  }

  /*
   * Fermer une session existante
   * Appelé une fois depuis /webservices/argos_parent.php et une fois depuis ajax.php
   * 
   * @param void
   * @return void
   */
  public static function close()
  {
    // Pas besoin de session_start() car la session a déjà été ouverte avant appel à cette fonction.
    $_SESSION = array();
    session_unset();
    setcookie(session_name(),'',time()-42000,'');
    session_destroy();
  }

  /*
   * Rechercher une session existante et gérer les différents cas possibles.
   * 
   * @param array $TAB_PROFILS_AUTORISES
   * @return void | exit ! (sur une string si ajax, une page html, ou modification $PAGE pour process SSO)
   */
  public static function execute($TAB_PROFILS_AUTORISES)
  {
    if(!isset($_COOKIE[SESSION_NOM]))
    {
      // 1. Aucune session transmise
      Session::open_new(); Session::init();
      if(!$TAB_PROFILS_AUTORISES['public'])
      {
        // 1.1. Demande d'accès à une page réservée, donc besoin d'identification
        if(isset($_GET['verif_cookie']))
        {
          // 1.1.1. En fait l'utilisateur vient déjà de s'identifier : c'est donc anormal, le cookie de session n'a pas été trouvé car le navigateur client n'enregistre pas les cookies
          exit_error( 'Problème de cookies' /*titre*/ , 'Session non retrouvée !<br />Configurez votre navigateur pour qu\'il accepte les cookies.' /*contenu*/ );
        }
        else
        {
          // 1.1.2. Session perdue ou expirée, ou demande d'accès direct (lien profond) : redirection pour une nouvelle identification
          Session::exit_sauf_SSO(); // Si SSO au prochain coup on ne passera plus par là.
        }
      }
      else
      {
        // 1.2 Accès à une page publique : RAS
      }
    }
    else
    {
      // 2. id de session transmis
      Session::open_old();
      if(!isset($_SESSION['USER_PROFIL']))
      {
        // 2.1. Pas de session retrouvée (sinon cette variable serait renseignée)
        if(!$TAB_PROFILS_AUTORISES['public'])
        {
          // 2.1.1. Session perdue ou expirée et demande d'accès à une page réservée : redirection pour une nouvelle identification
          Session::close(); Session::open_new(); Session::init();
          Session::exit_sauf_SSO(); // On peut initialiser la session avant car si SSO au prochain coup on ne passera plus par là.
        }
        else
        {
          // 2.1.2. Session perdue ou expirée et page publique : création d'une nouvelle session, pas de message d'alerte pour indiquer que la session perdue
          Session::close();Session::open_new();Session::init();
        }
      }
      elseif($_SESSION['SESSION_KEY'] != Session::session_key())
      {
        // 2.2. Session retrouvée, mais louche car IP ou navigateur modifié (tentative de piratage ? c'est cependant difficile de récupérer le cookie d'un tiers, voire impossible avec les autres protections dont SACoche bénéficie).
        Session::close();
        exit_error( 'Session incompatible avec votre connexion' /*titre*/ , 'Modification d\'adresse IP ou de navigateur a été détectée !<br />Par sécurité, vous devez vous reconnecter.' /*contenu*/ );
      }
      elseif($_SESSION['USER_PROFIL'] == 'public')
      {
        // 2.3. Session retrouvée, utilisateur non identifié
        if(!$TAB_PROFILS_AUTORISES['public'])
        {
          // 2.3.1. Espace non identifié => Espace identifié : redirection pour identification
          Session::exit_sauf_SSO(); // Pas d'initialisation de session sinon la redirection avec le SSO tourne en boucle.
        }
        else
        {
          // 2.3.2. Espace non identifié => Espace non identifié : RAS
        }
      }
      else
      {
        // 2.4. Session retrouvée, utilisateur identifié
        if($TAB_PROFILS_AUTORISES[$_SESSION['USER_PROFIL']])
        {
          // 2.4.1. Espace identifié => Espace identifié identique : RAS
        }
        elseif($TAB_PROFILS_AUTORISES['public'])
        {
          // 2.4.2. Espace identifié => Espace non identifié : création d'une nouvelle session vierge, pas de message d'alerte pour indiquer que la session perdue
          // A un moment il fallait tester que ce n'était pas un appel ajax,pour éviter une déconnexion si appel au calendrier qui était dans l'espace public, mais ce n'est plus le cas...
          // Par contre il faut conserver la session de SimpleSAMLphp pour laisser à l'utilisateur la choix de se déconnecter ou non de son SSO.
          $SimpleSAMLphp_SESSION = ( ($_SESSION['CONNEXION_MODE']=='gepi') && (isset($_SESSION['SimpleSAMLphp_SESSION'])) ) ? $_SESSION['SimpleSAMLphp_SESSION'] : FALSE ; // isset() pour le cas où l'admin vient de cocher le mode Gepi mais c'est connecté sans juste avant
          Session::close();Session::open_new();Session::init();
          if($SimpleSAMLphp_SESSION) { $_SESSION['SimpleSAMLphp_SESSION'] = $SimpleSAMLphp_SESSION; }
        }
        elseif(!$TAB_PROFILS_AUTORISES['public']) // (forcément)
        {
          // 2.4.3. Espace identifié => Autre espace identifié incompatible : redirection pour une nouvelle identification
          // Pas de redirection SSO sinon on tourne en boucle (il faudrait faire une déconnexion SSO préalable).
          exit_error( 'Page interdite avec votre profil' /*titre*/ , 'Vous avez appelé une page inaccessible avec votre identification actuelle !<br />Déconnectez-vous ou retournez à la page précédente.' /*contenu*/ );
        }
      }
    }
  }

}
?>