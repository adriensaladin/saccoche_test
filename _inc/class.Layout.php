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

class Layout
{

  // %%%%%%%%%% Le fichier _loader.php doit avoir été appelé en amont pour définir plusieurs constantes requises. %%%%%%%%%%

  /**
   * Attributs de la classe (équivalents des "variables")
   */

  // Titre du navigateur
  private static $head_browser_title = 'SACoche';
  // Quelques constantes à adapter selon le domaine
  const META_DESCRIPTION = 'SACoche - Suivi d\'Acquisition de Compétences - Evaluation par compétences - Valider le socle commun';
  const META_KEYWORDS    = 'SACoche Sésamath évaluer évaluation compétences compétence validation valider socle commun collège points note notes Lomer';
  const META_AUTHOR      = 'Thomas Crespin pour Sésamath';
  const META_ROBOTS      = 'index,follow';
  // Tableaux
  private static $tab_css_file         = array(); // CSS Fichiers
  private static $tab_css_file_ie      = array(); // CSS Fichiers réservés à IE<9
  private static $tab_css_inline       = array(); // CSS Inline
  private static $tab_js_file          = array(); //  JS Fichiers
  private static $tab_js_file_ie       = array(); //  JS Fichiers réservés à IE<9
  private static $tab_js_inline_before = array(); //  JS Inline avant fichiers JS
  private static $tab_js_inline_after  = array(); //  JS Inline après fichiers JS

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes privées (internes)
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Méthode pour compresser ou minifier un fichier css ou js
   * 
   * Compression ou minification d'un fichier css ou js sur le serveur en production, avec date auto-insérée si besoin pour éviter tout souci de mise en cache.
   * Si pas de compression (hors PROD), ajouter un GET dans l'URL force le navigateur à mettre à jour son cache.
   * Attention cependant concernant cette dernière technique : avec les réglages standards d'Apache, ajouter un GET dans l'URL fait que beaucoup de navigateurs ne mettent pas le fichier en cache (donc il est rechargé tout le temps, même si le GET est le même) ; pas de souci si le serveur envoie un header avec une date d'expiration explicite...
   * 
   * @param string $chemin    chemin complet vers le fichier
   * @param string $methode   soit "pack" soit "mini" soit "comm"
   * @return string           chemin vers le fichier à prendre en compte (à indiquer dans la page web) ; il sera relatif si non compressé, absolu si compressé
   */
  private static function compacter($chemin,$methode)
  {
    if(substr($chemin,0,4)=='http')
    {
      // Cas d'un fichier distant
      return $chemin;
    }
    $fichier_original_chemin = $chemin;
    $fichier_original_date   = filemtime($fichier_original_chemin);
    $fichier_original_url    = $fichier_original_chemin.'?t='.$fichier_original_date;
    if(SERVEUR_TYPE == 'PROD')
    {
      // On peut se permettre d'enregistrer les js et css en dehors de leur dossier d'origine car les répertoires sont tous de mêmes niveaux.
      // En cas d'appel depuis le site du projet il faut éventuellement respecter le chemin vers le site du projet.
      $tmp_appli = ( (!defined('APPEL_SITE_PROJET')) || (strpos($chemin,'/sacoche/')!==FALSE) ) ? TRUE : FALSE ;
      // Conserver les extensions des js et css (le serveur pouvant se baser sur les extensions pour sa gestion du cache et des charsets).
      $fichier_original_extension = pathinfo($fichier_original_chemin,PATHINFO_EXTENSION);
      $fichier_chemin_sans_slash  = substr( str_replace( array('./sacoche/','./','/') , array('','','__') , $fichier_original_chemin ) , 0 , -(strlen($fichier_original_extension)+1) );
      $fichier_compact_nom        = $fichier_chemin_sans_slash.'_'.$fichier_original_date.'.'.$methode.'.'.$fichier_original_extension;
      $fichier_compact_chemin     = ($tmp_appli) ? CHEMIN_DOSSIER_TMP.$fichier_compact_nom : CHEMIN_DOSSIER_PROJET_TMP.$fichier_compact_nom ;
      $fichier_compact_url        = ($tmp_appli) ?        URL_DIR_TMP.$fichier_compact_nom :        URL_DIR_PROJET_TMP.$fichier_compact_nom ;
      $fichier_compact_date       = (is_file($fichier_compact_chemin)) ? filemtime($fichier_compact_chemin) : 0 ;
      // Sur le serveur en production, on compresse le fichier s'il ne l'est pas.
      if($fichier_compact_date<$fichier_original_date)
      {
        $fichier_original_contenu = file_get_contents($fichier_original_chemin);
        $fichier_original_contenu = utf8_decode($fichier_original_contenu); // Attention, il faut envoyer à ces classes de l'iso et pas de l'utf8.
        if( ($fichier_original_extension=='js') && ($methode=='pack') )
        {
          $myPacker = new JavaScriptPacker($fichier_original_contenu, 62, TRUE, FALSE);
          $fichier_compact_contenu = trim($myPacker->pack());
        }
        elseif( ($fichier_original_extension=='js') && ($methode=='mini') )
        {
          $fichier_compact_contenu = trim(JSMin::minify($fichier_original_contenu));
        }
        elseif( ($fichier_original_extension=='js') && ($methode=='comm') )
        {
          // Retrait des commentaires // ... et /** ... */ et /*! ... */
          // Option de recherche "s" (PCRE_DOTALL) pour inclure les retours à la lignes (@see http://fr.php.net/manual/fr/reference.pcre.pattern.modifiers.php).
          $fichier_compact_contenu = trim(
            preg_replace( '#'.'(\n)+'.'#s' , "\n" , 
            preg_replace( '#'.'// '.'(.*?)'.'\n'.'#s' , '' , 
            preg_replace( '#'.'/\*!'.'(.*?)'.'\*/'.'#s' , '' , 
            preg_replace( '#'.'/\*\*'.'(.*?)'.'\*/'.'#s' , '' , 
            $fichier_original_contenu ) ) ) ) );
        }
        elseif( ($fichier_original_extension=='css') && ($methode=='mini') )
        {
          $fichier_compact_contenu = cssmin::minify($fichier_original_contenu);
        }
        else
        {
          // Normalement on ne doit pas en arriver là... sauf à passer de mauvais paramètres à la fonction.
          $fichier_compact_contenu = $fichier_original_contenu;
        }
        $fichier_compact_contenu = utf8_encode($fichier_compact_contenu);  // On réencode donc en UTF-8...
        // Il se peut que le droit en écriture ne soit pas autorisé et que la procédure d'install ne l'ai pas encore vérifié ou que le dossier __tmp n'ait pas encore été créé.
        $test_ecriture = FileSystem::ecrire_fichier_si_possible($fichier_compact_chemin,$fichier_compact_contenu);
        return $test_ecriture ? $fichier_compact_url : $fichier_original_url ;
      }
      return $fichier_compact_url;
    }
    else
    {
      // Sur un serveur local on n'encombre pas le SVN, en DEV on garde le fichier normal pour debugguer si besoin.
      return $fichier_original_url;
    }
  }

