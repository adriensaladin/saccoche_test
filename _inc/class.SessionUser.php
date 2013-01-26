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

class SessionUser
{

  // //////////////////////////////////////////////////
  // Méthodes publiques
  // //////////////////////////////////////////////////

  /**
   * Tester si mdp du webmestre transmis convient.
   * 
   * @param string    $password
   * @return string   'ok' ou un message d'erreur
   */
  public static function tester_authentification_webmestre($password)
  {
    // Si tentatives trop rapprochées...
    $delai_tentative_secondes = time() - WEBMESTRE_ERREUR_DATE ;
    if($delai_tentative_secondes<3)
    {
      FileSystem::fabriquer_fichier_hebergeur_info( array('WEBMESTRE_ERREUR_DATE'=>time()) );
      return'Calmez-vous et patientez 10s avant toute nouvelle tentative !';
    }
    elseif($delai_tentative_secondes<10)
    {
      $delai_attente_restant = 10-$delai_tentative_secondes ;
      return'Merci d\'attendre encore '.$delai_attente_restant.'s avant une nouvelle tentative.';
    }
    // Si mdp incorrect...
    $password_crypte = crypter_mdp($password);
    if($password_crypte!=WEBMESTRE_PASSWORD_MD5)
    {
      FileSystem::fabriquer_fichier_hebergeur_info( array('WEBMESTRE_ERREUR_DATE'=>time()) );
      return'Mot de passe incorrect ! Patientez 10s avant une nouvelle tentative.';
    }
    // Si on arrive ici c'est que l'identification s'est bien effectuée !
    return'ok';
  }

