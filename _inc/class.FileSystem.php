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
      $affichage .= '<label for="rien" class="erreur">Echec lors de la création du dossier &laquo;&nbsp;<b>'.FileSystem::fin_chemin($dossier).'</b>&nbsp;&raquo; : veuillez le créer manuellement.</label><br />'."\r\n";
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
        $_SESSION['tmp']['fichier'][substr($chemin_contenu,$longueur_prefixe)][$indice] = ($calc_md5) ? fabriquer_md5_file($chemin_contenu) : file_get_contents($chemin_contenu) ;
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
   * @return void
   */
  public static function ecrire_fichier($fichier_chemin,$fichier_contenu,$file_append=0)
  {
    @umask(0000); // Met le chmod à 666 - 000 = 666 pour les fichiers prochains fichiers créés (et à 777 - 000 = 777 pour les dossiers).
    $test_ecriture = @file_put_contents($fichier_chemin,$fichier_contenu,$file_append);
    if($test_ecriture===FALSE)
    {
      exit('Erreur : problème lors de l\'écriture du fichier '.FileSystem::fin_chemin($fichier_chemin).' !');
    }
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
   * @return void
   */
  public static function ecrire_fichier_index($dossier_chemin,$obligatoire=TRUE)
  {
    $fichier_chemin  = $dossier_chemin.DS.'index.htm';
    $fichier_contenu = 'Circulez, il n\'y a rien à voir par ici !';
    if($obligatoire) FileSystem::ecrire_fichier( $fichier_chemin , $fichier_contenu );
    else FileSystem::ecrire_fichier_si_possible( $fichier_chemin , $fichier_contenu );
  }

  /**
   * Nettoie le BOM éventuel d'un fichier UTF-8.
   * Code inspiré de http://libre-d-esprit.thinking-days.net/2009/03/et-bom-le-script/
   * Ne semble plus utilisé par SACoche... ?!
   * 
   * @param string   $fichier_chemin
   * @return void
   */
  public static function deleteBOM($fichier_chemin)
  {
    $fcontenu = file_get_contents($fichier_chemin);
    if (substr($fcontenu,0,3) == "\xEF\xBB\xBF") // Ne pas utiliser mb_substr() sinon ça ne fonctionne pas
    {
      FileSystem::ecrire_fichier($fichier_chemin, substr($fcontenu,3)); // Ne pas utiliser mb_substr() sinon ça ne fonctionne pas
    }
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
        $date_unix = filemtime($fichier);
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
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_RSS.$BASE      ,  43800); // Nettoyer ce dossier des fichiers antérieurs à  1 mois
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_OFFICIEL.$BASE , 438000); // Nettoyer ce dossier des fichiers antérieurs à 10 mois
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_BADGE.$BASE    , 481800); // Nettoyer ce dossier des fichiers antérieurs à 11 mois
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_COOKIE.$BASE   , 525600); // Nettoyer ce dossier des fichiers antérieurs à  1 an
      FileSystem::effacer_fichiers_temporaires(CHEMIN_DOSSIER_DEVOIR.$BASE   , 43800*FICHIER_DUREE_CONSERVATION); // Nettoyer ce dossier des fichiers antérieurs à la date fixée par le webmestre (1 an par défaut)
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
   * Dezipper un fichier contenant un ensemble de fichiers dans un dossier, avec son arborescence.
   * 
   * Inspiré de http://fr.php.net/manual/fr/ref.zip.php#79057
   * A l'origine pour remplacer $zip = new ZipArchive(); $result_open = $zip->open($fichier_import); qui plante sur le serveur Nantais s'il y a trop de fichiers dans le zip (code erreur "5 READ").
   * Mais il s'avère finalement que ça ne fonctionne pas mieux...
   * 
   * @param string   $fichier_zip
   * @param string   $dossier_dezip
   * @return bool    $use_ZipArchive
   * @return int     code d'erreur (0 si RAS)
   */
  public static function unzip($fichier_zip,$dossier_dezip,$use_ZipArchive)
  {
    // Utiliser la classe ZipArchive http://fr.php.net/manual/fr/class.ziparchive.php (PHP 5 >= 5.2.0, PECL zip >= 1.1.0)
    if($use_ZipArchive)
    {
      $zip = new ZipArchive();
      $result_open = $zip->open($fichier_zip);
      if($result_open!==true)
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
      $contenu_zip = zip_open($fichier_zip);
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
          file_put_contents( $dossier_dezip.$ds.zip_entry_name($zip_element) , zip_entry_read($zip_element,zip_entry_filesize($zip_element)) );
        }
        zip_entry_close($zip_element);
      }
      zip_close($contenu_zip);
    }
    // Tout s'est bien passé
    return 0;
  }

}
?>