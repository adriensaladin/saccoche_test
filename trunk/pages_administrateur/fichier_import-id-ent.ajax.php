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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_GET['action']!='initialiser')){exit('Action désactivée pour la démo...');}

$action = (isset($_GET['action'])) ? $_GET['action'] : '';

$dossier_import = './__tmp/import/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Recopier l'identifiant de Gepi comme identifiant de l'ENT
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='copy_id_Gepi')
{
	DB_recopier_identifiants('id_gepi','id_ent');
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Recopier le login de SACoche comme identifiant de l'ENT
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='copy_login_SACoche')
{
	DB_recopier_identifiants('login','id_ent');
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
	$fichier_dest = 'import-id-ent_'.$_SESSION['BASE'].'.txt' ;
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
	if( (!perso_mb_detect_encoding_utf8($contenu)) || (!mb_check_encoding($contenu,'UTF-8')) )
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
	// Utiliser $_SESSION['CONNEXION_MODE'] et $_SESSION['CONNEXION_NOM'] pour déterminer l'emplacement des données à récupérer
	require_once('./_inc/tableau_sso.php');
	// Récupérer les données
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		if(count($tab_elements)>2)
		{
			$tab_elements = array_map('clean_csv',$tab_elements);
			$id_ent = $tab_elements[ $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_id_ent'] ];
			$nom    = $tab_elements[ $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_nom']    ];
			$prenom = $tab_elements[ $tab_connexion_info[$_SESSION['CONNEXION_MODE']][$_SESSION['CONNEXION_NOM']]['csv_prenom'] ];
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
	$DB_TAB = DB_lister_users('tous',$only_actifs=false,$with_classe=true);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['id_ent'][$DB_ROW['user_id']] = $DB_ROW['user_id_ent'];
		$tab_users_base['nom'][$DB_ROW['user_id']]    = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']] = $DB_ROW['user_prenom'];
		$tab_users_base['info'][$DB_ROW['user_id']]   = ($DB_ROW['user_profil']=='eleve') ? $DB_ROW['groupe_nom'] : mb_strtoupper($DB_ROW['user_profil']) ;
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
						DB_modifier_utilisateur( $id , array(':id_ent'=>$id_ent) );
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
