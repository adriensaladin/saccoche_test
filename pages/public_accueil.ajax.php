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

$action     = (isset($_POST['f_action']))     ? Clean::texte($_POST['f_action'])      : '';
$BASE       = (isset($_POST['f_base']))       ? Clean::entier($_POST['f_base'])       : 0 ;
$profil     = (isset($_POST['f_profil']))     ? Clean::texte($_POST['f_profil'])      : '';  // structure | webmestre | partenaire
$login      = (isset($_POST['f_login']))      ? Clean::login($_POST['f_login'])       : '';
$password   = (isset($_POST['f_password']))   ? Clean::password($_POST['f_password']) : '';
$partenaire = (isset($_POST['f_partenaire'])) ? Clean::entier($_POST['f_partenaire']) : 0 ;
$courriel   = (isset($_POST['f_courriel']))   ? Clean::courriel($_POST['f_courriel']) : '';

/*
 * Afficher le formulaire de choix des établissements (installation multi-structures)
 */
function afficher_formulaire_etablissement($BASE,$profil)
{
  $options_structures = Form::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_structures_sacoche() , FALSE /*select_nom*/ , FALSE /*option_first*/ , $BASE /*selection*/ , 'zones_geo' /*optgroup*/ );
  echo'<label class="tab" for="f_base">Établissement :</label><select id="f_base" name="f_base" tabindex="1" class="t9">'.$options_structures.'</select><br />'.NL;
  echo'<span class="tab"></span><button id="f_choisir" type="button" tabindex="2" class="valider">Choisir cet établissement.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
  echo'<input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" />'.NL;
}

/*
 * Afficher le nom d'un établissement, avec l'icône pour changer de structure si installation multi-structures
 */
function afficher_nom_etablissement($BASE,$denomination)
{
  $changer = (HEBERGEUR_INSTALLATION=='multi-structures') ? ' - <a id="f_changer" href="#">changer</a>' : '' ;
  echo'<label class="tab">Établissement :</label><input id="f_base" name="f_base" type="hidden" value="'.$BASE.'" /><em>'.html($denomination).'</em>'.$changer.'<br />'.NL;
}

/*
 * Afficher la partie du formulaire spécialement dédiée à l'identification :
 * - pour le webmestre -> seulement la saisie du mot de passe pour le webmestre
 * - pour un partenaire -> un select avec la liste des partenaires et la saisie du mot de passe
 * - si le mode de connexion est normal -> saisie login & mot de passe SACoche
 * - si l'établissement est configuré pour un autre mode de connexion -> au choix [ saisie login & mot de passe SACoche ] ou [ utilisation de l'authentification externe ]
 */
