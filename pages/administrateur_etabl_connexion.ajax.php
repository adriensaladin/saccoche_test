<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$f_action                     = (isset($_POST['f_action']))                     ? Clean::texte($_POST['f_action'])                      : '';
$f_annee                      = (isset($_POST['f_annee']))                      ? Clean::entier($_POST['f_annee'])                      : -1;
$f_convention_id              = (isset($_POST['f_convention_id']))              ? Clean::entier($_POST['f_convention_id'])              : 0 ;
$f_connexion_mode             = (isset($_POST['f_connexion_mode']))             ? Clean::texte($_POST['f_connexion_mode'])              : '';
$f_connexion_ref              = (isset($_POST['f_connexion_ref']))              ? Clean::texte($_POST['f_connexion_ref'])               : '';
$cas_serveur_host             = (isset($_POST['cas_serveur_host']))             ? Clean::texte($_POST['cas_serveur_host'])              : '';
$cas_serveur_port             = (isset($_POST['cas_serveur_port']))             ? Clean::entier($_POST['cas_serveur_port'])             : 0 ;
$cas_serveur_root             = (isset($_POST['cas_serveur_root']))             ? Clean::texte($_POST['cas_serveur_root'])              : '';
$cas_serveur_url_login        = (isset($_POST['cas_serveur_url_login']))        ? Clean::texte($_POST['cas_serveur_url_login'])         : '';
$cas_serveur_url_logout       = (isset($_POST['cas_serveur_url_logout']))       ? Clean::texte($_POST['cas_serveur_url_logout'])        : '';
$cas_serveur_url_validate     = (isset($_POST['cas_serveur_url_validate']))     ? Clean::texte($_POST['cas_serveur_url_validate'])      : '';
$cas_serveur_verif_certif_ssl = (isset($_POST['cas_serveur_verif_certif_ssl'])) ? Clean::entier($_POST['cas_serveur_verif_certif_ssl']) : NULL;
$serveur_host_subdomain       = (isset($_POST['serveur_host_subdomain']))       ? Clean::texte($_POST['serveur_host_subdomain'])        : '';
$serveur_host_domain          = (isset($_POST['serveur_host_domain']))          ? Clean::texte($_POST['serveur_host_domain'])           : '';
$serveur_port                 = (isset($_POST['serveur_port']))                 ? Clean::entier($_POST['serveur_port'])                 : 0 ;
$gepi_saml_url                = (isset($_POST['gepi_saml_url']))                ? Clean::texte($_POST['gepi_saml_url'])                 : '';
$gepi_saml_rne                = (isset($_POST['gepi_saml_rne']))                ? Clean::uai($_POST['gepi_saml_rne'])                   : '';
$gepi_saml_certif             = (isset($_POST['gepi_saml_certif']))             ? Clean::texte($_POST['gepi_saml_certif'])              : '';
$f_first_time                 = (isset($_POST['f_first_time']))                 ? Clean::texte($_POST['f_first_time'])                  : '';

