<?php
/**
 * @version $Id: fichier_import-professeur-directeur.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if($_SESSION['STRUCTURE_ID']==ID_DEMO) {exit('Action désactivée pour la démo...');}

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
	$fichier_dest = (in_array($extension,array('zip','xml'))) ? 'import_'.$_SESSION['STRUCTURE_ID'].'.xml' : 'import_'.$_SESSION['STRUCTURE_ID'].'.txt' ;
	$fichier_kill = (in_array($extension,array('txt','csv'))) ? 'import_'.$_SESSION['STRUCTURE_ID'].'.xml' : 'import_'.$_SESSION['STRUCTURE_ID'].'.txt' ;
	if(in_array($extension,array('xml','txt','csv')))
	{
		if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_dest))
		{
			exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
		}
	}
	else
	{
		$nom_fichier = (date('n')>7) ? date('Y') : date('Y')-1 ;
		$nom_fichier = 'sts_emp_'.$_SESSION['STRUCTURE_UAI'].'_'.$nom_fichier.'.xml';
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
		if($zip->extractTo($dossier_import,$nom_fichier)!==true)
		{
			exit('Erreur : fichier '.$nom_fichier.' non trouvé dans votre archive !');
		}
		$zip->close();
		@unlink($dossier_import.$fichier_dest);
		if(!rename($dossier_import.$nom_fichier , $dossier_import.$fichier_dest))
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
	// Pour récupérer les données des professeurs et des directeurs
	$tab_professeurs_directeurs               = array();
	$tab_professeurs_directeurs['num_sconet'] = array();
	$tab_professeurs_directeurs['reference']  = array();
	$tab_professeurs_directeurs['profil']     = array();
	$tab_professeurs_directeurs['nom']        = array();
	$tab_professeurs_directeurs['prenom']     = array();
	// On a pu réceptionner 2 fichiers suivant la procédure choisie
	$fichier_src_sconet  = 'import_'.$_SESSION['STRUCTURE_ID'].'.xml';
	$fichier_src_tableur = 'import_'.$_SESSION['STRUCTURE_ID'].'.txt' ;
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
		foreach ($xml->DONNEES->INDIVIDUS->INDIVIDU as $individu)
		{
			$fonction = $individu->FONCTION ;
			// Prendre les professeurs, les CPE, le personnel de direction (je ne sais pas s'il y a d'autres cas)
			if(in_array( $fonction , array('ENS','EDU','DIR') ))
			{
				$tab_professeurs_directeurs['num_sconet'][] = clean_entier($individu->attributes()->ID);
				$tab_professeurs_directeurs['reference'][]  = '';
				$tab_professeurs_directeurs['profil'][]     = ( ($fonction=='ENS') || ($fonction=='EDU') ) ? 'professeur' : 'directeur' ;
				$tab_professeurs_directeurs['nom'][]        = clean_nom($individu->NOM_USAGE);
				$tab_professeurs_directeurs['prenom'][]     = clean_prenom($individu->PRENOM);
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
				list($reference,$nom,$prenom,$fonction) = $tab_elements;
				$fonction = perso_strtolower($fonction);
				if( ($nom!='') && ($prenom!='') && ( ($fonction=='professeur') || ($fonction=='directeur') ) )
				{
					$tab_professeurs_directeurs['num_sconet'][] = 0;
					$tab_professeurs_directeurs['reference'][]  = clean_ref($reference);
					$tab_professeurs_directeurs['profil'][]     = $fonction;
					$tab_professeurs_directeurs['nom'][]        = clean_nom($nom);
					$tab_professeurs_directeurs['prenom'][]     = clean_prenom($prenom);
				}
			}
		}
	}
	else
	{
		exit('Erreur : le fichier n\'a pas été retrouvé !');
	}
	// On trie
	array_multisort($tab_professeurs_directeurs['nom'],SORT_ASC,SORT_STRING,$tab_professeurs_directeurs['prenom'],SORT_ASC,SORT_STRING,$tab_professeurs_directeurs['num_sconet'],$tab_professeurs_directeurs['reference'],$tab_professeurs_directeurs['profil']);
	// On enregistre
	$tab_professeurs_directeurs_fichier = array('num_sconet'=>$tab_professeurs_directeurs['num_sconet'],'reference'=>$tab_professeurs_directeurs['reference'],'nom'=>$tab_professeurs_directeurs['nom'],'prenom'=>$tab_professeurs_directeurs['prenom'],'profil'=>$tab_professeurs_directeurs['profil']);
	file_put_contents($dossier_import.'import_'.$_SESSION['STRUCTURE_ID'].'_professeurs_directeurs.txt',serialize($tab_professeurs_directeurs_fichier));
	echo'<div id="ok">';
	echo' <p><label class="valide">Les données ont été correctement extraites.</label></p>';
	echo' <p><span class="tab"><a href="#" class="step3">Passer à l\'étape 3.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 3 - importation des professeurs et directeurs : paramétrage
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==3 )
{
	$fnom = 'import_'.$_SESSION['STRUCTURE_ID'].'_professeurs_directeurs.txt';
	if(!file_exists($dossier_import.$fnom))
	{
		exit('Erreur : le fichier contenant les professeurs et directeurs est introuvable !');
	}
	$contenu = file_get_contents($dossier_import.$fnom);
	$tab_professeurs_directeurs_fichier = @unserialize($contenu);	// $tab_professeurs_directeurs_fichier['champ'] : i -> valeur du champ
	if($tab_professeurs_directeurs_fichier===FALSE)
	{
		exit('Erreur : le fichier contenant les professeurs et directeurs est syntaxiquement incorrect !');
	}
	// $tab_professeurs_directeurs_base['champ'] : id -> valeur du champ
	$tab_professeurs_directeurs_base               = array();
	$tab_professeurs_directeurs_base['num_sconet'] = array();
	$tab_professeurs_directeurs_base['reference']  = array();
	$tab_professeurs_directeurs_base['profil']     = array();
	$tab_professeurs_directeurs_base['nom']        = array();
	$tab_professeurs_directeurs_base['prenom']     = array();
	$tab_professeurs_directeurs_base['statut']     = array();
	$DB_TAB = DB_lister_professeurs_directeurs($_SESSION['STRUCTURE_ID']);
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$tab_professeurs_directeurs_base['num_sconet'][$DB_ROW['livret_user_id']] = $DB_ROW['livret_user_num_sconet'];
		$tab_professeurs_directeurs_base['reference'][$DB_ROW['livret_user_id']]  = $DB_ROW['livret_user_reference'];
		$tab_professeurs_directeurs_base['profil'][$DB_ROW['livret_user_id']]     = $DB_ROW['livret_user_profil'];
		$tab_professeurs_directeurs_base['nom'][$DB_ROW['livret_user_id']]        = $DB_ROW['livret_user_nom'];
		$tab_professeurs_directeurs_base['prenom'][$DB_ROW['livret_user_id']]     = $DB_ROW['livret_user_prenom'];
		$tab_professeurs_directeurs_base['statut'][$DB_ROW['livret_user_id']]     = $DB_ROW['livret_user_statut'];
	}
	// Pour préparer l'affichage
	$lignes_ajouter   = '';
	$lignes_retirer   = '';
	$lignes_modifier  = '';
	$lignes_conserver = '';
	$lignes_inchanger = '';
	// Pour préparer l'enregistrement des données
	$tab_professeurs_directeurs_ajout = array();
	$tab_professeurs_directeurs_modif = array();
	// Comparer fichier et base : c'est parti !
	$tab_indices_fichier = array_keys($tab_professeurs_directeurs_fichier['num_sconet']);
	// Parcourir chaque entrée du fichier
	foreach($tab_indices_fichier as $i)
	{
		$id = false;
		// Recherche sur num_sconet
		if( (!$id) && ($tab_professeurs_directeurs_fichier['num_sconet'][$i]) )
		{
			$id = array_search($tab_professeurs_directeurs_fichier['num_sconet'][$i],$tab_professeurs_directeurs_base['num_sconet']);
		}
		// Si pas trouvé, recherche sur reference
		if( (!$id) && ($tab_professeurs_directeurs_fichier['reference'][$i]) )
		{
			$id = array_search($tab_professeurs_directeurs_fichier['reference'][$i],$tab_professeurs_directeurs_base['reference']);
		}
		// Si pas trouvé, recherche sur nom prénom
		if(!$id)
		{
			$tab_id_nom    = array_keys($tab_professeurs_directeurs_base['nom'],$tab_professeurs_directeurs_fichier['nom'][$i]);
			$tab_id_prenom = array_keys($tab_professeurs_directeurs_base['prenom'],$tab_professeurs_directeurs_fichier['prenom'][$i]);
			$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
			if(count($tab_id_commun))
			{
				list($inutile,$id) = each($tab_id_commun);
			}
		}
		// Cas [2] : présent dans le fichier, absent de la base : contenu à ajouter (nouveau professeur / directeur)
		if(!$id)
		{
			$lignes_ajouter .= '<tr><th>Ajouter <input id="add_'.$i.'" name="add_'.$i.'" type="checkbox" value="1" checked="checked" /></th><td>'.html($tab_professeurs_directeurs_fichier['num_sconet'][$i].' / '.$tab_professeurs_directeurs_fichier['reference'][$i].' || '.$tab_professeurs_directeurs_fichier['profil'][$i].' || '.$tab_professeurs_directeurs_fichier['nom'][$i].' '.$tab_professeurs_directeurs_fichier['prenom'][$i]).'</td></tr>';
			$tab_professeurs_directeurs_ajout[$i] = array( 'num_sconet'=>$tab_professeurs_directeurs_fichier['num_sconet'][$i] , 'reference'=>$tab_professeurs_directeurs_fichier['reference'][$i] , 'nom'=>$tab_professeurs_directeurs_fichier['nom'][$i] , 'prenom'=>$tab_professeurs_directeurs_fichier['prenom'][$i] , 'profil'=>$tab_professeurs_directeurs_fichier['profil'][$i] );
		}
		else
		{
			// On compare les données de 2 enregistrements pour voir si des choses ont été modifiées
			$td_modif = '';
			$nb_modif = 0;
			$tab_champs = array( 'num_sconet'=>'n° Sconet' , 'reference'=>'Référence' , 'profil'=>'Profil' , 'nom'=>'Nom' , 'prenom'=>'Prénom' );
			foreach($tab_champs as $champ_ref => $champ_aff)
			{
				if($tab_professeurs_directeurs_base[$champ_ref][$id]!=$tab_professeurs_directeurs_fichier[$champ_ref][$i])
				{
					$td_modif .= ' || <b>'.$champ_aff.' : '.html($tab_professeurs_directeurs_base[$champ_ref][$id]).' => '.html($tab_professeurs_directeurs_fichier[$champ_ref][$i]).'</b>';
					$tab_professeurs_directeurs_modif[$id][$champ_ref] = ($champ_ref!='classe') ? $tab_professeurs_directeurs_fichier[$champ_ref][$i] : $tab_classe_ref[$tab_professeurs_directeurs_fichier[$champ_ref][$i]] ;
					$nb_modif++;
				}
				else
				{
					$td_modif .= ' || '.$champ_aff.' : '.html($tab_professeurs_directeurs_base[$champ_ref][$id]);
					$tab_professeurs_directeurs_modif[$id][$champ_ref] = false;
				}
			}
			if(!$tab_professeurs_directeurs_base['statut'][$id])
			{
				$td_modif .= ' || <b>Statut : inactif => actif</b>';
				$tab_professeurs_directeurs_modif[$id]['statut'] = 1 ;
				$nb_modif++;
			}
			else
			{
				$tab_professeurs_directeurs_modif[$id]['statut'] = false ;
			}
			// Cas [5] : présent dans le fichier, présent dans la base, statut inactif dans la base et/ou différence constatée : contenu à modifier (professeur revenant ou mise à jour)
			if($nb_modif)
			{
				$lignes_modifier .= '<tr><th>Modifier <input id="mod_'.$id.'" name="mod_'.$id.'" type="checkbox" value="1" checked="checked" /></th><td>'.mb_substr($td_modif,4).'</td></tr>';
			}
			// Cas [6] : présent dans le fichier, présent dans la base, statut actif dans la base et aucune différence constatée : contenu à conserver (contenu identique)
			else
			{
				$lignes_conserver .= '<tr><th>Conserver</th><td>'.html($tab_professeurs_directeurs_base['num_sconet'][$id].' / '.$tab_professeurs_directeurs_base['reference'][$id].' || '.$tab_professeurs_directeurs_base['profil'][$id].' || '.$tab_professeurs_directeurs_base['nom'][$id].' '.$tab_professeurs_directeurs_base['prenom'][$id]).'</td></tr>';
			}
		}
		// Supprimer l'entrée du fichier et celle de la base éventuelle
		unset( $tab_professeurs_directeurs_fichier['num_sconet'][$i] , $tab_professeurs_directeurs_fichier['reference'][$i] , $tab_professeurs_directeurs_fichier['profil'][$i] , $tab_professeurs_directeurs_fichier['nom'][$i] , $tab_professeurs_directeurs_fichier['prenom'][$i] );
		if($id)
		{
			unset( $tab_professeurs_directeurs_base['num_sconet'][$id] , $tab_professeurs_directeurs_base['reference'][$id] , $tab_professeurs_directeurs_base['profil'][$id] , $tab_professeurs_directeurs_base['nom'][$id] , $tab_professeurs_directeurs_base['prenom'][$id] , $tab_professeurs_directeurs_base['statut'][$id] );
		}
	}
	// Parcourir chaque entrée de la base
	if(count($tab_professeurs_directeurs_base['num_sconet']))
	{
		$tab_indices_base = array_keys($tab_professeurs_directeurs_base['num_sconet']);
		// Parcourir chaque entrée du fichier
		foreach($tab_indices_base as $id)
		{
			// Cas [7] : absent dans le fichier, présent dans la base, statut actif : contenu à retirer (probablement un professeur / directeur nouvellement sortant)
			if($tab_professeurs_directeurs_base['statut'][$id])
			{
				$lignes_retirer .= '<tr><th>Retirer <input id="del_'.$id.'" name="del_'.$id.'" type="checkbox" value="1" checked="checked" /></th><td>'.html($tab_professeurs_directeurs_base['num_sconet'][$id].' / '.$tab_professeurs_directeurs_base['reference'][$id].' || '.$tab_professeurs_directeurs_base['profil'][$id].' || '.$tab_professeurs_directeurs_base['nom'][$id].' '.$tab_professeurs_directeurs_base['prenom'][$id]).' || <b>Statut : actif => inactif</b></td></tr>';
			}
			// Cas [8] : absent dans le fichier, présent dans la base, statut inactif : contenu inchangé (contenu restant inactif)
			else
			{
				$lignes_inchanger .= '<tr><th>Conserver</th><td>'.html($tab_professeurs_directeurs_base['num_sconet'][$id].' / '.$tab_professeurs_directeurs_base['reference'][$id].' || '.$tab_professeurs_directeurs_base['profil'][$id].' || '.$tab_professeurs_directeurs_base['nom'][$id].' '.$tab_professeurs_directeurs_base['prenom'][$id]).'</td></tr>';
			}
			unset( $tab_professeurs_directeurs_base['num_sconet'][$id] , $tab_professeurs_directeurs_base['reference'][$id] , $tab_professeurs_directeurs_base['profil'][$id] , $tab_professeurs_directeurs_base['nom'][$id] , $tab_professeurs_directeurs_base['prenom'][$id] , $tab_professeurs_directeurs_base['statut'][$id] );
		}
	}
	// On enregistre
	$tab_traitement = array('modif'=>$tab_professeurs_directeurs_modif,'ajout'=>$tab_professeurs_directeurs_ajout);
	file_put_contents($dossier_import.'import_'.$_SESSION['STRUCTURE_ID'].'_traitement_professeur_directeur.txt',serialize($tab_traitement));
	// On affiche
	echo'<div id="ok">';
	echo	'<p><label class="valide">Veuillez vérifier le résultat de l\'analyse des professeurs et des directeurs puis valider.</label></p>';
	echo	'<table>';
	// Cas [2]
	echo		'<tbody>';
	echo'   <tr><th colspan="2">Professeurs / directeurs à ajouter (absents de la base, nouveaux dans le fichier).</th></tr>';
	echo($lignes_ajouter) ? $lignes_ajouter : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [7]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Professeurs / directeurs à retirer (absents du fichier).</th></tr>';
	echo($lignes_retirer) ? $lignes_retirer : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [5]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Professeurs / directeurs à modifier (ou à réintégrer)</th></tr>';
	echo($lignes_modifier) ? $lignes_modifier : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [6]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Professeurs / directeurs à conserver (statut actif)</th></tr>';
	echo($lignes_conserver) ? $lignes_conserver : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	// Cas [8]
	echo		'<tbody>';
	echo			'<tr><th colspan="2">Professeurs / directeurs inchangés (statut inactif)</th></tr>';
	echo($lignes_inchanger) ? $lignes_inchanger : '<tr><td colspan="2">Aucun</td></tr>';
	echo		'</tbody>';
	echo	'</table>';
	echo	'<p><span class="tab"><a href="#" class="step4">Valider et passer à l\'étape 4.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 4 - importation des professeurs et directeurs : résultat
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==4 )
{
	$fnom = 'import_'.$_SESSION['STRUCTURE_ID'].'_traitement_professeur_directeur.txt';
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
	list($nb_debut_actif,$nb_debut_inactif) = DB_compter_professeurs_directeurs_suivant_statut($structure_id);
	// Retirer des professeurs / directeurs éventuels
	$nb_del = 0;
	if(count($tab_del))
	{
		foreach($tab_del as $user_id)
		{
			if( $user_id )
			{
				// Mettre à jour l'enregistrement
				DB_modifier_statut_utilisateur($_SESSION['STRUCTURE_ID'],$user_id,0)
				$nb_del++;
			}
		}
	}
	// Ajouter des professeurs / directeurs éventuels
	$nb_add = 0;
	$tab_password = array();
	$fcontenu_csv = 'N°SCONET'."\t".'REFERENCE'."\t".'PROFIL'."\t".'NOM'."\t".'PRENOM'."\t".'LOGIN'."\t".'MOT DE PASSE'."\r\n\r\n";
	$fcontenu_pdf_tab = array();
	if(count($tab_add))
	{
		foreach($tab_add as $i)
		{
			if( isset($tab_traitement['ajout'][$i]) )
			{
				// Construire le login
				$login = fabriquer_login($tab_traitement['ajout'][$i]['prenom'] , $tab_traitement['ajout'][$i]['nom'] , 'professeur');
				// Puis tester le login (parmi tout le personnel de l'établissement)
				if( DB_tester_login($_SESSION['STRUCTURE_ID'],$login) )
				{
					// Login pris : en chercher un autre en remplaçant la fin par des chiffres si besoin
					$login = DB_rechercher_login_disponible($_SESSION['STRUCTURE_ID'],$login);
				}
				// Construire le password
				$password = fabriquer_mdp();
				// Ajouter l'utilisateur
				$user_id = DB_ajouter_utilisateur($_SESSION['STRUCTURE_ID'],$tab_traitement['ajout'][$i]['num_sconet'],$tab_traitement['ajout'][$i]['reference'],$tab_traitement['ajout'][$i]['profil'],$tab_traitement['ajout'][$i]['nom'],$tab_traitement['ajout'][$i]['prenom'],$login,$password,0);
				$nb_add++;
				$tab_password[$user_id] = $password;
				$fcontenu_csv .= $tab_traitement['ajout'][$i]['num_sconet']."\t".$tab_traitement['ajout'][$i]['reference']."\t".$tab_traitement['ajout'][$i]['profil']."\t".$tab_traitement['ajout'][$i]['nom']."\t".$tab_traitement['ajout'][$i]['prenom']."\t".$login."\t".$password."\r\n";
				$fcontenu_pdf_tab[] = mb_strtoupper($tab_traitement['ajout'][$i]['profil'])."\r\n".$tab_traitement['ajout'][$i]['nom'].' '.$tab_traitement['ajout'][$i]['prenom']."\r\n".'Utilisateur : '.$login."\r\n".'Mot de passe : '.$password;
			}
		}
	}
	// Modifier des professeurs / directeurs éventuels
	$nb_mod = 0;
	if(count($tab_mod))
	{
		foreach($tab_mod as $id)
		{
			// Il peut théoriquement subsister un conflit de num_sconet pour des professeurs / directeurs ayant même reference, et réciproquement...
			$tab_champs = array( 'num_sconet' , 'reference' , 'profil' , 'nom' , 'prenom' , 'statut' );
			$tab_set = array();
			$DB_VAR  = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':id'=>$id);
			foreach($tab_champs as $champ_ref)
			{
				if($tab_traitement['modif'][$id][$champ_ref] !== false)
				{
					$tab_set[] = 'livret_user_'.$champ_ref.'=:'.$champ_ref;
					$DB_VAR[':'.$champ_ref] = $tab_traitement['modif'][$id][$champ_ref];
				}
			}
			// bilan
			if( count($tab_set) )
			{
				$DB_SQL = 'UPDATE livret_user ';
				$DB_SQL.= 'SET '.implode(',',$tab_set).' ';
				$DB_SQL.= 'WHERE livret_structure_id=:structure_id AND livret_user_id=:id ';
				$DB_SQL.= 'LIMIT 1';
				DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			}
			$nb_mod++;
		}
	}
	// Afficher le bilan
	$tab_statut = array(0=>'inactif',1=>'actif');
	$lignes         = '';
	$nb_fin_actif   = 0;
	$nb_fin_inactif = 0;
	$DB_TAB = DB_lister_professeurs_directeurs_tri_statut($_SESSION['STRUCTURE_ID'])
	foreach($DB_TAB as $key => $DB_ROW)
	{
		$class       = (isset($tab_password[$DB_ROW['livret_user_id']])) ? ' class="new"' : '' ;
		$td_password = (isset($tab_password[$DB_ROW['livret_user_id']])) ? '<td class="new">'.html($tab_password[$DB_ROW['livret_user_id']]).'</td>' : '<td class="i">champ crypté</td>' ;
		if($DB_ROW['livret_user_statut']) {$nb_fin_actif++;} else {$nb_fin_inactif++;}
		$lignes .= '<tr'.$class.'><td>'.html($DB_ROW['livret_user_num_sconet']).'</td><td>'.html($DB_ROW['livret_user_reference']).'</td><td>'.html($DB_ROW['livret_user_profil']).'</td><td>'.html($DB_ROW['livret_user_nom']).'</td><td>'.html($DB_ROW['livret_user_prenom']).'</td><td'.$class.'>'.html($DB_ROW['livret_user_login']).'</td>'.$td_password.'<td>'.$tab_statut[$DB_ROW['livret_user_statut']].'</td></tr>'."\r\n";
	}
	$s_debut_actif   = ($nb_debut_actif>1)   ? 's' : '';
	$s_debut_inactif = ($nb_debut_inactif>1) ? 's' : '';
	$s_fin_actif     = ($nb_fin_actif>1)     ? 's' : '';
	$s_fin_inactif   = ($nb_fin_inactif>1)   ? 's' : '';
	$s_mod = ($nb_mod>1) ? 's' : '';
	$s_add = ($nb_add>1) ? 's' : '';
	$s_del = ($nb_del>1) ? 's' : '';
	echo'<div id="ok">';
	echo' <p><label class="valide">'.$nb_debut_actif.' personnel'.$s_debut_actif.' actif'.$s_debut_actif.' et '.$nb_debut_inactif.' personnel'.$s_debut_inactif.' inactif'.$s_debut_inactif.' => '.$nb_mod.' personnel'.$s_mod.' modifié'.$s_mod.' + '.$nb_add.' personnel'.$s_add.' ajouté'.$s_add.' &minus; '.$nb_del.' personnel'.$s_del.' retiré'.$s_del.' => '.$nb_fin_actif.' personnel'.$s_fin_actif.' actif'.$s_fin_actif.' et '.$nb_fin_inactif.' personnel'.$s_fin_inactif.' inactif'.$s_fin_inactif.'.</label></p>';
	if($nb_add)
	{
		// On archive les nouveaux identifiants dans un fichier tableur zippé (csv tabulé)
		$fnom = 'identifiants_'.$_SESSION['STRUCTURE_ID'].'_profs_'.time();
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
		echo' <p><label class="alerte">La page suivante vous permettra de conserver les identifiants de tout nouveau professeur inscrit.</label><input id="archive" name="archive" type="hidden" value="'.html($fnom).'" /></p>';
	}
	echo' <p><span class="tab"><a href="#" class="step5">Passer à l\'étape 5.</a><label id="ajax_msg">&nbsp;</label></span></p>';
	echo' <table>';
	echo'  <thead>';
	echo'   <tr><th>n° Sconet</th><th>Référence</th><th>Profil</th><th>Nom</th><th>Prénom</th><th>Login</th><th>Mot de passe</th><th>Statut</th></tr>';
	echo'  </thead>';
	echo'  <tbody>';
	echo    $lignes;
	echo'  </tbody>';
	echo' </table>';
	echo'</div>';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Étape 5 - confirmation / impression
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

elseif( $step==5 )
{
	echo'<div id="ok">';
	echo' <p><label class="valide">Fin de l\'importation des professeurs et des directeurs !</label></p>';
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
