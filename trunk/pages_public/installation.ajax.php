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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$step = (isset($_POST['f_step'])) ? clean_entier($_POST['f_step']) : '';
$affichage = '';
$poursuivre = true;
$filename_php = './__mysql_config/serveur_sacoche_'.SERVEUR_TYPE.'.php';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 1 - vérification / création des dossiers supplémentaires et de leurs droits
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( $step==1 )
{
	// Création des deux dossiers principaux, et vérification de leur accès en éciture
	$tab_dossier = array('./__tmp','./__mysql_config');
	foreach($tab_dossier as $dossier)
	{
		if(is_dir($dossier))
		{
			$affichage .= '<label for="rien" class="valide">Dossier <b>'.$dossier.'</b> déjà en place.</label><br />'."\r\n";
		}
		else
		{
			$test = @mkdir($dossier);
			if($test)
			{
				$affichage .= '<label for="rien" class="valide">Dossier <b>'.$dossier.'</b> créé.</label><br />'."\r\n";
				$test = is_writable($dossier);
				if($test)
				{
					$affichage .= '<label for="rien" class="valide">Dossier <b>'.$dossier.'</b> accesible en écriture.</label><br />'."\r\n";
				}
				else
				{
					$affichage .= '<label for="rien" class="erreur">Dossier <b>'.$dossier.'</b> inaccessible en écriture : veuillez en changer les droits manuellement.</label><br />'."\r\n";
					$poursuivre = false;
				}
			}
			else
			{
				$affichage .= '<label for="rien" class="erreur">Echec lors de la création du dossier <b>'.$dossier.'</b> : veuillez le créer manuellement.</label><br />'."\r\n";
				$poursuivre = false;
			}
		}
	}
	// Création des sous-dossiers, et vérification de leur accès en éciture
	if($poursuivre)
	{
		$tab_dossier = array('./__tmp/bilan_periode','./__tmp/cartouche','./__tmp/cookie','./__tmp/dump_database','./__tmp/etiquette','./__tmp/grille_niveau','./__tmp/import','./__tmp/init');
		foreach($tab_dossier as $dossier)
		{
			if(is_dir($dossier))
			{
				$affichage .= '<label for="rien" class="valide">Dossier <b>'.$dossier.'</b> déjà en place.</label><br />'."\r\n";
			}
			else
			{
				$test = @mkdir($dossier);
				if($test)
				{
					$affichage .= '<label for="rien" class="valide">Dossier <b>'.$dossier.'</b> créé.</label><br />'."\r\n";
					$test = is_writable($dossier);
					if($test)
					{
						$affichage .= '<label for="rien" class="valide">Dossier <b>'.$dossier.'</b> accesible en écriture.</label><br />'."\r\n";
					}
					else
					{
						$affichage .= '<label for="rien" class="erreur">Dossier <b>'.$dossier.'</b> inaccessible en écriture : veuillez en changer les droits manuellement.</label><br />'."\r\n";
						$poursuivre = false;
					}
				}
				else
				{
					$affichage .= '<label for="rien" class="erreur">Echec lors de la création du dossier <b>'.$dossier.'</b> : veuillez le créer manuellement.</label><br />'."\r\n";
					$poursuivre = false;
				}
			}
		}
	}
	// Affichage du résultat des opérations
	echo $affichage;
	echo ($poursuivre) ? '<p><span class="tab"><a href="#" class="step2">Passer à l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></span></p>' : '<p><span class="tab"><a href="#" class="step1">Reprendre l\'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 2 - vérification / remplissage de ces dossiers avec le contenu approprié
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==2 )
{
	// Création des fichiers index.htm
	$tab_dossier = array('./__tmp','./__mysql_config','./__tmp/bilan_periode','./__tmp/cartouche','./__tmp/cookie','./__tmp/dump_database','./__tmp/etiquette','./__tmp/grille_niveau','./__tmp/import','./__tmp/init');
	foreach($tab_dossier as $dossier)
	{
		$test = @file_put_contents($dossier.'/index.htm','Circulez, il n\'y a rien à voir par ici !');
		$poursuivre = (!$test) ? false : $poursuivre ;
	}
	if($poursuivre)
	{
		$affichage .= '<label for="rien" class="valide">Fichiers <b>index.htm</b> créés dans chaque dossier.</label><br />'."\r\n";
	}
	else
	{
		$affichage .= '<label for="rien" class="erreur">Echec lors de la création d\'un ou plusieurs fichiers <b>index.htm</b> dans chaque dossier.</label><br />'."\r\n";
	}
	// Création du fichier .htaccess
	$test = @file_put_contents('./__mysql_config/.htaccess','Order deny,allow'."\r\n".'allow from 127.0.0.1'."\r\n".'deny from all'."\r\n");
	if($test)
	{
		$affichage .= '<label for="rien" class="valide">Fichiers <b>.htaccess</b> créé dans le dossier <b>./__mysql_config</b>.</label><br />'."\r\n";
	}
	else
	{
		$affichage .= '<label for="rien" class="erreur">Echec lors de la création du fichier <b>.htaccess</b> dans le dossier <b>./__mysql_config</b>.</label><br />Veuiller y recopier ceui se trouvant par exemple dans le dossier <b>./_inc</b>.'."\r\n";
		$poursuivre = false;
	}
	// Affichage du résultat des opérations
	echo $affichage;
	echo ($poursuivre) ? '<p><span class="tab"><a href="#" class="step3">Passer à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></span></p>' : '<p><span class="tab"><a href="#" class="step1">Reprendre l\'étape 1.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 3 - vérification / indication des paramètres de connexion MySQL
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==3 )
{
	$serveur_mode = (SERVEUR_TYPE=='LOCAL') ? 'local' : 'production' ;
	$affichage .= '<span class="astuce">Deux ensembles de paramètres de connexion MySQL peuvent être utilisés : l\'un pour un serveur de test local, l\'autre pour un serveur en production en ligne.</span><br />'."\r\n";
	$affichage .= 'La distinction entre les deux se fait en PHP en testant si <b>$_SERVER[\'SERVER_NAME\']</b> est égal ou pas à <b>localhost</b>.<br />'."\r\n";
	$affichage .= 'La détection actuelle indique l\'usage d\'<b>un serveur en '.$serveur_mode.'</b>.<p />'."\r\n";
	if(is_file($filename_php))
	{
		// fichier présent : on passe à la suite
		$affichage .= '<label for="rien" class="valide">Le fichier <b>'.$filename_php.'</b> existe ; modifiez-en manuellement le contenu si les paramètres sont incorrects.</label>'."\r\n";
		$affichage .= '<p><span class="tab"><a href="#" class="step4">Passer à l\'étape 4.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
	}
	else
	{
		// afficher le formulaire pour entrer les paramètres
		$affichage .= '<fieldset>'."\r\n";
		$affichage .= 'Le fichier <b>'.$filename_php.'</b> n\'existant pas, indiquez ci-dessous les paramètres de connexion à la base en '.$serveur_mode.'.</label><p />'."\r\n";
		$affichage .= '<label class="tab" for="f_host">Nom du serveur :</label><input id="f_host" name="f_host" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_name">Nom de la base :</label><input id="f_name" name="f_name" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_user">Nom d\'utilisateur :</label><input id="f_user" name="f_user" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<label class="tab" for="f_pass">Mot de passe :</label><input id="f_pass" name="f_pass" size="20" type="text" value="" /><br />'."\r\n";
		$affichage .= '<span class="tab"></span><input id="f_step" name="f_step" type="hidden" value="31" /><input id="f_submit" type="submit" value="Valider." /><label id="ajax_msg">&nbsp;</label>'."\r\n";
		$affichage .= '</fieldset>'."\r\n";
	}
	echo $affichage;
}
elseif( $step==31 )
{
	// récupérer et tester les paramètres
	$HOST = (isset($_POST['f_host'])) ? clean_texte($_POST['f_host']) : '';
	$NAME = (isset($_POST['f_name'])) ? clean_texte($_POST['f_name']) : '';
	$USER = (isset($_POST['f_user'])) ? clean_texte($_POST['f_user']) : '';
	$PASS = (isset($_POST['f_pass'])) ? clean_texte($_POST['f_pass']) : '';
	// tester la connexion
	$BDlink = @mysql_connect($HOST,$USER,$PASS);
	if(!$BDlink)
	{
		exit('Erreur : impossible d\'accéder à la base : '.mysql_error().' !');
	}
	$bool = @mysql_select_db($NAME,$BDlink);
	if(!$bool)
	{
		$res = @mysql_query('CREATE DATABASE '.$NAME.' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci');
		if(!$bool && !$res)
		{
			exit('Erreur : impossible d\'accéder et de créer la base : '.mysql_error().' !');
		}
	}
	// c'est bon : créer le fichier de connexion et poursuivre
	$f_contenu = '<?php'."\r\n";
	$f_contenu.= '// Paramètres MySQL de la base de donnée SACoche en production'."\r\n";
	$f_contenu.= "\r\n";
	$f_contenu.= 'define(\'SACOCHE_BD_HOST\',\''.addcslashes($HOST,'\'').'\');	// Nom d\'hôte / serveur'."\r\n";
	$f_contenu.= 'define(\'SACOCHE_BD_NAME\',\''.addcslashes($NAME,'\'').'\');	// Nom de la base'."\r\n";
	$f_contenu.= 'define(\'SACOCHE_BD_USER\',\''.addcslashes($USER,'\'').'\');	// Nom d\'utilisateur'."\r\n";
	$f_contenu.= 'define(\'SACOCHE_BD_PASS\',\''.addcslashes($PASS,'\'').'\');	// Mot de passe'."\r\n";
	$f_contenu.= "\r\n";
	$f_contenu.= '?>'."\r\n";
	file_put_contents($filename_php,$f_contenu);
	$affichage .= '<p><label for="rien" class="valide">Les paramètres de connexion ont été utilisés avec succès, le fichier <b>'.$filename_php.'</b> a été créé.</label></p>'."\r\n";
	$affichage .= '<p><span class="tab"><a href="#" class="step4">Passer à l\'étape 4.</a><label id="ajax_msg">&nbsp;</label></span></p>' ;
	echo $affichage;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 4 - vérification / installation de la base de données
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( $step==4 )
{
	include_once($filename_php);
	require_once('./_inc/class.DB.config.sacoche.php');
	require_once('./_inc/class.DB.php');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_competence_domaine (
			livret_domaine_id smallint(5) unsigned NOT NULL auto_increment,
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_matiere_id smallint(5) unsigned NOT NULL,
			livret_niveau_id tinyint(3) unsigned NOT NULL,
			livret_domaine_ref char(1) collate utf8_unicode_ci NOT NULL,
			livret_domaine_nom varchar(128) collate utf8_unicode_ci NOT NULL,
			livret_domaine_ordre tinyint(3) unsigned NOT NULL,
			PRIMARY KEY (livret_domaine_id),
			KEY livret_structure_id (livret_structure_id),
			KEY livret_matiere_id (livret_matiere_id),
			KEY livret_niveau_id (livret_niveau_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_competence_item (
			livret_competence_id mediumint(8) unsigned NOT NULL auto_increment,
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_theme_id smallint(5) unsigned NOT NULL,
			livret_socle_id smallint(5) unsigned NOT NULL,
			livret_competence_nom tinytext collate utf8_unicode_ci NOT NULL,
			livret_competence_ordre tinyint(3) unsigned NOT NULL,
			livret_competence_coef tinyint(3) unsigned NOT NULL default "1",
			livret_competence_lien tinytext collate utf8_unicode_ci NOT NULL,
			PRIMARY KEY (livret_competence_id),
			KEY livret_structure_id (livret_structure_id),
			KEY livret_theme_id (livret_theme_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_competence_theme (
			livret_theme_id smallint(5) unsigned NOT NULL auto_increment,
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_domaine_id smallint(5) unsigned NOT NULL,
			livret_theme_nom varchar(128) collate utf8_unicode_ci NOT NULL,
			livret_theme_ordre tinyint(3) unsigned NOT NULL,
			PRIMARY KEY (livret_theme_id),
			KEY livret_structure_id (livret_structure_id),
			KEY livret_domaine_id (livret_domaine_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_evaluation (
			livret_evaluation_id mediumint(8) unsigned NOT NULL auto_increment,
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_prof_id int(11) unsigned NOT NULL,
			livret_groupe_id mediumint(8) unsigned NOT NULL,
			livret_evaluation_date date NOT NULL,
			livret_evaluation_info varchar(60) collate utf8_unicode_ci NOT NULL,
			PRIMARY KEY (livret_evaluation_id),
			KEY livret_structure_id (livret_structure_id),
			KEY livret_prof_id (livret_prof_id),
			KEY livret_groupe_id (livret_groupe_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_groupe (
			livret_groupe_id mediumint(8) unsigned NOT NULL auto_increment,
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_groupe_type enum("classe","groupe","besoin","eval") collate utf8_unicode_ci NOT NULL,
			livret_groupe_prof_id int(10) unsigned NOT NULL,
			livret_groupe_ref char(8) collate utf8_unicode_ci NOT NULL,
			livret_groupe_nom varchar(20) collate utf8_unicode_ci NOT NULL,
			livret_niveau_id tinyint(3) unsigned NOT NULL,
			PRIMARY KEY (livret_groupe_id),
			KEY livret_structure_id (livret_structure_id),
			KEY livret_niveau_id (livret_niveau_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_jointure_evaluation_competence (
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_evaluation_id mediumint(8) unsigned NOT NULL,
			livret_competence_id mediumint(8) unsigned NOT NULL,
			UNIQUE KEY livret_structure_id (livret_structure_id,livret_evaluation_id,livret_competence_id),
			KEY livret_evaluation_id (livret_evaluation_id),
			KEY livret_competence_id (livret_competence_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_jointure_user_competence (
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_prof_id int(11) unsigned NOT NULL,
			livret_eleve_id int(11) unsigned NOT NULL,
			livret_evaluation_id mediumint(8) unsigned NOT NULL,
			livret_competence_id mediumint(8) unsigned NOT NULL,
			livret_user_competence_date date NOT NULL,
			livret_user_competence_note enum("VV","V","R","RR","ABS","NN","DISP") collate utf8_unicode_ci NOT NULL,
			livret_user_competence_info tinytext collate utf8_unicode_ci NOT NULL,
			UNIQUE KEY livret_eleve_id (livret_eleve_id,livret_evaluation_id,livret_competence_id),
			KEY livret_structure_id (livret_structure_id),
			KEY livret_prof_id (livret_prof_id),
			KEY livret_evaluation_id (livret_evaluation_id),
			KEY livret_competence_id (livret_competence_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_jointure_user_groupe (
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_user_id int(11) unsigned NOT NULL,
			livret_groupe_id mediumint(8) unsigned NOT NULL,
			livret_jointure_pp tinyint(1) NOT NULL,
			UNIQUE KEY jointure_user_groupe (livret_structure_id,livret_user_id,livret_groupe_id),
			KEY livret_user_id (livret_user_id),
			KEY livret_groupe_id (livret_groupe_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_jointure_user_matiere (
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_user_id int(11) unsigned NOT NULL,
			livret_matiere_id smallint(5) unsigned NOT NULL,
			livret_jointure_coord tinyint(1) NOT NULL,
			UNIQUE KEY jointure_user_matiere (livret_structure_id,livret_user_id,livret_matiere_id),
			KEY livret_user_id (livret_user_id),
			KEY livret_matiere_id (livret_matiere_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_matiere (
			livret_matiere_id smallint(5) unsigned NOT NULL auto_increment,
			livret_matiere_structure_id mediumint(8) unsigned default NULL,
			livret_matiere_ref varchar(5) collate utf8_unicode_ci NOT NULL,
			livret_matiere_nom varchar(50) collate utf8_unicode_ci NOT NULL,
			PRIMARY KEY (livret_matiere_id),
			KEY livret_structure_id (livret_matiere_structure_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_niveau (
			livret_niveau_id tinyint(3) unsigned NOT NULL auto_increment,
			livret_niveau_ordre tinyint(3) unsigned NOT NULL,
			livret_niveau_ref varchar(6) collate utf8_unicode_ci NOT NULL,
			livret_niveau_nom varchar(50) collate utf8_unicode_ci NOT NULL,
			PRIMARY KEY (livret_niveau_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_referentiel (
			livret_matiere_id smallint(5) unsigned NOT NULL,
			livret_niveau_id tinyint(3) unsigned NOT NULL,
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_referentiel_partage enum("bof","non","oui","hs") collate utf8_unicode_ci NOT NULL,
			livret_referentiel_succes smallint(6) NOT NULL,
			UNIQUE KEY livret_matiere_id (livret_matiere_id,livret_niveau_id,livret_structure_id),
			KEY livret_niveau_id (livret_niveau_id),
			KEY livret_structure_id (livret_structure_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_socle_item (
			livret_socle_id smallint(5) unsigned NOT NULL auto_increment,
			livret_section_id smallint(5) unsigned NOT NULL,
			livret_socle_nom tinytext collate utf8_unicode_ci NOT NULL,
			livret_socle_ordre tinyint(3) unsigned NOT NULL,
			PRIMARY KEY (livret_socle_id),
			KEY livret_section_id (livret_section_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_socle_palier (
			livret_palier_id tinyint(3) unsigned NOT NULL auto_increment,
			livret_palier_nom varchar(25) collate utf8_unicode_ci NOT NULL,
			livret_palier_ordre tinyint(3) unsigned NOT NULL,
			PRIMARY KEY (livret_palier_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_socle_pilier (
			livret_pilier_id smallint(5) unsigned NOT NULL auto_increment,
			livret_palier_id tinyint(3) unsigned NOT NULL,
			livret_pilier_nom varchar(128) collate utf8_unicode_ci NOT NULL,
			livret_pilier_ordre tinyint(3) unsigned NOT NULL,
			PRIMARY KEY (livret_pilier_id),
			KEY livret_palier_id (livret_palier_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_socle_section (
			livret_section_id smallint(5) unsigned NOT NULL auto_increment,
			livret_pilier_id smallint(5) unsigned NOT NULL,
			livret_section_nom varchar(128) collate utf8_unicode_ci NOT NULL,
			livret_section_ordre tinyint(3) unsigned NOT NULL,
			PRIMARY KEY (livret_section_id),
			KEY livret_pilier_id (livret_pilier_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_structure (
			livret_structure_id mediumint(8) unsigned NOT NULL,
			structure_type_ref varchar(6) collate utf8_unicode_ci NOT NULL,
			structure_nom varchar(40) collate utf8_unicode_ci NOT NULL,
			geo_continent_ordre tinyint(3) unsigned NOT NULL,
			geo_continent_nom varchar(25) collate utf8_unicode_ci NOT NULL,
			geo_pays_nom varchar(35) collate utf8_unicode_ci NOT NULL,
			geo_departement_numero char(4) collate utf8_unicode_ci NOT NULL,
			geo_departement_nom varchar(40) collate utf8_unicode_ci NOT NULL,
			geo_commune_nom varchar(45) collate utf8_unicode_ci NOT NULL,
			admin_id int(10) unsigned NOT NULL,
			admin_nom varchar(20) collate utf8_unicode_ci NOT NULL,
			admin_prenom varchar(20) collate utf8_unicode_ci NOT NULL,
			admin_courriel varchar(60) collate utf8_unicode_ci NOT NULL,
			admin_password char(32) collate utf8_unicode_ci NOT NULL,
			livret_structure_modele_professeur varchar(10) collate utf8_unicode_ci NOT NULL default "pnom",
			livret_structure_modele_eleve varchar(10) collate utf8_unicode_ci NOT NULL default "pnom",
			livret_structure_matieres tinytext collate utf8_unicode_ci NOT NULL,
			livret_structure_niveaux tinytext collate utf8_unicode_ci NOT NULL,
			livret_structure_paliers set("1","2","3") collate utf8_unicode_ci NOT NULL,
			PRIMARY KEY (livret_structure_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		CREATE TABLE IF NOT EXISTS livret_user (
			livret_user_id int(11) unsigned NOT NULL auto_increment,
			livret_structure_id mediumint(8) unsigned NOT NULL,
			livret_user_ref char(11) collate utf8_unicode_ci NOT NULL,
			livret_user_profil enum("professeur","eleve") collate utf8_unicode_ci NOT NULL,
			livret_user_nom varchar(20) collate utf8_unicode_ci NOT NULL,
			livret_user_prenom varchar(20) collate utf8_unicode_ci NOT NULL,
			livret_user_login varchar(20) collate utf8_unicode_ci NOT NULL,
			livret_user_password char(32) collate utf8_unicode_ci NOT NULL,
			livret_eleve_classe_id mediumint(8) unsigned NOT NULL,
			PRIMARY KEY (livret_user_id),
			KEY livret_structure_id (livret_structure_id)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	');
	DB::query(SACOCHE_BD_NAME , '
		REPLACE INTO livret_structure VALUES
		(2404, "CLG", "Robert Barriere", 1, "France métropolitaine", "France", " 33", "Gironde", "Sauveterre-de-Guyenne", 0, "JOCAL", "Julien", "collegerb@free.fr", "93fd17ea6023ba55146bfbfcface2a03", "pre.nom", "pre.nom", "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,99", "1,2,3,4", "")
	');
	$affichage .= '<p><label for="rien" class="valide">Tables installées si nécessaire, établissement de Julien Jocal ajouté si nécessaire.</label></p>'."\r\n";
	$affichage .= '<p><span class="tab"><a href="./index.php">Installation terminée - Retour à l\'accueil.</a></span></p>' ;
	echo $affichage;
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