function afficher_formulaire_identification($profil,$mode='normal',$nom='')
{
  if($profil=='webmestre')
  {
    echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="1" autocomplete="off" /><br />'.NL;
    echo'<span class="tab"></span><input id="f_login" name="f_login" type="hidden" value="'.$profil.'" /><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="2" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    echo'<span class="tab"></span><a class="lost" href="#lost_webmestre">[ Identifiants oubliés ! ]</a>'.NL;
  }
  elseif($profil=='partenaire')
  {
    // Lecture d'un cookie sur le poste client servant à retenir le dernier partenariat sélectionné si identification avec succès
    $selection = (isset($_COOKIE[COOKIE_PARTENAIRE])) ? Clean::entier($_COOKIE[COOKIE_PARTENAIRE]) : FALSE ;
    $options_partenaires = Form::afficher_select(DB_WEBMESTRE_SELECT::DB_OPT_partenaires_conventionnes() , FALSE /*select_nom*/ , '' /*option_first*/ , $selection , '' /*optgroup*/ );
    echo'<label class="tab" for="f_partenaire">Partenariat :</label><select id="f_partenaire" name="f_partenaire" tabindex="1" class="t9">'.$options_partenaires.'</select><br />'.NL;
    echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="2" autocomplete="off" /><br />'.NL;
    echo'<span class="tab"></span><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="'.$profil.'" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="3" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    echo'<span class="tab"></span><a class="lost" href="#lost_partenaire">[ Identifiants oubliés ! ]</a>'.NL;
  }
  elseif($mode=='normal')
  {
    echo'<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="20" type="text" value="" tabindex="2" autocomplete="off" /><br />'.NL;
    echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" autocomplete="off" /><br />'.NL;
    echo'<span class="tab"></span><input id="f_mode" name="f_mode" type="hidden" value="normal" /><input id="f_profil" name="f_profil" type="hidden" value="structure" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    echo'<span class="tab"></span><a class="lost" href="#lost_structure">[ Identifiants oubliés ! ]</a>'.NL;
  }
  else
  {
    echo'<label class="tab">Mode de connexion :</label>';
    echo  '<label for="f_mode_normal"><input type="radio" id="f_mode_normal" name="f_mode" value="normal" /> formulaire <em>SACoche</em></label>&nbsp;&nbsp;&nbsp;';
    echo  '<label for="f_mode_'.$mode.'"><input type="radio" id="f_mode_'.$mode.'" name="f_mode" value="'.$mode.'" checked /> authentification extérieure <em>'.html($mode.'-'.$nom).'</em></label><br />'.NL;
    echo'<fieldset id="fieldset_normal" class="hide">'.NL;
    echo'<label class="tab" for="f_login">Nom d\'utilisateur :</label><input id="f_login" name="f_login" size="20" type="text" value="" tabindex="2" autocomplete="off" /><br />'.NL;
    echo'<label class="tab" for="f_password">Mot de passe :</label><input id="f_password" name="f_password" size="20" type="password" value="" tabindex="3" autocomplete="off" /><br />'.NL;
    echo'</fieldset>'.NL;
    echo'<span class="tab"></span><input id="f_profil" name="f_profil" type="hidden" value="structure" /><input id="f_action" name="f_action" type="hidden" value="identifier" /><button id="f_submit" type="submit" tabindex="4" class="mdp_perso">Accéder à son espace.</button><label id="ajax_msg">&nbsp;</label><br />'.NL;
    echo'<span id="lien_lost" class="hide"><span class="tab"></span><a class="lost" href="#lost_structure">[ Identifiants oubliés ! ]</a></span>'.NL;
  }
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Rechercher la dernière version disponible
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='tester_version')
{
  $version_last = recuperer_numero_derniere_version();
  if(!preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}[a-z]?$#', $version_last))
  {
    $tab_retour = array( 'class'=>'alerte' , 'texte'=>$version_last , 'after' => '' );
  }
  elseif($version_last==VERSION_PROG)
  {
    $tab_retour = array( 'class'=>'valide' , 'texte'=>'Cette version est la dernière disponible.' , 'after' => '' );
  }
  else
  {
    // Compte approximativement le nombre de mois qui sépare ces 2 versions (sans s'occuper des jours).
    $class = ( (substr($version_last,0,4)-substr(VERSION_PROG,0,4))*12 - substr($version_last,5,2) + substr(VERSION_PROG,5,2) < 12 ) ? '' : ' class="probleme"' ;
    $tab_retour = array( 'class'=>'alerte' , 'texte'=>'<span'.$class.'>Dernière version disponible <em>'.$version_last.'</em>.</span>' , 'after' => ' &rarr; <a class="lien_ext" href="'.SERVEUR_NEWS.'">Nouveautés.</a>' );
  }
  exit(json_encode($tab_retour));
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger un formulaire d'identification
// ////////////////////////////////////////////////////////////////////////////////////////////////////

// Charger le formulaire pour le webmestre d'un serveur

if( ($action=='initialiser') && ($profil=='webmestre') )
{
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    // Mettre à jour la base du webmestre si besoin
    $version_base_webmestre = DB_WEBMESTRE_MAJ_BASE::DB_version_base();
    if($version_base_webmestre != VERSION_BASE_WEBMESTRE)
    {
      DB_WEBMESTRE_MAJ_BASE::DB_maj_base($version_base_webmestre);
    }
  }
  exit( afficher_formulaire_identification($profil,'normal') );
}

// Charger le formulaire pour un partenaire

if( ($action=='initialiser') && ($profil=='partenaire') )
{
  exit( afficher_formulaire_identification($profil,'normal') );
}

// Charger le formulaire pour un établissement donné (installation mono-structure)

