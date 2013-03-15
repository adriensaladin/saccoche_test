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
$TITRE = "Connexion SSO";

/*
 * Cette page n'est pas (plus en fait) appelée directement.
 * Elle est appelée lors d'un lien direct vers une page nécessitant une identification :
 * - si des paramètres dans l'URL indiquent explicitement un SSO (nouvelle connexion, appel depuis un service tiers...)
 * - ou si des informations en cookies indiquent un SSO (session perdue mais tentative de reconnexion automatique)
 * 
 * En cas d'installation de type multi-structures, SACoche doit connaître la structure concernée AVANT de lancer SAML ou CAS pour savoir si l'établissement l'a configuré ou pas, et avec quels paramètres !
 * Si on ne sait pas de quel établissement il s'agit, on ne peut pas savoir s'il y a un CAS, un SAML-GEPI, et si oui quelle URL appeler, etc.
 * (sur un même serveur il peut y avoir un SACoche avec authentification reliée à l'ENT de Nantes, un SACoche relié à un LCS, un SACoche relié à un SAML-GEPI, ...)
 * D'autre part on ne peut pas me fier à une éventuelle info transmise par SAML ou CAS ; non seulement car elle arrive trop tard comme je viens de l'expliquer, mais aussi car ce n'est pas le même schéma partout.
 * (CAS, par exemple, peut renvoyer le RNE en attribut APRES authentification à une appli donnée, dans une acad donnée, mais pas pour autant à une autre appli, ou dans une autre acad)
 * 
 * Normalement on passe en GET le numéro de la base, mais il se peut qu'une connection directe ne puisse être établie qu'avec l'UAI (connu de l'ENT) en non avec le numéro de la base SACoche (inconnu de l'ENT).
 * Dans ce cas, on récupère le numéro de la base et on le remplace dans les variable PHP, pour ne pas avoir à recommencer ce petit jeu à chaque échange avec le serveur SSO pendant l'authentification.
 * 
 * URL directe mono-structure            : http://adresse.com/?sso
 * URL directe multi-structure normale   : http://adresse.com/?sso&base=... | http://adresse.com/?sso&id=...
 * URL directe multi-structure spéciale  : http://adresse.com/?sso&uai=...
 * 
 * URL profonde mono-structure           : http://adresse.com/?page=...&sso
 * URL profonde multi-structure normale  : http://adresse.com/?page=...&sso&base=... | http://adresse.com/?page=...&sso&id=...
 * URL profonde multi-structure spéciale : http://adresse.com/?page=...&sso&uai=...
 */

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// En cas de multi-structures, il faut savoir dans quelle base récupérer les informations.
// Un UAI ou un id de base doit être transmis, même s'il est toléré de le retrouver dans un cookie.
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
  // Remplacer l'info par le numéro de base correspondant dans toutes les variables accessibles à PHP avant que la classe SSO ne s'en mèle.
  // Pourquoi ??? Economiser une requête ? Retiré car trifouillage pas indispensable et pouvant dérouter des partenaires ENT autorisant un format d'URL donné.
  /*
  $bad = 'uai='.$_GET['uai'];
  $bon = 'base='.$BASE;
  $_GET['base']     = $BASE;
  $_REQUEST['base'] = $BASE;
  if(isset($_SERVER['HTTP_REFERER'])) { $_SERVER['HTTP_REFERER'] = str_replace($bad,$bon,$_SERVER['HTTP_REFERER']); }
  if(isset($_SERVER['QUERY_STRING'])) { $_SERVER['QUERY_STRING'] = str_replace($bad,$bon,$_SERVER['QUERY_STRING']); }
  if(isset($_SERVER['REQUEST_URI'] )) { $_SERVER['REQUEST_URI']  = str_replace($bad,$bon,$_SERVER['REQUEST_URI'] ); }
  unset($_GET['uai']);
  */
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Connexion à la base pour charger les paramètres du SSO demandé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Mettre à jour la base si nécessaire
maj_base_structure_si_besoin($BASE);

$DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"connexion_mode","cas_serveur_host","cas_serveur_port","cas_serveur_root","gepi_url","gepi_rne","gepi_certificat_empreinte"'); // A compléter
foreach($DB_TAB as $DB_ROW)
{
  ${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
}
if($connexion_mode=='normal')
{
  exit_error( 'Configuration manquante' /*titre*/ , 'Etablissement non paramétré par l\'administrateur pour utiliser un service d\'authentification externe.<br />Un administrateur doit renseigner cette configuration dans le menu [Paramétrages][Mode&nbsp;d\'identification].' /*contenu*/ );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Identification avec le protocole CAS
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($connexion_mode=='cas')
{
  /**
   * Si la bufferisation est active et contient la sortie de phpCAS sur une CAS_Exception,
   *   récupère le contenu et l'affiche dans notre template (sinon lance un exit sans rien faire)
   *
   * @author Daniel Caillibaud <daniel.caillibaud@sesamath.net>
   * @param string $msg_sup (facultatif) Du contenu supplémentaire ajouté juste avant le </body> (mettre les <p>)
   */
  function exit_CAS_Exception($msg_sup='')
  {
    // on veut pas afficher ça mais notre jolie page
    $content = ob_get_clean();
    if ($content)
    {
      // cf CAS/Client.php:printHTMLHeader()
      $pattern = '/<html><head><title>([^<]*)<\/title><\/head><body><h1>[^<]*<\/h1>(.*)<\/body><\/html>/';
      $matches = array();
      preg_match($pattern, $content, $matches);
      if (!empty($matches[1]))
      {
        exit_error( $matches[1] /*titre*/ , $matches[2].$msg_sup /*contenu*/ , FALSE /*setup*/ );
      }
    }
    // si on arrive là, on a pas trouvé le contenu, on laisse l'existant (à priori la page moche de phpCAS)
    exit();
  }
  /**
   * Renvoie les traces d'une exception sous forme de chaîne
   *
   * @author Daniel Caillibaud <daniel.caillibaud@sesamath.net>
   * @param Exception $e L'exception dont on veut les traces
   * @return string Les traces (une par ligne sous la forme "clé : valeur")
   * @throws Exception 
   */
  function get_string_traces($e)
  {
    if (!is_a($e, 'Exception'))
    {
      throw new Exception('get_string_traces() veut une exception en paramètre');
    }
    $traces = $e->getTrace();
    $str_traces = '';
    $i = 0;
    foreach ($traces as $trace)
    {
      // init
      $str_traces .= "\n\t".'trace '.$i.' => ';
      // class
      if (isset($trace['class']))
      {
        $str_traces .= $trace['class'].' :->: ';
        unset($trace['class']);
      }

      if (isset($trace['function']))
      {
        // le nom de la fct concernée
        $str_traces .= $trace['function'];
        unset($trace['function']);
        // et ses arguments
        if (isset($trace['args']))
        {
          // faut ajouter les traces, mais $trace['args'] peut contenir des objets impossible à afficher
          // on pourrait récupérer la sortie du dump mais ça peut être gros, on affichera donc que 
          // la classe des objets ou bien "array"
          $args_aff = array();
          foreach ($trace['args'] as $arg)
          {
            if (is_scalar($arg))
            {
              $args_aff[] = $arg;
            }
            elseif (is_array($arg))
            {
              $args_aff[] = '[array ' .count($arg) .' elts]';
            }
            elseif (is_object($arg))
            {
              $args_aff[] = 'obj ' .get_class($arg);
            }
            else
            {
              $args_aff[] = 'type ' .gettype($arg);
            }
          }
          // reste que des strings, on ajoute à la trace globale
          $str_traces .= '(' .implode(', ', $args_aff) .')';
          unset($trace['args']);
        }
        else
        { // pas d'args, on ajoute les () pour mieux voir que c'est une fct
          $str_traces .= '()';
        }
      }
      // line
      if (isset($trace['line']))
      {
        $str_traces .= ' on line '.$trace['line'];
        unset($trace['line']);
      }
      // file
      if (isset($trace['file']))
      {
        $str_traces .= ' in '.$trace['file'];
        unset($trace['file']);
      }
      // type
      if (isset($trace['type']))
      {
        if ( ($trace['type']!='') && ($trace['type']!='->') )
        {
          $str_traces .= "\n\t\t".'type : '.$trace['type']."\n";
        }
        unset($trace['file']);
      }
      // si jamais il reste des trucs...
      if (count($trace))
      {
        $str_traces .= ' et il reste les infos :'."\n\t\t";
        foreach ($trace as $key => $value)
        {
          $str_traces .= "\t\t".$key.' : ';
          if (is_scalar($value))
          {
            $str_traces .= $value."\n";
          }
          else
          {
            $str_traces .= print_r($value, TRUE)."\n";
          }
        }
      }
      $i++;
    }
    return $str_traces;
  }
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
  // Demander à CAS d'aller interroger le serveur
  // Cette méthode permet de forcer CAS à demander au client de s'authentifier s'il ne trouve aucun client d'authentifié.
  // (redirige vers le serveur d'authentification si aucun utilisateur authentifié n'a été trouvé par le client CAS)
  try
  {
    phpCAS::forceAuthentication();
  }
  catch(Exception $e)
  {
    // @author Daniel Caillibaud <daniel.caillibaud@sesamath.net>
    $msg  = 'phpCAS::forceAuthentication() sur '.$cas_serveur_host.' a planté ';
    $msg .= $e->getMessage();
    // on ajoute les traces dans le log
    $traces = get_string_traces($e);
    if ($traces != '')
    {
      $msg .= ' avec la trace :'."\n".$traces;
    }
    trigger_error($msg);
    $msg_sup = '<p>Cette erreur peut être due à des paramètres de serveur CAS incorrects, ou un XML renvoyé par le serveur CAS syntaxiquement invalide.</p>';
    if (is_a($e, 'CAS_AuthenticationException'))
    {
      exit_CAS_Exception($msg_sup);
    }
    else
    {
      // peut-on passer là ?
      trigger_error('phpCAS::forceAuthentication() sur '.$cas_serveur_host.' a planté mais ce n\'est pas une CAS_AuthenticationException');
      exit_error( 'Erreur d\'authentification CAS' /*titre*/ , '<p>L\'authentification CAS sur '.$cas_serveur_host.' a échouée.<br />'.$e->getMessage().'</p>'.$msg_sup /*contenu*/ , $setup=FALSE );
    }
  }
  // A partir de là, l'utilisateur est forcément authentifié sur son CAS.
  // Récupérer l'identifiant (login ou numéro interne...) de l'utilisateur authentifié pour le traiter dans l'application
  $id_ENT = phpCAS::getUser();
  // Récupérer les attributs CAS : SACoche ne récupère aucun attribut, il n'y a pas de récupération de données via le ticket et donc pas de nécessité de convention.
  // $tab_attributs = phpCAS::getAttributes();
  // Forcer à réinterroger le serveur CAS en cas de nouvel appel à cette page pour être certain que c'est toujours le même utilisateur qui est connecté au CAS.
  unset($_SESSION['phpCAS']);
  // Comparer avec les données de la base
  list($auth_resultat,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $id_ENT /*login*/ , FALSE /*password*/ , 'cas' /*mode_connection*/ );
  if($auth_resultat!='ok')
  {
    exit_error( 'Incident authentification CAS' /*titre*/ , $auth_resultat /*contenu*/ );
  }
  // Connecter l'utilisateur
  SessionUser::initialiser_utilisateur($BASE,$auth_DB_ROW);
  // Redirection vers la page demandée en cas de succès.
  // En théorie il faudrait laisser la suite du code se poursuivre, ce qui n'est pas impossible, mais ça pose le souci de la transmission de &verif_cookie
  // Rediriger le navigateur.
  header('Status: 307 Temporary Redirect', TRUE, 307);
  header('Location: '.URL_BASE.$_SERVER['REQUEST_URI'].'&verif_cookie');
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Authentification assurée par Shibboleth
// La redirection est effectuée en amont (configuration du serveur web qui est "shibbolisé"), l'utilisateur doit donc être authentifié à ce stade.
// @see https://services.renater.fr/federation/docs/fiches/shibbolisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($connexion_mode=='shibboleth')
{
  /*
  * Récupération de l'identifiant de l'utilisateur authentifié dans les variables serveur.
  * A cause du chainage réalisé depuis Shibboleth entre différents IDP pour compléter les attributs exportés, l'UID arrive en double séparé par un « ; ».
  */
  if( (empty($_SERVER['HTTP_UID'])) || empty($_SERVER['HTTP_SHIB_SESSION_ID']) )
  {
    $http_uid             = isset($_SERVER['HTTP_UID'])             ? 'vaut "'.html($_SERVER['HTTP_UID']).'"'             : 'n\'est pas définie' ;
    $http_shib_session_id = isset($_SERVER['HTTP_SHIB_SESSION_ID']) ? 'vaut "'.html($_SERVER['HTTP_SHIB_SESSION_ID']).'"' : 'n\'est pas définie' ;
    exit_error( 'Incident authentification Shibboleth' /*titre*/ , 'Ce serveur ne semble pas disposer d\'une authentification Shibboleth, ou bien celle ci n\'a pas été mise en &oelig;uvre :<br />- la variable $_SERVER["HTTP_UID"] '.$http_uid.'<br />- la variable $_SERVER["HTTP_SHIB_SESSION_ID"] '.$http_shib_session_id /*contenu*/ );
  }
  $http_uid = explode( ';' , $_SERVER['HTTP_UID'] );
  $id_ENT = $http_uid[0];
  // Comparer avec les données de la base
  list($auth_resultat,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $id_ENT /*login*/ , FALSE /*password*/ , 'shibboleth' /*mode_connection*/ );
  if($auth_resultat!='ok')
  {
    exit_error( 'Incident authentification Shibboleth' /*titre*/ , $auth_resultat /*contenu*/ );
  }
  // En cas d'authentification avec le protocole Shibboleth, on prend l'ID Shibboleth comme identifiant de session afin de pouvoir propager une éventuelle déconnexion.
  // SACoche comportant une partie publique ne requérant pas d'authentification, et un accès possible avec une authentification locale, toute l'application n'est pas shibbolisée.
  // La session a donc déjà pu être ouverte par SACoche avec un autre identifiant perso.
  if( $_COOKIE[SESSION_NOM] != $_SERVER['HTTP_SHIB_SESSION_ID'] )
  {
    Session::close();Session::open_new();Session::init(); // Pour init(), seul session_key() a ici de l'intérêt.
  }
  // Connecter l'utilisateur
  SessionUser::initialiser_utilisateur($BASE,$auth_DB_ROW);
  // Redirection vers la page demandée en cas de succès, avec transmission de &verif_cookie
  header('Status: 307 Temporary Redirect', TRUE, 307);
  header('Location: '.URL_BASE.$_SERVER['REQUEST_URI'].'&verif_cookie');
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Identification à partir de GEPI avec le protocole SAML
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
  //on forge une extension SAML pour tramsmettre l'établissement précisé dans SACoche
  $ext = array();
  if($BASE)
  {
    $dom = new DOMDocument();
    $ce = $dom->createElementNS('gepi_name_space', 'gepi_name_space:organization', $BASE);
    $ext[] = new SAML2_XML_Chunk($ce);
  }
  // Tester si le user est authentifié, rediriger sinon
  $auth->requireAuth( array('saml:Extensions'=>$ext) );
  // Tester si le user est authentifié, rediriger sinon
  $auth->requireAuth();
  // Récupérer l'identifiant Gepi de l'utilisateur authentifié pour le traiter dans l'application
  $attr = $auth->getAttributes();
  $login_GEPI = $attr['USER_ID_GEPI'][0];
  // Comparer avec les données de la base
  list($auth_resultat,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $login_GEPI /*login*/ , FALSE /*password*/ , 'gepi' /*mode_connection*/ );
  if($auth_resultat!='ok')
  {
    exit_error( 'Incident authentification Gepi' /*titre*/ , $auth_resultat /*contenu*/ );
  }
  // Connecter l'utilisateur
  SessionUser::initialiser_utilisateur($BASE,$auth_DB_ROW);
  // Pas de redirection car passage possible d'infos en POST à conserver ; tant pis pour la vérification du cookie...
}

?>