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
 * @param string $hebergeur_uai
 * @param string $hebergeur_adresse_site
 * @param string $hebergeur_logo
 * @param string $hebergeur_cnil
 * @param string $webmestre_nom
 * @param string $webmestre_prenom
 * @param string $webmestre_courriel
 * @param string $webmestre_password_md5
 * @return void
 */

function fabriquer_fichier_hebergeur_info($hebergeur_installation,$hebergeur_denomination,$hebergeur_uai,$hebergeur_adresse_site,$hebergeur_logo,$hebergeur_cnil,$webmestre_nom,$webmestre_prenom,$webmestre_courriel,$webmestre_password_md5)
{
	$fichier_nom     = './__hebergement_info/constantes.php';
	$fichier_contenu = '<?php'."\r\n";
	$fichier_contenu.= '// Informations concernant l\'hébergement et son webmestre (n°UAI uniquement pour une installation de type mono-structure)'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_INSTALLATION\',\''.str_replace('\'','\\\'',$hebergeur_installation).'\');'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_DENOMINATION\',\''.str_replace('\'','\\\'',$hebergeur_denomination).'\');'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_UAI\'         ,\''.str_replace('\'','\\\'',$hebergeur_uai)         .'\');'."\r\n";
	$fichier_contenu.= 'define(\'HEBERGEUR_ADRESSE_SITE\',\''.str_replace('\'','\\\'',$hebergeur_adresse_site).'\');'."\r\n";
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
 * tester_blocage_acces
 * Blocage des sites sur demande du webmestre ou d'un administrateur (maintenance, sauvegarde/restauration, ...).
 * Nécessite que la session soit ouverte.
 * Appelé depuis les pages index.php + ajax.php + lors d'une demande d'identification d'un utilisateur (sauf webmestre)
 * 
 * @param string $demande_connexion_profil   false si appel depuis index.php ou ajax.php, le profil si demande d'identification
 * @return void
 */

function tester_blocage_acces($demande_connexion_profil)
{
	// Par le webmestre
	$fichier_blocage_webmestre = './__hebergement_info/blocage_webmestre.txt';
	if( (is_file($fichier_blocage_webmestre)) && (($_SESSION['USER_PROFIL']!='public')||($demande_connexion_profil!=false)) && ($_SESSION['USER_PROFIL']!='webmestre') )
	{
		affich_message_exit($titre='Blocage par le webmestre',$contenu='Blocage par le webmestre : '.file_get_contents($fichier_blocage_webmestre) );
	}
	// Par un administrateur
	$fichier_blocage_administrateur = './__hebergement_info/blocage_admin_'.$_SESSION['BASE'].'.txt';
	if( (is_file($fichier_blocage_administrateur)) && (($_SESSION['USER_PROFIL']!='public')||($demande_connexion_profil!='administrateur')) && ($_SESSION['USER_PROFIL']!='webmestre') && ($_SESSION['USER_PROFIL']!='administrateur') )
	{
		affich_message_exit($titre='Blocage par un administrateur',$contenu='Blocage par un administrateur : '.file_get_contents($fichier_blocage_administrateur) );
	}
}

/**
 * connecter_webmestre
 * 
 * @param string $password
 * @return string   'ok' (et dans ce cas la session est mise à jour) ou un message d'erreur
 */

function connecter_webmestre($password)
{
	$password_crypte = crypter_mdp($password);
	if($password_crypte==WEBMESTRE_PASSWORD_MD5)
	{
		$_SESSION['BASE']             = 0;
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
		return 'ok';
	}
	return 'Mot de passe incorrect !';
}

/**
 * connecter_user
 * 
 * @param int    $BASE
 * @param string $profil   'normal' ou 'administrateur'
 * @param string $login
 * @param string $password
 * @param string $sso
 * @return string   retourne 'ok' en cas de succès (et dans ce cas la session est mise à jour) ou un message d'erreur sinon
 */

function connecter_user($BASE,$profil,$login,$password,$sso)
{
	// Blocage éventuel par le webmestre ou un administrateur
	tester_blocage_acces($demande_connexion_profil=$profil);
	// En cas de multi-structures, il faut charger les paramètres de connexion à la base en question
	if($BASE)
	{
		charger_parametres_mysql_supplementaires($BASE);
	}
	$DB_ROW = DB_recuperer_donnees_utilisateur($sso,$login);
	if(!count($DB_ROW))
	{
		return ($sso) ? 'Identification réussie mais valeur "'.$login.'" inconnue dans SACoche !' : 'Nom d\'utilisateur incorrect !' ;
	}
	elseif( (!$sso) && ($DB_ROW['user_password']!=crypter_mdp($password)) )
	{
		return 'Mot de passe incorrect !';
	}
	elseif($DB_ROW['user_statut']!=1)
	{
		return 'Identification réussie mais ce compte est desactivé !';
	}
	elseif( ( ($profil!='administrateur')&&($DB_ROW['user_profil']=='administrateur') ) || ( ($profil=='administrateur')&&($DB_ROW['user_profil']!='administrateur') ) )
	{
		return 'Ces identifiants sont ceux d\'un '.$DB_ROW['user_profil'].' : utilisez le formulaire approprié !';
	}
	/*
		Reste à étudier le cas d'un blocage par un admin ou le webmestre (à enregistrer dans un fichier).............
	*/
	// Si on arrive ici c'est que l'identification s'est bien effectuée !
	// Enregistrer le numéro de la base
	$_SESSION['BASE']             = $BASE;
	// On récupère les données associées à l'utilisateur.
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
	$DB_TAB = DB_lister_parametres();
	foreach($DB_TAB as $DB_ROW)
	{
		switch($DB_ROW['parametre_nom'])
		{
			case 'sesamath_id' :       $_SESSION['SESAMATH_ID']         = (int) $DB_ROW['parametre_valeur']; break;
			case 'sesamath_uai' :      $_SESSION['SESAMATH_UAI']        =       $DB_ROW['parametre_valeur']; break;
			case 'sesamath_type_nom' : $_SESSION['SESAMATH_TYPE_NOM']   =       $DB_ROW['parametre_valeur']; break;
			case 'sesamath_key' :      $_SESSION['SESAMATH_KEY']        =       $DB_ROW['parametre_valeur']; break;
			case 'uai' :               $_SESSION['UAI']                 =       $DB_ROW['parametre_valeur']; break;
			case 'denomination':       $_SESSION['DENOMINATION']        =       $DB_ROW['parametre_valeur']; break;
			case 'sso':                $_SESSION['SSO']                 =       $DB_ROW['parametre_valeur']; break;
			case 'modele_professeur':  $_SESSION['MODELE_PROF']         =       $DB_ROW['parametre_valeur']; break;
			case 'modele_eleve':       $_SESSION['MODELE_ELEVE']        =       $DB_ROW['parametre_valeur']; break;
			case 'matieres':           $_SESSION['MATIERES']            =       $DB_ROW['parametre_valeur']; break;
			case 'niveaux':            $_SESSION['NIVEAUX']             =       $DB_ROW['parametre_valeur']; break;
			case 'paliers':            $_SESSION['PALIERS']             =       $DB_ROW['parametre_valeur']; break;
			case 'eleve_options':      $_SESSION['ELEVE_OPTIONS']       =       $DB_ROW['parametre_valeur']; break;
			case 'eleve_demandes':     $_SESSION['ELEVE_DEMANDES']      = (int) $DB_ROW['parametre_valeur']; break;
			case 'duree_inactivite':   $_SESSION['DUREE_INACTIVITE']    = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_RR':   $_SESSION['CALCUL_VALEUR']['RR'] = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_R':    $_SESSION['CALCUL_VALEUR']['R']  = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_V':    $_SESSION['CALCUL_VALEUR']['V']  = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_VV':   $_SESSION['CALCUL_VALEUR']['VV'] = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_seuil_R':     $_SESSION['CALCUL_SEUIL']['R']   = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_seuil_V':     $_SESSION['CALCUL_SEUIL']['V']   = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_methode':     $_SESSION['CALCUL_METHODE']      =       $DB_ROW['parametre_valeur']; break;
			case 'calcul_limite':      $_SESSION['CALCUL_LIMITE']       = (int) $DB_ROW['parametre_valeur']; break;
		}
	}
	// Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
	setcookie('SACoche-etablissement',$BASE,time()+60*60*24*365,'/');
	return('ok');
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

/**
 * afficher_arborescence_from_SQL
 * Retourner une liste ordonnée à afficher à partir d'une requête SQL transmise.
 * 
 * @param tab  $DB_TAB
 * @param bool        $dynamique   arborescence cliquable ou pas (plier/replier)
 * @param bool        $reference   afficher ou pas les références
 * @param bool|string $aff_coef    false | 'texte' | 'image' : affichage des coefficients des items
 * @param bool|string $aff_socle   false | 'texte' | 'image' : affichage de la liaison au socle
 * @param bool|string $aff_lien    false | 'image' | 'click' : affichage des ressources de remédiation
 * @param bool        $aff_input   affichage ou pas des input checkbox avec label
 * @return string
 */

function afficher_arborescence_from_SQL($DB_TAB,$dynamique,$reference,$aff_coef,$aff_socle,$aff_lien,$aff_input)
{
	$input_texte = '';
	$coef_texte  = '';
	$socle_texte = '';
	$lien_texte  = '';
	$lien_texte_avant = '';
	$lien_texte_apres = '';
	$label_texte_avant = '';
	$label_texte_apres = '';
	// Traiter le retour SQL : on remplit les tableaux suivants.
	$tab_matiere    = array();
	$tab_niveau     = array();
	$tab_domaine    = array();
	$tab_theme      = array();
	$tab_competence = array();
	$matiere_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['matiere_id']!=$matiere_id)
		{
			$matiere_id = $DB_ROW['matiere_id'];
			$tab_matiere[$matiere_id] = ($reference) ? $DB_ROW['matiere_ref'].' - '.$DB_ROW['matiere_nom'] : $DB_ROW['matiere_nom'] ;
			$niveau_id     = 0;
			$domaine_id    = 0;
			$theme_id      = 0;
			$competence_id = 0;
		}
		if( (!is_null($DB_ROW['niveau_id'])) && ($DB_ROW['niveau_id']!=$niveau_id) )
		{
			$niveau_id = $DB_ROW['niveau_id'];
			$tab_niveau[$matiere_id][$niveau_id] = ($reference) ? $DB_ROW['niveau_ref'].' - '.$DB_ROW['niveau_nom'] : $DB_ROW['niveau_nom'];
		}
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$tab_domaine[$matiere_id][$niveau_id][$domaine_id] = ($reference) ? $DB_ROW['domaine_ref'].' - '.$DB_ROW['domaine_nom'] : $DB_ROW['domaine_nom'];
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$tab_theme[$matiere_id][$niveau_id][$domaine_id][$theme_id] = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].' - '.$DB_ROW['theme_nom'] : $DB_ROW['theme_nom'] ;
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$competence_id) )
		{
			$competence_id = $DB_ROW['item_id'];
			switch($aff_coef)
			{
				case 'texte' :	$coef_texte = '['.$DB_ROW['item_coef'].'] ';
												break;
				case 'image' :	$coef_texte = '<img src="./_img/x'.$DB_ROW['item_coef'].'.gif" title="Coefficient '.$DB_ROW['item_coef'].'." /> ';
			}
			switch($aff_socle)
			{
				case 'texte' :	$socle_texte = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
												break;
				case 'image' :	$socle_image = ($DB_ROW['entree_id']) ? 'on' : 'off' ;
												$socle_nom   = ($DB_ROW['entree_id']) ? html($DB_ROW['entree_nom']) : 'Hors-socle.' ;
												$socle_texte = '<img src="./_img/socle_'.$socle_image.'.png" title="'.$socle_nom.'" /> ';
			}
			switch($aff_lien)
			{
				case 'click' :	$lien_texte_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
												$lien_texte_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
				case 'image' :	$lien_image = ($DB_ROW['item_lien']) ? 'on' : 'off' ;
												$lien_nom   = ($DB_ROW['item_lien']) ? html($DB_ROW['item_lien']) : 'Absence de ressource.' ;
												$lien_texte = '<img src="./_img/link_'.$lien_image.'.png" title="'.$lien_nom.'" /> ';
			}
			if($aff_input)
			{
				$input_texte = '<input id="id_'.$competence_id.'" name="f_competences[]" type="checkbox" value="'.$competence_id.'" /> ';
				$label_texte_avant = '<label for="id_'.$competence_id.'">';
				$label_texte_apres = '</label>';
			}
			$competence_texte = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'].' - '.$DB_ROW['item_nom'] : $DB_ROW['item_nom'] ;
			$tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id][$competence_id] = $input_texte.$label_texte_avant.$coef_texte.$socle_texte.$lien_texte.$lien_texte_avant.html($competence_texte).$lien_texte_apres.$label_texte_apres;
		}
	}
	// Affichage de l'arborescence
	$span_avant = ($dynamique) ? '<span>' : '' ;
	$span_apres = ($dynamique) ? '</span>' : '' ;
	$retour = '<ul class="ul_m1">'."\r\n";
	if(count($tab_matiere))
	{
		foreach($tab_matiere as $matiere_id => $matiere_texte)
		{
			$retour .= '<li class="li_m1">'.$span_avant.html($matiere_texte).$span_apres."\r\n";
			$retour .= '<ul class="ul_m2">'."\r\n";
			if(isset($tab_niveau[$matiere_id]))
			{
				foreach($tab_niveau[$matiere_id] as $niveau_id => $niveau_texte)
				{
					$retour .= '<li class="li_m2">'.$span_avant.html($niveau_texte).$span_apres."\r\n";
					$retour .= '<ul class="ul_n1">'."\r\n";
					if(isset($tab_domaine[$matiere_id][$niveau_id]))
					{
						foreach($tab_domaine[$matiere_id][$niveau_id] as $domaine_id => $domaine_texte)
						{
							$retour .= '<li class="li_n1">'.$span_avant.html($domaine_texte).$span_apres."\r\n";
							$retour .= '<ul class="ul_n2">'."\r\n";
							if(isset($tab_theme[$matiere_id][$niveau_id][$domaine_id]))
							{
								foreach($tab_theme[$matiere_id][$niveau_id][$domaine_id] as $theme_id => $theme_texte)
								{
									$retour .= '<li class="li_n2">'.$span_avant.html($theme_texte).$span_apres."\r\n";
									$retour .= '<ul class="ul_n3">'."\r\n";
									if(isset($tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id]))
									{
										foreach($tab_competence[$matiere_id][$niveau_id][$domaine_id][$theme_id] as $competence_id => $competence_texte)
										{
											$retour .= '<li class="li_n3">'.$competence_texte.'</li>'."\r\n";
										}
									}
									$retour .= '</ul>'."\r\n";
									$retour .= '</li>'."\r\n";
								}
							}
							$retour .= '</ul>'."\r\n";
							$retour .= '</li>'."\r\n";
						}
					}
					$retour .= '</ul>'."\r\n";
					$retour .= '</li>'."\r\n";
				}
			}
			$retour .= '</ul>'."\r\n";
			$retour .= '</li>'."\r\n";
		}
	}
	$retour .= '</ul>'."\r\n";
	return $retour;
}