  /**
   * Méthode pour afficher les fichiers CSS
   * 
   * @param void
   * @return string
   */
  private static function afficher_css_file()
  {
    $string = '';
    if(!empty(Layout::$tab_css_file))
    {
      foreach(Layout::$tab_css_file as $css_file)
      {
        $string .= '<link rel="stylesheet" type="text/css" href="'.$css_file.'" />'.NL;
      }
    }
    if(!empty(Layout::$tab_css_file_ie))
    {
      foreach(Layout::$tab_css_file_ie as $css_ie_file)
      {
        $string .= '<!--[if lte IE 8]><link rel="stylesheet" type="text/css" href="'.$css_ie_file.'" /><![endif]-->'.NL;
      }
    }
    return $string;
  }

  /**
   * Méthode pour afficher les fichiers JS
   * 
   * @param void
   * @return string
   */
  private static function afficher_js_file()
  {
    $string = '';
    if(!empty(Layout::$tab_js_file))
    {
      foreach(Layout::$tab_js_file as $js_file)
      {
        $string .= '<script type="text/javascript" charset="'.CHARSET.'" src="'.$js_file.'"></script>'.NL;
      }
    }
    if(!empty(Layout::$tab_js_file_ie))
    {
      foreach(Layout::$tab_js_file_ie as $js_ie_file)
      {
        $string .= '<!--[if lte IE 8]><script type="text/javascript" charset="'.CHARSET.'" src="'.$js_ie_file.'"></script><![endif]-->'.NL;
      }
    }
    return $string;
  }

