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

class InfoServeur
{

  // //////////////////////////////////////////////////
  // Attributs
  // //////////////////////////////////////////////////

  private static $SACoche_version_dispo = NULL;

  private static $tab_style = array(
    'vert' =>'bv' ,
    'jaune'=>'bj' ,
    'rouge'=>'br'
  );

  // //////////////////////////////////////////////////
  // Méthodes privées (internes) de bas niveau
  // //////////////////////////////////////////////////

  /**
   * info_base_complement
   *
   * @param void
   * @return string
   */
  private static function info_base_complement()
  {
    return ($_SESSION['USER_PROFIL_TYPE']!='webmestre') ? '' : ( (HEBERGEUR_INSTALLATION=='multi-structures') ? 'La valeur peut dépendre de la structure&hellip;<br />' : 'Information disponible sous un profil administrateur.<br />' ) ;
  }

  /**
   * SACoche_version_dispo
   * Retourne la dernière version disponible de SACoche, si ce n'est pas déjà fait.
   *
   * @param string $contenu
   * @param string $couleur   vert|jaune|rouge
   * @return string
   */
  private static function SACoche_version_dispo()
  {
    return (InfoServeur::$SACoche_version_dispo!==NULL) ? InfoServeur::$SACoche_version_dispo : recuperer_numero_derniere_version() ;
  }

  /**
   * commentaire
   *
   * @param string $sujet
   * @return string
   */
  private static function commentaire($sujet)
  {
    switch($sujet)
    {
      case 'version_php'                 : return 'Version '.PHP_VERSION_MINI_REQUISE.' ou ultérieure requise.<br \>Version '.PHP_VERSION_MINI_CONSEILLEE.' ou ultérieure conseillée.<br \>PHP 5.2 n\'est plus supporté depuis le 16 décembre 2010.';
      case 'version_mysql'               : return 'Version '.MYSQL_VERSION_MINI_REQUISE.' ou ultérieure requise.<br \>Version '.MYSQL_VERSION_MINI_CONSEILLEE.' ou ultérieure conseillée.<br \>MySQL 5.5 est stable depuis octobre 2010.';
      case 'version_sacoche_prog'        : return 'Dernière version disponible : '.InfoServeur::SACoche_version_dispo();
      case 'version_sacoche_base'        : return InfoServeur::info_base_complement().'Version attendue : '.VERSION_BASE_STRUCTURE;
      case 'max_execution_time'          : return 'Par défaut 30 secondes.<br />Une valeur trop faible pourrait gêner les sauvegardes / restaurations de grosses bases.';
      case 'max_input_vars'              : return 'Par défaut 1000.<br />Une valeur inférieure est susceptible de tronquer la transmission de formulaires importants.<br \>Disponible à compter de PHP 5.3.9 uniquement.';
      case 'memory_limit'                : return 'Par défaut 128Mo (convient très bien).<br />Doit être plus grand que post_max_size (ci-dessous).<br />Une valeur inférieure à 128Mo peut poser problème (pour générer des bilans PDF en particulier).<br />Mais 64Mo voire 32Mo peuvent aussi convenir, tout dépend de l\'usage (nombre d\'élèves considérés à la fois, quantité de données&hellip;).';
      case 'post_max_size'               : return 'Par défaut 8Mo.<br />Doit être plus grand que upload_max_filesize (ci-dessous).';
      case 'upload_max_filesize'         : return 'Par défaut 2Mo.<br />A augmenter si on doit envoyer un fichier d\'une taille supérieure.';
      case 'max_allowed_packet'          : return 'Par défaut 1Mo (1 048 576 octets).<br />Pour restaurer une sauvegarde, les fichiers contenus dans le zip ne doivent pas dépasser cette taille.';
      case 'max_user_connections'        : return 'Une valeur inférieure à 5 est susceptible, suivant la charge, de poser problème.';
      case 'group_concat_max_len'        : return 'Par défaut 1024 octets.<br />Une telle valeur devrait suffire.';
      case 'safe_mode'                   : return 'Fonctionnalité obsolète depuis PHP 5.3.0, à ne plus utiliser.<br />Son activation peut poser problème (pour échanger avec le serveur communautaire).';
      case 'open_basedir'                : return 'Limite les fichiers pouvant être ouverts par PHP à une architecture de dossiers spécifique.<br />Son activation peut poser problème (pour échanger avec le serveur communautaire).';
      case 'ini_set_memory_limit'        : return 'Possibilité d\'augmenter la mémoire allouée au script.';
      case 'register_globals'            : return 'Enregistrer les variables environnement Get/Post/Cookie/Server comme des variables globales.<br />Par défaut désactivé depuis PHP 4.2.<br />Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.';
      case 'magic_quotes_gpc'            : return 'Échapper les apostrophes pour Get/Post/Cookie.<br />Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.';
      case 'magic_quotes_sybase'         : return 'Échapper les apostrophes pour Get/Post/Cookie.<br />Remplace la directive magic_quotes_gpc en cas d\'activation.<br />Fonctionnalité obsolète depuis PHP 5.3 et supprimée depuis PHP 5.4.';
      case 'session_gc_maxlifetime'      : return 'Durée de vie des données (session) sur le serveur, en nombre de secondes.<br />Par défaut 1440s soit 24min.<br />SACoche permet de régler une conservation de session plus longue.<br />Mais cela ne fonctionnera que si le serveur est configuré pour une durée minimum de 10min.';
      case 'session_use_trans_sid'       : return 'Par défaut désactivé, ce qui rend le support de l\'identifiant de session transparent.<br />C\'est une protection contre les attaques qui utilisent des identifiants de sessions dans les URL.';
      case 'session_use_only_cookies'    : return 'Par défaut activé, ce qui indique d\'utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur.<br />C\'est une protection contre les attaques qui utilisent des identifiants de sessions dans les URL.';
      case 'zend_ze1_compatibility_mode' : return 'Activer le mode de compatibilité avec le Zend Engine 1 (PHP 4).<br />C\'est incompatible avec classe PDO, et l\'utilisation de simplexml_load_string() ou DOMDocument (par exemples) provoquent des erreurs fatales.<br />Fonctionnalité obsolète et supprimée depuis PHP 5.3.';
      case 'modules_PHP'                 : return 'Les modules sur fond coloré sont requis par SACoche.';
      default                            : return '';
    }
  }

