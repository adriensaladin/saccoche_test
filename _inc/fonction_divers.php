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
 * test_A
 * Tester un item est considéré comme acquis au vu du score transmis.
 * Le seuil peut être celui défini globalement (par défaut si rien de transmis) ou un seuil testé ; peut être appelé avec array_filter().
 * 
 * @param int $score
 * @param int $seuil (facultatif)
 * @return void
 */

function test_A($score,$seuil=null)
{
	$seuil = ($seuil===null) ? $_SESSION['CALCUL_SEUIL']['V'] : $seuil ;
	return $score>$seuil ;
}

/**
 * test_NA
 * Tester un item est considéré comme non acquis au vu du score transmis.
 * Le seuil peut être celui défini globalement (par défaut si rien de transmis) ou un seuil testé ; peut être appelé avec array_filter().
 * 
 * @param int $score
 * @param int $seuil
 * @return void
 */

function test_NA($score,$seuil=null)
{
	$seuil = ($seuil===null) ? $_SESSION['CALCUL_SEUIL']['R'] : $seuil ;
	return $score<$seuil ;
}

/**
 * calculer_score
 * Calculer le score d'un item, à partir des notes transmises et des paramètres de calcul.
 * 
 * @param array  $tab_devoirs      $tab_devoirs[$i]['note'] = note
 * @param string $calcul_methode   'geometrique' / 'arithmetique' / 'classique' / 'moyenne' / 'bestof'
 * @param int    $calcul_limite    nb maxi d'éval à prendre en compte
 * @return void
 */