/**
 * exporter_arborescence_to_XML
 * Fabriquer un export XML d'un référentiel (pour partage sur serveur central) à partir d'une requête SQL transmise.
 * Remarque : les ordres des domaines / thèmes / items ne sont pas transmis car il sont déjà indiqués par la position dans l'arborescence
 * 
 * @param tab  $DB_TAB
 * @return string
 */

function exporter_arborescence_to_XML($DB_TAB)
{
	// Traiter le retour SQL : on remplit les tableaux suivants.
	$tab_domaine = array();
	$tab_theme   = array();
	$tab_item    = array();
	$domaine_id = 0;
	$theme_id   = 0;
	$item_id    = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$tab_domaine[$domaine_id] = array('ref'=>$DB_ROW['domaine_ref'],'nom'=>$DB_ROW['domaine_nom']);
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$tab_theme[$domaine_id][$theme_id] = array('nom'=>$DB_ROW['theme_nom']);
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
		{
			$item_id = $DB_ROW['item_id'];
			$tab_item[$domaine_id][$theme_id][$item_id] = array('socle'=>$DB_ROW['entree_id'],'nom'=>$DB_ROW['item_nom'],'coef'=>$DB_ROW['item_coef'],'lien'=>$DB_ROW['item_lien']);
		}
	}
	// Fabrication de l'arbre XML
	$arbreXML = '<arbre id="SACoche">'."\r\n";
	if(count($tab_domaine))
	{
		foreach($tab_domaine as $domaine_id => $tab_domaine_info)
		{
			$arbreXML .= "\t".'<domaine ref="'.$tab_domaine_info['ref'].'" nom="'.html($tab_domaine_info['nom']).'">'."\r\n";
			if(isset($tab_theme[$domaine_id]))
			{
				foreach($tab_theme[$domaine_id] as $theme_id => $tab_theme_info)
				{
					$arbreXML .= "\t\t".'<theme nom="'.html($tab_theme_info['nom']).'">'."\r\n";
					if(isset($tab_item[$domaine_id][$theme_id]))
					{
						foreach($tab_item[$domaine_id][$theme_id] as $item_id => $tab_item_info)
						{
							$arbreXML .= "\t\t\t".'<item socle="'.$tab_item_info['socle'].'" nom="'.html($tab_item_info['nom']).'" coef="'.$tab_item_info['coef'].'" lien="'.html($tab_item_info['lien']).'" />'."\r\n";
						}
					}
					$arbreXML .= "\t\t".'</theme>'."\r\n";
				}
			}
			$arbreXML .= "\t".'</domaine>'."\r\n";
		}
	}
	$arbreXML .= '</arbre>'."\r\n";
	return $arbreXML;
}

