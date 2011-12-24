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
 * Pour tester la validité d'un document XML, on peut utiliser un analyseur syntaxique XML : http://fr3.php.net/manual/fr/book.xml.php
 * Voir en particulier l'exemple http://fr3.php.net/manual/fr/example.xml-structure.php
 * 
 * Mais ceci ne permet pas de vérifier la conformité d'un XML avec une DTD.
 * DOMDocument le permet : http://fr2.php.net/manual/fr/domdocument.validate.php
 * Mais d'une part ça emmet des warnings et d'autre part ça ne retourne qu'un booléen sans détails sur les erreurs trouvées
 * 
 * Pour y remédier on peut utiliser cette extention de classe "MyDOMDocument" : http://fr2.php.net/manual/fr/domdocument.validate.php#85792
 * Mais attention : il faut lui fournir un objet DOMDocument et load ou loadXML provoquent des warnings préliminaires si le XML est mal formé.
 * 
 * Ma solution est d'utiliser :
 * 1. dans un premier temps l'analyseur syntaxique XML xml_parse pour vérifier que le XML est bien formé
 * 2. dans un second temps l'extention de classe MyDOMDocument pour vérifier la conformité avec la DTD
 * 
 * J'en ai fait la fonction ci-dessous "analyser_XML($fichier)".
 * La classe "MyDOMDocument" est dans autochargée (elle se trouve ici : _inc/class.domdocument.php).
 * 
 * @param string    $fichier_adresse
 * @return string   'ok' ou un message d'erreur
 */
function analyser_XML($fichier_adresse)
{
	// Récupération du contenu du fichier
	$fichier_contenu = file_get_contents($fichier_adresse);
	$fichier_contenu = utf8($fichier_contenu); // Mettre en UTF-8 si besoin
	Ecrire_Fichier($fichier_adresse,$fichier_contenu); // Mettre à jour le fichier au cas où.
	// Analyse XML (s'arrête à la 1ère erreur trouvée)
	$xml_parser = xml_parser_create();
	$valid_XML = xml_parse($xml_parser , $fichier_contenu , TRUE);
	if(!$valid_XML)
	{
		return sprintf("Erreur XML ligne %d (%s)" , xml_get_current_line_number($xml_parser) , xml_error_string(xml_get_error_code($xml_parser)));
	}
	xml_parser_free($xml_parser);
	// Analyse DTD (renvoie un tableau d'erreurs, affiche la dernière)
	$xml = new DOMDocument;
	$xml -> load($fichier_adresse);
	$xml = new MyDOMDocument($xml);
	$valid_DTD = $xml->validate();
	if(!$valid_DTD)
	{
		return 'Erreur DTD : '.end($xml->errors);
	}
	// Tout est ok
	return 'ok';
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
	return str_replace( '=""></option>' , '="">Toutes les structures partageant au moins un référentiel</option>' , url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post) );
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
 * Ajouter le signature au XML d'export des validations vers LPC.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $matiere_id
 * @param int       $niveau_id
 * @param string    $exportXML
 * @return string   le XML signé ou un message d'erreur
 */
function signer_exportLPC($sesamath_id,$sesamath_key,$exportXML)
{
	$tab_post = array();
	$tab_post['fichier']        = 'lpc_signature';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['exportXML']      = $exportXML;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['version_base']   = VERSION_BASE; // La base doit être compatible (problème de socle modifié...)
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	$tab_post['integrite_key']  = fabriquer_chaine_integrite();
	return url_get_contents(SERVEUR_LPC_SIGNATURE,$tab_post,$timeout=10);
}

/**
 * Appel au serveur communautaire pour élaborer / éditer une page de liens (ressources pour travailler).
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $item_id
 * @return string   contenu html ou un message d'erreur
 */
