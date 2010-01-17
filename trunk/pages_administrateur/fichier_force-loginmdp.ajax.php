<?php
/**
 * @version $Id: fichier_force-loginmdp.ajax.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action = (isset($_GET['action'])) ? $_GET['action'] : '';

$dossier_export    = './__tmp/export/';
$dossier_import    = './__tmp/import/';
$dossier_login_mdp = './__tmp/login-mdp/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Export CSV du contenu de la base des utilisateurs (login nom prénom)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='user_export')
{
	$fcontenu_csv = 'LOGIN'."\t".'MOT DE PASSE'."\t".'NOM'."\t".'PRENOM'."\t".'PROFIL (INFO)'."\t".'CLASSE (INFO)'."\r\n\r\n";
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id ';
	$DB_SQL.= 'LEFT JOIN livret_niveau USING (livret_niveau_id) ';
	$DB_SQL.= 'WHERE livret_user.livret_structure_id=:structure_id ';
	$DB_SQL.= 'GROUP BY livret_user_id ';
	$DB_SQL.= 'ORDER BY livret_user_profil ASC, livret_niveau_ordre ASC, livret_groupe_ref ASC, livret_user_nom ASC, livret_user_prenom ASC';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$fcontenu_csv .= $DB_ROW['livret_user_login']."\t".''."\t".$DB_ROW['livret_user_nom']."\t".$DB_ROW['livret_user_prenom']."\t".$DB_ROW['livret_user_profil']."\t".$DB_ROW['livret_groupe_ref']."\r\n";
	}
	// On archive dans un fichier tableur zippé (csv tabulé)
	$fnom = 'export_'.$_SESSION['STRUCTURE_ID'].'_mdp_'.time();
	$zip = new ZipArchive();
	if ($zip->open($dossier_export.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
	{
		$zip->addFromString($fnom.'.csv',csv($fcontenu_csv));
		$zip->close();
	}
	echo'<a class="lien_ext" href="'.$dossier_export.$fnom.'.zip">Récupérez le fichier exporté de la base SACoche.</a>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Import csv du contenu d'un fichier pour forcer les logins ou/et mdp utilisateurs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif($action=='user_import')
{
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		exit('Erreur : erreur avec le fichier transmis (taille dépassant probablement post_max_size ) !');
	}
	$extension = pathinfo($fnom_transmis,PATHINFO_EXTENSION);
	if(!in_array($extension,array('txt','csv')))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	$fichier_dest = 'import_'.$_SESSION['STRUCTURE_ID'].'.txt' ;
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
	function extraire_lignes($texte)
	{
		$texte = trim($texte);
		$texte = str_replace('"','',$texte);
		$texte = str_replace(array("\r\n","\r","\n"),'®',$texte);
		return explode('®',$texte);
	}
	$contenu = file_get_contents($dossier_import.$fichier_dest);
	// Mettre en UTF-8 si besoin ; pose surtout pb pour les import tableur
	if( (mb_detect_encoding($contenu,"auto",TRUE)!='UTF-8') || (!mb_check_encoding($contenu,'UTF-8')) )
	{
		$contenu = mb_convert_encoding($contenu,'UTF-8','Windows-1252'); // Si on utilise utf8_encode() ou mb_convert_encoding() sans le paramètre 'Windows-1252' ça pose des pbs pour '’' 'Œ' 'œ' etc.
	}
	$tab_lignes = extraire_lignes($contenu);
	// Utiliser la ligne d'en-tête pour déterminer la nature du séparateur, puis la supprimer
			if(mb_substr_count($tab_lignes[0],';')>2)  {$separateur = ';';}
	elseif(mb_substr_count($tab_lignes[0],',')>2)  {$separateur = ',';}
	elseif(mb_substr_count($tab_lignes[0],':')>2)  {$separateur = ':';}
	elseif(mb_substr_count($tab_lignes[0],"\t")>2) {$separateur = "\t";}
	else {exit('Erreur : séparateur du fichier csv indéterminé !');}
	unset($tab_lignes[0]);
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
				$tab_users_fichier['mdp'][]    = ($mdp!='mdp inchangé') ? mb_substr(clean_password($mdp),0,20) : '';
				$tab_users_fichier['nom'][]    = mb_substr(clean_nom($nom),0,20);
				$tab_users_fichier['prenom'][] = mb_substr(clean_prenom($prenom),0,20);
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
	$DB_SQL = 'SELECT * FROM livret_user ';
	$DB_SQL.= 'LEFT JOIN livret_groupe ON livret_user.livret_eleve_classe_id=livret_groupe.livret_groupe_id ';
	$DB_SQL.= 'WHERE livret_user.livret_structure_id=:structure_id ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_users_base['login'][$DB_ROW['livret_user_id']]  = $DB_ROW['livret_user_login'];
		$tab_users_base['mdp'][$DB_ROW['livret_user_id']]    = $DB_ROW['livret_user_password'];
		$tab_users_base['nom'][$DB_ROW['livret_user_id']]    = $DB_ROW['livret_user_nom'];
		$tab_users_base['prenom'][$DB_ROW['livret_user_id']] = $DB_ROW['livret_user_prenom'];
		$tab_users_base['info'][$DB_ROW['livret_user_id']]   = ($DB_ROW['livret_user_profil']=='eleve') ? $DB_ROW['livret_groupe_nom'] : mb_strtoupper($DB_ROW['livret_user_profil']) ;
	}
	// Observer le contenu du fichier et comparer avec le contenu de la base
	$fcontenu_pdf_tab = array();
	$lignes_ras = '';
	$lignes_mod = '';
	$lignes_pb  = '';
	foreach($tab_users_fichier['login'] as $i => $login)
	{
		if( ($tab_users_fichier['login'][$i]=='') && ($tab_users_fichier['mdp'][$i]=='') )
		{
			// Contenu du fichier à ignorer : login et mdp non indiqués
			$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>nom d\'utilisateur et mot de passe non imposés</td></tr>';
		}
		else
		{
			// On recherche l'id de l'utilisateur de la base de même nom et prénom
			$tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i]);
			$tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i]);
			$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
			if(count($tab_id_commun))
			{
				list($inutile,$id) = each($tab_id_commun);
			}
			else
			{
				$id = false;
			}
			if(!$id)
			{
				// Contenu du fichier à ignorer : utilisateur non trouvé dans la base
				$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>nom et prénom non trouvés dans la base</td></tr>';
			}
			elseif($tab_users_fichier['login'][$i]=='')
			{
				// login non indiqué (mdp forcément indiqué)...
				if(md5($tab_users_fichier['mdp'][$i])==$tab_users_base['mdp'][$id])
				{
					// Contenu du fichier à ignorer : login non indiqué et mdp identiques
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>mot de passe identique et nom d\'utilisateur non imposé</td></tr>';
				}
				else
				{
					// Contenu du fichier à modifier :login non indiqué et mdp différents
					$password = $tab_users_fichier['mdp'][$i];
					$password_crypte = crypter_mdp($password);
					$DB_SQL = 'UPDATE livret_user ';
					$DB_SQL.= 'SET livret_user_password=:password_crypte ';
					$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
					$DB_SQL.= 'LIMIT 1';
					$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$id,':password_crypte'=>$password_crypte);
					DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i].' ('.$tab_users_base['info'][$id].')').'</td><td>login : <i>inchangé</i> || <b>password : '.html($password).'</b></td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id]."\r\n".$tab_users_base['nom'][$id].' '.$tab_users_base['prenom'][$id]."\r\n".'Utilisateur : '.$tab_users_base['login'][$id]."\r\n".'Mot de passe : '.$password;
				}
			}
			elseif($tab_users_fichier['login'][$i]==$tab_users_base['login'][$id])
			{
				// login identique...
				if($tab_users_fichier['mdp'][$i]=='')
				{
					// Contenu du fichier à ignorer : logins identiques et mdp non indiqué
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>nom d\'utilisateur identique et mot de passe non imposé</td></tr>';
				}
				elseif(crypter_mdp($tab_users_fichier['mdp'][$i])==$tab_users_base['mdp'][$id])
				{
					// Contenu du fichier à ignorer : logins identiques et mdp identique
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>nom d\'utilisateur et mot de passe identiques</td></tr>';
				}
				else
				{
					// Contenu du fichier à modifier : logins identiques et mdp différents
					$password = $tab_users_fichier['mdp'][$i];
					$password_crypte = crypter_mdp($password);
					$DB_SQL = 'UPDATE livret_user ';
					$DB_SQL.= 'SET livret_user_password=:password_crypte ';
					$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
					$DB_SQL.= 'LIMIT 1';
					$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$id,':password_crypte'=>$password_crypte);
					DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i].' ('.$tab_users_base['info'][$id].')').'</td><td>login : <i>inchangé</i> || <b>password : '.html($password).'</b></td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id]."\r\n".$tab_users_base['nom'][$id].' '.$tab_users_base['prenom'][$id]."\r\n".'Utilisateur : '.$tab_users_base['login'][$id]."\r\n".'Mot de passe : '.$password;
				}
			}
			else
			{
				// logins différents...
				if(in_array($tab_users_fichier['login'][$i],$tab_users_base['login']))
				{
					// Contenu du fichier à problème : login déjà pris
					$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>nom d\'utilisateur proposé déjà affecté à un autre utilisateur</td></tr>';
				}
				elseif( ($tab_users_fichier['mdp'][$i]=='') || (crypter_mdp($tab_users_fichier['mdp'][$i])==$tab_users_base['mdp'][$id]) )
				{
					// Contenu du fichier à modifier : logins différents et mdp identiques on non imposé
					$login = $tab_users_fichier['login'][$i];
					$DB_SQL = 'UPDATE livret_user ';
					$DB_SQL.= 'SET livret_user_login=:login ';
					$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
					$DB_SQL.= 'LIMIT 1';
					$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$id,':login'=>$login);
					DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i].' ('.$tab_users_base['info'][$id].')').'</td><td><b>login : '.html($login).'</b> || password : <i>inchangé</i></td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id]."\r\n".$tab_users_base['nom'][$id].' '.$tab_users_base['prenom'][$id]."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : [ inchangé ]';
				}
				else
				{
					// Contenu du fichier à modifier : logins différents et mdp différents
					$login = $tab_users_fichier['login'][$i];
					$password = $tab_users_fichier['mdp'][$i];
					$password_crypte = crypter_mdp($password);
					$DB_SQL = 'UPDATE livret_user ';
					$DB_SQL.= 'SET livret_user_login=:login, livret_user_password=:password_crypte ';
					$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
					$DB_SQL.= 'LIMIT 1';
					$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$id,':login'=>$login,':password_crypte'=>$password_crypte);
					DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
					$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i].' ('.$tab_users_base['info'][$id].')').'</td><td><b>Login : '.html($login).'</b> || <b>Password : '.html($password).'</b></td></tr>';
					$fcontenu_pdf_tab[] = $tab_users_base['info'][$id]."\r\n".$tab_users_base['nom'][$id].' '.$tab_users_base['prenom'][$id]."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : '.$password;
				}
			}
		}
	}
	// On archive les nouveaux identifiants dans un fichier pdf (classe fpdf + script étiquettes)
	if(count($fcontenu_pdf_tab))
	{
		$fnom = 'identifiants_'.$_SESSION['STRUCTURE_ID'].'_'.time();
		require_once('./_fpdf/PDF_Label.php');
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
		echo'<div><a class="lien_ext" href="'.$dossier_login_mdp.$fnom.'.pdf">Récupérez les identifiants modifiés dans un fichier pdf (étiquettes à imprimer).</a></div>';
		echo'<div><label class="alerte">Attention : les mots de passe, cryptés, ne sont plus accessibles ultérieurement !</label></div>';
	}
	// On affiche le bilan
	echo'<div>';
	echo' <p><b>Résultat de l\'analyse et des opérations effectuées :</b></p>';
	echo' <table>';
	echo'  <tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont les identifiants ont été modifiés.</th></tr>';
	echo($lignes_mod) ? $lignes_mod : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont les identifiants n\'ont pas pu être modifiés.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont les identifiants sont inchangés.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody>';
	echo' </table>';
	echo'</div>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
