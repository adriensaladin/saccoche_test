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
 * Fonctions de nettoyage des chaînes avant stockage ou affichage.
 * 
 * Les conseils à suivre que l'on donne génréralement sont les suivants :
 * + Desactiver magic_quotes_gpc() pour ne pas avoir à jouer conditionnellement avec stripslashes() et addslashes()
 * + Avant stockage dans la BDD utiliser mysql_real_escape_string() et intval()
 * + Avant affichage utiliser htmlspecialchars() couplé à nl2br() si on veut les sauts de ligne (hors textarea)
 * Ici c'est inutile, les fonctions mises en place et la classe PDO s'occupent de tout.
 * 
 */

/*
 * Attention ! strtr() renvoie n'importe quoi en UTF-8 car il fonctionne octet par octet et non caractère par caractère, or l'UTF-8 est multi-octets...
*/
define( 'LETTER_CHARS'   , utf8_decode(  '0123456789_abcdefghijklmnopqrstuvwxyz') );
define( 'FILENAME_CHARS' , utf8_decode('-.0123456789_abcdefghijklmnopqrstuvwxyz') );
define( 'LATIN1_LC_CHARS' , utf8_decode('abcdefghijklmnopqrstuvwxyzàáâãäåæçèéêëìíîïñòóôõöœøŕšùúûüýÿžðþ') );
define( 'LATIN1_UC_CHARS' , utf8_decode('ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖŒØŔŠÙÚÛÜÝŸŽÐÞ') );
define( 'LATIN1_YES_ACCENT' , utf8_decode('ÀÁÂÃÄÅàáâãäåÞþÇçÐðÈÉÊËèéêëÌÍÎÏìíîïÑñÒÓÔÕÖØòóôõöøŔŕŠšßÙÚÛÜùúûüÝŸýÿŽž') );
define( 'LATIN1_NOT_ACCENT' , utf8_decode('AAAAAAaaaaaaBbCcDdEEEEeeeeIIIIiiiiNnOOOOOOooooooRrSssUUUUuuuuYYyyZz') );

class Clean
{

  // //////////////////////////////////////////////////
  // Méthodes privées (internes)
  // //////////////////////////////////////////////////