  /**
   * Tester si les données transmises permettent d'authentifier un utilisateur (sauf webmestre).
   * 
   * En cas de connexion avec les identifiants SACoche, la reconnaissance s'effectue sur le couple login/password.
   * En cas de connexion depuis un service SSO extérieur type CAS, la reconnaissance s'effectue en comparant l'identifiant transmis (via $login) avec l'id ENT de jointure connu de SACoche.
   * En cas de connexion utilisant GEPI, la reconnaissance s'effectue en comparant le login GEPI transmis avec l'id Gepi de jointure connu de SACoche.
   * 
   * @param int       $BASE
   * @param string    $login
   * @param string    $password
   * @param string    $mode_connection   'normal' | 'cas' | 'gepi' | 'ldap' (?)
   * @return array(string,array)   ('ok',$DB_ROW) ou (message_d_erreur,tableau_vide)
   */
  public static function tester_authentification_utilisateur($BASE,$login,$password,$mode_connection)
  {
    // En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
    // Sauf pour une connexion à un ENT, car alors il a déjà fallu les charger pour récupérer les paramètres de connexion à l'ENT
    if( ($BASE) && ($mode_connection=='normal') )
    {
      charger_parametres_mysql_supplementaires($BASE);
    }
    // Récupérer les données associées à l'utilisateur.
    $DB_ROW = DB_STRUCTURE_PUBLIC::DB_recuperer_donnees_utilisateur($mode_connection,$login);
    // Si login non trouvé...
    if(empty($DB_ROW))
    {
      switch($mode_connection)
      {
        case 'normal' : $message = 'Nom d\'utilisateur incorrect !'; break;
        case 'cas'    : $message = 'Identification réussie mais identifiant SSO "'.$login.'" inconnu dans SACoche !<br />Un administrateur doit renseigner que l\'identifiant ENT associé à votre compte SACoche est "'.$login.'"&hellip;<br />Il doit pour cela se connecter à SACoche, menu [Gestion&nbsp;courante], et indiquer "'.$login.'" dans la case [Id.&nbsp;ENT] de la ligne correspondant à votre compte.'; break;
        case 'gepi'   : $message = 'Identification réussie mais login GEPI "'.$login.'" inconnu dans SACoche !<br />Un administrateur doit renseigner que l\'identifiant GEPI associé à votre compte SACoche est "'.$login.'"&hellip;<br />Il doit pour cela se connecter à SACoche, menu [Gestion&nbsp;courante], et indiquer "'.$login.'" dans la case [Id.&nbsp;Gepi] de la ligne correspondant à votre compte.'; break;
      }
      return array($message,array());
    }
    // Blocage éventuel par le webmestre ou un administrateur ou l'automate
    LockAcces::stopper_si_blocage( $BASE , $DB_ROW['user_profil'] );
    // Si tentatives trop rapprochées...
    if($DB_ROW['user_tentative_date']!==NULL) // Sinon $DB_ROW['delai_tentative_secondes'] vaut NULL
    {
      if($DB_ROW['delai_tentative_secondes']<3)
      {
        DB_STRUCTURE_PUBLIC::DB_enregistrer_date( 'tentative' , $DB_ROW['user_id'] );
        return array('Calmez-vous et patientez 10s avant toute nouvelle tentative !',array());
      }
      elseif($DB_ROW['delai_tentative_secondes']<10)
      {
        $delai_attente_restant = 10 - $DB_ROW['delai_tentative_secondes'] ;
        return array('Merci d\'attendre encore '.$delai_attente_restant.'s avant une nouvelle tentative.',array());
      }
    }
    // Si mdp incorrect...
    if( ($mode_connection=='normal') && ($DB_ROW['user_password']!=crypter_mdp($password)) )
    {
      DB_STRUCTURE_PUBLIC::DB_enregistrer_date( 'tentative' , $DB_ROW['user_id'] );
      return array('Mot de passe incorrect ! Patientez 10s avant une nouvelle tentative.',array());
    }
    // Si compte desactivé...
    if($DB_ROW['user_sortie_date']<=TODAY_MYSQL)
    {
      return array('Identification réussie mais ce compte est desactivé !',array());
    }
    // Mémoriser la date de la (dernière) connexion (pour les autres cas, sera enregistré lors de la confirmation de la prise en compte des infos CNIL).
    if( ($DB_ROW['user_connexion_date']!==NULL) || in_array($DB_ROW['user_profil'],array('webmestre','administrateur')) )
    {
      DB_STRUCTURE_PUBLIC::DB_enregistrer_date( 'connexion' , $DB_ROW['user_id'] );
    }
    // Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
    setcookie( COOKIE_STRUCTURE /*name*/ , $BASE /*value*/ , time()+31536000 /*expire*/ , '/' /*path*/ , getServerUrl() /*domain*/ ); /* 60*60*24*365 */
    // Enregistrement d'un cookie sur le poste client servant à retenir le dernier mode de connexion utilisé si identification avec succès
    setcookie( COOKIE_AUTHMODE /*name*/ , $mode_connection /*value*/ , 0 /*expire*/ , '/' /*path*/ , getServerUrl() /*domain*/ );
    // Si on arrive ici c'est que l'identification s'est bien effectuée !
    return array('ok',$DB_ROW);
  }

  /**
   * Enregistrer en session les informations authentifiant le webmestre.
   * 
   * @param void
   * @return void
   */
  public static function initialiser_webmestre()
  {
    // Numéro de la base
    $_SESSION['BASE']                          = 0;
    // Données associées à l'utilisateur.
    $_SESSION['USER_PROFIL']                   = 'webmestre';
    $_SESSION['USER_ID']                       = 0;
    $_SESSION['USER_NOM']                      = WEBMESTRE_NOM;
    $_SESSION['USER_PRENOM']                   = WEBMESTRE_PRENOM;
    $_SESSION['USER_DESCR']                    = '[webmestre] '.WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM;
    // Données associées à l'établissement.
    $_SESSION['SESAMATH_ID']                   = 0;
    $_SESSION['ETABLISSEMENT']['DENOMINATION'] = 'Gestion '.HEBERGEUR_INSTALLATION;
    $_SESSION['MODE_CONNEXION']                = 'normal';
    $_SESSION['DUREE_INACTIVITE']              = 15;
    $_SESSION['MDP_LONGUEUR_MINI']             = 6;
  }

