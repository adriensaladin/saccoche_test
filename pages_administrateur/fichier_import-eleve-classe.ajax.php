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
if($_SESSION['SESAMATH_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

$step = (isset($_POST['f_step'])) ? clean_entier($_POST['f_step']) : '';

$dossier_import    = './__tmp/import/';
$dossier_login_mdp = './__tmp/login-mdp/';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 1 - fichier Sconet ou tableur : récupération
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( $step==1 )
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
	if(!in_array($extension,array('zip','xml','txt','csv')))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	$fichier_dest = (in_array($extension,array('zip','xml'))) ? 'import_'.$_SESSION['BASE'].'.xml' : 'import_'.$_SESSION['BASE'].'.txt' ;
	$fichier_kill = (in_array($extension,array('txt','csv'))) ? 'import_'.$_SESSION['BASE'].'.xml' : 'import_'.$_SESSION['BASE'].'.txt' ;
	if(in_array($extension,array('xml','txt','csv')))
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
		$zip = new ZipArchive;
		if($zip->open($fnom_serveur)!==true)
		{
			exit('Erreur : votre archive ZIP n\'a pas pu être ouverte !');
		}
		if($zip->extractTo($dossier_import,'ElevesSansAdresses.xml')!==true)
		{
			exit('Erreur : fichier ElevesSansAdresses.xml non trouvé dans votre archive !');
		}
		$zip->close();
		@unlink($dossier_import.$fichier_dest);
		if(!rename($dossier_import.'ElevesSansAdresses.xml' , $dossier_import.$fichier_dest))
		{
			exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
		}
	}
	@unlink($dossier_import.$fichier_kill);
	echo'<div id="ok">';
	echo' <p><label class="valide">Votre fichier a été correctement réceptionné.</label></p>';
	echo' <p><span class="tab"><a href="#" class="step2">Passer à l\'étape 2.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 2 - fichier Sconet ou tableur : traitement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==2 )
{
	// Pour récupérer les données des élèves
	$tab_eleves               = array();
	$tab_eleves['num_sconet'] = array();
	$tab_eleves['reference']  = array();
	$tab_eleves['nom']        = array();
	$tab_eleves['prenom']     = array();
	$tab_eleves['classe']     = array();
	// Pour récupérer les données des classes
	$tab_classes              = array();
	$tab_classes['ref']       = array();
	$tab_classes['niveau']    = array();
	// On a pu réceptionner 2 fichiers suivant la procédure choisie
	$fichier_src_sconet  = 'import_'.$_SESSION['BASE'].'.xml';
	$fichier_src_tableur = 'import_'.$_SESSION['BASE'].'.txt' ;
	if(is_file($dossier_import.$fichier_src_sconet))
	{
		// Cas d'un XML SCONET
		$xml = @simplexml_load_file($dossier_import.$fichier_src_sconet);
		if($xml===false)
		{
			exit('Erreur : le fichier XML transmis n\'est pas valide !');
		}
		$uai = $xml->PARAMETRES->UAJ;
		if($uai===false)
		{
			exit('Erreur : le fichier XML transmis n\'est pas le bon fichier Sconet !');
		}
		foreach ($xml->DONNEES->ELEVES->ELEVE as $eleve)
		{
			$eleve_id                            = clean_entier($eleve->attributes()->ELEVE_ID);
			$tab_eleves['num_sconet'][$eleve_id] = clean_entier($eleve->attributes()->ELENOET);
			$tab_eleves['reference'][$eleve_id]  = clean_ref($eleve->ID_NATIONAL);
			$tab_eleves['nom'][$eleve_id]        = clean_nom($eleve->NOM);
			$tab_eleves['prenom'][$eleve_id]     = clean_prenom($eleve->PRENOM);
			$tab_eleves['classe'][$eleve_id]     = '';
			$tab_eleves['niveau'][$eleve_id]     = clean_ref($eleve->CODE_MEF);
		}
		foreach ($xml->DONNEES->STRUCTURES->STRUCTURES_ELEVE as $structures)
		{
			$eleve_id   = clean_entier($structures->attributes()->ELEVE_ID);
			foreach ($structures->STRUCTURE as $structure)
			{
				if($structure->TYPE_STRUCTURE == 'D')
				{
					$classe_ref = clean_ref($structure->CODE_STRUCTURE);
					if(isset($tab_eleves['classe'][$eleve_id]))
					{
						$tab_eleves['classe'][$eleve_id] = $classe_ref;
					}
					if( (!in_array($classe_ref,$tab_classes['ref'])) && (isset($tab_eleves['niveau'][$eleve_id])) )
					{
						$tab_classes['ref'][]    = $classe_ref;
						$tab_classes['niveau'][] = $tab_eleves['niveau'][$eleve_id];
					}
				}
			}
		}
	}
	elseif(is_file($dossier_import.$fichier_src_tableur))
	{
		// Cas d'un CSV
		function extraire_lignes($texte)
		{
			$texte = trim($texte);
			$texte = str_replace('"','',$texte);
			$texte = str_replace(array("\r\n","\r","\n"),'®',$texte);
			return explode('®',$texte);
		}
		$contenu = file_get_contents($dossier_import.$fichier_src_tableur);
		// Mettre en UTF-8 si besoin ; pose surtout pb pour les import tableur
		if( (!perso_mb_detect_encoding_utf8($contenu)) || (!mb_check_encoding($contenu,'UTF-8')) )
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
				list($ref,$nom,$prenom,$classe) = $tab_elements;
				if( ($nom!='') && ($prenom!='') )
				{
					$classe_ref                 = mb_substr(clean_ref($classe),0,8);
					$tab_eleves['num_sconet'][] = 0;
					$tab_eleves['reference'][]  = clean_ref($ref);
					$tab_eleves['nom'][]        = clean_nom($nom);
					$tab_eleves['prenom'][]     = clean_prenom($prenom);
					$tab_eleves['classe'][]     = $classe_ref;
					if( ($classe_ref) && (!in_array($classe_ref,$tab_classes['ref'])) )
					{
						$tab_classes['ref'][]    = $classe_ref;
						$tab_classes['niveau'][] = '';
					}
				}
			}
		}
	}
	else
	{
		exit('Erreur : le fichier n\'a pas été retrouvé !');
	}
	// On trie
	array_multisort($tab_eleves['nom'],SORT_ASC,SORT_STRING,$tab_eleves['prenom'],SORT_ASC,SORT_STRING,$tab_eleves['num_sconet'],$tab_eleves['reference'],$tab_eleves['classe']);
	array_multisort($tab_classes['niveau'],SORT_DESC,SORT_STRING,$tab_classes['ref']);
	// On enregistre
	$tab_eleves_fichier = array('num_sconet'=>$tab_eleves['num_sconet'],'reference'=>$tab_eleves['reference'],'nom'=>$tab_eleves['nom'],'prenom'=>$tab_eleves['prenom'],'classe'=>$tab_eleves['classe']);
	file_put_contents($dossier_import.'import_'.$_SESSION['BASE'].'_eleves.txt',serialize($tab_eleves_fichier));
	$tab_classes_fichier = array('ref'=>$tab_classes['ref'],'niveau'=>$tab_classes['niveau']);
	file_put_contents($dossier_import.'import_'.$_SESSION['BASE'].'_classes.txt',serialize($tab_classes_fichier));
	echo'<div id="ok">';
	echo' <p><label class="valide">Les données ont été correctement extraites.</label></p>';
	echo' <p><span class="tab"><a href="#" class="step3">Passer à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 3 - importation des classes : paramétrage
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==3 )
{
	$fnom = 'import_'.$_SESSION['BASE'].'_classes.txt';
	if(!file_exists($dossier_import.$fnom))
	{
		exit('Erreur : le fichier contenant les classes est introuvable !');
	}
	$contenu = file_get_contents($dossier_import.$fnom);
	$tab_classes_fichier = @unserialize($contenu);	// $tab_classes_fichier['ref'] : i -> ref ; $tab_classes_fichier['niveau'] : i -> niveau
	if($tab_classes_fichier===FALSE)
	{
		exit('Erreur : le fichier contenant les classes est syntaxiquement incorrect !');
	}
	// $tab_classes_base['ref'] : id -> ref ; $tab_classes_base['nom'] : id -> nom
	$tab_classes_base        = array();
	$tab_classes_base['ref'] = array();
	$tab_classes_base['nom'] = array();
	$DB_TAB = DB_STRUCTURE_lister_classes();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_classes_base['ref'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_ref'];
		$tab_classes_base['nom'][$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
	}
	// Comparer sconet et base : contenu à conserver
	$lignes_ras = '';
	foreach($tab_classes_fichier['ref'] as $i => $ref)
	{
		$id = array_search($ref,$tab_classes_base['ref']);
		if($id!==false)
		{
			$lignes_ras .= '<tr><th>'.html($tab_classes_base['ref'][$id]).'</th><td>'.html($tab_classes_base['nom'][$id]).'</td></tr>';
			unset($tab_classes_fichier['ref'][$i] , $tab_classes_fichier['niveau'][$i] , $tab_classes_base['ref'][$id] , $tab_classes_base['nom'][$id]);
		}
	}
	// Comparer sconet et base : contenu à supprimer
	$lignes_del = '';
	if(count($tab_classes_base['ref']))
	{
		foreach($tab_classes_base['ref'] as $id => $ref)
		{
			$lignes_del .= '<tr><th>'.html($ref).'</th><td>Supprimer <input id="del_'.$id.'" name="del_'.$id.'" type="checkbox" value="1" checked="checked" /> '.html($tab_classes_base['nom'][$id]).'</td></tr>';
		}
	}
	// Comparer sconet et base : contenu à ajouter
	$lignes_add = '';
	if(count($tab_classes_fichier['ref']))
	{
		$mode = ($tab_classes_fichier['ref'][0]=='') ? 'tableur' : 'sconet' ;
		$select_niveau = '<option value=""></option>';
		$tab_niveau_ref = array();
		$DB_TAB = DB_STRUCTURE_lister_niveaux_etablissement($_SESSION['NIVEAUX'],$listing_paliers=false);
		foreach($DB_TAB as $DB_ROW)
		{
			$select_niveau .= '<option value="'.$DB_ROW['niveau_id'].'">'.html($DB_ROW['niveau_nom']).'</option>';
			$key = ($mode=='sconet') ? $DB_ROW['code_mef'] : $DB_ROW['niveau_ref'] ;
			$tab_niveau_ref[$key] = $DB_ROW['niveau_id'];
		}
		foreach($tab_classes_fichier['ref'] as $i => $ref)
		{
			// On préselectionne un niveau ; pour un fichier tableur on compare avec le début du nom, pour un fichier sconet on compare avec un masque d'expression régulière.
			$id_checked = '';
			foreach($tab_niveau_ref as $masque_recherche => $niveau_id)
			{
				if($mode=='sconet')
				{
					$id_checked = (preg_match('/^'.$masque_recherche.'$/',$tab_classes_fichier['niveau'][$i])) ? $niveau_id : '';
				}
				elseif($mode=='tableur')
				{
					$id_checked = (strpos($ref,$masque_recherche)) ? $niveau_id : '';
				}
				if($id_checked)
				{
					break;
				}
			}
			$lignes_add .= '<tr><th>'.html($ref).'<input id="add_ref_'.$i.'" name="add_ref_'.$i.'" type="hidden" value="'.html($ref).'" /></th><td>Niveau : <select id="add_niv_'.$i.'" name="add_niv_'.$i.'">'.str_replace('value="'.$id_checked.'"','value="'.$id_checked.'" selected="selected"',$select_niveau).'</select> Nom complet : <input id="add_nom_'.$i.'" name="add_nom_'.$i.'" size="10" type="text" value="'.html($ref).'" maxlength="20" /></td></tr>';
		}
	}
	echo'<div id="ok">';
	echo' <p><label class="valide">Veuillez vérifier le résultat de l\'analyse des classes puis valider.</label></p>';
	echo' <table>';
	echo'  <tbody>';
	echo'   <tr><th colspan="2">Classes actuelles à conserver</th></tr>';
	echo($lignes_ras) ? $lignes_ras : '<tr><td colspan="2">Aucune</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Classes nouvelles à ajouter</th></tr>';
	echo($lignes_add) ? $lignes_add : '<tr><td colspan="2">Aucune</td></tr>';
	echo'  </tbody><tbody>';
	echo'   <tr><th colspan="2">Classes anciennes à supprimer</th></tr>';
	echo($lignes_del) ? $lignes_del : '<tr><td colspan="2">Aucune</td></tr>';
	echo'  </tbody>';
	echo' <table>';
	echo' <p><span class="tab"><a href="#" class="step4">Valider et passer à l\'étape 4.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 4 - importation des classes : résultat
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==4 )
{
	// Récupérer les éléments postés
	$tab_add = array();
	$tab_del = array();
	foreach($_POST as $key => $val)
	{
		if(substr($key,0,8)=='add_ref_')
		{
			$i = substr($key,8);
			$tab_add[$i]['ref'] = clean_ref($val);
		}
		elseif(substr($key,0,8)=='add_nom_')
		{
			$i = substr($key,8);
			$tab_add[$i]['nom'] = clean_texte($val);
		}
		elseif(substr($key,0,8)=='add_niv_')
		{
			$i = substr($key,8);
			$tab_add[$i]['niv'] = clean_entier($val);
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
				DB_STRUCTURE_ajouter_groupe('classe',0,$tab['ref'],$tab['nom'],$tab['niv']);
				$nb_add++;
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
	echo'<div id="ok">';
	echo' <p><label class="valide">'.$nb_ras.' classe'.$s_ras.' présente'.$s_ras.' + '.$nb_add.' classe'.$s_add.' ajoutée'.$s_add.' &minus; '.$nb_del.' classe'.$s_del.' supprimée'.$s_del.' = '.$nb_fin.' classe'.$s_fin.' résultante'.$s_fin.'.</label></p>';
	echo' <p><span class="tab"><a href="#" class="step5">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo' <table>';
	echo'  <thead>';
	echo'   <tr><th>Niveau</th><th>Référence</th><th>Nom complet</th></tr>';
	echo'  </thead>';
	echo'  <tbody>';
	echo    $lignes;
	echo'  </tbody>';
	echo' </table>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 5 - importation des élèves : paramétrage
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==5 )
{
	$fnom = 'import_'.$_SESSION['BASE'].'_eleves.txt';
	if(!file_exists($dossier_import.$fnom))
	{
		exit('Erreur : le fichier contenant les élèves est introuvable !');
	}
	$contenu = file_get_contents($dossier_import.$fnom);
	$tab_eleves_fichier = @unserialize($contenu);	// $tab_eleves_fichier['champ'] : i -> valeur du champ
	if($tab_eleves_fichier===FALSE)
	{
		exit('Erreur : le fichier contenant les élèves est syntaxiquement incorrect !');
	}
	// $tab_eleves_base['champ'] : id -> valeur du champ
	$tab_eleves_base               = array();
	$tab_eleves_base['num_sconet'] = array();
	$tab_eleves_base['reference']  = array();
	$tab_eleves_base['nom']        = array();
	$tab_eleves_base['prenom']     = array();
	$tab_eleves_base['classe']     = array();
	$tab_eleves_base['statut']     = array();
	$DB_TAB = DB_STRUCTURE_lister_users($profil='eleve',$only_actifs=false,$with_classe=true);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleves_base['num_sconet'][$DB_ROW['user_id']] = $DB_ROW['user_num_sconet'];
		$tab_eleves_base['reference'][$DB_ROW['user_id']]  = $DB_ROW['user_reference'];
		$tab_eleves_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
		$tab_eleves_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
		$tab_eleves_base['classe'][$DB_ROW['user_id']]     = $DB_ROW['groupe_ref'];
		$tab_eleves_base['statut'][$DB_ROW['user_id']]     = $DB_ROW['user_statut'];
	}
	// $tab_classe_ref['ref'] -> id : tableau des id des classes à partir de leurs références
	$tab_classe_ref = array();
	$DB_TAB = DB_STRUCTURE_lister_classes();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_classe_ref[$DB_ROW['groupe_ref']] = (int) $DB_ROW['groupe_id'];
	}
	// Pour préparer l'affichage
	$lignes_ignorer   = '';
	$lignes_ajouter   = '';
	$lignes_retirer   = '';
	$lignes_modifier  = '';
	$lignes_conserver = '';
	$lignes_inchanger = '';
	// Pour préparer l'enregistrement des données
	$tab_eleves_ajout = array();
	$tab_eleves_modif = array();
	// Comparer fichier et base : c'est parti !
	$tab_indices_fichier = array_keys($tab_eleves_fichier['num_sconet']);
	// Parcourir chaque entrée du fichier
	foreach($tab_indices_fichier as $i)
	{
		$id = false;
		// Recherche sur num_sconet
		if( (!$id) && ($tab_eleves_fichier['num_sconet'][$i]) )
		{
			$id = array_search($tab_eleves_fichier['num_sconet'][$i],$tab_eleves_base['num_sconet']);
		}
		// Si pas trouvé, recherche sur reference
		if( (!$id) && ($tab_eleves_fichier['reference'][$i]) )
		{
			$id = array_search($tab_eleves_fichier['reference'][$i],$tab_eleves_base['reference']);
		}
		// Si pas trouvé, recherche sur nom prénom
		if(!$id)
		{
			$tab_id_nom    = array_keys($tab_eleves_base['nom'],$tab_eleves_fichier['nom'][$i]);
			$tab_id_prenom = array_keys($tab_eleves_base['prenom'],$tab_eleves_fichier['prenom'][$i]);
			$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
			if(count($tab_id_commun))
			{
				list($inutile,$id) = each($tab_id_commun);
			}
		}
		// Cas [1] : présent dans le fichier, absent de la base, pas de classe dans le fichier : contenu à ignorer (probablement des anciens élèves, ou des élèves jamais venus, qu'il est inutile d'importer)
		if( (!$id) && (!$tab_eleves_fichier['classe'][$i]) )
		{
			$lignes_ignorer .= '<tr><th>Ignorer</th><td>'.html($tab_eleves_fichier['num_sconet'][$i].' / '.$tab_eleves_fichier['reference'][$i].' || '.$tab_eleves_fichier['nom'][$i].' '.$tab_eleves_fichier['prenom'][$i].' ('.$tab_eleves_fichier['classe'][$i].')').'</td></tr>';
		}
		// Cas [2] : présent dans le fichier, absent de la base, classe indiquée dans le fichier : contenu à ajouter (nouvel élève)
		elseif( (!$id) && ($tab_eleves_fichier['classe'][$i]) )
		{
			$lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i.'" name="add_'.$i.'" type="checkbox" value="1" checked="checked" /></th><td>'.html($tab_eleves_fichier['num_sconet'][$i].' / '.$tab_eleves_fichier['reference'][$i].' || '.$tab_eleves_fichier['nom'][$i].' '.$tab_eleves_fichier['prenom'][$i].' ('.$tab_eleves_fichier['classe'][$i].')').'</td></tr>';
			$tab_eleves_ajout[$i] = array( 'num_sconet'=>$tab_eleves_fichier['num_sconet'][$i] , 'reference'=>$tab_eleves_fichier['reference'][$i] , 'nom'=>$tab_eleves_fichier['nom'][$i] , 'prenom'=>$tab_eleves_fichier['prenom'][$i] , 'classe'=>$tab_classe_ref[$tab_eleves_fichier['classe'][$i]] );
		}
		// Cas [3] : présent dans le fichier, présent dans la base, pas de classe dans le fichier, statut actif dans la base : contenu à retirer (probablement des élèves nouvellement sortants)
		elseif( (!$tab_eleves_fichier['classe'][$i]) && ($tab_eleves_base['statut'][$id]) )
		{
			$lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id.'" name="del_'.$id.'" type="checkbox" value="1" checked="checked" /></th><td>'.html($tab_eleves_base['num_sconet'][$id].' / '.$tab_eleves_base['reference'][$id].' || '.$tab_eleves_base['nom'][$id].' '.$tab_eleves_base['prenom'][$id].' ('.$tab_eleves_base['classe'][$id].')').' || <b>Statut : actif => inactif</b></td></tr>';
		}
		// Cas [4] : présent dans le fichier, présent dans la base, pas de classe dans le fichier, statut inactif dans la base : contenu inchangé (probablement des anciens élèves déjà écartés)
		elseif( (!$tab_eleves_fichier['classe'][$i]) && (!$tab_eleves_base['statut'][$id]) )
		{
			$lignes_inchanger .= '<tr><th>Ignorer</th><td>'.html($tab_eleves_fichier['num_sconet'][$i].' / '.$tab_eleves_fichier['reference'][$i].' || '.$tab_eleves_fichier['nom'][$i].' '.$tab_eleves_fichier['prenom'][$i].' ('.$tab_eleves_fichier['classe'][$i].')').'</td></tr>';
		}
		else
		{
			// On compare les données de 2 enregistrements pour voir si des choses ont été modifiées
			$td_modif = '';
			$nb_modif = 0;
			$tab_champs = array( 'num_sconet'=>'n° Sconet' , 'reference'=>'Référence' , 'nom'=>'Nom' , 'prenom'=>'Prénom' , 'classe'=>'Classe' );
			foreach($tab_champs as $champ_ref => $champ_aff)
			{
				if($tab_eleves_base[$champ_ref][$id]!=$tab_eleves_fichier[$champ_ref][$i])
				{
					$td_modif .= ' || <b>'.$champ_aff.' : '.html($tab_eleves_base[$champ_ref][$id]).' => '.html($tab_eleves_fichier[$champ_ref][$i]).'</b>';
					$tab_eleves_modif[$id][$champ_ref] = ($champ_ref!='classe') ? $tab_eleves_fichier[$champ_ref][$i] : $tab_classe_ref[$tab_eleves_fichier[$champ_ref][$i]] ;
					$nb_modif++;
				}
				else
				{
					$td_modif .= ' || '.$champ_aff.' : '.html($tab_eleves_base[$champ_ref][$id]);
					$tab_eleves_modif[$id][$champ_ref] = false;
				}
			}
			if(!$tab_eleves_base['statut'][$id])
			{
				$td_modif .= ' || <b>Statut : inactif => actif</b>';
				$tab_eleves_modif[$id]['statut'] = 1 ;
				$nb_modif++;
			}
			else
			{
				$tab_eleves_modif[$id]['statut'] = false ;
			}
			// Cas [5] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier, statut inactif dans la base et/ou différence constatée : contenu à modifier (élève revenant ou mise à jour)
			if($nb_modif)
			{
				$lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$id.'" name="mod_'.$id.'" type="checkbox" value="1" checked="checked" /></th><td>'.mb_substr($td_modif,4).'</td></tr>';
			}
			// Cas [6] : présent dans le fichier, présent dans la base, classe indiquée dans le fichier, statut actif dans la base et aucune différence constatée : contenu à conserver (contenu identique)
			else
			{
				$lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_eleves_base['num_sconet'][$id].' / '.$tab_eleves_base['reference'][$id].' || '.$tab_eleves_base['nom'][$id].' '.$tab_eleves_base['prenom'][$id].' ('.$tab_eleves_base['classe'][$id].')').'</td></tr>';
			}
		}
		// Supprimer l'entrée du fichier et celle de la base éventuelle
		unset( $tab_eleves_fichier['num_sconet'][$i] , $tab_eleves_fichier['reference'][$i] , $tab_eleves_fichier['nom'][$i] , $tab_eleves_fichier['prenom'][$i] , $tab_eleves_fichier['classe'][$i] );
		if($id)
		{
			unset( $tab_eleves_base['num_sconet'][$id] , $tab_eleves_base['reference'][$id] , $tab_eleves_base['nom'][$id] , $tab_eleves_base['prenom'][$id] , $tab_eleves_base['classe'][$id] , $tab_eleves_base['statut'][$id] );
		}
	}
	// Parcourir chaque entrée de la base
	if(count($tab_eleves_base['num_sconet']))
	{
		$tab_indices_base = array_keys($tab_eleves_base['num_sconet']);
		// Parcourir chaque entrée du fichier
		foreach($tab_indices_base as $id)
		{
			// Cas [7] : absent dans le fichier, présent dans la base, statut actif : contenu à retirer (probablement un élève nouvellement sortant)
			if($tab_eleves_base['statut'][$id])
			{
				$lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id.'" name="del_'.$id.'" type="checkbox" value="1" checked="checked" /></th><td>'.html($tab_eleves_base['num_sconet'][$id].' / '.$tab_eleves_base['reference'][$id].' || '.$tab_eleves_base['nom'][$id].' '.$tab_eleves_base['prenom'][$id].' ('.$tab_eleves_base['classe'][$id].')').' || <b>Statut : actif => inactif</b></td></tr>';
			}
			// Cas [8] : absent dans le fichier, présent dans la base, statut inactif : contenu inchangé (contenu restant inactif)
			else
			{
				$lignes_inchanger .= '<tr><th>Conserver</th><td>'.html($tab_eleves_base['num_sconet'][$id].' / '.$tab_eleves_base['reference'][$id].' || '.$tab_eleves_base['nom'][$id].' '.$tab_eleves_base['prenom'][$id].' ('.$tab_eleves_base['classe'][$id].')').'</td></tr>';
			}
			unset( $tab_eleves_base['num_sconet'][$id] , $tab_eleves_base['reference'][$id] , $tab_eleves_base['nom'][$id] , $tab_eleves_base['prenom'][$id] , $tab_eleves_base['classe'][$id] , $tab_eleves_base['statut'][$id] );
		}
	}
	// On enregistre
	$tab_traitement = array('modif'=>$tab_eleves_modif,'ajout'=>$tab_eleves_ajout);
	file_put_contents($dossier_import.'import_'.$_SESSION['BASE'].'_traitement_eleve.txt',serialize($tab_traitement));
	// On affiche
	echo'<div id="ok">';
	echo	'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des élèves puis valider.</label></p>';
	echo	'<table>';
	// Cas [2]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Élèves à ajouter (absents de la base, nouveaux dans le fichier).</th></tr>';
	echo($lignes_ajouter) ? $lignes_ajouter : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [3] et [7]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Élèves à retirer (absents du fichier ou sans classe affectée).</th></tr>';
	echo($lignes_retirer) ? $lignes_retirer : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [5]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Élèves à modifier (ou à réintégrer)</th></tr>';
	echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [6]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Élèves à conserver (statut actif)</th></tr>';
	echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [4] et [8]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Élèves inchangés (statut inactif)</th></tr>';
	echo($lignes_inchanger) ? $lignes_inchanger : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [1]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Élèves ignorés (sans classe affectée).</th></tr>';
	echo($lignes_ignorer) ? $lignes_ignorer : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	echo	'</table>';
	echo	'<p><span class="tab"><a href="#" class="step6">Valider et passer à l\'étape 6.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 6 - importation des élèves : résultat
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==6 )
{
	$fnom = 'import_'.$_SESSION['BASE'].'_traitement_eleve.txt';
	if(!file_exists($dossier_import.$fnom))
	{
		exit('Erreur : le fichier contenant les données à traiter est introuvable !');
	}
	$contenu = file_get_contents($dossier_import.$fnom);
	$tab_traitement = @unserialize($contenu);	// $tab_traitement['modif'] : id -> array ; $tab_traitement['ajout'] : i -> array
	if($tab_traitement===FALSE)
	{
		exit('Erreur : le fichier contenant les données à traiter est syntaxiquement incorrect !');
	}
	// Récupérer les éléments postés
	$tab_mod = array();	// id à modifier
	$tab_add = array();	// i à ajouter
	$tab_del = array();	// id à supprimer
	foreach($_POST as $key => $val)
	{
		if(substr($key,0,4)=='mod_')
		{
			$tab_mod[] = clean_entier( substr($key,4) );
		}
		elseif(substr($key,0,4)=='add_')
		{
			$tab_add[] = clean_entier( substr($key,4) );
		}
		elseif(substr($key,0,4)=='del_')
		{
			$tab_del[] = clean_entier( substr($key,4) );
		}
	}
	// Dénombrer combien d'actifs et d'inactifs au départ
	list($nb_debut_actif,$nb_debut_inactif) = DB_STRUCTURE_compter_eleves_suivant_statut();
	// Retirer des élèves éventuels
	$nb_del = 0;
	if(count($tab_del))
	{
		foreach($tab_del as $user_id)
		{
			if( $user_id )
			{
				// Mettre à jour l'enregistrement
				DB_STRUCTURE_modifier_utilisateur( $user_id , array(':statut'=>0) );
				$nb_del++;
			}
		}
	}
	// Ajouter des élèves éventuels
	$nb_add = 0;
	$tab_password = array();
	$fcontenu_csv = 'CLASSE'."\t".'N°SCONET'."\t".'REFERENCE'."\t".'NOM'."\t".'PRENOM'."\t".'LOGIN'."\t".'MOT DE PASSE'."\r\n\r\n";
	$fcontenu_pdf_tab = array();
	if(count($tab_add))
	{
		// Récupérer les noms de classes pour le fichier avec les logins/mdp
		$tab_nom_classe = array();
		$DB_TAB = DB_STRUCTURE_lister_classes();
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_nom_classe[$DB_ROW['groupe_id']] = $DB_ROW['groupe_nom'];
		}
		foreach($tab_add as $i)
		{
			if( isset($tab_traitement['ajout'][$i]) )
			{
				// Il peut théoriquement subsister un conflit de num_sconet pour des élèves ayant même reference, et réciproquement...
				// Construire le login
				$login = fabriquer_login($tab_traitement['ajout'][$i]['prenom'] , $tab_traitement['ajout'][$i]['nom'] , 'eleve');
				// Puis tester le login (parmi tout le personnel de l'établissement)
				if( DB_STRUCTURE_tester_login($login) )
				{
					// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
					$login = DB_STRUCTURE_rechercher_login_disponible($login);
				}
				// Construire le password
				$password = fabriquer_mdp();
				// Ajouter l'utilisateur
				$user_id = DB_STRUCTURE_ajouter_utilisateur($tab_traitement['ajout'][$i]['num_sconet'],$tab_traitement['ajout'][$i]['reference'],'eleve',$tab_traitement['ajout'][$i]['nom'],$tab_traitement['ajout'][$i]['prenom'],$login,$password,$tab_traitement['ajout'][$i]['classe']);
				$nb_add++;
				$tab_password[$user_id] = $password;
				$fcontenu_csv .= $tab_nom_classe[$tab_traitement['ajout'][$i]['classe']]."\t".$tab_traitement['ajout'][$i]['num_sconet']."\t".$tab_traitement['ajout'][$i]['reference']."\t".$tab_traitement['ajout'][$i]['nom']."\t".$tab_traitement['ajout'][$i]['prenom']."\t".$login."\t".$password."\r\n";
				$fcontenu_pdf_tab[] = $tab_nom_classe[$tab_traitement['ajout'][$i]['classe']]."\r\n".$tab_traitement['ajout'][$i]['nom'].' '.$tab_traitement['ajout'][$i]['prenom']."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : '.$password;
			}
		}
	}
	// Modifier des élèves éventuels
	$nb_mod = 0;
	if(count($tab_mod))
	{
		foreach($tab_mod as $id)
		{
			// Il peut théoriquement subsister un conflit de num_sconet pour des élèves ayant même reference, et réciproquement...
			$tab_champs = array( 'num_sconet' , 'reference' , 'nom' , 'prenom' , 'classe' , 'statut' );
			$DB_VAR  = array();
			foreach($tab_champs as $champ_ref)
			{
				if($tab_traitement['modif'][$id][$champ_ref] !== false)
				{
					$DB_VAR[':'.$champ_ref] = $tab_traitement['modif'][$id][$champ_ref];
				}
			}
			// bilan
			if( count($DB_VAR) )
			{
				DB_STRUCTURE_modifier_utilisateur( $id , $DB_VAR );
			}
			$nb_mod++;
		}
	}
	// Afficher le bilan
	$tab_statut = array(0=>'inactif',1=>'actif');
	$lignes         = '';
	$nb_fin_actif   = 0;
	$nb_fin_inactif = 0;
	$DB_TAB = DB_STRUCTURE_lister_eleves_tri_statut_classe();
	foreach($DB_TAB as $DB_ROW)
	{
		$class       = (isset($tab_password[$DB_ROW['user_id']])) ? ' class="new"' : '' ;
		$td_password = (isset($tab_password[$DB_ROW['user_id']])) ? '<td class="new">'.html($tab_password[$DB_ROW['user_id']]).'</td>' : '<td class="i">champ crypté</td>' ;
		if($DB_ROW['user_statut']) {$nb_fin_actif++;} else {$nb_fin_inactif++;}
		$lignes .= '<tr'.$class.'><td>'.html($DB_ROW['user_num_sconet']).'</td><td>'.html($DB_ROW['user_reference']).'</td><td>'.html($DB_ROW['user_nom']).'</td><td>'.html($DB_ROW['user_prenom']).'</td><td>'.html($DB_ROW['groupe_ref']).'</td><td'.$class.'>'.html($DB_ROW['user_login']).'</td>'.$td_password.'<td>'.$tab_statut[$DB_ROW['user_statut']].'</td></tr>'."\r\n";
	}
	$s_debut_actif   = ($nb_debut_actif>1)   ? 's' : '';
	$s_debut_inactif = ($nb_debut_inactif>1) ? 's' : '';
	$s_fin_actif     = ($nb_fin_actif>1)     ? 's' : '';
	$s_fin_inactif   = ($nb_fin_inactif>1)   ? 's' : '';
	$s_mod = ($nb_mod>1) ? 's' : '';
	$s_add = ($nb_add>1) ? 's' : '';
	$s_del = ($nb_del>1) ? 's' : '';
	echo'<div id="ok">';
	echo' <p><label class="valide">'.$nb_debut_actif.' élève'.$s_debut_actif.' actif'.$s_debut_actif.' et '.$nb_debut_inactif.' élève'.$s_debut_inactif.' inactif'.$s_debut_inactif.' => '.$nb_mod.' élève'.$s_mod.' modifié'.$s_mod.' + '.$nb_add.' élève'.$s_add.' ajouté'.$s_add.' &minus; '.$nb_del.' élève'.$s_del.' retiré'.$s_del.' => '.$nb_fin_actif.' élève'.$s_fin_actif.' actif'.$s_fin_actif.' et '.$nb_fin_inactif.' élève'.$s_fin_inactif.' inactif'.$s_fin_inactif.'.</label></p>';
	if($nb_add)
	{
		// On archive les nouveaux identifiants dans un fichier tableur zippé (csv tabulé)
		$fnom = 'identifiants_'.$_SESSION['BASE'].'_eleves_'.time();
		$zip = new ZipArchive();
		if ($zip->open($dossier_login_mdp.$fnom.'.zip', ZIPARCHIVE::CREATE)===TRUE)
		{
			$zip->addFromString($fnom.'.csv',csv($fcontenu_csv));
			$zip->close();
		}
		// On archive les nouveaux identifiants dans un fichier pdf (classe fpdf + script étiquettes)
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
		echo' <p><label class="alerte">La page suivante vous permettra de conserver les identifiants de tout nouvel élève inscrit.</label><input id="archive" name="archive" type="hidden" value="'.html($fnom).'" /></p>';
	}
	echo' <p><span class="tab"><a href="#" class="step7">Passer à l\'étape 7.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo' <table>';
	echo'  <thead>';
	echo'   <tr><th>n° Sconet</th><th>Référence</th><th>Nom</th><th>Prénom</th><th>Groupes</th><th>Login</th><th>Mot de passe</th><th>Statut</th></tr>';
	echo'  </thead>';
	echo'  <tbody>';
	echo    $lignes;
	echo'  </tbody>';
	echo' </table>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 7 - confirmation / impression
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==7 )
{
	echo'<div id="ok">';
	echo' <p><label class="valide">Fin de l\'importation des classes et des élèves !</label></p>';
	$archive = (isset($_POST['archive'])) ? $_POST['archive'] : '';
	if($archive)
	{
		echo' <div><a class="lien_ext" href="'.$dossier_login_mdp.$archive.'.zip">Récupérez les identifiants des nouveaux inscrits dans un fichier csv tabulé pour tableur.</a></div>';
		echo' <div><a class="lien_ext" href="'.$dossier_login_mdp.$archive.'.pdf">Récupérez les identifiants des nouveaux inscrits dans un fichier pdf (étiquettes à imprimer).</a></div>';
		echo' <div><label class="alerte">Attention : les mots de passe, cryptés, ne sont plus accessibles ultérieurement !</label></div>';
	}
	echo'</div>';
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