  /**
   * Méthode pour afficher les CSS dans la page (personnalisations propres à la page ou à la session)
   * 
   * @param void
   * @return string
   */
  private static function afficher_css_inline()
  {
    $string = '';
    if(!empty(Layout::$tab_css_inline))
    {
      $string_css_inline = implode(NL,Layout::$tab_css_inline);
      $string .= '<style type="text/css">'.NL;
      $string .= (SERVEUR_TYPE == 'PROD') ? cssmin::minify($string_css_inline).NL : $string_css_inline.NL ;
      $string .= '</style>'.NL;
    }
    return $string;
  }

  /**
   * Méthode pour afficher les JS dans la page (données dynamiques, telles des constantes supplémentaires)
   * 
   * @param string   position   'before' | 'after'
   * @return string
   */
  private static function afficher_js_inline($position)
  {
    $string = '';
    if(!empty(Layout::${'tab_js_inline_'.$position}))
    {
      $string_js_inline = implode(NL,Layout::${'tab_js_inline_'.$position});
      $string .= '<script type="text/javascript">'.NL;
      $string .= (SERVEUR_TYPE == 'PROD') ? trim(JSMin::minify($string_js_inline)).NL : $string_js_inline.NL ;
      $string .= '</script>'.NL;
    }
    return $string;
  }

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Méthodes publiques
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * Méthode équivalent d'un __set()
   * 
   * @param string $type      "browser_title" | "css_file" | "css_file_ie" | "css_inline" | "js_file" | "js_file_ie" | "js_inline_before" | "js_inline_after"
   * @param string $contenu   la chaine (si type "inline") ou le chemin du fichier (si type "file")
   * @param string $compress  "pack" | "mini" | "comm" | NULL (pour les fichiers seulements)
   * @return void
   */
  public static function add( $type , $contenu , $compress=NULL )
  {
    switch($type)
    {
      case 'browser_title';
        Layout::$head_browser_title = $contenu;
        break;
      case 'css_inline';
      case  'js_inline_before';
      case  'js_inline_after';
        Layout::${'tab_'.$type}[] = $contenu;
        break;
      case 'css_file';
      case 'css_file_ie';
      case  'js_file';
      case  'js_file_ie';
        Layout::${'tab_'.$type}[] = Layout::compacter($contenu,$compress);
        break;
    }
  }