if( ($action=='initialiser') && (HEBERGEUR_INSTALLATION=='mono-structure') && $profil )
{
  // Mettre à jour la base si nécessaire
  maj_base_structure_si_besoin($BASE);
  // Requête pour récupérer la dénomination et le mode de connexion
  $DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"webmestre_denomination","connexion_mode","connexion_nom"');
  foreach($DB_TAB as $DB_ROW)
  {
    ${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
  }
  if(isset($webmestre_denomination,$connexion_mode,$connexion_nom)==FALSE)
  {
    exit('Erreur : base de l\'établissement incomplète !');
  }
  exit( afficher_nom_etablissement($BASE=0,$webmestre_denomination) . afficher_formulaire_identification($profil,$connexion_mode,$connexion_nom) );
}

// Charger le formulaire de choix des établissements (installation multi-structures)

if( ( ($action=='initialiser') && ($BASE==0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='choisir') && $profil )
{
  exit( afficher_formulaire_etablissement($BASE,$profil) );
}

// Charger le formulaire pour un établissement donné (installation multi-structures)

if( ( ($action=='initialiser') && ($BASE>0) && (HEBERGEUR_INSTALLATION=='multi-structures') ) || ($action=='charger') && $profil )
{
  // Une première requête sur SACOCHE_WEBMESTRE_BD_NAME pour vérifier que la structure est référencée
  $structure_denomination = DB_WEBMESTRE_PUBLIC::DB_recuperer_structure_nom_for_Id($BASE);
  if($structure_denomination===NULL)
  {
    // Sans doute un établissement supprimé, mais le cookie est encore là
    setcookie( COOKIE_STRUCTURE /*name*/ , '' /*value*/ , $_SERVER['REQUEST_TIME']-42000 /*expire*/ , '' /*path*/ ); // précédente version...
    setcookie( COOKIE_STRUCTURE /*name*/ , '' /*value*/ , $_SERVER['REQUEST_TIME']-42000 /*expire*/ , '/' /*path*/ , getServerUrl() /*domain*/ );
    exit('Erreur : établissement non trouvé dans la base d\'administration !');
  }
  afficher_nom_etablissement($BASE,$structure_denomination);
  // Mettre à jour la base si nécessaire
  charger_parametres_mysql_supplementaires($BASE);
  maj_base_structure_si_besoin($BASE);
  // Une deuxième requête sur SACOCHE_STRUCTURE_BD_NAME pour savoir si le mode de connexion est SSO ou pas
  $DB_TAB = DB_STRUCTURE_PUBLIC::DB_lister_parametres('"connexion_mode","connexion_nom"');
  foreach($DB_TAB as $DB_ROW)
  {
    ${$DB_ROW['parametre_nom']} = $DB_ROW['parametre_valeur'];
  }
  if(isset($connexion_mode,$connexion_nom)==FALSE)
  {
    exit('Erreur : base de l\'établissement incomplète !');
  }
  exit( afficher_formulaire_identification($profil,$connexion_mode,$connexion_nom) );
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traiter une demande d'identification
// ////////////////////////////////////////////////////////////////////////////////////////////////////

function adresse_redirection_apres_authentification()
{
  if( empty($_SESSION['MEMO_GET']) || !isset($_SESSION['MEMO_GET']['page']) )
  {
    $_SESSION['MEMO_GET']['page'] = 'compte_accueil';
  }
  $tab_get = array();
  foreach($_SESSION['MEMO_GET'] as $key => $val)
  {
    $tab_get[] = $key.'='.urlencode($val);
  }
  unset($_SESSION['MEMO_GET']);
  return URL_DIR_SACOCHE.'index.php?'.implode('&',$tab_get);
}

// Pour un utilisateur d'établissement, y compris un administrateur

if( ($action=='identifier') && ($profil=='structure') && ($login!='') && ($password!='') )
{
  list($auth_resultat,$auth_DB_ROW) = SessionUser::tester_authentification_utilisateur( $BASE , $login , $password , 'normal' /*mode_connection*/ );
  if($auth_resultat=='ok')
  {
    SessionUser::initialiser_utilisateur($BASE,$auth_DB_ROW);
    exit(adresse_redirection_apres_authentification());
  }
  exit($auth_resultat);
}

// Pour le webmestre d'un serveur ou  éventuellement un développeur

if( ($action=='identifier') && ($profil=='webmestre') && ($login=='webmestre') && ($password!='') )
{
  $auth_resultat = SessionUser::tester_authentification_webmestre($password);
  if($auth_resultat=='ok')
  {
    SessionUser::initialiser_webmestre();
    exit(adresse_redirection_apres_authentification());
  }
  else
  {
    $auth_resultat = SessionUser::tester_authentification_developpeur($password);
    if($auth_resultat=='ok')
    {
      SessionUser::initialiser_developpeur();
      exit(adresse_redirection_apres_authentification());
    }
  }
  exit($auth_resultat);
}

// Pour un partenaire conventionné (serveur Sésamath uniquement)

if( ($action=='identifier') && ($profil=='partenaire') && ($partenaire!=0) && ($password!='') && IS_HEBERGEMENT_SESAMATH && (HEBERGEUR_INSTALLATION=='multi-structures') )
{
  list($auth_resultat,$auth_DB_ROW) = SessionUser::tester_authentification_partenaire($partenaire,$password);
  if($auth_resultat=='ok')
  {
    SessionUser::initialiser_partenaire($auth_DB_ROW);
    exit(adresse_redirection_apres_authentification());
  }
  exit($auth_resultat);
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Demander l'obtention de nouveaux identifiants
// ////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='demande_mdp') && ($courriel!='') && ( ($BASE>0) || (HEBERGEUR_INSTALLATION=='mono-structure') ) )
{
  // On vérifie le domaine du serveur mail même en mode mono-structures parce que de toutes façons il faudra ici envoyer un mail, donc l'installation doit être ouverte sur l'extérieur.
  $mail_domaine = tester_domaine_courriel_valide($courriel);
  if($mail_domaine!==TRUE)
  {
    exit('Erreur avec le domaine "'.$mail_domaine.'" !');
  }
  // En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
  if(HEBERGEUR_INSTALLATION=='multi-structures')
  {
    charger_parametres_mysql_supplementaires($BASE);
  }
  // On teste si l'adresse mail est trouvée
  $DB_ROW = DB_STRUCTURE_PUBLIC::DB_recuperer_user_for_new_mdp('user_email',$courriel);
  if(empty($DB_ROW))
  {
    exit('Adresse de courriel non enregistrée !');
  }
  $user_pass_key = crypter_mdp($DB_ROW['user_id'].$DB_ROW['user_email'].$DB_ROW['user_password'].$DB_ROW['user_connexion_date']);
  $code_mdp = ($BASE) ? $user_pass_key.'g'.$BASE : $user_pass_key ;
  DB_STRUCTURE_PUBLIC::DB_modifier_user_password_or_key ($DB_ROW['user_id'] , '' /*user_password*/ , $user_pass_key /*user_pass_key*/ );
  $AdresseIP = Session::get_IP();
  $HostName  = gethostbyaddr($AdresseIP);
  $UserAgent = Session::get_UserAgent();
  $mail_contenu = 'Bonjour,'."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= 'Une demande de nouveaux identifiants vient d\'être formulée concernant le compte SACoche ayant cette adresse de courriel :'."\r\n";
  $mail_contenu.= $DB_ROW['user_email']."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= 'Pour confirmer la génération d\'un nouveau mot de passe, veuillez cliquer sur ce lien :'."\r\n";
  $mail_contenu.= URL_DIR_SACOCHE.'?code_mdp='.$code_mdp."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= 'Si vous n\'êtes pas à l\'origine de cette demande, alors il s\'agit d\'une mauvaise plaisanterie !'."\r\n";
  $mail_contenu.= 'Dans ce cas, merci d\'ignorer ce message.'."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= 'Voici pour information les informations relatives à la connexion internet utilisée :'."\r\n";
  $mail_contenu.= 'Adresse IP --> '.$AdresseIP."\r\n";
  $mail_contenu.= 'Nom d\'hôte --> '.$HostName."\r\n";
  $mail_contenu.= 'Navigateur --> '.$UserAgent."\r\n";
  $mail_contenu.= "\r\n";
  $mail_contenu.= '--'."\r\n";
  $mail_contenu.= 'SACoche - '.HEBERGEUR_DENOMINATION."\r\n";
  $courriel_bilan = Sesamail::mail( $DB_ROW['user_email'] , 'Demande de nouveaux identifiants' , $mail_contenu );
  if(!$courriel_bilan)
  {
    exit('Erreur lors de l\'envoi du courriel !'.$code_mdp);
  }
  exit('ok');
}

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// On ne devrait pas en arriver là...
// ////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');
?>