/**
 * compresser_arborescence_XML
 * 
 * << Problème >>
 * Attention, si on balance le xml tel quel en GET on obtient l'erreur "414 Request-URI Too Large : The requested URL's length exceeds the capacity limit for this server.".
 * En ce qui concerne Apache (v2), cette limite est dans la constante DEFAULT_LIMIT_REQUEST_LINE et correspond à la taille maximale de la ligne de requête.
 * Par défaut c’est 8190, ce qui si on retire les 14 caractères de « GET / HTTP/1.1″ nous donne exactement la limite observée empiriquement : 8176.
 * La directive d'Apache LimitRequestLine permet de modifier cette valeur (http://httpd.apache.org/docs/2.0/mod/core.html#limitrequestline).
 * Mais elle est inaccessible à PHP...
 * << Solution >>
 * Lors de l'expérimentation, la longueur moyenne de $arbreXML était de 9195, avec un maximum à 22806.
 * Les tests ont été effectués sur $arbreXML de longueur 17574 (donc assez lourd).
 * Suite à une compression utilisant gzcompress() la longueur est descendue à 3414 (-80%).
 * Mais pour obtenir des caractères transmissibles il a fallu utiliser base64_encode() et la longueur est remontée à 4552 (la doc indique +33% en moyenne).
 * Enfin pour le passer dans l'URL il a fallu utiliser urlencode() et la longueur est devenue 4796.
 * Au final on obtient 70%/75% de compression, ce qui permet normalement de résoudre ce problème !
 * 
 * @param string $arbreXML
 * @return string
 */

