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
 * URL directe mono-structure             : http://adresse.com/?sso
 * URL directe multi-structures normale   : http://adresse.com/?sso&base=[BASE] | http://adresse.com/?sso&id=[BASE] | http://adresse.com/?sso=[BASE]
 * URL directe multi-structures spéciale  : http://adresse.com/?sso&uai=[UAI]   | http://adresse.com/?sso=[UAI]
 * 
 * URL profonde mono-structure            : http://adresse.com/?page=...&sso
 * URL profonde multi-structures normale  : http://adresse.com/?page=...&sso&base=[BASE] | http://adresse.com/?page=...&sso&id=[BASE] | http://adresse.com/?page=...&sso=[BASE]
 * URL profonde multi-structures spéciale : http://adresse.com/?page=...&sso&uai=[UAI]   | http://adresse.com/?page=...&sso=[UAI]
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
  // Test si id d'établissement transmis dans l'URL
  // Historiquement "id" si connexion normale et "base" si connexion SSO
  // Nouveauté 07/2014 : pouvoir passer l'info de l'établissement comme valeur du paramètre SSO
  $BASE = ctype_digit($_GET['sso']) ? Clean::entier($_GET['sso'])  : $BASE ;
  $BASE = (isset($_GET['id']))      ? Clean::entier($_GET['id'])   : $BASE ;
  $BASE = (isset($_GET['base']))    ? Clean::entier($_GET['base']) : $BASE ;
  // Test si UAI d'établissement transmis dans l'URL
  // Nouveauté 07/2014 : pouvoir passer l'UAI de l'établissement comme valeur du paramètre SSO
  $trans_UAI = (isset($_GET['uai'])) ? Clean::uai($_GET['uai']) : ( tester_UAI($_GET['sso']) ? Clean::uai($_GET['sso']) : NULL ) ;
  $BASE = ($trans_UAI) ? DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_id_base_for_UAI($trans_UAI) : $BASE ;
  if(!$BASE)
  {
    if($trans_UAI)
    {
      exit_error( 'Paramètre incorrect' /*titre*/ , 'Le numéro UAI transmis '.$trans_UAI.' n\'est pas référencé sur cette installation de SACoche : vérifiez son exactitude et si cet établissement est bien inscrit sur ce serveur.' /*contenu*/ );
    }
    else
    {
      exit_error( 'Donnée manquante' /*titre*/ , 'Référence de base manquante (le paramètre "base" ou "id" ou "sso" n\'a pas été transmis ou n\'est pas un entier et n\'a pas non plus été trouvé dans un Cookie).' /*contenu*/ );
    }
  }
  charger_parametres_mysql_supplementaires($BASE);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Connexion à la base pour charger les paramètres du SSO demandé
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Mettre à jour la base si nécessaire
maj_base_structure_si_besoin($BASE);

$DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"connexion_departement","connexion_mode","connexion_nom","cas_serveur_host","cas_serveur_port","cas_serveur_root","cas_serveur_url_login","cas_serveur_url_logout","cas_serveur_url_validate","gepi_url","gepi_rne","gepi_certificat_empreinte"'); // A compléter
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
   * récupère le contenu et l'affiche dans notre template (sinon lance un exit sans rien faire).
   *
   * Une cause rencontrée (peut-être pas la seule)
   * est que le XML renvoyé par le serveur CAS est syntaxiquement invalide.
   * En général car il contient un caractère parmi & < >
   * 
   * Quand c'est un &, avant l'erreur fatale on a un warning : DOMDocument::loadXML(): xmlParseEntityRef: no name in Entity...
   * Quand c'est un <, avant l'erreur fatale on a un warning : DOMDocument::loadXML(): StartTag: invalid element name...
   * Quand c'est un >, avant l'erreur fatale on a un warning : DOMDocument::loadXML(): Start tag expected, '<' not found in Entity...
   * L'ENT doit s'arranger pour envoyer un XML valide, donc :
   * - soit convertir ces caractères en entités HTML (&amp; &lt; &gt;)
   * - soit retirer ces caractères ou les remplacer par d'autres
   * - soit utiliser des sections CDATA : <![CDATA[some text & some more text]]>
   * 
   * Par ailleurs, il est tout de même dommage que phpCas ne renvoie pas un message plus causant 
   * (genre xml parse error, ou à défaut invalid Response).
   * 
   * @author Daniel Caillibaud <daniel.caillibaud@sesamath.net>
   * @param string $msg_sup (facultatif) Du contenu supplémentaire ajouté juste avant le </body> (mettre les <p>)
   */
  function exit_CAS_Exception($msg_sup='')
  {
    // on ne veut pas afficher ça mais notre jolie page
    $content = ob_get_clean();
    if ($content)
    {
      // cf CAS/Client.php:printHTMLHeader()
      $pattern = '/<html><head><title>([^<]*)<\/title><\/head><body><h1>[^<]*<\/h1>(.*)<\/body><\/html>/';
      $matches = array();
      preg_match($pattern, $content, $matches);
      if (!empty($matches[1]))
      {
        exit_error( $matches[1] /*titre*/ , $matches[2].$msg_sup /*contenu*/ );
      }
    }
    // si on arrive là, c'est qu'on n'a pas trouvé le contenu, on laisse l'existant (a priori la page moche de phpCAS)
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
          $str_traces .=  ' type : '.$trace['type'];
        }
        unset($trace['file']);
      }
      // si jamais il reste des trucs...
      if (count($trace))
      {
        $str_traces .= ' et il reste les infos :';
        foreach ($trace as $key => $value)
        {
          $str_traces .= " ".$key.' : ';
          if (is_scalar($value))
          {
            $str_traces .= $value.' ; ';
          }
          else
          {
            $str_traces .= print_r($value, TRUE).' ; ';
          }
        }
      }
      $i++;
    }
    return $str_traces;
  }
  try
  {
    // Si besoin, cette méthode statique créé un fichier de log sur ce qui se passe avec CAS
    if(DEBUG_PHPCAS)
    {
      if( (HEBERGEUR_INSTALLATION=='mono-structure') || !PHPCAS_ETABL_ID_LISTING || (strpos(PHPCAS_ETABL_ID_LISTING,','.$BASE.',')!==FALSE) )
      {
        $fichier_nom_debut = 'debugcas_'.$BASE;
        $fichier_nom_fin   = fabriquer_fin_nom_fichier__pseudo_alea($fichier_nom_debut);
        phpCAS::setDebug(PHPCAS_CHEMIN_LOGS.$fichier_nom_debut.'_'.$fichier_nom_fin.'.txt');
      }
    }
    // Initialiser la connexion avec CAS  ; le premier argument est la version du protocole CAS ; le dernier argument indique qu'on utilise la session existante
    // $cas_protocole = ($BASE!=100500) ? '2.0' /* CAS_VERSION_2_0 */ : 'S1' /*SAML_VERSION_1_1*/ ;
    // phpCAS::client($cas_protocole, $cas_serveur_host, (int)$cas_serveur_port, $cas_serveur_root, FALSE);
    phpCAS::client(CAS_VERSION_2_0, $cas_serveur_host, (int)$cas_serveur_port, $cas_serveur_root, FALSE);
    phpCAS::setLang(PHPCAS_LANG_FRENCH);
    // Surcharge éventuelle des URL
    if ($cas_serveur_url_login)    { phpCAS::setServerLoginURL($cas_serveur_url_login); }
    if ($cas_serveur_url_logout)   { phpCAS::setServerLogoutURL($cas_serveur_url_logout); }
    if ($cas_serveur_url_validate) { phpCAS::setServerServiceValidateURL($cas_serveur_url_validate); }
    // Appliquer un proxy si défini par le webmestre ; voir cURL::get_contents() pour les commentaires.
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
    // On indique qu'il faut vérifier la validité du certificat SSL, sauf exception paramétrée, mais alors dans ce cas ça ne sert à rien d'utiliser une connexion sécurisée.
    if(strpos(PHPCAS_NO_CERTIF_LISTING,','.$connexion_nom.',')===FALSE)
    {
      phpCAS::setCasServerCACert(CHEMIN_FICHIER_CA_CERTS_FILE);
    }
    else
    {
      phpCAS::setNoCasServerValidation();
    }
    // Gestion du single sign-out
    phpCAS::handleLogoutRequests(FALSE);
    // Demander à CAS d'aller interroger le serveur
    // Cette méthode permet de forcer CAS à demander au client de s'authentifier s'il ne trouve aucun client d'authentifié.
    // (redirige vers le serveur d'authentification si aucun utilisateur authentifié n'a été trouvé par le client CAS)
    phpCAS::forceAuthentication();
    // A partir de là, l'utilisateur est forcément authentifié sur son CAS.
    // Récupérer l'identifiant (login ou numéro interne...) de l'utilisateur authentifié pour le traiter dans l'application
    $id_ENT = phpCAS::getUser();
  }
  catch(Exception $e)
  {
    // @author Daniel Caillibaud <daniel.caillibaud@sesamath.net>
    $msg  = 'phpCAS::forceAuthentication() sur '.$cas_serveur_host.' pour l\'établissement n°'.$BASE.' qui utilise l\'ENT '.$connexion_nom.' a planté ';
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
      exit_error( 'Erreur d\'authentification CAS' /*titre*/ , '<p>L\'authentification CAS sur '.$cas_serveur_host.' a échouée.<br />'.$e->getMessage().'</p>'.$msg_sup /*contenu*/ );
    }
  }
  // Forcer à réinterroger le serveur CAS en cas de nouvel appel à cette page pour être certain que c'est toujours le même utilisateur qui est connecté au CAS.
  unset($_SESSION['phpCAS']);
  // Comparer avec les données de la base
  list($auth_resultat,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $id_ENT /*login*/ , FALSE /*password*/ , 'cas' /*mode_connection*/ );
  if($auth_resultat!='ok')
  {
    exit_error( 'Incident authentification CAS' /*titre*/ , $auth_resultat /*contenu*/ );
  }
  // Vérifier la présence d'une convention valide si besoin,
  // sauf pour les administrateurs qui doivent pouvoir accéder à leur espace pour régulariser la situation (même s'il leur est toujours possible d'utiliser une authentification locale),
  // et sauf pour les établissements pour tester les connecteurs ENT en PROD, d'identifiants >= 100 000.
  if( IS_HEBERGEMENT_SESAMATH && (SERVEUR_TYPE=='PROD') && CONVENTION_ENT_REQUISE && (CONVENTION_ENT_START_DATE_MYSQL<=TODAY_MYSQL) && ($auth_DB_ROW['user_profil_type']!='administrateur') && ($BASE<100000) )
  {
    // Vérifier que les paramètres de la base n'ont pas été trafiqués (via une sauvegarde / restauration de la base avec modification intermédiaire) pour passer outre : nom de connexion mis à perso ou modifié etc.
    $connexion_ref = $connexion_departement.'|'.$connexion_nom;
    require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');
    if(!isset($tab_connexion_info[$connexion_mode][$connexion_ref]))
    {
      exit_error( 'Paramètres CAS anormaux' /*titre*/ , 'Les paramètres CAS sont anormaux (connexion_mode vaut "'.$connexion_mode.'" ; connexion_departement vaut "'.$connexion_departement.'" ; connexion_nom vaut "'.$connexion_nom.'") !<br />Un administrateur doit sélectionner l\'ENT concerné depuis son menu [Paramétrage&nbsp;établissement] [Mode&nbsp;d\'identification].' /*contenu*/ );
    }
    $tab_info = $tab_connexion_info[$connexion_mode][$connexion_ref];
    if($connexion_nom!='perso')
    {
      if( (strpos($cas_serveur_host,$tab_info['serveur_host_domain'])===FALSE) || ($tab_info['serveur_port']!=$cas_serveur_port) || ($tab_info['serveur_root']!=$cas_serveur_root) || ($tab_info['serveur_url_login']!=$cas_serveur_url_login) || ($tab_info['serveur_url_logout']!=$cas_serveur_url_logout) || ($tab_info['serveur_url_validate']!=$cas_serveur_url_validate) )
      {
        exit_error( 'Paramètres CAS anormaux' /*titre*/ , 'Les paramètres CAS enregistrés ne correspondent pas à ceux attendus pour la référence "'.$connexion_ref.'" !<br />Un administrateur doit revalider la sélection depuis son menu [Paramétrage&nbsp;établissement] [Mode&nbsp;d\'identification].' /*contenu*/ );
      }
    }
    if(!is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT))
    {
      exit_error( 'Fichier manquant' /*titre*/ , 'Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_SESAMATH_ENT).'</b>&nbsp;&raquo; (uniquement présent sur le serveur Sésamath) n\'a pas été détecté !' /*contenu*/ );
    }
    // Normalement les hébergements académiques ne sont pas concernés
    require(CHEMIN_FICHIER_WS_SESAMATH_ENT); // Charge les tableaux   $tab_connecteurs_hebergement & $tab_connecteurs_convention
    if( isset($tab_connecteurs_hebergement[$connexion_ref]) )
    {
      exit_error( 'Mode d\'authentification anormal' /*titre*/ , 'Le mode d\'authentification sélectionné ('.$connexion_nom.') doit être utilisé sur l\'hébergement académique dédié (département '.$connexion_departement.') !' /*contenu*/ );
    }
    // Pas besoin de vérification si convention signée à un plus haut niveau
    if( isset($tab_connecteurs_convention[$connexion_ref]) && $tab_ent_convention_infos[$tab_connecteurs_convention[$connexion_ref]]['actif'] )
    {
      // Cas d'une convention signée par un partenaire ENT => Mettre en session l'affichage de sa communication en page d'accueil.
      $partenaire_id = DB_WEBMESTRE_PUBLIC::DB_recuperer_id_partenaire_for_connecteur($connexion_ref);
      $fichier_chemin = 'info_'.$partenaire_id.'.php';
      if( $partenaire_id && is_file(CHEMIN_DOSSIER_PARTENARIAT.$fichier_chemin) )
      {
        require(CHEMIN_DOSSIER_PARTENARIAT.$fichier_chemin);
        $partenaire_logo_url = ($partenaire_logo_actuel_filename) ? URL_DIR_PARTENARIAT.$partenaire_logo_actuel_filename : URL_DIR_IMG.'auto.gif' ;
        $partenaire_lien_ouvrant = ($partenaire_adresse_web) ? '<a href="'.html($partenaire_adresse_web).'" target="_blank">' : '' ;
        $partenaire_lien_fermant = ($partenaire_adresse_web) ? '</a>'                                                         : '' ;
        $_SESSION['CONVENTION_PARTENAIRE_ENT_COMMUNICATION'] = $partenaire_lien_ouvrant.'<span id="partenaire_logo"><img src="'.html($partenaire_logo_url).'" /></span><span id="partenaire_message">'.nl2br(html($partenaire_message)).'</span>'.$partenaire_lien_fermant.'<hr id="partenaire_hr" />';
      }
    }
    else
    {
      if(!DB_WEBMESTRE_PUBLIC::DB_tester_convention_active( $BASE , $connexion_nom ))
      {
        $message_introduction = ( isset($tab_connecteurs_convention[$connexion_ref]) && !$tab_ent_convention_infos[$tab_connecteurs_convention[$connexion_ref]]['actif'] ) ? $tab_ent_convention_infos[$tab_connecteurs_convention[$connexion_ref]]['texte'].'<br />L\'usage de ce service sur ce serveur est donc désormais soumis à la signature et au règlement d\'une convention avec l\'établissement.' : 'L\'usage de ce service sur ce serveur est soumis à la signature et au règlement d\'une convention.' ;
        $message_explication  = '<br />Un administrateur doit effectuer les démarches depuis son menu [Paramétrage&nbsp;établissement] [Mode&nbsp;d\'identification].<br />Veuillez consulter <a href="'.SERVEUR_GUIDE_ENT.'#toggle_partenariats" target="_blank">cette documentation pour davantage d\'explications</a> et <a href="'.SERVEUR_GUIDE_ENT.'#toggle_gestion_convention" target="_blank">cette documentation pour la marche à suivre</a>.' ;
        exit_error( 'Absence de convention valide' /*titre*/ , $message_introduction.$message_explication /*contenu*/ );
      }
    }
  }
  // Connecter l'utilisateur
  SessionUser::initialiser_utilisateur($BASE,$auth_DB_ROW);
  // Pas de redirection (passage possible d'infos en POST à conserver), on peut laisser le code se poursuivre.
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Authentification assurée par Shibboleth
// La redirection est effectuée en amont (configuration du serveur web qui est "shibbolisé"), l'utilisateur doit donc être authentifié à ce stade.
// Attention : SACoche comportant une partie publique ne requérant pas d'authentification, et un accès possible avec une authentification locale, toute l'application n'est pas à shibboliser.
// @see https://services.renater.fr/federation/docs/fiches/shibbolisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