  /**
   * Enregistrer en session les informations authentifiant un utilisateur (sauf le webmestre).
   * 
   * @param int     $BASE
   * @param array   $DB_ROW   ligne issue de la table sacoche_user correspondant à l'utilisateur qui se connecte.
   * @return void
   */
  public static function initialiser_utilisateur($BASE,$DB_ROW)
  {
    // On en profite pour effacer les fichiers inutiles
    FileSystem::nettoyer_fichiers_temporaires($BASE);
    // Enregistrer en session le numéro de la base
    $_SESSION['BASE']               = $BASE;
    // Enregistrer en session les données associées à l'utilisateur (indices du tableau de session en majuscules).
    $_SESSION['USER_PROFIL']        = $DB_ROW['user_profil'];
    $_SESSION['USER_ID']            = (int) $DB_ROW['user_id'];
    $_SESSION['USER_NOM']           = $DB_ROW['user_nom'];
    $_SESSION['USER_PRENOM']        = $DB_ROW['user_prenom'];
    $_SESSION['USER_LOGIN']         = $DB_ROW['user_login'];
    $_SESSION['USER_DESCR']         = '['.$DB_ROW['user_profil'].'] '.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'];
    $_SESSION['USER_DALTONISME']    = $DB_ROW['user_daltonisme'];
    $_SESSION['USER_ID_ENT']        = $DB_ROW['user_id_ent'];
    $_SESSION['USER_ID_GEPI']       = $DB_ROW['user_id_gepi'];
    $_SESSION['USER_PARAM_ACCUEIL'] = $DB_ROW['user_param_accueil'];
    $_SESSION['ELEVE_CLASSE_ID']    = (int) $DB_ROW['eleve_classe_id'];
    $_SESSION['ELEVE_CLASSE_NOM']   = $DB_ROW['groupe_nom'];
    $_SESSION['ELEVE_LANGUE']       = (int) $DB_ROW['eleve_langue'];
    $_SESSION['DELAI_CONNEXION']    = (int) $DB_ROW['delai_connexion_secondes']; // Vaut (int)NULL = 0 à la 1e connexion, mais dans ce cas $_SESSION['FIRST_CONNEXION'] est testé avant.
    $_SESSION['FIRST_CONNEXION']    = ($DB_ROW['user_connexion_date']===NULL) ? TRUE : FALSE ;
    if( ($DB_ROW['user_connexion_date']===NULL) && !in_array($DB_ROW['user_profil'],array('webmestre','administrateur')) )
    {
      $_SESSION['STOP_CNIL'] = TRUE;
    }
    // Récupérer et Enregistrer en session les données des élèves associées à un responsable légal.
    if($_SESSION['USER_PROFIL']=='parent')
    {
      $_SESSION['OPT_PARENT_ENFANTS'] = DB_STRUCTURE_COMMUN::DB_OPT_enfants_parent($_SESSION['USER_ID']);
      $_SESSION['OPT_PARENT_CLASSES'] = DB_STRUCTURE_COMMUN::DB_OPT_classes_parent($_SESSION['USER_ID']);
      $_SESSION['NB_ENFANTS'] = (is_array($_SESSION['OPT_PARENT_ENFANTS'])) ? count($_SESSION['OPT_PARENT_ENFANTS']) : 0 ;
      if( ($_SESSION['NB_ENFANTS']==1) && (is_array($_SESSION['OPT_PARENT_CLASSES'])) )
      {
        $_SESSION['ELEVE_CLASSE_ID']  = (int) $_SESSION['OPT_PARENT_CLASSES'][0]['valeur'];
        $_SESSION['ELEVE_CLASSE_NOM'] = $_SESSION['OPT_PARENT_CLASSES'][0]['texte'];
      }
    }
    // Récupérer et Enregistrer en session les données associées à l'établissement (indices du tableau de session en majuscules).
    $DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres();
    $tab_type_entier  = array(
      'SESAMATH_ID','MOIS_BASCULE_ANNEE_SCOLAIRE','MDP_LONGUEUR_MINI','DROIT_ELEVE_DEMANDES','DUREE_INACTIVITE',
      'CALCUL_VALEUR_RR','CALCUL_VALEUR_R','CALCUL_VALEUR_V','CALCUL_VALEUR_VV','CALCUL_SEUIL_R','CALCUL_SEUIL_V','CALCUL_LIMITE',
      'CAS_SERVEUR_PORT',
      'ENVELOPPE_HORIZONTAL_GAUCHE','ENVELOPPE_HORIZONTAL_MILIEU','ENVELOPPE_HORIZONTAL_DROITE',
      'ENVELOPPE_VERTICAL_HAUT','ENVELOPPE_VERTICAL_MILIEU','ENVELOPPE_VERTICAL_BAS',
      'OFFICIEL_MARGE_GAUCHE','OFFICIEL_MARGE_DROITE','OFFICIEL_MARGE_HAUT','OFFICIEL_MARGE_BAS',
      'OFFICIEL_RELEVE_ONLY_SOCLE','OFFICIEL_RELEVE_APPRECIATION_RUBRIQUE','OFFICIEL_RELEVE_APPRECIATION_GENERALE','OFFICIEL_RELEVE_ASSIDUITE','OFFICIEL_RELEVE_ETAT_ACQUISITION','OFFICIEL_RELEVE_MOYENNE_SCORES','OFFICIEL_RELEVE_POURCENTAGE_ACQUIS','OFFICIEL_RELEVE_CONVERSION_SUR_20','OFFICIEL_RELEVE_CASES_NB','OFFICIEL_RELEVE_AFF_COEF','OFFICIEL_RELEVE_AFF_SOCLE','OFFICIEL_RELEVE_AFF_DOMAINE','OFFICIEL_RELEVE_AFF_THEME',
      'OFFICIEL_BULLETIN_ONLY_SOCLE','OFFICIEL_BULLETIN_APPRECIATION_RUBRIQUE','OFFICIEL_BULLETIN_APPRECIATION_GENERALE','OFFICIEL_BULLETIN_ASSIDUITE','BULLETIN_BARRE_ACQUISITIONS','OFFICIEL_BULLETIN_MOYENNE_SCORES','OFFICIEL_BULLETIN_CONVERSION_SUR_20','OFFICIEL_BULLETIN_MOYENNE_CLASSE','OFFICIEL_BULLETIN_MOYENNE_GENERALE',
      'OFFICIEL_SOCLE_APPRECIATION_RUBRIQUE','OFFICIEL_SOCLE_APPRECIATION_GENERALE','OFFICIEL_SOCLE_ONLY_PRESENCE','OFFICIEL_SOCLE_POURCENTAGE_ACQUIS','OFFICIEL_SOCLE_ETAT_VALIDATION'
    );
    $tab_type_tableau = array(
      'CSS_BACKGROUND-COLOR','CALCUL_VALEUR','CALCUL_SEUIL','NOTE_TEXTE','NOTE_LEGENDE','ACQUIS_TEXTE','ACQUIS_LEGENDE',
      'ETABLISSEMENT','ENVELOPPE','OFFICIEL'
    );
    foreach($DB_TAB as $DB_ROW)
    {
      $parametre_nom = strtoupper($DB_ROW['parametre_nom']);
      // Certains paramètres sont de type entier.
      $parametre_valeur = (in_array($parametre_nom,$tab_type_entier)) ? (int) $DB_ROW['parametre_valeur'] : $DB_ROW['parametre_valeur'] ;
      // Certains paramètres sont à enregistrer sous forme de tableau.
      $find = FALSE;
      foreach($tab_type_tableau as $key1)
      {
        $longueur_key1 = strlen($key1);
        if(substr($parametre_nom,0,$longueur_key1)==$key1)
        {
          $key2 = substr($parametre_nom,$longueur_key1+1);
          $_SESSION[$key1][$key2] = $parametre_valeur ;
          $find = TRUE;
          break;
        }
      }
      // Les autres paramètres sont à enregistrer tels quels.
      if(!$find)
      {
        $_SESSION[$parametre_nom] = $parametre_valeur ;
      }
    }
    // Fabriquer $_SESSION['NOTE_DOSSIER'] et $_SESSION['BACKGROUND_...'] en fonction de $_SESSION['USER_DALTONISME'] à partir de $_SESSION['NOTE_IMAGE_STYLE'] et $_SESSION['CSS_BACKGROUND-COLOR']['...']
    // remarque : $_SESSION['USER_DALTONISME'] ne peut être utilisé que pour les profils élèves/parents/profs/directeurs, pas les admins ni le webmestre
    SessionUser::adapter_daltonisme() ;
    // Enregistrer en session le CSS personnalisé
    SessionUser::actualiser_style();
    // Juste pour davantage de lisibilité si besoin de debug...
    ksort($_SESSION);
  }

