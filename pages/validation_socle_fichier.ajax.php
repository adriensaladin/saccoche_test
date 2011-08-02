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

$top_depart = microtime(TRUE);

$dossier_export = './__tmp/export/';
$dossier_import = './__tmp/import/';

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
// Exporter un fichier de validations à destination de LPC
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='exporter')
{
	$tab_validations = array(); // [i] => [user_id][palier_id][pilier_id][entree_id] => [date] Retenir les validations ; item à 0 si validation d'un palier.
	$tab_eleves      = array(); // [user_id] => array(nom,prenom,sconet_id) Ordonné par classe et alphabet.
	// Validations des items
	$DB_TAB = DB_STRUCTURE_lister_validations_items($only_positives=TRUE);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_validations[$DB_ROW['user_id']][$DB_ROW['palier_id']][$DB_ROW['pilier_id']][$DB_ROW['entree_id']] = $DB_ROW['validation_entree_date'] ;
		$tab_eleves[$DB_ROW['user_id']] = $DB_ROW['user_id'] ;
	}
	// Validations des compétences
	$DB_TAB = DB_STRUCTURE_lister_validations_competences($only_positives=TRUE);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_validations[$DB_ROW['user_id']][$DB_ROW['palier_id']][$DB_ROW['pilier_id']][0] = $DB_ROW['validation_pilier_date'] ;
		$tab_eleves[$DB_ROW['user_id']] = $DB_ROW['user_id'] ;
	}
	// Validations trouvées ?
	if(!count($tab_eleves))
	{
		exit('<li><label class="alerte">Erreur : aucune validation positive d\'élève trouvée !</label></li>');
	}
	// Données élèves
	$listing_eleve_id = implode(',',$tab_eleves);
	$tab_eleves       = array(); // On réinitialise le tableau des élèves pour ne conserver que ce qui est trouvé dans user au cas où cela ne concorderait pas.
	$DB_TAB = DB_STRUCTURE_lister_eleves_cibles_actifs_avec_sconet_id($listing_eleve_id);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_eleves[$DB_ROW['user_id']] = array('nom'=>$DB_ROW['user_nom'],'prenom'=>$DB_ROW['user_prenom'],'sconet_id'=>$DB_ROW['user_sconet_id']);
	}
	// Elèves trouvés ?
	if(!count($DB_TAB))
	{
		exit('<li><label class="alerte">Erreur : les élèves trouvés n\'ont pas d\'identifiant Sconet ou sont incatifs !</label></li>');
	}
	// Fabrication du XML
	$nb_eleves  = 0;
	$nb_piliers = 0;
	$nb_items   = 0;
	$xml = '<?xml version="1.0" encoding="ISO-8859-15"?>'."\r\n";
	$xml.= '<lpc xmlns="urn:ac-grenoble.fr:lpc:import:v1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:ac-grenoble.fr:lpc:import:v1.0 import-lpc.xsd">'."\r\n";
	$xml.= '	<entete>'."\r\n";
	$xml.= '		<editeur>SESAMATH</editeur>'."\r\n";
	$xml.= '		<application>SACOCHE</application>'."\r\n";
	$xml.= '		<etablissement>'.html($_SESSION['UAI']).'</etablissement>'."\r\n";
	$xml.= '	</entete>'."\r\n";
	$xml.= '	<donnees>'."\r\n";
	foreach($tab_eleves as $user_id => $tab_user)
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
					$xml.= '						<date>'.$tab_item[0].'</date>'."\r\n";
					$xml.= '					</validation>'."\r\n";
					unset($tab_item[0]);
				}
				if(count($tab_item))
				{
					// Validation d'items de la compétence
					foreach($tab_item as $item_id => $date)
					{
						$nb_items++;
						$xml.= '					<item id="'.$item_id.'">'."\r\n";
						$xml.= '						<renseignement>'."\r\n";
						$xml.= '							<date>'.$date.'</date>'."\r\n";
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
	$xml.= '	</donnees>'."\r\n";
	$xml.= '</lpc>'."\r\n";
	// Signer le fichier via un appel au serveur communautaire
	$xml = utf8_decode($xml);
	// ...
	// ...
	// ...
	/*
	$signature = signer_exportLPC($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$xml); // fonction sur le modèle de envoyer_arborescence_XML()
	if(substr($signature,0,13)!='<ds:Signature') // Voir si ça renvoie le XML signé ou que la signature...
	{
		exit('<li><label class="alerte">'.html($signature).'</label></li>');
	}
	*/
	// ...
	// ...
	// ...
	// Enregistrement et afficher le retour
	$fichier_nom = 'import-lpc-'.$_SESSION['BASE'].'_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.xml';
	Ecrire_Fichier( $dossier_export.$fichier_nom , $xml );
	$se = ($nb_eleves>1)  ? 's' : '' ;
	$sp = ($nb_piliers>1) ? 's' : '' ;
	$si = ($nb_items>1)   ? 's' : '' ;
	echo'<li><label class="valide">Fichier d\'export généré ('.$nb_piliers.' validation'.$sp.' de compétence'.$sp.' et '.$nb_items.' validation'.$si.' d\'item'.$si.' concernant '.$nb_eleves.' élève'.$se.').</label></li>';
	echo'<li><a class="lien_ext" href="'.$dossier_export.$fichier_nom.'">Récupérez le fichier au format XML.</a></li>';
	echo'<li><label class="alerte">Pour des raisons de sécurité et de confidentialité, ce fichier sera effacé du serveur dans 1h.</label></li>';
	exit();
}

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Uploader / vérifier un fichier de validations en provenance de LPC
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

if($action=='uploader')
{
/*
	$tab_file = $_FILES['userfile'];
	$fnom_transmis = $tab_file['name'];
	$fnom_serveur = $tab_file['tmp_name'];
	$ftaille = $tab_file['size'];
	$ferreur = $tab_file['error'];
	if( (!file_exists($fnom_serveur)) || (!$ftaille) || ($ferreur) )
	{
		require_once('./_inc/fonction_infos_serveur.php');
		exit('<li><label class="alerte">Erreur : problème de transfert ! Fichier trop lourd ? min(memory_limit,post_max_size,upload_max_filesize)='.minimum_limitations_upload().'</label></li>');
	}
	$extension = strtolower(pathinfo($fnom_transmis,PATHINFO_EXTENSION));
	if($extension!='zip')
	{
		exit('<li><label class="alerte">Erreur : l\'extension du fichier transmis est incorrecte !</label></li>');
	}
	$fichier_upload_nom = 'dump_'.$_SESSION['BASE'].'_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.zip';
	if(!move_uploaded_file($fnom_serveur , $dossier_import.$fichier_upload_nom))
	{
		exit('<li><label class="alerte">Erreur : le fichier n\'a pas pu être enregistré sur le serveur.</label></li>');
	}
	// Créer ou vider le dossier temporaire
	Creer_ou_Vider_Dossier($dossier_temp);
	// Dezipper dans le dossier temporaire
	$zip = new ZipArchive();
	$result_open = $zip->open($dossier_import.$fichier_upload_nom);
	if($result_open!==true)
	{
		require('./_inc/tableau_zip_error.php');
		exit('<li><label class="alerte">Erreur : votre archive ZIP n\'a pas pu être ouverte ('.$result_open.$tab_zip_error[$result_open].') !</label></li>');
	}
	$zip->extractTo($dossier_temp);
	$zip->close();
	unlink($dossier_import.$fichier_upload_nom);
	// Vérifier le contenu : noms des fichiers
	$fichier_taille_maximale = verifier_dossier_decompression_sauvegarde($dossier_temp);
	if(!$fichier_taille_maximale)
	{
		Supprimer_Dossier($dossier_temp); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit('<li><label class="alerte">Erreur : votre archive ZIP ne semble pas contenir les fichiers d\'une sauvegarde de la base effectuée par SACoche !</label></li>');
	}
	// Vérifier le contenu : taille des requêtes
	if( !verifier_taille_requetes($fichier_taille_maximale) )
	{
		Supprimer_Dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit('<li><label class="alerte">Erreur : votre archive ZIP contient au moins un fichier dont la taille dépasse la limitation <em>max_allowed_packet</em> de MySQL !</label></li>');
	}
	// Vérifier le contenu : version de la base compatible avec la version logicielle
	if( version_base_fichier_svg($dossier_temp) > VERSION_BASE )
	{
		Supprimer_Dossier($dossier_temp_sql); // Pas seulement vider, au cas où il y aurait des sous-dossiers créés par l'archive.
		exit('<li><label class="alerte">Erreur : votre archive ZIP contient une sauvegarde plus récente que celle supportée par cette installation ! Le webmestre doit préalablement mettre à jour le programme...</label></li>');
	}
	// Afficher le retour
	echo'<li><label class="valide">Contenu du fichier récupéré avec succès.</label></li>';
	exit();
*/
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Importer les données d'un fichier de validations en provenance de LPC
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='importer')
{
	/*
	// Bloquer l'application
	bloquer_application('automate',$_SESSION['BASE'],'Restauration de la base en cours.');
	// Restaurer des fichiers de svg et mettre la base à jour si besoin.
	$texte_maj = restaurer_tables_base_etablissement($dossier_temp);
	// Débloquer l'application
	debloquer_application('automate',$_SESSION['BASE']);
	// Supprimer le dossier temporaire
	Supprimer_Dossier($dossier_temp);
	// Afficher le retour
	$top_arrivee = microtime(TRUE);
	$duree = number_format($top_arrivee - $top_depart,2,',','');
	echo'<li><label class="valide">Restauration de la base réalisée'.$texte_maj.' en '.$duree.'s.</label></li>';
	echo'<li><label class="alerte">Veuillez maintenant vous déconnecter / reconnecter pour mettre la session en conformité avec la base restaurée.</label></li>';
	exit();
	*/
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On ne devrait pas en arriver là...
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