/*

>>> extrait conf shibboleth2.xml

<RequestMapper type="Native">
 <RequestMap applicationId="default">
  <Host name="vm-iozone3.in.ac-bordeaux.fr">
   <Path name="sacoche">
    <Query name="sso" authType="shibboleth" requireSession="true" />
   </Path>
  </Host>
 </RequestMap>
</RequestMapper>

>>> extrait httpd.conf

Redirect permanent /sacoche /sacoche/
ProxyPass /sacoche/ https://ent2d.ac-bordeaux.fr/sacoche/
ProxyPassReverse /sacoche/ https://ent2d.ac-bordeaux.fr/sacoche/

>>> extrait $_SERVER[]

[HTTP_AFFILIATION]
[HTTP_CTEMAIL]
[HTTP_DISPLAYNAME]
[HTTP_ENTAUXENSCATEGODISCIPLINE]
[HTTP_ENTELEVECLASSES]
[HTTP_ENTELEVESTRUCTRATTACHID]
[HTTP_ENTITLEMENT]
[HTTP_ENTPERSONFONCTIONS]
[HTTP_ENTPERSONJOINTURE]
[HTTP_ENTPERSONLOGIN]
[HTTP_ENTPERSONNOMPATRO]
[HTTP_EPPN]
[HTTP_FREDUCODEMEF]
[HTTP_FREDUVECTEUR]
[HTTP_GIVENNAME]
[HTTP_ID_SOURCE]
[HTTP_MAIL]
[HTTP_PERSISTENT_ID]
[HTTP_SHIB_APPLICATION_ID]
[HTTP_SHIB_ASSERTION_COUNT]
[HTTP_SHIB_AUTHENTICATION_INSTANT]
[HTTP_SHIB_AUTHENTICATION_METHOD]
[HTTP_SHIB_AUTHNCONTEXT_CLASS]
[HTTP_SHIB_AUTHNCONTEXT_DECL]
[HTTP_SHIB_COOKIE_NAME]
[HTTP_SHIB_IDENTITY_PROVIDER]
[HTTP_SHIB_SESSION_ID]
[HTTP_SHIB_SESSION_INDEX]
[HTTP_SN]
[HTTP_TARGETED_ID]
[HTTP_TSSCONETID]
[HTTP_UID]
[HTTP_UNSCOPED_AFFILIATION]


*/

