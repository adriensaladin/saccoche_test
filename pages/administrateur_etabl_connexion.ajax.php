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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$f_action                 = (isset($_POST['f_action']))                 ? Clean::texte($_POST['f_action'])                 : '';
$f_annee                  = (isset($_POST['f_annee']))                  ? Clean::entier($_POST['f_annee'])                 : -1;
$f_convention_id          = (isset($_POST['f_convention_id']))          ? Clean::entier($_POST['f_convention_id'])         : 0 ;
$f_connexion_mode         = (isset($_POST['f_connexion_mode']))         ? Clean::texte($_POST['f_connexion_mode'])         : '';
$f_connexion_ref          = (isset($_POST['f_connexion_ref']))          ? Clean::texte($_POST['f_connexion_ref'])          : '';
$cas_serveur_host         = (isset($_POST['cas_serveur_host']))         ? Clean::texte($_POST['cas_serveur_host'])         : '';
$cas_serveur_port         = (isset($_POST['cas_serveur_port']))         ? Clean::entier($_POST['cas_serveur_port'])        : 0 ;
$cas_serveur_root         = (isset($_POST['cas_serveur_root']))         ? Clean::texte($_POST['cas_serveur_root'])         : '';
$cas_serveur_url_login    = (isset($_POST['cas_serveur_url_login']))    ? Clean::texte($_POST['cas_serveur_url_login'])    : '';
$cas_serveur_url_logout   = (isset($_POST['cas_serveur_url_logout']))   ? Clean::texte($_POST['cas_serveur_url_logout'])   : '';
$cas_serveur_url_validate = (isset($_POST['cas_serveur_url_validate'])) ? Clean::texte($_POST['cas_serveur_url_validate']) : '';
$gepi_saml_url            = (isset($_POST['gepi_saml_url']))            ? Clean::texte($_POST['gepi_saml_url'])            : '';
$gepi_saml_rne            = (isset($_POST['gepi_saml_rne']))            ? Clean::uai($_POST['gepi_saml_rne'])              : '';
$gepi_saml_certif         = (isset($_POST['gepi_saml_certif']))         ? Clean::texte($_POST['gepi_saml_certif'])         : '';

