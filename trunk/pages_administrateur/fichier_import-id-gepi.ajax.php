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
//	Recopier l'identifiant de l'ENT comme identifiant de Gepi
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='copy_id_ENT')
{
	DB_STRUCTURE_recopier_identifiants('id_ent','id_gepi');
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Recopier le login de SACoche comme identifiant de Gepi
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='copy_login_SACoche')
{
	DB_STRUCTURE_recopier_identifiants('login','id_gepi');
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Import csv du contenu d'un fichier pour forcer les identifiants professeurs de Gepi
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='import_gepi_profs')
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
	if($fnom_transmis != 'base_professeurs_gepi.csv')
	{
		exit('Erreur : le nom du fichier n\'est pas "base_professeurs_gepi.csv" !');
	}
	$fichier_dest = 'import-gepi-profs_'.$_SESSION['BASE'].'.txt' ;
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// Pour récupérer les données des utilisateurs
	$tab_users_fichier            = array();
	$tab_users_fichier['id_gepi'] = array();
	$tab_users_fichier['nom']     = array();
	$tab_users_fichier['prenom']  = array();
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
	// Déterminer la nature du séparateur
	    if(mb_substr_count($tab_lignes[0],';')>1)  {$separateur = ';';}
	elseif(mb_substr_count($tab_lignes[0],',')>1)  {$separateur = ',';}
	elseif(mb_substr_count($tab_lignes[0],':')>1)  {$separateur = ':';}
	elseif(mb_substr_count($tab_lignes[0],"\t")>1) {$separateur = "\t";}
	else {exit('Erreur : séparateur du fichier csv indéterminé !');}
	// Pas de ligne d'en-tête à supprimer
	// Récupérer les données
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		if(count($tab_elements)>2)
		{
			$tab_elements = array_map('clean_csv',$tab_elements);
			$id_gepi = $tab_elements[2];
			$nom     = $tab_elements[0];
			$prenom  = $tab_elements[1];
			if( ($id_gepi!='') && ($nom!='') && ($prenom!='') )
			{
				$tab_users_fichier['id_gepi'][] = mb_substr(clean_texte($id_gepi),0,32);
				$tab_users_fichier['nom'][]     = mb_substr(clean_nom($nom),0,20);
				$tab_users_fichier['prenom'][]  = mb_substr(clean_prenom($prenom),0,20);
			}
		}
	}
	// On trie
	array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['id_gepi']);
	// On récupère le contenu de la base pour comparer (la recherche d'éventuels doublons d'ids gepi ne se fera que sur les profs...)
	$tab_users_base            = array();
	$tab_users_base['id_gepi'] = array();
	$tab_users_base['nom']     = array();
	$tab_users_base['prenom']  = array();
	$DB_TAB = DB_STRUCTURE_lister_users('professeur',$only_actifs=false,$with_classe=false);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['id_gepi'][$DB_ROW['user_id']] = $DB_ROW['user_id_gepi'];
		$tab_users_base['nom'][$DB_ROW['user_id']]     = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']]  = $DB_ROW['user_prenom'];
	}
	// Observer le contenu du fichier et comparer avec le contenu de la base
	$lignes_ras = '';
	$lignes_mod = '';
	$lignes_pb  = '';
	foreach($tab_users_fichier['id_gepi'] as $i => $id_gepi)
	{
		if($tab_users_fichier['id_gepi'][$i]=='')
		{
			// Contenu du fichier à ignorer : id_gepi non indiqué
			$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant de GEPI non indiqué</td></tr>';
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
				if($tab_users_fichier['id_gepi'][$i]==$tab_users_base['id_gepi'][$id])
				{
					// Contenu du fichier à ignorer : id_gepi identique
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant de GEPI identique</td></tr>';
				}
				else
				{
					// id_gepi différents...
					if(in_array($tab_users_fichier['id_gepi'][$i],$tab_users_base['id_gepi']))
					{
						// Contenu du fichier à problème : id_gepi déjà pris
						$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant de GEPI déjà affecté à un autre utilisateur</td></tr>';
					}
					else
					{
						// Contenu du fichier à modifier : id_gepi nouveau
						DB_STRUCTURE_modifier_utilisateur( $id , array(':id_gepi'=>$id_gepi) );
						$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td><b>Id Gepi : '.html($id_gepi).'</b></td></tr>';
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
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi a été modifié.</th></tr>';
	echo($lignes_mod) ? $lignes_mod : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi n\'a pas pu être modifié.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi est inchangé.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody>';
	echo' </table>';
	echo'</div>';
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Import csv du contenu d'un fichier pour forcer les identifiants élèves de Gepi
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='import_gepi_eleves')
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
	if($fnom_transmis != 'base_eleves_gepi.csv')
	{
		exit('Erreur : le nom du fichier n\'est pas "base_eleves_gepi.csv" !');
	}
	$fichier_dest = 'import-gepi-eleves_'.$_SESSION['BASE'].'.txt' ;
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
	{
		exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
	}
	// Pour récupérer les données des utilisateurs
	$tab_users_fichier               = array();
	$tab_users_fichier['id_gepi']    = array();
	$tab_users_fichier['nom']        = array();
	$tab_users_fichier['prenom']     = array();
	$tab_users_fichier['num_sconet'] = array();
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
	// Déterminer la nature du séparateur
	    if(mb_substr_count($tab_lignes[0],';')>1)  {$separateur = ';';}
	elseif(mb_substr_count($tab_lignes[0],',')>1)  {$separateur = ',';}
	elseif(mb_substr_count($tab_lignes[0],':')>1)  {$separateur = ':';}
	elseif(mb_substr_count($tab_lignes[0],"\t")>1) {$separateur = "\t";}
	else {exit('Erreur : séparateur du fichier csv indéterminé !');}
	// Pas de ligne d'en-tête à supprimer
	// Récupérer les données
	foreach ($tab_lignes as $ligne_contenu)
	{
		$tab_elements = explode($separateur,$ligne_contenu);
		if(count($tab_elements)>2)
		{
			$tab_elements = array_map('clean_csv',$tab_elements);
			$id_gepi    = $tab_elements[2];
			$nom        = $tab_elements[0];
			$prenom     = $tab_elements[1];
			$num_sconet = (isset($tab_elements[4])) ? $tab_elements[4] : 0;
			if( ($id_gepi!='') && ($nom!='') && ($prenom!='') )
			{
				$tab_users_fichier['id_gepi'][]    = mb_substr(clean_texte($id_gepi),0,32);
				$tab_users_fichier['nom'][]        = mb_substr(clean_nom($nom),0,20);
				$tab_users_fichier['prenom'][]     = mb_substr(clean_prenom($prenom),0,20);
				$tab_users_fichier['num_sconet'][] = clean_entier($num_sconet);
			}
		}
	}
	// On trie
	array_multisort($tab_users_fichier['nom'],SORT_ASC,SORT_STRING,$tab_users_fichier['prenom'],SORT_ASC,SORT_STRING,$tab_users_fichier['id_gepi'],$tab_users_fichier['num_sconet']);
	// On récupère le contenu de la base pour comparer (la recherche d'éventuels doublons d'ids gepi ne se fera que sur les élèves...)
	$tab_users_base               = array();
	$tab_users_base['id_gepi']    = array();
	$tab_users_base['nom']        = array();
	$tab_users_base['prenom']     = array();
	$tab_users_base['num_sconet'] = array();
	$DB_TAB = DB_STRUCTURE_lister_users('eleve',$only_actifs=false,$with_classe=false);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_users_base['id_gepi'][$DB_ROW['user_id']]    = $DB_ROW['user_id_gepi'];
		$tab_users_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
		$tab_users_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
		$tab_users_base['num_sconet'][$DB_ROW['user_id']] = $DB_ROW['user_num_sconet'];
	}
	// Observer le contenu du fichier et comparer avec le contenu de la base
	$lignes_ras = '';
	$lignes_mod = '';
	$lignes_pb  = '';
	foreach($tab_users_fichier['id_gepi'] as $i => $id_gepi)
	{
		if($tab_users_fichier['id_gepi'][$i]=='')
		{
			// Contenu du fichier à ignorer : id_gepi non indiqué
			$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant de GEPI non indiqué</td></tr>';
		}
		else
		{
			$id = 0;
			// Si le num_sconet est rensigné, on recherche l'id de l'utilisateur de la base de même num_sconet
			if($tab_users_fichier['num_sconet'][$i])
			{
				$id = array_search($tab_users_fichier['num_sconet'][$i],$tab_users_base['num_sconet']);
			}
			if(!$id)
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
				}
			}
			if($id)
			{
				if($tab_users_fichier['id_gepi'][$i]==$tab_users_base['id_gepi'][$id])
				{
					// Contenu du fichier à ignorer : id_gepi identique
					$lignes_ras .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant de GEPI identique</td></tr>';
				}
				else
				{
					// id_gepi différents...
					if(in_array($tab_users_fichier['id_gepi'][$i],$tab_users_base['id_gepi']))
					{
						// Contenu du fichier à problème : id_gepi déjà pris
						$lignes_pb .= '<tr><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td>identifiant de GEPI déjà affecté à un autre utilisateur</td></tr>';
					}
					else
					{
						// Contenu du fichier à modifier : id_gepi nouveau
						DB_STRUCTURE_modifier_utilisateur( $id , array(':id_gepi'=>$id_gepi) );
						$lignes_mod .= '<tr class="new"><td>'.html($tab_users_fichier['nom'][$i].' '.$tab_users_fichier['prenom'][$i]).'</td><td><b>Id Gepi : '.html($id_gepi).'</b></td></tr>';
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
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi a été modifié.</th></tr>';
	echo($lignes_mod) ? $lignes_mod : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi n\'a pas pu être modifié.</th></tr>';
	echo($lignes_pb) ? $lignes_pb : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Utilisateurs trouvés dans le fichier dont l\'identifiant Gepi est inchangé.</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucun</td></tr>';
	echo'  </tbody>';
	echo' </table>';
	echo'</div>';
	exit();
}

//	C'est pas normal d'arriver là !
exit('Erreur avec les données transmises !');

?>
