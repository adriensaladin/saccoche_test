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
 * Classe pour gérer les sessions et les jetons anti-CSRF.
 * La session est transmise via le cookie "$_COOKIE[SESSION_NOM]".
 */
class Session
{

  // //////////////////////////////////////////////////
  // Attributs
  // //////////////////////////////////////////////////

  public static  $_sso_redirect      = FALSE;
  private static $tab_droits_page    = array();
  public static $tab_message_erreur  = array();
  public static  $_CSRF_value        = '';

  // //////////////////////////////////////////////////
  // Méthodes publiques - Outils
  // //////////////////////////////////////////////////

  /*
   * Renvoyer l'IP
   * 
   * @param void
   * @return string
   */
  public static function get_IP()
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
      if ( (count($ip_composantes)==4) && (min($ip_composantes)>=0) && (max($ip_composantes)<256) && (max($ip_composantes)>0) )
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
   * @return string
   */
  public static function get_UserAgent()
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

  // //////////////////////////////////////////////////
  // Méthodes privées (internes) - Gestion de la session
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
    // Header à envoyer avant toute génération de cookie pour mettre en place une stratégie de confidentialité compacte.
    // La plateforme pour les préférences de confidentialité est un standart W3C nommé en anglais "Platform for Privacy Preferences" d'où "P3P".
    // Permet d'éviter des soucis avec IE si son paramétrage de confidentialité des cookies est en position haute.
    // @see http://www.webmaster-hub.com/topic/3754-cookies-et-strategie-de-confidentialite/
    // @see http://www.yoyodesign.org/doc/w3c/p3p1/#compact_policies
    // @see http://www.yoyodesign.org/doc/w3c/p3p1/#ref_file_policyref
    // @see http://www.symantec.com/region/fr/resources/protocole.html
    // @see http://fr.php.net/manual/fr/function.setcookie.php#102352
    header('P3P:policyref="'.URL_DIR_SACOCHE.'p3p.xml",CP="NON DSP COR CURa OUR NOR UNI"');
    session_name(SESSION_NOM);
    session_cache_limiter('nocache');
  }

  /*
   * Ouvrir une session existante
   * 
   * @param void
   * @return void
   */
  private static function open_old()
  {
    Session::param();
    $ID = $_COOKIE[SESSION_NOM];
    session_id($ID);
    if( !session_start() )
    {
      exit_error( 'Ouverture de session' /*titre*/ , 'La session n\'a pu être démarrée.<br />Le disque dur du serveur hébergeant SACoche serait-il plein ?' /*contenu*/ );
    }
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
    // Clef pour éviter les vols de session.
    $_SESSION['SESSION_KEY']           = Session::session_key();
    // Numéro de la base
    $_SESSION['BASE']                  = 0;
    // Données associées au profil de l'utilisateur.
    $_SESSION['USER_PROFIL_SIGLE']     = 'OUT';
    $_SESSION['USER_PROFIL_TYPE']      = 'public';
    $_SESSION['USER_PROFIL_NOM_COURT'] = 'non connecté';
    $_SESSION['USER_PROFIL_NOM_LONG']  = 'utilisateur non connecté';
    $_SESSION['USER_DUREE_INACTIVITE'] = 0;
    // Données personnelles de l'utilisateur.
    $_SESSION['USER_ID']               = 0;
    $_SESSION['USER_NOM']              = '-';
    $_SESSION['USER_PRENOM']           = '-';
    // Données associées à l'établissement.
    $_SESSION['SESAMATH_ID']           = 0;
    $_SESSION['CONNEXION_MODE']        = 'normal';
    // Informations navigateur.
    // Détecter si usage d'un appareil mobile (tablette, téléphone...).
    $_SESSION['BROWSER'] = Browser::caracteristiques_navigateur();
    $Mobile_Detect = new Mobile_Detect();
    $_SESSION['BROWSER']['mobile'] = $Mobile_Detect->isMobile();
  }

  /*
   * Renvoyer une clef associée au navigateur, à l'adresse IP et à la session en cours
   * 
   * @param void
   * @return string
   */
  private static function session_key()
  {
    return md5( Session::get_IP() . Session::get_UserAgent() . session_id() );
  }

  /*
   * Rediriger vers l'authentification SSO si détecté.
   * 
   * Si HTML : message d'erreur mis en session qui provoquera un retour en page d'accueil.
   * Si AJAX : sortir de suite avec un message d'erreur.
   * 
   * @param string $message
   * @return void
   */
  private static function exit_sauf_SSO($message)
  {
    $test_get =    ( isset($_GET['sso']) && ( isset($_GET['base']) || isset($_GET['id']) || isset($_GET['uai']) || isset($_COOKIE[COOKIE_MEMOGET]) || (HEBERGEUR_INSTALLATION=='mono-structure') ) ) ? TRUE : FALSE ;
    $test_cookie = ( ( isset($_COOKIE[COOKIE_STRUCTURE]) || (HEBERGEUR_INSTALLATION=='mono-structure') ) && isset($_COOKIE[COOKIE_AUTHMODE]) && ($_COOKIE[COOKIE_AUTHMODE]!='normal') )              ? TRUE : FALSE ;
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
        // accès direct à une page réservée, onglets incompatibles ouverts, inactivité, disque plein, chemin invalide, ...
        Session::$tab_message_erreur[] = $message.' Veuillez vous (re)-connecter.';
      }
    }
    // si ajax
    else
    {
      $conseil = ( $test_get || $test_cookie ) ? ' Veuillez actualiser la page.' : ' Veuillez vous (re)-connecter.' ;
      exit_error( 'Session perdue / expirée' /*titre*/ , $message.$conseil /*contenu*/ );
    }
  }

  /*
   * Lancer en cascade les processus pour repartir avec une nouvelle session
   * 
   * @param bool   $memo_GET   Pour réinjecter les paramètres après authentification SACoche (pour une authentification SSO, c'est déjà automatique)
   * @return void
   */
  private static function close__open_new__init($memo_GET)
  {
    Session::close();
    Session::open_new();
    Session::init();
    $_SESSION['MEMO_GET'] = ( $memo_GET && !empty($_GET) ) ? $_GET : NULL ;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques - Gestion de la session
  // //////////////////////////////////////////////////

  /**
   * Vérifier le droit d'accès à une page donnée.
   * Le fait de lister les droits d'accès de chaque page empêche de surcroit l'exploitation d'une vulnérabilité "include PHP" (http://www.certa.ssi.gouv.fr/site/CERTA-2003-ALE-003/).
   *
   * @param string $page
   * @return bool
   */
  public static function verif_droit_acces($page)
  {
    // Pour des raison de clarté / maintenance, il est préférable d'externaliser ce tableau dans un fichier.
    require(CHEMIN_DOSSIER_INCLUDE.'tableau_droits.php');
    if(isset($tab_droits_par_page[$page]))
    {
      Session::$tab_droits_page = $tab_droits_par_page[$page];
      return TRUE;
    }
    else
    {
      // La page n'a pas de droit défini, elle ne sera donc pas chargée ; on renseigne toutefois Session::$tab_droits_page pour ne pas provoquer d'erreur.
      Session::$tab_droits_page = $tab_droits_profil_tous;
      return FALSE;
    }
  }

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
    // Utiliser l'option préfixe ou entropie de uniqid() insère un '.' qui peut provoquer une erreur disant que les seuls caractères autorisés sont a-z, A-Z, 0-9 et -
    $ID = uniqid().md5('grain_de_sable'.mt_rand());
    session_id($ID);
    return session_start();
  }

  /*
   * Fermer une session existante
   * Appelé une fois depuis /webservices/argos_parent.php et une fois depuis ajax.php
   * Remarque: pour obliger à une reconnexion sans détruire la session (donc les infos des fournisseurs de SSO), il suffit de faire $_SESSION['USER_PROFIL_SIGLE'] = 'OUT';
   * 
   * @param void
   * @return void
   */
  public static function close()
  {
    // Pas besoin de session_start() car la session a déjà été ouverte avant appel à cette fonction.
    $_SESSION = array();
    session_unset();
    setcookie( session_name() /*name*/ , '' /*value*/ , $_SERVER['REQUEST_TIME']-42000 /*expire*/ , '/' /*path*/ , getServerUrl() /*domain*/ );
    session_destroy();
  }

  /*
   * Rechercher une session existante et gérer les différents cas possibles.
   * Session::$tab_droits_page a déjà été renseigné lors de l'appel à Session::verif_droit_acces()
   * 
   * @param void
   * @return void | exit ! (sur une string si ajax, une page html, ou modification $PAGE pour process SSO)
   */
  public static function execute()
  {
    if(!isset($_COOKIE[SESSION_NOM]))
    {
      // 1. Aucune session transmise
      Session::open_new();
      Session::init();
      if(!Session::$tab_droits_page['public'])
      {
        // 1.1. Demande d'accès à une page réservée (donc besoin d'identification) : session perdue / expirée, ou demande d'accès direct (lien profond) -> redirection pour une nouvelle identification
        Session::exit_sauf_SSO('Session absente / perdue / expirée / incompatible.'); // Si SSO au prochain coup on ne passera plus par là.
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
      if(!isset($_SESSION['USER_PROFIL_SIGLE']))
      {
        // 2.1. Pas de session retrouvée (sinon cette variable serait renseignée)
        if(!Session::$tab_droits_page['public'])
        {
          // 2.1.1. Session perdue ou expirée et demande d'accès à une page réservée : redirection pour une nouvelle identification
          Session::close__open_new__init( TRUE /*memo_GET*/ );
          Session::exit_sauf_SSO('Session absente / perdue / expirée / incompatible.'); // On peut initialiser la session avant car si SSO au prochain coup on ne passera plus par là.
        }
        else
        {
          // 2.1.2. Session perdue ou expirée et page publique : création d'une nouvelle session, pas de message d'alerte pour indiquer que la session est perdue
          Session::close__open_new__init( TRUE /*memo_GET*/ );
        }
      }
      elseif($_SESSION['SESSION_KEY'] != Session::session_key())
      {
        // 2.2. Session retrouvée, mais louche car IP ou navigateur modifié (tentative de piratage ? c'est cependant difficile de récupérer le cookie d'un tiers, voire impossible avec les autres protections dont SACoche bénéficie).
        Session::close__open_new__init( TRUE /*memo_GET*/ );
        Session::exit_sauf_SSO('Appel anormal (modification d\'adresse IP ou de navigateur).');
      }
      elseif($_SESSION['USER_PROFIL_SIGLE'] == 'OUT')
      {
        // 2.3. Session retrouvée, utilisateur non identifié
        if(!Session::$tab_droits_page['public'])
        {
          // 2.3.1. Espace non identifié => Espace identifié : redirection pour identification
          Session::exit_sauf_SSO('Authentification manquante ou perdue (onglets incompatibles ouverts ?).'); // Pas d'initialisation de session sinon la redirection avec le SSO tourne en boucle.
        }
        else
        {
          // 2.3.2. Espace non identifié => Espace non identifié : RAS
        }
      }
      else
      {
        // 2.4. Session retrouvée, utilisateur identifié
        if(Session::$tab_droits_page[$_SESSION['USER_PROFIL_TYPE']])
        {
          // 2.4.1. Espace identifié => Espace identifié identique : RAS
        }
        elseif(Session::$tab_droits_page['public'])
        {
          // 2.4.2. Espace identifié => Espace non identifié : création d'une nouvelle session vierge, pas de message d'alerte pour indiquer que la session est perdue
          // A un moment il fallait tester que ce n'était pas un appel ajax, pour éviter une déconnexion si appel au calendrier qui était dans l'espace public, mais ce n'est plus le cas...
          // Par contre il faut conserver la session de SimpleSAMLphp pour laisser à l'utilisateur le choix de se déconnecter ou non de son SSO.
          $SimpleSAMLphp_SESSION = ( ($_SESSION['CONNEXION_MODE']=='gepi') && (isset($_SESSION['SimpleSAMLphp_SESSION'])) ) ? $_SESSION['SimpleSAMLphp_SESSION'] : FALSE ; // isset() pour le cas où l'admin vient de cocher le mode Gepi mais c'est connecté sans juste avant
          Session::close__open_new__init( FALSE /*memo_GET*/ );
          if($SimpleSAMLphp_SESSION) { $_SESSION['SimpleSAMLphp_SESSION'] = $SimpleSAMLphp_SESSION; }
        }
        elseif(!Session::$tab_droits_page['public']) // (forcément)
        {
          // 2.4.3. Espace identifié => Autre espace identifié incompatible : redirection pour une nouvelle identification
          // Pas de redirection SSO sinon on tourne en boucle (il faudrait faire une déconnexion SSO préalable).
          Session::close__open_new__init( FALSE /*memo_GET*/ ); // FALSE car sinon on peut tourner en boucle (toujours redirigé vers une page qui ne correspond pas au profil utilisé)
          Session::exit_sauf_SSO('Appel incompatible avec votre identification actuelle.');
        }
      }
    }
  }

  // //////////////////////////////////////////////////
  // Méthodes privées (internes) - Gestion CSRF
  // //////////////////////////////////////////////////

  /*
   * Tester si une page est dispensée de contrôle CSRF
   * 
   * @param string
   * @return bool
   */
  private static function page_avec_jeton_CSRF($page)
  {
    // //////////////////////////////////////////////////
    // Tableau avec les pages pour lesquelles une vérification de jeton est effectuée lors d'un appel AJAX pour contrer les attaques de type CSRF
    // En fait plutôt que de lister les pages qui en ont besoin, on liste les pages qui n'en ont pas besoin :)
    // @see http://fr.wikipedia.org/wiki/Cross-site_request_forgery
    // @see http://www.siteduzero.com/tutoriel-3-157576-securisation-des-failles-csrf.html
    // //////////////////////////////////////////////////
    $tab_sans_verif_csrf = array
    (
      // appel depuis plusieurs pages + pas de vérif utile
      '_maj_select_directeurs',
      '_maj_select_domaines',
      '_maj_select_eleves',
      '_maj_select_eval',
      '_maj_select_matieres',
      '_maj_select_matieres_famille',
      '_maj_select_matieres_prof',
      '_maj_select_niveaux',
      '_maj_select_niveaux_famille',
      '_maj_select_parents',
      '_maj_select_piliers',
      '_maj_select_professeurs',
      '_maj_select_professeurs_directeurs',
      '_maj_select_profs_groupe',
      'calque_date_calendrier',
      'calque_voir_photo',
      'compte_selection_items',
      'conserver_session_active',
      'evaluation_demande_eleve_ajout',
      'fermer_session',
      // sans objet (sans besoin d'identification) + sinon si la session a expiré alors elle est réinitialisée de façon transparente lors de l'appel ajax mais forcément le jeton de session n'est pas retrouvé
      'public_accueil',
      // sans objet car pas de formulaire
      'public_login_SSO', 
      'public_logout_SSO',
      'releve',
      'releve_pdf',
      'webservices',
    );
    return (in_array($page,$tab_sans_verif_csrf)) ? FALSE : TRUE ;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques - Gestion CSRF
  // //////////////////////////////////////////////////

  /*
   * Générer un jeton CSRF pour une page donnée (le met en session).
   * Inutile d'essayer de le fixer uniquement sur l'IP ou la Session car pour ce type d'attaque c'est le navigateur de l'utilisateur qui est utilisé.
   * On est donc contraint d'utiliser un élément aléatoire ou indicateur de temps.
   * Pour éviter de fausses alertes si utilisation de plusieurs onglets d'une même page, on ne retient pas qu'un seul jeton par page.
   * La session doit être ouverte.
   * 
   * @param string $page
   * @return void
   */
  public static function generer_jeton_anti_CSRF($page)
  {
    if(Session::page_avec_jeton_CSRF($page))
    {
      Session::$_CSRF_value = uniqid(); // sera écrit dans la page pour que javascript l'envoie
      $_SESSION['CSRF'][Session::$_CSRF_value.'.'.$page] = TRUE;
    }
  }

  /**
   * Appelé par ajax.php pour vérifier un jeton CSRF lors d'un appel ajax (soumission de données) d'une page donnée (vérifie sa valeur en session, quitte si pb).
   * Peut être aussi potentiellement appelé par de rares pages PHP s'envoyant un formulaire sans passer par AJAX (seule officiel_accueil.php est concernée au 10/2012).
   * On utilise REQUEST car c'est tranmis en POST si ajax maison mais en GET si utilisation de jquery.form.js.
   * La session doit être ouverte.
   *
   * @param string $page
   * @return void
   */
  public static function verifier_jeton_anti_CSRF($page)
  {
    if(Session::page_avec_jeton_CSRF($page))
    {
      if( empty($_REQUEST['csrf']) || empty($_SESSION['CSRF'][$_REQUEST['csrf'].'.'.$page]) )
      {
        exit_error( 'Alerte CSRF' /*titre*/ , 'Jeton anti-CSRF invalide.<br />Plusieurs onglets ouverts avec des sessions incompatibles ?' /*contenu*/ );
      }
    }
  }

}
?>