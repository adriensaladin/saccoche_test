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
 * Fonctions utilisées avec array_filter() ; teste si différent "X" et "REQ".
 * @return bool
 */
function non_note($note)
{
	return ($note!='X')&&($note!='REQ') ;
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
 * @param bool   $is_meta_robots  affichage ou non des balises meta pour les robots
 * @param bool   $is_favicon      affichage ou non du favicon
 * @param bool   $is_rss          affichage ou non du flux RSS associé
 * @param array  $tab_fichiers    tableau [i] => array( css | css_ie | js , chemin_fichier )
 * @param string $titre_page      titre de la page
 * @param string $css_additionnel css complémentaire (facultatif)
 * @return void
 */
function declaration_entete( $is_meta_robots ,$is_favicon , $is_rss , $tab_fichiers , $titre_page , $css_additionnel=FALSE )
{
	header('Content-Type: text/html; charset='.CHARSET);
	echo'<!DOCTYPE html>';
	echo'<html>';
	echo'<head>';
	echo'<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />';
	if($is_meta_robots)
	{
		echo'<meta name="description" content="SACoche - Suivi d\'Acquisition de Compétences - Evaluation par compétences - Valider le socle commun" />';
		echo'<meta name="keywords" content="SACoche Sésamath évaluer évaluation compétences compétence validation valider socle commun collège points note notes Lomer" />';
		echo'<meta name="author" content="Thomas Crespin pour Sésamath" />';
		echo'<meta name="robots" content="index,follow" />';
	}
	if($is_favicon)
	{
		echo'<link rel="shortcut icon" type="images/x-icon" href="./favicon.ico" />';
		echo'<link rel="icon" type="image/png" href="./favicon.png" />';
		echo'<link rel="apple-touch-icon" href="./_img/apple-touch-icon-114x114.png" />';
		echo'<link rel="apple-touch-icon-precomposed" href="./_img/apple-touch-icon-114x114.png" />';
	}
	if($is_rss)
	{
		echo'<link rel="alternate" type="application/rss+xml" href="'.SERVEUR_RSS.'" title="SACoche" />';
	}
	foreach($tab_fichiers as $tab_infos)
	{
		list( $type , $url ) = $tab_infos;
		switch($type)
		{
			case 'css'    : echo'<link rel="stylesheet" type="text/css" href="'.$url.'" />'; break;
	//  case 'css_ie' : echo'<!--[if lte IE 8]><link rel="stylesheet" type="text/css" href="'.$url.'" /><![endif]-->'; break;
			case 'js'     : echo'<script type="text/javascript" charset="'.CHARSET.'" src="'.$url.'"></script>'; break;
		}
	}
	if($css_additionnel)
	{
		echo $css_additionnel; // style complémentaire déjà dans <style type="text/css">...</style>
	}
	echo'<title>'.$titre_page.'</title>';
	echo'</head>';
}

/**
 * Compression ou minification d'un fichier css ou js sur le serveur en production, avec date auto-insérée si besoin pour éviter tout souci de mise en cache.
 * Si pas de compression (hors PROD), ajouter un GET dans l'URL force le navigateur à mettre à jour son cache.
 * Attention cependant concernant cette dernière technique : avec les réglages standards d'Apache, ajouter un GET dans l'URL fait que beaucoup de navigateurs ne mettent pas le fichier en cache (donc il est rechargé tout le temps, même si le GET est le même) ; pas de souci si le serveur envoie un header avec une date d'expiration explicite...
 * 
 * @param string $chemin    chemin complet vers le fichier
 * @param string $methode   soit "pack" soit "mini"
 * @return string           chemin vers le fichier à prendre en compte (à indiquer dans la page web) ; il sera relatif si non compressé, absolu si compressé
 */
function compacter($chemin,$methode)
{
	$fichier_original_chemin = $chemin;
	$fichier_original_date   = filemtime($fichier_original_chemin);
	$fichier_original_url    = $fichier_original_chemin.'?t='.$fichier_original_date;
	if(SERVEUR_TYPE == 'PROD')
	{
		// On peut se permettre d'enregistrer les js et css en dehors de leur dossier d'origine car les répertoires sont tous de mêmes niveaux
		// Pour un css l'extension doit être conservée (pour un js aussi, le serveur pouvant se baser sur les extensions pour sa gestion du cache et des charset)
		$fichier_original_extension = pathinfo($fichier_original_chemin,PATHINFO_EXTENSION);
		$fichier_chemin_sans_slash  = substr( str_replace( array('./sacoche/','./','/') , array('','','__') , $fichier_original_chemin ) , 0 , -(strlen($fichier_original_extension)+1) );
		$fichier_compact_nom        = $fichier_chemin_sans_slash.'_'.$fichier_original_date.'.'.$methode.'.'.$fichier_original_extension;
		$fichier_compact_chemin     = (!defined('APPEL_SITE_PROJET')) ? CHEMIN_DOSSIER_TMP.$fichier_compact_nom : str_replace(DS.'sacoche'.DS,DS ,CHEMIN_DOSSIER_TMP).$fichier_compact_nom ;
		$fichier_compact_url        = (!defined('APPEL_SITE_PROJET')) ? URL_DIR_TMP       .$fichier_compact_nom : str_replace('/sacoche/'    ,'/',URL_DIR_TMP       ).$fichier_compact_nom ;
		$fichier_compact_date       = (is_file($fichier_compact_chemin)) ? filemtime($fichier_compact_chemin) : 0 ;
		// Sur le serveur en production, on compresse le fichier s'il ne l'est pas
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
			elseif( ($fichier_original_extension=='css') && ($methode=='mini') )
			{
				$fichier_compact_contenu = cssmin::minify($fichier_original_contenu);
			}
			else
			{
				// Normalement on ne doit pas en arriver là... sauf à passer de mauvais paramètres à la fonction.
				$fichier_compact_contenu = $fichier_original_contenu;
			}
			$fichier_compact_contenu = utf8_encode($fichier_compact_contenu);	// On réencode donc en UTF-8...
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
 * 
 * @param int   $BASE
 * @return void | exit
 */
function charger_parametres_mysql_supplementaires($BASE)
{
	$file_config_base_structure_multi = CHEMIN_DOSSIER_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php';
	if(is_file($file_config_base_structure_multi))
	{
		global $_CONST; // Car si on charge les paramètres dans une fonction, ensuite ils ne sont pas trouvés par la classe de connexion.
		require($file_config_base_structure_multi);
		require(CHEMIN_DOSSIER_INCLUDE.'class.DB.config.sacoche_structure.php');
	}
	else
	{
		exit_error( 'Paramètres BDD manquants' /*titre*/ , 'Les paramètres de connexion à la base de données n\'ont pas été trouvés.<br />Le fichier "'.FileSystem::fin_chemin($file_config_base_structure_multi).'" (base n°'.$BASE.') est manquant !' /*contenu*/ );
	}
}

/**
 * Fabriquer un login à partir de nom/prénom selon le format paramétré par l'administrateur (reste à tester sa disponibilité).
 * 
 * @param string $prenom
 * @param string $nom
 * @param string $profil   eleve | parent | professeur | directeur
 * @return string
 */
function fabriquer_login($prenom,$nom,$profil)
{
	$modele = $_SESSION['MODELE_'.strtoupper($profil)];
	$login_prenom = mb_substr( str_replace(array('.','-','_'),'',Clean::login($prenom)) , 0 , mb_substr_count($modele,'p') );
	$login_nom    = mb_substr( str_replace(array('.','-','_'),'',Clean::login($nom))    , 0 , mb_substr_count($modele,'n') );
	$login_separe = str_replace(array('p','n'),'',$modele);
	$login = ($modele{0}=='p') ? $login_prenom.$login_separe.$login_nom : $login_nom.$login_separe.$login_prenom ;
	return $login;
}

/**
 * Fabriquer un mot de passe ; 8 caractères imposés.
 * 
 * Certains caractères sont évités :
 * "e" sinon un tableur peut interpréter le mot de passe comme un nombre avec exposant
 * "i"j"1"l" pour éviter une confusion entre eux
 * "m"w" pour éviter la confusion avec "nn"vv"
 * "o"0" pour éviter une confusion entre eux
 * 
 * @param void
 * @return string
 */
function fabriquer_mdp()
{
	return mb_substr(str_shuffle('2345678923456789aaaaauuuuubcdfghknpqrstvxyz'),0,8);
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
 * Utilisé pour les flux RSS et les bilans officiels PDF.
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
function maj_base_si_besoin($BASE)
{
	$version_base = DB_STRUCTURE_PUBLIC::DB_version_base();
	if($version_base != VERSION_BASE)
	{
		// On ne met pas à jour la base tant que le webmestre bloque l'accès à l'application, car sinon cela pourrait se produire avant le transfert de tous les fichiers.
		if(LockAcces::tester_blocage('webmestre',0)===NULL)
		{
			// Bloquer l'application
			LockAcces::bloquer_application('automate',$BASE,'Mise à jour de la base en cours.');
			// Lancer une mise à jour de la base
			DB_STRUCTURE_MAJ_BASE::DB_maj_base($version_base);
			// Log de l'action
			SACocheLog::ajouter('Mise à jour automatique de la base '.SACOCHE_STRUCTURE_BD_NAME.'.');
			// Débloquer l'application
			LockAcces::debloquer_application('automate',$BASE);
		}
	}
}

/**
 * Équivalent de file_get_contents pour récupérer un fichier sur un serveur distant.
 * 
 * On peut aussi l'utiliser pour récupérer le résultat d'un script PHP exécuté sur un serveur distant.
 * On peut alors envoyer au script des paramètres en POST.
 * 
 * Concernant le timeout.
 * La fonction set_time_limit(), tout comme la directive de configuration de php.ini max_execution_time, n'affectent que le temps d'exécution du script lui-même. Tout temps passé en dehors du script, comme un appel système utilisant system(), des opérations sur les flux, les requêtes sur base de données, etc. n'est pas pris en compte lors du calcul de la durée maximale d'exécution du script.
 * Un appel cURL est un exemple d'opération de flux et n'est donc pas limité parun max_execution_time.
 * Du point du vue de l'administrateur système, un timeout cURL élevé n'est pas un souci : une connexion ouverte sans trafic dessus, tant qu'il n'y en a pas des milliers, c'est pas important.
 * Le timeout cURL sert juste à fixer "à partir de X secondes je n'attends plus et j'annonce que ça a planté", donc avec un timeout cURL élevé l'utilisateur risque juste de poireauter davantage avant de se prendre une erreur.
 * Le timeout cURL sert aussi à ne pas laisser de connexion ouverte indéfiniment.
 * 
 * @param string $url
 * @param array  $tab_post   tableau[nom]=>valeur de données à envoyer en POST (facultatif)
 * @param int    $timeout    valeur du timeout en s ; facultatif, par défaut 10
 * @return string
 */
function url_get_contents($url,$tab_post=FALSE,$timeout=10)
{
	// Ne pas utiliser file_get_contents() car certains serveurs n'accepent pas d'utiliser une URL comme nom de fichier (gestionnaire fopen non activé).
	// On utilise donc la bibliothèque cURL en remplacement
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 3600); // Le temps en seconde que cURL doit conserver les entrées DNS en mémoire. Cette option est définie à 120 secondes (2 minutes) par défaut.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);    // TRUE retourne directement le transfert sous forme de chaîne de la valeur retournée par curl_exec() au lieu de l'afficher directement.
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);   // FALSE pour que cURL ne vérifie pas le certificat (sinon, en l'absence de certificat, on récolte l'erreur "SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed").
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);   // CURLOPT_SSL_VERIFYHOST doit aussi être positionnée à 1 ou 0 si CURLOPT_SSL_VERIFYPEER est désactivée (par défaut à 2) ; sinon, on peut récolter l'erreur "SSL: certificate subject name 'secure.sesamath.fr' does not match target host name 'sacoche.sesamath.net'", mê si ça a été résolu depuis. (http://fr.php.net/manual/fr/function.curl-setopt.php#75711)
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);       // TRUE pour que PHP traite silencieusement les codes HTTP supérieurs ou égaux à 400. Le comportement par défaut est de retourner la page normalement, en ignorant ce code.
	curl_setopt($ch, CURLOPT_HEADER, FALSE);           // FALSE pour ne pas inclure l'en-tête dans la valeur de retour.
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);       // Le temps maximum d'exécution de la fonction cURL (en s) ; éviter de monter cette valeur pour libérer des ressources plus rapidement : 'classiquement', le serveur doit répondre en qq ms, donc si au bout de 5s il a pas répondu c'est qu'il ne répondra plus, alors pas la peine de bloquer une connexion et de la RAM pendant plus longtemps.
	curl_setopt($ch, CURLOPT_URL, $url);               // L'URL à récupérer. Vous pouvez aussi choisir cette valeur lors de l'appel à curl_init().
	if( (!ini_get('safe_mode')) && (!ini_get('open_basedir')) )
	{                                                 // Option CURLOPT_FOLLOWLOCATION sous conditions car certaines installations renvoient "CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set" (http://www.php.net/manual/fr/features.safe-mode.functions.php#92192)
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // TRUE pour suivre toutes les en-têtes "Location: " que le serveur envoie dans les en-têtes HTTP (notez que cette fonction est récursive et que PHP suivra toutes les en-têtes "Location: " qu'il trouvera à moins que CURLOPT_MAXREDIRS ne soit définie).
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);         // Le nombre maximal de redirections HTTP à suivre. Utilisez cette option avec l'option CURLOPT_FOLLOWLOCATION.
	}
	else
	{                                                 // Solution de remplacement inspirée de http://fr.php.net/manual/fr/function.curl-setopt.php#102121
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		$maxredirs = 3 ;
		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$rch = curl_copy_handle($ch);
		curl_setopt($rch, CURLOPT_HEADER, TRUE);
		curl_setopt($rch, CURLOPT_NOBODY, TRUE);
		curl_setopt($rch, CURLOPT_FORBID_REUSE, FALSE);
		curl_setopt($rch, CURLOPT_RETURNTRANSFER, TRUE);
		do
		{
			curl_setopt($rch, CURLOPT_URL, $url);
			$header = curl_exec($rch);
			if (curl_errno($rch))
			{
				$code = 0;
			}
			else
			{
				$code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
				if ($code == 301 || $code == 302)
				{
					preg_match('/Location:(.*?)\n/', $header, $matches);
					$newurl = trim(array_pop($matches));
					// Pb : l'URL peut être relative, et si on perd le domaine alors après ça plante
					if( (substr($newurl,0,4)!='http') && (substr($newurl,0,3)!='ftp') )
					{
						$pos_last_slash = strrpos($url,'/');
						$newurl_debut = ($pos_last_slash>7) ? substr($url,0,$pos_last_slash+1) : $url.'/' ;
						$newurl_fin   = ($newurl{0}=='/')   ? substr($newurl,1)                : $newurl ;
						$newurl = $newurl_debut.$newurl_fin;
					}
					$url = $newurl;
				}
				else
				{
					$code = 0;
				}
			}
		}
		while ($code && --$maxredirs);
		curl_close($rch);
		curl_setopt($ch, CURLOPT_URL, $url);
	}
	if( (defined('SERVEUR_PROXY_USED')) && (SERVEUR_PROXY_USED) )
	{                                                                    // Serveur qui nécessite d'utiliser un tunnel à travers un proxy HTTP.
		curl_setopt($ch, CURLOPT_PROXY,     SERVEUR_PROXY_NAME);           // Le nom du proxy HTTP au tunnel qui le demande.
		curl_setopt($ch, CURLOPT_PROXYPORT, (int)SERVEUR_PROXY_PORT);      // Le numéro du port du proxy à utiliser pour la connexion. Ce numéro de port peut également être défini dans l'option CURLOPT_PROXY.
		curl_setopt($ch, CURLOPT_PROXYTYPE, constant(SERVEUR_PROXY_TYPE)); // Soit CURLPROXY_HTTP (par défaut), soit CURLPROXY_SOCKS5.
		if(SERVEUR_PROXY_AUTH_USED)
		{                                                                                              // Serveur qui nécessite de s'authentifier pour utiliser le proxy.
			curl_setopt($ch, CURLOPT_PROXYAUTH,    constant(SERVEUR_PROXY_AUTH_METHOD));                 // La méthode d'identification HTTP à utiliser pour la connexion à un proxy. Utilisez la même méthode que celle décrite dans CURLOPT_HTTPAUTH. Pour une identification avec un proxy, seuls CURLAUTH_BASIC et CURLAUTH_NTLM sont actuellement supportés.
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, SERVEUR_PROXY_AUTH_USER.':'.SERVEUR_PROXY_AUTH_PASS); // Un nom d'utilisateur et un mot de passe formatés sous la forme "[username]:[password]" à utiliser pour la connexion avec le proxy.
		}
	}
	if(is_array($tab_post))
	{
		curl_setopt($ch, CURLOPT_POST,       TRUE);             // TRUE pour que PHP fasse un HTTP POST. Un POST est un encodage normal application/x-www-from-urlencoded, utilisé couramment par les formulaires HTML. 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $tab_post);        // Toutes les données à passer lors d'une opération de HTTP POST. Peut être passé sous la forme d'une chaîne encodée URL, comme 'para1=val1&para2=val2&...' ou sous la forme d'un tableau dont le nom du champ est la clé, et les données du champ la valeur. Si le paramètre value est un tableau, l'en-tête Content-Type sera définie à multipart/form-data. 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); // Eviter certaines erreurs cURL 417 ; voir explication http://fr.php.net/manual/fr/function.curl-setopt.php#82418 ou http://www.gnegg.ch/2007/02/the-return-of-except-100-continue/
	}
	else
	{
		curl_setopt($ch, CURLOPT_POST, FALSE);                   // Si pas de données à poster, mieux vaut forcer un appel en GET, sinon ça peut poser pb. http://fr.php.net/manual/fr/function.curl-setopt.php#104387
	}
	$requete_reponse = curl_exec($ch);
	if($requete_reponse === FALSE)
	{
		$requete_reponse = 'Erreur : '.curl_error($ch);
	}
	curl_close($ch);
	return $requete_reponse;
}