  /**
   * Méthode pour afficher l'ouverture de la déclaration HTML & la section HEAD du document & l'ouverture de la balise BODY
   * 
   * @param bool   $is_meta_robots  insertion (ou pas) des balises meta pour les robots
   * @param bool   $is_favicon      insertion (ou pas) des favicon
   * @param bool   $is_rss          insertion (ou pas) du flux RSS associé
   * @param bool   $add_noscript    pour ajouter une balise <noscript> au début du <body>
   * @param string $body_class      classe (facultative) de l'élément <body>
   * @return string
   */
  public static function afficher_page_entete( $is_meta_robots ,$is_favicon , $is_rss , $add_noscript=TRUE , $body_class=NULL )
  {
    header('Content-Type: text/html; charset='.CHARSET);
    // @see http://www.alsacreations.com/astuce/lire/1437-comment-interdire-le-mode-de-compatibilite-sur-ie.html
    header('X-UA-Compatible: IE=edge');
    $retour = '';
    $body_class = ($body_class) ? ' class="'.$body_class.'"' : '' ;
    $retour.= '<!DOCTYPE html>'.NL;
    $retour.= '<html lang="fr">'.NL;
    $retour.=   '<head>'.NL;
    $retour.=     '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />'.NL;
    if($is_meta_robots)
    {
      $retour.=     '<meta name="description" content="'.Layout::META_DESCRIPTION.'" />'.NL;
      $retour.=     '<meta name="keywords" content="'.Layout::META_KEYWORDS.'" />'.NL;
      $retour.=     '<meta name="author" content="'.Layout::META_AUTHOR.'" />'.NL;
      $retour.=     '<meta name="robots" content="'.Layout::META_ROBOTS.'" />'.NL;
    }
    if($is_favicon)
    {
      // @see http://realfavicongenerator.net/
      $retour.=     '<meta name="msapplication-TileColor" content="#e6e6ff" />'.NL;
      $retour.=     '<meta name="msapplication-TileImage" content="/_img/favicon/microsoft-tile-square-144x144.png" />'.NL;
      $retour.=     '<meta name="msapplication-square70x70logo"   content="/_img/favicon/microsoft-tile-square-70x70.png" />'.NL;
      // Pb validité HTML : Bad value msapplication-square144x144logo for attribute name on element meta: Keyword msapplication-square144x144logo is not registered (ce qui peut se vérifier ici : http://wiki.whatwg.org/wiki/MetaExtensions).
      // $retour.=     '<meta name="msapplication-square144x144logo" content="/_img/favicon/microsoft-tile-square-144x144.png" />'.NL;
      $retour.=     '<meta name="msapplication-square150x150logo" content="/_img/favicon/microsoft-tile-square-150x150.png" />'.NL;
      $retour.=     '<meta name="msapplication-square310x310logo" content="/_img/favicon/microsoft-tile-square-310x310.png" />'.NL;
      $retour.=     '<meta name="msapplication-wide310x150logo"   content="/_img/favicon/microsoft-tile-wide-310x150.png" />'.NL;
      $retour.=     '<link rel="icon" type="image/png" href="./favicon.png" />'.NL;
      $retour.=     '<link rel="shortcut icon"  type="images/x-icon" href="./_img/favicon/favicon.ico" />'.NL;
      $retour.=     '<link rel="apple-touch-icon-precomposed"     href="./_img/apple-touch-icon-114x114.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="57x57"   href="./_img/favicon/apple-touch-icon-57x57.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="114x114" href="./_img/favicon/apple-touch-icon-114x114.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="72x72"   href="./_img/favicon/apple-touch-icon-72x72.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="144x144" href="./_img/favicon/apple-touch-icon-144x144.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="60x60"   href="./_img/favicon/apple-touch-icon-60x60.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="120x120" href="./_img/favicon/apple-touch-icon-120x120.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="76x76"   href="./_img/favicon/apple-touch-icon-76x76.png" />'.NL;
      $retour.=     '<link rel="apple-touch-icon" sizes="152x152" href="./_img/favicon/apple-touch-icon-152x152.png" />'.NL;
      $retour.=     '<link rel="icon" type="image/png" href="./_img/favicon/favicon-196x196.png" sizes="196x196" />'.NL;
      $retour.=     '<link rel="icon" type="image/png" href="./_img/favicon/favicon-160x160.png" sizes="160x160" />'.NL;
      $retour.=     '<link rel="icon" type="image/png" href="./_img/favicon/favicon-96x96.png" sizes="96x96" />'.NL;
      $retour.=     '<link rel="icon" type="image/png" href="./_img/favicon/favicon-32x32.png" sizes="32x32" />'.NL;
      $retour.=     '<link rel="icon" type="image/png" href="./_img/favicon/favicon-16x16.png" sizes="16x16" />'.NL;
    }
    if($is_rss)
    {
      $retour.=     '<link rel="alternate" type="application/rss+xml" href="'.SERVEUR_RSS.'" title="SACoche" />'.NL;
    }
    $retour.=     '<title>'.Layout::$head_browser_title.'</title>'.NL;
    $retour.=     Layout::afficher_css_file();
    $retour.=     Layout::afficher_css_inline();
    $retour.=   '</head>'.NL;
    $retour.=   '<body'.$body_class.'>'.NL;
    if($add_noscript)
    {
      $retour.=     '<noscript>Pour afficher convenablement cette page, vous devez activer JavaScript dans votre navigateur.</noscript>'.NL;
    }
    return $retour;
  }

  /**
   * Méthode pour afficher la fermeture de la balise BODY & la fermeture de la déclaration HTML
   * 
   * @param void
   * @return string
   */
  public static function afficher_page_pied()
  {
    $retour = '';
    $retour.=     Layout::afficher_js_inline('before');
    $retour.=     Layout::afficher_js_file();
    $retour.=     Layout::afficher_js_inline('after');
    $retour.=   '</body>'.NL;
    $retour.= '</html>'.NL;
    return $retour;
  }

}
?>