  /**
   * Compléter la session avec les informations de style dépendant du daltonisme + des choix paramétrés au niveau de l'établissement (couleurs, codes de notation).
   * 
   * @param void
   * @return void
   */
  public static function adapter_daltonisme()
  {
    // codes de notation
    $_SESSION['NOTE_DOSSIER']  = $_SESSION['USER_DALTONISME'] ? 'Dalton'  : $_SESSION['NOTE_IMAGE_STYLE'] ;
    // couleurs des états d'acquisition
    $_SESSION['BACKGROUND_NA'] = $_SESSION['USER_DALTONISME'] ? '#909090' : $_SESSION['CSS_BACKGROUND-COLOR']['NA'] ;
    $_SESSION['BACKGROUND_VA'] = $_SESSION['USER_DALTONISME'] ? '#BEBEBE' : $_SESSION['CSS_BACKGROUND-COLOR']['VA'] ;
    $_SESSION['BACKGROUND_A']  = $_SESSION['USER_DALTONISME'] ? '#EAEAEA' : $_SESSION['CSS_BACKGROUND-COLOR']['A'] ;
    // couleurs des états de validation
    $_SESSION['BACKGROUND_V0'] = $_SESSION['USER_DALTONISME'] ? '#909090' : '#FF9999' ; // validation négative
    $_SESSION['BACKGROUND_V1'] = $_SESSION['USER_DALTONISME'] ? '#EAEAEA' : '#99FF99' ; // validation positive
    $_SESSION['BACKGROUND_V2'] = $_SESSION['USER_DALTONISME'] ? '#BEBEBE' : '#BBBBFF' ; // validation en attente
    $_SESSION['OPACITY']       = $_SESSION['USER_DALTONISME'] ? 1         : 0.3 ;
  }

