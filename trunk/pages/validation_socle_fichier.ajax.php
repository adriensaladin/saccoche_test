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

$action = (isset($_POST['f_action'])) ? clean_texte($_POST['f_action']) : '';
$tab_select_eleves = (isset($_POST['select_eleves'])) ? array_map('clean_entier',explode(',',$_POST['select_eleves'])) : array() ;
$tab_select_eleves = array_filter($tab_select_eleves,'positif');
$nb = count($tab_select_eleves);

$top_depart = microtime(TRUE);

$dossier_export = './__tmp/export/';
$dossier_import = './__tmp/import/';

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Exporter un fichier de validations
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if( in_array( $action , array('export_lpc','export_sacoche') ) && $nb )
{
	$tab_validations  = array(); // [i] => [user_id][palier_id][pilier_id][entree_id] => [date][etat] Retenir les validations ; item à 0 si validation d'un palier.
	$listing_eleve_id = implode(',',$tab_select_eleves);
	$only_positives   = ($action=='export_lpc') ? TRUE : FALSE ;
	// Validations des items
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_items($listing_eleve_id,$only_positives);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_validations[$DB_ROW['user_id']][$DB_ROW['palier_id']][$DB_ROW['pilier_id']][$DB_ROW['entree_id']] = array('date'=>$DB_ROW['validation_entree_date'],'etat'=>$DB_ROW['validation_entree_etat'],'info'=>$DB_ROW['validation_entree_info']) ;
	}
	// Validations des compétences
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_competences($listing_eleve_id,$only_positives);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_validations[$DB_ROW['user_id']][$DB_ROW['palier_id']][$DB_ROW['pilier_id']][0] = array('date'=>$DB_ROW['validation_pilier_date'],'etat'=>$DB_ROW['validation_pilier_etat'],'info'=>$DB_ROW['validation_pilier_info']) ;
	}
	// Validations trouvées ?
	if(!count($tab_validations))
	{
		$positive = $only_positives ? 'positive ' : '' ;
		exit('Erreur : aucune validation '.$positive.'d\'élève trouvée !');
	}
	// Données élèves
	$tab_eleves     = array(); // [user_id] => array(nom,prenom,sconet_id) Ordonné par classe et alphabet.
	$only_sconet_id = ($action=='export_lpc') ? TRUE : FALSE ;
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_eleves_cibles_actifs_avec_sconet_id($listing_eleve_id,$only_sconet_id);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleves[$DB_ROW['user_id']] = array('nom'=>$DB_ROW['user_nom'],'prenom'=>$DB_ROW['user_prenom'],'sconet_id'=>$DB_ROW['user_sconet_id']);
	}
	// Elèves trouvés ?
	if(!count($DB_TAB))
	{
		$identifiant = $only_sconet_id ? 'n\'ont pas d\'identifiant Sconet ou ' : '' ;
		exit('Erreur : les élèves trouvés '.$identifiant.'sont inactifs !');
	}
	// Fabrication du XML
	$nb_eleves  = 0;
	$nb_piliers = 0;
	$nb_items   = 0;
	if($action=='export_lpc')
	{
		$xml = '<?xml version="1.0" encoding="ISO-8859-15"?>'."\r\n";
		$xml.= '<lpc xmlns="urn:ac-grenoble.fr:lpc:import:v1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ac-grenoble.fr:lpc:import:v1.0 import-lpc.xsd">'."\r\n";
		$xml.= '	<entete>'."\r\n";
		$xml.= '		<editeur>SESAMATH</editeur>'."\r\n";
		$xml.= '		<application>SACOCHE</application>'."\r\n";
		$xml.= '		<etablissement>'.html($_SESSION['UAI']).'</etablissement>'."\r\n";
		$xml.= '	</entete>'."\r\n";
		$xml.= '	<donnees>'."\r\n";
	}
	else
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		$xml.= '<sacoche>'."\r\n";
		$xml.= '	<donnees>'."\r\n";
	}
	foreach($tab_eleves as $user_id => $tab_user)
	{
		if(isset($tab_validations[$user_id]))
		{
			$nb_eleves++;
			$xml.= '		<eleve id="'.$tab_user['sconet_id'].'" nom="'.html($tab_user['nom']).'" prenom="'.html($tab_user['prenom']).'">'."\r\n";
			foreach($tab_validations[$user_id] as $palier_id => $tab_pilier)
			{
				$xml.= '			<palier id="'.$palier_id.'">'."\r\n";
				foreach($tab_pilier as $pilier_id => $tab_item)
				{
					$xml.= '				<competence id="'.$pilier_id.'">'."\r\n";
					if(isset($tab_item[0]))
					{
						// Validation de la compétence
						$nb_piliers++;
						$xml.= '					<validation>'."\r\n";
						$xml.= '						<date>'.$tab_item[0]['date'].'</date>'."\r\n";
						if(!$only_positives)
						{
							$xml.= '						<etat>'.$tab_item[0]['etat'].'</etat>'."\r\n";
							$xml.= '						<info>'.html($tab_item[0]['info']).'</info>'."\r\n";
						}
						$xml.= '					</validation>'."\r\n";
						unset($tab_item[0]);
					}
					if(count($tab_item))
					{
						// Validation d'items de la compétence
						foreach($tab_item as $item_id => $tab_item_infos)
						{
							$nb_items++;
							$xml.= '					<item id="'.$item_id.'">'."\r\n";
							$xml.= '						<renseignement>'."\r\n";
							$xml.= '							<date>'.$tab_item_infos['date'].'</date>'."\r\n";
							if(!$only_positives)
							{
								$xml.= '							<etat>'.$tab_item_infos['etat'].'</etat>'."\r\n";
								$xml.= '							<info>'.html($tab_item_infos['info']).'</info>'."\r\n";
							}
							$xml.= '						</renseignement>'."\r\n";
							$xml.= '					</item>'."\r\n";
						}
					}
					$xml.= '				</competence>'."\r\n";
				}
				$xml.= '			</palier>'."\r\n";
			}
			$xml.= '		</eleve>'."\r\n";
		}
	}
	$fichier_extension = ($action=='export_lpc') ? 'xml' : 'zip' ;
	if($action=='export_lpc')
	{
		$xml.= '	</donnees>'."\r\n";
		$xml.= '</lpc>'."\r\n";
		// Pour LPC, ajouter la signature via un appel au serveur sécurisé
		$xml = utf8_decode($xml);
		$xml = signer_exportLPC($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$xml); // fonction sur le modèle de envoyer_arborescence_XML()
		if(substr($xml,0,5)!='<?xml')
		{
			exit(html($xml));
		}
		$fichier_nom = str_replace('export_','import-',$action).'-'.time().'_'.$_SESSION['BASE'].'_'.mt_rand().'.'.$fichier_extension; // LPC recommande le modèle "import-lpc-{timestamp}.xml"
		Ecrire_Fichier( $dossier_export.$fichier_nom , $xml );
	}
	else
	{
		$xml.= '	</donnees>'."\r\n";
		$xml.= '</sacoche>'."\r\n";
		$fichier_nom = str_replace('export_','import-',$action).'_'.$_SESSION['BASE'].'_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.'.$fichier_extension;
		// L'export pour SACoche on peut le zipper (le gain est très significatif : facteur 40 à 50 !)
		$zip = new ZipArchive();
		$result_open = $zip->open($dossier_export.$fichier_nom, ZIPARCHIVE::CREATE);
		if($result_open!==TRUE)
		{
			require('./_inc/tableau_zip_error.php');
			exit('Erreur : problème de création de l\'archive ZIP ('.$result_open.$tab_zip_error[$result_open].') !');
		}
		$zip->addFromString('import_validations.xml',$xml);
		$zip->close();
	}
	// Afficher le retour
	$se = ($nb_eleves>1)  ? 's' : '' ;
	$sp = ($nb_piliers>1) ? 's' : '' ;
	$si = ($nb_items>1)   ? 's' : '' ;
	$in = $only_positives ? '' : '(in)-' ;
	echo'<li><label class="valide">Fichier d\'export généré : '.$nb_piliers.' '.$in.'validation'.$sp.' de compétence'.$sp.' et '.$nb_items.' '.$in.'validation'.$si.' d\'item'.$si.' concernant '.$nb_eleves.' élève'.$se.'.</label></li>';
	echo'<li><a class="lien_ext" href="'.$dossier_export.$fichier_nom.'"><span class="file file_'.$fichier_extension.'">Récupérez le fichier au format <em>'.$fichier_extension.'</em> <img alt="" src="./_img/bulle_aide.png" title="Si le navigateur ouvre le fichier au lieu de l\'enregistrer, cliquer avec le bouton droit et choisir «&nbsp;Enregistrer&nbsp;sous...&nbsp;»." />.</span></a></li>';
	echo'<li>Vous devrez indiquer dans <em>lpc</em> les dates suivantes : <span class="b">'.html(CNIL_DATE_ENGAGEMENT).'</span> (déclaration <em>cnil</em>) et <span class="b">'.html(CNIL_DATE_RECEPISSE).'</span> (retour du récépissé).</li>';
	echo'<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Importer un fichier de validations
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if( in_array( $action , array('import_sacoche') ) )
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
	if(!in_array($extension,array('xml','zip')))
	{
		exit('Erreur : l\'extension du fichier transmis est incorrecte !');
	}
	$fichier_upload_nom = 'import_validations_'.$_SESSION['BASE'].'.xml';
	if($extension!='zip')
	{
		if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_upload_nom))
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
		$nom_fichier_extrait = 'import_validations.xml';
		if($zip->extractTo($dossier_import,$nom_fichier_extrait)!==true)
		{
			exit('Erreur : fichier '.$nom_fichier_extrait.' non trouvé dans l\'archive ZIP !');
		}
		$zip->close();
		if(!rename($dossier_import.$nom_fichier_extrait , $dossier_import.$fichier_upload_nom))
		{
			exit('Erreur : le fichier n\'a pas pu être enregistré sur le serveur.');
		}
	}
	$fichier_contenu = file_get_contents($dossier_import.$fichier_upload_nom);
	$fichier_contenu = utf8($fichier_contenu); // Mettre en UTF-8 si besoin
	$xml = @simplexml_load_string($fichier_contenu);
	if($xml===false)
	{
		exit('Erreur : le fichier transmis n\'est pas un XML valide !');
	}
	// On extrait les infos du XML
	$tab_eleve_fichier = array();
	if( ($xml->donnees) && ($xml->donnees->eleve) )
	{
		foreach ($xml->donnees->eleve as $eleve)
		{
			$tab_eleve_fichier['sconet_id'][] = clean_entier($eleve->attributes()->id);
			$tab_eleve_fichier['nom'][]       = clean_nom($eleve->attributes()->nom);
			$tab_eleve_fichier['prenom'][]    = clean_prenom($eleve->attributes()->prenom);
			// Indication des (in-)validations
			$tab_validations = array();
			if($eleve->palier)
			{
				foreach ($eleve->palier as $palier)
				{
					$palier_id = clean_entier($palier->attributes()->id);
					if($palier->competence)
					{
						foreach ($palier->competence as $competence)
						{
							$pilier_id = clean_entier($competence->attributes()->id);
							if( ($competence->validation) && ($competence->validation->date) )
							{
								$date = clean_texte($competence->validation->date) ;
								$etat = ($competence->validation->etat) ? clean_entier($competence->validation->etat) : 1 ;
								$info = ($competence->validation->info) ? html_decode($competence->validation->info) : $action ;
								$tab_validations['pilier'][$pilier_id] = array('date'=>$date,'etat'=>$etat,'info'=>$info);
							}
							if( ($competence->item) && ($competence->item->renseignement) && ($competence->item->renseignement->date) )
							{
								foreach ($competence->item as $item)
								{
									if( ($item->renseignement) && ($item->renseignement->date) )
									{
										$item_id = clean_entier($item->attributes()->id);
										$date = clean_texte($item->renseignement->date) ;
										$etat = ($item->renseignement->etat) ? clean_entier($item->renseignement->etat) : 1 ;
										$info = ($item->renseignement->info) ? html_decode($item->renseignement->info) : $action ;
										$tab_validations['entree'][$item_id] = array('date'=>$date,'etat'=>$etat,'info'=>$info);
									}
								}
							}
						}
					}
				}
			}
			$tab_eleve_fichier['validations'][] = $tab_validations;
		}
	}
	// On récupère les infos de la base pour les comparer ; on commence par les identités des élèves
	$tab_eleve_base                = array();
	$tab_eleve_base['sconet_id']   = array();
	$tab_eleve_base['nom']         = array();
	$tab_eleve_base['prenom']      = array();
	$tab_eleve_base['validations'] = array();
	$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_eleves_identite_et_sconet();
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleve_base['sconet_id'][$DB_ROW['user_id']]  = (int)$DB_ROW['user_sconet_id'];
		$tab_eleve_base['nom'][$DB_ROW['user_id']]        = $DB_ROW['user_nom'];
		$tab_eleve_base['prenom'][$DB_ROW['user_id']]     = $DB_ROW['user_prenom'];
	}
	// Voyons donc si on trouve les élèves du fichier dans la base
	$tab_i_fichier_TO_id_base = array();
	// Pour préparer l'affichage
	$lignes_ignorer   = '';
	$lignes_modifier  = '';
	$lignes_inchanger = '';
	$tab_indices_fichier = array_keys($tab_eleve_fichier['sconet_id']);
	// Parcourir chaque entrée du fichier
	foreach($tab_indices_fichier as $i_fichier)
	{
		$id_base = false;
		// Recherche sur sconet_id
		if( (!$id_base) && ($tab_eleve_fichier['sconet_id'][$i_fichier]) )
		{
			$id_base = array_search($tab_eleve_fichier['sconet_id'][$i_fichier],$tab_eleve_base['sconet_id']);
		}
		// Si pas trouvé, recherche sur nom prénom
		if(!$id_base)
		{
			$tab_id_nom    = array_keys($tab_eleve_base['nom'],$tab_eleve_fichier['nom'][$i_fichier]);
			$tab_id_prenom = array_keys($tab_eleve_base['prenom'],$tab_eleve_fichier['prenom'][$i_fichier]);
			$tab_id_commun = array_intersect($tab_id_nom,$tab_id_prenom);
			$nb_homonymes  = count($tab_id_commun);
			if($nb_homonymes==1)
			{
				list($inutile,$id_base) = each($tab_id_commun);
			}
		}
		// Cas [1] : non trouvé dans la base : contenu à ignorer
		if(!$id_base)
		{
			$lignes_ignorer .= '<li><em>Ignoré</em> (non trouvé dans la base) : '.html($tab_users_fichier['nom'][$i_fichier].' '.$tab_users_fichier['prenom'][$i_fichier]).' ('.$tab_users_fichier['sconet_id'][$i_fichier].')</li>';
			unset( $tab_eleve_fichier['validations'][$i_fichier] );
		}
		// Cas [2] : trouvé dans la base : contenu à étudier par la suite
		else
		{
			$tab_i_fichier_TO_id_base[$i_fichier] = $id_base;
		}
	}
	unset( $tab_eleve_fichier['sconet_id'] , $tab_eleve_fichier['nom'] , $tab_eleve_fichier['prenom'] );
	if(count($tab_i_fichier_TO_id_base))
	{
		// On récupère les infos de la base pour les comparer ; on poursuit par les validations
		$tab_validations  = array();
		$listing_eleve_id = implode(',',$tab_i_fichier_TO_id_base);
		$only_positives   = FALSE ;
		// Validations des items
		$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_items($listing_eleve_id,$only_positives);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_validations[$DB_ROW['user_id']]['entree'][$DB_ROW['entree_id']] = $DB_ROW['validation_entree_date'] ; // Pas besoin d'autre chose que la date
		}
		// Validations des compétences
		$DB_TAB = DB_STRUCTURE_SOCLE::DB_lister_validations_competences($listing_eleve_id,$only_positives);
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_validations[$DB_ROW['user_id']]['pilier'][$DB_ROW['pilier_id']] = $DB_ROW['validation_pilier_date'] ; // Pas besoin d'autre chose que la date
		}
		// Parcourir chaque entrée du fichier
		foreach($tab_i_fichier_TO_id_base as $i_fichier => $id_base)
		{
			$nb_modifs = 0;
			// les validations de piliers
			if(isset($tab_eleve_fichier['validations'][$i_fichier]['pilier']))
			{
				foreach($tab_eleve_fichier['validations'][$i_fichier]['pilier'] as $pilier_id => $tab_infos_fichier)
				{
					if(!isset($tab_validations[$id_base]['pilier'][$pilier_id]))
					{
						DB_STRUCTURE_SOCLE::DB_ajouter_validation('pilier',$id_base,$pilier_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
						$nb_modifs++;
					}
					elseif($tab_validations[$id_base]['pilier'][$pilier_id]<$tab_infos_fichier['date'])
					{
						DB_STRUCTURE_SOCLE::DB_modifier_validation('pilier',$id_base,$pilier_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
						$nb_modifs++;
					}
				}
			}
			// les validations d'items
			if(isset($tab_eleve_fichier['validations'][$i_fichier]['entree']))
			{
				foreach($tab_eleve_fichier['validations'][$i_fichier]['entree'] as $entree_id => $tab_infos_fichier)
				{
					if(!isset($tab_validations[$id_base]['entree'][$entree_id]))
					{
						DB_STRUCTURE_SOCLE::DB_ajouter_validation('entree',$id_base,$entree_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
						$nb_modifs++;
					}
					elseif($tab_validations[$id_base]['entree'][$entree_id]<$tab_infos_fichier['date'])
					{
						DB_STRUCTURE_SOCLE::DB_modifier_validation('entree',$id_base,$entree_id,$tab_infos_fichier['etat'],$tab_infos_fichier['date'],$tab_infos_fichier['info']);
						$nb_modifs++;
					}
				}
			}
			if($nb_modifs)
			{
				$s = ($nb_modifs>1) ? 's' : '' ;
				$lignes_modifier .= '<li><em>Modifié</em> ('.$nb_modifs.' import'.$s.' de validation'.$s.' ) : '.html($tab_eleve_base['nom'][$id_base].' '.$tab_eleve_base['prenom'][$id_base]).' ('.$tab_eleve_base['sconet_id'][$id_base].')</li>';
			}
			else
			{
				$lignes_inchanger .= '<li><em>Inchangé</em> (pas de validations nouvelles) : '.html($tab_eleve_base['nom'][$id_base].' '.$tab_eleve_base['prenom'][$id_base]).' ('.$tab_eleve_base['sconet_id'][$id_base].')</li>';
			}
		}
	}
	// Afficher le retour
	echo'<li><label class="valide">Fichier d\'import traité.</label></li>';
	echo $lignes_modifier;
	echo $lignes_inchanger;
	echo $lignes_ignorer;
	exit();
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