function compresser_arborescence_XML($arbreXML)
{
	return base64_encode(gzcompress($arbreXML,9));
}

/**
 * decompresser_arborescence_XML
 * 
 * @param string $arbreXML
 * @return string|bool
 */

function decompresser_arborescence_XML($arbreXML)
{
	return @gzuncompress(base64_decode($arbreXML),35000);
}

/**
 * envoyer_arborescence_XML
 * Transmettre le XML d'un référentiel d'un serveur à un autre (en bidouillant...).
 * 
 * @param int    $structure_id
 * @param string $structure_key
 * @param int    $matiere_id
 * @param int    $niveau_id
 * @param string $arbreXML       si fourni vide, provoquera l'effacement du référentiel mis en partage
 * @return string                "ok" ou un message d'erreur
 */

function envoyer_arborescence_XML($structure_id,$structure_key,$matiere_id,$niveau_id,$arbreXML)
{
	require_once('./_inc/class.httprequest.php');
	$tab_get = array();
	$tab_get[] = 'mode=httprequest';
	$tab_get[] = 'fichier=referentiel_uploader';
	$tab_get[] = 'structure_id='.$structure_id;
	$tab_get[] = 'structure_key='.$structure_key;
	$tab_get[] = 'matiere_id='.$matiere_id;
	$tab_get[] = 'niveau_id='.$niveau_id;
	$tab_get[] = 'adresse_retour='.urlencode(SERVEUR_ADRESSE);
	if($arbreXML)
	{
		$tab_get[] = 'arbreXML='.urlencode( compresser_arborescence_XML($arbreXML) );
	}
	$requete_envoi   = new HTTPRequest(SERVEUR_COMMUNAUTAIRE.'?'.implode('&',$tab_get));
	$requete_reponse = $requete_envoi->DownloadToString();
	return $requete_reponse;
}