  /**
   * Compléter la session avec les informations de style dépendant des choix paramétrés au niveau de l'établissement (couleurs, codes de notation).
   * 
   * @param void
   * @return void
   */
  public static function actualiser_style()
  {
    $_SESSION['CSS']  = '';
    // codes de notation
    $_SESSION['CSS'] .= 'table.scor_eval tbody.h td input.RR {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/h/RR.gif) no-repeat center center;}';
    $_SESSION['CSS'] .= 'table.scor_eval tbody.v td input.RR {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/v/RR.gif) no-repeat center center;}';
    $_SESSION['CSS'] .= 'table.scor_eval tbody.h td input.R  {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/h/R.gif)  no-repeat center center;}';
    $_SESSION['CSS'] .= 'table.scor_eval tbody.v td input.R  {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/v/R.gif)  no-repeat center center;}';
    $_SESSION['CSS'] .= 'table.scor_eval tbody.h td input.V  {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/h/V.gif)  no-repeat center center;}';
    $_SESSION['CSS'] .= 'table.scor_eval tbody.v td input.V  {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/v/V.gif)  no-repeat center center;}';
    $_SESSION['CSS'] .= 'table.scor_eval tbody.h td input.VV {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/h/VV.gif) no-repeat center center;}';
    $_SESSION['CSS'] .= 'table.scor_eval tbody.v td input.VV {background:#FFF url(./_img/note/'.$_SESSION['NOTE_DOSSIER'].'/v/VV.gif) no-repeat center center;}';
    // couleurs des états d'acquisition
    $_SESSION['CSS'] .= 'table th.r , table td.r , div.r ,span.r ,label.r {background-color:'.$_SESSION['BACKGROUND_NA'].'}';
    $_SESSION['CSS'] .= 'table th.o , table td.o , div.o ,span.o ,label.o {background-color:'.$_SESSION['BACKGROUND_VA'].'}';
    $_SESSION['CSS'] .= 'table th.v , table td.v , div.v ,span.v ,label.v {background-color:'.$_SESSION['BACKGROUND_A'].'}';
    // couleurs des états de validation
    $_SESSION['CSS'] .= '#tableau_validation tbody th.down0 {background:'.$_SESSION['BACKGROUND_V0'].' url(./_img/socle/arrow_down.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.down1 {background:'.$_SESSION['BACKGROUND_V1'].' url(./_img/socle/arrow_down.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.down2 {background:'.$_SESSION['BACKGROUND_V2'].' url(./_img/socle/arrow_down.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.left0 {background:'.$_SESSION['BACKGROUND_V0'].' url(./_img/socle/arrow_left.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.left1 {background:'.$_SESSION['BACKGROUND_V1'].' url(./_img/socle/arrow_left.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.left2 {background:'.$_SESSION['BACKGROUND_V2'].' url(./_img/socle/arrow_left.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.diag0 {background:'.$_SESSION['BACKGROUND_V0'].' url(./_img/socle/arrow_diag.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.diag1 {background:'.$_SESSION['BACKGROUND_V1'].' url(./_img/socle/arrow_diag.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= '#tableau_validation tbody th.diag2 {background:'.$_SESSION['BACKGROUND_V2'].' url(./_img/socle/arrow_diag.gif) no-repeat center center;opacity:'.$_SESSION['OPACITY'].'}';
    $_SESSION['CSS'] .= 'th.v0 , td.v0 , span.v0 {background:'.$_SESSION['BACKGROUND_V0'].'}';
    $_SESSION['CSS'] .= 'th.v1 , td.v1 , span.v1 {background:'.$_SESSION['BACKGROUND_V1'].'}';
    $_SESSION['CSS'] .= 'th.v2 , td.v2 , span.v2 {background:'.$_SESSION['BACKGROUND_V2'].'}';
    $_SESSION['CSS'] .= '#zone_information .v0 {background:'.$_SESSION['BACKGROUND_V0'].';padding:0 1em;margin-right:1ex}';
    $_SESSION['CSS'] .= '#zone_information .v1 {background:'.$_SESSION['BACKGROUND_V1'].';padding:0 1em;margin-right:1ex}';
    $_SESSION['CSS'] .= '#zone_information .v2 {background:'.$_SESSION['BACKGROUND_V2'].';padding:0 1em;margin-right:1ex}';
    $_SESSION['CSS'] .= '#tableau_validation tbody td[lang=lock] {background:'.$_SESSION['BACKGROUND_V1'].' url(./_img/socle/lock.gif) no-repeat center center;} /* surclasse une classe v0 ou v1 ou v2 car défini après */';
    $_SESSION['CSS'] .= '#tableau_validation tbody td[lang=done] {background-image:url(./_img/socle/done.gif);background-repeat:no-repeat;background-position:center center;} /* pas background pour ne pas écraser background-color défini avant */';
  }

}
?>