  /**
   * Equivalent de "strtoupper()" pour mettre en majuscules y compris les caractères accentués
   *
   * @param string
   * @return string
   */
  private static function perso_strtoupper($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_UPPER,'UTF-8') : strtr($text,LATIN1_LC_CHARS,LATIN1_UC_CHARS) ;
  }

  /**
   * Equivalent de "strtolower()" pour mettre en minuscules y compris les caractères accentués
   *
   * @param string
   * @return string
   */
  private static function perso_strtolower($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_LOWER,'UTF-8') : strtr($text,LATIN1_UC_CHARS,LATIN1_LC_CHARS) ;
  }

  /**
   * Séparer les caractères ligaturés
   *
   * @param string
   * @return string
   */
  private static function ligatures($text)
  {
    $bad = array('Æ' ,'æ' ,'Œ' ,'œ' );
    $bon = array('AE','ae','OE','oe');
    return str_replace($bad,$bon,$text);
  }

  /**
   * Enlever les symboles embétants
   *
   * @param string
   * @return string
   */
  private static function symboles($text)
  {
    $bad = array('&','<','>','\\','"','\'','/','`','’');
    $bon = '';
    return str_replace($bad,$bon,$text);
  }

  /**
   * Ne conserver que les lettres minuscules | chiffres et supprimer le reste
   *
   * @param string
   * @return string
   */
  private static function only_letters($text)
  {
    $lettres = (perso_mb_detect_encoding_utf8($text)) ? utf8_decode($text) : $text ;
    if(strlen($lettres))
    {
      $tab_lettres = str_split($lettres);
      foreach($tab_lettres as $key => $lettre)
      {
        $tab_lettres[$key] = (strpos(LETTER_CHARS,$lettre)!==FALSE) ? $lettre : '' ;
      }
      $lettres = implode('',$tab_lettres);
    }
    return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode($lettres) : $lettres ;
  }

  /**
   * Ne conserver que les lettres minuscules | chiffres points et tirets, et mettre des tirets pour le reste
   *
   * @param string
   * @return string
   */
  private static function only_filechars($text)
  {
    $lettres = (perso_mb_detect_encoding_utf8($text)) ? utf8_decode($text) : $text ;
    if(strlen($lettres))
    {
      $tab_lettres = str_split($lettres);
      foreach($tab_lettres as $key => $lettre)
      {
        $tab_lettres[$key] = (strpos(FILENAME_CHARS,$lettre)!==FALSE) ? $lettre : '-' ;
      }
      $lettres = implode('',$tab_lettres);
    }
    return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode($lettres) : $lettres ;
  }

  /**
   * Abréger une expression dépassant le nb de caractères autorisés de façon intelligente.
   * Ainsi le nom de famille "Froilán de Marichalar y de Borbón" ne deviendra pas
   * "Froilán de Marichalar y d" mais "Froilán de M. y de B."
   *
   * @param string
   * @param int   $longueur_totale_maxi
   * @return string
   */
  private static function tronquer_chaine($texte,$longueur_totale_maxi)
  {
    $nb_car = mb_strlen($texte);
    // Expression trop longue
    if($nb_car>$longueur_totale_maxi)
    {
      // On la coupe en morceaux placés dans $tab_sections
      $tab_sections = array();
      $i_section = -1;
      $liste_autres = ' -.\'"`&~,';
      $car_is_autre = NULL;
      for( $i_car=0 ; $i_car<$nb_car ; $i_car++ )
      {
        $car = $texte{$i_car};
        $test_this_car_autre = (strpos($liste_autres,$car)!==FALSE) ? TRUE : FALSE ;
        if($test_this_car_autre!==$car_is_autre)
        {
          $i_section++;
          $car_is_autre = $test_this_car_autre;
          $tab_sections[$i_section] = $car;
        }
        else
        {
          $tab_sections[$i_section] .= $car;
        }
      }
      // On abrège les morceaux nécessaires jusqu'à avoir une taille raisonnable
      $longueur_mot_maxi = 3;
      $nb_sections = count($tab_sections);
      for( $i_section=$nb_sections-1 ; $i_section>=0 ; $i_section-- )
      {
        $longueur_section = mb_strlen($tab_sections[$i_section]);
        if($longueur_section>$longueur_mot_maxi)
        {
          $tab_sections[$i_section] = $tab_sections[$i_section]{0}.'.';
          $nb_car = $nb_car - $longueur_section + 2 ;
          if($nb_car<=$longueur_totale_maxi)
          {
            break;
          }
        }
      }
      // On regroupe les morceaux
      $texte = implode('',$tab_sections);
    }
    return $texte;
  }

  /*
   * Enlever les guillemets éventuels entourants des champs dans un fichier csv (fonction utilisée avec "array_map()")
   * 
   * @param string
   * @return string
   */
  private static function retirer_guillemets($text)
  {
    if(mb_strlen($text)>1)
    {
      $tab_guillemets = array('"','\'');
      $premier = mb_substr($text,0,1);
      $dernier = mb_substr($text,-1);
      if( ($premier==$dernier) && (in_array($premier,$tab_guillemets)) )
      {
        $text = mb_substr($text,1,-1);
      }
    }
    return $text;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques de bas niveau
  // //////////////////////////////////////////////////

  /*
   * Equivalent de "ucwords()" adaptée aux caractères accentués et aux expressions séparées par autre chose qu'une espace (virgule, point, tiret, parenthèse...)
   * Appelé depuis /pages/administrateur_fichier_identifiant.ajax.php
   * 
   * @param string
   * @return string
   */
  public static function perso_ucwords($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? mb_convert_case($text,MB_CASE_TITLE,'UTF-8') : trim(preg_replace('/([^a-z'.LATIN1_LC_CHARS.']|^)([a-z'.LATIN1_LC_CHARS.'])/e', 'stripslashes("$1".Clean::perso_strtoupper("$2"))', Clean::perso_strtolower($text)));
  }

  /*
   * Enlever les accents
   * Appelé depuis /pages/administrateur_fichier_identifiant.ajax.php
   * 
   * @param string
   * @return string
   */
  public static function accents($text)
  {
    return (perso_mb_detect_encoding_utf8($text)) ? utf8_encode(strtr(utf8_decode($text),LATIN1_YES_ACCENT,LATIN1_NOT_ACCENT)) : strtr($text,LATIN1_YES_ACCENT,LATIN1_NOT_ACCENT) ;
  }

  // //////////////////////////////////////////////////
  // Méthodes publiques principales
  // //////////////////////////////////////////////////

  /*
    Les fonctions centrales à modifier sans avoir à modifier tous les scripts.
    En général il s'agit d'harmoniser les données de la base ou d'aider l'utilisateur (en évitant les problèmes de casse par exemple).
    Le login est davantage nettoyé car il y a un risque d'engendrer des comportements incertains (à l'affichage ou à l'enregistrement) avec les applications externes (pmwiki, phpbb...).
  */
  public static function login($text)        { return str_replace(' ','', Clean::perso_strtolower( Clean::accents( Clean::ligatures( Clean::symboles( trim($text) ) ) ) ) ); }
  public static function fichier($text)      { return Clean::only_filechars( Clean::perso_strtolower( Clean::accents( Clean::ligatures( trim($text) ) ) ) ); }
  public static function id($text)           { return Clean::only_letters(   Clean::perso_strtolower( Clean::accents( Clean::ligatures( trim($text) ) ) ) ); }
  public static function zip_filename($text) { return Clean::fichier(iconv('CP850','UTF-8',$text)); } //  filenames stored in the ZIP archives created on non-Unix systems are encoded in CP850 http://fr.php.net/manual/fr/function.zip-entry-name.php#87130
  public static function password($text)     { return trim($text); }
  public static function ref($text)          { return Clean::perso_strtoupper( trim($text) ); }
  public static function nom($text)          { return Clean::tronquer_chaine( Clean::perso_strtoupper( trim($text) ) , 25); }
  public static function uai($text)          { return Clean::perso_strtoupper( trim($text) ); }
  public static function prenom($text)       { return Clean::tronquer_chaine( Clean::perso_ucwords( trim($text) ) , 25); }
  public static function structure($text)    { return Clean::perso_ucwords( trim($text) ); }
  public static function adresse($text)      { return Clean::tronquer_chaine( Clean::perso_ucwords( trim($text) ) , 50); }
  public static function commune($text)      { return Clean::tronquer_chaine( Clean::perso_strtoupper( trim($text) ) , 45); }
  public static function pays($text)         { return Clean::tronquer_chaine( Clean::perso_strtoupper( trim($text) ) , 35); }
  public static function code($text)         { return Clean::perso_strtolower( trim($text) ); }
  public static function texte($text)        { return trim($text); }
  public static function courriel($text)     { return Clean::perso_strtolower( Clean::accents( trim($text) ) ); }
  public static function url($text)          { return Clean::perso_strtolower( trim($text) ); }
  public static function id_ent($text)       { return mb_substr( Clean::texte( (string)$text ) ,0,63 ); }
  public static function entier($text)       { return intval($text); }
  public static function decimal($text)      { return floatval(str_replace(',','.',$text)); }
  public static function txt_note($text)     { return Clean::tronquer_chaine( trim($text) , 40); }

  public static function map_entier($array)
  {
    return array_map( 'intval' , $array );
  }

  public static function map_texte($array)
  {
    return array_map( 'trim' , $array );
  }

  public static function map_quotes($array)
  {
    return array_map( 'Clean::retirer_guillemets' , $array );
  }

}
?>