/**
 * recuperer_arborescence_XML
 * Demander à ce que nous soit retourné le XML d'un référentiel depuis un autre serveur (en bidouillant...).
 * 
 * @param int    $structure_id
 * @param string $structure_key
 * @param int    $referentiel_id
 * @return string         le XML ou un message d'erreur
 */

function recuperer_arborescence_XML($structure_id,$structure_key,$referentiel_id)
{
	/*
	Comme pour la fonction envoyer_arborescence_XML(), l'arbre est compressé avant d'être transféré.
	Il faut donc le décompresser une fois réceptionné.
	*/
	require_once('./_inc/class.httprequest.php');
	$tab_get = array();
	$tab_get[] = 'mode=httprequest';
	$tab_get[] = 'fichier=referentiel_downloader';
	$tab_get[] = 'structure_id='.$structure_id;
	$tab_get[] = 'structure_key='.$structure_key;
	$tab_get[] = 'referentiel_id='.$referentiel_id;
	$requete_envoi   = new HTTPRequest(SERVEUR_COMMUNAUTAIRE.'?'.implode('&',$tab_get));
	$requete_reponse = $requete_envoi->DownloadToString();
	if(mb_substr($requete_reponse,0,6)=='Erreur')
	{
		return $requete_reponse;
	}
	$arbreXML = @gzuncompress( base64_decode( $requete_reponse ) , 35000 ) ;
	if($arbreXML==false)
	{
		return 'Erreur lors de la décompression du référentiel transmis.';
	}
	return $arbreXML;
}