require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mode de connexion (normal, SSO...)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($f_action=='enregistrer_mode_identification')
{

  if(!isset($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]))
  {
    exit('Erreur avec les données transmises !');
  }

  list($f_connexion_departement,$f_connexion_nom) = explode('|',$f_connexion_ref);

  if( ($f_connexion_mode=='normal') || ($f_connexion_mode=='shibboleth') )
  {
    DB_STRUCTURE_COMMUN::DB_modifier_parametres( array('connexion_mode'=>$f_connexion_mode,'connexion_nom'=>$f_connexion_nom,'connexion_departement'=>$f_connexion_departement) );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']        = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']         = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT'] = $f_connexion_departement;
    exit('ok');
  }

  if($f_connexion_mode=='cas')
  {
    // Vérifier les paramètres CAS en reprenant le code de phpCAS
    if ( empty($cas_serveur_host) || !preg_match('/[\.\d\-abcdefghijklmnopqrstuvwxyz]*/',$cas_serveur_host) )
    {
      exit('Syntaxe du domaine incorrecte !');
    }
    if ( ($cas_serveur_port == 0) || !is_int($cas_serveur_port) )
    {
      exit('Numéro du port incorrect !');
    }
    if ( !preg_match('/[\.\d\-_abcdefghijklmnopqrstuvwxyz\/]*/',$cas_serveur_root) )
    {
      exit('Syntaxe du chemin incorrecte !');
    }
    // Expression régulière pour tester une URL (pas trop compliquée)
    $masque = '#^http(s)?://[\w-]+[\w.-]+\.[a-zA-Z]{2,6}(:[0-9]+)?#';
    if ( $cas_serveur_url_login && !preg_match($masque,$cas_serveur_url_login) )
    {
      exit('Syntaxe URL login incorrecte !');
    }
    if ( $cas_serveur_url_logout && !preg_match($masque,$cas_serveur_url_logout) )
    {
      exit('Syntaxe URL logout incorrecte !');
    }
    if ( $cas_serveur_url_validate && !preg_match($masque,$cas_serveur_url_validate) )
    {
      exit('Syntaxe URL validate incorrecte !');
    }
    // Ne pas dupliquer en paramétrage CAS-perso un paramétrage CAS-ENT existant (utiliser la connexion CAS officielle, pour laquelle une convention peut être requise)
    if($f_connexion_nom=='perso')
    {
      foreach($tab_serveur_cas as $tab_cas_param)
      {
        $is_param_defaut_identiques = ( ($cas_serveur_host==$tab_cas_param['serveur_host']) && ($cas_serveur_port==$tab_cas_param['serveur_port']) && ($cas_serveur_root==$tab_cas_param['serveur_root']) ) ? TRUE : FALSE ;
        $is_param_force_identiques  = ( ($cas_serveur_url_login!='') && ( ($cas_serveur_url_login==$tab_cas_param['serveur_url_login']) || ($cas_serveur_url_login=='https://'.$tab_cas_param['serveur_host'].':'.$tab_cas_param['serveur_port'].'/'.$tab_cas_param['serveur_root'].'/login') ) ) ? TRUE : FALSE ;
        if( $is_param_defaut_identiques || $is_param_force_identiques )
        {
          exit('Paramètres d\'un ENT référencé : sélectionnez-le !');
        }
      }
    }
    // Sur le serveur Sésamath, ne pas autoriser un paramétrage CAS correspondant à un hébergement académique (ne devrait pas se produire, Sésamath n'hébergeant pas ces établissements).
    else if(IS_HEBERGEMENT_SESAMATH)
    {
      if(!is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT))
      {
        exit('Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_SESAMATH_ENT).'</b>&nbsp;&raquo; (uniquement présent sur le serveur Sésamath) n\'a pas été détecté !');
      }  
      require(CHEMIN_FICHIER_WS_SESAMATH_ENT); // Charge les tableaux   $tab_connecteurs_hebergement & $tab_connecteurs_convention
      if( isset($tab_connecteurs_hebergement[$f_connexion_ref]) )
      {
        exit('Paramètres d\'un serveur CAS à utiliser sur l\'hébergement académique dédié !');
      }
    }
    // C'est ok
    DB_STRUCTURE_COMMUN::DB_modifier_parametres( array('connexion_mode'=>$f_connexion_mode,'connexion_nom'=>$f_connexion_nom,'connexion_departement'=>$f_connexion_departement,'cas_serveur_host'=>$cas_serveur_host,'cas_serveur_port'=>$cas_serveur_port,'cas_serveur_root'=>$cas_serveur_root,'cas_serveur_url_login'=>$cas_serveur_url_login,'cas_serveur_url_logout'=>$cas_serveur_url_logout,'cas_serveur_url_validate'=>$cas_serveur_url_validate) );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']        = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']         = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT'] = $f_connexion_departement;
    $_SESSION['CAS_SERVEUR']['HOST'] = $cas_serveur_host;
    $_SESSION['CAS_SERVEUR']['PORT'] = $cas_serveur_port;
    $_SESSION['CAS_SERVEUR']['ROOT'] = $cas_serveur_root;
    $_SESSION['CAS_SERVEUR']['URL_LOGIN']    = $cas_serveur_url_login;
    $_SESSION['CAS_SERVEUR']['URL_LOGOUT']   = $cas_serveur_url_logout;
    $_SESSION['CAS_SERVEUR']['URL_VALIDATE'] = $cas_serveur_url_validate;
    exit('ok');
  }

  if($f_connexion_mode=='gepi')
  {
    // Vérifier les paramètres GEPI-SAML
    // Le RNE n'étant pas obligatoire, et pas forcément un vrai RNE dans Gepi (pour les établ sans UAI, c'est un identifiant choisi...), on ne vérifie rien.
    // Pas de vérif particulière de l'empreinte du certificat non plus, ne sachant pas s'il peut y avoir plusieurs formats.
    // Donc on va se contenter de vraiment vérifier l'URL de Gepi via une requête cURL
    if(strlen($gepi_saml_url)<8)
    {
      exit('Adresse de GEPI manquante !');
    }
    if(empty($gepi_saml_certif))
    {
      exit('Signature (empreinte du certificat) manquante !');
    }
    $gepi_saml_url = (substr($gepi_saml_url,-1)=='/') ? substr($gepi_saml_url,0,-1) : $gepi_saml_url ;
    $fichier_distant = url_get_contents($gepi_saml_url.'/bandeau.css'); // Le mieux serait d'appeler le fichier du web-services... si un jour il y en a un...
    if(substr($fichier_distant,0,6)=='Erreur')
    {
      exit('Adresse de Gepi incorrecte [ '.$fichier_distant.' ]');
    }
    // C'est ok
    DB_STRUCTURE_COMMUN::DB_modifier_parametres( array('connexion_mode'=>$f_connexion_mode,'connexion_nom'=>$f_connexion_nom,'connexion_departement'=>$f_connexion_departement,'gepi_url'=>$gepi_saml_url,'gepi_rne'=>$gepi_saml_rne,'gepi_certificat_empreinte'=>$gepi_saml_certif) );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']        = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']         = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT'] = $f_connexion_departement;
    $_SESSION['GEPI_URL'] = $gepi_saml_url;
    $_SESSION['GEPI_RNE'] = $gepi_saml_rne;
    $_SESSION['GEPI_CERTIFICAT_EMPREINTE'] = $gepi_saml_certif;
    exit('ok');
  }

}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($f_action=='ajouter_convention') && $f_connexion_mode && $f_connexion_ref && in_array($f_annee,array(0,1)) )
{
  if( ($f_connexion_mode!='cas') || (!isset($tab_connexion_info['cas'][$f_connexion_ref])) )
  {
    exit('Erreur avec les données transmises !');
  }
  // Extraire les infos
  list($f_connexion_departement,$f_connexion_nom) = explode('|',$f_connexion_ref);
  $date_debut_mysql = jour_debut_annee_scolaire('mysql',$f_annee);
  $date_fin_mysql   = jour_fin_annee_scolaire(  'mysql',$f_annee);
  // Vérifier que la convention n'existe pas déjà
  charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  if(DB_WEBMESTRE_ADMINISTRATEUR::DB_tester_convention_precise( $_SESSION['BASE'] , $f_connexion_nom , $date_debut_mysql ))
  {
    exit('Erreur : convention déjà existante pour le service "'.html($f_connexion_nom).'" sur cette période !');
  }
  // Insérer l'enregistrement
  $convention_id = DB_WEBMESTRE_ADMINISTRATEUR::DB_ajouter_convention( $_SESSION['BASE'] , $f_connexion_nom , $date_debut_mysql , $date_fin_mysql );
  // Afficher le retour
  echo'<tr id="id_'.$convention_id.'" class="new">';
  echo  '<td>'.html($f_connexion_nom).'</td>';
  echo  '<td>du '.convert_date_mysql_to_french($date_debut_mysql).' au '.convert_date_mysql_to_french($date_fin_mysql).'</td>';
  echo  '<td class="br">Non réceptionnée</td>';
  echo  '<td class="br">Non réceptionné</td>';
  echo  '<td class="br">Non</td>';
  echo  '<td class="nu"><q class="voir_archive" title="Récupérer / Imprimer les documents associés."></q></td>';
  echo'</tr>';
  exit();
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Imprimer les documents associés à une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($f_action=='imprimer_convention') && $f_convention_id )
{
  // Récupération et vérification des infos de la convention
  charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  $DB_ROW = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_convention($f_convention_id);
  if(empty($DB_ROW))
  {
    exit('Erreur : convention non trouvée !');
  }
  if($DB_ROW['sacoche_base']!=$_SESSION['BASE'])
  {
    exit('Erreur : convention d\'une autre structure !');
  }
  // Imprimer la convention.
  // TODO !
  // Imprimer la facture.
  $facture_url = '';
  if($DB_ROW['convention_paiement']!==NULL)
  {
    // TODO !
    $url_dossier_facture = URL_DIR_EXPORT.$_SESSION['BASE'].'/';
    $facture_fichier_nom = 'chemin_fichier_facture.pdf';
    $facture_url = $url_dossier_facture.$facture_fichier_nom;
  }
  // Retour des informations.
  exit('ok'.']¤['.'chemin_fichier_convention.pdf'.']¤['.$facture_url);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
