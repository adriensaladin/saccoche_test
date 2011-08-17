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

/**
 * Transmettre le XML d'un référentiel au serveur communautaire.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $matiere_id
 * @param int       $niveau_id
 * @param string    $arbreXML       si fourni vide, provoquera l'effacement du référentiel mis en partage
 * @return string   "ok" ou un message d'erreur
 */
function envoyer_arborescence_XML($sesamath_id,$sesamath_key,$matiere_id,$niveau_id,$arbreXML)
{
	$tab_post = array();
	$tab_post['fichier']        = 'referentiel_uploader';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['matiere_id']     = $matiere_id;
	$tab_post['niveau_id']      = $niveau_id;
	$tab_post['arbreXML']       = $arbreXML;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['version_base']   = VERSION_BASE; // La base doit être compatible (problème de socle modifié...)
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	$tab_post['integrite_key']  = fabriquer_chaine_integrite();
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post,$timeout=10);
}

/**
 * Demander à ce que nous soit retourné le XML d'un référentiel depuis le serveur communautaire.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $referentiel_id
 * @return string   le XML ou un message d'erreur
 */
function recuperer_arborescence_XML($sesamath_id,$sesamath_key,$referentiel_id)
{
	$tab_post = array();
	$tab_post['fichier']        = 'referentiel_downloader';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['referentiel_id'] = $referentiel_id;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['version_base']   = VERSION_BASE; // La base doit être compatible (problème de socle modifié...)
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	$tab_post['integrite_key']  = fabriquer_chaine_integrite();
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post,$timeout=10);
}

/**
 * Vérifier qu'une arborescence XML d'un référentiel est syntaxiquement valide.
 * 
 * @param string    $arbreXML
 * @return string   "ok" ou "Erreur..."
 */
function verifier_arborescence_XML($arbreXML)
{
	// On ajoute déclaration et doctype au fichier (évite que l'utilisateur ait à se soucier de cette ligne et permet de le modifier en cas de réorganisation
	// Attention, le chemin du DTD est relatif par rapport à l'emplacement du fichier XML (pas celui du script en cours) !
	$fichier_adresse = './__tmp/import/referentiel_'.date('Y-m-d_H-i-s').'_'.mt_rand().'.xml';
	$fichier_contenu = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".'<!DOCTYPE arbre SYSTEM "../../_dtd/referentiel.dtd">'."\r\n".$arbreXML;
	$fichier_contenu = utf8($fichier_contenu); // Mettre en UTF-8 si besoin
	// On enregistre temporairement dans un fichier pour analyse
	Ecrire_Fichier($fichier_adresse,$fichier_contenu);
	// On lance le test
	require('class.domdocument.php');	// Ne pas mettre de chemin !
	$test_XML_valide = analyser_XML($fichier_adresse);
	// On efface le fichier temporaire
	unlink($fichier_adresse);
	return $test_XML_valide;
}

/**
 * Demander à ce que la structure soit identifiée et enregistrée dans la base du serveur communautaire.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @return string   'ok' ou un message d'erreur
 */
function Sesamath_enregistrer_structure($sesamath_id,$sesamath_key)
{
	$tab_post = array();
	$tab_post['fichier']        = 'structure_enregistrer';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour afficher le formulaire géographique n°1.
 * 
 * @param void
 * @return string   '<option>...</option>' ou un message d'erreur
 */
function Sesamath_afficher_formulaire_geo1()
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_afficher_formulaire_geo';
	$tab_post['etape']        = 1;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour afficher le formulaire géographique n°2.
 * 
 * @param int       $geo1
 * @return string   '<option>...</option>' ou un message d'erreur
 */
function Sesamath_afficher_formulaire_geo2($geo1)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_afficher_formulaire_geo';
	$tab_post['etape']        = 2;
	$tab_post['geo1']         = $geo1;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour afficher le formulaire géographique n°3.
 * 
 * @param int       $geo1
 * @param int       $geo2
 * @return string   '<option>...</option>' ou un message d'erreur
 */
function Sesamath_afficher_formulaire_geo3($geo1,$geo2)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_afficher_formulaire_geo';
	$tab_post['etape']        = 3;
	$tab_post['geo1']         = $geo1;
	$tab_post['geo2']         = $geo2;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour lister les structures de la commune indiquée.
 * 
 * @param int       $geo3
 * @return string   '<option>...</option>' ou un message d'erreur
 */
function Sesamath_lister_structures_by_commune($geo3)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_lister_structures';
	$tab_post['methode']      = 'commune';
	$tab_post['geo3']         = $geo3;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour récupérer la structure du numéro UAI indiqué.
 * 
 * @param string    $uai
 * @return string   '<option>...</option>' ou un message d'erreur
 */
function Sesamath_recuperer_structure_by_UAI($uai)
{
	$tab_post = array();
	$tab_post['fichier']      = 'Sesamath_lister_structures';
	$tab_post['methode']      = 'UAI';
	$tab_post['uai']          = $uai;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour afficher le formulaire des structures ayant partagées au moins un référentiel.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @return string   '<option>...</option>' ou un message d'erreur
 */
function afficher_formulaire_structures_communautaires($sesamath_id,$sesamath_key)
{
	$tab_post = array();
	$tab_post['fichier']      = 'structures_afficher_formulaire';
	$tab_post['sesamath_id']  = $sesamath_id;
	$tab_post['sesamath_key'] = $sesamath_key;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour lister les référentiels partagés trouvés selon les critères retenus (matière / niveau / structure).
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $matiere_id
 * @param int       $niveau_id
 * @param int       $structure_id
 * @return string   listing ou un message d'erreur
 */
function afficher_liste_referentiels($sesamath_id,$sesamath_key,$matiere_id,$niveau_id,$structure_id)
{
	$tab_post = array();
	$tab_post['fichier']      = 'referentiels_afficher_liste';
	$tab_post['sesamath_id']  = $sesamath_id;
	$tab_post['sesamath_key'] = $sesamath_key;
	$tab_post['matiere_id']   = $matiere_id;
	$tab_post['niveau_id']    = $niveau_id;
	$tab_post['structure_id'] = $structure_id;
	$tab_post['version_prog'] = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire voir le contenu d'un référentiel partagé.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $referentiel_id
 * @return string   arborescence ou un message d'erreur
 */
function afficher_contenu_referentiel($sesamath_id,$sesamath_key,$referentiel_id)
{
	$tab_post = array();
	$tab_post['fichier']        = 'referentiel_afficher_contenu';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['referentiel_id'] = $referentiel_id;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Fabriquer un md5 attestant l'intégrité des fichiers.
 * 
 * @param void
 * @return string
 */
function fabriquer_chaine_integrite()
{
	// Liste de fichiers représentatifs mais n'impactant pas sur des modifs à la marge.
	$tab_fichiers = array(
		'index.php',
		'ajax.php',
		'VERSION.txt',
		'_img/logo_grand.gif',
		'_inc/constantes.php',
		'_inc/fonction_appel_serveur_communautaire.php',
		'_inc/fonction_sessions.php',
		'pages/__menu_professeur.html',
		'pages/validation_socle_item.ajax.php',
		'pages/validation_socle_pilier.ajax.php'
	);
	// Fabrication de la chaine
	$chaine = '';
	foreach($tab_fichiers as $fichier)
	{
		$chaine .= md5_file('./'.$fichier);
	}
	return md5($chaine);
}

?>