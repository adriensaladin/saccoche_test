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

class FileSystem
{

  // Nom du fichier uploadé transmis
  public static $file_upload_name = '';
  // Nom du fichier uploadé enregistré
  public static $file_saved_name = '';

  // //////////////////////////////////////////////////
  // Tableau avec les messages d'erreurs correspondants aux codes renvoyés par la librairie ZipArchive
  // http://fr.php.net/manual/fr/zip.constants.php#83827
  // //////////////////////////////////////////////////

  public static $tab_zip_error = array(
     0 =>  "0 | OK | pas d'erreur",
     1 =>  "1 | MULTIDISK | multi-volumes non supporté",
     2 =>  "2 | RENAME | échec renommage fichier temporaire",
     3 =>  "3 | CLOSE | échec fermeture archive",
     4 =>  "4 | SEEK | erreur recherche",
     5 =>  "5 | READ | erreur lecture",
     6 =>  "6 | WRITE | erreur écriture",
     7 =>  "7 | CRC | erreur contrôle redondance cyclique",
     8 =>  "8 | ZIPCLOSED | conteneur de l'archive fermé",
     9 =>  "9 | NOENT | pas de fichier",
    10 => "10 | EXISTS | fichier déjà existant",
    11 => "11 | OPEN | fichier impossible à ouvrir",
    12 => "12 | TMPOPEN | échec création fichier temporaire",
    13 => "13 | ZLIB | erreur Zlib",
    14 => "14 | MEMORY | défaillance allocation mémoire",
    15 => "15 | CHANGED | entrée modifiée",
    16 => "16 | COMPNOTSUPP | méthode de compression non supportée",
    17 => "17 | EOF | fin de fichier prématurée",
    18 => "18 | INVAL | argument invalide",
    19 => "19 | NOZIP | n'est pas une archive zip",
    20 => "20 | INTERNAL | erreur interne",
    21 => "21 | INCONS | archive incohérente",
    22 => "22 | REMOVE | fichier impossible à supprimer",
    23 => "23 | DELETED | entrée supprimée"
  );

  // //////////////////////////////////////////////////
  // Méthodes privées (internes)
  // //////////////////////////////////////////////////

  /**
   * Liste les noms des fichiers contenus dans un dossier, sans le contenu temporaire ou personnel.
   * 
   * @param string   $dossier
   * @return array
   */
  private static function lister_contenu_dossier_sources_publiques($dossier)
  {
    return array_diff( scandir($dossier) , array('.','..','__private','__tmp','webservices','.svn') );
  }

  /**
   * Vider un dossier ne contenant que d'éventuels fichiers.
   * 
   * @param string   $dossier
   * @return void
   */
  private static function vider_dossier($dossier)
  {
    if(is_dir($dossier))
    {
      $tab_fichier = FileSystem::lister_contenu_dossier($dossier);
      $ds = (substr($dossier,-1)==DS) ? '' : DS ;
      foreach($tab_fichier as $fichier_nom)
      {
        unlink($dossier.$ds.$fichier_nom);
      }
    }
  }

