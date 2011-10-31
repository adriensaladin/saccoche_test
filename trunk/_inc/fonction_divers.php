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
 * Fonctions utilisées avec array_filter() ; teste si différent de "".
 * @return bool
 */
function non_vide($n)
{
	return $n!='' ;
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
 * @return bool
 */
function test_NA($score,$seuil=null)
{
	$seuil = ($seuil===null) ? $_SESSION['CALCUL_SEUIL']['R'] : $seuil ;
	return $score<$seuil ;
}

/**
 * Calculer le score d'un item, à partir des notes transmises et des paramètres de calcul.
 * 
 * @param array  $tab_devoirs      $tab_devoirs[$i]['note'] = note
 * @param string $calcul_methode   'geometrique' / 'arithmetique' / 'classique' / 'moyenne' / 'bestof'
 * @param int    $calcul_limite    nb maxi d'éval à prendre en compte
 * @return int|false
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
 * Ajout d'un log dans un fichier d'actions sensibles (un fichier par structure)
 * 
 * @param string $contenu   description de l'action
 * @return void
 */
function ajouter_log_SACoche($contenu)
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
 * Ajout d'un log PHP dans le fichier error-log du serveur Web
 * 
 * @param string $log_objet       objet du log
 * @param string $log_contenu     contenu du log
 * @param string $log_fichier     transmettre __FILE__
 * @param string $log_ligne       transmettre __LINE__
 * @param bool   $only_sesamath   [true] pour une inscription uniquement sur le serveur Sésamath (par défaut), [false] sinon
 * @return void
 */
function ajouter_log_PHP($log_objet,$log_contenu,$log_fichier,$log_ligne,$only_sesamath=true)
{
	if( (!$only_sesamath) || (strpos(SERVEUR_ADRESSE,SERVEUR_PROJET)===0) )
	{
		$SEP = ' ║ ';
		error_log('SACoche info' . $SEP . $log_objet . $SEP . 'base '.$_SESSION['BASE'] . $SEP . 'user '.$_SESSION['USER_ID'] . $SEP . basename($log_fichier).' '.$log_ligne . $SEP . $log_contenu,0);
	}
}

/**
 * Compression ou minification d'un fichier css ou js sur le serveur en production
 * 
 * @param string $chemin    chemin complet vers le fichier
 * @param string $version   $version éventuelle du fichier pour éviter un pb de mise en cache
 * @param string $methode   soit "pack" soit "mini"
 * @return string           chemin complet vers le fichier à prendre en compte
 */
function compacter($chemin,$version,$methode)
{
	$chemin_fichier_original  = $chemin;
	$extension                = pathinfo($chemin,PATHINFO_EXTENSION);
	$dossier_fichier_compacte = './__tmp/'; // On peut se permettre d'enregistrer les js et css en dehors de leur dossier d'origine car les répertoires sont tous de mêmes niveaux
	$nom_fichier_compacte     = substr( str_replace( array('./','/') , array('','__') , $chemin_fichier_original ) ,0,-(strlen($extension)+1));
	$chemin_fichier_compacte  = $dossier_fichier_compacte.$nom_fichier_compacte.'.'.$methode.$version.'.'.$extension; // Pour un css l'extension doit être conservée (pour un js peu importe)
	if(SERVEUR_TYPE == 'PROD')
	{
		// Sur le serveur en production, on compresse le fichier s'il ne l'est pas
		if( (!is_file($chemin_fichier_compacte)) || (filemtime($chemin_fichier_compacte)<filemtime($chemin_fichier_original)) )
		{
			$fichier_contenu = file_get_contents($chemin_fichier_original);
			$fichier_contenu = utf8_decode($fichier_contenu); // Attention, il faut envoyer à ces classes de l'iso et pas de l'utf8.
			if( ($extension=='js') && ($methode=='pack') )
			{
				$myPacker = new JavaScriptPacker($fichier_contenu, 62, true, false);
				$fichier_compacte = $myPacker->pack();
			}
			elseif( ($extension=='js') && ($methode=='mini') )
			{
				$fichier_compacte = JSMin::minify($fichier_contenu);
			}
			elseif( ($extension=='css') && ($methode=='mini') )
			{
				$fichier_compacte = cssmin::minify($fichier_contenu);
			}
			else
			{
				// Normalement on ne doit pas en arriver là... sauf à passer de mauvais paramètres à la fonction.
				$fichier_compacte = $fichier_contenu;
			}
			$fichier_compacte = utf8_encode($fichier_compacte);	// On réencode donc en UTF-8...
			@umask(0000); // Met le chmod à 666 - 000 = 666 pour les fichiers prochains fichiers créés (et à 777 - 000 = 777 pour les dossiers).
			$test_ecriture = @file_put_contents($chemin_fichier_compacte,$fichier_compacte);
			// Il se peut que le droit en écriture ne soit pas autorisé et que la procédure d'install ne l'ai pas encore vérifié ou que le dossier __tmp n'ait pas encore été créé.
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
 * Charger les parametres mysql de connexion d'un établissement qui n'auraient pas été chargé par le fichier index ou ajax.
 * 
 * Dans le cas d'une installation de type multi-structures, on peut avoir besoin d'effectuer une requête sur une base d'établissement sans y être connecté :
 * => pour savoir si le mode de connexion est SSO ou pas (./pages/public_*.php)
 * => pour l'identification (fonction tester_authentification_user())
 * => pour le webmestre (création d'un admin, info sur les admins, initialisation du mdp...)
 * 
 * @param int   $BASE
 * @return void | exit
 */
function charger_parametres_mysql_supplementaires($BASE)
{
	$file_config_base_structure_multi = CHEMIN_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php';
	if(is_file($file_config_base_structure_multi))
	{
		global $_CONST; // Car si on charge les paramètres dans une fonction, ensuite ils ne sont pas trouvés par la classe de connexion.
		require_once($file_config_base_structure_multi);
		require_once(CHEMIN_SACOCHE.'_inc'.DIRECTORY_SEPARATOR.'class.DB.config.sacoche_structure.php');
	}
	else
	{
		affich_message_exit($titre='Paramètre incorrect',$contenu='Le fichier avec les paramètres de la base n°'.$BASE.' est manquant !');
	}
}

/**
 * Ajouter une structure (mode multi-structures)
 *
 * @param int    $base_id   Pour forcer l'id de la base de la structure ; normalement transmis à 0 (=> auto-increment), sauf dans un cadre de gestion interne à Sésamath
 * @param int    $geo_id
 * @param string $structure_uai
 * @param string $localisation
 * @param string $denomination
 * @param string $contact_nom
 * @param string $contact_prenom
 * @param string $contact_courriel
 * @param string $inscription_date   Pour forcer la date d'inscription, par exemple en cas de transfert de bases académiques (facultatif).
 * @return int
 */
function ajouter_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel,$inscription_date=0)
{
	// Insérer l'enregistrement d'une nouvelle structure dans la base du webmestre
	$base_id = DB_WEBMESTRE_WEBMESTRE::DB_ajouter_structure($base_id,$geo_id,$structure_uai,$localisation,$denomination,$contact_nom,$contact_prenom,$contact_courriel,$inscription_date);
	// Génération des paramètres de connexion à la base de données
	$BD_name = 'sac_base_'.$base_id; // Limité à 64 caractères (tranquille...)
	$BD_user = 'sac_user_'.$base_id; // Limité à 16 caractères (attention !)
	$BD_pass = fabriquer_mdp();
	// Créer le fichier de connexion de la base de données de la structure
	fabriquer_fichier_connexion_base($base_id,SACOCHE_WEBMESTRE_BD_HOST,SACOCHE_WEBMESTRE_BD_PORT,$BD_name,$BD_user,$BD_pass);
	// Créer la base de données d'une structure, un utilisateur MySQL, et lui attribuer ses droits.
	DB_WEBMESTRE_WEBMESTRE::DB_ajouter_base_structure_et_user_mysql($base_id,$BD_name,$BD_user,$BD_pass);
	/* Il reste à :
		+ Lancer les requêtes pour installer et remplir les tables, éventuellement personnaliser certains paramètres de la structure
		+ Insérer le compte administrateur dans la base de cette structure, éventuellement lui envoyer un courriel
		+ Créer un dossier pour les les vignettes images
	*/
	return $base_id;
}

/**
 * Supprimer une structure (mode mono-structures)
 *
 * @param void
 * @return void
 */
function supprimer_mono_structure()
{
	// Supprimer les tables de la base
	DB_STRUCTURE_WEBMESTRE::DB_supprimer_tables_structure();
	// Supprimer le fichier de connexion
	unlink(CHEMIN_MYSQL.'serveur_sacoche_structure.php');
	// Supprimer les dossiers de fichiers temporaires par établissement : vignettes verticales, flux RSS des demandes, cookies des choix de formulaires
	Supprimer_Dossier('./__tmp/badge/'.'0');
	Supprimer_Dossier('./__tmp/cookie/'.'0');
	Supprimer_Dossier('./__tmp/rss/'.'0');
	// Supprimer les éventuels fichiers de blocage
	@unlink(CHEMIN_CONFIG.'blocage_webmestre_0.txt');
	@unlink(CHEMIN_CONFIG.'blocage_administrateur_0.txt');
	@unlink(CHEMIN_CONFIG.'blocage_automate_0.txt');
	// Log de l'action
	ajouter_log_SACoche('Résiliation de l\'inscription.');
}

/**
 * Supprimer une structure (mode multi-structures)
 *
 * @param int    $BASE 
 * @return void
 */
function supprimer_multi_structure($BASE)
{
	// Paramètres de connexion à la base de données
	$BD_name = 'sac_base_'.$BASE;
	$BD_user = 'sac_user_'.$BASE; // Limité à 16 caractères
	// Supprimer la base de données d'une structure, et son utilisateur MySQL une fois défait de ses droits.
	DB_WEBMESTRE_WEBMESTRE::DB_supprimer_base_structure_et_user_mysql($BD_name,$BD_user);
	// Supprimer le fichier de connexion
	unlink(CHEMIN_MYSQL.'serveur_sacoche_structure_'.$BASE.'.php');
	// Retirer l'enregistrement d'une structure dans la base du webmestre
	DB_WEBMESTRE_WEBMESTRE::DB_supprimer_structure($BASE);
	// Supprimer les dossiers de fichiers temporaires par établissement : vignettes verticales, flux RSS des demandes, cookies des choix de formulaires
	Supprimer_Dossier('./__tmp/badge/'.$BASE);
	Supprimer_Dossier('./__tmp/cookie/'.$BASE);
	Supprimer_Dossier('./__tmp/rss/'.$BASE);
	// Supprimer les éventuels fichiers de blocage
	@unlink(CHEMIN_CONFIG.'blocage_webmestre_'.$BASE.'.txt');
	@unlink(CHEMIN_CONFIG.'blocage_administrateur_'.$BASE.'.txt');
	@unlink(CHEMIN_CONFIG.'blocage_automate_'.$BASE.'.txt');
	// Log de l'action
	ajouter_log_SACoche('Suppression de la structure n°'.$BASE.'.');
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
		if(!is_file(CHEMIN_CONFIG.'blocage_webmestre_0.txt'))
		{
			// Bloquer l'application
			bloquer_application('automate',$BASE,'Mise à jour de la base en cours.');
			// Lancer une mise à jour de la base
			DB_STRUCTURE_MAJ_BASE::DB_maj_base($version_base);
			// Log de l'action
			ajouter_log_SACoche('Mise à jour automatique de la base '.SACOCHE_STRUCTURE_BD_NAME.'.');
			// Débloquer l'application
			debloquer_application('automate',$BASE);
		}
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
	$login_prenom = mb_substr( str_replace(array('.','-','_'),'',clean_login($prenom)) , 0 , mb_substr_count($modele,'p') );
	$login_nom    = mb_substr( str_replace(array('.','-','_'),'',clean_login($nom))    , 0 , mb_substr_count($modele,'n') );
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
	return mb_substr(str_shuffle('2345678923456789abcdfghknpqrstuvxyz'),0,8);
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
 * Fabriquer ou mettre à jour le fichier de configuration de l'hébergement (gestion par le webmestre)
 * 
 * @param array tableau $constante_nom => $constante_valeur des paramètres à modifier (sinon, on prend les constantes déjà définies)
 * @return void
 */
function fabriquer_fichier_hebergeur_info($tab_constantes_modifiees)
{
	$fichier_nom     = CHEMIN_CONFIG.'constantes.php';
	$tab_constantes_requises = array('HEBERGEUR_INSTALLATION','HEBERGEUR_DENOMINATION','HEBERGEUR_UAI','HEBERGEUR_ADRESSE_SITE','HEBERGEUR_LOGO','CNIL_NUMERO','CNIL_DATE_ENGAGEMENT','CNIL_DATE_RECEPISSE','WEBMESTRE_NOM','WEBMESTRE_PRENOM','WEBMESTRE_COURRIEL','WEBMESTRE_PASSWORD_MD5','WEBMESTRE_ERREUR_DATE','SERVEUR_PROXY_USED','SERVEUR_PROXY_NAME','SERVEUR_PROXY_PORT','SERVEUR_PROXY_TYPE','SERVEUR_PROXY_AUTH_USED','SERVEUR_PROXY_AUTH_METHOD','SERVEUR_PROXY_AUTH_USER','SERVEUR_PROXY_AUTH_PASS');
	$fichier_contenu = '<?php'."\r\n";
	$fichier_contenu.= '// Informations concernant l\'hébergement et son webmestre (n°UAI uniquement pour une installation de type mono-structure)'."\r\n";
	foreach($tab_constantes_requises as $constante_nom)
	{
		$constante_valeur = (isset($tab_constantes_modifiees[$constante_nom])) ? $tab_constantes_modifiees[$constante_nom] : constant($constante_nom);
		$espaces = str_repeat(' ',25-strlen($constante_nom));
		$fichier_contenu.= 'define(\''.$constante_nom.'\''.$espaces.',\''.str_replace('\'','\\\'',$constante_valeur).'\');'."\r\n";
	}
	$fichier_contenu.= '?>'."\r\n";
	Ecrire_Fichier($fichier_nom,$fichier_contenu);
}

/**
 * Fabriquer ou mettre à jour le fichier de connexion à la base (soit celle du webmestre, soit celle d'un établissement).
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
	if( (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id>0) )
	{
		$fichier_nom = CHEMIN_MYSQL.'serveur_sacoche_structure_'.$base_id.'.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche n°'.$base_id.' (installation multi-structures).';
		$prefixe = 'STRUCTURE';
	}
	elseif(HEBERGEUR_INSTALLATION=='mono-structure')
	{
		$fichier_nom = CHEMIN_MYSQL.'serveur_sacoche_structure.php';
		$fichier_descriptif = 'Paramètres MySQL de la base de données SACoche (installation mono-structure).';
		$prefixe = 'STRUCTURE';
	}
	else	// (HEBERGEUR_INSTALLATION=='multi-structures') && ($base_id==0)
	{
		$fichier_nom = CHEMIN_MYSQL.'serveur_sacoche_webmestre.php';
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
 * Enregister le (nouveau) mot de passe du webmestre.
 * 
 * @param string $password_ancien
 * @param string $password_nouveau
 * @return string   'ok' | 'Le mot de passe actuel est incorrect !'
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
	fabriquer_fichier_hebergeur_info( array('WEBMESTRE_PASSWORD_MD5'=>$password_nouveau_crypte) );
	return 'ok';
}

/**
 * Bloquer l'accès à SACoche (les profils concernés dépendent du profil qui exerce le blocage).
 * 
 * @param string $profil_demandeur (webmestre|administrateur|automate)
 * @param int    $id_base   (0 si demande mono-structure ou du webmestre multi-structures de bloquer tous les établissements)
 * @param string $motif
 * @return void
 */
function bloquer_application($profil_demandeur,$id_base,$motif)
{
	$fichier_nom = CHEMIN_CONFIG.'blocage_'.$profil_demandeur.'_'.$id_base.'.txt' ;
	Ecrire_Fichier($fichier_nom,$motif);
	// Log de l'action
	ajouter_log_SACoche('Blocage de l\'accès à l\'application ['.$motif.'].');
}

/**
 * Débloquer l'accès à SACoche.
 * 
 * @param string $profil_demandeur (webmestre|administrateur|automate)
 * @param int    $id_base   (0 si demande mono-structure ou du webmestre multi-structures de débloquer tous les établissements)
 * @return void
 */
function debloquer_application($profil_demandeur,$id_base)
{
	$fichier_nom = CHEMIN_CONFIG.'blocage_'.$profil_demandeur.'_'.$id_base.'.txt' ;
	@unlink($fichier_nom);
	// Log de l'action
	ajouter_log_SACoche('Déblocage de l\'accès à l\'application.');
}

/**
 * Annuler un blocage anormal à SACoche.
 * 
 * Concerne un blocage demandé par l'automate pour un établissement donné.
 * Au cas où une procédure de sauvegarde / restauration / nettoyage / tranfert échouerait, un fichier de blocage automatique pourrait être créé et ne pas être effacé.
 * Pour cette raison on teste une durée de vie anormalement longue d'une tel fichier de blocage (puisqu'il ne devrait être que temporaire).
 * 
 * Nécessite que la session soit ouverte.
 * N'est pas effectué au moment de l'appel à tester_blocage_application() car nécessite des fonctions pas encore chargées (le test du blocage devant être effectué dès que possible).
 * Appelé depuis les pages index.php + ajax.php + lors d'une demande d'identification d'un utilisateur (sauf webmestre)
 * 
 * @param void
 * @return void
 */
function annuler_blocage_anormal()
{
	if(isset($_SESSION['blocage_anormal']))
	{
		unset($_SESSION['blocage_anormal']);
		debloquer_application('automate',$BASE);
	}
}

/**
 * Tester si mdp du webmestre transmis convient.
 * 
 * @param string    $password
 * @return string   'ok' ou un message d'erreur
 */
function tester_authentification_webmestre($password)
{
	// Si tentatives trop rapprochées...
	$delai_attente_consomme = time() - WEBMESTRE_ERREUR_DATE ;
	if($delai_attente_consomme<3)
	{
		fabriquer_fichier_hebergeur_info( array('WEBMESTRE_ERREUR_DATE'=>time()) );
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
		fabriquer_fichier_hebergeur_info( array('WEBMESTRE_ERREUR_DATE'=>time()) );
		return'Mot de passe incorrect ! Patientez 10s avant une nouvelle tentative.';
	}
	// Si on arrive ici c'est que l'identification s'est bien effectuée !
	return'ok';
}

/**
 * Enregistrer en session les informations authentifiant le webmestre.
 * 
 * @param void
 * @return void
 */
function enregistrer_session_webmestre()
{
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
function tester_authentification_user($BASE,$login,$password,$mode_connection)
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
	if(!count($DB_ROW))
	{
		switch($mode_connection)
		{
			case 'normal' : $message = 'Nom d\'utilisateur incorrect !'; break;
			case 'cas'    : $message = 'Identification réussie mais identifiant SSO "'.$login.'" inconnu dans SACoche !<br />Un administrateur doit renseigner que l\'identifiant ENT associé à votre compte SACoche est "'.$login.'"&hellip;'; break;
			case 'gepi'   : $message = 'Identification réussie mais login GEPI "'.$login.'" inconnu dans SACoche !<br />Un administrateur doit renseigner que l\'identifiant GEPI associé à votre compte SACoche est "'.$login.'"&hellip;'; break;
		}
		return array($message,array());
	}
	// Blocage éventuel par le webmestre ou un administrateur
	tester_blocage_application($BASE,$DB_ROW['user_profil']);
	annuler_blocage_anormal();
	// Si tentatives trop rapprochées...
	$delai_attente_consomme = time() - $DB_ROW['tentative_unix'] ;
	if($delai_attente_consomme<3)
	{
		DB_STRUCTURE_PUBLIC::DB_modifier_date('tentative',$DB_ROW['user_id']);
		return array('Calmez-vous et patientez 10s avant toute nouvelle tentative !',array());
	}
	elseif($delai_attente_consomme<10)
	{
		$delai_attente_restant = 10-$delai_attente_consomme ;
		return array('Merci d\'attendre encore '.$delai_attente_restant.'s avant une nouvelle tentative.',array());
	}
	// Si mdp incorrect...
	if( ($mode_connection=='normal') && ($DB_ROW['user_password']!=crypter_mdp($password)) )
	{
		DB_STRUCTURE_PUBLIC::DB_modifier_date('tentative',$DB_ROW['user_id']);
		return array('Mot de passe incorrect ! Patientez 10s avant une nouvelle tentative.',array());
	}
	// Si compte desactivé...
	if($DB_ROW['user_statut']!=1)
	{
		return array('Identification réussie mais ce compte est desactivé !',array());
	}
	// Mémoriser la date de la (dernière) connexion
	DB_STRUCTURE_PUBLIC::DB_modifier_date('connexion',$DB_ROW['user_id']);
	// Enregistrement d'un cookie sur le poste client servant à retenir le dernier établissement sélectionné si identification avec succès
	setcookie(COOKIE_STRUCTURE,$BASE,time()+60*60*24*365,'');
	// Enregistrement d'un cookie sur le poste client servant à retenir le dernier mode de connexion utilisé si identification avec succès
	setcookie(COOKIE_AUTHMODE,$mode_connection,0,'');
	// Si on arrive ici c'est que l'identification s'est bien effectuée !
	return array('ok',$DB_ROW);
}

/**
 * Enregistrer en session les informations authentifiant un utilisateur (sauf le webmestre).
 * 
 * @param int     $BASE
 * @param array   $DB_ROW   ligne issue de la table sacoche_user correspondant à l'utilisateur qui se connecte.
 * @return void
 */
function enregistrer_session_user($BASE,$DB_ROW)
{
	// Enregistrer en session le numéro de la base
	$_SESSION['BASE']             = $BASE;
	// Enregistrer en session les données associées à l'utilisateur (indices du tableau de session en majuscules).
	$_SESSION['USER_PROFIL']      = $DB_ROW['user_profil'];
	$_SESSION['USER_ID']          = (int) $DB_ROW['user_id'];
	$_SESSION['USER_NOM']         = $DB_ROW['user_nom'];
	$_SESSION['USER_PRENOM']      = $DB_ROW['user_prenom'];
	$_SESSION['USER_LOGIN']       = $DB_ROW['user_login'];
	$_SESSION['USER_DESCR']       = '['.$DB_ROW['user_profil'].'] '.$DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'];
	$_SESSION['USER_DALTONISME']  = $DB_ROW['user_daltonisme'];
	$_SESSION['USER_ID_ENT']      = $DB_ROW['user_id_ent'];
	$_SESSION['USER_ID_GEPI']     = $DB_ROW['user_id_gepi'];
	$_SESSION['ELEVE_CLASSE_ID']  = (int) $DB_ROW['eleve_classe_id'];
	$_SESSION['ELEVE_CLASSE_NOM'] = $DB_ROW['groupe_nom'];
	$_SESSION['ELEVE_LANGUE']     = (int) $DB_ROW['eleve_langue'];
	// Récupérer et Enregistrer en session les données des élèves associées à un resposnable légal.
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
	$tab_type_entier  = array('SESAMATH_ID','DUREE_INACTIVITE','CALCUL_VALEUR_RR','CALCUL_VALEUR_R','CALCUL_VALEUR_V','CALCUL_VALEUR_VV','CALCUL_SEUIL_R','CALCUL_SEUIL_V','CALCUL_LIMITE','CAS_SERVEUR_PORT');
	$tab_type_tableau = array('CSS_BACKGROUND-COLOR','CALCUL_VALEUR','CALCUL_SEUIL','NOTE_TEXTE','NOTE_LEGENDE','ACQUIS_TEXTE','ACQUIS_LEGENDE');
	foreach($DB_TAB as $DB_ROW)
	{
		$parametre_nom = strtoupper($DB_ROW['parametre_nom']);
		// Certains paramètres sont de type entier.
		$parametre_valeur = (in_array($parametre_nom,$tab_type_entier)) ? (int) $DB_ROW['parametre_valeur'] : $DB_ROW['parametre_valeur'] ;
		// Certains paramètres sont à enregistrer sous forme de tableau.
		$find = false;
		foreach($tab_type_tableau as $key1)
		{
			$longueur_key1 = strlen($key1);
			if(substr($parametre_nom,0,$longueur_key1)==$key1)
			{
				$key2 = substr($parametre_nom,$longueur_key1+1);
				$_SESSION[$key1][$key2] = $parametre_valeur ;
				$find = true;
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
	adapter_session_daltonisme() ;
	// Enregistrer en session le CSS personnalisé
	actualiser_style_session();
	// Juste pour davantage de lisibilité si besoin de debug...
	ksort($_SESSION);
}

/**
 * Compléter la session avec les informations de style dépendant du daltonisme + des choix paramétrés au niveau de l'établissement (couleurs, codes de notation).
 * 
 * @param void
 * @return void
 */
function adapter_session_daltonisme()
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
function actualiser_style_session()
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
	$_SESSION['CSS'] .= 'th.v0 , td.v0 {background:'.$_SESSION['BACKGROUND_V0'].'}';
	$_SESSION['CSS'] .= 'th.v1 , td.v1 {background:'.$_SESSION['BACKGROUND_V1'].'}';
	$_SESSION['CSS'] .= 'th.v2 , td.v2 {background:'.$_SESSION['BACKGROUND_V2'].'}';
	$_SESSION['CSS'] .= '#zone_information .v0 {background:'.$_SESSION['BACKGROUND_V0'].';padding:0 1em;margin-right:1ex}';
	$_SESSION['CSS'] .= '#zone_information .v1 {background:'.$_SESSION['BACKGROUND_V1'].';padding:0 1em;margin-right:1ex}';
	$_SESSION['CSS'] .= '#zone_information .v2 {background:'.$_SESSION['BACKGROUND_V2'].';padding:0 1em;margin-right:1ex}';
	$_SESSION['CSS'] .= '#tableau_validation tbody td[lang=lock] {background:'.$_SESSION['BACKGROUND_V1'].' url(./_img/socle/lock.gif) no-repeat center center;} /* surclasse une classe v0 ou v1 ou v2 car défini après */';
	$_SESSION['CSS'] .= '#tableau_validation tbody td[lang=done] {background-image:url(./_img/socle/done.gif);background-repeat:no-repeat;background-position:center center;} /* pas background pour ne pas écraser background-color défini avant */';
}

/**
 * Envoyer un courriel au webmestre.
 * 
 * @param string   $adresse
 * @param string   $objet
 * @param string   $contenu
 * @return bool
 */
function envoyer_webmestre_courriel($adresse,$objet,$contenu)
{
	$param = 'From: '.WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM.' <'.WEBMESTRE_COURRIEL.'>'."\n";
	$param.= 'Reply-To: '.WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM.' <'.WEBMESTRE_COURRIEL.'>'."\n";
	$param.= 'Content-type: text/plain; charset=utf-8'."\r\n"; // \r\n ici et \n avant : http://fr.php.net/manual/fr/function.mail.php#103590
	// Pb avec les accents dans l'entête (sujet, expéditeur...) ; le charset n'a d'effet que sur le corps et les clients de messagerie interprètent différemment le reste (UTF-8 ou ISO-8859-1 etc.).
	// $back=($retour)?'-fwebmestre@sesaprof.net':'';
	// Fonction bridée : 5° paramètre supprimé << Warning: mail(): SAFE MODE Restriction in effect. The fifth parameter is disabled in SAFE MODE.
	$envoi = @mail( $adresse , clean_accents('[SACoche - '.HEBERGEUR_DENOMINATION.'] '.$objet) , $contenu , clean_accents($param) );
	return $envoi ;
}

/**
 * Retourner une liste HTML ordonnée de l'arborescence d'un référentiel matière à partir d'une requête SQL transmise.
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
	$input_all = ($aff_input) ? ' <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." />' : '' ;
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
			$prefixe   = ($reference) ? $DB_ROW['niveau_ref'].' - ' : '' ;
			$tab_niveau[$matiere_id][$niveau_id] = $prefixe.$DB_ROW['niveau_nom'];
		}
		if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
		{
			$domaine_id = $DB_ROW['domaine_id'];
			$prefixe   = ($reference) ? $DB_ROW['domaine_ref'].' - ' : '' ;
			$tab_domaine[$matiere_id][$niveau_id][$domaine_id] = $prefixe.$DB_ROW['domaine_nom'];
		}
		if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
		{
			$theme_id = $DB_ROW['theme_id'];
			$prefixe   = ($reference) ? $DB_ROW['domaine_ref'].$DB_ROW['theme_ordre'].' - ' : '' ;
			$tab_theme[$matiere_id][$niveau_id][$domaine_id][$theme_id] = $prefixe.$DB_ROW['theme_nom'];
		}
		if( (!is_null($DB_ROW['item_id'])) && ($DB_ROW['item_id']!=$item_id) )
		{
			$item_id = $DB_ROW['item_id'];
			if($aff_coef)
			{
				$coef_texte = '<img src="./_img/coef/'.sprintf("%02u",$DB_ROW['item_coef']).'.gif" title="Coefficient '.$DB_ROW['item_coef'].'." /> ';
			}
			if($aff_cart)
			{
				$cart_image = ($DB_ROW['item_cart']) ? 'oui' : 'non' ;
				$cart_title = ($DB_ROW['item_cart']) ? 'Demande possible.' : 'Demande interdite.' ;
				$cart_texte = '<img src="./_img/etat/cart_'.$cart_image.'.png" title="'.$cart_title.'" /> ';
			}
			switch($aff_socle)
			{
				case 'texte' :	$socle_texte = ($DB_ROW['entree_id']) ? '[S] ' : '[–] ';
												break;
				case 'image' :	$socle_image = ($DB_ROW['entree_id']) ? 'oui' : 'non' ;
												$socle_title = ($DB_ROW['entree_id']) ? html($DB_ROW['entree_nom']) : 'Hors-socle.' ;
												$socle_texte = '<img src="./_img/etat/socle_'.$socle_image.'.png" title="'.$socle_title.'" /> ';
			}
			switch($aff_lien)
			{
				case 'click' :	$lien_texte_avant = ($DB_ROW['item_lien']) ? '<a class="lien_ext" href="'.html($DB_ROW['item_lien']).'">' : '';
												$lien_texte_apres = ($DB_ROW['item_lien']) ? '</a>' : '';
				case 'image' :	$lien_image = ($DB_ROW['item_lien']) ? 'oui' : 'non' ;
												$lien_title = ($DB_ROW['item_lien']) ? html($DB_ROW['item_lien']) : 'Absence de ressource.' ;
												$lien_texte = '<img src="./_img/etat/link_'.$lien_image.'.png" title="'.$lien_title.'" /> ';
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
							$retour .= '<li class="li_n1">'.$span_avant.html($domaine_texte).$span_apres.$input_all."\r\n";
							$retour .= '<ul class="ul_n2">'."\r\n";
							if(isset($tab_theme[$matiere_id][$niveau_id][$domaine_id]))
							{
								foreach($tab_theme[$matiere_id][$niveau_id][$domaine_id] as $theme_id => $theme_texte)
								{
									$retour .= '<li class="li_n2">'.$span_avant.html($theme_texte).$span_apres.$input_all."\r\n";
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
 * Retourner une liste HTML ordonnée de l'arborescence d'un référentiel socle à partir d'une requête SQL transmise.
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
 * Fabriquer un export XML d'un référentiel (pour partage sur serveur central) à partir d'une requête SQL transmise.
 * 
 * Remarque : les ordres des domaines / thèmes / items ne sont pas transmis car il sont déduits par leur position dans l'arborescence.
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
 * Équivalent de file_get_contents pour récupérer un fichier sur un serveur distant.
 * 
 * On peut aussi l'utiliser pour récupérer le résultat d'un script PHP exécuté sur un serveur distant.
 * On peut alors envoyer au script des paramètres en POST.
 * 
 * @param string $url
 * @param array  $tab_post   tableau[nom]=>valeur de données à envoyer en POST (facultatif)
 * @param int    $timeout    valeur du timeout en s ; facultatif, par défaut 5 ; pour l'interrogation du LDAP (Bx) je suis obligé de monter à 30
 * @return string
 */
function url_get_contents($url,$tab_post=false,$timeout=5)
{
	// Ne pas utiliser file_get_contents() car certains serveurs n'accepent pas d'utiliser une URL comme nom de fichier (gestionnaire fopen non activé).
	// On utilise donc la bibliothèque cURL en remplacement
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 3600); // Le temps en seconde que cURL doit conserver les entrées DNS en mémoire. Cette option est définie à 120 secondes (2 minutes) par défaut.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);    // TRUE retourne directement le transfert sous forme de chaîne de la valeur retournée par curl_exec() au lieu de l'afficher directement.
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);   // FALSE pour que cURL ne vérifie pas le certificat (sinon, en l'absence de certificat, on récolte l'erreur "SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed").
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);       // TRUE pour que PHP traite silencieusement les codes HTTP supérieurs ou égaux à 400. Le comportement par défaut est de retourner la page normalement, en ignorant ce code.
	curl_setopt($ch, CURLOPT_HEADER, FALSE);           // FALSE pour ne pas inclure l'en-tête dans la valeur de retour.
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);       // Le temps maximum d'exécution de la fonction cURL (en s) ; éviter de monter cette valeur pour libérer des ressources plus rapidement : 'classiquement', le serveur doit répondre en qq ms, donc si au bout de 5s il a pas répondu c'est qu'il ne répondra plus, alors pas la peine de bloquer une connexion et de la RAM pendant plus longtemps.
	curl_setopt($ch, CURLOPT_URL, $url);               // L'URL à récupérer. Vous pouvez aussi choisir cette valeur lors de l'appel à curl_init().
	if( (!ini_get('safe_mode')) && (!ini_get('open_basedir')) )
	{                                                 // Option CURLOPT_FOLLOWLOCATION sous conditions car certaines installations renvoient "CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set" (http://www.php.net/manual/fr/features.safe-mode.functions.php#92192)
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // TRUE pour suivre toutes les en-têtes "Location: " que le serveur envoie dans les en-têtes HTTP (notez que cette fonction est récursive et que PHP suivra toutes les en-têtes "Location: " qu'il trouvera à moins que CURLOPT_MAXREDIRS ne soit définie).
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);         // Le nombre maximal de redirections HTTP à suivre. Utilisez cette option avec l'option CURLOPT_FOLLOWLOCATION.
	}
	if( (defined('SERVEUR_PROXY_USED')) && (SERVEUR_PROXY_USED) )
	{                                                                    // Serveur qui nécessite d'utiliser un tunnel à travers un proxy HTTP.
		curl_setopt($ch, CURLOPT_PROXY, SERVEUR_PROXY_NAME);               // Le nom du proxy HTTP au tunnel qui le demande.
		curl_setopt($ch, CURLOPT_PROXYPORT, (int)SERVEUR_PROXY_PORT);      // Le numéro du port du proxy à utiliser pour la connexion. Ce numéro de port peut également être défini dans l'option CURLOPT_PROXY.
		curl_setopt($ch, CURLOPT_PROXYTYPE, constant(SERVEUR_PROXY_TYPE)); // Soit CURLPROXY_HTTP (par défaut), soit CURLPROXY_SOCKS5.
		if(SERVEUR_PROXY_AUTH_USED)
		{                                                                                              // Serveur qui nécessite de s'authentifier pour utiliser le proxy.
			curl_setopt($ch, CURLOPT_PROXYAUTH, constant(SERVEUR_PROXY_AUTH_METHOD));                    // La méthode d'identification HTTP à utiliser pour la connexion à un proxy. Utilisez la même méthode que celle décrite dans CURLOPT_HTTPAUTH. Pour une identification avec un proxy, seuls CURLAUTH_BASIC et CURLAUTH_NTLM sont actuellement supportés.
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, SERVEUR_PROXY_AUTH_USER.':'.SERVEUR_PROXY_AUTH_PASS); // Un nom d'utilisateur et un mot de passe formatés sous la forme "[username]:[password]" à utiliser pour la connexion avec le proxy.
		}
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
 * Liste le contenu d'un dossier (fichiers et dossiers).
 * 
 * @param string   $dossier
 * @return array
 */
function Lister_Contenu_Dossier($dossier)
{
	return array_diff( scandir($dossier) , array('.','..') );
}

/**
 * Liste les noms des fichiers contenus dans un dossier, sans le contenu temporaire ou personnel.
 * 
 * @param string   $dossier
 * @return array
 */
function Lister_Contenu_Dossier_Programme($dossier)
{
	return array_diff( scandir($dossier) , array('.','..','__private','__tmp','webservices','.svn') );
}

/**
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
		return TRUE;
	}
	@umask(0000); // Met le chmod à 666 - 000 = 666 pour les fichiers prochains fichiers créés (et à 777 - 000 = 777 pour les dossiers).
	$test = @mkdir($dossier);
	// Le dossier a-t-il bien été créé ?
	if(!$test)
	{
		$affichage .= '<label for="rien" class="erreur">Echec lors de la création du dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; : veuillez le créer manuellement.</label><br />'."\r\n";
		return FALSE;
	}
	$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; créé.</label><br />'."\r\n";
	// Le dossier est-il accessible en écriture ?
	$test = is_writable($dossier);
	if(!$test)
	{
		$affichage .= '<label for="rien" class="erreur">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; inaccessible en écriture : veuillez en changer les droits manuellement.</label><br />'."\r\n";
		return FALSE;
	}
	// Si on arrive là, c'est bon...
	$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; accessible en écriture.</label><br />'."\r\n";
	return TRUE;
}

/**
 * Vider un dossier ne contenant que d'éventuels fichiers.
 * 
 * @param string   $dossier
 * @return void
 */
function Vider_Dossier($dossier)
{
	if(is_dir($dossier))
	{
		$tab_fichier = Lister_Contenu_Dossier($dossier);
		foreach($tab_fichier as $fichier_nom)
		{
			unlink($dossier.'/'.$fichier_nom);
		}
	}
}

/**
 * Créer un dossier s'il n'existe pas, le vider de ses éventuels fichiers sinon.
 * 
 * @param string   $dossier
 * @return void
 */
function Creer_ou_Vider_Dossier($dossier)
{
	if(!is_dir($dossier))
	{
		Creer_Dossier($dossier);
	}
	else
	{
		Vider_Dossier($dossier);
	}
}

/**
 * Supprimer un dossier, après avoir effacé récursivement son contenu.
 * 
 * @param string   $dossier
 * @return void
 */
function Supprimer_Dossier($dossier)
{
	if(is_dir($dossier))
	{
		$tab_contenu = Lister_Contenu_Dossier($dossier);
		foreach($tab_contenu as $contenu)
		{
			$chemin_contenu = $dossier.'/'.$contenu;
			if(is_dir($chemin_contenu))
			{
				Supprimer_Dossier($chemin_contenu);
			}
			else
			{
				unlink($chemin_contenu);
			}
		}
		rmdir($dossier);
	}
}

/**
 * Recense récursivement les dossiers présents et les md5 des fichiers (utilisé pour la maj automatique par le webmestre).
 * 
 * @param string   $dossier
 * @param int      $longueur_prefixe   longueur de $dossier lors du premier appel
 * @param string   $indice   "avant" ou "apres"
 * @return void
 */
function Analyser_Dossier($dossier,$longueur_prefixe,$indice)
{
	$tab_contenu = Lister_Contenu_Dossier_Programme($dossier);
	foreach($tab_contenu as $contenu)
	{
		$chemin_contenu = $dossier.'/'.$contenu;
		if(is_dir($chemin_contenu))
		{
			Analyser_Dossier($chemin_contenu,$longueur_prefixe,$indice);
		}
		else
		{
			$_SESSION['tmp']['fichier'][substr($chemin_contenu,$longueur_prefixe)][$indice] = md5_file($chemin_contenu);
		}
	}
	$_SESSION['tmp']['dossier'][substr($dossier,$longueur_prefixe)][$indice] = TRUE;
}

/**
 * Ecrire du contenu dans un fichier, exit() en cas d'erreur
 * 
 * @param string   $fichier_chemin
 * @param string   $fichier_contenu
 * @param int      facultatif ; si constante FILE_APPEND envoyée, alors ajoute en fin de fichier au lieu d'écraser le contenu
 * @return void
 */
function Ecrire_Fichier($fichier_chemin,$fichier_contenu,$file_append=0)
{
	@umask(0000); // Met le chmod à 666 - 000 = 666 pour les fichiers prochains fichiers créés (et à 777 - 000 = 777 pour les dossiers).
	$test_ecriture = @file_put_contents($fichier_chemin,$fichier_contenu,$file_append);
	if($test_ecriture===false)
	{
		exit('Erreur : problème lors de l\'écriture du fichier '.$fichier_chemin.' !');
	}
}

/**
 * Dezipper un fichier contenant un ensemble de fichiers dans un dossier, avec son arborescence.
 * 
 * Inspiré de http://fr.php.net/manual/fr/ref.zip.php#79057
 * A l'origine pour remplacer $zip = new ZipArchive(); $result_open = $zip->open($fichier_import); qui plante sur le serveur Nantais s'il y a trop de fichiers dans le zip (code erreur "5 READ").
 * Mais il s'avère finalement que ça ne fonctionne pas mieux...
 * 
 * @param string   $fichier_zip
 * @param string   $dossier_dezip   sans le slash final
 * @return bool    $use_ZipArchive
 * @return int     code d'erreur (0 si RAS)
 */
function unzip($fichier_zip,$dossier_dezip,$use_ZipArchive)
{
	// Utiliser la classe ZipArchive http://fr.php.net/manual/fr/class.ziparchive.php (PHP 5 >= 5.2.0, PECL zip >= 1.1.0)
	if($use_ZipArchive)
	{
		$zip = new ZipArchive();
		$result_open = $zip->open($fichier_zip);
		if($result_open!==true)
		{
			return $result_open;
		}
		$zip->extractTo($dossier_dezip);
		$zip->close();
	}
	// Utiliser les fonctions Zip http://fr.php.net/manual/fr/ref.zip.php (PHP 4 >= 4.1.0, PHP 5 >= 5.2.0, PECL zip >= 1.0.0)
	else
	{
		$dossier_dezip .= '/';
		$contenu_zip = zip_open($fichier_zip);
		if(!is_resource($contenu_zip))
		{
			return $contenu_zip;
		}
		while( $zip_element = zip_read($contenu_zip) )
		{
			zip_entry_open($contenu_zip, $zip_element);
			if (substr(zip_entry_name($zip_element), -1) == '/')
			{
				// C'est un dossier
				mkdir( $dossier_dezip.substr(zip_entry_name($zip_element), 0, -1) );
			}
			else
			{
				// C'est un fichier
				file_put_contents( $dossier_dezip.zip_entry_name($zip_element) , zip_entry_read($zip_element,zip_entry_filesize($zip_element)) );
			}
			zip_entry_close($zip_element);
		}
		zip_close($contenu_zip);
	}
	// Tout c'est bien passé
	return 0;
}

/**
 * Retourne le chemin du fichier RSS d'un prof ; s'il n'existe pas, en créer un vierge (pour recueillir les demandes d'évaluations des élèves).
 * 
 * @param int     $prof_id
 * @return string
 */
function adresse_RSS($prof_id)
{
	// Si le dossier n'existe pas, on le créé (possible car au début tous les RSS des établissements étaient dans un même dossier commun).
	$dossier_nom = './__tmp/rss/'.$_SESSION['BASE'];
	if(!is_dir($dossier_nom))
	{
		Creer_Dossier($dossier_nom);
		Ecrire_Fichier($dossier_nom.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
	}
	// Le nom du RSS est tordu pour le rendre un minimum privé ; il peut être retrouvé, mais très difficilement, par un bidouilleur qui met le nez dans le code, mais il n'y a rien de confidentiel non plus.
	$fichier_nom_debut = 'rss_'.$prof_id;
	$fichier_nom_fin   = md5($fichier_nom_debut.$_SERVER['DOCUMENT_ROOT']);
	$fichier_chemin    = $dossier_nom.'/'.$fichier_nom_debut.'_'.$fichier_nom_fin.'.xml';
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
	$alphabet = "ABCDEFGHJKLMNPRSTUVWXYZ";
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

?>