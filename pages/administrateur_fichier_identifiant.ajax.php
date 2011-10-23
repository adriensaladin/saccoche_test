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

$action = (isset($_POST['action'])) ? $_POST['action'] : '';
$profil = (isset($_POST['profil'])) ? $_POST['profil'] : '';
$tab_select_users = (isset($_POST['select_users'])) ? array_map('clean_entier',explode(',',$_POST['select_users'])) : array() ;
$tab_select_users = array_filter($tab_select_users,'positif');
$nb = count($tab_select_users);

$dossier_export    = './__tmp/export/';
$dossier_import    = './__tmp/import/';
$dossier_login_mdp = './__tmp/login-mdp/';

$tab_profils = array('eleves','parents','professeurs','directeurs');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Initialiser plusieurs noms d'utilisateurs élèves | parents | professeurs | directeurs
//	Initialiser plusieurs mots de passe élèves | parents | professeurs | directeurs
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( (($action=='init_login')||($action=='init_mdp')) && (in_array($profil,$tab_profils)) && $nb )
{
	$prefixe = ($profil!='parents') ? 'user_' : 'parent_' ;
	// Nom sans extension des fichiers de sortie
	$fnom = 'identifiants_'.$_SESSION['BASE'].'_'.$profil.'_'.time();
	// La classe n'est affichée que pour l'élève
	$avec_info = ($profil=='eleves') ? 'classe' : ( ($profil=='parents') ? 'enfant' : '' ) ;
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Initialiser plusieurs noms d'utilisateurs
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($action=='init_login')
	{
		$tab_login = array();
		// Récupérer les données des utilisateurs concernés (besoin de le faire maintenant, on a besoin des infos pour générer le login)
		$listing_champs = ($profil!='parents') ? 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_profil,user_nom,user_prenom' :  'parent.user_id AS parent_id,parent.user_sconet_id AS parent_sconet_id,parent.user_sconet_elenoet AS parent_sconet_elenoet,parent.user_reference AS parent_reference,parent.user_profil AS parent_profil,parent.user_nom AS parent_nom,parent.user_prenom AS parent_prenom' ;
		$DB_TAB = DB_STRUCTURE_lister_users_cibles(implode(',',$tab_select_users),$listing_champs,$avec_info);
		// Mettre à jour les noms d'utilisateurs des utilisateurs concernés
		foreach($DB_TAB as $DB_ROW)
		{
			// Construire le login
			$login = fabriquer_login($DB_ROW[$prefixe.'prenom'] , $DB_ROW[$prefixe.'nom'] , $DB_ROW[$prefixe.'profil']);
			// Puis tester le login
			if( DB_STRUCTURE_tester_login($login,$DB_ROW[$prefixe.'id']) )
			{
				// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
				$login = DB_STRUCTURE_rechercher_login_disponible($login);
			}
			DB_STRUCTURE_modifier_utilisateur( $DB_ROW[$prefixe.'id'] , array(':login'=>$login) );
			$tab_login[$DB_ROW[$prefixe.'id']] = $login;
		}
	}
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Initialiser plusieurs mots de passe
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	if($action=='init_mdp')
	{
		$tab_password = array();
		// Mettre à jour les mots de passe des utilisateurs concernés
		foreach($tab_select_users as $user_id)
		{
			$password = fabriquer_mdp();
			DB_STRUCTURE_modifier_utilisateur( $user_id , array(':password'=>$password) );
			$tab_password[$user_id] = $password;
		}
		// Récupérer les données des utilisateurs concernés (besoin ensuite pour les csv / pdf)
		$listing_champs = ($profil!='parents') ? 'user_id,user_sconet_id,user_sconet_elenoet,user_reference,user_profil,user_nom,user_prenom,user_login' :  'parent.user_id AS parent_id,parent.user_sconet_id AS parent_sconet_id,parent.user_sconet_elenoet AS parent_sconet_elenoet,parent.user_reference AS parent_reference,parent.user_profil AS parent_profil,parent.user_nom AS parent_nom,parent.user_prenom AS parent_prenom,parent.user_login AS parent_login' ;
		$DB_TAB = DB_STRUCTURE_lister_users_cibles(implode(',',$tab_select_users),$listing_champs,$avec_info);
	}
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Générer une sortie csv zippé (login ou mdp) (élève ou prof)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$separateur = ';';
	$champ_nom = ($profil=='eleves') ? 'CLASSE' : 'PROFIL' ;
	$fcontenu = 'SCONET_ID'.$separateur.'SCONET_N°'.$separateur.'REFERENCE'.$separateur.'PROFIL'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'LOGIN'.$separateur.'MOT DE PASSE'.$separateur.'INFO'."\r\n\r\n";
	foreach($DB_TAB as $DB_ROW)
	{
		$login = ($action=='init_login')  ? $tab_login[$DB_ROW[$prefixe.'id']]    : $DB_ROW[$prefixe.'login'] ;
		$mdp   = ($action=='init_mdp')    ? $tab_password[$DB_ROW[$prefixe.'id']] : 'inchangé' ;
		$info  = (isset($DB_ROW['info'])) ? $DB_ROW['info'] : '' ;
		$fcontenu .= $DB_ROW[$prefixe.'sconet_id'].$separateur.$DB_ROW[$prefixe.'sconet_elenoet'].$separateur.$DB_ROW[$prefixe.'reference'].$separateur.$DB_ROW[$prefixe.'profil'].$separateur.$DB_ROW[$prefixe.'nom'].$separateur.$DB_ROW[$prefixe.'prenom'].$separateur.$login.$separateur.$mdp.$separateur.$info."\r\n";
	}
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_login_mdp.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($fcontenu));
	$zip->close();
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Générer une sortie pdf : classe fpdf + script étiquettes (login ou mdp) (élève ou prof)
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	$font_size = ($profil!='parents') ? 11 : 10 ;
	$pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>$font_size));
	$pdf -> AddFont('Arial','' ,'arial.php');
	$pdf -> SetFont('Arial'); // Permet de mieux distinguer les "l 1" etc. que la police Times ou Courrier
	$pdf -> AddPage();
	$pdf -> SetFillColor(245,245,245);
	$pdf -> SetDrawColor(145,145,145);
	foreach($DB_TAB as $DB_ROW)
	{
		$ligne1 = perso_ucwords($DB_ROW[$prefixe.'profil']) ;
		$ligne1.= (isset($DB_ROW['info'])) ? ' : '.perso_ucwords($DB_ROW['info']) : '' ;
		$ligne2 = $DB_ROW[$prefixe.'nom'].' '.$DB_ROW[$prefixe.'prenom'];
		$ligne3 = ($action=='init_login')  ? 'Utilisateur : '.$tab_login[$DB_ROW[$prefixe.'id']] : 'Utilisateur : '.$DB_ROW[$prefixe.'login'] ;
		$ligne4 = ($action=='init_mdp')    ? 'Mot de passe : '.$tab_password[$DB_ROW[$prefixe.'id']] : 'Mot de passe : inchangé' ;
		$pdf -> Add_Label(pdf($ligne1."\r\n".$ligne2."\r\n".$ligne3."\r\n".$ligne4));
	}
	$pdf->Output($dossier_login_mdp.$fnom.'.pdf','F');
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	//	Affichage du résultat
	//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
	echo'<ul class="puce">';
	echo'<li><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.pdf">Nouveaux identifiants &rarr; Archiver / Imprimer (étiquettes <em>pdf</em>)</a></li>';
	echo'<li><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.zip">Nouveaux identifiants &rarr; Récupérer / Manipuler (fichier <em>csv</em> pour tableur).</a></li>';
	if($action=='init_mdp')
	{
		echo'<li><label class="alerte">Les mots de passe, cryptés, ne sont plus accessibles ultérieurement !</label></li>';
	}
	echo'</ul>';
	exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Export CSV du contenu de la base des utilisateurs (login nom prénom de SACoche)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='user_export')
{
	$separateur = ';';
	// Récupérer les données des utilisateurs
	$DB_TAB = DB_STRUCTURE_lister_users(array('eleve','parent','professeur','directeur'),$only_actifs=true,$with_classe=true);
	// Générer le csv
	$fcontenu_csv = 'LOGIN'.$separateur.'MOT DE PASSE'.$separateur.'NOM'.$separateur.'PRENOM'.$separateur.'PROFIL (INFO)'.$separateur.'CLASSE (INFO)'."\r\n\r\n";
	foreach($DB_TAB as $DB_ROW)
	{
		$fcontenu_csv .= $DB_ROW['user_login'].$separateur.''.$separateur.$DB_ROW['user_nom'].$separateur.$DB_ROW['user_prenom'].$separateur.$DB_ROW['user_profil'].$separateur.$DB_ROW['groupe_ref']."\r\n";
	}
	// On archive dans un fichier tableur zippé (csv tabulé)
	$fnom = 'export_'.$_SESSION['BASE'].'_mdp_'.time();
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE);
	if($result_open!==TRUE)
	{
		require('./_inc/tableau_zip_error.php');
		exit('Problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
	}
	$zip->addFromString($fnom.'.csv',csv($fcontenu_csv));
	$zip->close();
	exit('<ul class="puce"><li><a class="lien_ext" href="'.$dossier_export.$fnom.'.zip">Récupérez le fichier exporté de la base SACoche.</a></li></ul>');
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Import CSV du contenu d'un fichier pour forcer les logins ou/et mdp utilisateurs de SACoche
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='import_loginmdp')
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
	if(!in_array($extension,array('txt','csv')))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	$fichier_dest = $action.'_'.$_SESSION['BASE'].'.txt' ;
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// Pour récupérer les données des utilisateurs
	$tab_users_fichier           = array();
	$tab_users_fichier['login']  = array();
	$tab_users_fichier['mdp']    = array();
	$tab_users_fichier['nom']    = array();
	$tab_users_fichier['prenom'] = array();
	$contenu = file_get_contents($dossier_import.$fichier_dest);
	$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
	$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
	$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
	unset($tab_lignes[0]); // Supprimer la 1e ligne
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		$tab_elements = array_slice($tab_elements,0,4);
		if(count($tab_elements)==4)
		{
			$tab_elements = array_map('clean_csv',$tab_elements);
			list($login,$mdp,$nom,$prenom) = $tab_elements;
			if( ($nom!='') && ($prenom!='') )
			{
				$tab_users_fichier['login'][]  = mb_substr(clean_login($login),0,20);
				$tab_users_fichier['mdp'][]    = ($mdp!='inchangé') ? mb_substr(clean_password($mdp),0,20) : '';
				$tab_users_fichier['nom'][]    = mb_substr(clean_nom($nom),0,25);
				$tab_users_fichier['prenom'][] = mb_substr(clean_prenom($prenom),0,25);
			}
		}
	}
	// On trie
	array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['login'],$tab_users_fichier['mdp']);
	// On récupère le contenu de la base pour comparer, y compris les professeurs afin de comparer avec leurs logins, et y compris les classes pour les étiquettes pdf
	$tab_users_base           = array();
	$tab_users_base['login']  = array();
	$tab_users_base['mdp']    = array();
	$tab_users_base['nom']    = array();
	$tab_users_base['prenom'] = array();
	$tab_users_base['info']   = array();
	$DB_TAB = DB_STRUCTURE_lister_users(array('eleve','parent','professeur','directeur'),$only_actifs=false,$with_classe=true);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['login'][$DB_ROW['user_id']]  = $DB_ROW['user_login'];
		$tab_users_base['mdp'][$DB_ROW['user_id']]    = $DB_ROW['user_password'];
		$tab_users_base['nom'][$DB_ROW['user_id']]    = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']] = $DB_ROW['user_prenom'];
		$tab_users_base['info'][$DB_ROW['user_id']]   = ($DB_ROW['user_profil']=='eleve') ? $DB_ROW['groupe_nom'] : mb_strtoupper($DB_ROW['user_profil']) ;
	}
	// Observer le contenu du fichier et comparer avec le contenu de la base
	$fcontenu_pdf_tab = array();
	$lignes_ras = '';
	$lignes_mod = '';
	$lignes_pb  = '';
	foreach($tab_users_fichier['login'] as $i_fichier => $login)
	{
		if( ($tab_users_fichier['login'][$i_fichier]=='') && ($tab_users_fichier['mdp'][$i_fichier]=='') )
		{
			// Contenu du fichier à ignorer : login et mdp non indiqués
			$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur et mot de passe non imposés</td></tr>';
		}
		else
		{
			// On recherche l'id de l'utilisateur de la base de même nom et prénom
			$tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i_fichier]);
			$tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i_fichier]);
			$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
			if(count($tab_id_commun))
			{
				list($inutile,$id_base) = each($tab_id_commun);
			}
			else
			{
				$id_base = false;
			}
			if(!$id_base)
			{
				// Contenu du fichier à ignorer : utilisateur non trouvé dans la base
				$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom et prénom non trouvés dans la base</td></tr>';
			}
			elseif($tab_users_fichier['login'][$i_fichier]=='')
			{
				// login non indiqué (mdp forcément indiqué)...
				if(md5($tab_users_fichier['mdp'][$i_fichier])==$tab_users_base['mdp'][$id_base])
				{
					// Contenu du fichier à ignorer : login non indiqué et mdp identiques
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">mot de passe identique et nom d\'utilisateur non imposé</td></tr>';
				}
				else
				{
					// Contenu du fichier à modifier : login non indiqué et mdp différents
					$password = $tab_users_fichier['mdp'][$i_fichier];
					DB_STRUCTURE_modifier_utilisateur( $id_base , array(':password'=>$password) );
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="i">Utilisateur : inchangé</td><td class="b">Password : '.html($password).'</td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$tab_users_base['login'][$id_base]."\r\n".'Mot de passe : '.$password;
				}
			}
			elseif($tab_users_fichier['login'][$i_fichier]==$tab_users_base['login'][$id_base])
			{
				// login identique...
				if($tab_users_fichier['mdp'][$i_fichier]=='')
				{
					// Contenu du fichier à ignorer : logins identiques et mdp non indiqué
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur identique et mot de passe non imposé</td></tr>';
				}
				elseif(crypter_mdp($tab_users_fichier['mdp'][$i_fichier])==$tab_users_base['mdp'][$id_base])
				{
					// Contenu du fichier à ignorer : logins identiques et mdp identique
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur et mot de passe identiques</td></tr>';
				}
				else
				{
					// Contenu du fichier à modifier : logins identiques et mdp différents
					$password = $tab_users_fichier['mdp'][$i_fichier];
					DB_STRUCTURE_modifier_utilisateur( $id_base , array(':password'=>$password) );
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="i">Utilisateur : inchangé</td><td class="b">Password : '.html($password).'</td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$tab_users_base['login'][$id_base]."\r\n".'Mot de passe : '.$password;
				}
			}
			else
			{
				// logins différents...
				if(in_array($tab_users_fichier['login'][$i_fichier],$tab_users_base['login']))
				{
					// Contenu du fichier à problème : login déjà pris
					$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td colspan="2">nom d\'utilisateur proposé déjà affecté à un autre utilisateur</td></tr>';
				}
				elseif( ($tab_users_fichier['mdp'][$i_fichier]=='') || (crypter_mdp($tab_users_fichier['mdp'][$i_fichier])==$tab_users_base['mdp'][$id_base]) )
				{
					// Contenu du fichier à modifier : logins différents et mdp identiques on non imposé
					$login = $tab_users_fichier['login'][$i_fichier];
					DB_STRUCTURE_modifier_utilisateur( $id_base , array(':login'=>$login) );
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Utilisateur : '.html($login).'</td><td class="i">Password : inchangé</td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : <span class="i">inchangé</span>';
				}
				else
				{
					// Contenu du fichier à modifier : logins différents et mdp différents
					$login = $tab_users_fichier['login'][$i_fichier];
					$password = $tab_users_fichier['mdp'][$i_fichier];
					DB_STRUCTURE_modifier_utilisateur( $id_base , array(':login'=>$login,':password'=>$password) );
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Utilisateur : '.html($login).'</td><td class="b">Password : '.html($password).'</td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id_base]."\r\n".$tab_users_base['nom'][$id_base].' '.$tab_users_base['prenom'][$id_base]."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : '.$password;
				}
			}
		}
	}
	// On archive les nouveaux identifiants dans un fichier pdf (classe fpdf + script étiquettes)
	echo'<ul class="puce">';
	if(count($fcontenu_pdf_tab))
	{
		$fnom = 'identifiants_'.$_SESSION['BASE'].'_'.time();
		$pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>5, 'marginTop'=>5, 'NX'=>3, 'NY'=>8, 'SpaceX'=>7, 'SpaceY'=>5, 'width'=>60, 'height'=>30, 'font-size'=>11));
		$pdf -> AddFont('Arial','' ,'arial.php');
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
		echo'<li><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.pdf">Archiver / Imprimer les identifiants modifiés (étiquettes <em>pdf</em>).</a></li>';
		echo'<li><label class="alerte">Les mots de passe, cryptés, ne sont plus accessibles ultérieurement !</label></li>';
	}
	// On affiche le bilan
	echo'<li><b>Résultat de l\'analyse et des opérations effectuées :</b></li>';
	echo'</ul>';
	echo'<table>';
	echo' <tbody>';
	echo'  <tr><th colspan="3">Utilisateurs trouvés dans le fichier dont les identifiants ont été modifiés.</th></tr>';
	echo($lignes_mod) ? $lignes_mod : '<tr><td colspan="3">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="3">Utilisateurs trouvés dans le fichier dont les identifiants n\'ont pas pu être modifiés.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="3">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="3">Utilisateurs trouvés dans le fichier dont les identifiants sont inchangés.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="3">Aucun</td></tr>';
	echo' </tbody>';
	echo'</table>';
	exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Import CSV du contenu d'un fichier pour forcer les identifiants élèves ou professeurs de GEPI
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='import_gepi_eleves') || ($action=='import_gepi_profs') )
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
	$tab_fnom_attendu = array( 'import_gepi_eleves'=>array('base_eleve_gepi.csv') , 'import_gepi_profs'=>array('base_professeur_gepi.csv','base_cpe_gepi.csv') );
	if(!in_array($fnom_transmis,$tab_fnom_attendu[$action]))
	{
		exit('Erreur : le nom du fichier n\'est pas "'.$tab_fnom_attendu[$action][0].'" !');
	}
	$fichier_dest = $action.'_'.$_SESSION['BASE'].'.txt' ;

	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// Pour récupérer les données des utilisateurs
	$tab_users_fichier               = array();
	$tab_users_fichier['id_gepi']    = array();
	$tab_users_fichier['nom']        = array();
	$tab_users_fichier['prenom']     = array();
	$tab_users_fichier['sconet_num'] = array(); // Ne servira que pour les élèves
	$contenu = file_get_contents($dossier_import.$fichier_dest);
	$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
	$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
	$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
	// Pas de ligne d'en-tête à supprimer
	// Récupérer les données du fichier
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		if(count($tab_elements)>2)
		{
			$tab_elements = array_map('clean_csv',$tab_elements);
			$id_gepi    = $tab_elements[2];
			$nom        = $tab_elements[0];
			$prenom     = $tab_elements[1];
			$sconet_num = (isset($tab_elements[4])) ? $tab_elements[4] : 0;
			if( ($id_gepi!='') && ($nom!='') && ($prenom!='') )
			{
				$tab_users_fichier['id_gepi'][] = mb_substr(clean_texte($id_gepi),0,32);
				$tab_users_fichier['nom'][]     = mb_substr(clean_nom($nom),0,25);
				$tab_users_fichier['prenom'][]  = mb_substr(clean_prenom($prenom),0,25);
				$tab_users_fichier['sconet_num'][] = clean_entier($sconet_num);
			}
		}
	}
	// On trie
	array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['id_gepi'],$tab_users_fichier['sconet_num']);
	// On récupère le contenu de la base pour comparer (la recherche d'éventuels doublons d'ids gepi ne se fera que sur les profs...)
	$tab_users_base               = array();
	$tab_users_base['id_gepi']    = array();
	$tab_users_base['nom']        = array();
	$tab_users_base['prenom']     = array();
	$tab_users_base['sconet_num'] = array(); // Ne servira que pour les élèves
	$profil = ($action=='import_gepi_eleves') ? 'eleve' : 'professeur' ;
	$DB_TAB = DB_STRUCTURE_lister_users($profil,$only_actifs=false,$with_classe=false);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['id_gepi'][$DB_ROW['user_id']]    = $DB_ROW['user_id_gepi'];
		$tab_users_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
		$tab_users_base['sconet_num'][$DB_ROW['user_id']] = $DB_ROW['user_sconet_elenoet'];
	}
	// Observer le contenu du fichier et comparer avec le contenu de la base
	$lignes_ras = '';
	$lignes_mod = '';
	$lignes_pb  = '';
	foreach($tab_users_fichier['id_gepi'] as $i_fichier => $id_gepi)
	{
		if($tab_users_fichier['id_gepi'][$i_fichier]=='')
		{
			// Contenu du fichier à ignorer : id_gepi non indiqué
			$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>identifiant de GEPI non indiqué</td></tr>';
		}
		else
		{
			$id_base = 0;
			// Si sconet_num (elenoet) est renseigné (élèves uniquement), on recherche l'id de l'utilisateur de la base de même sconet_num
			if($tab_users_fichier['sconet_num'][$i_fichier])
			{
				$id_base = array_search($tab_users_fichier['sconet_num'][$i_fichier],$tab_users_base['sconet_num']);
			}
			if(!$id_base)
			{
				// Sinon on recherche l'id de l'utilisateur de la base de même nom et prénom
				$tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i_fichier]);
				$tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i_fichier]);
				$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
				$nb_homonymes  = count($tab_id_commun);
				if($nb_homonymes == 0)
				{
					// Contenu du fichier à ignorer : utilisateur non trouvé dans la base
					$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>nom et prénom non trouvés dans la base</td></tr>';
				}
				elseif($nb_homonymes > 1 )
				{
					// Contenu du fichier à ignorer : plusieurs homonymes trouvés dans la base
					$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>';
				}
				else
				{
					list($inutile,$id_base) = each($tab_id_commun);
				}
			}
			if($id_base)
			{
				if($tab_users_fichier['id_gepi'][$i_fichier]==$tab_users_base['id_gepi'][$id_base])
				{
					// Contenu du fichier à ignorer : id_gepi identique
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>identifiant de GEPI identique</td></tr>';
				}
				else
				{
					// id_gepi différents...
					if(in_array($tab_users_fichier['id_gepi'][$i_fichier],$tab_users_base['id_gepi']))
					{
						// Contenu du fichier à problème : id_gepi déjà pris
						$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_gepi'][$i_fichier].']').'</td><td>identifiant de GEPI déjà affecté à un autre utilisateur</td></tr>';
					}
					else
					{
						// Contenu du fichier à modifier : id_gepi nouveau
						DB_STRUCTURE_modifier_utilisateur( $id_base , array(':id_gepi'=>$id_gepi) );
						$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td class="b">Id Gepi : '.html($id_gepi).'</td></tr>';
					}
				}
			}
		}
	}
	// On affiche le bilan
	echo'<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>';
	echo'<table>';
	echo' <tbody>';
	echo'  <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi a été modifié.</th></tr>';
	echo($lignes_mod) ? $lignes_mod : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi n\'a pas pu être modifié.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi est inchangé.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody>';
	echo'</table>';
	exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Import CSV du contenu d'un fichier pour forcer les identifiants d'un ENT
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='import_ent')
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
	if(!in_array($extension,array('txt','csv')))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	$fichier_dest = $action.'_'.$_SESSION['BASE'].'.txt' ;
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// Utiliser $_SESSION['CONNEXION_MODE'] et $_SESSION['CONNEXION_NOM'] pour déterminer l'emplacement des données à récupérer
	require_once('./_inc/tableau_sso.php');
	// Pour récupérer les données des utilisateurs
	$tab_users_fichier              = array();
	$tab_users_fichier['id_ent']    = array();
	$tab_users_fichier['nom']       = array();
	$tab_users_fichier['prenom']    = array();
	$tab_users_fichier['id_sconet'] = array();
	$contenu = file_get_contents($dossier_import.$fichier_dest);
	$contenu = utf8($contenu); // Mettre en UTF-8 si besoin
	$tab_lignes = extraire_lignes($contenu); // Extraire les lignes du fichier
	$separateur = extraire_separateur_csv($tab_lignes[0]); // Déterminer la nature du séparateur
	if($tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_entete'])
	{
		unset($tab_lignes[0]); // Supprimer la 1e ligne
	}
	// Récupérer les données
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		if(count($tab_elements)>2)
		{
			$tab_elements = array_map('clean_csv',$tab_elements);
			$id_ent    = $tab_elements[ $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_id_ent'] ];
			$nom       = $tab_elements[ $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_nom']    ];
			$prenom    = $tab_elements[ $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_prenom'] ];
			$id_sconet = ($tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_id_sconet']==NULL) ? '' : $tab_elements[ $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_id_sconet'] ] ;
			if( ($id_ent!='') && ($nom!='') && ($prenom!='') )
			{
				if(in_array($_SESSION['CONNEXION_NOM'],array('celia','lilie')))
				{
					$id_ent = str_replace('ID : ','UT',$id_ent); // Dans les CSV de Lilie & Celi@, il faut par exemple remplacer "ID : 75185265" par "UT75185265"
				}
				$tab_users_fichier['id_ent'][]    = mb_substr(clean_texte($id_ent),0,32);
				$tab_users_fichier['nom'][]       = mb_substr(clean_nom($nom),0,25);
				$tab_users_fichier['prenom'][]    = mb_substr(clean_prenom($prenom),0,25);
				$tab_users_fichier['id_sconet'][] = clean_entier($id_sconet);
			}
		}
	}
	// On trie
	array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['id_ent'],$tab_users_fichier['id_sconet']);
	// On récupère le contenu de la base pour comparer
	$tab_users_base              = array();
	$tab_users_base['id_ent']    = array();
	$tab_users_base['nom']       = array();
	$tab_users_base['prenom']    = array();
	$tab_users_base['id_sconet'] = array();
	$tab_users_base['info']      = array();
	$tab_profils = array('eleve','parent','professeur','directeur'); // cybercolleges42 (au moins) contient les parents dans son csv
	$DB_TAB = DB_STRUCTURE_lister_users($tab_profils,$only_actifs=false,$with_classe=true);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['id_ent'][$DB_ROW['user_id']]    = $DB_ROW['user_id_ent'];
		$tab_users_base['nom'][$DB_ROW['user_id']]       = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']]    = $DB_ROW['user_prenom'];
		$tab_users_base['id_sconet'][$DB_ROW['user_id']] = $DB_ROW['user_sconet_id'];
		$tab_users_base['info'][$DB_ROW['user_id']]      = ($DB_ROW['user_profil']=='eleve') ? $DB_ROW['groupe_nom'] : mb_strtoupper($DB_ROW['user_profil']) ;
	}
	// Observer le contenu du fichier et comparer avec le contenu de la base
	$lignes_ras = '';
	$lignes_mod = '';
	$lignes_pb  = '';
	foreach($tab_users_fichier['id_ent'] as $i_fichier => $id_ent)
	{
		if($tab_users_fichier['id_ent'][$i_fichier]=='')
		{
			// Contenu du fichier à ignorer : id_ent non indiqué
			$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).'</td><td>identifiant d\'ENT non imposé</td></tr>';
		}
		else
		{
			// Dans les CSV de Lilie & Celi@ les noms/prénoms ne sont pas accentués, mais par contre on a l'id Sconet
			if($tab_users_fichier['id_sconet'][$i_fichier])
			{
				$id_base = array_search( $tab_users_fichier['id_sconet'][$i_fichier] , $tab_users_base['id_sconet'] );
				if($id_base == FALSE)
				{
					// Contenu du fichier à ignorer : utilisateur non trouvé dans la base
					$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant Sconet '.$tab_users_fichier['id_sconet'][$i_fichier].' non trouvé dans la base</td></tr>';
				}
				else
				{
					if($tab_users_fichier['id_ent'][$i_fichier]==$tab_users_base['id_ent'][$id_base])
					{
						// Contenu du fichier à ignorer : id_ent identique
						$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT identique</td></tr>';
					}
					else
					{
						// id_ent différents...
						if(in_array($tab_users_fichier['id_ent'][$i_fichier],$tab_users_base['id_ent']))
						{
							// Contenu du fichier à problème : id_ent déjà pris
							$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT déjà affecté à un autre utilisateur</td></tr>';
						}
						else
						{
							// Contenu du fichier à modifier : id_ent nouveau
							DB_STRUCTURE_modifier_utilisateur( $id_base , array(':id_ent'=>$id_ent) );
							$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Id ENT : '.html($id_ent).'</td></tr>';
						}
					}
				}
			}
			else
			{
				// On recherche l'id de l'utilisateur de la base de même nom et prénom
				$tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i_fichier]);
				$tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i_fichier]);
				$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
				$nb_homonymes  = count($tab_id_commun);
				if($nb_homonymes == 0)
				{
					// Contenu du fichier à ignorer : utilisateur non trouvé dans la base
					$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>nom et prénom non trouvés dans la base</td></tr>';
				}
				elseif($nb_homonymes > 1 )
				{
					// Contenu du fichier à ignorer : plusieurs homonymes trouvés dans la base
					$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>';
				}
				else
				{
					list($inutile,$id_base) = each($tab_id_commun);
					if($tab_users_fichier['id_ent'][$i_fichier]==$tab_users_base['id_ent'][$id_base])
					{
						// Contenu du fichier à ignorer : id_ent identique
						$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT identique</td></tr>';
					}
					else
					{
						// id_ent différents...
						if(in_array($tab_users_fichier['id_ent'][$i_fichier],$tab_users_base['id_ent']))
						{
							// Contenu du fichier à problème : id_ent déjà pris
							$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ['.$tab_users_fichier['id_ent'][$i_fichier].']').'</td><td>identifiant d\'ENT déjà affecté à un autre utilisateur</td></tr>';
						}
						else
						{
							// Contenu du fichier à modifier : id_ent nouveau
							DB_STRUCTURE_modifier_utilisateur( $id_base , array(':id_ent'=>$id_ent) );
							$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier].' ('.$tab_users_base['info'][$id_base].')').'</td><td class="b">Id ENT : '.html($id_ent).'</td></tr>';
						}
					}
				}
			}
		}
	}
	// On affiche le bilan
	echo'<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>';
	echo'<table>';
	echo' <tbody>';
	echo'  <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant ENT a été modifié.</th></tr>';
	echo($lignes_mod) ? $lignes_mod : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant ENT est inchangé.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody>';
	echo'</table>';
	exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Dupliquer l'identifiant de Gepi enregistré comme identifiant de l'ENT (COPY_id_gepi_TO_id_ent)