  /**
   * Fabriquer le md5 d'un fichier pour le comparer à ceux d'une archive.
   * Volontairement non utilisé par ServeurCommunautaire::fabriquer_chaine_integrite() car un peu différent.
   * 
   * @param string
   * @return string
   */
  private static function fabriquer_md5_file($fichier)
  {
    // Lors du transfert FTP de fichiers, il arrive que les \r\n en fin de ligne soient convertis en \n, ce qui fait que md5_file() renvoie un résultat différent.
    // Pour y remédier on utilise son équivalent md5(file_get_contents()) couplé à un remplacement des caractères de fin de ligne.
    return md5( str_replace( array("\r\n","\r","\n") , ' ' , file_get_contents($fichier) ) );
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Afficher la seule fin intéressante d'un chemin, c'est à dire sans le chemin menant jusqu'au répertoire d'installation de SACoche.
   * 
   * @param string   $chemin
   * @return string
   */
  public static function fin_chemin($chemin)
  {
    return substr($chemin,LONGUEUR_CHEMIN_SACOCHE);
  }

  /**
   * Liste le contenu d'un dossier (fichiers et dossiers).
   * 
   * @param string   $dossier
   * @return array
   */
  public static function lister_contenu_dossier($dossier)
  {
    return array_diff( scandir($dossier) , array('.','..') );
  }

  /**
   * Tester l'existence d'un dossier, le créer, tester son accès en écriture.
   * 
   * @param     string   $dossier
   * @staticvar string   $affichage   Facultatif, n'est utilisé que lors de la procédure d'installation.
   * @return bool
   */
  public static function creer_dossier($dossier,&$affichage='')
  {
    // Le dossier existe-t-il déjà ?
    if(is_dir($dossier))
    {
      $affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin($dossier).'</b>&nbsp;&raquo; déjà en place.</label><br />'."\r\n";
      return TRUE;
    }
    @umask(0000); // Met le chmod à 666 - 000 = 666 pour les fichiers prochains fichiers créés (et à 777 - 000 = 777 pour les dossiers).
    $test = @mkdir($dossier);
    // Le dossier a-t-il bien été créé ?
    if(!$test)
    {
      $affichage .= '<label for="rien" class="erreur">Échec lors de la création du dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin($dossier).'</b>&nbsp;&raquo; : veuillez le créer manuellement.</label><br />'."\r\n";
      return FALSE;
    }
    $affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin($dossier).'</b>&nbsp;&raquo; créé.</label><br />'."\r\n";
    // Le dossier est-il accessible en écriture ?
    $test = is_writable($dossier);
    if(!$test)
    {
      $affichage .= '<label for="rien" class="erreur">Dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin($dossier).'</b>&nbsp;&raquo; inaccessible en écriture : veuillez en changer les droits manuellement.</label><br />'."\r\n";
      return FALSE;
    }
    // Si on arrive là, c'est bon...
    $affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin($dossier).'</b>&nbsp;&raquo; accessible en écriture.</label><br />'."\r\n";
    return TRUE;
  }

  /**
   * Créer un dossier s'il n'existe pas, le vider de ses éventuels fichiers sinon.
   * 
   * @param string   $dossier
   * @return void
   */
  public static function creer_ou_vider_dossier($dossier)
  {
    if(!is_dir($dossier))
    {
      FileSystem::creer_dossier($dossier);
    }
    else
    {
      FileSystem::vider_dossier($dossier);
    }
  }

  /**
   * Supprimer un dossier, après avoir effacé récursivement son contenu.
   * 
   * @param string   $dossier
   * @return void
   */
  public static function supprimer_dossier($dossier)
  {
    if(is_dir($dossier))
    {
      $tab_contenu = FileSystem::lister_contenu_dossier($dossier);
      $ds = (substr($dossier,-1)==DS) ? '' : DS ;
      foreach($tab_contenu as $contenu)
      {
        $chemin_contenu = $dossier.$ds.$contenu;
        if(is_dir($chemin_contenu))
        {
          FileSystem::supprimer_dossier($chemin_contenu);
        }
        else
        {
          unlink($chemin_contenu);
        }
      }
      rmdir($dossier);
    }
  }

  /**
   * Recense récursivement les dossiers présents et les md5 des fichiers (utilisé pour la maj automatique par le webmestre).
   * 
   * @param string   $dossier
   * @param int      $longueur_prefixe   longueur de $dossier lors du premier appel
   * @param string   $indice   "avant" ou "apres"
   * @param bool     $calc_md5   TRUE par défaut, FALSE si le fichier est son MD5
   * @return void
   */
  public static function analyser_dossier($dossier,$longueur_prefixe,$indice,$calc_md5=TRUE)
  {
    $tab_contenu = FileSystem::lister_contenu_dossier_sources_publiques($dossier);
    $ds = (substr($dossier,-1)==DS) ? '' : DS ;
    foreach($tab_contenu as $contenu)
    {
      $chemin_contenu = $dossier.$ds.$contenu;
      if(is_dir($chemin_contenu))
      {
        FileSystem::analyser_dossier($chemin_contenu,$longueur_prefixe,$indice,$calc_md5);
      }
      else
      {
        $_SESSION['tmp']['fichier'][substr($chemin_contenu,$longueur_prefixe)][$indice] = ($calc_md5) ? FileSystem::fabriquer_md5_file($chemin_contenu) : file_get_contents($chemin_contenu) ;
      }
    }
    $_SESSION['tmp']['dossier'][substr($dossier,$longueur_prefixe)][$indice] = TRUE;
  }

  /**
   * Ecrire du contenu dans un fichier, exit() en cas d'erreur
   * 
   * @param string   $fichier_chemin
   * @param string   $fichier_contenu
   * @param int      facultatif ; si constante FILE_APPEND envoyée, alors ajoute en fin de fichier au lieu d'écraser le contenu
   * @return TRUE    par compatibilité avec ecrire_fichier_si_possible()
   */
  public static function ecrire_fichier($fichier_chemin,$fichier_contenu,$file_append=0)
  {
    @umask(0000); // Met le chmod à 666 - 000 = 666 pour les fichiers prochains fichiers créés (et à 777 - 000 = 777 pour les dossiers).
    $test_ecriture = @file_put_contents($fichier_chemin,$fichier_contenu,$file_append);
    if($test_ecriture===FALSE)
    {
      exit('Erreur : problème lors de l\'écriture du fichier '.FileSystem::fin_chemin($fichier_chemin).' !');
    }
    return TRUE;
  }

  /**
   * Ecrire du contenu dans un fichier, retourne un booléen indiquant la réussite de l'opération
   * 
   * @param string   $fichier_chemin
   * @param string   $fichier_contenu
   * @return bool
   */
  public static function ecrire_fichier_si_possible($fichier_chemin,$fichier_contenu)
  {
    @umask(0000); // Met le chmod à 666 - 000 = 666 pour les fichiers prochains fichiers créés (et à 777 - 000 = 777 pour les dossiers).
    $test_ecriture = @file_put_contents($fichier_chemin,$fichier_contenu,$file_append);
    return ($test_ecriture===FALSE) ? FALSE : TRUE ;
  }

  /**
   * Ecrire un fichier "index.htm" vide dans un dossier pour éviter le lsitage du répertoire.
   * 
   * @param string   $dossier_chemin   Chemin jusqu'au dossier, SANS le séparateur final.
   * @param bool     $obligatoire      Facultatif, TRUE par défaut.
   * @return bool
   */
  public static function ecrire_fichier_index($dossier_chemin,$obligatoire=TRUE)
  {
    $fichier_chemin  = $dossier_chemin.DS.'index.htm';
    $fichier_contenu = 'Circulez, il n\'y a rien à voir par ici !';
    if($obligatoire) return FileSystem::ecrire_fichier( $fichier_chemin , $fichier_contenu );
    else return FileSystem::ecrire_fichier_si_possible( $fichier_chemin , $fichier_contenu );
  }

  /**
   * Fabriquer ou mettre à jour le fichier de configuration de l'hébergement (gestion par le webmestre)
   * 
   * @param array tableau $constante_nom => $constante_valeur des paramètres à modifier (sinon, on prend les constantes déjà définies)
   * @return void
   */
  public static function fabriquer_fichier_hebergeur_info($tab_constantes_modifiees)
  {
    $tab_constantes_requises = array(
      'HEBERGEUR_INSTALLATION',
      'HEBERGEUR_DENOMINATION',
      'HEBERGEUR_UAI',
      'HEBERGEUR_ADRESSE_SITE',
      'HEBERGEUR_LOGO',
      'CNIL_NUMERO',
      'CNIL_DATE_ENGAGEMENT',
      'CNIL_DATE_RECEPISSE',
      'WEBMESTRE_NOM',
      'WEBMESTRE_PRENOM',
      'WEBMESTRE_COURRIEL',
      'WEBMESTRE_PASSWORD_MD5',
      'WEBMESTRE_ERREUR_DATE',
      'SERVEUR_PROXY_USED',
      'SERVEUR_PROXY_NAME',
      'SERVEUR_PROXY_PORT',
      'SERVEUR_PROXY_TYPE',
      'SERVEUR_PROXY_AUTH_USED',
      'SERVEUR_PROXY_AUTH_METHOD',
      'SERVEUR_PROXY_AUTH_USER',
      'SERVEUR_PROXY_AUTH_PASS',
      'FICHIER_TAILLE_MAX',
      'FICHIER_DUREE_CONSERVATION',
      'CHEMIN_LOGS_PHPCAS'
    );
    $fichier_contenu = '<?php'."\r\n";
    $fichier_contenu.= '// Informations concernant l\'hébergement et son webmestre (n°UAI uniquement pour une installation de type mono-structure)'."\r\n";
    foreach($tab_constantes_requises as $constante_nom)
    {
      if(isset($tab_constantes_modifiees[$constante_nom]))
      {
        $constante_valeur = $tab_constantes_modifiees[$constante_nom];
      }
      else if(defined($constante_nom))
      {
        $constante_valeur = constant($constante_nom);
      }
      else
      {
        // Il est déjà arrivé que cette fonction soit appelée de façon inopportune :
        // le fichier existe mais il n'est pas lu (problème du système de fichier ?), 
        // du coup on tente de le réécrire avec des constantes vides (puisque non récupérées),
        // ce qui pose un gros souci (fichier de configuration de l'hébergement corrompu).
        exit_error( 'Constante manquante' /*titre*/ , 'Pour la mise à jour du fichier de configuration de l\'hébergement, la constante "'.$constante_nom.'" s\'avère manquer.<br />Par sécurité, arrêt de l\'application.' /*contenu*/ );
      }
      $espaces = str_repeat(' ',26-strlen($constante_nom));
      $quote = '\'';
      // var_export() permet d'échapper \ ' et inclus des ' autour.
      $fichier_contenu.= 'define('.$quote.$constante_nom.$quote.$espaces.','.var_export((string)$constante_valeur,TRUE).');'."\r\n";
    }
    $fichier_contenu.= '?>'."\r\n";
    FileSystem::ecrire_fichier(CHEMIN_FICHIER_CONFIG_INSTALL,$fichier_contenu);
  }

  /**
   * Fabriquer ou mettre à jour le fichier de connexion à la base (soit celle du webmestre, soit celle d'un établissement).
   * 
   * @param int    $base_id   0 dans le cas d'une install mono-structure ou de la base du webmestre
   * @param string $BD_host
   * @param string $BD_name
   * @param string $BD_user
   * @param string $BD_pass
   * @return void
   */
  public static function fabriquer_fichier_connexion_base($base_id,$BD_host,$BD_port,$BD_name,$BD_user,$BD_pass)
  {
    if( (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id>0) )
    {
      $fichier_nom = CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_structure_'.$base_id.'.php';
      $fichier_descriptif = 'Paramètres MySQL de la base de données SACoche n°'.$base_id.' (installation multi-structures).';
      $prefixe = 'STRUCTURE';
    }
    elseif(HEBERGEUR_INSTALLATION=='mono-structure')
    {
      $fichier_nom = CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_structure.php';
      $fichier_descriptif = 'Paramètres MySQL de la base de données SACoche (installation mono-structure).';
      $prefixe = 'STRUCTURE';
    }
    else // (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id==0)
    {
      $fichier_nom = CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_webmestre.php';
      $fichier_descriptif = 'Paramètres MySQL de la base de données SACoche du webmestre (installation multi-structures).';
      $prefixe = 'WEBMESTRE';
    }
    $fichier_contenu  = '<?php'."\r\n";
    $fichier_contenu .= '// '.$fichier_descriptif."\r\n";
    $fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_HOST\',\''.$BD_host.'\');  // Nom d\'hôte / serveur'."\r\n";
    $fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_PORT\',\''.$BD_port.'\');  // Port de connexion'."\r\n";
    $fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_NAME\',\''.$BD_name.'\');  // Nom de la base'."\r\n";
    $fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_USER\',\''.$BD_user.'\');  // Nom d\'utilisateur'."\r\n";
    $fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_PASS\',\''.$BD_pass.'\');  // Mot de passe'."\r\n";
    $fichier_contenu .= '?>'."\r\n";
    FileSystem::ecrire_fichier($fichier_nom,$fichier_contenu);
  }

  /**
   * Effacer d'anciens fichiers temporaires sur le serveur.
   * 
   * @param string   $dossier      le dossier à vider
   * @param int      $nb_minutes   le délai d'expiration en minutes
   * @return void
   */
  public static function effacer_fichiers_temporaires($dossier,$nb_minutes)
  {
    if(is_dir($dossier))
    {
      $date_limite = time() - $nb_minutes*60;
      $tab_fichier = FileSystem::lister_contenu_dossier($dossier);
      $ds = (substr($dossier,-1)==DS) ? '' : DS ;
      foreach($tab_fichier as $fichier_nom)
      {
        $fichier = $dossier.$ds.$fichier_nom;
        $extension = pathinfo($fichier,PATHINFO_EXTENSION);
        $date_unix = @filemtime($fichier); // @ car dans de rares cas le fichier est simultanément supprimé par un autre processus
        if( (is_file($fichier)) && ($date_unix<$date_limite) && ($extension!='htm') )
        {
          unlink($fichier);
        }
      }
    }
  }

  /**
   * Nettoyer les fichiers temporaires
   * Fonction appeler lors d'une nouvelle connexion d'un utilisateur (pas mis en page d'accueil sinon c'est appelé trop souvent)
   * 
   * @param int       $BASE
   * @return void
   */
  public static function nettoyer_fichiers_temporaires($BASE)
  {
    // On essaye de faire en sorte que plusieurs nettoyages ne se lancent pas simultanément (sinon on trouve des warning php dans les logs)
    $fichier_lock = CHEMIN_DOSSIER_TMP.'lock.txt';
    if(!file_exists($fichier_lock))
    {
      FileSystem::ecrire_fichier($fichier_lock,'');
      // On verifie que certains sous-dossiers existent : 'devoir' n'a été ajouté qu'en mars 2012, 'officiel' n'a été ajouté qu'en mai 2012, 'cookie' et 'rss' étaient oublié depuis le formulaire Sésamath ('badge' a priori c'est bon)
      $tab_sous_dossier = array( 'devoir' , 'officiel' , 'cookie'.DS.$BASE , 'devoir'.DS.$BASE , 'officiel'.DS.$BASE , 'rss'.DS.$BASE );
      foreach($tab_sous_dossier as $sous_dossier)
      {
        $dossier = CHEMIN_DOSSIER_TMP.$sous_dossier;
        if(!is_dir($dossier))
        {
          FileSystem::creer_dossier($dossier);
          FileSystem::ecrire_fichier($dossier.DS.'index.htm','Circulez, il n\'y a rien à voir par ici !');
        }
      }
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_LOGINPASS      ,     10); // Nettoyer ce dossier des fichiers antérieurs à 10 minutes
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_EXPORT         ,     60); // Nettoyer ce dossier des fichiers antérieurs à  1 heure
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_DUMP           ,     60); // Nettoyer ce dossier des fichiers antérieurs à  1 heure
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_IMPORT         ,  10080); // Nettoyer ce dossier des fichiers antérieurs à  1 semaine
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_TMP            , 219000); // Nettoyer ce dossier des fichiers antérieurs à  6 mois
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_RSS.$BASE      ,  43800); // Nettoyer ce dossier des fichiers antérieurs à  1 mois
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_OFFICIEL.$BASE , 438000); // Nettoyer ce dossier des fichiers antérieurs à 10 mois
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_BADGE.$BASE    , 481800); // Nettoyer ce dossier des fichiers antérieurs à 11 mois
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_COOKIE.$BASE   , 525600); // Nettoyer ce dossier des fichiers antérieurs à  1 an
      if(defined('FICHIER_DUREE_CONSERVATION')) // Une fois tout a été supprimé sans raison claire : nettoyage simultané avec une mise à jour ?
      {
        FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_DEVOIR.$BASE , 43800*FICHIER_DUREE_CONSERVATION); // Nettoyer ce dossier des fichiers antérieurs à la date fixée par le webmestre (1 an par défaut)
      }
      unlink($fichier_lock);
    }
    // Si le fichier témoin du nettoyage existe, on vérifie que sa présence n'est pas anormale (cela s'est déjà produit...)
    else
    {
      if( time() - filemtime($fichier_lock) > 30 )
      {
        unlink($fichier_lock);
      }
    }
  }

  /**
   * zipper_fichiers
   * Zipper les fichiers de svg
   *
   * @param string $dossier_fichiers_a_zipper
   * @param string $dossier_zip_final
   * @param string $fichier_zip_nom
   * @return void
   */

  public static function zipper_fichiers($dossier_fichiers_a_zipper,$dossier_zip_final,$fichier_zip_nom)
  {
    $zip = new ZipArchive();
    $ds = (substr($dossier_zip_final,-1)==DS) ? '' : DS ;
    $zip->open($dossier_zip_final.$ds.$fichier_zip_nom, ZIPARCHIVE::CREATE);
    $tab_fichier = FileSystem::lister_contenu_dossier($dossier_fichiers_a_zipper);
    $ds = (substr($dossier_fichiers_a_zipper,-1)==DS) ? '' : DS ;
    foreach($tab_fichier as $fichier_sql_nom)
    {
      $zip->addFile($dossier_fichiers_a_zipper.$ds.$fichier_sql_nom,$fichier_sql_nom);
    }
    $zip->close();
  }

  /**
   * Zipper un fichier seul.
   * Exit sur une phrase d'erreur si problème
   * 
   * @param string   $chemin_fichier_zip   chemin et nom de l'archive zip à créer
   * @param string   $fichier_nom          nom du fichier dans l'archive zip
   * @param string   $fichier_contenu      contenu à zipper
   * @return void
   */
  public static function zip( $chemin_fichier_zip , $fichier_nom , $fichier_contenu )
  {
    $zip = new ZipArchive();
    $result_open = $zip->open($chemin_fichier_zip, ZIPARCHIVE::CREATE);
    if($result_open!==TRUE)
    {
      exit('Problème de création de l\'archive ZIP ('.FileSystem::$tab_zip_error[$result_open].') !');
    }
    $zip->addFromString($fichier_nom,$fichier_contenu);
    $zip->close();
  }

  /**
   * Dezipper une archive contenant un seul fichier.
   * Exit sur une phrase d'erreur si problème
   * Le chemin d'extraction n'est pas indiqué : c'est CHEMIN_DOSSIER_IMPORT (idem pour le chemin du fichier final)
   * 
   * @param string   $chemin_fichier_zip   chemin et nom de l'archive zip
   * @param string   $fichier_nom_archive  nom du fichier à rechercher dans l'archive zip
   * @param string   $chemin_nom_final     chemin du fichier une fois extrait
   * @return void
   */
  public static function unzip_one($chemin_fichier_zip,$fichier_nom_archive,$chemin_nom_final)
  {
    $zip = new ZipArchive();
    $result_open = $zip->open($chemin_fichier_zip);
    if($result_open!==TRUE)
    {
      exit('Problème d\'ouverture de l\'archive ZIP ('.FileSystem::$tab_zip_error[$result_open].') !');
    }
    if($zip->extractTo(CHEMIN_DOSSIER_IMPORT,$fichier_nom_archive)!==TRUE)
    {
      exit('Fichier '.$fichier_nom_archive.' non trouvé dans l\'archive ZIP !');
    }
    $zip->close();
    if(!rename(CHEMIN_DOSSIER_IMPORT.$fichier_nom_archive , $chemin_nom_final))
    {
      exit('Le fichier extrait n\'a pas pu être enregistré sur le serveur.');
    }
  }

  /**
   * Dezipper une archive contenant un ensemble de fichiers dans un dossier, avec son arborescence.
   * 
   * Inspiré de http://fr.php.net/manual/fr/ref.zip.php#79057
   * A l'origine pour remplacer $zip = new ZipArchive(); $result_open = $zip->open($fichier_import); qui plante sur le serveur Nantais s'il y a trop de fichiers dans le zip (code erreur "5 READ").
   * Mais il s'avère finalement que ça ne fonctionne pas mieux...
   * 
   * @param string   $chemin_fichier_zip
   * @param string   $dossier_dezip
   * @param bool     $use_ZipArchive   FALSE permet de nettoyer les noms des fichiers extraits : à préférer donc
   * @return int     code d'erreur (0 si RAS)
   */
  public static function unzip($chemin_fichier_zip,$dossier_dezip,$use_ZipArchive)
  {
    // Utiliser la classe ZipArchive http://fr.php.net/manual/fr/class.ziparchive.php (PHP 5 >= 5.2.0, PECL zip >= 1.1.0)
    if($use_ZipArchive)
    {
      $zip = new ZipArchive();
      $result_open = $zip->open($chemin_fichier_zip);
      if($result_open!==TRUE)
      {
        return $result_open;
      }
      $zip->extractTo($dossier_dezip);
      $zip->close();
    }
    // Utiliser les fonctions Zip http://fr.php.net/manual/fr/ref.zip.php (PHP 4 >= 4.1.0, PHP 5 >= 5.2.0, PECL zip >= 1.0.0)
    else
    {
      $ds = (substr($dossier_dezip,-1)==DS) ? '' : DS ;
      $contenu_zip = zip_open($chemin_fichier_zip);
      if(!is_resource($contenu_zip))
      {
        return $contenu_zip;
      }
      while( $zip_element = zip_read($contenu_zip) )
      {
        zip_entry_open($contenu_zip, $zip_element);
        if (substr(zip_entry_name($zip_element), -1) == DS)
        {
          // C'est un dossier
          mkdir( $dossier_dezip.$ds.zip_entry_name($zip_element) );
        }
        else
        {
          // C'est un fichier
          file_put_contents( $dossier_dezip.$ds.Clean::zip_filename(zip_entry_name($zip_element)) , zip_entry_read($zip_element,zip_entry_filesize($zip_element)) );
        }
        zip_entry_close($zip_element);
      }
      zip_close($contenu_zip);
    }
    // Tout s'est bien passé
    return 0;
  }

  /**
   * Récupérer un fichier uploadé, effectuer les vérifications demandées, et l'enregistrer dans le dossier et sous le nom indiqué.
   * 
   * @param string   $fichier_final_chemin
   * @param string   $fichier_final_nom           (facultatif) Si pas transmis, ce sera le nom du fichier envoyé (nettoyé) ; si transmis, peut comporter ".<EXT>" qui sera remplacé par l'extension du fichier réceptionné.
   * @param array    $tab_extensions_autorisees   (facultatif) tableau des extensions autorisées
   * @param array    $tab_extensions_interdites   (facultatif) tableau des extensions interdites
   * @param int      $taille_maxi                 (facultatif) en Ko
   * @param string   $filename_in_zip             (facultatif) nom d'un fichier contenu dans le fichier zippé reçu, à extraire au passage
   * @return TRUE|string                          TRUE ou un message d'erreur
   */
  public static function recuperer_upload( $fichier_final_chemin , $fichier_final_nom=NULL , $tab_extensions_autorisees=NULL , $tab_extensions_interdites=NULL , $taille_maxi=NULL , $filename_in_zip=NULL )
  {
    // Si le fichier dépasse les capacités du serveur, il se peut que $_FILES ne soit même pas renseigné.
    if(!isset($_FILES['userfile']))
    {
      return 'Erreur : problème de transfert ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload();
    }
    // Si $_FILES est renseigné, il se peut qu'il y ait quand même eu un dépassement des limites.
    $tab_file = $_FILES['userfile'];
    $fichier_tmp_nom    = $tab_file['name'];
    $fichier_tmp_chemin = $tab_file['tmp_name'];
    $fichier_tmp_taille = $tab_file['size']/1000; // Conversion octets => Ko
    $fichier_tmp_erreur = $tab_file['error'];
    if( (!file_exists($fichier_tmp_chemin)) || (!$fichier_tmp_taille) || ($fichier_tmp_erreur) )
    {
      return 'Problème de récupération ! Fichier trop lourd ? '.InfoServeur::minimum_limitations_upload();
    }
    // Vérification d'une sécurité sur le nom
    if($fichier_tmp_nom{0}=='.')
    {
      return 'Le nom du fichier ne doit pas commencer par un point !';
    }
    // Vérification de l'extension
    $extension = strtolower(pathinfo($fichier_tmp_nom,PATHINFO_EXTENSION));
    if( ($tab_extensions_autorisees!==NULL) && (!in_array($extension,$tab_extensions_autorisees)) )
    {
      return 'L\'extension du fichier transmis n\'est pas conforme !';
    }
    if( ($tab_extensions_interdites!==NULL) && (in_array($extension,$tab_extensions_interdites)) )
    {
      return 'L\'extension du fichier transmis est interdite !';
    }
    // Vérification de la taille
    if( ($taille_maxi!==NULL) && ($fichier_tmp_taille>$taille_maxi) )
    {
      $conseil = '';
      if( ($tab_extensions_autorisees!==NULL) && (in_array('jpg',$tab_extensions_autorisees)) )
      {
        $conseil = (($extension=='jpg')||($extension=='jpeg')) ? ' : réduisez les dimensions de l\'image' : ' : convertissez l\'image au format JPEG' ;
      }
      return 'Le fichier dépasse les '.$taille_maxi.' Ko autorisés'.$conseil.' !';
    }
    // On rapatrie le fichier dans l'arborescence SACoche, en en dézippant un fichier précis si demandé
    $fichier_final_nom = ($fichier_final_nom) ? str_replace('.<EXT>','.'.$extension,$fichier_final_nom) : Clean::fichier($fichier_tmp_nom);
    if( ($extension!='zip') || ($filename_in_zip===NULL) )
    {
      if(!move_uploaded_file($fichier_tmp_chemin,$fichier_final_chemin.$fichier_final_nom))
      {
        return 'Le fichier n\'a pas pu être enregistré sur le serveur.';
      }
    }
    else
    {
      // Dézipper le fichier (on considère alors que c'est un zip venant de SACoche et contenant import_validations.xml)
      if(extension_loaded('zip')!==TRUE)
      {
        return 'Le serveur ne gère pas les fichiers ZIP ! Renvoyez votre fichier sans compression.';
      }
      // Remarque : la ligne suivante peut balancer un exit sans se poser de questions
      FileSystem::unzip_one( $fichier_tmp_chemin , $filename_in_zip , $fichier_final_chemin.$fichier_final_nom );
    }
    // C'est bon :)
    FileSystem::$file_upload_name = $fichier_tmp_nom;
    FileSystem::$file_saved_name  = $fichier_final_nom;
    return TRUE;
  }

}
?>