require(CHEMIN_DOSSIER_INCLUDE.'tableau_sso.php');

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mode de connexion (normal, SSO...)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($f_action=='enregistrer_mode_identification')
{

  if(!isset($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]))
  {
    exit_json( FALSE , 'Erreur avec les données transmises !' );
  }

  if( ($f_connexion_mode=='cas') && ($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]['serveur_host_subdomain']=='*') && !$serveur_host_subdomain )
  {
    exit_json( FALSE , 'Sous-domaine manquant !' );
  }

  if( ($f_connexion_mode=='cas') && ($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]['serveur_port']=='*') && !$serveur_port )
  {
    exit_json( FALSE , 'Port manquant !' );
  }

  list($f_connexion_departement,$f_connexion_nom) = explode('|',$f_connexion_ref);

  if( ($f_connexion_mode=='normal') || ($f_connexion_mode=='shibboleth') )
  {
    DB_STRUCTURE_COMMUN::DB_modifier_parametres( array('connexion_mode'=>$f_connexion_mode,'connexion_nom'=>$f_connexion_nom,'connexion_departement'=>$f_connexion_departement) );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']        = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']         = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT'] = $f_connexion_departement;
    exit_json( TRUE );
  }

  if($f_connexion_mode=='cas')
  {
    // Soit le host est saisi manuellement, soit il faut le recomposer si sous-domaine saisi (dans la situation majoritaire où il n'y a pas de sous-domaine variable, le résultat est le même)
    $cas_serveur_host = ($f_connexion_nom=='perso') ? $cas_serveur_host : ( ($serveur_host_subdomain!='') ? ( (substr($serveur_host_subdomain,-1)!='.') ? $serveur_host_subdomain.'.'.$serveur_host_domain : $serveur_host_subdomain.$serveur_host_domain ) : $serveur_host_domain ) ;
    // Cas du port
    $cas_serveur_port = ($tab_connexion_info[$f_connexion_mode][$f_connexion_ref]['serveur_port']!='*') ? $cas_serveur_port : $serveur_port ;
    // Vérifier les paramètres CAS en reprenant le code de phpCAS
    if ( empty($cas_serveur_host) || !preg_match('/[\.\d\-abcdefghijklmnopqrstuvwxyz]*/',$cas_serveur_host) )
    {
      exit_json( FALSE , 'Syntaxe du domaine incorrecte !' );
    }
    if ( ($cas_serveur_port == 0) || !is_int($cas_serveur_port) )
    {
      exit_json( FALSE , 'Numéro du port incorrect !' );
    }
    if ( !preg_match('/[\.\d\-_abcdefghijklmnopqrstuvwxyz\/]*/',$cas_serveur_root) )
    {
      exit_json( FALSE , 'Syntaxe du chemin incorrecte !' );
    }
    // Expression régulière pour tester une URL (pas trop compliquée)
    $masque = '#^http(s)?://[\w-]+[\w.-]+\.[a-zA-Z]{2,6}(:[0-9]+)?#';
    if ( $cas_serveur_url_login && !preg_match($masque,$cas_serveur_url_login) )
    {
      exit_json( FALSE , 'Syntaxe URL login incorrecte !' );
    }
    if ( $cas_serveur_url_logout && !preg_match($masque,$cas_serveur_url_logout) )
    {
      exit_json( FALSE , 'Syntaxe URL logout incorrecte !' );
    }
    if ( $cas_serveur_url_validate && !preg_match($masque,$cas_serveur_url_validate) )
    {
      exit_json( FALSE , 'Syntaxe URL validate incorrecte !' );
    }
    if( is_null($cas_serveur_verif_certif_ssl) )
    {
      exit_json( FALSE , 'Paramètre vérif. certificat SSL manquant !' );
    }
    // Deux tests sauf pour les établissements destinés à tester les connecteurs ENT
    if( !IS_HEBERGEMENT_SESAMATH || ($_SESSION['BASE']<CONVENTION_ENT_ID_ETABL_MAXI) )
    {
      // Ne pas dupliquer en paramétrage CAS-perso un paramétrage CAS-ENT existant (utiliser la connexion CAS officielle)
      if($f_connexion_nom=='perso')
      {
        foreach($tab_serveur_cas as $cas_nom => $tab_cas_param)
        {
          if($cas_nom)
          {
            $is_param_defaut_identiques = ( (strpos($cas_serveur_host,$tab_cas_param['serveur_host_domain'])!==FALSE) && ($cas_serveur_root==$tab_cas_param['serveur_root']) ) ? TRUE : FALSE ; // Pas de test sur le sous-domaine ni le port car ils peuvent varier
            $is_param_force_identiques  = ( ($cas_serveur_url_login!='') && ( ($cas_serveur_url_login==$tab_cas_param['serveur_url_login']) || (strpos($cas_serveur_url_login,$tab_cas_param['serveur_host_domain'].':'.$tab_cas_param['serveur_port'].'/'.$tab_cas_param['serveur_root'])!==FALSE) ) ) ? TRUE : FALSE ;
            if( $is_param_defaut_identiques || $is_param_force_identiques )
            {
              exit_json( FALSE , 'Paramètres d\'un ENT référencé : sélectionnez-le !' );
            }
          }
        }
      }
      // Sur le serveur Sésamath, ne pas autoriser un paramétrage CAS correspondant à un hébergement académique (ne devrait pas se produire, Sésamath n'hébergeant pas ces établissements).
      else if(IS_HEBERGEMENT_SESAMATH)
      {
        if(!is_file(CHEMIN_FICHIER_WS_SESAMATH_ENT))
        {
          exit_json( FALSE , 'Le fichier &laquo;&nbsp;<b>'.FileSystem::fin_chemin(CHEMIN_FICHIER_WS_SESAMATH_ENT).'</b>&nbsp;&raquo; (uniquement présent sur le serveur Sésamath) n\'a pas été détecté !' );
        }  
        require(CHEMIN_FICHIER_WS_SESAMATH_ENT); // Charge les tableaux   $tab_connecteurs_hebergement & $tab_connecteurs_convention
        if( isset($tab_connecteurs_hebergement[$f_connexion_ref]) )
        {
          exit_json( FALSE , 'Paramètres d\'un serveur CAS à utiliser sur l\'hébergement académique dédié !' );
        }
      }
    }
    // C'est ok
    $tab_parametres = array(
      'connexion_mode'               => $f_connexion_mode,
      'connexion_nom'                => $f_connexion_nom,
      'connexion_departement'        => $f_connexion_departement,
      'cas_serveur_host'             => $cas_serveur_host,
      'cas_serveur_port'             => $cas_serveur_port,
      'cas_serveur_root'             => $cas_serveur_root,
      'cas_serveur_url_login'        => $cas_serveur_url_login,
      'cas_serveur_url_logout'       => $cas_serveur_url_logout,
      'cas_serveur_url_validate'     => $cas_serveur_url_validate,
      'cas_serveur_verif_certif_ssl' => $cas_serveur_verif_certif_ssl,
    );
    DB_STRUCTURE_COMMUN::DB_modifier_parametres( $tab_parametres );
    // ne pas oublier de mettre aussi à jour la session (normalement faudrait pas car connecté avec l'ancien mode, mais sinon pb d'initalisation du formulaire)
    $_SESSION['CONNEXION_MODE']                  = $f_connexion_mode;
    $_SESSION['CONNEXION_NOM']                   = $f_connexion_nom;
    $_SESSION['CONNEXION_DEPARTEMENT']           = $f_connexion_departement;
    $_SESSION['CAS_SERVEUR']['HOST']             = $cas_serveur_host;
    $_SESSION['CAS_SERVEUR']['PORT']             = $cas_serveur_port;
    $_SESSION['CAS_SERVEUR']['ROOT']             = $cas_serveur_root;
    $_SESSION['CAS_SERVEUR']['URL_LOGIN']        = $cas_serveur_url_login;
    $_SESSION['CAS_SERVEUR']['URL_LOGOUT']       = $cas_serveur_url_logout;
    $_SESSION['CAS_SERVEUR']['URL_VALIDATE']     = $cas_serveur_url_validate;
    $_SESSION['CAS_SERVEUR']['VERIF_CERTIF_SSL'] = $cas_serveur_verif_certif_ssl;
    exit_json( TRUE );
  }

  if($f_connexion_mode=='gepi')
  {
    // Vérifier les paramètres GEPI-SAML
    // Le RNE n'étant pas obligatoire, et pas forcément un vrai RNE dans Gepi (pour les établ sans UAI, c'est un identifiant choisi...), on ne vérifie rien.
    // Pas de vérif particulière de l'empreinte du certificat non plus, ne sachant pas s'il peut y avoir plusieurs formats.
    // Donc on va se contenter de vraiment vérifier l'URL de Gepi via une requête cURL
    if(strlen($gepi_saml_url)<8)
    {
      exit_json( FALSE , 'Adresse de GEPI manquante !' );
    }
    if(empty($gepi_saml_certif))
    {
      exit_json( FALSE , 'Signature (empreinte du certificat) manquante !' );
    }
    $gepi_saml_url = (substr($gepi_saml_url,-1)=='/') ? substr($gepi_saml_url,0,-1) : $gepi_saml_url ;
    $fichier_distant = cURL::get_contents($gepi_saml_url.'/bandeau.css'); // Le mieux serait d'appeler le fichier du web-services... si un jour il y en a un...
    if(substr($fichier_distant,0,6)=='Erreur')
    {
      exit_json( FALSE , 'Adresse de Gepi incorrecte [ '.$fichier_distant.' ] !' );
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
    exit_json( TRUE );
  }

}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($f_action=='ajouter_convention') && $f_connexion_mode && $f_connexion_ref && in_array($f_annee,array(0,1)) )
{
  if( ($f_connexion_mode!='cas') || (!isset($tab_connexion_info['cas'][$f_connexion_ref])) )
  {
    exit_json( FALSE , 'Erreur avec les données transmises !' );
  }
  // Extraire les infos
  list($f_connexion_departement,$f_connexion_nom) = explode('|',$f_connexion_ref);
  $date_debut_mysql = jour_debut_annee_scolaire('mysql',$f_annee);
  $date_fin_mysql   = jour_fin_annee_scolaire(  'mysql',$f_annee);
  // Vérifier que la convention n'existe pas déjà
  charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  if(DB_WEBMESTRE_ADMINISTRATEUR::DB_tester_convention_precise( $_SESSION['BASE'] , $f_connexion_nom , $date_debut_mysql ))
  {
    exit_json( FALSE , 'Erreur : convention déjà existante pour ce service sur cette période !' );
  }
  // Insérer l'enregistrement
  $convention_id = DB_WEBMESTRE_ADMINISTRATEUR::DB_ajouter_convention( $_SESSION['BASE'] , $f_connexion_nom , $date_debut_mysql , $date_fin_mysql );
  // Afficher le retour
  $retour = '<tr id="id_'.$convention_id.'" class="new">';
  $retour.=   '<td>'.html($f_connexion_nom).'</td>';
  $retour.=   '<td>du '.convert_date_mysql_to_french($date_debut_mysql).' au '.convert_date_mysql_to_french($date_fin_mysql).'</td>';
  $retour.=   '<td>'.TODAY_FR.'</td>';
  $retour.=   '<td class="br">Non réceptionné</td>';
  $retour.=   '<td class="br">Non réceptionné</td>';
  $retour.=   '<td class="br">Non</td>';
  $retour.=   '<td class="nu"><q class="voir_archive" title="Récupérer / Imprimer les documents associés."></q></td>';
  $retour.= '</tr>';
  $tab_retour = array( 'convention_id'=>$convention_id , 'tr'=>$retour );
  exit_json( TRUE, $tab_retour );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Imprimer les documents associés à une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($f_action=='imprimer_documents') && $f_convention_id && in_array($f_first_time,array('oui','non')) )
{
  // Récupération et vérification des infos de la convention
  charger_parametres_mysql_supplementaires( 0 /*BASE*/ );
  $DB_ROW = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_convention($f_convention_id);
  if(empty($DB_ROW))
  {
    exit_json( FALSE , 'Erreur : convention non trouvée !' );
  }
  if($DB_ROW['sacoche_base']!=$_SESSION['BASE'])
  {
    exit_json( FALSE , 'Erreur : convention d\'une autre structure !' );
  }
  // Coordonnées de l'établissement
  $tab_etabl_coords = array( 0 => $_SESSION['ETABLISSEMENT']['DENOMINATION'] );
  if($_SESSION['ETABLISSEMENT']['ADRESSE1'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE1']; }
  if($_SESSION['ETABLISSEMENT']['ADRESSE2'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE2']; }
  if($_SESSION['ETABLISSEMENT']['ADRESSE3'])  { $tab_etabl_coords[] = $_SESSION['ETABLISSEMENT']['ADRESSE3']; }
  if($_SESSION['ETABLISSEMENT']['TELEPHONE']) { $tab_etabl_coords[] = 'Tel : '.$_SESSION['ETABLISSEMENT']['TELEPHONE']; }
  if($_SESSION['ETABLISSEMENT']['FAX'])       { $tab_etabl_coords[] = 'Fax : '.$_SESSION['ETABLISSEMENT']['FAX']; }
  if($_SESSION['ETABLISSEMENT']['COURRIEL'])  { $tab_etabl_coords[] = 'Mel : '.$_SESSION['ETABLISSEMENT']['COURRIEL']; }
  if($_SESSION['ETABLISSEMENT']['URL'])       { $tab_etabl_coords[] = 'Web : '.$_SESSION['ETABLISSEMENT']['URL']; }
  // Coordonnées du contact référent
  $DB_ROW2 = DB_WEBMESTRE_ADMINISTRATEUR::DB_recuperer_contact_infos($_SESSION['BASE']);
  $tab_etabl_coords[] = '';
  $tab_etabl_coords[] = 'Contact référent pour SACoche :';
  $tab_etabl_coords[] = $DB_ROW2['structure_contact_nom'].' '.$DB_ROW2['structure_contact_prenom'];
  $tab_etabl_coords[] = 'Mel : '.$DB_ROW2['structure_contact_courriel'];
  // référence du connecteur
  $connecteur_ref = $_SESSION['BASE'].' . '.$f_convention_id.' . '.$DB_ROW['connexion_nom'];
  //
  // Imprimer le contrat.
  //
  $contrat_PDF = new FPDI( NULL /*make_officiel*/ , 'portrait' /*orientation*/ , 15 /*marge_gauche*/ , 15 /*marge_droite*/ , 10 /*marge_haut*/ , 15 /*marge_bas*/ , 'oui' /*couleur*/ , 'non' /*legende*/ , NULL /*filigrane*/ );
  $contrat_PDF->setSourceFile(CHEMIN_DOSSIER_WEBSERVICES.'sesamath_ent_convention_sacoche_etablissement_contrat.pdf');
  $hauteur_ligne = 5.5;
  $marge_bordure = 1;
  $taille_police = 14;
  // Boucle pour l'exemplaire à conserver et l'exemplaire à renvoyer
  for( $numero_exemplaire=0 ; $numero_exemplaire<2 ; $numero_exemplaire++ )
  {
    // ajouter une page ; y importer la page 1 ; l'utiliser comme support
    $contrat_PDF->AddPage();
    $tplIdx = $contrat_PDF->importPage(1);
    $contrat_PDF->useTemplate($tplIdx);
    // numéro
    $contrat_PDF->SetFont('Arial','',$taille_police);
    $contrat_PDF->choisir_couleur_fond('gris_clair');
    $contrat_PDF->SetXY(130,10);
    $contrat_PDF->CellFit( 50 , $hauteur_ligne , To::pdf('Convention n°'.$f_convention_id) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    // établissement
    $contrat_PDF->Rect( 120-$marge_bordure , 20-$marge_bordure , 70+2*$marge_bordure , $hauteur_ligne*count($tab_etabl_coords)+2*$marge_bordure , 'D' );
    $contrat_PDF->SetXY(120,20);
    foreach($tab_etabl_coords as $ligne)
    {
      $contrat_PDF->CellFit( 70 , $hauteur_ligne , To::pdf($ligne) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    }
    // type d'exemplaire
    $contrat_PDF->SetFont('Arial','B',$taille_police);
    $contrat_PDF->choisir_couleur_fond('gris_moyen');
    $contrat_PDF->SetXY(20,70);
    if($numero_exemplaire==0)
    {
      $contrat_PDF->CellFit( 70 , $hauteur_ligne , To::pdf('Exemplaire à retourner à :') , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
      $contrat_PDF->Rect( 20 , 70+$hauteur_ligne , 70 , $hauteur_ligne*3+2*$marge_bordure , 'DF' );
      $contrat_PDF->SetXY(20+$marge_bordure,70+$hauteur_ligne+$marge_bordure);
      $contrat_PDF->CellFit( 70-2*$marge_bordure , $hauteur_ligne , To::pdf('M. RINDEL Christophe') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $contrat_PDF->CellFit( 70-2*$marge_bordure , $hauteur_ligne , To::pdf('32 Résidence la clé des champs') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
      $contrat_PDF->CellFit( 70-2*$marge_bordure , $hauteur_ligne , To::pdf('80160 Plachy Buyon') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    }
    else
    {
      $contrat_PDF->CellFit( 70 , $hauteur_ligne , To::pdf('Exemplaire à conserver') , 1 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
    }
    // référence du connecteur
    $contrat_PDF->SetXY(40,116);
    $contrat_PDF->CellFit( 100 , $hauteur_ligne , To::pdf($connecteur_ref) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // connecteur
    $contrat_PDF->SetXY(93,142);
    $contrat_PDF->CellFit( 100 , $hauteur_ligne , To::pdf('"'.$DB_ROW['connexion_nom'].'"') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // date fin
    $contrat_PDF->SetXY(90,216.5);
    $contrat_PDF->CellFit( 100 , $hauteur_ligne , To::pdf(convert_date_mysql_to_french($DB_ROW['convention_date_fin']).'.') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // date création
    $contrat_PDF->SetXY(121,237.5);
    $contrat_PDF->CellFit( 80 , $hauteur_ligne , To::pdf(convert_date_mysql_to_french($DB_ROW['convention_creation']).'.') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
    // signature
    $contrat_PDF->Image( CHEMIN_DOSSIER_WEBSERVICES.'sesamath_ent_conventions_sacoche_etablissement_signature.png' , 30 /*x*/ , 254 /*y*/ , 254*0.2 /*largeur*/ , 158*0.2 /*hauteur*/ , 'PNG' );
    // ajouter une page ; y importer la page 2 ; l'utiliser comme support
    $contrat_PDF->AddPage();
    $tplIdx = $contrat_PDF->importPage(2);
    $contrat_PDF->useTemplate($tplIdx);
  }
  // On enregistre la sortie PDF
  $contrat_fichier_nom = 'convention_contrat_'.fabriquer_fin_nom_fichier__date_et_alea().'.pdf';
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$contrat_fichier_nom , $contrat_PDF );
  //
  // Imprimer la facture.
  //
  $facture_PDF = new FPDI( NULL /*make_officiel*/ , 'portrait' /*orientation*/ , 15 /*marge_gauche*/ , 15 /*marge_droite*/ , 10 /*marge_haut*/ , 15 /*marge_bas*/ , 'oui' /*couleur*/ , 'non' /*legende*/ , NULL /*filigrane*/ );
  $facture_PDF->setSourceFile(CHEMIN_DOSSIER_WEBSERVICES.'sesamath_ent_convention_sacoche_etablissement_facture.pdf');
  // ajouter une page ; y importer la page 1 ; l'utiliser comme support
  $facture_PDF->AddPage();
  $tplIdx = $facture_PDF->importPage(1);
  $facture_PDF->useTemplate($tplIdx);
  // numéro
  $facture_PDF->SetFont('Arial','',$taille_police);
  $facture_PDF->choisir_couleur_fond('gris_clair');
  $facture_PDF->SetXY(130,10);
  $facture_PDF->CellFit( 50 , $hauteur_ligne , To::pdf('Facture n°'.$f_convention_id) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , TRUE /*remplissage*/ );
  // établissement
  $facture_PDF->Rect( 120-$marge_bordure , 20-$marge_bordure , 70+2*$marge_bordure , $hauteur_ligne*count($tab_etabl_coords)+2*$marge_bordure , 'D' );
  $facture_PDF->SetXY(120,20);
  foreach($tab_etabl_coords as $ligne)
  {
    $facture_PDF->CellFit( 70 , $hauteur_ligne , To::pdf($ligne) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  }
  // date création
  $facture_PDF->SetXY(14,99);
  $facture_PDF->CellFit( 70 , $hauteur_ligne , To::pdf('À Erôme, le '.convert_date_mysql_to_french($DB_ROW['convention_creation']).'.') , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  // référence du connecteur
  $facture_PDF->SetFont('Arial','B',$taille_police);
  $facture_PDF->SetXY(17,138);
  $facture_PDF->CellFit( 100 , $hauteur_ligne , To::pdf($connecteur_ref) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
  // période du connecteur
  $facture_PDF->SetFont('Arial','B',$taille_police);
  $facture_PDF->SetXY(17,144);
  $facture_PDF->CellFit( 100 , $hauteur_ligne , To::pdf('du '.convert_date_mysql_to_french($DB_ROW['convention_date_debut']).' au '.convert_date_mysql_to_french($DB_ROW['convention_date_fin'])) , 0 /*bordure*/ , 2 /*br*/ , 'C' /*alignement*/ , FALSE /*remplissage*/ );
  // date de mise en service
  $connecteur_date_debut_mise_en_service = ($DB_ROW['convention_signature']!==NULL) ? max($DB_ROW['convention_date_debut'],$DB_ROW['convention_signature']) : $DB_ROW['convention_date_debut'] ;
  $texte = ($DB_ROW['convention_signature']!==NULL) ? 'du '.convert_date_mysql_to_french($connecteur_date_debut_mise_en_service) : 'de la réception du contrat' ;
  $texte.= ' au '.convert_date_mysql_to_french($DB_ROW['convention_date_fin']).'.';
  $facture_PDF->SetXY(78,166);
  $facture_PDF->CellFit( 100 , $hauteur_ligne , To::pdf($texte) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  // date de règlement
  if($DB_ROW['convention_paiement']!==NULL)
  {
    $texte = 'Règlement acquitté le '.convert_date_mysql_to_french($DB_ROW['convention_paiement']).'.';
  }
  elseif($DB_ROW['convention_signature']!==NULL)
  {
    list($annee,$mois,$jour) = explode('-',$DB_ROW['convention_signature']);
    $timeunix_signature_plus2mois = mktime(0,0,0,$mois+2,$jour,$annee);
    list($annee,$mois,$jour) = explode('-',$DB_ROW['convention_date_debut']);
    $timeunix_anneescolaire_plus3mois = mktime(0,0,0,$mois+2,$jour,$annee);
    $date_limite = date("d/m/Y", max($timeunix_signature_plus2mois,$timeunix_anneescolaire_plus3mois) );
    $texte = 'Date limite de règlement : '.$date_limite.'.';
  }
  else
  {
    $texte = 'Date limite de règlement : 2 mois après la mise en service effective du connecteur.';
  }
  $facture_PDF->SetXY(20,174);
  $facture_PDF->CellFit( 180 , $hauteur_ligne , To::pdf($texte) , 0 /*bordure*/ , 2 /*br*/ , 'L' /*alignement*/ , FALSE /*remplissage*/ );
  // On enregistre la sortie PDF
  $facture_fichier_nom = 'convention_facture_'.fabriquer_fin_nom_fichier__date_et_alea().'.pdf';
  FileSystem::ecrire_sortie_PDF( CHEMIN_DOSSIER_EXPORT.$facture_fichier_nom , $facture_PDF );
  //
  // Envoyer un courriel au contact.
  //
  if($f_first_time=='oui')
  {
    $titre = 'Convention connecteur ENT établissement - Documents générés';
    $texte = 'Bonjour '.$DB_ROW2['structure_contact_prenom'].' '.$DB_ROW2['structure_contact_nom'].','."\r\n";
    $texte.= "\r\n";
    $texte.= 'Vous venez de générer les documents associés à une convention pour un connecteur ENT :'."\r\n";
    $texte.= 'Référence : '.$connecteur_ref."\r\n";
    $texte.= 'Établissement : '.$_SESSION['ETABLISSEMENT']['DENOMINATION']."\r\n";
    $texte.= 'Période : du '.convert_date_mysql_to_french($DB_ROW['convention_date_debut']).' au '.convert_date_mysql_to_french($DB_ROW['convention_date_fin'])."\r\n";
    $texte.= "\r\n";
    $texte.= 'Le contrat est en deux exemplaires.'."\r\n";
    $texte.= 'L\'un est à retourner signé au président de l\'association (ses coordonnées postales figurent sur le document).'."\r\n";
    $texte.= 'L\'autre est à conserver par votre établissement.'."\r\n";
    $texte.= "\r\n";
    $texte.= 'La facture comporte les coordonnées bancaires de l\'association.'."\r\n";
    $texte.= 'Votre service gestionnaire peut régler par mandat administratif.'."\r\n";
    $texte.= "\r\n";
    $texte.= 'Ces documents vous resteront accessibles en vous connectant comme administrateur puis en vous rendant dans le menu [Paramétrages établissement] [Mode d\'identification / Connecteur ENT] (cliquer alors sur l\'icône en bout de ligne du tableau).'."\r\n";
    $texte.= URL_DIR_SACOCHE.'?id='.$_SESSION['BASE']."\r\n";
    $texte.= "\r\n";
    if($DB_ROW['convention_date_debut']<TODAY_MYSQL)
    {
      $texte.= 'Dès réception du contrat (ou perception du règlement), votre connecteur ENT sera automatiquement activé.'."\r\n";
    }
    else
    {
      $texte.= 'La réception du contrat (ou la perception du règlement) entrainera l\'activation automatique de votre connecteur ENT au '.convert_date_mysql_to_french($DB_ROW['convention_date_debut']).' (changement d\'année scolaire).'."\r\n";
    }
    $texte.= 'Un courriel est alors envoyé au contact référent pour l\'en informer.'."\r\n";
    $texte.= 'Vous disposez de 2 mois à compter de l\'activation du connecteur ENT pour le tester et nous faire parvenir votre règlement (ou le contrat).'."\r\n";
    $texte.= "\r\n";
    $texte.= 'Nous vous remercions de votre confiance et de votre soutien.'."\r\n";
    $texte.= "\r\n";
    $texte.= 'Remarque : si vous ne souhaitez pas donner suite à cette convention, il vous suffit de ne rien envoyer et de sélectionner une connexion avec les identifiants de SACoche.'."\r\n";
    $texte.= "\r\n";
    $texte.= 'Cordialement,'."\r\n";
    $texte.= WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM."\r\n";
    $texte.= 'Responsable SACoche pour Sésamath'."\r\n";
    $texte.= "\r\n";
    $courriel_bilan = Sesamail::mail( $DB_ROW2['structure_contact_courriel'] , $titre , $texte ); // Ce serait mieux si le Reply-To était MAIL_SACOCHE_CONTACT mais cette contante n'est pas connue ici...
    if(!$courriel_bilan)
    {
      exit_json( FALSE , 'Envoi du courriel infructueux !' );
    }
  }
  // Retour des informations.
  exit_json( TRUE ,  array( 'fichier_contrat'=>URL_DIR_EXPORT.$contrat_fichier_nom , 'fichier_facture'=>URL_DIR_EXPORT.$facture_fichier_nom ) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit_json( FALSE , 'Erreur avec les données transmises !' );

?>