/**
 * verifier_arborescence_XML
 * 
 * @param string    $arbreXML
 * @return string   "ok" ou "Erreur..."
 */

function verifier_arborescence_XML($arbreXML)
{
	// On ajoute déclaration et doctype au fichier (évite que l'utilisateur ait à se soucier de cette ligne et permet de le modifier en cas de réorganisation
	// Attention, le chemin du DTD est relatif par rapport à l'emplacement du fichier XML (pas celui du script en cours) !
	$fichier_adresse = './__tmp/import/referentiel_'.date('Y-m-d_H-i-s').'_'.mt_rand().'_xml';
	$fichier_contenu = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".'<!DOCTYPE arbre SYSTEM "../../_dtd/referentiel.dtd">'."\r\n".$arbreXML;
	// On convertit en UTF-8 si besoin
	if( (mb_detect_encoding($fichier_contenu,"auto",TRUE)!='UTF-8') || (!mb_check_encoding($fichier_contenu,'UTF-8')) )
	{
		$fichier_contenu = mb_convert_encoding($fichier_contenu,'UTF-8','Windows-1252'); // Si on utilise utf8_encode() ou mb_convert_encoding() sans le paramètre 'Windows-1252' ça pose des pbs pour '’' 'Œ' 'œ' etc.
	}
	// On enregistre temporairement dans un fichier pour analyse
	file_put_contents($fichier_adresse,$fichier_contenu);
	// On lance le test
	require('./_inc/class.domdocument.php');
	$test_XML_valide = analyser_XML($fichier_adresse);
	// On efface le fichier temporaire
	unlink($fichier_adresse);
	return $test_XML_valide;
}

/**
 * enregistrer_structure_Sesamath
 * Demander à ce que la structure soit identifiée et enregistrée dans la base du serveur partagée.
 * 
 * @param int    $structure_id
 * @param string $structure_key
 * @return string         'ok' ou un message d'erreur
 */

function enregistrer_structure_Sesamath($structure_id,$structure_key)
{
	require_once('./_inc/class.httprequest.php');
	$tab_get = array();
	$tab_get[] = 'mode=httprequest';
	$tab_get[] = 'fichier=structure_enregistrer';
	$tab_get[] = 'structure_id='.$structure_id;
	$tab_get[] = 'structure_key='.$structure_key;
	$tab_get[] = 'adresse_retour='.urlencode(SERVEUR_ADRESSE);
	$requete_envoi   = new HTTPRequest(SERVEUR_COMMUNAUTAIRE.'?'.implode('&',$tab_get));
	$requete_reponse = $requete_envoi->DownloadToString();
	return $requete_reponse;
}

?>