/**
 * Récupérer le numéro de la dernière version de SACoche disponible auprès du serveur communautaire.
 * 
 * @param void
 * @return string 'AAAA-MM-JJi' ou message d'erreur
 */
function recuperer_numero_derniere_version()
{
	$requete_reponse = url_get_contents(SERVEUR_VERSION);
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
	return (getmxrr($mail_domaine,$tab_mxhosts)==TRUE) ? TRUE : $mail_domaine ;
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
 * @param string $date   AAAA-MM-JJ
 * @return string        JJ/MM/AAAA
 */
function convert_date_mysql_to_french($date)
{
	list($annee,$mois,$jour) = explode('-',$date);
	return $jour.'/'.$mois.'/'.$annee;
}

/**
 * Passer d'une date française JJ/MM/AAAA à une date MySQL AAAA-MM-JJ.
 *
 * @param string $date   JJ/MM/AAAA
 * @return string|NULL   AAAA-MM-JJ
 */
function convert_date_french_to_mysql($date)
{
	if($date=='00/00/0000') return NULL;
	list($jour,$mois,$annee) = explode('/',$date);
	return $annee.'-'.$mois.'-'.$jour;
}

/**
 * Renvoyer le 1er jour de l'année scolaire en cours, au format français JJ/MM/AAAA ou MySQL AAAA-MM-JJ.
 *
 * @param string $format   'mysql'|'french'
 * @return string
 */
function jour_debut_annee_scolaire($format)
{
	$jour  = '01';
	$mois  = sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']);
	$annee = (date("n")<$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']) ? date("Y")-1 : date("Y") ;
	return ($format=='mysql') ? $annee.'-'.$mois.'-'.$jour : $jour.'/'.$mois.'/'.$annee ;
}

/**
 * Renvoyer le dernier jour de l'année scolaire en cours, au format français JJ/MM/AAAA ou MySQL AAAA-MM-JJ.
 * En fait, c'est plus exactement le 1er jour de l'année scolaire suivante...
 *
 * @param string $format   'mysql'|'french'
 * @return string
 */
function jour_fin_annee_scolaire($format)
{
	$jour  = '01';
	$mois  = sprintf("%02u",$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']);
	$annee = (date("n")<$_SESSION['MOIS_BASCULE_ANNEE_SCOLAIRE']) ? date("Y") : date("Y")+1 ;
	return ($format=='mysql') ? $annee.'-'.$mois.'-'.$jour : $jour.'/'.$mois.'/'.$annee ;
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
?>