function calculer_score($tab_devoirs,$calcul_methode,$calcul_limite)
{
	// on passe en revue les évaluations disponibles, et on retient les notes exploitables
	$tab_modele_bon = array('RR','R','V','VV');	// les notes prises en compte dans le calcul du score
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
		return false;
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
 * ajouter_log
 * Ajout d'un log dans un fichier d'actions critiques
 * 
 * @param string $contenu   description de l'action
 * @return void
 */

function ajouter_log($contenu)
{
	$chemin_fichier = './__private/log/base_'.$_SESSION['BASE'].'.php';
	$tab_ligne = array();
	$tab_ligne[] = '<?php /*';
	$tab_ligne[] = date('d-m-Y H:i:s');
	$tab_ligne[] = html($_SESSION['USER_PROFIL'].' ['.$_SESSION['USER_ID'].'] '.$_SESSION['USER_NOM'].' '.$_SESSION['USER_PRENOM']);
	$tab_ligne[] = html($contenu);
	$tab_ligne[] = '*/ ?>'."\r\n";
	Ecrire_Fichier($chemin_fichier, implode("\t",$tab_ligne), FILE_APPEND);
}

/**
 * compacter
 * Compression si d'un fichier css ou js sur le serveur en production
 * 
 * @param string $chemin    chemin complet vers le fichier
 * @param string $version   $version éventuelle du fichier pour éviter un pb de mise en cache
 * @param string $methode   soit "pack" soit "mini"
 * @return string           chemin complet vers le fichier à prendre en compte
 */

function compacter($chemin,$version,$methode)
{
	$extension = pathinfo($chemin,PATHINFO_EXTENSION);
	$chemin_sans_extension   = substr($chemin,0,-(strlen($extension)+1)); // PATHINFO_FILENAME ajouté en PHP 5.2.0 seulement...
	$chemin_fichier_original = $chemin;
	$chemin_fichier_compacte = $chemin_sans_extension.'.'.$methode.$version.'.'.$extension; // Pour un css l'extension doit être conservée (pour un js peu importe)
	if(SERVEUR_TYPE == 'PROD')
	{
		// Sur le serveur en production, on compresse le fichier s'il ne l'est pas
		if( (!is_file($chemin_fichier_compacte)) || (filemtime($chemin_fichier_compacte)<filemtime($chemin_fichier_original)) )
		{
			$fichier_contenu = file_get_contents($chemin_fichier_original);
			$fichier_contenu = utf8_decode($fichier_contenu); // Attention, il faut envoyer à ces classes de l'iso et pas de l'utf8.
			if( ($extension=='js') && ($methode=='pack') )
			{
				require_once('class.JavaScriptPacker.php');	// Ne pas mettre de chemin !
				$myPacker = new JavaScriptPacker($fichier_contenu, 62, true, false);
				$fichier_compacte = $myPacker->pack();
			}
			elseif( ($extension=='js') && ($methode=='mini') )
			{
				require_once('class.JavaScriptMinified.php');	// Ne pas mettre de chemin !
				$fichier_compacte = JSMin::minify($fichier_contenu);
			}
			elseif( ($extension=='css') && ($methode=='mini') )
			{
				require_once('class.CssMinified.php');	// Ne pas mettre de chemin !
				$fichier_compacte = cssmin::minify($fichier_contenu);
			}
			else
			{
				// Normalement on ne doit pas en arriver là... sauf à passer de mauvais paramètres à la fonction.
				$fichier_compacte = $fichier_contenu;
			}
			$fichier_compacte = utf8_encode($fichier_compacte);	// On réencode donc en UTF-8...
			@umask(0002); // Met le chmod à 666 - 002 = 664 pour les fichiers prochains fichiers créés (et à 777 - 002 = 775 pour les dossiers).
			$test_ecriture = @file_put_contents($chemin_fichier_compacte,$fichier_compacte);
			// Il se peut que le droit en écriture ne soit pas autorisé et que la procédure d'install ne l'ai pas encore vérifié.
			return $test_ecriture ? $chemin_fichier_compacte : $chemin_fichier_original ;
		}
		return $chemin_fichier_compacte;
	}
	else
	{
		// Sur le serveur local, on travaille avec le fichier normal pour le debugguer si besoin et ne pas encombrer le SVN
		return $chemin_fichier_original;
	}
}

/**
 * charger_parametres_mysql_supplementaires
 * 
 * Dans le cas d'une installation de type multi-structures, on peut avoir besoin d'effectuer une requête sur une base d'établissement sans y être connecté :
 * => pour savoir si le mode de connexion est SSO ou pas (./pages/public_accueil.ajax.php)
 * => pour l'identification (fonction connecter_user() dans ./_inc/fonction_requetes_administration)
 * => pour le webmestre (création d'un admin, info sur les admins, initialisation du mdp...)
 * 
 * @param int   $BASE
 * @return void
 */

function charger_parametres_mysql_supplementaires($BASE)
{
	global $CHEMIN_MYSQL;
	$file_config_base_structure_multi = $CHEMIN_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php';
	if(is_file($file_config_base_structure_multi))
	{
		global $_CONST; // Car si on charge les paramètres dans une fonction, ensuite ils ne sont pas trouvés par la classe de connexion.
		require_once($file_config_base_structure_multi);
		require_once($CHEMIN_MYSQL.'../../_inc/class.DB.config.sacoche_structure.php'); // Chemin un peu tordu... mais nécessaire à cause d'un appel particulier pour l'install Sésamath
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
	// Le "salage" complique la recherche d'un mdp à partir de son empreinte md5 en utilisant une table arc-en-ciel
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
 * @param int    $webmestre_erreur_date
 * @return void
 */

function fabriquer_fichier_hebergeur_info($hebergeur_installation,$hebergeur_denomination,$hebergeur_uai,$hebergeur_adresse_site,$hebergeur_logo,$hebergeur_cnil,$webmestre_nom,$webmestre_prenom,$webmestre_courriel,$webmestre_password_md5,$webmestre_erreur_date)
{
	global $CHEMIN_CONFIG;
	$fichier_nom     = $CHEMIN_CONFIG.'constantes.php';
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
	$fichier_contenu.= 'define(\'WEBMESTRE_ERREUR_DATE\' ,\''.str_replace('\'','\\\'',$webmestre_erreur_date) .'\');'."\r\n";
	$fichier_contenu.= '?>'."\r\n";
	Ecrire_Fichier($fichier_nom,$fichier_contenu);
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

function fabriquer_fichier_connexion_base($base_id,$BD_host,$BD_port,$BD_name,$BD_user,$BD_pass)
{
	global $CHEMIN_MYSQL;
	if( (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id>0) )
	{
		$fichier_nom = $CHEMIN_MYSQL.'serveur_sacoche_structure_'.$base_id.'.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche n°'.$base_id.' (installation multi-structures).';
		$prefixe = 'STRUCTURE';
	}
	elseif(HEBERGEUR_INSTALLATION=='mono-structure')
	{
		$fichier_nom = $CHEMIN_MYSQL.'serveur_sacoche_structure.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche (installation mono-structure).';
		$prefixe = 'STRUCTURE';
	}
	else	// (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id==0)
	{
		$fichier_nom = $CHEMIN_MYSQL.'serveur_sacoche_webmestre.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche du webmestre (installation multi-structures).';
		$prefixe = 'WEBMESTRE';
	}
	$fichier_contenu  = '<?php'."\r\n";
	$fichier_contenu .= '// '.$fichier_descriptif."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_HOST\',\''.$BD_host.'\');	// Nom d\'hôte / serveur'."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_PORT\',\''.$BD_port.'\');	// Port de connexion'."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_NAME\',\''.$BD_name.'\');	// Nom de la base'."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_USER\',\''.$BD_user.'\');	// Nom d\'utilisateur'."\r\n";
	$fichier_contenu .= 'define(\'SACOCHE_'.$prefixe.'_BD_PASS\',\''.$BD_pass.'\');	// Mot de passe'."\r\n";
	$fichier_contenu .= '?>'."\r\n";
	Ecrire_Fichier($fichier_nom,$fichier_contenu);
}

/**
 * modifier_mdp_webmestre
 * 
 * @param string $password_ancien
 * @param string $password_nouveau
 * @return string   'ok' ou 'Le mot de passe actuel est incorrect !'
 */

function modifier_mdp_webmestre($password_ancien,$password_nouveau)
{
	// Tester si l'ancien mot de passe correspond à celui enregistré
	$password_ancien_crypte = crypter_mdp($password_ancien);
	if($password_ancien_crypte!=WEBMESTRE_PASSWORD_MD5)
	{
		return 'Le mot de passe actuel est incorrect !';
	}
	// Remplacer par le nouveau mot de passe
	$password_nouveau_crypte = crypter_mdp($password_nouveau);
	fabriquer_fichier_hebergeur_info(HEBERGEUR_INSTALLATION,HEBERGEUR_DENOMINATION,HEBERGEUR_UAI,HEBERGEUR_ADRESSE_SITE,HEBERGEUR_LOGO,HEBERGEUR_CNIL,WEBMESTRE_NOM,WEBMESTRE_PRENOM,WEBMESTRE_COURRIEL,$password_nouveau_crypte,WEBMESTRE_ERREUR_DATE);
	return 'ok';
}

/**
 * bloquer_application
 * 
 * @param string $profil_demandeur
 * @param string $motif
 * @return void
 */

function bloquer_application($profil_demandeur,$motif)
{
	global $CHEMIN_CONFIG;
	$fichier_nom = ($profil_demandeur=='webmestre') ? $CHEMIN_CONFIG.'blocage_webmestre.txt' : $CHEMIN_CONFIG.'blocage_admin_'.$_SESSION['BASE'].'.txt' ;
	Ecrire_Fichier($fichier_nom,$motif);
	// Log de l'action
	ajouter_log('Blocage de l\'accès à l\'application ['.$motif.'].');
}

/**
 * debloquer_application
 * 
 * @param string $profil_demandeur
 * @return void
 */

function debloquer_application($profil_demandeur)
{
	global $CHEMIN_CONFIG;
	$fichier_nom = ($profil_demandeur=='webmestre') ? $CHEMIN_CONFIG.'blocage_webmestre.txt' : $CHEMIN_CONFIG.'blocage_admin_'.$_SESSION['BASE'].'.txt' ;
	@unlink($fichier_nom);
	// Log de l'action
	ajouter_log('Déblocage de l\'accès à l\'application.');
}

/**
 * tester_blocage_application
 * Blocage des sites sur demande du webmestre ou d'un administrateur (maintenance, sauvegarde/restauration, ...).
 * Nécessite que la session soit ouverte.
 * Appelé depuis les pages index.php + ajax.php + lors d'une demande d'identification d'un utilisateur (sauf webmestre)
 * 
 * @param string $BASE                       car $_SESSION['BASE'] non encore renseigné si demande d'identification
 * @param string $demande_connexion_profil   false si appel depuis index.php ou ajax.php, le profil si demande d'identification
 * @return void
 */

function tester_blocage_application($BASE,$demande_connexion_profil)
{
	global $CHEMIN_CONFIG;
	// Blocage demandé par le webmestre : on ne laisse l'accès que
	// + pour le webmestre déjà identifié
	// + pour la partie publique, si pas une demande d'identification, sauf demande webmestre
	$fichier_blocage_webmestre = $CHEMIN_CONFIG.'blocage_webmestre.txt';
	if( (is_file($fichier_blocage_webmestre)) && ($_SESSION['USER_PROFIL']!='webmestre') && (($_SESSION['USER_PROFIL']!='public')||($demande_connexion_profil!=false)) )
	{
		affich_message_exit($titre='Blocage par le webmestre',$contenu='Blocage par le webmestre : '.file_get_contents($fichier_blocage_webmestre) );
	}
	// Blocage demandé par un administrateur : on ne laisse l'accès que
	// + pour le webmestre déjà identifié
	// + pour un administrateur déjà identifié
	// + pour la partie publique, si pas une demande d'identification, sauf demande webmestre ou administrateur
	$fichier_blocage_administrateur = $CHEMIN_CONFIG.'blocage_admin_'.$BASE.'.txt';
	if( (is_file($fichier_blocage_administrateur)) && ($_SESSION['USER_PROFIL']!='webmestre') && ($_SESSION['USER_PROFIL']!='administrateur') && (($_SESSION['USER_PROFIL']!='public')||($demande_connexion_profil!='administrateur')) )
	{
		affich_message_exit($titre='Blocage par un administrateur',$contenu='Blocage par un administrateur : '.file_get_contents($fichier_blocage_administrateur) );
	}
}

/**
 * connecter_webmestre
 * 
 * @param string    $password
 * @return string   'ok' (et dans ce cas la session est mise à jour) ou un message d'erreur
 */

function connecter_webmestre($password)
{
	// Si tentatives trop rapprochées...
	$delai_attente_consomme = time() - WEBMESTRE_ERREUR_DATE ;
	if($delai_attente_consomme<3)
	{
		fabriquer_fichier_hebergeur_info(HEBERGEUR_INSTALLATION,HEBERGEUR_DENOMINATION,HEBERGEUR_UAI,HEBERGEUR_ADRESSE_SITE,HEBERGEUR_LOGO,HEBERGEUR_CNIL,WEBMESTRE_NOM,WEBMESTRE_PRENOM,WEBMESTRE_COURRIEL,WEBMESTRE_PASSWORD_MD5,time());
		return'Calmez-vous et patientez 10s avant toute nouvelle tentative !';
	}
	elseif($delai_attente_consomme<10)
	{
		$delai_attente_restant = 10-$delai_attente_consomme ;
		return'Merci d\'attendre encore '.$delai_attente_restant.'s avant une nouvelle tentative.';
	}
	// Si mdp incorrect...
	$password_crypte = crypter_mdp($password);
	if($password_crypte!=WEBMESTRE_PASSWORD_MD5)
	{
		fabriquer_fichier_hebergeur_info(HEBERGEUR_INSTALLATION,HEBERGEUR_DENOMINATION,HEBERGEUR_UAI,HEBERGEUR_ADRESSE_SITE,HEBERGEUR_LOGO,HEBERGEUR_CNIL,WEBMESTRE_NOM,WEBMESTRE_PRENOM,WEBMESTRE_COURRIEL,WEBMESTRE_PASSWORD_MD5,time());
		return 'Mot de passe incorrect ! Patientez 10s avant une nouvelle tentative.';
	}
	// Si on arrive ici c'est que l'identification s'est bien effectuée !
	// Numéro de la base
	$_SESSION['BASE']             = 0;
	// Données associées à l'utilisateur.
	$_SESSION['USER_PROFIL']      = 'webmestre';
	$_SESSION['USER_ID']          = 0;
	$_SESSION['USER_NOM']         = WEBMESTRE_NOM;
	$_SESSION['USER_PRENOM']      = WEBMESTRE_PRENOM;
	$_SESSION['USER_DESCR']       = '[webmestre] '.WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM;
	// Données associées à l'établissement.
	$_SESSION['SESAMATH_ID']      = 0;
	$_SESSION['DENOMINATION']     = 'Gestion '.HEBERGEUR_INSTALLATION;
	$_SESSION['MODE_CONNEXION']   = 'normal';
	$_SESSION['DUREE_INACTIVITE'] = 30;
	return 'ok';
}

/**
 * connecter_user
 * 
 * @param int       $BASE
 * @param string    $profil   'normal' ou 'administrateur'
 * @param string    $login
 * @param string    $password
 * @param string    $mode_connection   'normal' ou 'cas' ou ...
 * @return string   retourne 'ok' en cas de succès (et dans ce cas la session est mise à jour) ou un message d'erreur sinon
 */

function connecter_user($BASE,$profil,$login,$password,$mode_connection)
{
	// Blocage éventuel par le webmestre ou un administrateur
	tester_blocage_application($BASE,$demande_connexion_profil=$profil);
	// En cas de multi-structures, il faut charger les paramètres de connexion à la base concernée
	// Sauf pour une connexion à un ENT, car alors il a déjà fallu les charger pour récupérer les paramètres de connexion à l'ENT
	if( ($BASE) && ($mode_connection=='normal') )
	{
		charger_parametres_mysql_supplementaires($BASE);
	}
	// Récupérer les données associées à l'utilisateur.
	$DB_ROW = DB_STRUCTURE_recuperer_donnees_utilisateur($mode_connection,$login);
	// Si login non trouvé...
	if(!count($DB_ROW))
	{
		return ($mode_connection=='normal') ? 'Nom d\'utilisateur incorrect !' : 'Identification réussie mais identifiant ENT "'.$login.'" inconnu dans SACoche !' ;
	}
	// Si tentatives trop rapprochées...
	$delai_attente_consomme = time() - $DB_ROW['tentative_unix'] ;
	if($delai_attente_consomme<3)
	{
		DB_STRUCTURE_modifier_date('tentative',$DB_ROW['user_id']);
		return'Calmez-vous et patientez 10s avant toute nouvelle tentative !';
	}
	elseif($delai_attente_consomme<10)
	{
		$delai_attente_restant = 10-$delai_attente_consomme ;
		return'Merci d\'attendre encore '.$delai_attente_restant.'s avant une nouvelle tentative.';
	}
	// Si mdp incorrect...
	if( ($mode_connection=='normal') && ($DB_ROW['user_password']!=crypter_mdp($password)) )
	{
		DB_STRUCTURE_modifier_date('tentative',$DB_ROW['user_id']);
		return'Mot de passe incorrect ! Patientez 10s avant une nouvelle tentative.';
	}
	// Si compte desactivé...
	if($DB_ROW['user_statut']!=1)
	{
		return'Identification réussie mais ce compte est desactivé !';
	}
	// Si erreur de profil...
	if( ( ($profil!='administrateur')&&($DB_ROW['user_profil']=='administrateur') ) || ( ($profil=='administrateur')&&($DB_ROW['user_profil']!='administrateur') ) )
	{
		return'Utilisez le formulaire approprié aux '.str_replace('eleve','élève',$DB_ROW['user_profil']).'s !';
	}
	// Si on arrive ici c'est que l'identification s'est bien effectuée !
	// Enregistrer le numéro de la base
	$_SESSION['BASE']             = $BASE;
	// Enregistrer les données associées à l'utilisateur.
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
	// Récupérer et Enregistrer les données associées à l'établissement.
	$DB_TAB = DB_STRUCTURE_lister_parametres();
	foreach($DB_TAB as $DB_ROW)
	{
		switch($DB_ROW['parametre_nom'])
		{
			case 'version_base':             $_SESSION['VERSION_BASE']               =       $DB_ROW['parametre_valeur']; break;
			case 'sesamath_id' :             $_SESSION['SESAMATH_ID']                = (int) $DB_ROW['parametre_valeur']; break;
			case 'sesamath_uai' :            $_SESSION['SESAMATH_UAI']               =       $DB_ROW['parametre_valeur']; break;
			case 'sesamath_type_nom' :       $_SESSION['SESAMATH_TYPE_NOM']          =       $DB_ROW['parametre_valeur']; break;
			case 'sesamath_key' :            $_SESSION['SESAMATH_KEY']               =       $DB_ROW['parametre_valeur']; break;
			case 'uai' :                     $_SESSION['UAI']                        =       $DB_ROW['parametre_valeur']; break;
			case 'denomination':             $_SESSION['DENOMINATION']               =       $DB_ROW['parametre_valeur']; break;
			case 'connexion_mode':           $_SESSION['CONNEXION_MODE']             =       $DB_ROW['parametre_valeur']; break;
			case 'connexion_nom':            $_SESSION['CONNEXION_NOM']              =       $DB_ROW['parametre_valeur']; break;
			case 'modele_professeur':        $_SESSION['MODELE_PROF']                =       $DB_ROW['parametre_valeur']; break;
			case 'modele_eleve':             $_SESSION['MODELE_ELEVE']               =       $DB_ROW['parametre_valeur']; break;
			case 'matieres':                 $_SESSION['MATIERES']                   =       $DB_ROW['parametre_valeur']; break;
			case 'niveaux':                  $_SESSION['NIVEAUX']                    =       $DB_ROW['parametre_valeur']; break;
			case 'paliers':                  $_SESSION['PALIERS']                    =       $DB_ROW['parametre_valeur']; break;
			case 'profil_validation_entree': $_SESSION['PROFIL_VALIDATION_ENTREE']   =       $DB_ROW['parametre_valeur']; break;
			case 'profil_validation_pilier': $_SESSION['PROFIL_VALIDATION_PILIER']   =       $DB_ROW['parametre_valeur']; break;
			case 'eleve_options':            $_SESSION['ELEVE_OPTIONS']              =       $DB_ROW['parametre_valeur']; break;
			case 'eleve_demandes':           $_SESSION['ELEVE_DEMANDES']             = (int) $DB_ROW['parametre_valeur']; break;
			case 'duree_inactivite':         $_SESSION['DUREE_INACTIVITE']           = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_RR':         $_SESSION['CALCUL_VALEUR']['RR']        = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_R':          $_SESSION['CALCUL_VALEUR']['R']         = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_V':          $_SESSION['CALCUL_VALEUR']['V']         = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_valeur_VV':         $_SESSION['CALCUL_VALEUR']['VV']        = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_seuil_R':           $_SESSION['CALCUL_SEUIL']['R']          = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_seuil_V':           $_SESSION['CALCUL_SEUIL']['V']          = (int) $DB_ROW['parametre_valeur']; break;
			case 'calcul_methode':           $_SESSION['CALCUL_METHODE']             =       $DB_ROW['parametre_valeur']; break;
			case 'calcul_limite':            $_SESSION['CALCUL_LIMITE']              = (int) $DB_ROW['parametre_valeur']; break;
			case 'cas_serveur_host':         $_SESSION['CAS_SERVEUR_HOST']           =       $DB_ROW['parametre_valeur']; break;
			case 'cas_serveur_port':         $_SESSION['CAS_SERVEUR_PORT']           = (int) $DB_ROW['parametre_valeur']; break;
			case 'cas_serveur_root':         $_SESSION['CAS_SERVEUR_ROOT']           =       $DB_ROW['parametre_valeur']; break;
			case 'css_background-color_NA':  $_SESSION['CSS_BACKGROUND-COLOR']['NA'] =       $DB_ROW['parametre_valeur']; break;
			case 'css_background-color_VA':  $_SESSION['CSS_BACKGROUND-COLOR']['VA'] =       $DB_ROW['parametre_valeur']; break;
			case 'css_background-color_A':   $_SESSION['CSS_BACKGROUND-COLOR']['A']  =       $DB_ROW['parametre_valeur']; break;
			case 'css_note_style':           $_SESSION['CSS_NOTE_STYLE']             =       $DB_ROW['parametre_valeur']; break;
		}
	}
	actualiser_style_session();
	// Mémoriser la date de la (dernière) connexion
	DB_STRUCTURE_modifier_date('connexion',$_SESSION['USER_ID']);
	// Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
	setcookie(COOKIE_STRUCTURE,$BASE,time()+60*60*24*365,'/');
	return'ok';
}

/**
 * actualiser_style_session
 * 
 * @param void
 * @return void
 */

function actualiser_style_session()
{
	$_SESSION['CSS']  = '';
	$_SESSION['CSS'] .= 'table.scor_eval tbody td input.RR {background:#FFF url("./_img/note/'.$_SESSION['CSS_NOTE_STYLE'].'/RR.gif") no-repeat center center;}';
	$_SESSION['CSS'] .= 'table.scor_eval tbody td input.R  {background:#FFF url("./_img/note/'.$_SESSION['CSS_NOTE_STYLE'].'/R.gif")  no-repeat center center;}';
	$_SESSION['CSS'] .= 'table.scor_eval tbody td input.V  {background:#FFF url("./_img/note/'.$_SESSION['CSS_NOTE_STYLE'].'/V.gif")  no-repeat center center;}';
	$_SESSION['CSS'] .= 'table.scor_eval tbody td input.VV {background:#FFF url("./_img/note/'.$_SESSION['CSS_NOTE_STYLE'].'/VV.gif") no-repeat center center;}';
	$_SESSION['CSS'] .= 'table th.r , table td.r , div.r ,span.r ,label.r {background-color:'.$_SESSION['CSS_BACKGROUND-COLOR']['NA'].'}';
	$_SESSION['CSS'] .= 'table th.o , table td.o , div.o ,span.o ,label.o {background-color:'.$_SESSION['CSS_BACKGROUND-COLOR']['VA'].'}';
	$_SESSION['CSS'] .= 'table th.v , table td.v , div.v ,span.v ,label.v {background-color:'.$_SESSION['CSS_BACKGROUND-COLOR']['A'].'}';
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
 * afficher_arborescence_matiere_from_SQL
 * Retourner une liste ordonnée à afficher à partir d'une requête SQL transmise.
 * 
 * @param tab         $DB_TAB
 * @param bool        $dynamique   arborescence cliquable ou pas (plier/replier)
 * @param bool        $reference   afficher ou pas les références
 * @param bool        $aff_coef    affichage des coefficients des items (sous forme d'image)
 * @param bool        $aff_cart    affichage des possibilités de demandes d'évaluation des items (sous forme d'image)
 * @param bool|string $aff_socle   false | 'texte' | 'image' : affichage de la liaison au socle
 * @param bool|string $aff_lien    false | 'image' | 'click' : affichage des ressources de remédiation
 * @param bool        $aff_input   affichage ou pas des input checkbox avec label
 * @return string
 */

function afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique,$reference,$aff_coef,$aff_cart,$aff_socle,$aff_lien,$aff_input)
{
	$input_texte = '';
	$coef_texte  = '';
	$cart_texte  = '';
	$socle_texte = '';
	$lien_texte  = '';
	$lien_texte_avant = '';
	$lien_texte_apres = '';
	$label_texte_avant = '';
	$label_texte_apres = '';
	// Traiter le retour SQL : on remplit les tableaux suivants.
	$tab_matiere = array();
	$tab_niveau  = array();
	$tab_domaine = array();
	$tab_theme   = array();
	$tab_item    = array();
	$matiere_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['matiere_id']!=$matiere_id)
		{
			$matiere_id = $DB_ROW['matiere_id'];
			$tab_matiere[$matiere_id] = ($reference) ? $DB_ROW['matiere_ref'].' - '.$DB_ROW['matiere_nom'] : $DB_ROW['matiere_nom'] ;
			$niveau_id  = 0;
			$domaine_id = 0;
			$theme_id   = 0;
			$item_id    = 0;
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
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
		{
			$item_id = $DB_ROW['item_id'];
			if($aff_coef)
			{
				$coef_texte = '<img src="./_img/x'.$DB_ROW['item_coef'].'.gif" title="Coefficient '.$DB_ROW['item_coef'].'." /> ';
			}
			if($aff_cart)
			{
				$title = ($DB_ROW['item_cart']) ? 'Demande possible.' : 'Demande interdite.' ;
				$cart_texte = '<img src="./_img/cart'.$DB_ROW['item_cart'].'.png" title="'.$title.'" /> ';
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
				$input_texte = '<input id="id_'.$item_id.'" name="f_items[]" type="checkbox" value="'.$item_id.'" /> ';
				$label_texte_avant = '<label for="id_'.$item_id.'">';
				$label_texte_apres = '</label>';
			}
			$item_texte = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].$DB_ROW['item_ordre'].' - '.$DB_ROW['item_nom'] : $DB_ROW['item_nom'] ;
			$tab_item[$matiere_id][$niveau_id][$domaine_id][$theme_id][$item_id] = $input_texte.$label_texte_avant.$coef_texte.$cart_texte.$socle_texte.$lien_texte.$lien_texte_avant.html($item_texte).$lien_texte_apres.$label_texte_apres;
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
									if(isset($tab_item[$matiere_id][$niveau_id][$domaine_id][$theme_id]))
									{
										foreach($tab_item[$matiere_id][$niveau_id][$domaine_id][$theme_id] as $item_id => $item_texte)
										{
											$retour .= '<li class="li_n3">'.$item_texte.'</li>'."\r\n";
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
 * afficher_arborescence_socle_from_SQL
 * Retourner une liste ordonnée à afficher à partir d'une requête SQL transmise.
 * 
 * @param tab         $DB_TAB
 * @param bool        $dynamique   arborescence cliquable ou pas (plier/replier)
 * @param bool        $reference   afficher ou pas les références
 * @param bool        $aff_input   affichage ou pas des input radio avec label
 * @param bool        $ids         indiquer ou pas les identifiants des éléments (Pxxx / Sxxx / Exxx)
 * @return string
 */

function afficher_arborescence_socle_from_SQL($DB_TAB,$dynamique,$reference,$aff_input,$ids)
{
	$input_texte = '';
	$label_texte_avant = '';
	$label_texte_apres = '';
	// Traiter le retour SQL : on remplit les tableaux suivants.
	$tab_palier  = array();
	$tab_pilier  = array();
	$tab_section = array();
	$tab_entree   = array();
	$palier_id = 0;
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['palier_id']!=$palier_id)
		{
			$palier_id = $DB_ROW['palier_id'];
			$tab_palier[$palier_id] = $DB_ROW['palier_nom'];
			$pilier_id  = 0;
			$section_id = 0;
			$entree_id   = 0;
		}
		if( (!is_null($DB_ROW['pilier_id'])) && ($DB_ROW['pilier_id']!=$pilier_id) )
		{
			$pilier_id = $DB_ROW['pilier_id'];
			$tab_pilier[$palier_id][$pilier_id] = $DB_ROW['pilier_nom'];
			$tab_pilier[$palier_id][$pilier_id] = ($reference) ? $DB_ROW['pilier_ref'].' - '.$DB_ROW['pilier_nom'] : $DB_ROW['pilier_nom'];
		}
		if( (!is_null($DB_ROW['section_id'])) && ($DB_ROW['section_id']!=$section_id) )
		{
			$section_id = $DB_ROW['section_id'];
			$tab_section[$palier_id][$pilier_id][$section_id] = ($reference) ? $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].' - '.$DB_ROW['section_nom'] : $DB_ROW['section_nom'];
		}
		if( (!is_null($DB_ROW['entree_id'])) && ($DB_ROW['entree_id']!=$entree_id) )
		{
			$entree_id = $DB_ROW['entree_id'];
			if($aff_input)
			{
				$input_texte = '<input id="socle_'.$entree_id.'" name="f_socle" type="radio" value="'.$entree_id.'" /> ';
				$label_texte_avant = '<label for="socle_'.$entree_id.'">';
				$label_texte_apres = '</label>';
			}
			$entree_texte = ($reference) ? $DB_ROW['pilier_ref'].'.'.$DB_ROW['section_ordre'].'.'.$DB_ROW['entree_ordre'].' - '.$DB_ROW['entree_nom'] : $DB_ROW['entree_nom'] ;
			$tab_entree[$palier_id][$pilier_id][$section_id][$entree_id] = $input_texte.$label_texte_avant.html($entree_texte).$label_texte_apres;
		}
	}
	// Affichage de l'arborescence
	$span_avant = ($dynamique) ? '<span>' : '' ;
	$span_apres = ($dynamique) ? '</span>' : '' ;
	$retour = '<ul class="ul_m1">'."\r\n";
	if(count($tab_palier))
	{
		foreach($tab_palier as $palier_id => $palier_texte)
		{
			$retour .= '<li class="li_m1" id="palier_'.$palier_id.'">'.$span_avant.html($palier_texte).$span_apres."\r\n";
			$retour .= '<ul class="ul_n1">'."\r\n";
			if(isset($tab_pilier[$palier_id]))
			{
				foreach($tab_pilier[$palier_id] as $pilier_id => $pilier_texte)
				{
					$aff_id = ($ids) ? ' id="P'.$pilier_id.'"' : '' ;
					$retour .= '<li class="li_n1"'.$aff_id.'>'.$span_avant.html($pilier_texte).$span_apres."\r\n";
					$retour .= '<ul class="ul_n2">'."\r\n";
					if(isset($tab_section[$palier_id][$pilier_id]))
					{
						foreach($tab_section[$palier_id][$pilier_id] as $section_id => $section_texte)
						{
							$aff_id = ($ids) ? ' id="S'.$section_id.'"' : '' ;
							$retour .= '<li class="li_n2"'.$aff_id.'>'.$span_avant.html($section_texte).$span_apres."\r\n";
							$retour .= '<ul class="ul_n3">'."\r\n";
							if(isset($tab_entree[$palier_id][$pilier_id][$section_id]))
							{
								foreach($tab_entree[$palier_id][$pilier_id][$section_id] as $entree_id => $entree_texte)
								{
									$aff_id = ($ids) ? ' id="E'.$entree_id.'"' : '' ;
									$retour .= '<li class="li_n3"'.$aff_id.'>'.$entree_texte.'</li>'."\r\n";
									
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
			$tab_item[$domaine_id][$theme_id][$item_id] = array('socle'=>$DB_ROW['entree_id'],'nom'=>$DB_ROW['item_nom'],'coef'=>$DB_ROW['item_coef'],'cart'=>$DB_ROW['item_cart'],'lien'=>$DB_ROW['item_lien']);
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
							$arbreXML .= "\t\t\t".'<item socle="'.$tab_item_info['socle'].'" nom="'.html($tab_item_info['nom']).'" coef="'.$tab_item_info['coef'].'" cart="'.$tab_item_info['cart'].'" lien="'.html($tab_item_info['lien']).'" />'."\r\n";
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
 * url_get_contents
 * Équivalent de file_get_contents pour récupérer un fichier sur un serveur distant.
 * On peut aussi l'utiliser pour récupérer le résultat d'un script PHP éxécuté sur un serveur distant.
 * On peut alors envoyer au script des paramètres en POST.
 * 
 * @param string $url
 * @param array  $tab_post   tableau[nom]=>valeur de données à envoyer en POST (facultatif)
 * @return string
 */

function url_get_contents($url,$tab_post=false)
{
	// Ne pas utiliser file_get_contents() car certains serveurs n'accepent pas d'utiliser une URL comme nom de fichier (gestionnaire fopen non activé).
	// On utilise donc la bibliothèque cURL en remplacement
	// Option CURLOPT_FOLLOWLOCATION sous conditions car certaines installations renvoient "CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set" (http://www.php.net/manual/fr/features.safe-mode.functions.php#92192)
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 3600);        // Le temps en seconde que CURL doit conserver les entrées DNS en mémoire. Cette option est définie à 120 secondes (2 minutes) par défaut.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);           // TRUE retourne directement le transfert sous forme de chaîne de la valeur retournée par curl_exec() au lieu de l'afficher directement.
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);              // TRUE pour que PHP traite silencieusement les codes HTTP supérieurs ou égaux à 400. Le comportement par défaut est de retourner la page normalement, en ignorant ce code.
	curl_setopt($ch, CURLOPT_HEADER, FALSE);                  // FALSE pour ne pas inclure l'en-tête dans la valeur de retour.
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);                     // Le temps maximum d'exécution de la fonction cURL (en s).
	curl_setopt($ch, CURLOPT_URL, $url);                      // L'URL à récupérer. Vous pouvez aussi choisir cette valeur lors de l'appel à curl_init().
	if(!ini_get('safe_mode'))
	{
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);           // TRUE pour suivre toutes les en-têtes "Location: " que le serveur envoie dans les en-têtes HTTP (notez que cette fonction est récursive et que PHP suivra toutes les en-têtes "Location: " qu'il trouvera à moins que CURLOPT_MAXREDIRS ne soit définie).
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);                   // Le nombre maximal de redirections HTTP à suivre. Utilisez cette option avec l'option CURLOPT_FOLLOWLOCATION.
	}
	if(is_array($tab_post))
	{
		curl_setopt($ch, CURLOPT_POST, TRUE);                   // TRUE pour que PHP fasse un HTTP POST. Un POST est un encodage normal application/x-www-from-urlencoded, utilisé couramment par les formulaires HTML. 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $tab_post);        // Toutes les données à passer lors d'une opération de HTTP POST. Peut être passé sous la forme d'une chaîne encodée URL, comme 'para1=val1&para2=val2&...' ou sous la forme d'un tableau dont le nom du champ est la clé, et les données du champ la valeur. Si le paramètre value est un tableau, l'en-tête Content-Type sera définie à multipart/form-data. 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); // Eviter certaines erreurs curl 417 ; voir explication http://fr.php.net/manual/fr/function.curl-setopt.php#82418 ou http://www.gnegg.ch/2007/02/the-return-of-except-100-continue/
	}
	$requete_reponse = curl_exec($ch);
	if($requete_reponse === false)
	{
		$requete_reponse = 'Erreur : '.curl_error($ch);
	}
	curl_close($ch);
	return $requete_reponse;
}

/**
 * recuperer_numero_derniere_version
 * Récupérer le numéro de la dernière version de SACoche disponible auprès du serveur communautaire.
 * 
 * @param void
 * @return string 'AAAA-MM-JJi' ou message d'erreur
 */

function recuperer_numero_derniere_version()
{
	$requete_reponse = url_get_contents(SERVEUR_VERSION);
	return (preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}[a-z]?$#',$requete_reponse)) ? $requete_reponse : 'Dernière version non détectée...' ;
}

/**
 * envoyer_arborescence_XML
 * Transmettre le XML d'un référentiel au serveur communautaire.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $matiere_id
 * @param int       $niveau_id
 * @param string    $arbreXML       si fourni vide, provoquera l'effacement du référentiel mis en partage
 * @return string   "ok" ou un message d'erreur
 */

function envoyer_arborescence_XML($sesamath_id,$sesamath_key,$matiere_id,$niveau_id,$arbreXML)
{
	$tab_post = array();
	$tab_post['fichier']        = 'referentiel_uploader';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['matiere_id']     = $matiere_id;
	$tab_post['niveau_id']      = $niveau_id;
	$tab_post['arbreXML']       = $arbreXML;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['version_base']   = VERSION_BASE; // La base doit être compatible (problème de socle modifié...)
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * recuperer_arborescence_XML
 * Demander à ce que nous soit retourné le XML d'un référentiel depuis le serveur communautaire.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $referentiel_id
 * @return string   le XML ou un message d'erreur
 */

function recuperer_arborescence_XML($sesamath_id,$sesamath_key,$referentiel_id)
{
	$tab_post = array();
	$tab_post['fichier']        = 'referentiel_downloader';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['referentiel_id'] = $referentiel_id;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['version_base']   = VERSION_BASE; // La base doit être compatible (problème de socle modifié...)
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
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
	$fichier_adresse = './__tmp/import/referentiel_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.xml';
	$fichier_contenu = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".'<!DOCTYPE arbre SYSTEM "../../_dtd/referentiel.dtd">'."\r\n".$arbreXML;
	$fichier_contenu = utf8($fichier_contenu); // Mettre en UTF-8 si besoin
	// On enregistre temporairement dans un fichier pour analyse
	Ecrire_Fichier($fichier_adresse,$fichier_contenu);
	// On lance le test
	require('class.domdocument.php');	// Ne pas mettre de chemin !
	$test_XML_valide = analyser_XML($fichier_adresse);
	// On efface le fichier temporaire
	unlink($fichier_adresse);
	return $test_XML_valide;
}

/**
 * Sesamath_enregistrer_structure
 * Demander à ce que la structure soit identifiée et enregistrée dans la base du serveur communautaire.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @return string   'ok' ou un message d'erreur
 */

function Sesamath_enregistrer_structure($sesamath_id,$sesamath_key)
{
	$tab_post = array();
	$tab_post['fichier']        = 'structure_enregistrer';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Sesamath_afficher_formulaire_geo1
 * Appel au serveur communautaire pour afficher le formulaire géographique n°1.
 * 
 * @param void
 * @return string   '<option>...</option>' ou un message d'erreur
 */

function Sesamath_afficher_formulaire_geo1()
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_afficher_formulaire_geo';
	$tab_post['etape']        = 1;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Sesamath_afficher_formulaire_geo2
 * Appel au serveur communautaire pour afficher le formulaire géographique n°2.
 * 
 * @param int       $geo1
 * @return string   '<option>...</option>' ou un message d'erreur
 */

function Sesamath_afficher_formulaire_geo2($geo1)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_afficher_formulaire_geo';
	$tab_post['etape']        = 2;
	$tab_post['geo1']         = $geo1;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Sesamath_afficher_formulaire_geo3
 * Appel au serveur communautaire pour afficher le formulaire géographique n°3.
 * 
 * @param int       $geo1
 * @param int       $geo2
 * @return string   '<option>...</option>' ou un message d'erreur
 */

function Sesamath_afficher_formulaire_geo3($geo1,$geo2)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_afficher_formulaire_geo';
	$tab_post['etape']        = 3;
	$tab_post['geo1']         = $geo1;
	$tab_post['geo2']         = $geo2;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Sesamath_lister_structures_by_commune
 * Appel au serveur communautaire pour lister les structures de la commune indiquée.
 * 
 * @param int       $geo3
 * @return string   '<option>...</option>' ou un message d'erreur
 */

function Sesamath_lister_structures_by_commune($geo3)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_lister_structures';
	$tab_post['methode']      = 'commune';
	$tab_post['geo3']         = $geo3;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Sesamath_recuperer_structure_by_UAI
 * Appel au serveur communautaire pour récupérer la structure du numéro UAI indiqué.
 * 
 * @param string    $uai
 * @return string   '<option>...</option>' ou un message d'erreur
 */

function Sesamath_recuperer_structure_by_UAI($uai)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_lister_structures';
	$tab_post['methode']      = 'UAI';
	$tab_post['uai']          = $uai;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * afficher_formulaire_structures_communautaires
 * Appel au serveur communautaire pour afficher le formulaire des structures ayant partagées au moins un référentiel.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @return string   '<option>...</option>' ou un message d'erreur
 */

function afficher_formulaire_structures_communautaires($sesamath_id,$sesamath_key)
{
	$tab_post = array();
	$tab_post['fichier']      = 'structures_afficher_formulaire';
	$tab_post['sesamath_id']  = $sesamath_id;
	$tab_post['sesamath_key'] = $sesamath_key;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * afficher_liste_referentiels
 * Appel au serveur communautaire pour lister les référentiels partagés trouvés selon les critères retenus (matière / niveau / structure).
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $matiere_id
 * @param int       $niveau_id
 * @param int       $structure_id
 * @return string   listing ou un message d'erreur
 */

function afficher_liste_referentiels($sesamath_id,$sesamath_key,$matiere_id,$niveau_id,$structure_id)
{
	$tab_post = array();
	$tab_post['fichier']      = 'referentiels_afficher_liste';
	$tab_post['sesamath_id']  = $sesamath_id;
	$tab_post['sesamath_key'] = $sesamath_key;
	$tab_post['matiere_id']   = $matiere_id;
	$tab_post['niveau_id']    = $niveau_id;
	$tab_post['structure_id'] = $structure_id;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * afficher_contenu_referentiel
 * Appel au serveur communautaire voir le contenu d'un référentiel partagé.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $referentiel_id
 * @return string   arborescence ou un message d'erreur
 */

function afficher_contenu_referentiel($sesamath_id,$sesamath_key,$referentiel_id)
{
	$tab_post = array();
	$tab_post['fichier']        = 'referentiel_afficher_contenu';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['referentiel_id'] = $referentiel_id;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Creer_Dossier
 * Tester l'existence d'un dossier, le créer, tester son accès en écriture.
 * 
 * @param string   $dossier
 * @return bool
 */

function Creer_Dossier($dossier)
{
	global $affichage;
	// Le dossier existe-t-il déjà ?
	if(is_dir($dossier))
	{
		$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; déjà en place.</label><br />'."\r\n";
		return true;
	}
	// Le dossier a-t-il bien été créé ?
	@umask(0002); // Met le chmod à 666 - 002 = 664 pour les fichiers prochains fichiers créés (et à 777 - 002 = 775 pour les dossiers).
	$test = @mkdir($dossier);
	if(!$test)
	{
		$affichage .= '<label for="rien" class="erreur">Echec lors de la création du dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; : veuillez le créer manuellement.</label><br />'."\r\n";
		return false;
	}
	$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; créé.</label><br />'."\r\n";
	// Le dossier est-il accessible en écriture ?
	$test = is_writable($dossier);
	if(!$test)
	{
		$affichage .= '<label for="rien" class="erreur">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; inaccessible en écriture : veuillez en changer les droits manuellement.</label><br />'."\r\n";
		return false;
	}
	// Si on arrive là, c'est bon...
	$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; accessible en écriture.</label><br />'."\r\n";
	return true;
}

/**
 * Vider_Dossier
 * Vider un dossier ne contenant que d'éventuels fichiers.
 * 
 * @param string   $dossier
 * @return void
 */

function Vider_Dossier($dossier)
{
	$tab_fichier = scandir($dossier);
	unset($tab_fichier[0],$tab_fichier[1]);	// fichiers '.' et '..'
	foreach($tab_fichier as $fichier_nom)
	{
		unlink($dossier.'/'.$fichier_nom);
	}
}

/**
 * Supprimer_Dossier
 * Supprimer un dossier, après avoir effacé récursivement son contenu.
 * 
 * @param string   $dossier
 * @return void
 */

function Supprimer_Dossier($dossier)
{
	$tab_contenu = scandir($dossier);
	foreach($tab_contenu as $contenu)
	{
		if( ($contenu!='.') && ($contenu!='..') )
		{
			$chemin_contenu = $dossier.'/'.$contenu;
			if(is_dir($chemin_contenu))
			{
				Supprimer_Dossier($chemin_contenu);
				rmdir($chemin_contenu);
			}
			else
			{
				unlink($chemin_contenu);
			}
		}
	}
	rmdir($dossier);
}

/**
 * Ecrire_Fichier
 * Ecrire du contenu dans un fichier, exit() en cas d'erreur
 * 
 * @param string   $fichier_chemin
 * @param string   $fichier_contenu
 * @param int      facultatif ; si constante FILE_APPEND envoyée, alors ajoute en fin de fichier au lieu d'écraser le contenu
 * @return void
 */

function Ecrire_Fichier($fichier_chemin,$fichier_contenu,$file_append=0)
{
	@umask(0002); // Met le chmod à 666 - 002 = 664 pour les fichiers prochains fichiers créés (et à 777 - 002 = 775 pour les dossiers).
	$test_ecriture = @file_put_contents($fichier_chemin,$fichier_contenu,$file_append);
	if($test_ecriture===false)
	{
		exit('Erreur : problème lors de l\'écriture du fichier '.$fichier_chemin.' !');
	}
}

/**
 * adresse_RSS
 * Retourne le chemin du fichier RSS d'un prof ; s'il n'existe pas, en créer un vierge (pour recueillir les demandes d'évaluations des élèves).
 * 
 * @param int     $prof_id
 * @return string
 */

function adresse_RSS($prof_id)
{
	// Le nom du RSS est tordu pour le rendre un minimum privé ; il peut être retrouvé, mais très difficilement, par un bidouilleur qui met le nez dans le code, mais il n'y a rien de confidentiel non plus.
	$fichier_nom_debut = 'rss_'.$_SESSION['BASE'].'_'.$prof_id;
	$fichier_nom_fin = md5($fichier_nom_debut.$_SERVER['DOCUMENT_ROOT']);
	$fichier_chemin = './__tmp/rss/'.$fichier_nom_debut.'_'.$fichier_nom_fin.'.xml';
	if(!file_exists($fichier_chemin))
	{
		$fichier_contenu ='<?xml version="1.0" encoding="utf-8"?>'."\r\n";
		$fichier_contenu.='<rss version="2.0">'."\r\n";
		$fichier_contenu.='<channel>'."\r\n\r\n";
		$fichier_contenu.='	<title>SACoche</title>'."\r\n";
		$fichier_contenu.='	<link>'.SERVEUR_ADRESSE.'</link>'."\r\n";
		$fichier_contenu.='	<description>Demandes d\'évaluations.</description>'."\r\n";
		$fichier_contenu.='	<language>fr-FR</language>'."\r\n";
		$fichier_contenu.='	<lastBuildDate>'.date("r",time()).'</lastBuildDate>'."\r\n";
		$fichier_contenu.='	<docs>http://www.scriptol.fr/rss/RSS-2.0.html</docs>'."\r\n";
		$fichier_contenu.='	<image>'."\r\n";
		$fichier_contenu.='		<url>http://sacoche.sesamath.net/_img/logo_grand.gif</url>'."\r\n";
		$fichier_contenu.='		<title>SACoche</title>'."\r\n";
		$fichier_contenu.='		<link>http://sacoche.sesamath.net</link>'."\r\n";
		$fichier_contenu.='		<width>208</width>'."\r\n";
		$fichier_contenu.='		<height>71</height>'."\r\n";
		$fichier_contenu.='		<description></description>'."\r\n";
		$fichier_contenu.='	</image>'."\r\n\r\n";
		$fichier_contenu.='</channel>'."\r\n";
		$fichier_contenu.='</rss>'."\r\n";
		Ecrire_Fichier($fichier_chemin,$fichier_contenu);
	}
	return $fichier_chemin;
}

/**
 * Modifier_RSS
 * Mettre à jour le fichier RSS vierge d'un prof avec une demande d'évaluation d'élève.
 * 
 * @param string   $fichier_chemin
 * @param string   $titre
 * @param string   $texte
 * @param string   $guid
 * @return void
 */

function Modifier_RSS($fichier_chemin,$titre,$texte,$guid)
{
	// Ajouter l'article
	$date = date("r",time());
	$fichier_contenu = file_get_contents($fichier_chemin); // Il existe déjà car adresse_RSS() a forcément été appelée avant
	$article ='	<item>'."\r\n";
	$article.='		<title>'.html($titre).'</title>'."\r\n";
	$article.='		<link>'.SERVEUR_ADRESSE.'</link>'."\r\n";
	$article.='		<description>'.html($texte).'</description>'."\r\n";
	$article.='		<pubDate>'.$date.'</pubDate>'."\r\n";
	$article.='		<guid isPermaLink="false">'.$guid.'</guid>'."\r\n";
	$article.='	</item>'."\r\n\r\n";
	$bad = '	</image>'."\r\n\r\n";
	$bon = '	</image>'."\r\n\r\n".$article;
	$fichier_contenu = str_replace($bad,$bon,$fichier_contenu);
	// Mettre à jour la date de reconstruction
	$pbad = '#<lastBuildDate>(.*?)</lastBuildDate>#';
	$pbon = '<lastBuildDate>'.$date.'</lastBuildDate>';
	$fichier_contenu = preg_replace($pbad,$pbon,$fichier_contenu);
	// Couper si le fichier est long (on le ramène à 100Ko)
	if(mb_strlen($fichier_contenu)>120000)
	{
		$pos = mb_strpos($fichier_contenu,'</item>',100000);
		$fichier_contenu = mb_substr($fichier_contenu,0,$pos).'</item>'."\r\n\r\n".'</channel>'."\r\n";
	}
	// Enregistrer
	Ecrire_Fichier($fichier_chemin,$fichier_contenu);
}

/**
 * extraire_lignes
 * Pour retourner un tableau de lignes à partir d'un texte en se basant sur les retours chariot.
 * Utilisé notamment lors de la récupération d'un fichier CSS.
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
 * extraire_separateur_csv
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

?>