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

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$step = (isset($_POST['f_step'])) ? clean_entier($_POST['f_step']) : '';
$affichage = '';
$filename_php = './__mysql_config/serveur_sacoche.php';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 1 - Création de dossiers supplémentaires et de leurs droits
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( $step==1 )
{
	$poursuivre = true;
	// Fonction utilisée pour tester l'existence d'un dossier, le créer, tester son accès en écriture
	function creer_dossier($dossier)
	{
		global $affichage,$poursuivre;
		if(is_dir($dossier))
		{
			$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; déjà en place.</label><br />'."\r\n";
		}
		else
		{
			$test = @mkdir($dossier);
			if($test)
			{
				$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; créé.</label><br />'."\r\n";
				$test = is_writable($dossier);
				if($test)
				{
					$affichage .= '<label for="rien" class="valide">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; accessible en écriture.</label><br />'."\r\n";
				}
				else
				{
					$affichage .= '<label for="rien" class="erreur">Dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; inaccessible en écriture : veuillez en changer les droits manuellement.</label><br />'."\r\n";
					$poursuivre = false;
				}
			}
			else
			{
				$affichage .= '<label for="rien" class="erreur">Echec lors de la création du dossier &laquo;&nbsp;<b>'.$dossier.'</b>&nbsp;&raquo; : veuillez le créer manuellement.</label><br />'."\r\n";
				$poursuivre = false;
			}
		}
	}
	// Création des trois dossiers principaux, et vérification de leur accès en écriture
	$tab_dossier = array('./__hebergement_info','./__mysql_config','./__tmp');
	foreach($tab_dossier as $dossier)
	{
		creer_dossier($dossier);
	}
	// Création des sous-dossiers, et vérification de leur accès en éciture
	if($poursuivre)
	{
		$tab_dossier = array('./__tmp/badge','./__tmp/cookie','./__tmp/dump-base','./__tmp/export','./__tmp/import','./__tmp/login-mdp','./__tmp/rss');
		foreach($tab_dossier as $dossier)
		{
			creer_dossier($dossier);
		}
	}
	// Affichage du résultat des opérations
	echo $affichage;
	echo ($poursuivre) ? '<p><span class="tab"><a href="#" class="step2">Passer à l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></span></p>' : '<p><span class="tab"><a href="#" class="step1">Reprendre l\'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 2 - Remplissage de ces dossiers avec le contenu approprié
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==2 )
{
	// Création des fichiers index.htm
	$poursuivre1 = true;
	$tab_dossier = array('./__hebergement_info','./__mysql_config','./__tmp','./__tmp/badge','./__tmp/cookie','./__tmp/dump-base','./__tmp/export','./__tmp/import','./__tmp/login-mdp','./__tmp/rss');
	foreach($tab_dossier as $dossier)
	{
		$test = @file_put_contents($dossier.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
		$poursuivre1 = (!$test) ? false : $poursuivre1 ;
	}
	if($poursuivre1)
	{
		$affichage .= '<label for="rien" class="valide">Fichiers &laquo;&nbsp;<b>index.htm</b>&nbsp;&raquo; créés dans chaque dossier.</label><br />'."\r\n";
	}
	else
	{
		$affichage .= '<label for="rien" class="erreur">Echec lors de la création d\'un ou plusieurs fichiers &laquo;&nbsp;<b>index.htm</b>&nbsp;&raquo; dans chaque dossier précédent.</label><br />'."\r\n";
	}
	// Création des fichiers .htaccess
	$poursuivre2 = true;
	$tab_dossier = array('./__hebergement_info','./__mysql_config');
	foreach($tab_dossier as $dossier)
	{
		$test = @file_put_contents($dossier.'/.htaccess','Order deny,allow'."\r\n".'allow from 127.0.0.1'."\r\n".'deny from all'."\r\n");
		$poursuivre2 = (!$test) ? false : $poursuivre2 ;
	}
	if($poursuivre2)
	{
		$affichage .= '<label for="rien" class="valide">Fichiers &laquo;&nbsp;<b>.htaccess</b>&nbsp;&raquo; créés dans les dossiers &laquo;&nbsp;<b>./__hebergement_info</b>&nbsp;&raquo; et &laquo;&nbsp;<b>./__mysql_config</b>&nbsp;&raquo;.</label><br />'."\r\n";
	}
	else
	{
		$affichage .= '<label for="rien" class="erreur">Echec lors de la création du fichier &laquo;&nbsp;<b>.htaccess</b>&nbsp;&raquo; dans les dossiers &laquo;&nbsp;<b>./__hebergement_info</b>&nbsp;&raquo; et &laquo;&nbsp;<b>./__mysql_config</b>&nbsp;&raquo;.</label><br />Veuiller y recopier ceui se trouvant par exemple dans le dossier <b>./_inc</b>.'."\r\n";
	}
	// Affichage du résultat des opérations
	echo $affichage;
	echo ($poursuivre1 && $poursuivre2) ? '<p><span class="tab"><a href="#" class="step3">Passer à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></span></p>' : '<p><span class="tab"><a href="#" class="step1">Reprendre l\'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 3 - Choix du type d'installation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==3 )
{
	if( defined('HEBERGEUR_INSTALLATION') && defined('HEBERGEUR_DENOMINATION') && defined('HEBERGEUR_UAI') && defined('HEBERGEUR_ADRESSE_SITE') && defined('HEBERGEUR_LOGO') && defined('HEBERGEUR_CNIL') && defined('WEBMESTRE_NOM') && defined('WEBMESTRE_PRENOM') && defined('WEBMESTRE_COURRIEL') && defined('WEBMESTRE_PASSWORD_MD5') )
	{
		$affichage .= '<p><label for="rien" class="valide">Les informations concernant le type d\'installation, l\'hébergement et le webmestre sont déjà renseignées.</label></p>'."\r\n";
		$affichage .= '<p><span class="tab"><a href="#" class="step5">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
	}
	else
	{
		$affichage .= '<p><label for="rien" class="alerte">Le fichier &laquo;&nbsp;<b>'.$fichier_constantes.'</b>&nbsp;&raquo; n\'existant pas ou étant corrompu, vous devez renseigner les étapes 3 et 4.</label></p>'."\r\n";
		$affichage .= '<h2>Type d\'installation</h2>'."\r\n";
		$affichage .= '<p class="astuce">Le type d\'installation, déterminant, n\'est pas modifiable ultérieurement : sélectionnez ce qui vous correspond vraiment !</p>'."\r\n";
		$affichage .= '<ul class="puce"><li><a href="#" class="step4" id="mono-structure">Installation d\'un unique établissement sur ce serveur, nécessitant une seule base de données.</a></li></ul>'."\r\n";
		$affichage .= '<div class="danger">La base MySQL à utiliser doit déjà exister (la créer maintenant si nécessaire, typiquement via "phpMyAdmin").</div>'."\r\n";
		$affichage .= '<p />'."\r\n";
		$affichage .= '<ul class="puce"><li><a href="#" class="step4" id="multi-structures">Gestion d\'établissements multiples (par un rectorat...) avec autant de bases de données associées.</a></li></ul>'."\r\n";
		$affichage .= '<div class="danger">Il faut disposer d\'un compte MySQL avec des droits d\'administration de bases et d\'utilisateurs (création, suppression).</div>'."\r\n";
		$affichage .= '<p><span class="tab"><label id="ajax_msg">&nbsp;</label></span></p>'."\r\n";
	}
	echo $affichage;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 4 - Informations concernant l'hébergement et le webmestre
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==4 )
{
	// récupérer et teste le paramètre
	$installation = (isset($_POST['f_installation'])) ? clean_texte($_POST['f_installation']) : '';
	if( in_array($installation,array('mono-structure','multi-structures')) )
	{
		$exemple_denomination = ($installation=='mono-structure') ? 'Collège de Trucville' : 'Rectorat du paradis' ;
		$exemple_adresse_web  = ($installation=='mono-structure') ? 'http://www.college-trucville.com' : 'http://www.ac-paradis.fr' ;
		$uai_div_hide_avant   = ($installation=='mono-structure') ? '' : '<div class="hide">' ;
		$uai_div_hide_apres   = ($installation=='mono-structure') ? '' : '</div>' ;
		$affichage .= '<fieldset>'."\r\n";
		$affichage .= '<h2>Caractéristiques de l\'hébergement</h2>'."\r\n";
		$affichage .= '<label class="tab" for="f_installation">Installation <img alt="" src="./_img/bulle_aide.png" title="Valeur déjà renseignée." /> :</label><input id="f_installation" name="f_installation" size="18" type="text" value="'.$installation.'" readonly="readonly" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_denomination">Dénomination <img alt="" src="./_img/bulle_aide.png" title="Exemple : '.$exemple_denomination.'" /> :</label><input id="f_denomination" name="f_denomination" size="55" type="text" value="" /><br />'."\r\n";
		$affichage .= $uai_div_hide_avant.'<label class="tab" for="f_uai">n° UAI (ex-RNE) <img alt="" src="./_img/bulle_aide.png" title="Ce champ est facultatif." /> :</label><input id="f_uai" name="f_uai" size="8" type="text" value="" /><br />'.$uai_div_hide_apres."\r\n";
		$affichage .= '<label class="tab" for="f_adresse_site">Adresse web <img alt="" src="./_img/bulle_aide.png" title="Exemple : '.$exemple_adresse_web.'<br />Ce champ est facultatif." /> :</label><input id="f_adresse_site" name="f_adresse_site" size="60" type="text" value="" /><br />'."\r\n";
		$affichage .= '<h2>Coordonnées du webmestre</h2>'."\r\n";
		$affichage .= '<label class="tab" for="f_nom">Nom :</label><input id="f_nom" name="f_nom" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_prenom">Prénom :</label><input id="f_prenom" name="f_prenom" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_courriel">Courriel :</label><input id="f_courriel" name="f_courriel" size="60" type="text" value="" /><br />'."\r\n";
		$affichage .= '<h2>Mot de passe du webmestre</h2>'."\r\n";
		$affichage .= '<div class="astuce">Ce mot de passe doit être complexe pour offrir un niveau de sécurité suffisant !</div>'."\r\n";
		$affichage .= '<label class="tab" for="f_password1">Saisie 1/2 :</label><input id="f_password1" name="f_password1" size="20" type="password" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_password2">Saisie 2/2 :</label><input id="f_password2" name="f_password2" size="20" type="password" value="" /><p />'."\r\n";
		$affichage .= '<span class="tab"></span><input id="f_step" name="f_step" type="hidden" value="41" /><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label>'."\r\n";
		$affichage .= '</fieldset>'."\r\n";
		echo $affichage;
	}
	else
	{
		exit('Erreur avec les données transmises !');
	}
}

elseif( $step==41 )
{
	// récupérer et tester les paramètres
	$installation = (isset($_POST['f_installation'])) ? clean_texte($_POST['f_installation']) : '';
	$denomination = (isset($_POST['f_denomination'])) ? clean_texte($_POST['f_denomination']) : '';
	$uai          = (isset($_POST['f_uai']))          ? clean_uai($_POST['f_uai'])            : '';
	$adresse_site = (isset($_POST['f_adresse_site'])) ? clean_url($_POST['f_adresse_site'])   : '';
	$nom          = (isset($_POST['f_nom']))          ? clean_nom($_POST['f_nom'])            : '';
	$prenom       = (isset($_POST['f_prenom']))       ? clean_prenom($_POST['f_prenom'])      : '';
	$courriel     = (isset($_POST['f_courriel']))     ? clean_courriel($_POST['f_courriel'])  : '';
	$password     = (isset($_POST['f_password1']))    ? clean_password($_POST['f_password1']) : '';
	if( in_array($installation,array('mono-structure','multi-structures')) && $denomination && $nom && $prenom && $courriel && $password )
	{
		fabriquer_fichier_hebergeur_info($installation,$denomination,$uai,$adresse_site,$logo='',$cnil='non renseignée',$nom,$prenom,$courriel,crypter_mdp($password));
		$affichage .= '<p><label for="rien" class="valide">Les informations concernant le webmestre et l\'hébergement sont maintenant renseignées.</label></p>'."\r\n";
		$affichage .= '<div class="astuce">Vous pourrez les modifier depuis l\'espace du webmestre, en particulier ajouter un logo et un numéro de déclaration à la CNIL.</div>'."\r\n";
		$affichage .= '<p><span class="tab"><a href="#" class="step5">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
		echo $affichage;
	}
	else
	{
		exit('Erreur avec les données transmises !');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 5 - Indication des paramètres de connexion MySQL
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==5 )
{
	// A ce niveau, le fichier d'informations sur l'hébergement doit exister.
	if(!defined('HEBERGEUR_INSTALLATION'))
	{
		$affichage .= '<label for="rien" class="valide">Les données du fichier <b>'.$fichier_constantes.'</b> n\'ont pas été correctement chargées.</label>'."\r\n";
		$affichage .= '<p><span class="tab"><a href="#" class="step3">Retour à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
	}
	elseif(is_file($fichier_mysql_config))
	{
		$affichage .= '<p><label for="rien" class="valide">Le fichier <b>'.$fichier_mysql_config.'</b> existe déjà ; modifiez-en manuellement le contenu si les paramètres sont incorrects.</label></p>'."\r\n";
		$affichage .= '<p><span class="tab"><a href="#" class="step6">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
	}
	else
	{
		// afficher le formulaire pour entrer les paramètres
		$texte_alerte = (HEBERGEUR_INSTALLATION=='multi-structures') ? 'ce compte mysql doit avoir des droits d\'administration de bases et d\'utilisateurs (utilisateur "root" typique)' : 'la base à utiliser doit déjà exister (elle ne sera pas créée par SACoche) ; veuillez la créer manuellement maintenant si besoin' ;
		$affichage .= '<p><label for="rien" class="alerte">Le fichier &laquo;&nbsp;<b>'.$fichier_mysql_config.'</b>&nbsp;&raquo; n\'existant pas, indiquez ci-dessous vos paramètres de connexion à la base de données.</label></p>'."\r\n";
		$affichage .= '<p class="danger">Comme indiqué précédemment, '.$texte_alerte.'.</p>'."\r\n";
		$affichage .= '<fieldset>'."\r\n";
		$affichage .= '<h2>Paramètres MySQL</h2>'."\r\n";
		$affichage .= '<label class="tab" for="f_host"><img alt="" src="./_img/bulle_aide.png" title="Parfois \'localhost\' sur un serveur que l\'on administre." /> Nom du serveur :</label><input id="f_host" name="f_host" size="20" type="text" value="" /><br />'."\r\n";
//		$affichage .= '<label class="tab" for="f_name">Nom de la base :</label><input id="f_name" name="f_name" size="20" type="hidden" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_user">Nom d\'utilisateur :</label><input id="f_user" name="f_user" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_pass">Mot de passe :</label><input id="f_pass" name="f_pass" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<span class="tab"></span><input id="f_name" name="f_name" size="20" type="hidden" value="remplissage bidon" /><input id="f_step" name="f_step" type="hidden" value="51" /><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label>'."\r\n";
		$affichage .= '</fieldset>'."\r\n";
	}
	echo $affichage;
}

elseif( $step==51 )
{
	// A ce niveau, le fichier d'informations sur l'hébergement doit exister.
	if(!defined('HEBERGEUR_INSTALLATION'))
	{
		exit('Erreur : problème avec le fichier : '.$fichier_constantes.' !');
	}
	// récupérer et tester les paramètres
	$BD_host = (isset($_POST['f_host'])) ? clean_texte($_POST['f_host']) : '';
	$BD_name = (isset($_POST['f_name'])) ? clean_texte($_POST['f_name']) : '';
	$BD_user = (isset($_POST['f_user'])) ? clean_texte($_POST['f_user']) : '';
	$BD_pass = (isset($_POST['f_pass'])) ? clean_texte($_POST['f_pass']) : '';
	// tester la connexion
	$BDlink = @mysql_connect($BD_host,$BD_user,$BD_pass);
	if(!$BDlink)
	{
		exit('Erreur : impossible de se connecter à MySQL ["'.html(trim(mysql_error())).'"] !');
	}
	// vérifier la version de MySQL
	$res = @mysql_query('SELECT VERSION()');
	$row = mysql_fetch_row($res);
	$mysql_version = (float)substr($row[0],0,3);
	if($mysql_version<$version_mysql_mini)
	{
		exit('Erreur : MySQL trop ancien (version utilisée '.$mysql_version.' ; version minimum requise '.$version_mysql_mini.') !');
	}
	if(HEBERGEUR_INSTALLATION=='multi-structures')
	{
		// Vérifier que ce compte a les droits suffisants
		// Réponses typiques :
		// GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION
		// GRANT USAGE ON *.* TO 'sql_user_...'@'%' IDENTIFIED BY PASSWORD '...'
		$res = @mysql_query('SHOW GRANTS FOR CURRENT_USER()');
		$row = mysql_fetch_row($res);
		if( (strpos($row[0],'ALL PRIVILEGES')==false) && (strpos($row[0],'WITH GRANT OPTION')==false) )
		{
			exit('Erreur : ce compte MySQL n\'a pas les droits suffisants !');
		}
		// Créer la base de données du webmestre, si elle n'existe pas déjà
		$BD_name = 'sacoche_webmestre';
		$bool = @mysql_select_db($BD_name,$BDlink);
		if(!$bool)
		{
			$res = @mysql_query('CREATE DATABASE '.$BD_name.' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci');
			if(!$bool && !$res)
			{
				exit('Erreur : impossible d\'accéder et de créer la base "sacoche_webmestre" ["'.html(trim(mysql_error())).'"] !');
			}
		}
		// Créer le fichier de connexion de la base de données du webmestre, installation multi-structures
		fabriquer_fichier_connexion_base(0,$BD_host,$BD_name,$BD_user,$BD_pass);
		$affichage .= '<p><label for="rien" class="valide">Les paramètres de connexion MySQL, testés avec succès, sont maintenant enregistrés.</label></p>'."\r\n";
		$affichage .= '<p><span class="tab"><a href="#" class="step6">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
	}
	elseif(HEBERGEUR_INSTALLATION=='mono-structure')
	{
		$res = @mysql_query('SHOW DATABASES');
		$num = mysql_num_rows($res);
		if(!$num)
		{
			exit('Erreur : aucune base de données existante ne semble accessible à cet utilisateur !');
		}
		$tab_tables = array();
		while($row = mysql_fetch_row($res))
		{
			if( ($row[0]!='mysql') && ($row[0]!='information_schema') )
			{
				$tab_tables[] = $row[0];
			}
		}
		if(!count($tab_tables))
		{
			exit('Erreur : aucune base de données existante ne semble accessible à cet utilisateur !');
		}
		// afficher le formulaire pour choisir le nom de la base
		$options = '<option value=""></option>';
		foreach($tab_tables as $table)
		{
			$options .= '<option value="'.html($table).'">'.html($table).'</option>';
		}
		$affichage .= '<fieldset>'."\r\n";
		$affichage .= '<p><label for="rien" class="valide">Les paramètres de connexion MySQL ont été testés avec succès.</label></p>'."\r\n";
		$affichage .= '<h2>Base à utiliser</h2>'."\r\n";
		$affichage .= '<label class="tab" for="f_name">Nom de la base :</label><select id="f_name" name="f_name">'.$options.'</select><br />'."\r\n";
		$affichage .= '<span class="tab"></span><input id="f_host" name="f_host" size="20" type="hidden" value="'.html($BD_host).'" /><input id="f_user" name="f_user" size="20" type="hidden" value="'.html($BD_user).'" /><input id="f_pass" name="f_pass" size="20" type="hidden" value="'.html($BD_pass).'" /><input id="f_step" name="f_step" type="hidden" value="42" /><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label>'."\r\n";
		$affichage .= '</fieldset>'."\r\n";
	}
	echo $affichage;
}

elseif( $step==42 )
{
	// A ce niveau, le fichier d'informations sur l'hébergement doit exister.
	if(!defined('HEBERGEUR_INSTALLATION'))
	{
		exit('Erreur : problème avec le fichier : '.$fichier_constantes.' !');
	}
	// récupérer et tester les paramètres
	$BD_host = (isset($_POST['f_host'])) ? clean_texte($_POST['f_host']) : '';
	$BD_name = (isset($_POST['f_name'])) ? clean_texte($_POST['f_name']) : '';
	$BD_user = (isset($_POST['f_user'])) ? clean_texte($_POST['f_user']) : '';
	$BD_pass = (isset($_POST['f_pass'])) ? clean_texte($_POST['f_pass']) : '';
	// tester la connexion
	$BDlink = @mysql_connect($BD_host,$BD_user,$BD_pass);
	if(!$BDlink)
	{
		exit('Erreur : impossible de se connecter à MySQL ["'.html(trim(mysql_error())).'"] !');
	}
	if(HEBERGEUR_INSTALLATION!='mono-structure')
	{
		exit('Erreur : cette étape est réservée au choix d\'un unique établissement !');
	}
	// Sélectionner la base de données de la structure
	$bool = @mysql_select_db($BD_name,$BDlink);
	if(!$bool)
	{
		exit('Erreur : impossible d\'accéder à la base "'.html($BD_name).'" ["'.html(trim(mysql_error())).'"] !');
	}
	// Créer le fichier de connexion de la base de données du webmestre, installation multi-structures
	fabriquer_fichier_connexion_base(0,$BD_host,$BD_name,$BD_user,$BD_pass);
	$affichage .= '<p><label for="rien" class="valide">Les paramètres de connexion MySQL sont maintenant enregistrés.</label></p>'."\r\n";
	$affichage .= '<p><span class="tab"><a href="#" class="step6">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
	echo $affichage;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 6 - Installation des tables de la base de données
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==6 )
{
	// A ce niveau, le fichier d'informations sur l'hébergement doit exister.
	if(!is_file($fichier_constantes))
	{
		exit('Erreur : problème avec le fichier : '.$fichier_constantes.' !');
	}
	// A ce niveau, le fichier de connexion à la base de données doit exister.
	if(!is_file($fichier_mysql_config))
	{
		exit('Erreur : problème avec le fichier : '.$fichier_mysql_config.' !');
	}
	// On cherche d'éventuelles tables existantes de SACoche.
	$DB_TAB = (HEBERGEUR_INSTALLATION=='mono-structure') ? DB::queryTab(SACOCHE_STRUCTURE_BD_NAME,'SHOW TABLE STATUS LIKE "sacoche_%"') : DB::queryTab(SACOCHE_WEBMESTRE_BD_NAME,'SHOW TABLE STATUS LIKE "sacoche_%"') ;
	$nb_tables_presentes = count($DB_TAB);
	if($nb_tables_presentes)
	{
		$s = ($nb_tables_presentes>1) ? 's' : '' ;
		$affichage .= '<p><label class="alerte">'.$nb_tables_presentes.' table'.$s.' de SACoche étant déjà présente'.$s.', les tables n\'ont pas été installées.</label></p>'."\r\n";
		$affichage .= '<p class="astuce">Si besoin, supprimez toutes les tables concernées manuellement, puis <a href="#" class="step6">relancer l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></p>'."\r\n";
		$affichage .= '<hr />'."\r\n";
		$affichage .= '<h2>Installation logicielle terminée</h2>'."\r\n";
		$affichage .= '<p>Pour se connecter avec le compte webmestre : <a href="'.SERVEUR_ADRESSE.'?webmestre">'.SERVEUR_ADRESSE.'?webmestre</a></p>'."\r\n";
	}
	else
	{
		if(HEBERGEUR_INSTALLATION=='mono-structure')
		{
			DB_creer_remplir_tables_structure();
			// Personnaliser certains paramètres de la structure
			$tab_parametres = array();
			$tab_parametres['version_base'] = VERSION_BASE;
			$tab_parametres['uai']          = HEBERGEUR_UAI;
			$tab_parametres['denomination'] = HEBERGEUR_DENOMINATION;
			DB_modifier_parametres($tab_parametres);
			// Insérer un compte administrateur dans la base de la structure
			$password = fabriquer_mdp();
			$user_id = DB_ajouter_utilisateur($num_sconet=0,$reference='','administrateur',WEBMESTRE_NOM,WEBMESTRE_PRENOM,$login='admin',$password,$classe_id=0,$id_ent='',$id_gepi='');
			$affichage .= '<p><label class="valide">Les tables de la base de données ont été installées.</label></p>'."\r\n";
			$affichage .= '<span class="astuce">Le premier compte administrateur a été créé avec votre identité :</span>'."\r\n";
			$affichage .= '<ul class="puce">';
			$affichage .= '<li>nom d\'utilisateur " admin "</li>';
			$affichage .= '<li>mot de passe " '.$password.' "</li>';
			$affichage .= '</ul>'."\r\n";
			$affichage .= '<label class="alerte">Notez ces identifiants avant de poursuivre !</label>'."\r\n";
			$affichage .= '<hr />'."\r\n";
			$affichage .= '<h2>Installation logicielle terminée</h2>'."\r\n";
			$affichage .= '<p>Se connecter avec le compte webmestre : <a href="'.SERVEUR_ADRESSE.'?webmestre">'.SERVEUR_ADRESSE.'?webmestre</a></p>'."\r\n";
			$affichage .= '<p>Se connecter avec le compte administrateur : <a href="'.SERVEUR_ADRESSE.'?admin">'.SERVEUR_ADRESSE.'?admin</a></p>'."\r\n";
		}
		elseif(HEBERGEUR_INSTALLATION=='multi-structures')
		{
			DB_creer_remplir_tables_webmestre();
			$affichage .= '<p><label class="valide">Les tables de la base de données du webmestre ont été installées.</label></p>'."\r\n";
			$affichage .= '<hr />'."\r\n";
			$affichage .= '<h2>Installation logicielle terminée</h2>'."\r\n";
			$affichage .= '<p>Se connecter avec le compte webmestre pour gérer les structures hébergées : <a href="'.SERVEUR_ADRESSE.'?webmestre">'.SERVEUR_ADRESSE.'?webmestre</a></p>'."\r\n";
		}
	}
	echo $affichage;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
