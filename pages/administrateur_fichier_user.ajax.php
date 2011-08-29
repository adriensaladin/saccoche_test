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
if($_SESSION['SESAMATH_ID']==ID_DEMO){exit('Action désactivée pour la démo...');}

$action = (isset($_POST['f_action'])) ? $_POST['f_action']             : ''; // transmis la 1e fois manuellement, ensuite dans un INPUT
$step   = (isset($_POST['f_step']))   ? clean_entier($_POST['f_step']) : 0;

$dossier_import    = './__tmp/import/';
$dossier_login_mdp = './__tmp/login-mdp/';

$tab_actions = array('sconet_professeurs_directeurs_oui'=>'sconet_professeurs_directeurs','tableur_professeurs_directeurs'=>'tableur_professeurs_directeurs','sconet_eleves_oui'=>'sconet_eleves','sconet_parents_oui'=>'sconet_parents','base-eleves_eleves'=>'base-eleves_eleves','tableur_eleves'=>'tableur_eleves');
$tab_etapes  = array();

if( !isset($tab_actions[$action]) )
{
	exit('Erreur avec les données transmises !');
}

$action = $tab_actions[$action];
$test_sconet = (substr($action,0,6)=='sconet') ? true : false ;
$tab_extensions_autorisees = $test_sconet ? array('zip','xml') : array('txt','csv') ;
$extension_fichier_dest    = $test_sconet ? 'xml'              : 'txt' ;
$fichier_dest = 'import_'.$action.'_'.$_SESSION['BASE'].'.'.$extension_fichier_dest ;

