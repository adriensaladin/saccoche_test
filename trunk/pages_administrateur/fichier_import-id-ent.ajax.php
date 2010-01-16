<?php
/**
 * @version $Id: fichier_import-id-ent.ajax.php 8 2009-10-30 20:56:02Z thomas $
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

$dossier_import = './__tmp/import/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Recopier l'identifiant de Gepi comme identifiant de l'ENT
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='copy_id_Gepi')
{
	$DB_SQL = 'UPDATE livret_user ';
	$DB_SQL.= 'SET livret_user_id_ent=livret_user_id_gepi ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Recopier le login de SACoche comme identifiant de l'ENT
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='copy_login_SACoche')
{
	$DB_SQL = 'UPDATE livret_user ';
	$DB_SQL.= 'SET livret_user_id_ent=livret_user_login ';
	$DB_SQL.= 'WHERE livret_structure_id=:structure_id ';
	$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID']);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Import csv du contenu d'un fichier pour forcer les identifiants d'un ENT
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='import_ent')
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
	$fichier_dest = 'import-id-ent_'.$_SESSION['STRUCTURE_ID'].'.txt' ;
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// Pour récupérer les données des utilisateurs
	$tab_users_fichier           = array();
	$tab_users_fichier['id_ent'] = array();
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
	    if(mb_substr_count($tab_lignes[0],';')>1)  {$separateur = ';';}
	elseif(mb_substr_count($tab_lignes[0],',')>1)  {$separateur = ',';}
	elseif(mb_substr_count($tab_lignes[0],':')>1)  {$separateur = ':';}
	elseif(mb_substr_count($tab_lignes[0],"\t")>1) {$separateur = "\t";}
	else {exit('Erreur : séparateur du fichier csv indéterminé !');}
	unset($tab_lignes[0]);
	// Utiliser $_SESSION['SSO'] pour déterminer l'emplacement des données à récupérer
	require_once('./_inc/tableau_sso.php');	// Charge $tab_sso['nom'] = array('txt'=>'...' , 'doc'=>'...');
	// Récupérer les données
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		if(count($tab_elements)>2)
		{
			$tab_elements = array_map('clean_csv',$tab_elements);
			$id_ent = $tab_elements[ $tab_sso[$_SESSION['SSO']]['id_ent'] ];
			$nom    = $tab_elements[ $tab_sso[$_SESSION['SSO']]['nom']    ];
			$prenom = $tab_elements[ $tab_sso[$_SESSION['SSO']]['prenom'] ];
			if( ($id_ent!='') && ($nom!='') && ($prenom!='') )
			{
				$tab_users_fichier['id_ent'][] = mb_substr(clean_texte($id_ent),0,32);
				$tab_users_fichier['nom'][]    = mb_substr(clean_nom($nom),0,20);
				$tab_users_fichier['prenom'][] = mb_substr(clean_prenom($prenom),0,20);
			}
		}
	}
	// On trie
	array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['id_ent']);
	// On récupère le contenu de la base pour comparer, y compris les professeurs afin de comparer avec leurs id ent
	$tab_users_base           = array();
	$tab_users_base['id_ent'] = array();
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
		$tab_users_base['id_ent'][$DB_ROW['livret_user_id']] = $DB_ROW['livret_user_id_ent'];
		$tab_users_base['nom'][$DB_ROW['livret_user_id']]    = $DB_ROW['livret_user_nom'];
		$tab_users_base['prenom'][$DB_ROW['livret_user_id']] = $DB_ROW['livret_user_prenom'];
		$tab_users_base['info'][$DB_ROW['livret_user_id']]   = ($DB_ROW['livret_user_profil']=='eleve') ? $DB_ROW['livret_groupe_nom'] : mb_strtoupper($DB_ROW['livret_user_profil']) ;
	}
	// Observer le contenu du fichier et comparer avec le contenu de la base
	$lignes_ras = '';
	$lignes_mod = '';
	$lignes_pb  = '';
	foreach($tab_users_fichier['id_ent'] as $i => $id_ent)
	{
		if($tab_users_fichier['id_ent'][$i]=='')
		{
			// Contenu du fichier à ignorer : id_ent non indiqué
			$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant d\'ENT non imposé</td></tr>';
		}
		else
		{
			// On recherche l'id de l'utilisateur de la base de même nom et prénom
			$tab_id_nom    = array_keys($tab_users_base['nom'],$tab_users_fichier['nom'][$i]);
			$tab_id_prenom = array_keys($tab_users_base['prenom'],$tab_users_fichier['prenom'][$i]);
			$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
			$nb_homonymes  = count($tab_id_commun);
			if($nb_homonymes == 0)
			{
				// Contenu du fichier à ignorer : utilisateur non trouvé dans la base
				$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>nom et prénom non trouvés dans la base</td></tr>';
			}
			elseif($nb_homonymes > 1 )
			{
				// Contenu du fichier à ignorer : plusieurs homonymes trouvés dans la base
				$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>homonymes trouvés dans la base : traiter ce cas manuellement</td></tr>';
			}
			else
			{
				list($inutile,$id) = each($tab_id_commun);
				if($tab_users_fichier['id_ent'][$i]==$tab_users_base['id_ent'][$id])
				{
					// Contenu du fichier à ignorer : id_ent identique
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant d\'ENT identique</td></tr>';
				}
				else
				{
					// id_ent différents...
					if(in_array($tab_users_fichier['id_ent'][$i],$tab_users_base['id_ent']))
					{
						// Contenu du fichier à problème : id_ent déjà pris
						$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant d\'ENT déjà affecté à un autre utilisateur</td></tr>';
					}
					else
					{
						// Contenu du fichier à modifier : id_ent nouveau
						$DB_SQL = 'UPDATE livret_user ';
						$DB_SQL.= 'SET livret_user_id_ent=:id_ent ';
						$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:user_id ';
						$DB_SQL.= 'LIMIT 1';
						$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':user_id'=>$id,':id_ent'=>$id_ent);
						DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
						$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i].' ('.$tab_users_base['info'][$id].')').'</td><td><b>Id ENT : '.html($id_ent).'</b></td></tr>';
					}
				}
			}
		}
	}
	// On affiche le bilan
	echo'<div>';
	echo' <p><b>Résultat de l\'analyse et des opérations effectuées :</b></p>';
	echo' <table>';
	echo'  <tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant ENT a été modifié.</th></tr>';
	echo($lignes_mod) ? $lignes_mod : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant ENT n\'a pas pu être modifié.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant ENT est inchangé.</th></tr>';
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