if($connexion_mode=='shibboleth')
{
  // Récupération dans les variables serveur de l'identifiant de l'utilisateur authentifié.
  if( ( (empty($_SERVER['HTTP_UID'])) || empty($_SERVER['HTTP_SHIB_SESSION_ID']) ) && empty($_SERVER['HTTP_FREDUVECTEUR']) )
  {
    $http_uid             = isset($_SERVER['HTTP_UID'])             ? 'vaut "'.html($_SERVER['HTTP_UID']).'"'             : 'n\'est pas définie' ;
    $http_shib_session_id = isset($_SERVER['HTTP_SHIB_SESSION_ID']) ? 'vaut "'.html($_SERVER['HTTP_SHIB_SESSION_ID']).'"' : 'n\'est pas définie' ;
    $http_freduvecteur    = isset($_SERVER['HTTP_FREDUVECTEUR'])    ? 'vaut "'.html($_SERVER['HTTP_FREDUVECTEUR']).'"'    : 'n\'est pas définie' ;
    $contenu = 'Ce serveur ne semble pas disposer d\'une authentification Shibboleth, ou bien celle ci n\'a pas été mise en &oelig;uvre, ou bien la session a été perdue :<br />'
             . '- la variable $_SERVER["HTTP_UID"] '.$http_uid.'<br />'
             . '- la variable $_SERVER["HTTP_SHIB_SESSION_ID"] '.$http_shib_session_id.'<br />'
             . '- la variable $_SERVER["HTTP_FREDUVECTEUR"] '.$http_freduvecteur ;
    exit_error( 'Incident authentification Shibboleth' /*titre*/ , $contenu );
  }
  // Comparer avec les données de la base.
  $auth_resultat_shibboleth = $auth_resultat_siecle = $auth_resultat_vecteur = '';
  // [1] On commence par regarder HTTP_UID, disponible pour tous les profils sauf les parents.
  // A cause du chainage réalisé depuis Shibboleth entre différents IDP pour compléter les attributs exportés, l'UID peut arriver en double séparé par un « ; ».
  $tab_http_uid = explode( ';' , $_SERVER['HTTP_UID'] );
  $id_ENT = $tab_http_uid[0];
  if($id_ENT)
  {
    list($auth_resultat_shibboleth,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $id_ENT /*login*/ , FALSE /*password*/ , 'shibboleth' /*mode_connection*/ );
  }
  if($auth_resultat_shibboleth!='ok')
  {
    // [2] Ensuite, on peut regarder HTTP_TSSCONETID ou HTTP_ENTELEVESTRUCTRATTACHID, disponible pour les élèves.
    $eleve_sconet_id = (!empty($_SERVER['HTTP_TSSCONETID'])) ? (int)$_SERVER['HTTP_TSSCONETID'] : ( (!empty($_SERVER['HTTP_ENTELEVESTRUCTRATTACHID'])) ? (int)$_SERVER['HTTP_ENTELEVESTRUCTRATTACHID'] : 0 ) ;
    if($eleve_sconet_id)
    {
      list($auth_resultat_siecle,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $eleve_sconet_id /*login*/ , FALSE /*password*/ , 'siecle' /*mode_connection*/ );
    }
    if($auth_resultat_siecle!='ok')
    {
      // [3] Enfin, on peut regarder HTTP_FREDUVECTEUR, disponible pour les élèves et les parents (pour les parents, on n'a même que ça...).
      // Pour les parents, il peut être multivalué, les différentes valeurs étant alors séparées par un « ; » (je ne m'embête pas, je prends la première valeur, inutile de tester tous les enfants, les associations doivent avoir été faites dans SACoche).
      $fr_edu_vecteur = (!empty($_SERVER['HTTP_FREDUVECTEUR'])) ? $_SERVER['HTTP_FREDUVECTEUR'] : '' ;
      $tab_vecteur = explode( ';' , $fr_edu_vecteur );
      $fr_edu_vecteur = $tab_vecteur[0];
      list( $vecteur_profil , $vecteur_nom , $vecteur_prenom , $vecteur_eleve_id , $vecteur_uai ) = explode('|',$fr_edu_vecteur ) + array_fill(0,5,NULL) ; // Evite des NOTICE en initialisant les valeurs manquantes
      if( in_array($vecteur_profil,array(3,4)) && ($vecteur_eleve_id) && ($vecteur_eleve_id!=$eleve_sconet_id) ) // cas d'un élève
      {
        list($auth_resultat_siecle,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $vecteur_eleve_id /*login*/ , FALSE /*password*/ , 'siecle' /*mode_connection*/ );
      }
      elseif( in_array($vecteur_profil,array(1,2)) && ($vecteur_eleve_id) ) // cas d'un parent
      {
        if( $vecteur_nom && $vecteur_prenom )
        {
          list($auth_resultat_vecteur,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $vecteur_eleve_id /*login*/ , FALSE /*password*/ , 'vecteur_parent' /*mode_connection*/ , $vecteur_nom , $vecteur_prenom );
        }
        else
        {
          $auth_resultat_vecteur = 'Identification réussie mais vecteur d\'identité parent "' .$fr_edu_vecteur.'" incomplet, ce qui empêche de rechercher le compte SACoche correspondant.';
        }
      }
    }
  }
  if( ($auth_resultat_shibboleth!='ok') && ($auth_resultat_siecle!='ok') && ($auth_resultat_vecteur!='ok') )
  {
    exit_error( 'Incident authentification Shibboleth' /*titre*/ , $auth_resultat_shibboleth.$auth_resultat2.$auth_resultat_vecteur );
  }
  // Connecter l'utilisateur
  SessionUser::initialiser_utilisateur($BASE,$auth_DB_ROW);
  // Pas de redirection (passage possible d'infos en POST à conserver), on peut laisser le code se poursuivre.
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Identification à partir de GEPI avec le protocole SAML
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($connexion_mode=='gepi')
{
  // Charger l'autoload de la librairie SimpleSAMLphp (qui ne peut être intégré de façon simple dans le _loader par un unique appel de classe (comme phpCAS).
  require(CHEMIN_DOSSIER_SACOCHE.'_lib'.DS.'SimpleSAMLphp'.DS.'lib'.DS.'_autoload.php');
  // Mise en session d'informations dont SimpleSAMLphp a besoin ; utiliser des constantes ne va pas car Gepi fait un appel à SimpleSAMLphp en court-circuitant SACoche pour vérifier la légitimité de l'appel.
  $_SESSION['SACoche-SimpleSAMLphp'] = array(
    'GEPI_URL'                  => $gepi_url,
    'GEPI_RNE'                  => $gepi_rne,
    'GEPI_CERTIFICAT_EMPREINTE' => $gepi_certificat_empreinte,
    'SIMPLESAMLPHP_BASEURLPATH' => substr($_SERVER['SCRIPT_NAME'],1,-9).'_lib/SimpleSAMLphp/www/',
    'WEBMESTRE_NOM'             => WEBMESTRE_NOM,
    'WEBMESTRE_PRENOM'          => WEBMESTRE_PRENOM,
    'WEBMESTRE_COURRIEL'        => WEBMESTRE_COURRIEL,
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
  // Pas de redirection (passage possible d'infos en POST à conserver), on peut laisser le code se poursuivre.
  return; // Ne pas exécuter la suite de ce fichier inclus.
}

?>