function load_fichier($nom)
{
	global $dossier_import,$action;
	$fnom = $dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_'.$nom.'.txt';
	if(!file_exists($fnom))
	{
		exit('Erreur : le fichier contenant les données à traiter est introuvable !');
	}
	$contenu = file_get_contents($fnom);
	$tableau = @unserialize($contenu);
	if($tableau===FALSE)
	{
		exit('Erreur : le fichier contenant les données à traiter est syntaxiquement incorrect !');
	}
	return $tableau;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Liste des étapes suivant le mode d'import
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$tab_etapes['sconet_professeurs_directeurs']   = '<li id="step1">Étape 1 - Récupération du fichier</li>';
$tab_etapes['sconet_professeurs_directeurs']  .= '<li id="step2">Étape 2 - Extraction des données</li>';
$tab_etapes['sconet_professeurs_directeurs']  .= '<li id="step3">Étape 3 - Classes (ajouts / modifications)</li>';
$tab_etapes['sconet_professeurs_directeurs']  .= '<li id="step4">Étape 4 - Groupes (ajouts / modifications)</li>';
$tab_etapes['sconet_professeurs_directeurs']  .= '<li id="step5">Étape 5 - Utilisateurs (ajouts / modifications / suppressions)</li>';
$tab_etapes['sconet_professeurs_directeurs']  .= '<li id="step6">Étape 6 - Affectations (ajouts / modifications)</li>';
$tab_etapes['sconet_professeurs_directeurs']  .= '<li id="step9">Étape 7 - Nettoyage des fichiers temporaires</li>';

$tab_etapes['tableur_professeurs_directeurs']  = '<li id="step1">Étape 1 - Récupération du fichier</li>';
$tab_etapes['tableur_professeurs_directeurs'] .= '<li id="step2">Étape 2 - Extraction des données</li>';
$tab_etapes['tableur_professeurs_directeurs'] .= '<li id="step5">Étape 3 - Utilisateurs (ajouts / modifications / suppressions)</li>';
$tab_etapes['tableur_professeurs_directeurs'] .= '<li id="step9">Étape 4 - Nettoyage des fichiers temporaires</li>';

$tab_etapes['sconet_eleves']                   = '<li id="step1">Étape 1 - Récupération du fichier</li>';
$tab_etapes['sconet_eleves']                  .= '<li id="step2">Étape 2 - Extraction des données</li>';
$tab_etapes['sconet_eleves']                  .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>';
$tab_etapes['sconet_eleves']                  .= '<li id="step4">Étape 4 - Groupes (ajouts / modifications / suppressions)</li>';
$tab_etapes['sconet_eleves']                  .= '<li id="step5">Étape 5 - Utilisateurs (ajouts / modifications / suppressions)</li>';
$tab_etapes['sconet_eleves']                  .= '<li id="step6">Étape 6 - Affectations (ajouts / modifications)</li>';
$tab_etapes['sconet_eleves']                  .= '<li id="step9">Étape 7 - Nettoyage des fichiers temporaires</li>';

$tab_etapes['sconet_parents']                  = '<li id="step1">Étape 1 - Récupération du fichier</li>';
$tab_etapes['sconet_parents']                 .= '<li id="step2">Étape 2 - Extraction des données</li>';
$tab_etapes['sconet_parents']                 .= '<li id="step5">Étape 3 - Utilisateurs (ajouts / modifications / suppressions)</li>';
$tab_etapes['sconet_parents']                 .= '<li id="step7">Étape 4 - Adresses (ajouts / modifications)</li>';
$tab_etapes['sconet_parents']                 .= '<li id="step8">Étape 5 - Responsabilités (ajouts / modifications / suppressions)</li>';
$tab_etapes['sconet_parents']                 .= '<li id="step9">Étape 6 - Nettoyage des fichiers temporaires</li>';

$tab_etapes['base-eleves_eleves']              = '<li id="step1">Étape 1 - Récupération du fichier</li>';
$tab_etapes['base-eleves_eleves']             .= '<li id="step2">Étape 2 - Extraction des données</li>';
$tab_etapes['base-eleves_eleves']             .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>';
$tab_etapes['base-eleves_eleves']             .= '<li id="step5">Étape 4 - Utilisateurs (ajouts / modifications / suppressions)</li>';
$tab_etapes['base-eleves_eleves']             .= '<li id="step9">Étape 5 - Nettoyage des fichiers temporaires</li>';

$tab_etapes['tableur_eleves']                  = '<li id="step1">Étape 1 - Récupération du fichier</li>';
$tab_etapes['tableur_eleves']                 .= '<li id="step2">Étape 2 - Extraction des données</li>';
$tab_etapes['tableur_eleves']                 .= '<li id="step3">Étape 3 - Classes (ajouts / modifications / suppressions)</li>';
$tab_etapes['tableur_eleves']                 .= '<li id="step5">Étape 4 - Utilisateurs (ajouts / modifications / suppressions)</li>';
$tab_etapes['tableur_eleves']                 .= '<li id="step9">Étape 5 - Nettoyage des fichiers temporaires</li>';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 10 - Récupération du fichier (sconet_professeurs_directeurs | tableur_professeurs_directeurs | sconet_eleves | sconet_parents | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==10 )
{
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		require_once('./_inc/fonction_infos_serveur.php');
		exit('Erreur : problème de transfert ! Fichier trop lourd ? min(memory_limit,post_max_size,upload_max_filesize)='.minimum_limitations_upload());
	}
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	if(!in_array($extension,$tab_extensions_autorisees))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	if($extension!='zip')
	{
		if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
		{
			exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
		}
	}
	else
	{
		// Dézipper le fichier
		if(extension_loaded('zip')!==true)
		{
			exit('Erreur : le serveur ne gère pas les fichiers ZIP ! Renvoyez votre fichier sans compression.');
		}
		$zip = new ZipArchive();
		$result_open = $zip->open($fnom_serveur);
		if($result_open!==true)
		{
			require('./_inc/tableau_zip_error.php');
			exit('Erreur : votre archive ZIP n\'a pas pu être ouverte ('.$result_open.$tab_zip_error[$result_open].') !');
		}
		if($action=='sconet_eleves')
		{
			$nom_fichier_extrait = 'ElevesSansAdresses.xml';
		}
		elseif($action=='sconet_parents')
		{
			$nom_fichier_extrait = 'ResponsablesAvecAdresses.xml';
		}
		else
		{
			$annee_scolaire  = (date('n')>7) ? date('Y') : date('Y')-1 ;
			$nom_fichier_extrait = 'sts_emp_'.$_SESSION['UAI'].'_'.$annee_scolaire.'.xml';
		}
		if($zip->extractTo($dossier_import,$nom_fichier_extrait)!==true)
		{
			exit('Erreur : fichier '.$nom_fichier_extrait.' non trouvé dans l\'archive ZIP !');
		}
		$zip->close();
		if(!rename($dossier_import.$nom_fichier_extrait , $dossier_import.$fichier_dest))
		{
			exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
		}
	}
	// On affiche le bilan et les puces des étapes
	echo'<ul id="step">'.$tab_etapes[$action].'</ul>';
	echo'<hr />';
	echo'<fieldset>';
	echo'<div><label class="valide">Votre fichier a été correctement réceptionné.</label></div>';
	echo'<p class="li"><a href="#step20" id="passer_etape_suivante">Passer à l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></p>';
	echo'</fieldset>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 20 - Extraction des données (sconet_professeurs_directeurs | tableur_professeurs_directeurs | sconet_eleves | sconet_parents | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==20 )
{
	if(!is_file($dossier_import.$fichier_dest))
	{
		exit('Erreur : le fichier récupéré et enregistré n\'a pas été retrouvé !');
	}
	// Pour récupérer les données des utilisateurs ; on prend comme indice $sconet_id ou $reference suivant le mode d'import
	/*
	 * On utilise la forme moins commode   ['nom'][i]=... ['prenom'][i]=...
	 * au lieu de la forme plus habituelle [i]['nom']=... [i]['prenom']=...
	 * parce qu'ensuite cela permet d'effectuer un tri multicolonnes.
	 */
	$tab_users_fichier               = array();
	$tab_users_fichier['sconet_id']  = array();
	$tab_users_fichier['sconet_num'] = array();
	$tab_users_fichier['reference']  = array();
	$tab_users_fichier['profil']     = array(); // Pour distinguer professeurs & directeurs
	$tab_users_fichier['nom']        = array();
	$tab_users_fichier['prenom']     = array();
	$tab_users_fichier['classe']     = array(); // Avec id sconet_id ou reference // Classe de l'élève || Classes du professeur, avec indication PP
	$tab_users_fichier['groupe']     = array(); // Avec id sconet_id // Groupes de l'élève || Groupes du professeur
	$tab_users_fichier['matiere']    = array(); // Avec id sconet_id // Matières du professeur, avec indication méthode récupération
	$tab_users_fichier['adresse']    = array(); // Avec id sconet_id // Adresse du responsable légal
	$tab_users_fichier['enfant']     = array(); // Avec id sconet_id // Liste des élèves rattachés
	// Pour récupérer les données des classes et des groupes
	$tab_classes_fichier['ref']    = array();
	$tab_classes_fichier['nom']    = array();
	$tab_classes_fichier['niveau'] = array();
	$tab_groupes_fichier['ref']    = array();
	$tab_groupes_fichier['nom']    = array();
	$tab_groupes_fichier['niveau'] = array();
	// Procédures différentes suivant le mode d'import...
	if($action=='sconet_professeurs_directeurs')
	{
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Étape 2a - Extraction sconet_professeurs_directeurs
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$xml = @simplexml_load_file($dossier_import.$fichier_dest);
		if($xml===false)
		{
			exit('Erreur : le fichier transmis n\'est pas un XML valide !');
		}
		$uai = $xml->PARAMETRES->UAJ;
		if($uai===false)
		{
			exit('Erreur : le fichier transmis n\'est pas correct (erreur de numéro UAI) !');
		}
		/*
		 * Les matières des profs peuvent être récupérées de 2 façons :
		 * 1. $xml->DONNEES->INDIVIDUS->INDIVIDU->DISCIPLINES->DISCIPLINE->attributes()->CODE
		 *    On récupère alors un code formé d'une lettre (L ou C) et de 4 chiffres (matières que le prof est apte à enseigner, son service peut préciser les choses...).
		 *    Je n'ai pas trouvé de correspondance officielle &rarr; Le tableau $tab_discipline_code_discipline_TO_matiere_code_gestion donne les principales.
		 */
		$tab_discipline_code_discipline_TO_matiere_code_gestion = array();
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0080'] = array('DOC');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0100'] = array('PHILO');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.02..'] = array('FRANC');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0421'] = array('ALL1','ALL2');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0422'] = array('AGL1','AGL2');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0424'] = array('CHI1','CHI2');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0426'] = array('ESP1','ESP2');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0429'] = array('ITA1','ITA2');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0433'] = array('POR1','POR2');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.0434'] = array('RUS1','RUS2');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1000'] = array('HIGEO','EDCIV');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1100'] = array('SES');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1300'] = array('MATHS');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1315'] = array('MATHS','PH-CH');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1400'] = array('TECHN');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1500'] = array('PH-CH');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1600'] = array('SVT');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1615'] = array('SVT','PH-CH');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1700'] = array('EDMUS');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1800'] = array('A-PLA');
		$tab_discipline_code_discipline_TO_matiere_code_gestion['.1900'] = array('EPS');
		/*
		 * Les matières des profs peuvent être récupérées de 2 façons :
		 * 2. $xml->DONNEES->STRUCTURES->DIVISIONS->DIVISION->SERVICES->SERVICE->attributes()->CODE_MATIERE
		 *    On récupère alors, si l'emploi du temps est rensigné, un code expliqué dans $xml->NOMENCLATURES->MATIERES->MATIERE.
		 *    &rarr; Le tableau $tab_matiere_code_matiere_TO_matiere_code_gestion liste ce contenu des nomenclatures.
		 */
		$tab_matiere_code_matiere_TO_matiere_code_gestion = array();
		if( ($xml->NOMENCLATURES) && ($xml->NOMENCLATURES->MATIERES) && ($xml->NOMENCLATURES->MATIERES->MATIERE) )
		{
			foreach ($xml->NOMENCLATURES->MATIERES->MATIERE as $matiere)
			{
				$matiere_code_matiere = (string) $matiere->attributes()->CODE; // (string) obligatoire sinon il n'aime pas une clef commençant par 0...
				$matiere_code_gestion = (string) $matiere->CODE_GESTION; // (string) obligatoire sinon il n'aime pas une clef commençant par 0...
				$tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere] = $matiere_code_gestion;
			}
		}
		//
		// On passe les utilisateurs en revue : on mémorise leurs infos, y compris les PP, d'éventuelles matières affectées, d'éventuelles classes présentes
		//
		$date_aujourdhui = date('Y-m-d');
		if( ($xml->DONNEES) && ($xml->DONNEES->INDIVIDUS) && ($xml->DONNEES->INDIVIDUS->INDIVIDU) )
		{
			foreach ($xml->DONNEES->INDIVIDUS->INDIVIDU as $individu)
			{
				$fonction = $individu->FONCTION ;
				// Prendre les professeurs, les CPE, le personnel de direction (je ne sais pas s'il y a d'autres cas)
				if(in_array( $fonction , array('ENS','EDU','DIR') ))
				{
					$sconet_id = clean_entier($individu->attributes()->ID);
					$i_fichier  = $sconet_id;
					$tab_users_fichier['sconet_id'][$i_fichier]  = $sconet_id;
					$tab_users_fichier['sconet_num'][$i_fichier] = 0;
					$tab_users_fichier['reference'][$i_fichier]  = '';
					$tab_users_fichier['profil'][$i_fichier]     = ($fonction=='DIR') ? 'directeur' : 'professeur' ;
					$tab_users_fichier['nom'][$i_fichier]        = clean_nom($individu->NOM_USAGE);
					$tab_users_fichier['prenom'][$i_fichier]     = clean_prenom($individu->PRENOM);
					$tab_users_fichier['classe'][$i_fichier]     = array();
					$tab_users_fichier['groupe'][$i_fichier]     = array();
					$tab_users_fichier['matiere'][$i_fichier]    = array();
					// Indication éventuelle de professeur principal
					if( ($individu->PROFS_PRINC) && ($individu->PROFS_PRINC->PROF_PRINC) )
					{
						foreach ($individu->PROFS_PRINC->PROF_PRINC as $prof_princ)
						{
							$classe_ref = clean_ref($prof_princ->CODE_STRUCTURE);
							$date_fin   = clean_ref($prof_princ->DATE_FIN);
							$i_classe   = 'i'.clean_login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
							if($date_fin <= $date_aujourdhui)
							{
								$tab_users_fichier['classe'][$i_fichier][$i_classe] = 'PP';
							}
							// Au passage on ajoute la classe trouvée
							if(!isset($tab_classes_fichier['ref'][$i_classe]))
							{
								$tab_classes_fichier['ref'][$i_classe]    = $classe_ref;
								$tab_classes_fichier['nom'][$i_classe]    = $classe_ref;
								$tab_classes_fichier['niveau'][$i_classe] = $classe_ref;
							}
						}
					}
					// Indication éventuelle des matières du professeur (toujours renseigné pour les profs, mais matières potentielles et non effectivement enseignées, et usage d'un code discipline pas commode à décrypter)
					if( ($individu->DISCIPLINES) && ($individu->DISCIPLINES->DISCIPLINE) )
					{
						foreach ($individu->DISCIPLINES->DISCIPLINE as $discipline)
						{
							$discipline_code_discipline = (string) $discipline->attributes()->CODE;
							foreach ($tab_discipline_code_discipline_TO_matiere_code_gestion as $masque_recherche => $tab_matiere_code_gestion)
							{
								if(preg_match('/^'.$masque_recherche.'$/',$discipline_code_discipline))
								{
									foreach ($tab_matiere_code_gestion as $matiere_code_gestion)
									{
										$tab_users_fichier['matiere'][$i_fichier][$matiere_code_gestion] = 'discipline';
									}
									break;
								}
							}
						}
					}
				}
			}
		}
		//
		// On passe les classes en revue : on mémorise leurs infos, y compris les profs rattachés éventuels, et les matières associées
		//
		if( ($xml->DONNEES) && ($xml->DONNEES->STRUCTURE) && ($xml->DONNEES->STRUCTURE->DIVISIONS) && ($xml->DONNEES->STRUCTURE->DIVISIONS->DIVISION) )
		{
			foreach ($xml->DONNEES->STRUCTURE->DIVISIONS->DIVISION as $division)
			{
				$classe_ref = clean_ref($division->attributes()->CODE);
				$classe_nom = clean_texte($division->LIBELLE_LONG);
				$i_classe   = 'i'.clean_login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
				// Au passage on ajoute la classe trouvée
				if(!isset($tab_classes_fichier['ref'][$i_classe]))
				{
					$tab_classes_fichier['ref'][$i_classe]    = $classe_ref;
					$tab_classes_fichier['nom'][$i_classe]    = $classe_nom;
					$tab_classes_fichier['niveau'][$i_classe] = $classe_ref;
				}
				else
				{
					$tab_classes_fichier['nom'][$i_classe]    = $classe_nom;
				}
				if( ($division->SERVICES) && ($division->SERVICES->SERVICE) )
				{
					foreach ($division->SERVICES->SERVICE as $service)
					{
						$matiere_code_matiere = (string) $service->attributes()->CODE_MATIERE; // (string) obligatoire sinon il n'aime pas une clef commençant par 0...
						if( ($service->ENSEIGNANTS) && ($service->ENSEIGNANTS->ENSEIGNANT) )
						{
							foreach ($service->ENSEIGNANTS->ENSEIGNANT as $enseignant)
							{
								$i_fichier = clean_entier($enseignant->attributes()->ID);
								// associer la classe au prof
								if(!isset($tab_users_fichier['classe'][$i_fichier][$i_classe]))
								{
									$tab_users_fichier['classe'][$i_fichier][$i_classe] = 'prof';
								}
								// associer la matière au prof
								if(isset($tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere]))
								{
									$matiere_code_gestion = $tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere];
									$tab_users_fichier['matiere'][$i_fichier][$matiere_code_gestion] = 'service';
								}
							}
						}
					}
				}
			}
		}
		//
		// On passe les groupes en revue : on mémorise leurs infos, y compris les profs rattachés éventuels, et les matières associées
		//
		if( ($xml->DONNEES) && ($xml->DONNEES->STRUCTURE) && ($xml->DONNEES->STRUCTURE->GROUPES) && ($xml->DONNEES->STRUCTURE->GROUPES->GROUPE) )
		{
			foreach ($xml->DONNEES->STRUCTURE->GROUPES->GROUPE as $groupe)
			{
				$groupe_ref = clean_ref($groupe->attributes()->CODE);
				$groupe_nom = clean_texte($groupe->LIBELLE_LONG);
				$i_groupe   = 'i'.clean_login($groupe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
				// Au passage on ajoute le groupe trouvé
				if(!isset($tab_groupes_fichier['ref'][$i_groupe]))
				{
					$tab_groupes_fichier['ref'][$i_groupe]    = $groupe_ref;
					$tab_groupes_fichier['nom'][$i_groupe]    = $groupe_nom;
					$tab_groupes_fichier['niveau'][$i_groupe] = $groupe_ref;
				}
				if( ($groupe->SERVICES) && ($groupe->SERVICES->SERVICE) )
				{
					foreach ($groupe->SERVICES->SERVICE as $service)
					{
						$matiere_code_matiere = (string) $service->attributes()->CODE_MATIERE; // (string) obligatoire sinon il n'aime pas une clef commençant par 0...
						if( ($service->ENSEIGNANTS) && ($service->ENSEIGNANTS->ENSEIGNANT) )
						{
							foreach ($service->ENSEIGNANTS->ENSEIGNANT as $enseignant)
							{
								$i_fichier = clean_entier($enseignant->attributes()->ID);
								// associer le groupe au prof
								if(!isset($tab_users_fichier['groupe'][$i_fichier][$i_groupe]))
								{
									$tab_users_fichier['groupe'][$i_fichier][$i_groupe] = 'prof';
								}
								// associer la matière au prof
								if(isset($tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere]))
								{
									$matiere_code_gestion = $tab_matiere_code_matiere_TO_matiere_code_gestion[$matiere_code_matiere];
									$tab_users_fichier['matiere'][$i_fichier][$matiere_code_gestion] = 'service';
								}
							}
						}
					}
				}
			}
		}
	}
	if($action=='sconet_eleves')
	{
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Étape 2b - Extraction sconet_eleves
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$xml = @simplexml_load_file($dossier_import.$fichier_dest);
		if($xml===false)
		{
			exit('Erreur : le fichier transmis n\'est pas un XML valide !');
		}
		$uai = $xml->PARAMETRES->UAJ;
		if($uai===false)
		{
			exit('Erreur : le fichier transmis n\'est pas correct (erreur de numéro UAI) !');
		}
		// tableau temporaire qui sera effacé, servant à retenir le niveau de l'élève en attendant de connaître sa classe.
		$tab_users_fichier['niveau'] = array();
		// tableau temporaire servant à retenir les élèves marqués comme sortis de l'établissement, mais encore dans le fichier et reliés à une classe et autres bricoles.
		$tab_eleves_sortis = array();
		//
		// On passe les utilisateurs en revue : on mémorise leurs infos, plus leur niveau
		//
		if( ($xml->DONNEES) && ($xml->DONNEES->ELEVES) && ($xml->DONNEES->ELEVES->ELEVE) )
		{
			foreach ($xml->DONNEES->ELEVES->ELEVE as $eleve)
			{
				$i_fichier = clean_entier($eleve->attributes()->ELEVE_ID);
				if($eleve->DATE_SORTIE)
				{
					$tab_eleves_sortis[$i_fichier] = TRUE;
				}
				else
				{
					$tab_users_fichier['sconet_id'][$i_fichier]  = $i_fichier;
					$tab_users_fichier['sconet_num'][$i_fichier] = clean_entier($eleve->attributes()->ELENOET);
					$tab_users_fichier['reference'][$i_fichier]  = clean_ref($eleve->ID_NATIONAL);
					$tab_users_fichier['profil'][$i_fichier]     = 'eleve' ;
					$tab_users_fichier['nom'][$i_fichier]        = clean_nom($eleve->NOM);
					$tab_users_fichier['prenom'][$i_fichier]     = clean_prenom($eleve->PRENOM);
					$tab_users_fichier['classe'][$i_fichier]     = '';
					$tab_users_fichier['groupe'][$i_fichier]     = array();
					$tab_users_fichier['niveau'][$i_fichier]     = clean_ref($eleve->CODE_MEF);
				}
			}
		}
		//
		// On passe les liaisons élèves/classes-groupes en revue : on mémorise leurs infos, et les élèves rattachés
		//
		if( ($xml->DONNEES) && ($xml->DONNEES->STRUCTURES) && ($xml->DONNEES->STRUCTURES->STRUCTURES_ELEVE) )
		{
			foreach ($xml->DONNEES->STRUCTURES->STRUCTURES_ELEVE as $structures_eleve)
			{
				$i_fichier = clean_entier($structures_eleve->attributes()->ELEVE_ID);
				if(!isset($tab_eleves_sortis[$i_fichier]))
				{
					foreach ($structures_eleve->STRUCTURE as $structure)
					{
						if($structure->TYPE_STRUCTURE == 'D')
						{
							$classe_ref = clean_ref($structure->CODE_STRUCTURE);
							$i_classe   = 'i'.clean_login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
							$tab_users_fichier['classe'][$i_fichier] = $i_classe;
							if(!isset($tab_classes_fichier['ref'][$i_classe]))
							{
								$tab_classes_fichier['ref'][$i_classe]    = $classe_ref;
								$tab_classes_fichier['nom'][$i_classe]    = $classe_ref;
								$tab_classes_fichier['niveau'][$i_classe] = '';
							}
							if($tab_users_fichier['niveau'][$i_fichier])
							{
								$tab_classes_fichier['niveau'][$i_classe] = $tab_users_fichier['niveau'][$i_fichier];
							}
						}
						elseif($structure->TYPE_STRUCTURE == 'G')
						{
							$groupe_ref = clean_ref($structure->CODE_STRUCTURE);
							$i_groupe   = 'i'.clean_login($groupe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
							if(!isset($tab_users_fichier['groupe'][$i_fichier][$i_groupe]))
							{
								$tab_users_fichier['groupe'][$i_fichier][$i_groupe] = $groupe_ref;
							}
							if(!isset($tab_groupes_fichier['ref'][$i_groupe]))
							{
								$tab_groupes_fichier['ref'][$i_groupe]    = $groupe_ref;
								$tab_groupes_fichier['nom'][$i_groupe]    = $groupe_ref;
								$tab_groupes_fichier['niveau'][$i_groupe] = '';
							}
							if($tab_users_fichier['niveau'][$i_fichier])
							{
								$tab_groupes_fichier['niveau'][$i_groupe] = $tab_users_fichier['niveau'][$i_fichier];
							}
						}
					}
				}
			}
		}
		// suppression du tableau temporaire
		unset($tab_users_fichier['niveau']);
	}
	if($action=='sconet_parents')
	{
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Étape 2c - Extraction sconet_parents
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$xml = @simplexml_load_file($dossier_import.$fichier_dest);
		if($xml===false)
		{
			exit('Erreur : le fichier transmis n\'est pas un XML valide !');
		}
		$uai = $xml->PARAMETRES->UAJ;
		if($uai===false)
		{
			exit('Erreur : le fichier transmis n\'est pas correct (erreur de numéro UAI) !');
		}
		//
		// On recense les adresses dans un tableau temporaire.
		//
		$tab_adresses = array();
		if( ($xml->DONNEES) && ($xml->DONNEES->ADRESSES) && ($xml->DONNEES->ADRESSES->ADRESSE) )
		{
			foreach ($xml->DONNEES->ADRESSES->ADRESSE as $adresse)
			{
				$tab_adresses[clean_entier($adresse->attributes()->ADRESSE_ID)] = array( clean_commune($adresse->LIGNE1_ADRESSE) , clean_commune($adresse->LIGNE2_ADRESSE) , clean_commune($adresse->LIGNE3_ADRESSE) , clean_commune($adresse->LIGNE4_ADRESSE) , clean_entier($adresse->CODE_POSTAL) , clean_nom($adresse->LIBELLE_POSTAL) , clean_nom($adresse->LL_PAYS) );
			}
		}
		//
		// On recense les liens de responsabilités dans un tableau temporaire.
		// On ne garde que les resp. légaux, les contacts n'ont pas à avoir accès aux notes ou à un éventuel bulletin.
		//
		$tab_enfants = array();
		$nb_lien_responsabilite = 0;
		if( ($xml->DONNEES) && ($xml->DONNEES->RESPONSABLES) && ($xml->DONNEES->RESPONSABLES->RESPONSABLE_ELEVE) )
		{
			foreach ($xml->DONNEES->RESPONSABLES->RESPONSABLE_ELEVE as $responsable)
			{
				$num_responsable = clean_entier($responsable->RESP_LEGAL);
				if($num_responsable)
				{
					$tab_enfants[clean_entier($responsable->PERSONNE_ID)][clean_entier($responsable->ELEVE_ID)] = $num_responsable;
					$nb_lien_responsabilite++;
				}
			}
		}
		// L'import Sconet peut apporter beaucoup de parents rattachés à des élèves sortis de l'établissement et encore présents dans le fichier.
		// Alors on récupère la liste des id_sconet des élèves actifs et on contrôle par la suite qu'il y en a au moins un dans la liste des enfants du parent.
		$tab_eleves_actifs = array();
		$DB_TAB = DB_STRUCTURE_lister_users($profil='eleve',$only_actifs=TRUE,$with_classe=FALSE,$tri_statut=FALSE);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_eleves_actifs[$DB_ROW['user_sconet_id']] = TRUE;
		}
		//
		// On passe les parents en revue : on mémorise leurs infos (dont adresses et enfants)
		// Si pas d'enfant trouvé, on laisse tomber, c'est en effet le choix par défaut de Gepi qui indique : "ne pas proposer d'ajouter les responsables non associés à des élèves (de telles entrées peuvent subsister en très grand nombre dans Sconet)".
		//
		if( ($xml->DONNEES) && ($xml->DONNEES->PERSONNES) && ($xml->DONNEES->PERSONNES->PERSONNE) )
		{
			foreach ($xml->DONNEES->PERSONNES->PERSONNE as $personne)
			{
				$i_fichier = clean_entier($personne->attributes()->PERSONNE_ID);
				if(isset($tab_enfants[$i_fichier]))
				{
					$nb_enfants_actifs = 0;
					foreach($tab_enfants[$i_fichier] as $eleve_sconet_id => $resp_legal)
					{
						$nb_enfants_actifs += (isset($tab_eleves_actifs[$eleve_sconet_id])) ? 1 : 0 ;
					}
					if($nb_enfants_actifs)
					{
						$i_adresse = clean_entier($personne->ADRESSE_ID);
						$tab_users_fichier['sconet_id'][$i_fichier]  = $i_fichier;
						$tab_users_fichier['sconet_num'][$i_fichier] = 0;
						$tab_users_fichier['reference'][$i_fichier]  = '';
						$tab_users_fichier['profil'][$i_fichier]     = 'parent' ;
						$tab_users_fichier['nom'][$i_fichier]        = clean_nom($personne->NOM);
						$tab_users_fichier['prenom'][$i_fichier]     = clean_prenom($personne->PRENOM);
						$tab_users_fichier['adresse'][$i_fichier]    = isset($tab_adresses[$i_adresse]) ? $tab_adresses[$i_adresse] : array('','','','',0,'','') ;
						$tab_users_fichier['enfant'][$i_fichier]     = $tab_enfants[$i_fichier];
					}
				}
			}
		}
	}
	if($action=='tableur_professeurs_directeurs')
	{
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Étape 2d - Extraction tableur_professeurs_directeurs
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$contenu = file_get_contents($dossier_import.$fichier_dest);
		$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
		$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
		$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
		unset($tab_lignes[0]); // Supprimer la 1e ligne
		//
		// On passe les utilisateurs en revue : on mémorise leurs infos
		//
		foreach ($tab_lignes as $ligne_contenu)
		{
			$tab_elements = explode($separateur,$ligne_contenu);
			$tab_elements = array_slice($tab_elements,0,4);
			if(count($tab_elements)==4)
			{
				$tab_elements = array_map('clean_csv',$tab_elements);
				list($reference,$nom,$prenom,$profil) = $tab_elements;
				$profil = perso_strtolower($profil);
				if( ($nom!='') && ($prenom!='') && ( ($profil=='professeur') || ($profil=='directeur') ) )
				{
					$tab_users_fichier['sconet_id'][]  = 0;
					$tab_users_fichier['sconet_num'][] = 0;
					$tab_users_fichier['reference'][]  = clean_ref($reference);
					$tab_users_fichier['profil'][]     = $profil;
					$tab_users_fichier['nom'][]        = clean_nom($nom);
					$tab_users_fichier['prenom'][]     = clean_prenom($prenom);
				}
			}
		}
	}
	if($action=='tableur_eleves')
	{
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Étape 2e - Extraction tableur_eleves
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$contenu = file_get_contents($dossier_import.$fichier_dest);
		$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
		$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
		$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
		unset($tab_lignes[0]); // Supprimer la 1e ligne
		//
		// On passe les utilisateurs en revue : on mémorise leurs infos et les classes trouvées
		//
		foreach ($tab_lignes as $ligne_contenu)
		{
			$tab_elements = explode($separateur,$ligne_contenu);
			$tab_elements = array_slice($tab_elements,0,4);
			if(count($tab_elements)==4)
			{
				$tab_elements = array_map('clean_csv',$tab_elements);
				list($reference,$nom,$prenom,$classe) = $tab_elements;
				if( ($nom!='') && ($prenom!='') )
				{
					$classe_ref = mb_substr(clean_ref($classe),0,8);
					$i_classe   = 'i'.clean_login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
					$tab_users_fichier['sconet_id'][]  = 0;
					$tab_users_fichier['sconet_num'][] = 0;
					$tab_users_fichier['reference'][]  = clean_ref($reference);
					$tab_users_fichier['profil'][]     = 'eleve';
					$tab_users_fichier['nom'][]        = clean_nom($nom);
					$tab_users_fichier['prenom'][]     = clean_prenom($prenom);
					$tab_users_fichier['classe'][]     = $i_classe;
					if( ($classe_ref) && (!isset($tab_classes_fichier['ref'][$i_classe])) )
					{
						$tab_classes_fichier['ref'][$i_classe]    = $classe_ref;
						$tab_classes_fichier['nom'][$i_classe]    = $classe_ref;
						$tab_classes_fichier['niveau'][$i_classe] = $classe_ref;
					}
				}
			}
		}
	}
	if($action=='base-eleves_eleves')
	{
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Étape 2f - Extraction base-eleves_eleves
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$contenu = file_get_contents($dossier_import.$fichier_dest);
		$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
		$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
		$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
		// Utiliser la 1e ligne pour repérer les colonnes intéressantes
		$tab_numero_colonne = array('nom'=>-100,'prenom'=>-100,'niveau'=>-100,'classe'=>-100);
		$tab_elements = explode($separateur,$tab_lignes[0]);
		$tab_elements = array_map('clean_csv',$tab_elements);
		$numero_max = 0;
		foreach ($tab_elements as $numero=>$element)
		{
			switch($element)
			{
				case 'Nom Elève' :    $tab_numero_colonne['nom']    = $numero; $numero_max = max($numero_max,$numero); break; // normalement 0
				case 'Prénom Elève' : $tab_numero_colonne['prenom'] = $numero; $numero_max = max($numero_max,$numero); break; // normalement 2
				case 'Niveau' :       $tab_numero_colonne['niveau'] = $numero; $numero_max = max($numero_max,$numero); break; // normalement 14
				case 'Classe' :       $tab_numero_colonne['classe'] = $numero; $numero_max = max($numero_max,$numero); break; // normalement 15
			}
		}
		if(array_sum($tab_numero_colonne)<0)
		{
			exit('Erreur : les champs nécessaires n\'ont pas pu être repérés !');
		}
		unset($tab_lignes[0]); // Supprimer la 1e ligne
		/*
		 * Des difficultés se posent.
		 * D'une part, les noms des niveaux et des classes ne semblent pas soumis à un format particulier ; on peut facilement dépasser les 20 caractères maxi autorisés par Sacoche
		 * D'autre part il n'existe pas de référence courte pour une classe.
		 * Enfin, des classes sont sur plusieurs niveaux, donc comportent plusieurs groupes !
		 */
		$tab_bon = array(); $tab_bad = array();
		$tab_bon[] = 'T';   $tab_bad[] = array('Toute ','toute ','TOUTE ');
		$tab_bon[] = 'P';   $tab_bad[] = array('Petite ','petite ','PETITE ');
		$tab_bon[] = 'M';   $tab_bad[] = array('Moyenne ','moyenne ','MOYENNE ');
		$tab_bon[] = 'G';   $tab_bad[] = array('Grande ','grande ','GRANDE ');
		$tab_bon[] = 'S';   $tab_bad[] = array('Section ','section ','SECTION ','Section','section','SECTION');
		$tab_bon[] = 'C';   $tab_bad[] = array('Cours ','cours ','COURS ');
		$tab_bon[] = 'P';   $tab_bad[] = array('Préparatoire','préparatoire','Preparatoire','preparatoire','PRÉPARATOIRE','PREPARATOIRE');
		$tab_bon[] = 'E';   $tab_bad[] = array('Élémentaire ','élémentaire ','Elementaire ','elementaire ','ÉLÉMENTAIRE ','ELÉMENTAIRE ','ELEMENTAIRE ');
		$tab_bon[] = 'M';   $tab_bad[] = array('Moyen ','moyen ','MOYEN ');
		$tab_bon[] = '1';   $tab_bad[] = array('1er ','1ere ','1ère ','1ER ','1ERE ','1ÈRE ','premier ','première ','premiere ','PREMIER ','PREMIÈRE ','PREMIERE ');
		$tab_bon[] = '2';   $tab_bad[] = array('2e ','2eme ','2ème ','2E ','2EME ','2ÈME ','deuxième ','deuxieme ','seconde ','DEUXIEME ','DEUXIÈME ','SECONDE ');
		$tab_bon[] = '-';   $tab_bad[] = '- ';
		$tab_bon[] = '';    $tab_bad[] = array('Classe ','classe ','CLASSE ');
		$tab_bon[] = '';    $tab_bad[] = array('De ','de ','DE ');
		$tab_bon[] = '';    $tab_bad[] = array('Maternelle','maternelle','MATERNELLE');
		$tab_bon[] = '';    $tab_bad[] = array('Année','année','annee','ANNÉE','ANNEE');
		//
		// On passe les utilisateurs en revue : on mémorise leurs infos, les classes trouvées, les groupes trouvés
		//
		foreach ($tab_lignes as $ligne_contenu)
		{
			$tab_elements = explode($separateur,$ligne_contenu);
			if(count($tab_elements)>$numero_max)
			{
				$tab_elements = array_map('clean_csv',$tab_elements);
				$nom    = $tab_elements[$tab_numero_colonne['nom']   ];
				$prenom = $tab_elements[$tab_numero_colonne['prenom']];
				$niveau = $tab_elements[$tab_numero_colonne['niveau']];
				$classe = $tab_elements[$tab_numero_colonne['classe']];
				if( ($nom!='') && ($prenom!='') && ($niveau!='') && ($classe!='') )
				{
					// Réduire la longueur du niveau et de la classe
					foreach ($tab_bon as $i=>$bon)
					{
						$niveau = str_replace($tab_bad[$i],$bon,$niveau);
						$classe = str_replace($tab_bad[$i],$bon,$classe);
					}
					$niveau_ref = mb_substr(clean_ref($niveau),0,8);
					$classe_nom = mb_substr('['.$niveau_ref.'] '.$classe,0,20); // On fait autant de classes que de groupes de niveaux par classes.
					$classe_ref = mb_substr(clean_ref($niveau_ref.'_'.md5($niveau_ref.$classe)),0,8);
					$i_classe   = 'i'.clean_login($classe_ref); // 'i' car la référence peut être numérique (ex : 61) et cela pose problème que l'indice du tableau soit un entier (ajouter (string) n'y change rien) lors du array_multisort().
					$tab_users_fichier['sconet_id'][]  = 0;
					$tab_users_fichier['sconet_num'][] = 0;
					$tab_users_fichier['reference'][]  = '';
					$tab_users_fichier['profil'][]     = 'eleve';
					$tab_users_fichier['nom'][]        = mb_substr(clean_nom($nom),0,25);
					$tab_users_fichier['prenom'][]     = mb_substr(clean_prenom($prenom),0,25);
					$tab_users_fichier['classe'][]     = $i_classe;
					if( ($classe_ref) && (!isset($tab_classes_fichier['ref'][$i_classe])) )
					{
						$tab_classes_fichier['ref'][$i_classe]    = $classe_ref;
						$tab_classes_fichier['nom'][$i_classe]    = $classe_nom;
						$tab_classes_fichier['niveau'][$i_classe] = $niveau_ref;
					}
				}
			}
		}
	}
	//
	// Fin des différents cas possibles
	//
	// Tableaux pour les étapes 61/62/71/72 (donc juste pour Sconet élèves / profs / parents)
	$tab_i_classe_TO_id_base  = array();
	$tab_i_groupe_TO_id_base  = array();
	$tab_i_fichier_TO_id_base = array();
	$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
	// On trie
	switch($action)
	{
		case 'sconet_professeurs_directeurs' :
			$test1 = array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['sconet_id'],$tab_users_fichier['sconet_num'],$tab_users_fichier['reference'],$tab_users_fichier['profil'],$tab_users_fichier['classe'],$tab_users_fichier['groupe'],$tab_users_fichier['matiere']);
			$test2 = array_multisort($tab_classes_fichier['niveau'],SORT_DESC,SORT_STRING,$tab_classes_fichier['ref'],SORT_ASC,SORT_STRING,$tab_classes_fichier['nom'],SORT_ASC,SORT_STRING);
			$test3 = array_multisort($tab_groupes_fichier['niveau'],SORT_DESC,SORT_STRING,$tab_groupes_fichier['ref'],SORT_ASC,SORT_STRING,$tab_groupes_fichier['nom'],SORT_ASC,SORT_STRING);
			break;
		case 'sconet_eleves' :
			$test1 = array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['sconet_id'],$tab_users_fichier['sconet_num'],$tab_users_fichier['reference'],$tab_users_fichier['profil'],$tab_users_fichier['classe'],$tab_users_fichier['groupe']);
			$test2 = array_multisort($tab_classes_fichier['niveau'],SORT_DESC,SORT_STRING,$tab_classes_fichier['ref'],SORT_ASC,SORT_STRING,$tab_classes_fichier['nom'],SORT_ASC,SORT_STRING);
			$test3 = array_multisort($tab_groupes_fichier['niveau'],SORT_DESC,SORT_STRING,$tab_groupes_fichier['ref'],SORT_ASC,SORT_STRING,$tab_groupes_fichier['nom'],SORT_ASC,SORT_STRING);
			break;
		case 'tableur_eleves' :
		case 'base-eleves_eleves' :
			$test1 = array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['sconet_id'],$tab_users_fichier['sconet_num'],$tab_users_fichier['reference'],$tab_users_fichier['profil'],$tab_users_fichier['classe']);
			$test2 = array_multisort($tab_classes_fichier['niveau'],SORT_DESC,SORT_STRING,$tab_classes_fichier['ref'],SORT_ASC,SORT_STRING,$tab_classes_fichier['nom'],SORT_ASC,SORT_STRING);
			break;
		case 'sconet_parents' :
			$test1 = array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['sconet_id'],$tab_users_fichier['sconet_num'],$tab_users_fichier['reference'],$tab_users_fichier['profil'],$tab_users_fichier['adresse'],$tab_users_fichier['enfant']);
			break;
		case 'tableur_professeurs_directeurs' :
			$test1 = array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['sconet_id'],$tab_users_fichier['sconet_num'],$tab_users_fichier['reference'],$tab_users_fichier['profil']);
			break;
	}
	// Outil de résolution de bug ; le test1 provoque parfois l'erreur "Array sizes are inconsistent".
	if(!$test1)
	{
		ajouter_log_PHP( $log_objet='Import fichier '.$action , $log_contenu=serialize($tab_users_fichier) , $log_fichier=__FILE__ , $log_ligne=__LINE__ , $only_sesamath=true );
	}
	// On enregistre
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_users.txt',serialize($tab_users_fichier));
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_classes.txt',serialize($tab_classes_fichier));
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_groupes.txt',serialize($tab_groupes_fichier));
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt',serialize($tab_liens_id_base));
	// On affiche le bilan des utilisateurs trouvés
	if(count($tab_users_fichier['profil']))
	{
		require_once('./_inc/tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
		$tab_profil_nombre = array_count_values($tab_users_fichier['profil']);
		foreach ($tab_profil_nombre as $profil=>$nombre)
		{
			$s = ($nombre>1) ? 's' : '' ;
			echo'<p><label class="valide">'.$nombre.' '.$tab_profil_libelle[$profil]['long'][min(2,$nombre)].' trouvé'.$s.'.</label></p>';
		}
	}
	else
	{
		echo'<p><label class="alerte">Aucun utilisateur trouvé !</label></p>';
	}
	// On affiche le bilan des classes trouvées
	if( ($action!='tableur_professeurs_directeurs') && ($action!='sconet_parents') )
	{
		$nombre = count($tab_classes_fichier['ref']);
		if($nombre)
		{
			$s = ($nombre>1) ? 's' : '' ;
			echo'<p><label class="valide">'.$nombre.' classe'.$s.' trouvée'.$s.'.</label></p>';
		}
		else
		{
			echo'<p><label class="alerte">Aucune classe trouvée !</label></p>';
		}
	}
	// On affiche le bilan des groupes trouvés
	if( ($action=='sconet_professeurs_directeurs') || ($action=='sconet_eleves') )
	{
		$nombre = count($tab_groupes_fichier['ref']);
		if($nombre)
		{
			$s = ($nombre>1) ? 's' : '' ;
			echo'<p><label class="valide">'.$nombre.' groupe'.$s.' trouvé'.$s.'.</label></p>';
		}
		else
		{
			echo'<p><label class="alerte">Aucun groupe trouvé !</label></p>';
		}
	}
	// On affiche le bilan des parents trouvés
	if($action=='sconet_parents')
	{
		$nombre = count($tab_adresses);
		if($nombre)
		{
			$s = ($nombre>1) ? 's' : '' ;
			echo'<p><label class="valide">'.$nombre.' adresse'.$s.' trouvée'.$s.'.</label></p>';
		}
		else
		{
			echo'<p><label class="alerte">Aucune adresse trouvée !</label></p>';
		}
		if($nb_lien_responsabilite)
		{
			$s = ($nb_lien_responsabilite>1) ? 's' : '' ;
			echo'<p><label class="valide">'.$nb_lien_responsabilite.' lien'.$s.' de responsabilité'.$s.' trouvé'.$s.'.</label></p>';
		}
		else
		{
			echo'<p><label class="alerte">Aucun lien de responsabilité trouvé !</label></p>';
		}
	}
	// Fin de l'extraction
	$step = ( ($action!='tableur_professeurs_directeurs') && ($action!='sconet_parents') ) ? '3' : '5' ;
	echo'<p class="li"><a href="#step'.$step.'1" id="passer_etape_suivante">Passer à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 31 - Analyse des données des classes (sconet_professeurs_directeurs | sconet_eleves | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==31 )
{
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
	$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
	$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
	// On récupère le fichier avec les classes : $tab_classes_fichier['ref'] : i -> ref ; $tab_classes_fichier['nom'] : i -> nom ; $tab_classes_fichier['niveau'] : i -> niveau
	$tab_classes_fichier = load_fichier('classes');
	// On récupère le contenu de la base pour comparer : $tab_classes_base['ref'] : id -> ref ; $tab_classes_base['nom'] : id -> nom
	$tab_classes_base        = array();
	$tab_classes_base['ref'] = array();
	$tab_classes_base['nom'] = array();
	$DB_TAB = DB_STRUCTURE_lister_classes();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_classes_base['ref'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_ref'];
		$tab_classes_base['nom'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
	}
	// Contenu du fichier à conserver
	$lignes_ras = '';
	foreach($tab_classes_fichier['ref'] as $i_classe => $ref)
	{
		$id_base = array_search($ref,$tab_classes_base['ref']);
		if($id_base!==false)
		{
			$lignes_ras .= '<tr><th>'.html($tab_classes_base['ref'][$id_base]).'</th><td>'.html($tab_classes_base['nom'][$id_base]).'</td></tr>';
			$tab_i_classe_TO_id_base[$i_classe] = $id_base;
			unset($tab_classes_fichier['ref'][$i_classe] , $tab_classes_fichier['nom'][$i_classe] ,  $tab_classes_fichier['niveau'][$i_classe] , $tab_classes_base['ref'][$id_base] , $tab_classes_base['nom'][$id_base]);
		}
	}
	// Contenu du fichier à supprimer
	// Pour sconet_professeurs_directeurs, les groupes ne figurent pas forcément dans le fichier si les services ne sont pas présents &rarr; on ne procède qu'à des ajouts éventuels.
	// Du coup les classes restantes ne sont pas à supprimer mais à conserver.
	$lignes_del = '';
	if(count($tab_classes_base['ref']))
	{
		foreach($tab_classes_base['ref'] as $id_base => $ref)
		{
			if($action!='sconet_professeurs_directeurs')
			{
				$lignes_del .= '<tr><th>'.html($ref).'</th><td>Supprimer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /> '.html($tab_classes_base['nom'][$id_base]).'</td></tr>';
			}
			else
			{
				$lignes_ras .= '<tr><th>'.html($ref).'</th><td>'.html($tab_classes_base['nom'][$id_base]).'</td></tr>';
			}
		}
	}
	// Contenu du fichier à ajouter
	$lignes_add = '';
	if(count($tab_classes_fichier['ref']))
	{
		$select_niveau = '<option value=""></option>';
		$tab_niveau_ref = array();
		$DB_TAB = DB_STRUCTURE_lister_niveaux_etablissement($_SESSION['NIVEAUX'],$listing_paliers=false);
		foreach($DB_TAB as $DB_ROW)
		{
			$select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
			$key = ($action=='sconet_eleves') ? $DB_ROW['code_mef'] : $DB_ROW['niveau_ref'] ;
			$tab_niveau_ref[$key] = $DB_ROW['niveau_id'];
		}
		foreach($tab_classes_fichier['ref'] as $i_classe => $ref)
		{
			// On préselectionne un niveau :
			// - pour sconet_eleves                 on compare avec un masque d'expression régulière
			// - pour base-eleves_eleves            on compare avec les niveaux de SACoche
			// - pour sconet_professeurs_directeurs on compare avec le début de la référence de la classe
			// - pour tableur_eleves                on compare avec le début de la référence de la classe
			$id_checked = '';
			foreach($tab_niveau_ref as $masque_recherche => $niveau_id)
			{
				if($action=='sconet_eleves')
				{
					$id_checked = (preg_match('/^'.$masque_recherche.'$/',$tab_classes_fichier['niveau'][$i_classe])) ? $niveau_id : '';
				}
				elseif($action=='base-eleves_eleves')
				{
					$id_checked = (mb_strpos($tab_classes_fichier['niveau'][$i_classe],$masque_recherche)===0) ? $niveau_id : '';
				}
				else
				{
					$id_checked = (mb_strpos($ref,$masque_recherche)===0) ? $niveau_id : '';
				}
				if($id_checked)
				{
					break;
				}
			}
			$nom_classe = ($tab_classes_fichier['nom'][$i_classe]) ? $tab_classes_fichier['nom'][$i_classe] : $ref ;
			$lignes_add .= '<tr><th><input id="add_'.$i_classe.'" name="add_'.$i_classe.'" type="checkbox" checked /> '.html($ref).'<input id="add_ref_'.$i_classe.'" name="add_ref_'.$i_classe.'" type="hidden" value="'.html($ref).'" /></th><td>Niveau : <select id="add_niv_'.$i_classe.'" name="add_niv_'.$i_classe.'">'.str_replace('value="'.$id_checked.'"','value="'.$id_checked.'" selected',$select_niveau).'</select> Nom complet : <input id="add_nom_'.$i_classe.'" name="add_nom_'.$i_classe.'" size="15" type="text" value="'.html($nom_classe).'" maxlength="20" /></td></tr>';
		}
	}
	// On enregistre (tableau mis à jour)
	$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt',serialize($tab_liens_id_base));
	// On affiche
	echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des classes.</label></p>';
	echo'<table>';
	echo' <tbody>';
	echo'  <tr><th colspan="2">Classes actuelles à conserver</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucune</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Classes nouvelles à ajouter <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_add) ? $lignes_add : '<tr><td colspan="2">Aucune</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Classes anciennes à supprimer <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_del) ? $lignes_del : ( ($action!='sconet_professeurs_directeurs') ? '<tr><td colspan="2">Aucune</td></tr>' : '<tr><td colspan="2">Sans objet, les services n\'étant pas toujours présents.</td></tr>' );
	echo' </tbody>';
	echo'</table>';
	echo'<p class="li"><a href="#step32" id="envoyer_infos_regroupements">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 32 - Traitement des actions à effectuer sur les classes (sconet_professeurs_directeurs | sconet_eleves | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==32 )
{
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
	$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
	$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
	// Récupérer les éléments postés
	$tab_add = array();
	$tab_del = array();
	foreach($_POST as $key => $val)
	{
		if( (substr($key,0,4)=='add_') && (!in_array(substr($key,0,8),array('add_ref_','add_nom_','add_niv_'))) )
		{
			$i = substr($key,4);
			$tab_add[$i]['ref'] = clean_ref($_POST['add_ref_'.$i]);
			$tab_add[$i]['nom'] = clean_ref($_POST['add_nom_'.$i]);
			$tab_add[$i]['niv'] = clean_ref($_POST['add_niv_'.$i]);
		}
		elseif(substr($key,0,4)=='del_')
		{
			$id = substr($key,4);
			$tab_del[] = clean_entier($id);
		}
	}
	// Ajouter des classes éventuelles
	$nb_add = 0;
	if(count($tab_add))
	{
		foreach($tab_add as $i => $tab)
		{
			if( (count($tab)==3) && $tab['ref'] && $tab['nom'] && $tab['niv'] )
			{
				$classe_id = DB_STRUCTURE_ajouter_groupe('classe',0,$tab['ref'],$tab['nom'],$tab['niv']);
				$nb_add++;
				$tab_i_classe_TO_id_base[$i] = (int) $classe_id;
			}
		}
	}
	// Supprimer des classes éventuelles
	$nb_del = 0;
	if(count($tab_del))
	{
		foreach($tab_del as $groupe_id)
		{
			if( $groupe_id )
			{
				DB_STRUCTURE_supprimer_groupe($groupe_id,'classe');
				$nb_del++;
			}
		}
	}
	// On enregistre (tableau mis à jour)
	$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt',serialize($tab_liens_id_base));
	// Afficher le bilan
	$lignes = '';
	$nb_fin = 0;
	$DB_TAB = DB_STRUCTURE_lister_classes_avec_niveaux();
	foreach($DB_TAB as $DB_ROW)
	{
		$lignes .= '<tr><td>'.html($DB_ROW['niveau_nom']).'</td><td>'.html($DB_ROW['groupe_ref']).'</td><td>'.html($DB_ROW['groupe_nom']).'</td></tr>'."\r\n";
		$nb_fin++;
	}
	$nb_ras = $nb_fin - $nb_add + $nb_del;
	$s_ras = ($nb_ras>1) ? 's' : '';
	$s_add = ($nb_add>1) ? 's' : '';
	$s_del = ($nb_del>1) ? 's' : '';
	$s_fin = ($nb_fin>1) ? 's' : '';
	echo'<p><label class="valide">'.$nb_ras.' classe'.$s_ras.' présente'.$s_ras.' + '.$nb_add.' classe'.$s_add.' ajoutée'.$s_add.' &minus; '.$nb_del.' classe'.$s_del.' supprimée'.$s_del.' = '.$nb_fin.' classe'.$s_fin.' résultante'.$s_fin.'.</label></p>';
	echo'<table>';
	echo' <thead>';
	echo'  <tr><th>Niveau</th><th>Référence</th><th>Nom complet</th></tr>';
	echo' </thead>';
	echo' <tbody>';
	echo   $lignes;
	echo' </tbody>';
	echo'</table>';
	$step = ( ($action=='sconet_eleves') || ($action=='sconet_professeurs_directeurs') ) ? '4' : '5' ;
	echo'<p class="li"><a href="#step'.$step.'1" id="passer_etape_suivante">Passer à l\'étape 4.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 41 - Analyse des données des groupes (sconet_professeurs_directeurs | sconet_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==41 )
{
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
	$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
	$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
	// On récupère le fichier avec les groupes : $tab_groupes_fichier['ref'] : i -> ref ; $tab_groupes_fichier['nom'] : i -> nom ; $tab_groupes_fichier['niveau'] : i -> niveau
	$tab_groupes_fichier = load_fichier('groupes');
	// On récupère le contenu de la base pour comparer : $tab_groupes_base['ref'] : id -> ref ; $tab_groupes_base['nom'] : id -> nom
	$tab_groupes_base        = array();
	$tab_groupes_base['ref'] = array();
	$tab_groupes_base['nom'] = array();
	$DB_TAB = DB_STRUCTURE_lister_groupes();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_groupes_base['ref'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_ref'];
		$tab_groupes_base['nom'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
	}
	// Contenu du fichier à conserver
	$lignes_ras = '';
	foreach($tab_groupes_fichier['ref'] as $i_groupe => $ref)
	{
		$id_base = array_search($ref,$tab_groupes_base['ref']);
		if($id_base!==false)
		{
			$lignes_ras .= '<tr><th>'.html($tab_groupes_base['ref'][$id_base]).'</th><td>'.html($tab_groupes_base['nom'][$id_base]).'</td></tr>';
			$tab_i_groupe_TO_id_base[$i_groupe] = $id_base;
			unset($tab_groupes_fichier['ref'][$i_groupe] , $tab_groupes_fichier['nom'][$i_groupe] ,  $tab_groupes_fichier['niveau'][$i_groupe] , $tab_groupes_base['ref'][$id_base] , $tab_groupes_base['nom'][$id_base]);
		}
	}
	// Contenu du fichier à supprimer
	// Pour sconet_professeurs_directeurs, les groupes ne figurent pas forcément dans le fichier si les services ne sont pas présents &rarr; on ne procède qu'à des ajouts éventuels.
	// Du coup les groupes restants ne sont pas à supprimer mais à conserver.
	$lignes_del = '';
	if(count($tab_groupes_base['ref']))
	{
		foreach($tab_groupes_base['ref'] as $id_base => $ref)
		{
			if($action!='sconet_professeurs_directeurs')
			{
				$lignes_del .= '<tr><th>'.html($ref).'</th><td>Supprimer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /> '.html($tab_groupes_base['nom'][$id_base]).'</td></tr>';
			}
			else
			{
				$lignes_ras .= '<tr><th>'.html($ref).'</th><td>'.html($tab_groupes_base['nom'][$id_base]).'</td></tr>';
			}
		}
	}
	// Contenu du fichier à ajouter
	$lignes_add = '';
	if(count($tab_groupes_fichier['ref']))
	{
		$select_niveau = '<option value=""></option>';
		$tab_niveau_ref = array();
		$DB_TAB = DB_STRUCTURE_lister_niveaux_etablissement($_SESSION['NIVEAUX'],$listing_paliers=false);
		foreach($DB_TAB as $DB_ROW)
		{
			$select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
			$key = ($action=='sconet_eleves') ? $DB_ROW['code_mef'] : $DB_ROW['niveau_ref'] ;
			$tab_niveau_ref[$key] = $DB_ROW['niveau_id'];
		}
		foreach($tab_groupes_fichier['ref'] as $i_groupe => $ref)
		{
			// On préselectionne un niveau :
			// - pour sconet_eleves                 on compare avec un masque d'expression régulière
			// - pour base-eleves_eleves            on compare avec les niveaux de SACoche
			// - pour sconet_professeurs_directeurs on compare avec le début de la référence du groupe
			// - pour tableur_eleves                on compare avec le début de la référence du groupe
			$id_checked = '';
			foreach($tab_niveau_ref as $masque_recherche => $niveau_id)
			{
				if($action=='sconet_eleves')
				{
					$id_checked = (preg_match('/^'.$masque_recherche.'$/',$tab_groupes_fichier['niveau'][$i_groupe])) ? $niveau_id : '';
				}
				else
				{
					$id_checked = (mb_strpos($ref,$masque_recherche)===0) ? $niveau_id : '';
				}
				if($id_checked)
				{
					break;
				}
			}
			$nom_groupe = ($tab_groupes_fichier['nom'][$i_groupe]) ? $tab_groupes_fichier['nom'][$i_groupe] : $ref ;
			$lignes_add .= '<tr><th><input id="add_'.$i_groupe.'" name="add_'.$i_groupe.'" type="checkbox" checked /> '.html($ref).'<input id="add_ref_'.$i_groupe.'" name="add_ref_'.$i_groupe.'" type="hidden" value="'.html($ref).'" /></th><td>Niveau : <select id="add_niv_'.$i_groupe.'" name="add_niv_'.$i_groupe.'">'.str_replace('value="'.$id_checked.'"','value="'.$id_checked.'" selected',$select_niveau).'</select> Nom complet : <input id="add_nom_'.$i_groupe.'" name="add_nom_'.$i_groupe.'" size="15" type="text" value="'.html($nom_groupe).'" maxlength="20" /></td></tr>';
		}
	}
	// On enregistre (tableau mis à jour)
	$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt',serialize($tab_liens_id_base));
	// On affiche
	echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des groupes.</label></p>';
	echo'<table>';
	echo' <tbody>';
	echo'  <tr><th colspan="2">Groupes actuels à conserver</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Groupes nouveaux à ajouter <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_add) ? $lignes_add : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Groupes anciens à supprimer <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_del) ? $lignes_del : ( ($action!='sconet_professeurs_directeurs') ? '<tr><td colspan="2">Aucun</td></tr>' : '<tr><td colspan="2">Sans objet, les services n\'étant pas toujours présents.</td></tr>' );
	echo' </tbody>';
	echo'</table>';
	echo'<p class="li"><a href="#step42" id="envoyer_infos_regroupements">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 42 - Traitement des actions à effectuer sur les groupes (sconet_professeurs_directeurs | sconet_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==42 )
{
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
	$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
	$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
	// Récupérer les éléments postés
	$tab_add = array();
	$tab_del = array();
	foreach($_POST as $key => $val)
	{
		if( (substr($key,0,4)=='add_') && (!in_array(substr($key,0,8),array('add_ref_','add_nom_','add_niv_'))) )
		{
			$i = substr($key,4);
			$tab_add[$i]['ref'] = clean_ref($_POST['add_ref_'.$i]);
			$tab_add[$i]['nom'] = clean_ref($_POST['add_nom_'.$i]);
			$tab_add[$i]['niv'] = clean_ref($_POST['add_niv_'.$i]);
		}
		elseif(substr($key,0,4)=='del_')
		{
			$id = substr($key,4);
			$tab_del[] = clean_entier($id);
		}
	}
	// Ajouter des groupes éventuels
	$nb_add = 0;
	if(count($tab_add))
	{
		foreach($tab_add as $i => $tab)
		{
			if( (count($tab)==3) && $tab['ref'] && $tab['nom'] && $tab['niv'] )
			{
				$groupe_id = DB_STRUCTURE_ajouter_groupe('groupe',0,$tab['ref'],$tab['nom'],$tab['niv']);
				$nb_add++;
				$tab_i_groupe_TO_id_base[$i] = (int) $groupe_id;
			}
		}
	}
	// Supprimer des groupes éventuels
	$nb_del = 0;
	if(count($tab_del))
	{
		foreach($tab_del as $groupe_id)
		{
			if( $groupe_id )
			{
				DB_STRUCTURE_supprimer_groupe($groupe_id,'groupe');
				$nb_del++;
			}
		}
	}
	// On enregistre (tableau mis à jour)
	$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt',serialize($tab_liens_id_base));
	// Afficher le bilan
	$lignes = '';
	$nb_fin = 0;
	$DB_TAB = DB_STRUCTURE_lister_groupes_avec_niveaux();
	foreach($DB_TAB as $DB_ROW)
	{
		$lignes .= '<tr><td>'.html($DB_ROW['niveau_nom']).'</td><td>'.html($DB_ROW['groupe_ref']).'</td><td>'.html($DB_ROW['groupe_nom']).'</td></tr>'."\r\n";
		$nb_fin++;
	}
	$nb_ras = $nb_fin - $nb_add + $nb_del;
	$s_ras = ($nb_ras>1) ? 's' : '';
	$s_add = ($nb_add>1) ? 's' : '';
	$s_del = ($nb_del>1) ? 's' : '';
	$s_fin = ($nb_fin>1) ? 's' : '';
	echo'<p><label class="valide">'.$nb_ras.' groupe'.$s_ras.' présent'.$s_ras.' + '.$nb_add.' groupe'.$s_add.' ajouté'.$s_add.' &minus; '.$nb_del.' groupe'.$s_del.' supprimé'.$s_del.' = '.$nb_fin.' groupe'.$s_fin.' résultant'.$s_fin.'.</label></p>';
	echo'<table>';
	echo' <thead>';
	echo'  <tr><th>Niveau</th><th>Référence</th><th>Nom complet</th></tr>';
	echo' </thead>';
	echo' <tbody>';
	echo   $lignes;
	echo' </tbody>';
	echo'</table>';
	echo'<p class="li"><a href="#step51" id="passer_etape_suivante">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 51 - Analyse des données des utilisateurs (sconet_professeurs_directeurs | tableur_professeurs_directeurs | sconet_eleves | sconet_parents | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==51 )
{
	$is_profil_eleve  = (mb_strpos($action,'eleves'))  ? true : false ;
	$is_profil_parent = (mb_strpos($action,'parents')) ? true : false ;
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
	$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
	$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
	// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / classe / groupes / matieres / adresse / enfant
	$tab_users_fichier = load_fichier('users');
	// On récupère le fichier avec les classes : $tab_classes_fichier['ref'] : i -> ref ; $tab_classes_fichier['nom'] : i -> nom ; $tab_classes_fichier['niveau'] : i -> niveau
	$tab_classes_fichier = load_fichier('classes');
	// On récupère le contenu de la base pour comparer : $tab_users_base['champ'] : id -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / statut / classe / adresse
	$tab_users_base               = array();
	$tab_users_base['sconet_id']  = array();
	$tab_users_base['sconet_num'] = array();
	$tab_users_base['reference']  = array();
	$tab_users_base['profil']     = array();
	$tab_users_base['nom']        = array();
	$tab_users_base['prenom']     = array();
	$tab_users_base['statut']     = array();
	$tab_users_base['classe']     = array();
	$tab_users_base['adresse']    = array();
	$profil = ($is_profil_eleve) ? 'eleve' : ( ($is_profil_parent) ? 'parent' : array('professeur','directeur') ) ;
	$with_classe = ($is_profil_eleve) ? TRUE : FALSE ;
	$DB_TAB = DB_STRUCTURE_lister_users($profil,$only_actifs=FALSE,$with_classe);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['sconet_id'][$DB_ROW['user_id']]  = $DB_ROW['user_sconet_id'];
		$tab_users_base['sconet_num'][$DB_ROW['user_id']] = $DB_ROW['user_sconet_elenoet'];
		$tab_users_base['reference'][$DB_ROW['user_id']]  = $DB_ROW['user_reference'];
		$tab_users_base['profil'][$DB_ROW['user_id']]     = $DB_ROW['user_profil'];
		$tab_users_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
		$tab_users_base['statut'][$DB_ROW['user_id']]     = $DB_ROW['user_statut'];
		$tab_users_base['classe'][$DB_ROW['user_id']]     = ($is_profil_eleve) ? $DB_ROW['groupe_ref'] : '' ;
	}
	// Pour préparer l'affichage
	$lignes_ignorer   = '';
	$lignes_ajouter   = '';
	$lignes_retirer   = '';
	$lignes_modifier  = '';
	$lignes_conserver = '';
	$lignes_inchanger = '';
	// Pour préparer l'enregistrement des données
	$tab_users_ajout = array();
	$tab_users_modif = array();
	// Comparer fichier et base : c'est parti !
	$tab_indices_fichier = array_keys($tab_users_fichier['sconet_id']);
	// Parcourir chaque entrée du fichier
	foreach($tab_indices_fichier as $i_fichier)
	{
		$id_base = false;
		// Recherche sur sconet_id
		if( (!$id_base) && ($tab_users_fichier['sconet_id'][$i_fichier]) )
		{
			$id_base = array_search($tab_users_fichier['sconet_id'][$i_fichier],$tab_users_base['sconet_id']);
		}
		// Recherche sur sconet_num
		if( (!$id_base) && ($tab_users_fichier['sconet_num'][$i_fichier]) )
		{
			$id_base = array_search($tab_users_fichier['sconet_num'][$i_fichier],$tab_users_base['sconet_num']);
		}
		// Si pas trouvé, recherche sur reference
		if( (!$id_base) && ($tab_users_fichier['reference'][$i_fichier]) )
		{
			$id_base = array_search($tab_users_fichier['reference'][$i_fichier],$tab_users_base['reference']);
		}
		// Si pas trouvé, recherche sur nom prénom
		if(!$id_base)
		{
			$tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i_fichier]);
			$tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i_fichier]);
			$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
			$nb_homonymes  = count($tab_id_commun);
			if($nb_homonymes>0)
			{
				list($inutile,$id_base) = each($tab_id_commun);
			}
		}
		// Cas [1] : présent dans le fichier, absent de la base, pas de classe dans le fichier (élèves uniquements) : contenu à ignorer (probablement des anciens élèves, ou des élèves jamais venus, qu'il est inutile d'importer)
		if( ($is_profil_eleve) && (!$id_base) && (!$tab_users_fichier['classe'][$i_fichier]) )
		{
			$indication = ($is_profil_eleve) ? $tab_users_fichier['classe'][$i_fichier] : $tab_users_fichier['profil'][$i_fichier] ;
			$lignes_ignorer .= '<tr><th>Ignorer</th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>';
		}
		// Cas [2] : présent dans le fichier, absent de la base, prof ou classe indiquée dans le fichier si élève : contenu à ajouter (nouvel élève ou nouveau professeur / directeur)
		elseif( (!$id_base) && ( (!$is_profil_eleve) || ($tab_users_fichier['classe'][$i_fichier]) ) )
		{
			$indication = ($is_profil_eleve) ? $tab_users_fichier['classe'][$i_fichier] : $tab_users_fichier['profil'][$i_fichier] ;
			$lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i_fichier.'" name="add_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>';
			$id_classe = ( ($is_profil_eleve) && isset($tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]]) ) ? $tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]] : 0 ;
			$tab_users_ajout[$i_fichier] = array( 'sconet_id'=>$tab_users_fichier['sconet_id'][$i_fichier] , 'sconet_num'=>$tab_users_fichier['sconet_num'][$i_fichier] , 'reference'=>$tab_users_fichier['reference'][$i_fichier] , 'nom'=>$tab_users_fichier['nom'][$i_fichier] , 'prenom'=>$tab_users_fichier['prenom'][$i_fichier] , 'profil'=>$tab_users_fichier['profil'][$i_fichier] , 'classe'=>$id_classe );
		}
		// Cas [3] : présent dans le fichier, présent dans la base, pas de classe dans le fichier (élèves uniquements), statut actif dans la base : contenu à retirer (probablement des élèves nouvellement sortants)
		elseif( ($is_profil_eleve) && (!$tab_users_fichier['classe'][$i_fichier]) && ($tab_users_base['statut'][$id_base]) )
		{
			$indication = ($is_profil_eleve) ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil'][$id_base] ;
			$lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').' || <b>Statut : actif &rarr; inactif</b></td></tr>';
		}
		// Cas [4] : présent dans le fichier, présent dans la base, pas de classe dans le fichier (élèves uniquements), statut inactif dans la base : contenu inchangé (probablement des anciens élèves déjà écartés)
		elseif( ($is_profil_eleve) && (!$tab_users_fichier['classe'][$i_fichier]) && (!$tab_users_base['statut'][$id_base]) )
		{
			$indication = ($is_profil_eleve) ? $tab_users_fichier['classe'][$i_fichier] : $tab_users_fichier['profil'][$i_fichier] ;
			$lignes_inchanger .= '<tr><th>Ignorer</th><td>'.html($tab_users_fichier['sconet_id'][$i_fichier].' / '.$tab_users_fichier['sconet_num'][$i_fichier].' / '.$tab_users_fichier['reference'][$i_fichier].' || '.$tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$indication.')').'</td></tr>';
		}
		else
		{
			// On compare les données de 2 enregistrements pour voir si des choses ont été modifiées
			$td_modif = '';
			$nb_modif = 0;
			$tab_champs = ($is_profil_eleve) ? array( 'sconet_id'=>'Id Sconet' , 'sconet_num'=>'n° Sconet' , 'reference'=>'Référence' , 'nom'=>'Nom' , 'prenom'=>'Prénom' , 'classe'=>'Classe' ) : array( 'sconet_id'=>'Id Sconet' , 'reference'=>'Référence' , 'profil'=>'Profil' , 'nom'=>'Nom' , 'prenom'=>'Prénom' ) ;
			foreach($tab_champs as $champ_ref => $champ_aff)
			{
				if($champ_ref=='classe')
				{
					$id_classe = (isset($tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]])) ? $tab_i_classe_TO_id_base[$tab_users_fichier['classe'][$i_fichier]] : 0 ;
					$tab_users_fichier[$champ_ref][$i_fichier] = ($id_classe) ? $tab_classes_fichier['ref'][$tab_users_fichier['classe'][$i_fichier]] : '' ;
				}
				if($tab_users_base[$champ_ref][$id_base]!=$tab_users_fichier[$champ_ref][$i_fichier])
				{
					$td_modif .= ' || <b>'.$champ_aff.' : '.html($tab_users_base[$champ_ref][$id_base]).' &rarr; '.html($tab_users_fichier[$champ_ref][$i_fichier]).'</b>';
					$tab_users_modif[$id_base][$champ_ref] = ($champ_ref!='classe') ? $tab_users_fichier[$champ_ref][$i_fichier] : $id_classe ;
					$nb_modif++;
				}
				else
				{
					$td_modif .= ' || '.$champ_aff.' : '.html($tab_users_base[$champ_ref][$id_base]);
					$tab_users_modif[$id_base][$champ_ref] = false;
				}
			}
			if(!$tab_users_base['statut'][$id_base])
			{
				$td_modif .= ' || <b>Statut : inactif &rarr; actif</b>';
				$tab_users_modif[$id_base]['statut'] = 1 ;
				$nb_modif++;
			}
			else
			{
				$tab_users_modif[$id_base]['statut'] = false ;
			}
			// Cas [5] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier si élève, statut inactif dans la base et/ou différence constatée : contenu à modifier (user revenant ou mise à jour)
			if($nb_modif)
			{
				$lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$id_base.'" name="mod_'.$id_base.'" type="checkbox" checked /></th><td>'.mb_substr($td_modif,4).'</td></tr>';
			}
			// Cas [6] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier si élève, statut actif dans la base et aucune différence constatée : contenu à conserver (contenu identique)
			else
			{
				$indication = ($is_profil_eleve) ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil'][$id_base] ;
				$lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').'</td></tr>';
			}
		}
		// Supprimer l'entrée du fichier et celle de la base éventuelle
		unset( $tab_users_fichier['sconet_id'][$i_fichier] , $tab_users_fichier['sconet_num'][$i_fichier] , $tab_users_fichier['reference'][$i_fichier] , $tab_users_fichier['nom'][$i_fichier] , $tab_users_fichier['prenom'][$i_fichier] , $tab_users_fichier['classe'][$i_fichier] );
		if($id_base)
		{
			$tab_i_fichier_TO_id_base[$i_fichier] = $id_base;
			unset( $tab_users_base['sconet_id'][$id_base] , $tab_users_base['sconet_num'][$id_base] , $tab_users_base['reference'][$id_base] , $tab_users_base['nom'][$id_base] , $tab_users_base['prenom'][$id_base] , $tab_users_base['classe'][$id_base] , $tab_users_base['statut'][$id_base] );
		}
	}
	// Parcourir chaque entrée de la base
	if(count($tab_users_base['sconet_id']))
	{
		$tab_indices_base = array_keys($tab_users_base['sconet_id']);
		foreach($tab_indices_base as $id_base)
		{
			// Cas [7] : absent dans le fichier, présent dans la base, statut actif : contenu à retirer (probablement un user nouvellement sortant)
			if($tab_users_base['statut'][$id_base])
			{
				$indication = ($is_profil_eleve) ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil'][$id_base] ;
				$lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id_base.'" name="del_'.$id_base.'" type="checkbox" checked /></th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').' || <b>Statut : actif &rarr; inactif</b></td></tr>';
			}
			// Cas [8] : absent dans le fichier, présent dans la base, statut inactif : contenu inchangé (contenu restant inactif)
			else
			{
				$indication = ($is_profil_eleve) ? $tab_users_base['classe'][$id_base] : $tab_users_base['profil'][$id_base] ;
				$lignes_inchanger .= '<tr><th>Conserver</th><td>'.html($tab_users_base['sconet_id'][$id_base].' / '.$tab_users_base['sconet_num'][$id_base].' / '.$tab_users_base['reference'][$id_base].' || '.$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base].' ('.$indication.')').'</td></tr>';
			}
			unset( $tab_users_base['sconet_id'][$id_base] , $tab_users_base['sconet_num'][$id_base] , $tab_users_base['reference'][$id_base] , $tab_users_base['nom'][$id_base] , $tab_users_base['prenom'][$id_base] , $tab_users_base['classe'][$id_base] , $tab_users_base['statut'][$id_base] );
		}
	}
	// On enregistre
	$tab_memo_analyse = array('modif'=>$tab_users_modif,'ajout'=>$tab_users_ajout);
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_memo_analyse.txt',serialize($tab_memo_analyse));
	// On enregistre (tableau mis à jour)
	$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt',serialize($tab_liens_id_base));
	// On affiche
	echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des utilisateurs.</label></p>';
	echo'<table>';
	// Cas [2]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Utilisateurs à ajouter (absents de la base, nouveaux dans le fichier). <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_ajouter) ? $lignes_ajouter : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [3] et [7]
	$texte = ($is_profil_eleve) ? ' ou sans classe affectée' : ( ($is_profil_parent) ? ' ou sans enfant au compte actif' : '' ) ;
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Utilisateurs à retirer (absents du fichier'.$texte.') <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_retirer) ? $lignes_retirer : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [5]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Utilisateurs à modifier (ou à réintégrer) <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [6]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Utilisateurs à conserver (statut actif)</th></tr>';
	echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [4] et [8]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Utilisateurs inchangés (statut inactif)</th></tr>';
	echo($lignes_inchanger) ? $lignes_inchanger : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [1]
	if($is_profil_eleve)
	{
		echo		'<tbody>';
		echo			'<tr><th colspan="2">Utilisateurs ignorés (sans classe affectée).</th></tr>';
		echo($lignes_ignorer) ? $lignes_ignorer : '<tr><td colspan="2">Aucun</td></tr>';
		echo		'</tbody>';
	}
	echo'</table>';
	echo'<p class="li"><a href="#step52" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 52 - Traitement des actions à effectuer sur les utilisateurs (sconet_professeurs_directeurs | tableur_professeurs_directeurs | sconet_eleves | sconet_parents | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==52 )
{
	$is_profil_eleve  = (mb_strpos($action,'eleves'))  ? true : false ;
	$is_profil_parent = (mb_strpos($action,'parents')) ? true : false ;
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_classe_TO_id_base = $tab_liens_id_base['classes'];
	$tab_i_groupe_TO_id_base = $tab_liens_id_base['groupes'];
	$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
	// On récupère le fichier avec des infos sur les utilisateurs : $tab_memo_analyse['modif'] : id -> array ; $tab_memo_analyse['ajout'] : i -> array
	$tab_memo_analyse = load_fichier('memo_analyse');
	// Récupérer les éléments postés
	$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
	$tab_mod = array();	// id à modifier
	$tab_add = array();	// i à ajouter
	$tab_del = array();	// id à supprimer
	foreach($tab_check as $check_infos)
	{
		if(substr($check_infos,0,4)=='mod_')
		{
			$tab_mod[] = clean_entier( substr($check_infos,4) );
		}
		elseif(substr($check_infos,0,4)=='add_')
		{
			$tab_add[] = clean_entier( substr($check_infos,4) );
		}
		elseif(substr($check_infos,0,4)=='del_')
		{
			$tab_del[] = clean_entier( substr($check_infos,4) );
		}
	}
	// Dénombrer combien d'actifs et d'inactifs au départ
	$profil = ($is_profil_eleve) ? 'eleve' : ( ($is_profil_parent) ? 'parent' : array('professeur','directeur') ) ;
	list($nb_debut_actif,$nb_debut_inactif) = DB_STRUCTURE_compter_users_suivant_statut($profil);
	// Retirer des users éventuels
	$nb_del = 0;
	if(count($tab_del))
	{
		foreach($tab_del as $id_base)
		{
			if( $id_base )
			{
				// Mettre à jour l'enregistrement
				DB_STRUCTURE_modifier_utilisateur( $id_base , array(':statut'=>0) );
				$nb_del++;
			}
		}
	}
	// Ajouter des users éventuels
	$nb_add = 0;
	$tab_password = array();
	$separateur = ';';
	$classe_ou_profil = ($is_profil_eleve) ? 'CLASSE' : 'PROFIL' ;
	$fcontenu_csv = 'SCONET_Id'.$separateur.'SCONET_N°'.$separateur.'REFERENCE'.$separateur.$classe_ou_profil.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'LOGIN'.$separateur.'MOT DE PASSE'."\r\n\r\n";
	$fcontenu_pdf_tab = array();
	if(count($tab_add))
	{
		// Récupérer les noms de classes pour le fichier avec les logins/mdp
		$tab_nom_classe = array();
		if($is_profil_eleve)
		{
			$DB_TAB = DB_STRUCTURE_lister_classes();
			foreach($DB_TAB as $DB_ROW)
			{
				$tab_nom_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
			}
		}
		foreach($tab_add as $i_fichier)
		{
			if( isset($tab_memo_analyse['ajout'][$i_fichier]) )
			{
				// Il peut théoriquement subsister un conflit de sconet_id pour des users ayant même reference, et réciproquement...
				// Construire le login
				$login = fabriquer_login($tab_memo_analyse['ajout'][$i_fichier]['prenom'] , $tab_memo_analyse['ajout'][$i_fichier]['nom'] , $tab_memo_analyse['ajout'][$i_fichier]['profil']);
				// Puis tester le login (parmi tout le personnel de l'établissement)
				if( DB_STRUCTURE_tester_login($login) )
				{
					// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
					$login = DB_STRUCTURE_rechercher_login_disponible($login);
				}
				// Construire le password
				$password = fabriquer_mdp();
				// Ajouter l'utilisateur
				$user_id = DB_STRUCTURE_ajouter_utilisateur($tab_memo_analyse['ajout'][$i_fichier]['sconet_id'],$tab_memo_analyse['ajout'][$i_fichier]['sconet_num'],$tab_memo_analyse['ajout'][$i_fichier]['reference'],$tab_memo_analyse['ajout'][$i_fichier]['profil'],$tab_memo_analyse['ajout'][$i_fichier]['nom'],$tab_memo_analyse['ajout'][$i_fichier]['prenom'],$login,$password,$tab_memo_analyse['ajout'][$i_fichier]['classe']);
				$tab_i_fichier_TO_id_base[$i_fichier] = (int) $user_id;
				$nb_add++;
				$tab_password[$user_id] = $password;
				$classe_ou_profil = ($is_profil_eleve) ? $tab_nom_classe[$tab_memo_analyse['ajout'][$i_fichier]['classe']] : mb_strtoupper($tab_memo_analyse['ajout'][$i_fichier]['profil']) ;
				$fcontenu_csv .= $tab_memo_analyse['ajout'][$i_fichier]['sconet_id'].$tab_memo_analyse['ajout'][$i_fichier]['sconet_num'].$separateur.$tab_memo_analyse['ajout'][$i_fichier]['reference'].$separateur.$classe_ou_profil.$separateur.$tab_memo_analyse['ajout'][$i_fichier]['nom'].$separateur.$tab_memo_analyse['ajout'][$i_fichier]['prenom'].$separateur.$login.$separateur.$password."\r\n";
				$ligne1 = $classe_ou_profil;
				$ligne2 = $tab_memo_analyse['ajout'][$i_fichier]['nom'].' '.$tab_memo_analyse['ajout'][$i_fichier]['prenom'];
				$ligne3 = 'Utilisateur : '.$login;
				$ligne4 = 'Mot de passe : '.$password;
				$fcontenu_pdf_tab[] = $ligne1."\r\n".$ligne2."\r\n".$ligne3."\r\n".$ligne4;
			}
		}
	}
	// Modifier des users éventuels
	$nb_mod = 0;
	if(count($tab_mod))
	{
		foreach($tab_mod as $id_base)
		{
			// Il peut théoriquement subsister un conflit de sconet_id pour des users ayant même reference, et réciproquement...
			$tab_champs = ($is_profil_eleve) ? array( 'sconet_id' , 'sconet_num' , 'reference' , 'classe' , 'nom' , 'prenom' , 'statut' ) : array( 'sconet_id' , 'reference' , 'profil' , 'nom' , 'prenom' , 'statut' ) ;
			$DB_VAR  = array();
			foreach($tab_champs as $champ_ref)
			{
				if($tab_memo_analyse['modif'][$id_base][$champ_ref] !== false)
				{
					$DB_VAR[':'.$champ_ref] = $tab_memo_analyse['modif'][$id_base][$champ_ref];
				}
			}
			// bilan
			if( count($DB_VAR) )
			{
				DB_STRUCTURE_modifier_utilisateur( $id_base , $DB_VAR );
			}
			$nb_mod++;
		}
	}
	// On enregistre (tableau mis à jour)
	$tab_liens_id_base = array('classes'=>$tab_i_classe_TO_id_base,'groupes'=>$tab_i_groupe_TO_id_base,'users'=>$tab_i_fichier_TO_id_base);
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt',serialize($tab_liens_id_base));
	// Afficher le bilan
	$tab_statut = array(0=>'inactif',1=>'actif');
	$lignes         = '';
	$nb_fin_actif   = 0;
	$nb_fin_inactif = 0;
	$profil = ($is_profil_eleve) ? 'eleve' : ( ($is_profil_parent) ? 'parent' : array('professeur','directeur') ) ;
	$with_classe = ($is_profil_eleve) ? TRUE : FALSE ;
	$DB_TAB = DB_STRUCTURE_lister_users($profil,$only_actifs=FALSE,$with_classe,$tri_statut=TRUE);
	foreach($DB_TAB as $DB_ROW)
	{
		$class       = (isset($tab_password[$DB_ROW['user_id']])) ? ' class="new"' : '' ;
		$td_password = (isset($tab_password[$DB_ROW['user_id']])) ? '<td class="new">'.html($tab_password[$DB_ROW['user_id']]).'</td>' : '<td class="i">champ crypté</td>' ;
		if($DB_ROW['user_statut']) {$nb_fin_actif++;} else {$nb_fin_inactif++;}
		$champ = ($is_profil_eleve) ? $DB_ROW['groupe_ref'] : $DB_ROW['user_profil'] ;
		$lignes .= '<tr'.$class.'><td>'.html($DB_ROW['user_sconet_id']).'</td><td>'.html($DB_ROW['user_sconet_elenoet']).'</td><td>'.html($DB_ROW['user_reference']).'</td><td>'.html($champ).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td'.$class.'>'.html($DB_ROW['user_login']).'</td>'.$td_password.'<td>'.$tab_statut[$DB_ROW['user_statut']].'</td></tr>'."\r\n";
	}
	$s_debut_actif   = ($nb_debut_actif>1)   ? 's' : '';
	$s_debut_inactif = ($nb_debut_inactif>1) ? 's' : '';
	$s_fin_actif     = ($nb_fin_actif>1)     ? 's' : '';
	$s_fin_inactif   = ($nb_fin_inactif>1)   ? 's' : '';
	$s_mod = ($nb_mod>1) ? 's' : '';
	$s_add = ($nb_add>1) ? 's' : '';
	$s_del = ($nb_del>1) ? 's' : '';
	if($nb_add)
	{
		// On archive les nouveaux identifiants dans un fichier tableur zippé (csv tabulé)
		$profil = ($is_profil_eleve) ? 'eleve' : ( ($is_profil_parent) ? 'parent' : array('professeur','directeur') ) ;
		$fnom = 'identifiants_'.$_SESSION['BASE'].'_'.$profil.'_'.time();
		$zip = new ZipArchive();
		$result_open = $zip->open($dossier_login_mdp.$fnom.'.zip', ZIPARCHIVE::CREATE);
		if($result_open!==TRUE)
		{
			require('./_inc/tableau_zip_error.php');
			exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
		}
		$zip->addFromString($fnom.'.csv',csv($fcontenu_csv));
		$zip->close();
		// On archive les nouveaux identifiants dans un fichier pdf (classe fpdf + script étiquettes)
		require_once('./_lib/FPDF/PDF_Label.php');
		$pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>11));
		$pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
		$pdf -> AddPage();
		$pdf -> SetFillColor(245,245,245);
		$pdf -> SetDrawColor(145,145,145);
		sort($fcontenu_pdf_tab);
		foreach($fcontenu_pdf_tab as $text)
		{
			$pdf -> Add_Label(pdf($text));
		}
		$pdf->Output($dossier_login_mdp.$fnom.'.pdf','F');
	}
	$champ = ($is_profil_eleve) ? 'Classe' : 'Profil' ;
	echo'<p><label class="valide">'.$nb_debut_actif.' utilisateur'.$s_debut_actif.' actif'.$s_debut_actif.' et '.$nb_debut_inactif.' utilisateur'.$s_debut_inactif.' inactif'.$s_debut_inactif.' &rarr; '.$nb_mod.' utilisateur'.$s_mod.' modifié'.$s_mod.' + '.$nb_add.' utilisateur'.$s_add.' ajouté'.$s_add.' &minus; '.$nb_del.' utilisateur'.$s_del.' retiré'.$s_del.' &rarr; '.$nb_fin_actif.' utilisateur'.$s_fin_actif.' actif'.$s_fin_actif.' et '.$nb_fin_inactif.' utilisateur'.$s_fin_inactif.' inactif'.$s_fin_inactif.'.</label></p>';
	echo'<table>';
	echo' <thead>';
	echo'  <tr><th>Id Sconet</th><th>N° Sconet</th><th>Référence</th><th>'.$champ.'</th><th>Nom</th><th>Prénom</th><th>Login</th><th>Mot de passe</th><th>Statut</th></tr>';
	echo' </thead>';
	echo' <tbody>';
	echo   $lignes;
	echo' </tbody>';
	echo'</table>';
	if($nb_add)
	{
		echo'<p class="li"><a href="#" class="step53">Récupérer les identifiants de tout nouvel utilisateur inscrit.</a><input id="archive" name="archive" type="hidden" value="'.$fnom.'" /><label id="ajax_msg">&nbsp;</label></p>';
	}
	else
	{
		echo'<p class="astuce">Il n\'y a aucun nouvel utilisateur inscrit, donc pas d\'identifiants à récupérer.</p>';
		switch($action)
		{
			case 'sconet_eleves' :                  $etape = 6; $step = 61; break;
			case 'sconet_parents' :                 $etape = 4; $step = 71; break;
			case 'sconet_professeurs_directeurs' :  $etape = 6; $step = 61; break;
			case 'tableur_eleves' :                 $etape = 5; $step = 90; break;
			case 'tableur_professeurs_directeurs' : $etape = 4; $step = 90; break;
			case 'base-eleves_eleves' :             $etape = 5; $step = 90; break;
		}
		echo'<p class="li"><a href="#step'.$step.'" id="passer_etape_suivante">Passer à l\'étape '.$etape.'.</a><label id="ajax_msg">&nbsp;</label></p>';
	}
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 53 - Récupérer les identifiants des nouveaux utilisateurs (sconet_professeurs_directeurs | tableur_professeurs_directeurs | sconet_eleves | sconet_parents | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==53 )
{
	$archive = (isset($_POST['archive'])) ? $_POST['archive'] : '';
	if(!$archive)
	{
		exit('Erreur : le nom du fichier contenant les identifiants est manquant !');
	}
	echo'<p><label class="alerte">Voici les identifiants des nouveaux inscrits :</label></p>';
	echo'<ul class="puce">';
	echo' <li><a class="lien_ext" href="'.$dossier_login_mdp.$archive.'.pdf">Archiver / Imprimer (étiquettes <em>pdf</em>).</a></li>';
	echo' <li><a class="lien_ext" href="'.$dossier_login_mdp.$archive.'.zip">Récupérer / Manipuler (fichier <em>csv</em> pour tableur).</a></li>';
	echo'</ul>';
	echo'<p class="danger">Les mots de passe, cryptés, ne sont plus accessibles ultérieurement !</p>';
	switch($action)
	{
		case 'sconet_eleves' :                  $etape = 6; $step = 61; break;
		case 'sconet_parents' :                 $etape = 4; $step = 71; break;
		case 'sconet_professeurs_directeurs' :  $etape = 6; $step = 61; break;
		case 'tableur_eleves' :                 $etape = 5; $step = 90; break;
		case 'tableur_professeurs_directeurs' : $etape = 4; $step = 90; break;
		case 'base-eleves_eleves' :             $etape = 5; $step = 90; break;
	}
	echo'<p class="li"><a href="#step'.$step.'" id="passer_etape_suivante">Passer à l\'étape '.$etape.'.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 61 - Ajouts d'affectations éventuelles (sconet_professeurs_directeurs | sconet_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==61 )
{
	$lignes_classes   = '';
	$lignes_principal = '';
	$lignes_matieres  = '';
	$lignes_groupes   = '';
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['classes'] -> $tab_i_classe_TO_id_base ; $tab_liens_id_base['groupes'] -> $tab_i_groupe_TO_id_base ; $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_classe_TO_id_base  = $tab_liens_id_base['classes'];
	$tab_i_groupe_TO_id_base  = $tab_liens_id_base['groupes'];
	$tab_i_fichier_TO_id_base = $tab_liens_id_base['users'];
	// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / classe / groupes / matieres / adresse / enfant
	$tab_users_fichier = load_fichier('users');
	//
	// Pour sconet_professeurs_directeurs, il faut regarder les associations profs/classes & profs/PP + profs/matières + profs/groupes.
	// Pour sconet_eleves, il faut juste à regarder les associations élèves/groupes.
	//
	if($action=='sconet_professeurs_directeurs')
	{
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// associations profs/classes
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// Garder trace des propositions d'ajouts profs/classes pour faire le lien avec les propositions d'ajouts profs/pp
		$tab_ajout_asso_prof_classe = array();
		// On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=true $tab_base_classe[groupe_id]=groupe_nom
		// En deux requêtes sinon on ne récupère pas les groupes sans utilisateurs affectés.
		$tab_base_classe = array();
		$DB_TAB = DB_STRUCTURE_lister_classes();
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_base_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
		}
		$tab_base_affectation = array();
		$DB_TAB = DB_STRUCTURE_lister_professeurs_avec_classes();
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = true;
		}
		// Parcourir chaque entrée du fichier à la recherche d'affectations profs/classes
		foreach( $tab_users_fichier['classe'] as $i_fichier => $tab_classes )
		{
			if(count($tab_classes))
			{
				foreach( $tab_classes as $i_classe => $classe_pp )
				{
					// On a trouvé une telle affectation ; comparer avec ce que contient la base
					if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_classe_TO_id_base[$i_classe])) )
					{
						$user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
						$groupe_id = $tab_i_classe_TO_id_base[$i_classe];
						if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
						{
							$lignes_classes .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>';
						}
						else
						{
							$tab_ajout_asso_prof_classe[$user_id.'_'.$groupe_id] = true;
							$lignes_classes .= '<tr><th>Ajouter <input id="add_classe_'.$user_id.'_'.$groupe_id.'_1" name="add_classe_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>';
						}
					}
				}
			}
		}
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// associations profs/PP
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=true ($tab_base_classe déjà renseigné)
		$tab_base_affectation = array();
		$DB_TAB = DB_STRUCTURE_lister_jointure_professeurs_principaux();
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = true;
		}
		// Parcourir chaque entrée du fichier à la recherche d'affectations profs/PP
		foreach( $tab_users_fichier['classe'] as $i_fichier => $tab_classes )
		{
			if(count($tab_classes))
			{
				foreach( $tab_classes as $i_classe => $classe_pp )
				{
					if($classe_pp=='PP')
					{
						// On a trouvé une telle affectation ; comparer avec ce que contient la base
						if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_classe_TO_id_base[$i_classe])) )
						{
							$user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
							$groupe_id = $tab_i_classe_TO_id_base[$i_classe];
							if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
							{
								$lignes_principal .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>';
							}
							else
							{
								$value = (isset($tab_ajout_asso_prof_classe[$user_id.'_'.$groupe_id])) ? 0 : 1 ;
								$lignes_principal .= '<tr><th>Ajouter <input id="add_pp_'.$user_id.'_'.$groupe_id.'_'.$value.'" name="add_pp_'.$user_id.'_'.$groupe_id.'_'.$value.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_classe[$groupe_id]).'</td></tr>';
							}
						}
					}
				}
			}
		}
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// associations profs/matières
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		// On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_matiere_id]=true + $tab_base_matiere[matiere_id]=matiere_nom + $tab_matiere_ref_TO_id_base[matiere_ref]=id_base
		// En deux requêtes sinon on ne récupère pas les matieres sans utilisateurs affectés.
		$tab_base_matiere = array();
		$tab_matiere_ref_TO_id_base = array();
		$DB_TAB = DB_STRUCTURE_lister_matieres_etablissement($_SESSION['MATIERES'],$with_transversal=false,$order_by_name=true);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_base_matiere[$DB_ROW['matiere_id']] = $DB_ROW['matiere_nom'];
			$tab_matiere_ref_TO_id_base[$DB_ROW['matiere_ref']] = $DB_ROW['matiere_id'];
		}
		$tab_base_affectation = array();
		$DB_TAB = DB_STRUCTURE_lister_jointure_professeurs_matieres();
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['matiere_id']] = true;
		}
		// Parcourir chaque entrée du fichier à la recherche d'affectations profs/matières
		foreach( $tab_users_fichier['matiere'] as $i_fichier => $tab_matieres )
		{
			if(count($tab_matieres))
			{
				foreach( $tab_matieres as $matiere_code => $type_rattachement ) // $type_rattachement vaut 'discipline' ou 'service'
				{
					// On a trouvé une telle affectation ; comparer avec ce que contient la base
					if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_matiere_ref_TO_id_base[$matiere_code])) )
					{
						$user_id    = $tab_i_fichier_TO_id_base[$i_fichier];
						$matiere_id = $tab_matiere_ref_TO_id_base[$matiere_code];
						if(isset($tab_base_affectation[$user_id.'_'.$matiere_id]))
						{
							$lignes_matieres .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_matiere[$matiere_id]).'</td></tr>';
						}
						else
						{
							$lignes_matieres .= '<tr><th>Ajouter <input id="add_matiere_'.$user_id.'_'.$matiere_id.'_1" name="add_matiere_'.$user_id.'_'.$matiere_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_matiere[$matiere_id]).'</td></tr>';
						}
					}
				}
			}
		}
	}
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// associations profs/groupes ou élèves/groupes
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	// On récupère le contenu de la base pour comparer : $tab_base_affectation[user_id_groupe_id]=true et $tab_base_groupe[groupe_id]=groupe_nom
	// En deux requêtes sinon on ne récupère pas les groupes sans utilisateurs affectés.
	$tab_base_groupe = array();
	$DB_TAB = DB_STRUCTURE_lister_groupes();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_base_groupe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
	}
	$tab_base_affectation = array();
	$profil_eleve = ($action=='sconet_eleves') ? true : false ;
	$DB_TAB = DB_STRUCTURE_lister_users_avec_groupe($profil_eleve,$prof_id=0,$only_actifs=true);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_base_affectation[$DB_ROW['user_id'].'_'.$DB_ROW['groupe_id']] = true;
	}
	// Parcourir chaque entrée du fichier à la recherche d'affectations utilisateurs/groupes
	foreach( $tab_users_fichier['groupe'] as $i_fichier => $tab_groupes )
	{
		if(count($tab_groupes))
		{
			foreach( $tab_groupes as $i_groupe => $groupe_ref )
			{
				// On a trouvé une telle affectation ; comparer avec ce que contient la base
				if( (isset($tab_i_fichier_TO_id_base[$i_fichier])) && (isset($tab_i_groupe_TO_id_base[$i_groupe])) )
				{
					$user_id   = $tab_i_fichier_TO_id_base[$i_fichier];
					$groupe_id = $tab_i_groupe_TO_id_base[$i_groupe];
					if(isset($tab_base_affectation[$user_id.'_'.$groupe_id]))
					{
						$lignes_groupes .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>';
					}
					else
					{
						$lignes_groupes .= '<tr><th>Ajouter <input id="add_groupe_'.$user_id.'_'.$groupe_id.'_1" name="add_groupe_'.$user_id.'_'.$groupe_id.'_1" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_base_groupe[$groupe_id]).'</td></tr>';
					}
				}
			}
		}
	}
	// On affiche
	echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des affectations éventuelles.</label></p>';
	echo'<table>';
	if($action=='sconet_professeurs_directeurs')
	{
		echo		'<tbody>';
		echo			'<tr><th colspan="3">Associations utilisateurs / classes. <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
		echo($lignes_classes) ? $lignes_classes : '<tr><td colspan="3">Aucune</td></tr>';
		echo		'</tbody>';
		echo		'<tbody>';
		echo			'<tr><th colspan="3">Associations utilisateurs / p.principal. <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
		echo($lignes_principal) ? $lignes_principal : '<tr><td colspan="3">Aucune</td></tr>';
		echo		'</tbody>';
		echo		'<tbody>';
		echo			'<tr><th colspan="3">Associations utilisateurs / matières. <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
		echo($lignes_matieres) ? $lignes_matieres : '<tr><td colspan="3">Aucune</td></tr>';
		echo		'</tbody>';
	}
	echo		'<tbody>';
	echo			'<tr><th colspan="3">Associations utilisateurs / groupes. <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_groupes) ? $lignes_groupes : '<tr><td colspan="3">Aucune</td></tr>';
	echo		'</tbody>';
	echo'</table>';
	echo'<p class="li"><a href="#step62" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 62 - Traitement des ajouts d'affectations éventuelles (sconet_professeurs_directeurs | sconet_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==62 )
{
	$user_profil = (mb_strpos($action,'eleves')) ? 'eleve' : 'professeur' ;
	// Récupérer les éléments postés
	$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
	$tab_post = array( 'classe'=>array() , 'pp'=>array() , 'matiere'=>array() , 'groupe'=>array() );
	foreach($tab_check as $check_infos)
	{
		if(substr($check_infos,0,4)=='add_')
		{
			list($add,$obj,$id1,$id2,$val) = explode('_',$check_infos);
			$tab_post[$obj][$id1][$id2] = $val;
		}
	}
	// Ajouter des associations users/classes (profs uniquements, pour les élèves c'est fait à l'étape 52)
	$nb_asso_classes = count($tab_post['classe']);
	if($nb_asso_classes)
	{
		foreach($tab_post['classe'] as $user_id => $tab_id2)
		{
			foreach($tab_id2 as $classe_id => $val)
			{
				DB_STRUCTURE_modifier_liaison_user_groupe($user_id,'classes',$classe_id,'classe',true);
			}
		}
	}
	// Ajouter des associations users/pp (profs uniquements)
	$nb_asso_pps = count($tab_post['pp']);
	if($nb_asso_pps)
	{
		foreach($tab_post['pp'] as $user_id => $tab_id2)
		{
			foreach($tab_id2 as $classe_id => $val)
			{
				// Ne pas effectuer l'association de PP avec une classe si l'association avec cette classe a été décochée
				if( ($val==1) || isset($tab_post['classe'][$user_id][$classe_id]) )
				{
					DB_STRUCTURE_modifier_liaison_professeur_principal($user_id,$classe_id,true);
				}
			}
		}
	}
	// Ajouter des associations users/matières (profs uniquements)
	$nb_asso_matieres = count($tab_post['matiere']);
	if($nb_asso_matieres)
	{
		foreach($tab_post['matiere'] as $user_id => $tab_id2)
		{
			foreach($tab_id2 as $matiere_id => $val)
			{
				DB_STRUCTURE_modifier_liaison_professeur_matiere($user_id,$matiere_id,true);
			}
		}
	}
	// Ajouter des associations users/groupes (profs ou élèves)
	$nb_asso_groupes = count($tab_post['groupe']);
	if($nb_asso_groupes)
	{
		foreach($tab_post['groupe'] as $user_id => $tab_id2)
		{
			foreach($tab_id2 as $groupe_id => $val)
			{
				DB_STRUCTURE_modifier_liaison_user_groupe($user_id,$user_profil,$groupe_id,'groupe',true);
			}
		}
	}
	// Afficher le résultat
	if($action=='sconet_professeurs_directeurs')
	{
		echo'<p><label class="valide">Nouvelles associations utilisateurs / classes effectuées : '.$nb_asso_classes.'</label></p>';
		echo'<p><label class="valide">Nouvelles associations utilisateurs / p.principal effectuées : '.$nb_asso_pps.'</label></p>';
		echo'<p><label class="valide">Nouvelles associations utilisateurs / matières effectuées : '.$nb_asso_matieres.'</label></p>';
	}
	echo'<p><label class="valide">Nouvelles associations utilisateurs / groupes effectuées : '.$nb_asso_groupes.'</label></p>';
	echo'<p class="li"><a href="#step90" id="passer_etape_suivante">Passer à l\'étape 7.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 71 - Adresses des parents (sconet_parents)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==71 )
{
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
	// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / classe / groupes / matieres / adresse / enfant
	$fnom = $dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_users.txt';
	if(!file_exists($fnom))
	{
		exit('Erreur : le fichier contenant les utilisateurs est introuvable !');
	}
	$contenu = file_get_contents($fnom);
	$tab_users_fichier = @unserialize($contenu);
	if($tab_users_fichier===FALSE)
	{
		exit('Erreur : le fichier contenant les utilisateurs est syntaxiquement incorrect !');
	}
	// On récupère le contenu de la base pour comparer : $tab_base_adresse[user_id]=array()
	$tab_base_adresse = array();
	$DB_TAB = DB_STRUCTURE_lister_adresses_parents();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_base_adresse[$DB_ROW['parent_id']] = array( $DB_ROW['adresse_ligne1'] , $DB_ROW['adresse_ligne2'] , $DB_ROW['adresse_ligne3'] , $DB_ROW['adresse_ligne4'] , (int)$DB_ROW['adresse_postal_code'] , $DB_ROW['adresse_postal_libelle'] , $DB_ROW['adresse_pays_nom'] );
	}
	// Pour préparer l'affichage
	$lignes_ajouter   = '';
	$lignes_modifier  = '';
	$lignes_conserver = '';
	// Pour préparer l'enregistrement des données
	$tab_users_ajout = array();
	$tab_users_modif = array();
	// Parcourir chaque entrée du fichier
	foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
	{
		// Cas [1] : parent présent dans le fichier, adresse absente de la base : il vient d'être ajouté, on ajoute aussi son adresse, sauf si elle est vide (on ne teste pas le pays qui vaut FRANCE par défaut dans l'export Sconet).
		if(!isset($tab_base_adresse[$id_base]))
		{
			if( $tab_users_fichier['adresse'][$i_fichier][0] || $tab_users_fichier['adresse'][$i_fichier][1] || $tab_users_fichier['adresse'][$i_fichier][2] || $tab_users_fichier['adresse'][$i_fichier][3] || $tab_users_fichier['adresse'][$i_fichier][4] || $tab_users_fichier['adresse'][$i_fichier][5] )
			{
				$lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i_fichier.'" name="add_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.html($tab_users_fichier['adresse'][$i_fichier][0].' / '.$tab_users_fichier['adresse'][$i_fichier][1].' / '.$tab_users_fichier['adresse'][$i_fichier][2].' / '.$tab_users_fichier['adresse'][$i_fichier][3].' / '.$tab_users_fichier['adresse'][$i_fichier][4].' / '.$tab_users_fichier['adresse'][$i_fichier][5].' / '.$tab_users_fichier['adresse'][$i_fichier][6]).'</td></tr>';
			}
		}
		// Cas [2] : parent présent dans le fichier, adresse présente de la base
		else
		{
			$nb_differences = 0;
			$td_contenu = array();
			for($indice=0 ; $indice<7 ; $indice++)
			{
				if($tab_users_fichier['adresse'][$i_fichier][$indice]==$tab_base_adresse[$id_base][$indice])
				{
					$td_contenu[] = html($tab_base_adresse[$id_base][$indice]);
				}
				else
				{
					$td_contenu[] = '<b>'.html($tab_base_adresse[$id_base][$indice]).' &rarr; '.html($tab_users_fichier['adresse'][$i_fichier][$indice]).'</b>';
					$nb_differences++;
				}
			}
			if($nb_differences==0)
			{
				// Cas [2a] : adresses identiques &rarr; conserver
				$lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.implode(' || ',$td_contenu).'</td></tr>';
			}
			else
			{
				// Cas [2b] : adresses différentes &rarr; modifier
				$lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$i_fichier.'" name="mod_'.$i_fichier.'" type="checkbox" checked /></th><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>'.implode(' || ',$td_contenu).'</td></tr>';
			}
		}
	}
	// On affiche
	echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des adresses.</label></p>';
	echo'<table>';
	// Cas [1]
	echo		'<tbody>';
	echo			'<tr><th colspan="3">Adresses à ajouter <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_ajouter) ? $lignes_ajouter : '<tr><td colspan="3">Aucune</td></tr>';
	echo		'</tbody>';
	// Cas [2b]
	echo		'<tbody>';
	echo			'<tr><th colspan="3">Adresses à modifier <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="3">Aucune</td></tr>';
	echo		'</tbody>';
	// Cas [2a]
	echo		'<tbody>';
	echo			'<tr><th colspan="3">Adresses à conserver</th></tr>';
	echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="3">Aucune</td></tr>';
	echo		'</tbody>';
	echo'</table>';
	echo'<p class="li"><a href="#step72" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 72 - Traitement des ajouts/modifications d'adresses éventuelles (sconet_parents)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==72 )
{
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
	// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / classe / groupes / matieres / adresse / enfant
	$tab_users_fichier = load_fichier('users');
	// Récupérer les éléments postés et ajouter/modifier les adresses
	$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
	$nb_add = 0;
	$nb_mod = 0;
	foreach($tab_check as $check_infos)
	{
		if(substr($check_infos,0,4)=='mod_')
		{
			$i_fichier = clean_entier( substr($check_infos,4) );
			if( isset($tab_i_fichier_TO_id_base[$i_fichier]) && isset($tab_users_fichier['adresse'][$i_fichier]) )
			{
				DB_STRUCTURE_modifier_adresse_parent( $tab_i_fichier_TO_id_base[$i_fichier] , $tab_users_fichier['adresse'][$i_fichier] );
				$nb_mod++;
			}
		}
		elseif(substr($check_infos,0,4)=='add_')
		{
			$i_fichier = clean_entier( substr($check_infos,4) );
			if( isset($tab_i_fichier_TO_id_base[$i_fichier]) && isset($tab_users_fichier['adresse'][$i_fichier]) )
			{
				DB_STRUCTURE_ajouter_adresse_parent( $tab_i_fichier_TO_id_base[$i_fichier] , $tab_users_fichier['adresse'][$i_fichier] );
				$nb_add++;
			}
		}
	}
	// Afficher le résultat
	echo'<p><label class="valide">Nouvelles adresses ajoutées : '.$nb_add.'</label></p>';
	echo'<p><label class="valide">Anciennes adresses modifiées : '.$nb_mod.'</label></p>';
	echo'<p class="li"><a href="#step81" id="passer_etape_suivante">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 81 - Liens de responsabilités des parents (sconet_parents)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==81 )
{
	// On récupère le fichier avec des infos sur les correspondances : $tab_liens_id_base['users'] -> $tab_i_fichier_TO_id_base
	$tab_liens_id_base = load_fichier('liens_id_base');
	$tab_i_fichier_TO_id_base  = $tab_liens_id_base['users'];
	// On récupère le fichier avec les utilisateurs : $tab_users_fichier['champ'] : i -> valeur, avec comme champs : sconet_id / sconet_num / reference / profil / nom / prenom / classe / groupes / matieres / adresse / enfant
	$tab_users_fichier = load_fichier('users');
	// On convertit les données du fichier parent=>enfant dans un tableau enfant=>parent
	$tab_fichier_parents_par_eleve = array();
	foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
	{
		if( (isset($tab_users_fichier['enfant'][$i_fichier])) && (count($tab_users_fichier['enfant'][$i_fichier])) )
		{
			foreach($tab_users_fichier['enfant'][$i_fichier] as $eleve_id => $resp_legal_num)
			{
				$tab_fichier_parents_par_eleve[$eleve_id][$i_fichier] = $resp_legal_num;
			}
		}
	}
	// On récupère le contenu de la base pour comparer : $tab_base_parents_par_eleve[eleve_sconet_id]=array( 'eleve'=>(eleve_id,eleve_nom,eleve_prenom) , 'parent'=>array(parent_sconet_id=>(parent_id,parent_nom,parent_prenom,num)) )
	$tab_base_parents_par_eleve = array();
	$DB_TAB = DB_STRUCTURE_lister_parents_par_eleve();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_infos_eleve  = array( 'id'=>(int)$DB_ROW['eleve_id']  , 'nom'=>$DB_ROW['eleve_nom']  , 'prenom'=>$DB_ROW['eleve_prenom'] );
		if( ($DB_ROW['parent_id']) && ($DB_ROW['parent_sconet_id']) ) // eleve_sconet_id est déjà filtré lors de la requête
		{
			$tab_infos_parent = array( 'id'=>(int)$DB_ROW['parent_id'] , 'nom'=>$DB_ROW['parent_nom'] , 'prenom'=>$DB_ROW['parent_prenom'] , 'sconet_id'=>(int)$DB_ROW['parent_sconet_id'] );
			if(!isset($tab_base_parents_par_eleve[(int)$DB_ROW['eleve_sconet_id']]))
			{
				$tab_base_parents_par_eleve[(int)$DB_ROW['eleve_sconet_id']] = array( 'eleve'=>$tab_infos_eleve , 'parent'=>array((int)$DB_ROW['resp_legal_num']=>$tab_infos_parent) );
			}
			else
			{
				$tab_base_parents_par_eleve[(int)$DB_ROW['eleve_sconet_id']]['parent'][$DB_ROW['resp_legal_num']] = $tab_infos_parent;
			}
		}
		else
		{
			// Cas d'un élève sans parent affecté ou un parent sans id Sconet
			if(!isset($tab_base_parents_par_eleve[$DB_ROW['eleve_sconet_id']]))
			{
				$tab_base_parents_par_eleve[$DB_ROW['eleve_sconet_id']] = array( 'eleve'=>$tab_infos_eleve , 'parent'=>array() );
			}
		}
	}
	// On enregistre une copie du tableau $tab_fichier_parents_par_eleve mais en remplaçant les id sconet par les id de la base (on partira de lui pour récupérer les identifiants à ajouter / modifier).
	// Il faut le faire maintenant car ensuite tab_base_parents_par_eleve est peu à peu vidé.
	$tab_memo_analyse = array();
	foreach($tab_fichier_parents_par_eleve as $eleve_sconet_id => $tab_parent)
	{
		foreach($tab_parent as $i_fichier => $resp_legal_num)
		{
			// Au passage, on vérifie que l'élève est dans la base avec son id sconet, sinon c'est cuit...
			if(isset($tab_base_parents_par_eleve[$eleve_sconet_id]))
			{
				$eleve_id  = $tab_base_parents_par_eleve[$eleve_sconet_id]['eleve']['id'];
				$parent_id = $tab_i_fichier_TO_id_base[$i_fichier];
				$tab_memo_analyse[$eleve_id][$parent_id] = $resp_legal_num;
			}
		}
	}
	Ecrire_Fichier($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_memo_analyse.txt',serialize($tab_memo_analyse));
	// Pour préparer l'affichage
	$lignes_modifier  = '';
	$lignes_conserver = '';
	// Pour préparer l'enregistrement des données
	$tab_users_modif = array();
	// Parcourir chaque élève de la base
	foreach($tab_base_parents_par_eleve as $eleve_sconet_id => $tab_base_eleve_infos)
	{
		if(isset($tab_fichier_parents_par_eleve[$eleve_sconet_id])) // Si on ne trouve aucun parent dans le fichier, on laisse tomber, c'est peut être un vieux compte
		{
			$nb_differences = 0;
			$td_contenu = array();
			// On fait des modifs s'il n'y a pas le même nombre de responsables ou si un responsable est différent ou si l'ordre des responsables est différent
			$num = 1;
			while( count($tab_fichier_parents_par_eleve[$eleve_sconet_id]) || count($tab_base_eleve_infos['parent']) )
			{
				$parent_id_fichier     = array_search($num,$tab_fichier_parents_par_eleve[$eleve_sconet_id]);
				$parent_sconet_id_base = (isset($tab_base_eleve_infos['parent'][$num])) ? $tab_base_eleve_infos['parent'][$num]['sconet_id'] : FALSE ;
				$parent_affich_fichier = ($parent_id_fichier===FALSE)     ? 'X' : $tab_users_fichier['nom'][$parent_id_fichier].' '.$tab_users_fichier['prenom'][$parent_id_fichier] ;
				$parent_affich_base    = ($parent_sconet_id_base===FALSE) ? 'X' : $tab_base_eleve_infos['parent'][$num]['nom'].' '.$tab_base_eleve_infos['parent'][$num]['prenom'] ;
				$parent_sconet_id_fichier = ($parent_id_fichier!==FALSE)  ? $tab_users_fichier['sconet_id'][$parent_id_fichier] : FALSE ;
				if($parent_sconet_id_fichier===$parent_sconet_id_base)
				{
					if($parent_affich_base!='X')
					{
						$td_contenu[] = 'Responsable n°'.$num.' : '.html($parent_affich_base);
					}
				}
				else
				{
					$td_contenu[] = 'Responsable n°'.$num.' : <b>'.html($parent_affich_base).' &rarr; '.html($parent_affich_fichier).'</b>';
					$nb_differences++;
				}
				if($parent_id_fichier!==FALSE)
				{
					unset($tab_fichier_parents_par_eleve[$eleve_sconet_id][$parent_id_fichier]);
				}
				if($parent_sconet_id_base)
				{
					unset($tab_base_eleve_infos['parent'][$num]);
				}
				$num++;
			}
			if($nb_differences==0)
			{
				// Cas [1] : responsables identiques &rarr; conserver
				$lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_base_eleve_infos['eleve']['nom'].' '.$tab_base_eleve_infos['eleve']['prenom']).'</td><td>'.implode('<br />',$td_contenu).'</td></tr>';
			}
			else
			{
				// Cas [2] : au moins une différence  &rarr; modifier
				$lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$tab_base_eleve_infos['eleve']['id'].'" name="mod_'.$tab_base_eleve_infos['eleve']['id'].'" type="checkbox" checked /></th><td>'.html($tab_base_eleve_infos['eleve']['nom'].' '.$tab_base_eleve_infos['eleve']['prenom']).'</td><td>'.implode('<br />',$td_contenu).'</td></tr>';
			}
		}
	}
	// On affiche
	echo'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des liens de responsabilité.</label></p>';
	echo'<table>';
	// Cas [2]
	echo		'<tbody>';
	echo			'<tr><th colspan="3">Liens de responsabilité à modifier <input name="all_check" type="image" src="./_img/all_check.gif" title="Tout cocher." /> <input name="all_uncheck" type="image" src="./_img/all_uncheck.gif" title="Tout décocher." /></th></tr>';
	echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="3">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [1]
	echo		'<tbody>';
	echo			'<tr><th colspan="3">Liens de responsabilité à conserver</th></tr>';
	echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="3">Aucun</td></tr>';
	echo		'</tbody>';
	echo'</table>';
	echo'<p class="li"><a href="#step82" id="envoyer_infos_utilisateurs">Valider et afficher le bilan obtenu.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 82 - Traitement des liens de responsabilités des parents (sconet_parents)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==82 )
{
	// On récupère le fichier avec des infos sur les utilisateurs : $tab_memo_analyse[$eleve_id][$parent_id] = $resp_legal_num;
	$tab_memo_analyse = load_fichier('memo_analyse');
	// Récupérer les éléments postés
	$tab_eleve_id = array() ;
	$tab_check = (isset($_POST['f_check'])) ? explode(',',$_POST['f_check']) : array() ;
	foreach($tab_check as $check_infos)
	{
		if(substr($check_infos,0,4)=='mod_')
		{
			$eleve_id = clean_entier( substr($check_infos,4) );
			if( isset($tab_memo_analyse[$eleve_id]) )
			{
				$tab_eleve_id[] = $eleve_id;
			}
		}
	}
	$nb_modifs_eleves = count($tab_eleve_id);
	if($nb_modifs_eleves)
	{
		// supprimer les liens de responsabilité des élèves concernés (il est plus simple de réinitialiser que de traiter les resp un par un puis de vérifier s'il n'en reste pas à supprimer...)
		DB_STRUCTURE_supprimer_jointures_parents_for_eleves(implode(',',$tab_eleve_id));
		// modifier les liens de responsabilité
		foreach($tab_eleve_id as $eleve_id)
		{
			foreach($tab_memo_analyse[$eleve_id] as $parent_id => $resp_legal_num)
			{
				DB_STRUCTURE_ajouter_jointure_parent_eleve($parent_id,$eleve_id,$resp_legal_num);
			}
		}
	}
	// Afficher le résultat
	$s = ($nb_modifs_eleves>1) ? 's' : '' ;
	echo'<p><label class="valide">Liens de responsabilités modifiés pour '.$nb_modifs_eleves.' élève'.$s.'</label></p>';
	echo'<p class="li"><a href="#step90" id="passer_etape_suivante">Passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 90 - Nettoyage des fichiers temporaires (sconet_professeurs_directeurs | tableur_professeurs_directeurs | sconet_eleves | base-eleves_eleves | tableur_eleves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==90 )
{
	unlink($dossier_import.$fichier_dest);
	unlink($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_users.txt');
	unlink($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_classes.txt');
	unlink($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_groupes.txt');
	unlink($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_memo_analyse.txt');
	unlink($dossier_import.'import_'.$action.'_'.$_SESSION['BASE'].'_liens_id_base.txt');
	echo'<p><label class="valide">Fichiers temporaires effacés, procédure d\'import terminée !</label></p>';
	echo'<p class="li"><a href="#" id="retourner_depart">Retour au départ.</a><label id="ajax_msg">&nbsp;</label></p>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