function afficher_liens_ressources($sesamath_id,$sesamath_key,$item_id)
{
	$tab_post = array();
	$tab_post['fichier']        = 'liens_ressources_elaborer_editer';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['item_id']        = $item_id;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour générer / actualiser une page de liens (ressources pour travailler).
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $item_id
 * @param string    $item_nom
 * @param string    $objet   { page_create | page_update }
 * @param string    $page_serialize   tableau sérializé
 * @return string   adresse html ou un message d'erreur
 */
function fabriquer_liens_ressources($sesamath_id,$sesamath_key,$item_id,$item_nom,$objet,$page_serialize)
{
	$tab_post = array();
	$tab_post['fichier']        = 'liens_ressources_generer_actualiser';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['item_id']        = $item_id;
	$tab_post['item_nom']       = $item_nom;
	$tab_post['objet']          = $objet;
	$tab_post['page_serialize'] = $page_serialize;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	$tab_post['adresse_retour'] = SERVEUR_ADRESSE;
	$tab_post['integrite_key']  = fabriquer_chaine_integrite();
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour rechercher à partir de mots clefs des liens existants de ressources pour travailler.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param int       $item_id
 * @param string    $findme
 * @return string   contenu html ou un message d'erreur
 */
function rechercher_ressources($sesamath_id,$sesamath_key,$item_id,$findme)
{
	$tab_post = array();
	$tab_post['fichier']        = 'liens_ressources_rechercher';
	$tab_post['sesamath_id']    = $sesamath_id;
	$tab_post['sesamath_key']   = $sesamath_key;
	$tab_post['item_id']        = $item_id;
	$tab_post['findme']         = $findme;
	$tab_post['version_prog']   = VERSION_PROG; // Le service web doit être compatible
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post);
}

/**
 * Appel au serveur communautaire pour envoyer un fichier uploadé par un utilisateur.
 * 
 * @param int       $sesamath_id
 * @param string    $sesamath_key
 * @param string    $matiere_ref
 * @param string    $fichier_nom
 * @param string    $fichier_contenu
 * @return string   url du fichier ou un message d'erreur
 */
function uploader_ressource($sesamath_id,$sesamath_key,$matiere_ref,$fichier_nom,$fichier_contenu)
{
	$tab_post = array();
	$tab_post['fichier']         = 'ressource_uploader';
	$tab_post['sesamath_id']     = $sesamath_id;
	$tab_post['sesamath_key']    = $sesamath_key;
	$tab_post['matiere_ref']     = $matiere_ref;
	$tab_post['fichier_nom']     = $fichier_nom;
	$tab_post['fichier_contenu'] = base64_encode($fichier_contenu);
	$tab_post['version_prog']    = VERSION_PROG; // Le service web doit être compatible
	$tab_post['adresse_retour']  = SERVEUR_ADRESSE;
	$tab_post['integrite_key']   = fabriquer_chaine_integrite();
	return url_get_contents(SERVEUR_COMMUNAUTAIRE,$tab_post,$timeout=20);
}

/**
 * Fabriquer le md5 d'un fichier pour le comparer à ceux d'une archive.
 * Volontairement non utilisé par fabriquer_chaine_integrite() car un peu différent.
 * 
 * @param string
 * @return string
 */
function fabriquer_md5_file($fichier)
{
	// Lors du transfert FTP de fichiers, il arrive que les \r\n en fin de ligne soient convertis en \n, ce qui fait que md5_file() renvoie un résultat différent.
	// Pour y remédier on utilise son équivalent md5(file_get_contents()) couplé à un remplacement des caractères de fin de ligne.
	return md5( str_replace( array("\r\n","\r","\n") , ' ' , file_get_contents($fichier) ) );
}

/**
 * Fabriquer un md5 attestant l'intégrité des fichiers d'une installation.
 * 
 * @param void
 * @return string
 */
function fabriquer_chaine_integrite()
{
	// Liste de fichiers représentatifs mais n'impactant pas sur des modifs à la marge.
	include_once('./_inc/tableau_fichier_integrite.php');
	// Fabrication de la chaine
	$chaine = '';
	$tab_crlf = array("\r\n","\r","\n");
	foreach($tab_fichier_integrite as $fichier)
	{
		// Lors du transfert FTP de fichiers, il arrive que les \r\n en fin de ligne soient convertis en \n, ce qui fait que md5_file() renvoie un résultat différent.
		// Pour y remédier on utilise son équivalent md5(file_get_contents()) couplé à une suppression des caractères de fin de ligne.
		$chaine .= md5( str_replace( $tab_crlf , '' , file_get_contents('./'.$fichier) ) );
	}
	return md5($chaine);
}

?>