//	Dupliquer le login de SACoche enregistré comme identifiant de l'ENT (COPY_login_TO_id_ent)
//	Dupliquer l'identifiant de l'ENT enregistré comme identifiant de Gepi (COPY_id_ent_TO_id_gepi)
//	Dupliquer le login de SACoche enregistré comme identifiant de Gepi (COPY_login_TO_id_gepi)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='COPY_id_gepi_TO_id_ent') || ($action=='COPY_login_TO_id_ent') || ($action=='COPY_id_ent_TO_id_gepi') || ($action=='COPY_login_TO_id_gepi') )
{
	list($champ_depart,$champ_arrive) = explode('_TO_',substr($action,5));
	DB_STRUCTURE_recopier_identifiants($champ_depart,$champ_arrive);
	exit('ok');
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Dupliquer l'identifiant récupéré du LCS comme identifiant de l'ENT (COPY_id_lcs_TO_id_ent)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($action=='COPY_id_lcs_TO_id_ent')
{
	$fichier = './webservices/import_lcs.php';
	if(!is_file($fichier))
	{
		exit('Erreur : le fichier "'.$fichier.'" n\'a pas été trouvé !');
	}
	require($fichier); // Charge la fonction "recuperer_infos_user_LCS()"
	// On récupère le contenu de la base, on va passer les users en revue un par un
	$DB_TAB = DB_STRUCTURE_lister_users(array('eleve','parent','professeur','directeur'),$only_actifs=true,$with_classe=true);
	// Pour chaque user de la base, rechercher son uid dans le LCS
	$lignes_ras     = '';
	$lignes_modif   = '';
	$lignes_pb      = '';
	$lignes_inconnu = ''; // de SACoche non trouvé dans LCS
	foreach($DB_TAB as $DB_ROW)
	{
		if($DB_ROW['user_profil']=='directeur')
		{
			// Contenu de SACoche à ignorer : utilisateur non cherché dans le LCS car profil 'directeur'
			$lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non cherché car profil directeur</td></tr>';
		}
		if($DB_ROW['user_profil']=='parent')
		{
			// Contenu de SACoche à ignorer : utilisateur non cherché dans le LCS car profil 'parent'
			$lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non cherché car profil parent</td></tr>';
		}
		elseif( ($DB_ROW['user_profil']=='eleve') && (!$DB_ROW['user_sconet_elenoet']) )
		{
			// Contenu de SACoche à ignorer : élève non cherché dans le LCS car pas d'Elenoet (numéro Sconet)
			$lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non cherché car élève sans Elenoet</td></tr>';
		}
		elseif( ($DB_ROW['user_profil']=='professeur') && (!$DB_ROW['user_sconet_id']) )
		{
			// Contenu de SACoche à ignorer : prof non cherché dans le LCS car pas d'Id Sconet
			$lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non cherché car prof sans Id Sconet</td></tr>';
		}
		else
		{
			list($code_erreur,$tab_valeurs_retournees) = recuperer_infos_user_LCS($DB_ROW['user_profil'],$DB_ROW['user_sconet_elenoet'],$DB_ROW['user_sconet_id']);
			if($code_erreur)
			{
				// Contenu de SACoche à problème : retour erroné du LCS
				$lignes_pb .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>non trouvé : erreur LCS n°'.html($code_erreur).'</td></tr>';
			}
			elseif(count($tab_valeurs_retournees)==0)
			{
				// Contenu de SACoche à ignorer : utilisateur non trouvé dans le LCS
				$identifiant = ($DB_ROW['user_profil']=='eleve') ? $DB_ROW['user_sconet_elenoet'] : $DB_ROW['user_sconet_id'] ;
				$lignes_inconnu .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>Identifiant '.html($identifiant).' non trouvé dans le LCS</td></tr>';
			}
			elseif(count($tab_valeurs_retournees)!=1)
			{
				// Contenu de SACoche à problème : plusieurs réponses retournées par le LCS
				$identifiant = ($DB_ROW['user_profil']=='eleve') ? $DB_ROW['user_sconet_elenoet'] : $DB_ROW['user_sconet_id'] ;
				$lignes_pb .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>Identifiant '.html($identifiant).' trouvé plusieurs fois dans le LCS</td></tr>';
			}
			else
			{
				$id_ent_LCS = mb_substr($tab_valeurs_retournees[0],0,32);
				if($DB_ROW['user_id_ent']==$id_ent_LCS)
				{
					// Contenu de SACoche à ignorer : id_ent identique
					$lignes_ras .= '<tr><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td>identifiant du LCS identique</td></tr>';
				}
				else
				{
					// Contenu de SACoche à modifier : id_ent nouveau
					DB_STRUCTURE_modifier_utilisateur( $DB_ROW['user_id'] , array(':id_ent'=>$id_ent_LCS) );
					$user_info = ($DB_ROW['user_profil']=='eleve') ? $DB_ROW['groupe_nom'] : mb_strtoupper($DB_ROW['user_profil']) ;
					$lignes_modif .= '<tr class="new"><td>'.html($DB_ROW['user_nom'].' '.$DB_ROW['user_prenom'].' ['.$DB_ROW['user_id_ent'].']').'</td><td class="b">Id ENT : '.html($id_ent_LCS).'</td></tr>';
				}
			}
		}
	}
	// On affiche le bilan
	echo'<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>';
	echo'<table>';
	echo' <tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche trouvés dans le LCS dont l\'identifiant ENT a été modifié.</th></tr>';
	echo($lignes_modif) ? $lignes_modif : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche trouvés dans le LCS dont l\'identifiant ENT est inchangé.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche non trouvés dans le LCS.</th></tr>';
	echo($lignes_inconnu) ? $lignes_inconnu : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody>';
	echo'</table>';
	exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Dupliquer l'identifiant récupéré d'Argos comme identifiant de l'ENT (COPY_id_argos_*_TO_id_ent)
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if( ($action=='COPY_id_argos_profs_TO_id_ent') || ($action=='COPY_id_argos_eleves_TO_id_ent') || ($action=='COPY_id_argos_parents_TO_id_ent') )
{
	$fichier = './webservices/import_argos.php';
	if(!is_file($fichier))
	{
		exit('Erreur : le fichier "'.$fichier.'" n\'a pas été trouvé !');
	}
	require($fichier); // Charge la fonction "recuperer_infos_LDAP()"
	$qui = substr($action,14,-10); // profs | eleves | parents
	// Appeler le serveur LDAP et enregistrer le fichier temporairement pour aider au débuggage
	$retour_Sarapis = recuperer_infos_LDAP($_SESSION['UAI'],$qui);
	Ecrire_Fichier( './__tmp/import/import_Sarapis_'.$_SESSION['UAI'].'_'.$qui.'.xml' , $retour_Sarapis );
	// Maintenant on regarde ce qu'il contient
	if(mb_substr($retour_Sarapis,0,6)=='Erreur')
	{
		exit($retour_Sarapis); // Erreur retournée par cURL
	}
	$xml = @simplexml_load_string($retour_Sarapis);
	if($xml===false)
	{
		exit('Erreur : le fichier récupéré n\'est pas un XML valide : problème possible de délai d\'attente trop long !');
	}
	if($xml->description->resultat != 'succes')
	{
		exit('Erreur : le LDAP a rencontré une erreur lors de l\'extraction des données !');
	}
	// Pour récupérer les données des utilisateurs
	$tab_users_ldap           = array();
	$tab_users_ldap['id_ent'] = array();
	$tab_users_ldap['nom']    = array();
	$tab_users_ldap['prenom'] = array();
	if( ($xml->reponses) && ($xml->reponses->utilisateur) )
	{
		foreach ($xml->reponses->utilisateur as $utilisateur)
		{
			if($qui!='parents')
			{
				$tab_users_ldap['id_ent'][] = mb_substr((string)$utilisateur->uid,0,32);
				$tab_users_ldap['nom'][]    = mb_substr(clean_nom($utilisateur->nom),0,25);
				$tab_users_ldap['prenom'][] = mb_substr(clean_prenom($utilisateur->prenom),0,25);
			}
			elseif($qui=='parents') /* forcément */
			{
				if( ($utilisateur->responsables) && ($utilisateur->responsables->responsable) )
				{
					foreach ($utilisateur->responsables->responsable as $responsable)
					{
						$id = (int) $responsable->entpersonlogin->attributes()->jointure; // Car ils reviennent plusieurs fois dans le fichier.
						$tab_users_ldap['id_ent'][$id] = mb_substr((string)$responsable->uid,0,32);
						$tab_users_ldap['nom'][$id]    = mb_substr(clean_nom($responsable->nom),0,25);
						$tab_users_ldap['prenom'][$id] = mb_substr(clean_prenom($responsable->prenom),0,25);
					}
				}
			}
		}
	}
	// On trie
	array_multisort($tab_users_ldap['nom'],SORT_ASC,SORT_STRING,$tab_users_ldap['prenom'],SORT_ASC,SORT_STRING,$tab_users_ldap['id_ent']);
	// On récupère le contenu de la base pour comparer
	$tab_users_base           = array();
	$tab_users_base['id_ent'] = array();
	$tab_users_base['nom']    = array();
	$tab_users_base['prenom'] = array();
	$tab_users_base['info']   = array();
	$profil      = ($qui=='profs') ? array('professeur','directeur') : substr($qui,0,-1) ;
	$with_classe = ($qui=='profs') ? false : true ;
	$DB_TAB = DB_STRUCTURE_lister_users($profil,$only_actifs=true,$with_classe);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['id_ent'][$DB_ROW['user_id']] = $DB_ROW['user_id_ent'];
		$tab_users_base['nom'][$DB_ROW['user_id']]    = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']] = $DB_ROW['user_prenom'];
		$tab_users_base['info'][$DB_ROW['user_id']]   = ($DB_ROW['user_profil']=='eleve') ? $DB_ROW['groupe_nom'] : mb_strtoupper($DB_ROW['user_profil']) ;
	}
	// Observer le contenu de Argos et comparer avec le contenu de la base
	$lignes_ras     = '';
	$lignes_modif   = '';
	$lignes_pb      = '';
	$lignes_inconnu = ''; // de SACoche non trouvé dans LDAP
	$lignes_reste   = ''; // du LDAP non trouvé dans SACoche
	foreach($tab_users_base['id_ent'] as $user_id => $id_ent_SACoche)
	{
		// Pour chaque user SACoche on recherche un utilisateur de l'ENT de même nom et prénom
		$tab_id_nom    = array_keys($tab_users_ldap['nom'],$tab_users_base['nom'][$user_id]);
		$tab_id_prenom = array_keys($tab_users_ldap['prenom'],$tab_users_base['prenom'][$user_id]);
		$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
		$nb_homonymes  = count($tab_id_commun);
		if($nb_homonymes == 0)
		{
			// Contenu de SACoche à ignorer : utilisateur non trouvé dans Argos
			$lignes_inconnu .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>nom et prénom non trouvés dans Argos</td></tr>';
		}
		elseif($nb_homonymes > 1 )
		{
			// Contenu de SACoche à problème : plusieurs homonymes trouvés dans Argos
			$lignes_pb .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>';
		}
		else
		{
			list($inutile,$i_ldap) = each($tab_id_commun);
			$id_ent_LDAP = $tab_users_ldap['id_ent'][$i_ldap];
			if($id_ent_SACoche==$id_ent_LDAP)
			{
				// Contenu de SACoche à ignorer : id_ent identique
				$lignes_ras .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>identifiant d\'ENT identique</td></tr>';
			}
			else
			{
				// id_ent différents...
				if(in_array($id_ent_LDAP,$tab_users_base['id_ent']))
				{
					// Contenu de SACoche à problème : id_ent déjà pris
					$lignes_pb .= '<tr><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ['.$id_ent_SACoche.']').'</td><td>identifiant d\'ENT ['.html($id_ent_LDAP).'] déjà affecté à un autre utilisateur</td></tr>';
				}
				else
				{
					// Contenu de SACoche à modifier : id_ent nouveau
					DB_STRUCTURE_modifier_utilisateur( $user_id , array(':id_ent'=>$id_ent_LDAP) );
					$lignes_modif .= '<tr class="new"><td>'.html($tab_users_base['nom'][$user_id].' '.$tab_users_base['prenom'][$user_id].' ('.$tab_users_base['info'][$user_id].')').'</td><td class="b">Id ENT : '.html($id_ent_LDAP).'</td></tr>';
				}
			}
			unset($tab_users_ldap['id_ent'][$i_ldap] , $tab_users_ldap['nom'][$i_ldap] , $tab_users_ldap['prenom'][$i_ldap]);
		}
	}
	if(count($tab_users_ldap['id_ent']))
	{
		foreach($tab_users_ldap['id_ent'] as $i_ldap => $id_ent_LDAP)
		{
			$lignes_reste .= '<tr><td>'.html($tab_users_ldap['nom'][$i_ldap].' '.$tab_users_ldap['prenom'][$i_ldap].' ['.$id_ent_LDAP.']').'</td><td>nom et prénom non trouvés dans SACoche</td></tr>';
		}
	}
	// On affiche le bilan
	echo'<ul class="puce"><li><b>Résultat de l\'analyse et des opérations effectuées :</b></li></ul>';
	echo'<table>';
	echo' <tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche trouvés dans Argos dont l\'identifiant ENT a été modifié.</th></tr>';
	echo($lignes_modif) ? $lignes_modif : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche trouvés dans Argos dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche trouvés dans Argos dont l\'identifiant ENT est inchangé.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de SACoche non trouvé dans Argos.</th></tr>';
	echo($lignes_inconnu) ? $lignes_inconnu : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody><tbody>';
	echo'  <tr><th colspan="2">Utilisateurs de Argos non trouvés dans SACoche.</th></tr>';
	echo($lignes_reste) ? $lignes_reste : '<tr><td colspan="2">Aucun</td></tr>';
	echo' </tbody>';
	echo'</table>';
	exit();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	On ne devrait pas en arriver là...
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

exit('Erreur avec les données transmises !');

?>
