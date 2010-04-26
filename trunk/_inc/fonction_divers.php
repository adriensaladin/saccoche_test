<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
 * charger_parametres_mysql_supplementaires
 * 
 * Dans le cas d'une installation de type multi-structures, on peut avoir besoin d'effectuer une requête sur une base d'établissement sans y être connecté :
 * => pour savoir si le mode de connexion est SSO ou pas (./page_public/accueil.ajax.php)
 * => pour l'identification (fonction connecter_user() dans ./_inc/fonction_requetes_administration)
 * => pour le webmestre (création d'un admin, info sur les admins, initialisation du mdp...)
 * 
 * @param string $BASE
 * @return void
 */

function charger_parametres_mysql_supplementaires($BASE)
{
	$file_config_base_structure_multi = './__mysql_config/serveur_sacoche_structure_'.$BASE.'.php';
	if(is_file($file_config_base_structure_multi))
	{
		global $_CONST; // Car si on charge les paramètres dans une fonction, ensuite ils ne sont pas trouvés par la classe de connexion.
		include_once($file_config_base_structure_multi);
		require_once('./_inc/class.DB.config.sacoche_structure.php');
	}
	else
	{
		exit('Erreur : paramètres BDD n°'.$BASE.' manquants !');
	}
}

/**
 * fabriquer_login
 * 
 * @param string $prenom
 * @param string $nom
 * @param string $profil   'eleve' ou 'professeur' (ou 'directeur')
 * @return string
 */

function fabriquer_login($prenom,$nom,$profil)
{
	$modele = ($profil=='eleve') ? $_SESSION['MODELE_ELEVE'] : $_SESSION['MODELE_PROF'] ;
	$login_prenom = mb_substr( clean_login($prenom) , 0 , mb_substr_count($modele,'p') );
	$login_nom    = mb_substr( clean_login($nom)    , 0 , mb_substr_count($modele,'n') );
	$login_separe = str_replace(array('p','n'),'',$modele);
	$login = ($modele{0}=='p') ? $login_prenom.$login_separe.$login_nom : $login_nom.$login_separe.$login_prenom ;
	return $login;
}

/**
 * fabriquer_mdp
 * 
 * @param void
 * @return string
 */

function fabriquer_mdp()
{
	// e enlevé sinon un tableur peut interpréter le mot de passe comme un nombre avec exposant ; hijklmoquvw retirés aussi pour éviter tout risque de confusion
	return mb_substr(str_shuffle('23456789abcdfgnprstxyz'),0,6);
}

/**
 * crypter_mdp
 * 
 * @param string $password
 * @return string
 */

function crypter_mdp($password)
{
	return md5('grain_de_sel'.$password);
}

/**
 * fabriquer_fichier_hebergeur_info
 * 
 * @param string $hebergeur_installation
 * @param string $hebergeur_denomination
 * @param string $hebergeur_logo
 * @param string $hebergeur_cnil
 * @param string $webmestre_nom
 * @param string $webmestre_prenom
 * @param string $webmestre_courriel
 * @param string $webmestre_password_md5
 * @return void
 */

function fabriquer_fichier_hebergeur_info($hebergeur_installation,$hebergeur_denomination,$hebergeur_logo,$hebergeur_cnil,$webmestre_nom,$webmestre_prenom,$webmestre_courriel,$webmestre_password_md5)
{
	$fichier_nom     = './__hebergeur_info/constantes.php';
	$fichier_contenu = '<?php'."\r\n";
	$fichier_contenu.= '// Informations concernant l\'hébergement et son webmestre'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_INSTALLATION\',\''.str_replace('\'','\\\'',$hebergeur_installation).'\');'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_DENOMINATION\',\''.str_replace('\'','\\\'',$hebergeur_denomination).'\');'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_LOGO\'        ,\''.str_replace('\'','\\\'',$hebergeur_logo)        .'\');'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_CNIL\'        ,\''.str_replace('\'','\\\'',$hebergeur_cnil)        .'\');'."\r\n";
	$fichier_contenu.= 'define(\'WEBMESTRE_NOM\'         ,\''.str_replace('\'','\\\'',$webmestre_nom)         .'\');'."\r\n";
	$fichier_contenu.= 'define(\'WEBMESTRE_PRENOM\'      ,\''.str_replace('\'','\\\'',$webmestre_prenom)      .'\');'."\r\n";
	$fichier_contenu.= 'define(\'WEBMESTRE_COURRIEL\'    ,\''.str_replace('\'','\\\'',$webmestre_courriel)    .'\');'."\r\n";
	$fichier_contenu.= 'define(\'WEBMESTRE_PASSWORD_MD5\',\''.str_replace('\'','\\\'',$webmestre_password_md5).'\');'."\r\n";
	$fichier_contenu.= '?>'."\r\n";
	file_put_contents($fichier_nom,$fichier_contenu);
}

/**
 * fabriquer_fichier_connexion_base
 * 
 * @param int    $base_id   0 dans le cas d'une install mono-structure ou de la base du webmestre
 * @param string $BD_host
 * @param string $BD_name
 * @param string $BD_user
 * @param string $BD_pass
 * @return void
 */

function fabriquer_fichier_connexion_base($base_id,$BD_host,$BD_name,$BD_user,$BD_pass)
{
	if( (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id>0) )
	{
		$fichier_nom = './__mysql_config/serveur_sacoche_structure_'.$base_id.'.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche n°'.$base_id.' (installation multi-structures).';
		$prefixe = 'STRUCTURE';
	}
	elseif(HEBERGEUR_INSTALLATION=='mono-structure')
	{
		$fichier_nom = './__mysql_config/serveur_sacoche_structure.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche (installation mono-structure).';
		$prefixe = 'STRUCTURE';
	}
	else	// (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id==0)
	{
		$fichier_nom = './__mysql_config/serveur_sacoche_webmestre.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche du webmestre (installation multi-structures).';
		$prefixe = 'WEBMESTRE';
	}
	$fichier_contenu  = '<?php'."\r\n";
	$fichier_contenu .= '// '.$fichier_descriptif."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_HOST\',\''.$BD_host.'\');	// Nom d\'hôte / serveur'."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_NAME\',\''.$BD_name.'\');	// Nom de la base'."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_USER\',\''.$BD_user.'\');	// Nom d\'utilisateur'."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_PASS\',\''.$BD_pass.'\');	// Mot de passe'."\r\n";
	$fichier_contenu .= '?>'."\r\n";
	file_put_contents($fichier_nom,$fichier_contenu);
}

/**
 * connecter_webmestre
 * 
 * @param string $password
 * @return void
 */

function connecter_webmestre($password)
{
	$password_crypte = crypter_mdp($password);
	$god = ($password_crypte==WEBMESTRE_PASSWORD_MD5) ? true : false ;
	if($god)
	{
		$_SESSION['BASE']             = 0;
		$_SESSION['GOD']              = $god;
		$_SESSION['USER_PROFIL']      = 'webmestre';
		$_SESSION['STRUCTURE_ID']     = 0;
		$_SESSION['DENOMINATION']     = 'Gestion '.HEBERGEUR_INSTALLATION;
		$_SESSION['USER_ID']          = 0;
		$_SESSION['USER_NOM']         = WEBMESTRE_NOM;
		$_SESSION['USER_PRENOM']      = WEBMESTRE_PRENOM;
		$_SESSION['USER_DESCR']       = '[webmestre] '.WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM;
		$_SESSION['SSO']              = 'normal';
		$_SESSION['DUREE_INACTIVITE'] = 30;
		$_SESSION['BLOCAGE_STATUT']   = 0;
	}
}

/**
 * connecter_user
 * 
 * @param int    $BASE
 * @param string $profil
 * @param string $login
 * @param string $password
 * @param string $sso
 * @return void
 */

function connecter_user($BASE,$profil,$login,$password,$sso)
{
	// En cas de multi-structures, il faut charger les paramètres de connexion à la base en question
	if($BASE)
	{
		charger_parametres_mysql_supplementaires($BASE);
	}
	if($sso)
	{
		$god = false ;
		$DB_SQL = 'SELECT sacoche_user.*,sacoche_groupe.groupe_nom FROM sacoche_user ';
		$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
		$DB_SQL.= 'WHERE user_id_ent=:id_ent AND user_statut=:statut ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':id_ent'=>$login,':sso'=>$sso,':statut'=>1);
	}
	else
	{
		$password_crypte = crypter_mdp($password);
		$is_admin = ($profil=='administrateur') ? '=' : '!=' ;
		$god = ($password_crypte==WEBMESTRE_PASSWORD_MD5) ? true : false ;
		$DB_SQL = 'SELECT sacoche_user.*,sacoche_groupe.groupe_nom FROM sacoche_user ';
		$DB_SQL.= 'LEFT JOIN sacoche_groupe ON sacoche_user.eleve_classe_id=sacoche_groupe.groupe_id ';
		$DB_SQL.= 'WHERE user_login=:login AND (user_password=:password_crypte OR :password_crypte=:password_webmestre) AND user_statut=:statut AND user_profil'.$is_admin.':profil ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':login'=>$login,':password_crypte'=>$password_crypte,':password_webmestre'=>WEBMESTRE_PASSWORD_MD5,':statut'=>1,':profil'=>'administrateur');
	}
	$DB_ROW = DB::queryRow(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_ROW))
	{
		// Enregistrer le numéro de la base
		$_SESSION['BASE']             = $BASE;
		// On récupère les données associées à l'utilisateur.
		$_SESSION['GOD']              = $god;
		$_SESSION['USER_PROFIL']      = $DB_ROW['user_profil'];
		$_SESSION['USER_ID']          = (int) $DB_ROW['user_id'];
		$_SESSION['USER_NOM']         = $DB_ROW['user_nom'];
		$_SESSION['USER_PRENOM']      = $DB_ROW['user_prenom'];
		$_SESSION['USER_LOGIN']       = $DB_ROW['user_login'];
		$_SESSION['USER_DESCR']       = '['.$DB_ROW['user_profil'].'] '.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'];
		$_SESSION['USER_ID_ENT']      = $DB_ROW['user_id_ent'];
		$_SESSION['USER_ID_GEPI']     = $DB_ROW['user_id_gepi'];
		$_SESSION['ELEVE_CLASSE_ID']  = (int) $DB_ROW['eleve_classe_id'];
		$_SESSION['ELEVE_CLASSE_NOM'] = $DB_ROW['groupe_nom'];
		// On récupère les données associées à l'établissement.
		$DB_SQL = 'SELECT parametre_nom,parametre_valeur FROM sacoche_parametre ';
		$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL );
		foreach($DB_TAB as $DB_ROW)
		{
			switch($DB_ROW['parametre_nom'])
			{
				case 'structure_id'  :    $_SESSION['STRUCTURE_ID']        = (int) $DB_ROW['parametre_valeur']; break;
				case 'structure_uai' :    $_SESSION['STRUCTURE_UAI']       =       $DB_ROW['parametre_valeur']; break;
				case 'structure_key' :    $_SESSION['STRUCTURE_KEY']       =       $DB_ROW['parametre_valeur']; break;
				case 'denomination':      $_SESSION['DENOMINATION']        =       $DB_ROW['parametre_valeur']; break;
				case 'sso':               $_SESSION['SSO']                 =       $DB_ROW['parametre_valeur']; break;
				case 'modele_professeur': $_SESSION['MODELE_PROF']         =       $DB_ROW['parametre_valeur']; break;
				case 'modele_eleve':      $_SESSION['MODELE_ELEVE']        =       $DB_ROW['parametre_valeur']; break;
				case 'matieres':          $_SESSION['MATIERES']            =       $DB_ROW['parametre_valeur']; break;
				case 'niveaux':           $_SESSION['NIVEAUX']             =       $DB_ROW['parametre_valeur']; break;
				case 'paliers':           $_SESSION['PALIERS']             =       $DB_ROW['parametre_valeur']; break;
				case 'eleve_options':     $_SESSION['ELEVE_OPTIONS']       =       $DB_ROW['parametre_valeur']; break;
				case 'eleve_demandes':    $_SESSION['ELEVE_DEMANDES']      = (int) $DB_ROW['parametre_valeur']; break;
				case 'duree_inactivite':  $_SESSION['DUREE_INACTIVITE']    = (int) $DB_ROW['parametre_valeur']; break;
				case 'calcul_valeur_RR':  $_SESSION['CALCUL_VALEUR']['RR'] = (int) $DB_ROW['parametre_valeur']; break;
				case 'calcul_valeur_R':   $_SESSION['CALCUL_VALEUR']['R']  = (int) $DB_ROW['parametre_valeur']; break;
				case 'calcul_valeur_V':   $_SESSION['CALCUL_VALEUR']['V']  = (int) $DB_ROW['parametre_valeur']; break;
				case 'calcul_valeur_VV':  $_SESSION['CALCUL_VALEUR']['VV'] = (int) $DB_ROW['parametre_valeur']; break;
				case 'calcul_seuil_R':    $_SESSION['CALCUL_SEUIL']['R']   = (int) $DB_ROW['parametre_valeur']; break;
				case 'calcul_seuil_V':    $_SESSION['CALCUL_SEUIL']['V']   = (int) $DB_ROW['parametre_valeur']; break;
				case 'calcul_methode':    $_SESSION['CALCUL_METHODE']      =       $DB_ROW['parametre_valeur']; break;
				case 'calcul_limite':     $_SESSION['CALCUL_LIMITE']       = (int) $DB_ROW['parametre_valeur']; break;
				case 'blocage_statut':    $_SESSION['BLOCAGE_STATUT']      = (int) $DB_ROW['parametre_valeur']; break;
				case 'blocage_message':   $_SESSION['BLOCAGE_MESSAGE']     =       $DB_ROW['parametre_valeur']; break;
			}
		}
		// Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
		setcookie('SACoche-etablissement',$BASE,time()+60*60*24*365,'/');
		// Vérifier qu'un admin de l'établissement n'a pas interdit l'accès
	}
}

function envoyer_webmestre_courriel($adresse,$objet,$contenu)
{
	$param = 'From: '.WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM.' <'.WEBMESTRE_COURRIEL.'>'."\r\n";
	$param.= 'Reply-To: '.WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM.' <'.WEBMESTRE_COURRIEL.'>'."\r\n";
	$param.= 'Content-type: text/plain; charset=utf-8'."\r\n";
	// Pb avec les accents dans l'entête (sujet, expéditeur...) ; le charset n'a d'effet que sur le corps et les clients de messagerie interprètent différemment le reste (UTF-8 ou ISO-8859-1 etc.).
	// $back=($retour)?'-fwebmestre@sesaprof.net':'';
	// Fonction bridée : 5° paramètre supprimé << Warning: mail(): SAFE MODE Restriction in effect. The fifth parameter is disabled in SAFE MODE.
	$envoi = @mail( $adresse , clean_accents('[SACoche - '.HEBERGEUR_DENOMINATION.'] '.$objet) , $contenu , clean_accents($param) );
	return $envoi ;
}

?>