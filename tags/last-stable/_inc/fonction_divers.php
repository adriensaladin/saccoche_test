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

/*
 * Réciproque de html()
 * 
 * @param string
 * @return string
 */
function html_decode($text)
{
  return htmlspecialchars_decode($text,ENT_COMPAT) ;
}

/**
 * Fonctions utilisées avec array_filter() ; teste si différent de FALSE.
 * @return bool
 */
function non_nul($n)
{
  return $n!==FALSE ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si différent de zéro.
 * @return bool
 */
function non_zero($n)
{
  return $n!=0 ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si strictement positif.
 * @return bool
 */
function positif($n)
{
  return $n>0 ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si différent "X" (pas "REQ" car désormais cela peut être saisi).
 * @return bool
 */
function sans_rien($note)
{
  return $note!='X' ;
}
/**
 * Fonctions utilisées avec array_filter() ; teste si différent de 2.
 * @return bool
 */
function is_renseigne($etat)
{
  return $etat!=2 ;
}

/**
 * Tester un item est considéré comme acquis au vu du score transmis.
 * 
 * Le seuil peut être celui défini globalement (par défaut si rien de transmis) ou un seuil testé ; peut être appelé avec array_filter().
 * 
 * @param int $score
 * @param int $seuil (facultatif)
 * @return bool
 */
function test_A($score,$seuil=NULL)
{
  $seuil = ($seuil===NULL) ? $_SESSION['CALCUL_SEUIL']['V'] : $seuil ;
  return $score>$seuil ;
}

/**
 * test_NA
 * Tester un item est considéré comme non acquis au vu du score transmis.
 * Le seuil peut être celui défini globalement (par défaut si rien de transmis) ou un seuil testé ; peut être appelé avec array_filter().
 * 
 * @param int $score
 * @param int $seuil
 * @return bool
 */
function test_NA($score,$seuil=NULL)
{
  $seuil = ($seuil===NULL) ? $_SESSION['CALCUL_SEUIL']['R'] : $seuil ;
  return $score<$seuil ;
}

/**
 * roundTo
 * Arrondir à une précision donnée, par exemple à 0,5 près
 * @see   http://fr.php.net/manual/fr/function.round.php#93747
 * @param float $nombre
 * @param float $precision
 * @return float
 */
function roundTo($nombre,$precision)
{
  return ($precision) ? round( $nombre/$precision , 0 ) * $precision : $nombre ;
}

/**
 * ceilTo
 * Arrondir à une précision donnée, par exemple à 0,5 près, par excès
 * @param float $nombre
 * @param float $precision
 * @return float
 */
function ceilTo($nombre,$precision)
{
  return ($precision) ? ceil( $nombre/$precision ) * $precision : $nombre ;
}

/**
 * floorTo
 * Arrondir à une précision donnée, par exemple à 0,5 près, par défaut
 * @param float $nombre
 * @param float $precision
 * @return float
 */
function floorTo($nombre,$precision)
{
  return ($precision) ? floor( $nombre/$precision ) * $precision : $nombre ;
}

/**
 * Calculer le score d'un item, à partir des notes transmises et des paramètres de calcul.
 * 
 * @param array  $tab_devoirs      $tab_devoirs[$i]['note'] = note
 * @param string $calcul_methode   'geometrique' / 'arithmetique' / 'classique' / 'moyenne' / 'bestof'
 * @param int    $calcul_limite    nb maxi d'éval à prendre en compte
 * @return int|FALSE
 */
function calculer_score($tab_devoirs,$calcul_methode,$calcul_limite)
{
  // on passe en revue les évaluations disponibles, et on retient les notes exploitables
  $tab_modele_bon = array('RR','R','V','VV');  // les notes prises en compte dans le calcul du score
  $tab_note = array(); // pour retenir les notes en question
  $nb_devoir = count($tab_devoirs);
  for($i=0;$i<$nb_devoir;$i++)
  {
    if(in_array($tab_devoirs[$i]['note'],$tab_modele_bon))
    {
      $tab_note[] = $_SESSION['CALCUL_VALEUR'][$tab_devoirs[$i]['note']];
    }
  }
  // si pas de notes exploitables, on arrête de suite (sinon, on est certain de pouvoir renvoyer un score)
  $nb_note = count($tab_note);
  if($nb_note==0)
  {
    return FALSE;
  }
  // si le paramétrage du référentiel l'indique, on tronque pour ne garder que les derniers résultats
  if( ($calcul_limite) && ($nb_note>$calcul_limite) )
  {
    $tab_note = array_slice($tab_note,-$calcul_limite);
    $nb_note = $calcul_limite;
  }
  // 1. Calcul de la note en fonction de la méthode du référentiel : 'geometrique','arithmetique','classique'
  if(in_array($calcul_methode,array('geometrique','arithmetique','classique')))
  {
    // 1a. Initialisation
    $somme_point = 0;
    $somme_coef = 0;
    $coef = 1;
    // 1b. Pour chaque devoir (note)...
    for($num_devoir=1 ; $num_devoir<=$nb_note ; $num_devoir++)
    {
      $somme_point += $tab_note[$num_devoir-1]*$coef;
      $somme_coef += $coef;
      $coef = ($calcul_methode=='geometrique') ? $coef*2 : ( ($calcul_methode=='arithmetique') ? $coef+1 : 1 ) ; // Calcul du coef de l'éventuel devoir suivant
    }
    // 1c. Calcul final du score
    return round( $somme_point/$somme_coef , 0 );
  }
  // 2. Calcul de la note en fonction de la méthode du référentiel : 'bestof1','bestof2','bestof3'
  if(in_array($calcul_methode,array('bestof1','bestof2','bestof3')))
  {
    // 2a. Initialisation
    $tab_notes = array();
    $nb_best = (int)substr($calcul_methode,-1);
    // 2b. Pour chaque devoir (note)...
    for($num_devoir=1 ; $num_devoir<=$nb_note ; $num_devoir++)
    {
      $tab_notes[] = $tab_note[$num_devoir-1];
    }
    // 2c. Calcul final du score
    rsort($tab_notes);
    $tab_notes = array_slice( $tab_notes , 0 , $nb_best );
    return round( array_sum($tab_notes)/count($tab_notes) , 0 );
  }
}

/**
 * Ajout d'un log PHP dans le fichier error-log du serveur Web
 * 
 * @param string $log_objet       objet du log
 * @param string $log_contenu     contenu du log
 * @param string $log_fichier     transmettre __FILE__
 * @param string $log_ligne       transmettre __LINE__
 * @param bool   $only_sesamath   [TRUE] pour une inscription uniquement sur le serveur Sésamath (par défaut), [FALSE] sinon
 * @return void
 */
function ajouter_log_PHP($log_objet,$log_contenu,$log_fichier,$log_ligne,$only_sesamath=TRUE)
{
  if( (!$only_sesamath) || (strpos(URL_INSTALL_SACOCHE,SERVEUR_PROJET)===0) )
  {
    $SEP = ' ║ ';
    error_log('SACoche info' . $SEP . $log_objet . $SEP . 'base '.$_SESSION['BASE'] . $SEP . 'user '.$_SESSION['USER_ID'] . $SEP . basename($log_fichier).' '.$log_ligne . $SEP . $log_contenu,0);
  }
}

/**
 * Affichage déclaration + section head du document
 * 
 * Utilise aussi le tableau $GLOBALS['HEAD'] avec les clefs suivantes :
 * ['title'] - ['css']['file'][] - ['css_ie']['file'][] - ['css']['inline'][] - ['js']['file'][] - ['js']['inline'][]
 * 
 * @param bool   $is_meta_robots  affichage ou non des balises meta pour les robots
 * @param bool   $is_favicon      affichage ou non du favicon
 * @param bool   $is_rss          affichage ou non du flux RSS associé
 * @return void
 */
function afficher_page_entete( $is_meta_robots ,$is_favicon , $is_rss )
{
  header('Content-Type: text/html; charset='.CHARSET);
  echo'<!DOCTYPE html>'.NL;
  echo'<html>'.NL;
  echo  '<head>'.NL;
  echo    '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />'.NL;
  if($is_meta_robots)
  {
    echo    '<meta name="description" content="SACoche - Suivi d\'Acquisition de Compétences - Evaluation par compétences - Valider le socle commun" />'.NL;
    echo    '<meta name="keywords" content="SACoche Sésamath évaluer évaluation compétences compétence validation valider socle commun collège points note notes Lomer" />'.NL;
    echo    '<meta name="author" content="Thomas Crespin pour Sésamath" />'.NL;
    echo    '<meta name="robots" content="index,follow" />'.NL;
  }
  if($is_favicon)
  {
    echo    '<link rel="shortcut icon" type="images/x-icon" href="./favicon.ico" />'.NL;
    echo    '<link rel="icon" type="image/png" href="./favicon.png" />'.NL;
    echo    '<link rel="apple-touch-icon" href="./_img/apple-touch-icon-114x114.png" />'.NL;
    echo    '<link rel="apple-touch-icon-precomposed" href="./_img/apple-touch-icon-114x114.png" />'.NL;
  }
  if($is_rss)
  {
    echo    '<link rel="alternate" type="application/rss+xml" href="'.SERVEUR_RSS.'" title="SACoche" />'.NL;
  }
  // Titre
  $title = !empty($GLOBALS['HEAD']['title']) ? $GLOBALS['HEAD']['title'] : 'SACoche' ;
  echo    '<title>'.$title.'</title>'.NL;
  // CSS fichiers
  if(!empty($GLOBALS['HEAD']['css']['file']))
  {
    foreach($GLOBALS['HEAD']['css']['file'] as $css_file)
    {
      echo    '<link rel="stylesheet" type="text/css" href="'.$css_file.'" />'.NL;
    }
  }
  // CSS fichiers IE (non utilisé)
  if(!empty($GLOBALS['HEAD']['css_ie']['file']))
  {
    foreach($GLOBALS['HEAD']['css_ie']['file'] as $css_ie_file)
    {
      echo    '<!--[if lte IE 8]><link rel="stylesheet" type="text/css" href="'.$css_ie_file.'" /><![endif]-->'.NL;
    }
  }
  // CSS inline (personnalisations)
  if(!empty($GLOBALS['HEAD']['css']['inline']))
  {
    echo    '<style type="text/css">'.NL;
    foreach($GLOBALS['HEAD']['css']['inline'] as $css_inline)
    {
      echo      $css_inline.NL;
    }
    echo    '</style>'.NL;
  }
  // JS fichiers
  if(!empty($GLOBALS['HEAD']['js']['file']))
  {
    foreach($GLOBALS['HEAD']['js']['file'] as $js_file)
    {
      echo    '<script type="text/javascript" charset="'.CHARSET.'" src="'.$js_file.'"></script>'.NL;
    }
  }
  // JS inline (données dynamiques, telles des constantes supplémentaires)
  if(!empty($GLOBALS['HEAD']['js']['inline']))
  {
    echo    '<script type="text/javascript">'.NL;
    foreach($GLOBALS['HEAD']['js']['inline'] as $js_inline)
    {
      echo      $js_inline.NL;
    }
    echo    '</script>'.NL;
  }
  echo  '</head>'.NL;
}

/**
 * Compression ou minification d'un fichier css ou js sur le serveur en production, avec date auto-insérée si besoin pour éviter tout souci de mise en cache.
 * Si pas de compression (hors PROD), ajouter un GET dans l'URL force le navigateur à mettre à jour son cache.
 * Attention cependant concernant cette dernière technique : avec les réglages standards d'Apache, ajouter un GET dans l'URL fait que beaucoup de navigateurs ne mettent pas le fichier en cache (donc il est rechargé tout le temps, même si le GET est le même) ; pas de souci si le serveur envoie un header avec une date d'expiration explicite...
 * 
 * @param string $chemin    chemin complet vers le fichier
 * @param string $methode   soit "pack" soit "mini" soit "comm"
 * @return string           chemin vers le fichier à prendre en compte (à indiquer dans la page web) ; il sera relatif si non compressé, absolu si compressé
 */
function compacter($chemin,$methode)
{
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
    $fichier_compact_chemin     = ($tmp_appli) ? CHEMIN_DOSSIER_TMP.$fichier_compact_nom : CHEMIN_DOSSIER_TMP_PROJET.$fichier_compact_nom ;
    $fichier_compact_url        = ($tmp_appli) ?        URL_DIR_TMP.$fichier_compact_nom :        URL_DIR_TMP_PROJET.$fichier_compact_nom ;
    $fichier_compact_date       = (is_file($fichier_compact_chemin)) ? filemtime($fichier_compact_chemin) : 0 ;
    // Sur le serveur en production, on compresse le fichier s'il ne l'est pas.
    if($fichier_compact_date<$fichier_original_date)
    {
      $fichier_original_contenu = file_get_contents($fichier_original_chemin);
      $fichier_original_contenu = utf8_decode($fichier_original_contenu); // Attention, il faut envoyer à ces classes de l'iso et pas de l'utf8.
      if( ($fichier_original_extension=='js') && ($methode=='pack') )
      {
        $myPacker = new JavaScriptPacker($fichier_original_contenu, 62, TRUE, FALSE);
        $fichier_compact_contenu = $myPacker->pack();
      }
      elseif( ($fichier_original_extension=='js') && ($methode=='mini') )
      {
        $fichier_compact_contenu = JSMin::minify($fichier_original_contenu);
      }
      elseif( ($fichier_original_extension=='js') && ($methode=='comm') )
      {
        // Retrait des /*! ... */ et /** ... */ ; option de recherche "s" (PCRE_DOTALL) pour inclure les retours à la lignes (@see http://fr.php.net/manual/fr/reference.pcre.pattern.modifiers.php).
        $fichier_compact_contenu = preg_replace( '#'.'/\*!'.'(.*?)'.'\*/'.'#s' , '' , preg_replace( '#'.'/\*\*'.'(.*?)'.'\*/'.'#s' , '' , $fichier_original_contenu ) );
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
 * Charger les parametres mysql de connexion d'un établissement qui n'auraient pas été chargé par le fichier index ou ajax.
 * 
 * Dans le cas d'une installation de type multi-structures, on peut avoir besoin d'effectuer une requête sur une base d'établissement sans y être connecté :
 * => pour savoir si le mode de connexion est SSO ou pas (./pages/public_*.php)
 * => pour l'identification (méthode SessionUser::tester_authentification_utilisateur())
 * => pour le webmestre (création d'un admin, info sur les admins, initialisation du mdp...)
 * Dans le cas d'une installation de type multi-structures, on peut avoir besoin d'effectuer une requête sur la base du webmestre :
 * => pour avoir des infos sur le contact référent, ou l'état d'une convention ENT
 * 
 * @param int   $BASE   0 pour celle du webmestre
 * @return void | exit
 */
function charger_parametres_mysql_supplementaires($BASE)
{
  $fichier_mysql_config_supplementaire = ($BASE) ? CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php' : CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_webmestre.php' ;
  $fichier_class_config_supplementaire = ($BASE) ? CHEMIN_DOSSIER_INCLUDE.'class.DB.config.sacoche_structure.php' : CHEMIN_DOSSIER_INCLUDE.'class.DB.config.sacoche_webmestre.php' ;
  if(is_file($fichier_mysql_config_supplementaire))
  {
    global $_CONST; // Car si on charge les paramètres dans une fonction, ensuite ils ne sont pas trouvés par la classe de connexion.
    require($fichier_mysql_config_supplementaire);
    require($fichier_class_config_supplementaire);
  }
  else
  {
    exit_error( 'Paramètres BDD manquants' /*titre*/ , 'Les paramètres de connexion à la base de données n\'ont pas été trouvés.<br />Le fichier "'.FileSystem::fin_chemin($fichier_mysql_config_supplementaire).'" (base n°'.$BASE.') est manquant !' /*contenu*/ );
  }
}

/**
 * Fabriquer un login à partir de nom/prénom selon le format paramétré par l'administrateur (reste à tester sa disponibilité).
 * Cette fonction n'est appelée que depuis un espace administrateur ; $_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'] est donc défini.
 * 
 * @param string $prenom
 * @param string $nom
 * @param string $profil_sigle
 * @return string
 */
function fabriquer_login($prenom,$nom,$profil_sigle)
{
  $modele = $_SESSION['TAB_PROFILS_ADMIN']['LOGIN_MODELE'][$profil_sigle];
  $login_prenom = mb_substr( str_replace(array('.','-','_'),'',Clean::login($prenom)) , 0 , mb_substr_count($modele,'p') );
  $login_nom    = mb_substr( str_replace(array('.','-','_'),'',Clean::login($nom))    , 0 , mb_substr_count($modele,'n') );
  $login_separe = str_replace(array('p','n'),'',$modele);
  $login = ($modele{0}=='p') ? $login_prenom.$login_separe.$login_nom : $login_nom.$login_separe.$login_prenom ;
  return $login;
}

/**
 * Fabriquer un mot de passe ; 8 caractères imposés.
 * Cette fonction peut être appelée par le webmestre ou à l'installation ; $_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'] n'est alors pas défini -> dans ce cas, on ne transmet pas de paramètre.
 * 
 * Certains caractères sont évités :
 * "e" sinon un tableur peut interpréter le mot de passe comme un nombre avec exposant
 * "i"j"1"l" pour éviter une confusion entre eux
 * "m"w" pour éviter la confusion avec "nn"vv"
 * "o"0" pour éviter une confusion entre eux
 * 
 * @param string $profil_sigle
 * @return string
 */
function fabriquer_mdp($profil_sigle=NULL)
{
  $nb_chars = ($profil_sigle) ? $_SESSION['TAB_PROFILS_ADMIN']['MDP_LONGUEUR_MINI'][$profil_sigle] : 8 ;
  return mb_substr(str_shuffle('2345678923456789aaaaauuuuubcdfghknpqrstvxyz'),0,$nb_chars);
}

/**
 * Crypter un mot de passe avant enregistrement dans la base.
 * 
 * Le "salage" complique la recherche d'un mdp à partir de son empreinte md5 en utilisant une table arc-en-ciel.
 * 
 * @param string $password
 * @return string
 */
function crypter_mdp($password)
{
  return md5('grain_de_sel'.$password);
}

/**
 * Fabrique une date et une valeur aléatoire pour terminer un nom de fichier.
 * 
 * @param void
 * @return string
 */
function fabriquer_fin_nom_fichier__date_et_alea()
{
  // date
  $chaine_date = date('Y-m-d_H\hi\m\i\ns\s'); // lisible par un humain et compatible avec le système de fichiers
  // valeur aléatoire
  $longueur_chaine = 15; // permet > 2x10^23 possibilités : même en en testant 1 milliard /s il faudrait plus de 7 millions d'années pour toutes les essayer
  $caracteres = '0123456789abcdefghijklmnopqrstuvwxyz';
  $alea_max = strlen($caracteres)-1;
  $chaine_alea = '';
  for( $i=0 ; $i<$longueur_chaine ; $i++ )
  {
    $chaine_alea .= $caracteres{mt_rand(0,$alea_max)};
  }
  // retour
  return $chaine_date.'_'.$chaine_alea;
}

/**
 * Fabrique une fin de fichier pseudo-aléatoire pour terminer un nom de fichier.
 * 
 * Le suffixe est suffisamment tordu pour le rendre un privé et non retrouvable par un utilisateur, mais sans être totalement aléatoire car il doit fixe (retrouvé).
 * Utilisé pour les flux RSS et les bilans officiels PDF.
 * 
 * @param string   $fichier_nom_debut
 * @return string
 */
function fabriquer_fin_nom_fichier__pseudo_alea($fichier_nom_debut)
{
  return md5($fichier_nom_debut.$_SERVER['DOCUMENT_ROOT']);
}

/**
 * Fabrique une fin de fichier pseudo-aléatoire pour terminer un nom de fichier.
 * 
 * Le suffixe est suffisamment tordu pour le rendre un privé et non retrouvable par un utilisateur, mais sans être totalement aléatoire car il doit fixe (retrouvé).
 * Utilisé pour les flux RSS, les bilans officiels PDF, les fiches brevet PDF.
 * 
 * @param int      $eleve_id
 * @param string   $bilan_type
 * @param int      $periode_id
 * @return string
 */
function fabriquer_nom_fichier_bilan_officiel( $eleve_id , $bilan_type , $periode_id )
{
  $fichier_bilan_officiel_nom_debut = 'user'.$eleve_id.'_officiel_'.$bilan_type.'_periode'.$periode_id;
  $fichier_bilan_officiel_nom_fin   = fabriquer_fin_nom_fichier__pseudo_alea($fichier_bilan_officiel_nom_debut);
  return $fichier_bilan_officiel_nom_debut.'_'.$fichier_bilan_officiel_nom_fin.'.pdf';
}

/**
 * Mettre à jour automatiquement la base si besoin ; à effectuer avant toute récupération des données sinon ça peut poser pb...
 * 
 * @param int   $BASE
 * @return void
 */
function maj_base_structure_si_besoin($BASE)
{
  $version_base_structure = DB_STRUCTURE_PUBLIC::DB_version_base();
  if($version_base_structure != VERSION_BASE_STRUCTURE)
  {
    // On ne met pas à jour la base tant que le webmestre bloque l'accès à l'application, car sinon cela pourrait se produire avant le transfert de tous les fichiers.
    if(LockAcces::tester_blocage('webmestre',0)===NULL)
    {
      // Bloquer l'application
      LockAcces::bloquer_application('automate',$BASE,'Mise à jour de la base en cours.');
      // Lancer une mise à jour de la base
      DB_STRUCTURE_MAJ_BASE::DB_maj_base($version_base_structure);
      // Log de l'action
      SACocheLog::ajouter('Mise à jour automatique de la base '.SACOCHE_STRUCTURE_BD_NAME.'.');
      // Débloquer l'application
      LockAcces::debloquer_application('automate',$BASE);
    }
  }
}

/**
 * Récupérer le numéro de la dernière version de SACoche disponible auprès du serveur communautaire.
 * 
 * @param void
 * @return string 'AAAA-MM-JJi' ou message d'erreur
 */
function recuperer_numero_derniere_version()
{
  $requete_reponse = cURL::get_contents(SERVEUR_VERSION);
  return (preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}[a-z]?$#',$requete_reponse)) ? $requete_reponse : 'Dernière version non détectée&hellip;' ;
}

/**
 * Retourner un tableau de lignes à partir d'un texte en se basant sur les retours chariot.
 * Utilisé notamment lors de la récupération d'un fichier CSV.
 * 
 * @param string   $texte
 * @return array
 */
function extraire_lignes($texte)
{
  $texte = trim($texte);
  $texte = str_replace('"','',$texte);
  $texte = str_replace(array("\r\n","\n\n","\r\r","\r","\n"),'®',$texte);
  return explode('®',$texte);
}

/**
 * Déterminer la nature du séparateur d'un fichier CSV.
 * 
 * @param string   $ligne   la première ligne du fichier
 * @return string
 */
function extraire_separateur_csv($ligne)
{
  $tab_separateur = array( ';'=>0 , ','=>0 , ':'=>0 , "\t"=>0 );
  foreach($tab_separateur as $separateur => $occurrence)
  {
    $tab_separateur[$separateur] = mb_substr_count($ligne,$separateur);
  }
  arsort($tab_separateur);
  reset($tab_separateur);
  return key($tab_separateur);
}

/**
 * Tester si une adresse de courriel semble normale.
 * 
 * Utilisé pour une récupération via un CSV parce que pour un champ de saisie javascript fait déjà le ménage.
 * http://fr2.php.net/manual/fr/function.preg-match.php#96910
 * 
 * @param string   $courriel
 * @return bool
 */
function tester_courriel($courriel)
{
  return preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/',$courriel) ? TRUE : FALSE;
}

/**
 * Vérifier que le domaine du serveur mail peut recevoir des mails, c'est à dire qu'il a un serveur de mail déclaré dans les DNS).
 * Ça évite tous les domaines avec une coquille du genre @gmaill.com, @hoatmail.com, @gmaol.com, @laoste.net, etc.
 *
 * @param string $mail_adresse
 * @return bool|string $mail_domaine   TRUE | le domaine en cas de problème
 */
function tester_domaine_courriel_valide($mail_adresse)
{
  $mail_domaine = mb_substr( $mail_adresse , mb_strpos($mail_adresse,'@')+1 );
  return ( ( function_exists('getmxrr') && getmxrr($mail_domaine, $tab_mxhosts) ) || (@fsockopen($mail_domaine,25,$errno,$errstr,5)) ) ? TRUE : $mail_domaine ;
}

/**
 * Tester si un numéro UAI est valide.
 * 
 * Utilisé pour une récupération via un CSV parce que pour un champ de saisie javascript fait déjà le ménage.
 * 
 * @param string   $uai
 * @return bool
 */
function tester_UAI($uai)
{
  // Il faut 7 chiffres suivis d'une lettre.
  if(!preg_match('#^[0-9]{7}[A-Z]{1}$#',$uai))
  {
    return FALSE;
  }
  // Il faut vérifier la clef de contrôle.
  $uai_nombre = substr($uai,0,7);
  $uai_lettre = substr($uai,-1);
  $reste = $uai_nombre - (23*floor($uai_nombre/23));
  $alphabet = 'ABCDEFGHJKLMNPRSTUVWXYZ';
  $clef = substr($alphabet,$reste,1);
  return ($clef==$uai_lettre) ? TRUE : FALSE;
}

/**
 * Tester si une date est valide : format AAAA-MM-JJ par exemple.
 * 
 * Utilisé pour une récupération via un CSV parce que pour un champ de saisie javascript fait déjà le ménage.
 * 
 * @param string   $date
 * @return bool
 */
function tester_date($date)
{
  $date_unix = strtotime($date);
  return ( ($date_unix!==FALSE) && ($date_unix!==-1) ) ? TRUE : FALSE ;
}

/**
 * Renvoyer les dimensions d'une image à mettre dans les attributs HTML si on veut limiter son affichage à une largeur / hauteur données.
 * 
 * @param int   $largeur_reelle
 * @param int   $hauteur_reelle
 * @param int   $largeur_maxi
 * @param int   $hauteur_maxi
 * @return array   [$largeur_imposee,$hauteur_imposee]
 */
function dimensions_affichage_image($largeur_reelle,$hauteur_reelle,$largeur_maxi,$hauteur_maxi)
{
  if( ($largeur_reelle>$largeur_maxi) || ($hauteur_reelle>$hauteur_maxi) )
  {
    $coef_reduction_largeur = $largeur_maxi/$largeur_reelle;
    $coef_reduction_hauteur = $hauteur_maxi/$hauteur_reelle;
    $coef_reduction = min($coef_reduction_largeur,$coef_reduction_hauteur);
    $largeur_imposee = round($largeur_reelle*$coef_reduction);
    $hauteur_imposee = round($hauteur_reelle*$coef_reduction);
    return array($largeur_imposee,$hauteur_imposee);
  }
  return array($largeur_reelle,$hauteur_reelle);
}

/**
 * Passer d'une date MySQL AAAA-MM-JJ à une date française JJ/MM/AAAA.
 *
 * @param string $date_mysql AAAA-MM-JJ
 * @return string|NULL       JJ/MM/AAAA
 */
function convert_date_mysql_to_french($date_mysql)
{
  if($date_mysql===NULL) return '00/00/0000';
  list($annee,$mois,$jour) = explode('-',$date_mysql);
  return $jour.'/'.$mois.'/'.$annee;
}

/**
 * Passer d'une date française JJ/MM/AAAA à une date MySQL AAAA-MM-JJ.
 *
 * @param string $date_fr JJ/MM/AAAA
 * @return string|NULL    AAAA-MM-JJ
 */
function convert_date_french_to_mysql($date_fr)
{
  if($date_fr=='00/00/0000') return NULL;
  list($jour,$mois,$annee) = explode('/',$date_fr);
  return $annee.'-'.$mois.'-'.$jour;
}

/**
 * Passer d'une date française JJ/MM/AAAA à une date J mois AAAA.
 *
 * @param string $date_fr JJ/MM/AAAA
 * @return string         J mois AAAA
 */
function convert_date_french_to_texte($date_fr)
{
  $tab_mois = array('01'=>'janvier','02'=>'février','03'=>'mars','04'=>'avril','05'=>'mai','06'=>'juin','07'=>'juillet','08'=>'août','09'=>'septembre','10'=>'octobre','11'=>'novembre','12'=>'décembre');
  if($date_fr=='00/00/0000') return '';
  list($jour,$mois,$annee) = explode('/',$date_fr);
  return intval($jour).' '.$tab_mois[$mois].' '.$annee;
}

/**
 * Passer d'une date française JJ/MM/AAAA à l'âge X ans et Y mois.
 *
 * Il existe des fonctions utilisables qu'à partir de PHP 5.2 ou 5.3.
 * @see http://fr.php.net/manual/fr/class.datetime.php
 * Ceci dit, le code élaboré est assez simple.
 *
 * @param string $date_fr JJ/MM/AAAA
 * @return string         X ans et Y mois
 */
function texte_age($date_fr)
{
  list($jour_birth,$mois_birth,$annee_birth) = explode('/',$date_fr);
  list($jour_today,$mois_today,$annee_today) = explode('/',TODAY_FR);
  $nb_annees = $annee_today - $annee_birth;
  $nb_mois   = $mois_today - $mois_birth;
  if( ($mois_birth>$mois_today) || (($mois_birth==$mois_today)&&($jour_birth>$jour_today)) )
  {
    $nb_annees-=1;
    $nb_mois+=12;
  }
  if($jour_birth>$jour_today)
  {
    $nb_mois-=1;
  }
  $s_annee = ($nb_annees>1) ? 's' : '' ;
  $aff_mois = ($nb_mois) ? ' et '.$nb_mois.' mois' : '' ;
  return $nb_annees.' an'.$s_annee.$aff_mois;
}

/**
 * Passer d'une date française JJ/MM/AAAA à l'affichage de la date de naissance et de l'âge.
 *
 * @param string $date_fr JJ/MM/AAAA
 * @return string
 */
function texte_ligne_naissance($date_fr)
{
  return 'Âge : '.texte_age($date_fr).' ('.convert_date_french_to_texte($date_fr).').';
}

/**
 * Renvoyer le 1er jour de l'année scolaire en cours, au format français JJ/MM/AAAA ou MySQL AAAA-MM-JJ.
 *
 * @param string $format   'mysql'|'french'
 * @param int    $annee_sup   facultatif, pour les années scolaires suivantes
 * @return string
 */
function jour_debut_annee_scolaire($format,$annee_sup=0)
{
  $jour  = '01';
  $mois  = sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']);
  $annee = (date("n")<$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']) ? date("Y")+$annee_sup-1 : date("Y")+$annee_sup ;
  return ($format=='mysql') ? $annee.'-'.$mois.'-'.$jour : $jour.'/'.$mois.'/'.$annee ;
}

/**
 * Renvoyer le dernier jour de l'année scolaire en cours, au format français JJ/MM/AAAA ou MySQL AAAA-MM-JJ.
 * En fait, c'est plus exactement le 1er jour de l'année scolaire suivante...
 *
 * @param string $format   'mysql'|'french'
 * @param int    $annee_sup   facultatif, pour les années scolaires suivantes
 * @return string
 */
function jour_fin_annee_scolaire($format,$annee_sup=0)
{
  $jour  = '01';
  $mois  = sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']);
  $annee = (date("n")<$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']) ? date("Y")+$annee_sup : date("Y")+$annee_sup+1 ;
  return ($format=='mysql') ? $annee.'-'.$mois.'-'.$jour : $jour.'/'.$mois.'/'.$annee ;
}

/**
 * Renvoyer l'année de session du DNB.
 *
 * @param void
 * @return string
 */
function annee_session_brevet()
{
  $mois_actuel    = date('n');
  $annee_actuelle = date('Y');
  $mois_bascule   = $_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE'];
  if($mois_bascule==1)
  {
    return $annee_actuelle;
  }
  else if($mois_actuel < $mois_bascule)
  {
    return $annee_actuelle;
  }
  else
  {
    return $annee_actuelle+1;
  }
}

/**
 * Renvoyer une taille de fichier lisible pour un humain :)
 * @see http://fr2.php.net/manual/fr/function.filesize.php#106569
 *
 * @param int $bytes
 * @param int $decimals (facultatif)
 * @return string
 */
function afficher_fichier_taille($bytes, $decimals = 1)
{
  $size_unit = ' KMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return round( $bytes / pow(1024,$factor) , $decimals ) . $size_unit[$factor].'o';
}

/**
 * Tester si un droit d'accès spécifique comporte une restriction aux PP ou aux coordonnateurs
 *
 * @param string $listing_droits_sigles
 * @param string $restriction   'ONLY_PP' | 'ONLY_COORD' | 'ONLY_LV'
 * @return bool
 */
function test_droit_specifique_restreint($listing_droits_sigles,$restriction)
{
  return (strpos($listing_droits_sigles,$restriction)!==FALSE);
}

/**
 * Tester si on a un droit d'accès spécifique
 *
 * @param string $listing_droits_sigles
 * @param int    $matiere_coord_or_groupe_pp_connu   si le droit comporte une restriction aux coordonnateurs matières | professeurs principaux, on peut déja connaitre et transmettre l'info (soit pour au moins une matière | classe, soit pour une matière | classe donnée)
 * @param int    $matiere_id_or_groupe_id_a_tester   si le droit comporte une restriction aux coordonnateurs matières | professeurs principaux, et si $matiere_coord_or_groupe_pp_connu n'est pas transmis, on peut chercher si le droit est bon soit pour une matière | classe donnée, soit pour au moins une matière | classe
 * @param bool   $forcer_parent                TRUE pour forcer à tester un profil parent au lieu du profil de l'utilisateur
 * @return bool
 */
function test_user_droit_specifique($listing_droits_sigles,$matiere_coord_or_groupe_pp_connu=NULL,$matiere_id_or_groupe_id_a_tester=0,$forcer_parent=FALSE)
{
  $user_profil_sigle = (!$forcer_parent) ? $_SESSION['USER_PROFIL_SIGLE'] : 'TUT'    ;
  $user_profil_type  = (!$forcer_parent) ? $_SESSION['USER_PROFIL_TYPE']  : 'parent' ;
  $tableau_droits_sigles = explode(',',$listing_droits_sigles);
  $test_droit = in_array($user_profil_sigle,$tableau_droits_sigles);
  if( $test_droit && ($user_profil_type=='professeur') && ($_SESSION['USER_JOIN_GROUPES']=='config') && test_droit_specifique_restreint($listing_droits_sigles,'ONLY_PP') )
  {
    return ($matiere_coord_or_groupe_pp_connu!==NULL) ? (bool)$matiere_coord_or_groupe_pp_connu : DB_STRUCTURE_PROFESSEUR::DB_tester_prof_principal($_SESSION['USER_ID'],$matiere_id_or_groupe_id_a_tester) ;
  }
  if( $test_droit && ($user_profil_type=='professeur') && ($_SESSION['USER_JOIN_MATIERES']=='config') && test_droit_specifique_restreint($listing_droits_sigles,'ONLY_COORD') )
  {
    return ($matiere_coord_or_groupe_pp_connu!==NULL) ? (bool)$matiere_coord_or_groupe_pp_connu : DB_STRUCTURE_PROFESSEUR::tester_prof_coordonnateur($_SESSION['USER_ID'],$matiere_id_or_groupe_id_a_tester) ;
  }
  if( $test_droit && ($user_profil_type=='professeur') && ($_SESSION['USER_JOIN_MATIERES']=='config') && test_droit_specifique_restreint($listing_droits_sigles,'ONLY_LV') )
  {
    return DB_STRUCTURE_PROFESSEUR::tester_prof_langue_vivante($_SESSION['USER_ID']) ;
  }
  return $test_droit;
}

/**
 * Tester si on a un droit d'accès spécifique
 *
 * @param string $listing_droits_sigles
 * @param string $format   "li" | "br"
 * @return bool
 */
function afficher_profils_droit_specifique($listing_droits_sigles,$format)
{
  $tab_profils = array();
  $texte_testriction_pp    = test_droit_specifique_restreint($listing_droits_sigles,'ONLY_PP')    ? ' restreint aux professeurs principaux'  : '' ;
  $texte_testriction_coord = test_droit_specifique_restreint($listing_droits_sigles,'ONLY_COORD') ? ' restreint aux coordonnateurs matières' : '' ;
  $texte_testriction_lv    = test_droit_specifique_restreint($listing_droits_sigles,'ONLY_LV')    ? ' restreint aux professeurs de LV'       : '' ;
  $tableau_droits_sigles = explode(',',$listing_droits_sigles);
  foreach($tableau_droits_sigles as $droit_sigle)
  {
    if(isset($_SESSION['TAB_PROFILS_DROIT']['TYPE'][$droit_sigle]))
    {
      $profil_nom  = $_SESSION['TAB_PROFILS_DROIT']['NOM_LONG_PLURIEL'][$droit_sigle];
      $profil_nom .= ( ($_SESSION['TAB_PROFILS_DROIT']['TYPE'][$droit_sigle]=='professeur') && ($_SESSION['TAB_PROFILS_DROIT']['JOIN_GROUPES'][$droit_sigle]=='config')  ) ? $texte_testriction_pp    : '' ;
      $profil_nom .= ( ($_SESSION['TAB_PROFILS_DROIT']['TYPE'][$droit_sigle]=='professeur') && ($_SESSION['TAB_PROFILS_DROIT']['JOIN_MATIERES'][$droit_sigle]=='config') ) ? $texte_testriction_coord : '' ;
      $profil_nom .= ( ($_SESSION['TAB_PROFILS_DROIT']['TYPE'][$droit_sigle]=='professeur') && ($_SESSION['TAB_PROFILS_DROIT']['JOIN_MATIERES'][$droit_sigle]=='config') ) ? $texte_testriction_lv    : '' ;
      $tab_profils[] = $profil_nom;
    }
  }
  if(!count($tab_profils))
  {
    $tab_profils[] = 'aucun !';
  }
  return ($format=='li') ? '<ul class="puce">'.NL.'<li>'.implode('</li>'.NL.'<li>',$tab_profils).'</li>'.NL.'</ul>'.NL : implode('<br />',$tab_profils) ;
}

?>