  /**
   * cellule_coloree_centree
   * Retourne une chaine de la forme '<td class="hc ...">...</td>'
   *
   * @param string $contenu
   * @param string $couleur   vert|jaune|rouge
   * @return string
   */
  private static function cellule_coloree_centree($contenu,$couleur)
  {
    return '<td class="hc '.InfoServeur::$tab_style[$couleur].'">'.$contenu.'</td>';
  }

  /**
   * cellule_centree
   * Retourne une chaine de la forme '<td class="hc">...</td>'
   *
   * @param string $contenu
   * @return string
   */
  private static function cellule_centree($contenu)
  {
    return '<td class="hc">'.$contenu.'</td>';
  }

  /**
   * tableau_deux_colonnes
   *
   * @param string $titre
   * @param string $tab_objets   $nom_objet => $nom_affichage
   * @return string
   */
  private static function tableau_deux_colonnes($titre,$tab_objets)
  {
    $tab_tr = array();
    foreach($tab_objets as $nom_objet => $nom_affichage)
    {
      $tab_tr[] = '<tr><td><img alt="" src="./_img/bulle_aide.png" title="'.InfoServeur::commentaire($nom_objet).'" /> '.$nom_affichage.'</td>'.call_user_func('InfoServeur::'.$nom_objet).'</tr>';
    }
    return'<table class="p"><thead><tr><th colspan="2">'.$titre.'</th></tr></thead><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  // //////////////////////////////////////////////////
  // Méthodes privées (internes) intermédiaires
  // //////////////////////////////////////////////////

  /**
   * version_php
   * Retourne le numéro de la version courante de PHP.
   * La vérification de la version de PHP est effectuée à chaque appel de SACoche.
   * Voir http://fr.php.net/phpversion
   *
   * @param void
   * @return string
   */
  private static function version_php()
  {
    if(version_compare(PHP_VERSION,PHP_VERSION_MINI_CONSEILLEE,'>=')) return InfoServeur::cellule_coloree_centree(PHP_VERSION,'vert');
    if(version_compare(PHP_VERSION,PHP_VERSION_MINI_REQUISE,'>='))    return InfoServeur::cellule_coloree_centree(PHP_VERSION,'jaune');
                                                                      return InfoServeur::cellule_coloree_centree(PHP_VERSION,'rouge');
  }

  /**
   * version_mysql
   * Retourne une chaîne indiquant la version courante du serveur MySQL.
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/information-functions.html
   *
   * @param void
   * @return string
   */
  private static function version_mysql()
  {
    $mysql_version = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_version_MySQL() : DB_WEBMESTRE_PUBLIC::DB_recuperer_version_MySQL() ;
    if(version_compare($mysql_version,MYSQL_VERSION_MINI_CONSEILLEE,'>=')) return InfoServeur::cellule_coloree_centree($mysql_version,'vert');
    if(version_compare($mysql_version,MYSQL_VERSION_MINI_REQUISE,'>='))    return InfoServeur::cellule_coloree_centree($mysql_version,'jaune');
                                                                           return InfoServeur::cellule_coloree_centree($mysql_version,'rouge');
  }

  /**
   * version_sacoche_prog
   * Retourne une chaîne indiquant la version logicielle des fichiers de SACoche.
   *
   * @param void
   * @return string
   */
  private static function version_sacoche_prog()
  {
    if(VERSION_PROG==InfoServeur::SACoche_version_dispo())
    {
      $couleur = 'vert';
    }
    else
    {
      $tab_version_installee  = explode('-',VERSION_PROG);
      $tab_version_disponible = explode('-',InfoServeur::SACoche_version_dispo());
      if(count($tab_version_disponible)==3)
      {
        $date_unix_version_installee  = mktime( 0 , 0 , 0 , (int)$tab_version_installee[1]  , (int)$tab_version_installee[2]  , (int)$tab_version_installee[0]  );
        $date_unix_version_disponible = mktime( 0 , 0 , 0 , (int)$tab_version_disponible[1] , (int)$tab_version_disponible[2] , (int)$tab_version_disponible[0] );
        $nb_jours_ecart = ( $date_unix_version_disponible - $date_unix_version_installee ) / ( 60 * 60 * 24 ) ;
        $couleur = ($nb_jours_ecart<90) ? 'jaune' : 'rouge' ;
      }
      else
      {
        // Dernière version non détectée…
        $couleur = 'rouge' ;
      }
    }
    return InfoServeur::cellule_coloree_centree(VERSION_PROG,$couleur);
  }

  /**
   * version_sacoche_base
   * Retourne une chaîne indiquant la version logicielle de la base de données de SACoche.
   * En mode multi-structure, celle-ci est propre à chaque établissement.
   *
   * @param void
   * @return string   AAAA-MM-JJ
   */
  private static function version_sacoche_base()
  {
    if($_SESSION['USER_PROFIL_TYPE']=='webmestre')                            return InfoServeur::cellule_coloree_centree('indisponible'           ,'jaune');
    if(version_compare($_SESSION['VERSION_BASE'],VERSION_BASE_STRUCTURE,'=')) return InfoServeur::cellule_coloree_centree($_SESSION['VERSION_BASE'],'vert');
                                                                              return InfoServeur::cellule_coloree_centree($_SESSION['VERSION_BASE'],'rouge');
  }

  /**
   * max_execution_time
   * Cela permet d'éviter que des scripts en boucles infinies saturent le serveur.
   * Lorsque PHP fonctionne depuis la ligne de commande, la valeur par défaut est 0.
   * Voir http://fr.php.net/manual/fr/info.configuration.php#ini.max-execution-time
   *
   * @param void
   * @return string
   */
  private static function max_execution_time()
  {
    $val = ini_get('max_execution_time');
    $val = ($val) ? $val.'s' : '<b>&infin;</b>' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * max_input_vars
   * Limite le nombre de variables transmises (POST ou GET).
   * Information à recouper avec des limites Suhosin éventuelles.
   * Disponible à compter de Lorsque PHP 5.3.9 uniquement.
   * Voir http://www.php.net/manual/fr/info.configuration.php#ini.max-input-vars
   *
   * @param void
   * @return string
   */
  private static function max_input_vars()
  {
    $val = (version_compare(phpversion(),'5.3.9','>=')) ? ini_get('max_input_vars') : '---' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * memory_limit
   * Cette option détermine la mémoire limite, en octets, qu'un script est autorisé à allouer.
   * Cela permet de prévenir l'utilisation de toute la mémoire par un script mal codé.
   * Notez que pour n'avoir aucune limite, vous devez définir cette directive à -1.
   * Voir http://fr.php.net/manual/fr/ini.core.php#ini.memory-limit
   *
   * @param void
   * @return string
   */
  private static function memory_limit()
  {
    $val = ini_get('memory_limit');
    $val = ($val!=-1) ? $val : '<b>&infin;</b>' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * post_max_size
   * Définit la taille maximale (en octets) des données reçues par la méthode POST.
   * Cette option affecte également les fichiers chargés.
   * Pour charger de gros fichiers, cette valeur doit être plus grande que la valeur de upload_max_filesize.
   * Si la limitation de mémoire est activée par votre script de configuration, memory_limit affectera également les fichiers chargés.
   * De façon générale, memory_limit doit être plus grand que post_max_size.
   * Voir http://fr.php.net/manual/fr/ini.core.php#post_max_size
   *
   * @param void
   * @return string
   */
  private static function post_max_size()
  {
    return InfoServeur::cellule_centree( ini_get('post_max_size') );
  }

  /**
   * upload_max_filesize
   * La taille maximale en octets d'un fichier à charger.
   * Voir http://fr.php.net/manual/fr/ini.core.php#ini.upload-max-filesize
   *
   * @param void
   * @return string
   */
  private static function upload_max_filesize()
  {
    return InfoServeur::cellule_centree( ini_get('upload_max_filesize') );
  }

  /**
   * max_allowed_packet
   * La taille maximale d'un paquet envoyé à MySQL.
   * Quand on fait un INSERT multiple, il ne faut pas balancer trop d'enregistrements car si la chaîne dépasse cette limitation (1Mo) alors la requête ne passe pas.
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
   *
   * @param void
   * @return string
   */
  private static function max_allowed_packet()
  {
    $DB_ROW = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_variable_MySQL('max_allowed_packet') : DB_WEBMESTRE_PUBLIC::DB_recuperer_variable_MySQL('max_allowed_packet') ;
    $val = $DB_ROW['Value'];
    return InfoServeur::cellule_centree( number_format($val,0,'',' ') );
  }

  /**
   * max_user_connections
   * Le nombre maximum de connexions actives à MySQL pour un utilisateur particulier (0 = pas de limite).
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
   *
   * @param void
   * @return string
   */
  private static function max_user_connections()
  {
    $DB_ROW = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_variable_MySQL('max_user_connections') : DB_WEBMESTRE_PUBLIC::DB_recuperer_variable_MySQL('max_user_connections') ;
    $val = ($DB_ROW['Value']) ? $DB_ROW['Value'] : '<b>&infin;</b>' ;
    return InfoServeur::cellule_centree( $val );
  }

  /**
   * group_concat_max_len
   * La taille maximale de la chaîne résultat de GROUP_CONCAT().
   * Voir http://dev.mysql.com/doc/refman/5.0/fr/server-system-variables.html
   * Pour lever cette limitation on peut effectuer la pré-requête DB::query(SACOCHE_STRUCTURE_BD_NAME , 'SET group_concat_max_len = ...');
   *
   * @param void
   * @return string
   */
  private static function group_concat_max_len()
  {
    $DB_ROW = defined('SACOCHE_STRUCTURE_BD_NAME') ? DB_STRUCTURE_COMMUN::DB_recuperer_variable_MySQL('group_concat_max_len') : DB_WEBMESTRE_PUBLIC::DB_recuperer_variable_MySQL('group_concat_max_len') ;
    $val = $DB_ROW['Value'];
    return InfoServeur::cellule_centree( number_format($val,0,'',' ') );
  }

  /**
   * safe_mode
   * Le "Safe Mode" est le mode de sécurité de PHP. Fonctionnalité obsolète depuis PHP 5.3.0, à ne plus utiliser.
   * Voir http://www.php.net/manual/fr/features.safe-mode.php
   *
   * @param void
   * @return string
   */
  private static function safe_mode()
  {
    return (ini_get('safe_mode')) ? InfoServeur::cellule_coloree_centree('ON','rouge') : InfoServeur::cellule_coloree_centree('OFF','vert') ;
  }

  /**
   * open_basedir
   * Limite les fichiers pouvant être ouverts par PHP à une architecture de dossiers spécifique, incluant le fichier lui-même.
   * Voir http://php.net/manual/fr/ini.core.php#ini.open-basedir
   *
   * @param void
   * @return string
   */
  private static function open_basedir()
  {
    return (ini_get('open_basedir')) ? InfoServeur::cellule_coloree_centree('ON','rouge') : InfoServeur::cellule_coloree_centree('OFF','vert') ;
  }

  /**
   * ini_set_memory_limit
   * Teste s'il est autorisé d'augmenter la mémoire allouée au script.
   * Voir http://www.php.net/manual/fr/ini.core.php#ini.memory-limit
   * Cette directive peut être interdite dans la conf PHP ou via Suhosin (http://www.hardened-php.net/suhosin/configuration.html#suhosin.memory_limit)
   *
   * @param void
   * @return string
   */
  private static function ini_set_memory_limit()
  {
    $valeur_avant = (int)ini_get('memory_limit');
    if($valeur_avant==-1) return '<td class="hc">---</td>';
    $valeur_nouvelle = $valeur_avant*2;
    @ini_set('memory_limit',$valeur_nouvelle.'M'); @ini_alter('memory_limit',$valeur_nouvelle.'M');
    $valeur_apres = (int)ini_get('memory_limit');
    return ($valeur_apres==$valeur_avant) ? InfoServeur::cellule_coloree_centree('OFF','jaune') : InfoServeur::cellule_coloree_centree('ON','vert') ;
  }

  /**
   * register_globals
   * Ne pas enregistrer les variables environnement Get/Post/Cookie/Server comme des variables globales.
   * Voir http://fr.php.net/manual/fr/ini.core.php#ini.register-globals
   * register_globals NE PEUT PAS être définie durant le traitement avec "ini_set" : ini_set(register_globals,0)
   *
   * @param void
   * @return string
   */
  private static function register_globals()
  {
    $valeur = (int)ini_get('register_globals');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * magic_quotes_gpc
   * Ne pas échapper les apostrophes pour Get/Post/Cookie.
   * Voir http://fr.php.net/manual/fr/info.configuration.php#ini.magic-quotes-gpc
   * Par défaut à 1 !!!
   *
   * @param void
   * @return string
   */
  private static function magic_quotes_gpc()
  {
    $valeur = (int)ini_get('magic_quotes_gpc');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * magic_quotes_sybase
   * Ne pas échapper les apostrophes pour Get/Post/Cookie.
   * Voir http://fr.php.net/manual/fr/sybase.configuration.php#ini.magic-quotes-sybase
   * Par défaut à 0.
   *
   * @param void
   * @return string
   */
  private static function magic_quotes_sybase()
  {
    $valeur = (int)ini_get('magic_quotes_sybase');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * session_gc_maxlifetime
   * Durée de vie des données (session) sur le serveur, en nombre de secondes.
   * Voir http://www.php.net/manual/fr/session.configuration.php#ini.session.gc-maxlifetime
   * Par défaut 1440.
   *
   * @param void
   * @return string
   */
  private static function session_gc_maxlifetime()
  {
    $valeur = (int)ini_get('session.gc_maxlifetime');
    $couleur = ($valeur>620) ? 'vert' : 'rouge' ;
    return InfoServeur::cellule_coloree_centree($valeur.'s',$couleur);
  }

  /**
   * session_use_trans_sid
   * Protection contre les attaques qui utilisent des identifiants de sessions dans les URL.
   * Voir http://www.php.net/manual/fr/session.configuration.php#ini.session.use-trans-sid
   * Par défaut à 0.
   *
   * @param void
   * @return string
   */
  private static function session_use_trans_sid()
  {
    $valeur = (int)ini_get('session.use_trans_sid');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  /**
   * session_use_only_cookies
   * Le module doit utiliser seulement les cookies pour stocker les identifiants de sessions du côté du navigateur.
   * Voir http://www.php.net/manual/fr/session.configuration.php#ini.session.use-only-cookies
   * Par défaut à 1.
   *
   * @param void
   * @return string
   */
  private static function session_use_only_cookies()
  {
    $valeur = (int)ini_get('session.use_only_cookies');
    return ($valeur==1) ? InfoServeur::cellule_coloree_centree('ON','vert') : InfoServeur::cellule_coloree_centree('OFF','rouge') ;
  }

  /**
   * zend_ze1_compatibility_mode
   * Activer le mode de compatibilité avec le Zend Engine 1 (PHP 4).
   * C'est incompatible avec classe PDO, et l'utilisation de "simplexml_load_string()" ou "DOMDocument" (par exemples) provoquent des erreurs fatales.
   * Voir http://www.php.net/manual/fr/ini.core.php#ini.zend.ze1-compatibility-mode
   *
   * @param void
   * @return string
   */
  private static function zend_ze1_compatibility_mode()
  {
    $valeur = (int)ini_get('zend.ze1_compatibility_mode');
    return ($valeur==0) ? InfoServeur::cellule_coloree_centree('OFF','vert') : InfoServeur::cellule_coloree_centree('ON','rouge') ;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques génériques
  // //////////////////////////////////////////////////

  /**
   * minimum_limitations_upload
   * La taille maximale d'upload d'un fichier est limitée par memory_limit + post_max_size + upload_max_filesize
   * Normalement on a memory_limit > post_max_size > upload_max_filesize
   * Cette fonction retourne le minimum de ces 3 valeurs (attention, ce ne sont pas des entiers mais des chaines avec des unités).
   *
   * @param bool    $avec_explication
   * @return string "min(memory_limit,post_max_size,upload_max_filesize)=..."
   */
  public static function minimum_limitations_upload($avec_explication=TRUE)
  {
    $tab_limit_chaine = array( ini_get('memory_limit') , ini_get('post_max_size') , ini_get('upload_max_filesize') );
    $valeur_mini = 0;
    $chaine_mini = '';
    foreach($tab_limit_chaine as $key => $chaine)
    {
      $chaine = trim($chaine);
      $valeur = (int)substr($chaine,0,-1);
      $unite  = strtoupper($chaine[strlen($chaine)-1]);
      switch($unite)
      {
        case 'G':
            $valeur *= 1000;
        case 'M':
            $valeur *= 1000;
        case 'K':
            $valeur *= 1000;
      }
      if( ($valeur!=0) && ($valeur!=-1) && ( ($valeur_mini==0) || ($valeur<$valeur_mini) ) )
      {
        $valeur_mini = $valeur;
        $chaine_mini = $chaine;
      }
    }
    return ($avec_explication) ? 'min(memory_limit,post_max_size,upload_max_filesize) = '.$chaine_mini : $chaine_mini ;
  }

  /**
   * modules_php
   * Liste de tous les modules compilés et chargés.
   * La présence des modules requis est effectuée à chaque appel de SACoche.
   * Voir http://fr.php.net/get_loaded_extensions
   *
   * @param void
   * @return array
   */
  public static function modules_php()
  {
    $tab_modules = get_loaded_extensions();
    natcasesort($tab_modules);
    return array_values($tab_modules);
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques pour la page "Caractéristiques du serveur"
  // //////////////////////////////////////////////////

  public static function tableau_versions_logicielles()
  {
    $tab_objets = array(
      'version_php'          => 'PHP',
      'version_mysql'        => 'MySQL',
      'version_sacoche_prog' => 'SACoche fichiers',
      'version_sacoche_base' => 'SACoche base'
    );
    return InfoServeur::tableau_deux_colonnes( 'Versions logicielles' , $tab_objets );
  }

  public static function tableau_limitations_PHP()
  {
    $tab_objets = array(
      'max_execution_time'  => 'max execution time',
      'max_input_vars'      => 'max input vars',
      'memory_limit'        => 'memory limit',
      'post_max_size'       => 'post max size',
      'upload_max_filesize' => 'upload max filesize'
    );
    return InfoServeur::tableau_deux_colonnes( 'Réglage des limitations PHP' , $tab_objets );
  }

  public static function tableau_limitations_MySQL()
  {
    $tab_objets = array(
      'max_allowed_packet'   => 'max allowed packet',
      'max_user_connections' => 'max user connections',
      'group_concat_max_len' => 'group concat max len'
    );
    return InfoServeur::tableau_deux_colonnes( 'Réglage des limitations MySQL' , $tab_objets );
  }

  public static function tableau_configuration_PHP()
  {
    $tab_objets = array(
      'safe_mode'                   => 'safe_mode',
      'open_basedir'                => 'open_basedir',
      'ini_set_memory_limit'        => 'ini_set(memory_limit)',
      'register_globals'            => 'register_globals',
      'magic_quotes_gpc'            => 'magic_quotes_gpc',
      'magic_quotes_sybase'         => 'magic_quotes_sybase',
      'session_gc_maxlifetime'      => 'session.gc_maxlifetime',
      'session_use_trans_sid'       => 'session.use_trans_sid',
      'session_use_only_cookies'    => 'session.use_only_cookies',
      'zend_ze1_compatibility_mode' => 'zend.ze1_compatibility_mode'
    );
    return InfoServeur::tableau_deux_colonnes( 'Configuration de PHP' , $tab_objets );
  }

  public static function tableau_modules_PHP($nb_lignes)
  {
    $tab_modules_requis = array('curl','dom','gd','mbstring','mysql','pdo','pdo_mysql','session','zip','zlib');
    $lignes = '';
    $tab_modules = InfoServeur::modules_php();
    $nb_modules = count($tab_modules);
    $nb_colonnes = ceil($nb_modules/$nb_lignes);
    for($numero_ligne=0 ; $numero_ligne<$nb_lignes ; $numero_ligne++)
    {
      $lignes .= '<tr>';
      for($numero_colonne=0 ; $numero_colonne<$nb_colonnes ; $numero_colonne++)
      {
        $indice = $numero_colonne*$nb_lignes + $numero_ligne ;
        $style  = ( ($indice<$nb_modules) && (in_array(strtolower($tab_modules[$indice]),$tab_modules_requis)) ) ? ' class="'.InfoServeur::$tab_style['vert'].'"' : '' ;
        $lignes .= ($indice<$nb_modules) ? '<td'.$style.'>'.$tab_modules[$indice].'</td>' : '<td class="hc">-</td>' ;
      }
      $lignes .= '</tr>';
    }
    $tr_head = '<tr><th colspan="'.$nb_colonnes.'">Modules PHP compilés et chargés <img alt="" src="./_img/bulle_aide.png" title="'.InfoServeur::commentaire('modules_PHP').'" /></th></tr>';
    return'<table class="p"><thead>'.$tr_head.'</thead><tbody>'.$lignes.'</tbody></table>';
  }

  public static function tableau_reglages_Suhosin()
  {
    $tab_lignes   = array(1=>'get','post','request');
    $tab_colonnes = array(1=>'max_name_length','max_totalname_length','max_value_length','max_vars');
    $tab_tr = array();
    $tab_suhosin_options = (version_compare(PHP_VERSION,5.3,'<')) ? @ini_get_all( 'suhosin' ) : @ini_get_all( 'suhosin' , FALSE /*details*/ ) ; // http://fr.php.net/ini_get_all
    $tab_tr[0] = '<tr><th>Suhosin</th>';
    foreach($tab_lignes as $i_ligne => $categorie)
    {
      $tab_tr[$i_ligne] = '<tr><td class="hc">'.$categorie.'</td>';
      foreach($tab_colonnes as $i_colonne => $option)
      {
        $tab_tr[0] .= ($i_ligne==1) ? '<td class="hc">'.str_replace('_',' ',$option).'</td>' : '' ;
        $option_nom = ( ($categorie!='request') || ($option!='max_name_length') ) ? 'suhosin'.'.'.$categorie.'.'.$option : 'suhosin.request.max_varname_length' ;
        $option_val = (isset($tab_suhosin_options[$option_nom])) ? $tab_suhosin_options[$option_nom] : '---' ;
        $tab_tr[$i_ligne] .= '<td class="hc">'.$option_val.'</td>' ;
      }
      $tab_tr[$i_ligne] .= '</tr>';
    }
    $tab_tr[0] .= '</tr>';
    return'<table class="p"><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  public static function tableau_reglages_GD()
  {
    $jpeg = (version_compare(PHP_VERSION,5.3,'<')) ? 'JPG' : 'JPEG' ;
    $tab_objets = array(
      'GD Version'       => 'Version', // 
      'FreeType Support' => 'Support FreeType', // Requis pour imagettftext()
      $jpeg.' Support'   => 'Support JPEG',
      'PNG Support'      => 'Support PNG',
      'GIF Read Support' => 'Support GIF' // "GIF Create Support" non testé car on n'écrit que des jpg (photos) et des png (étiquettes) de toutes façons.
    );
    $tab_gd_options = gd_info(); // http://fr.php.net/manual/fr/function.gd-info.php
    $tab_tr = array();
    foreach($tab_objets as $nom_objet => $nom_affichage)
    {
      if($nom_objet=='GD Version')
      {
        $search_version = preg_match( '/[0-9.]+/' , $tab_gd_options[$nom_objet] , $tab_match);
        $gd_version = ($search_version) ? $tab_match[0] : '' ;
        $img = ($nom_objet=='GD Version') ? '<img alt="" src="./_img/bulle_aide.png" title="La fonction imagecreatetruecolor() requiert la bibliothèque GD version 2.0.1 ou supérieure, 2.0.28 ou supérieure étant recommandée." /> ' : '' ;
             if(version_compare($gd_version,'2.0.28','>=')) $td = InfoServeur::cellule_coloree_centree($tab_gd_options[$nom_objet],'vert');
        else if(version_compare($gd_version,'2.0.1' ,'>=')) $td = InfoServeur::cellule_coloree_centree($tab_gd_options[$nom_objet],'jaune');
        else                                                $td = InfoServeur::cellule_coloree_centree($tab_gd_options[$nom_objet],'rouge');
      }
      else
      {
        $img = '' ;
        $td = ($tab_gd_options[$nom_objet]) ? InfoServeur::cellule_coloree_centree('ON','vert') : InfoServeur::cellule_coloree_centree('OFF','rouge') ;
      }
      $tab_tr[] = '<tr><td>'.$img.$nom_affichage.'</td>'.$td.'</tr>';
    }
    return'<table class="p"><thead><tr><th colspan="2">Bibliothèque GD</th></tr></thead><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

  public static function tableau_serveur_et_client()
  {
    $tab_tr = array();
    $tab_tr[] = '<tr><th>Identification du Client</th><td>'.html($_SERVER['HTTP_USER_AGENT']).'</td></tr>';
    $tab_tr[] = '<tr><th>Identification du Serveur</th><td>'.html($_SERVER['SERVER_SOFTWARE'].' '.PHP_SAPI).'</td></tr>';
    $tab_tr[] = '<tr><th>Système d\'exploitation</th><td>'.html(php_uname('s').' '.php_uname('r')).'</td></tr>';
    $tab_tr[] = '<tr><th>Adresse d\'installation</th><td>'.html(URL_INSTALL_SACOCHE).'</td></tr>';
    return'<table class="p"><tbody>'.implode('',$tab_tr).'</tbody></table>';
  }

}
?>