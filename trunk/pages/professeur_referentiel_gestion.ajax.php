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
if(($_SESSION['SESAMATH_ID']==ID_DEMO)&&($_POST['action']!='Voir')){exit('Action désactivée pour la démo...');}

$action         = (isset($_POST['action']))         ? $_POST['action'] : '';
$matiere_id     = (isset($_POST['matiere_id']))     ? clean_entier($_POST['matiere_id'])     : 0;
$niveau_id      = (isset($_POST['niveau_id']))      ? clean_entier($_POST['niveau_id'])      : 0;
$structure_id   = (isset($_POST['structure_id']))   ? clean_entier($_POST['structure_id'])   : 0;
$nb_demandes    = (isset($_POST['nb_demandes']))    ? clean_entier($_POST['nb_demandes'])    : -1;	// Changer le nb de demandes
$partage        = (isset($_POST['partage']))        ? clean_texte($_POST['partage'])         : '';	// Changer l'état de partage
$methode        = (isset($_POST['methode']))        ? clean_texte($_POST['methode'])         : '';	// Changer le mode de calcul
$limite         = (isset($_POST['limite']))         ? clean_entier($_POST['limite'])         : -1;	// Changer le nb d'items pris en compte
$referentiel_id = (isset($_POST['referentiel_id'])) ? clean_entier($_POST['referentiel_id']) : -1;	// Référence du référentiel importé (0 si vierge), ou référence du référentiel à consulter

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier le nb de demandes autorisées pour une matière
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='NbDemandes') && $matiere_id && ($nb_demandes!=-1) && ($nb_demandes<10) )
{
	DB_STRUCTURE_REFERENTIEL::DB_modifier_matiere_nb_demandes($matiere_id,$nb_demandes);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Afficher le formulaire des structures ayant partagées au moins un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='Afficher_structures') // La vérification concernant le nombre de contraintes s'effectue après
{
	exit( afficher_formulaire_structures_communautaires($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY']) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Lister les référentiels partagés trouvés selon les critères retenus (matière / niveau / structure)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if($action=='Lister_referentiels') // La vérification concernant le nombre de contraintes s'effectue après
{
	exit( afficher_liste_referentiels($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$matiere_id,$niveau_id,$structure_id) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Voir le contenu d'un référentiel partagé
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Voir_referentiel') && $referentiel_id )
{
	exit( afficher_contenu_referentiel($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$referentiel_id) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Pour les autres cas on doit récupérer le paramètre ids
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

$ids = (isset($_POST['ids'])) ? $_POST['ids'] : '';
if(mb_substr_count($ids,'_')!=3)
{
	exit('Erreur avec les données transmises !');
}

list($prefixe,$perso,$matiere_id,$niveau_id) = explode('_',$ids);
$perso      = clean_entier($perso);
$matiere_id = clean_entier($matiere_id);
$niveau_id  = clean_entier($niveau_id);

$tab_partages = array('oui','non','bof','hs');
$tab_methodes = array('geometrique','arithmetique','classique','bestof1','bestof2','bestof3');
$tab_limites['geometrique']  = array(1,2,3,4,5);
$tab_limites['arithmetique'] = array(1,2,3,4,5,6,7,8,9);
$tab_limites['classique']    = array(1,2,3,4,5,6,7,8,9,10,15,20,30,40,50,0);
$tab_limites['bestof1']      = array(1,2,3,4,5,6,7,8,9,10,15,20,30,40,50,0);
$tab_limites['bestof2']      = array(  2,3,4,5,6,7,8,9,10,15,20,30,40,50,0);
$tab_limites['bestof3']      = array(    3,4,5,6,7,8,9,10,15,20,30,40,50,0);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du détail d'un référentiel pour une matière et un niveau donnés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Voir') && $matiere_id && $niveau_id )
{
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id,$only_socle=false,$only_item=false,$socle_nom=true);
	exit( afficher_arborescence_matiere_from_SQL($DB_TAB,$dynamique=false,$reference=false,$aff_coef=true,$aff_cart=true,$aff_socle='image',$aff_lien='image',$aff_input=false) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier le partage d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Partager') && ($perso==0) && $matiere_id && $niveau_id && in_array($partage,$tab_partages) && ($partage!='hs') )
{
	if( ($partage=='oui') && ( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) ) )
	{
		exit('Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.');
	}
	// Envoyer le référentiel (éventuellement vide pour l'effacer) vers le serveur de partage
	if($partage=='oui')
	{
		$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence(0,$matiere_id,$niveau_id,$only_socle=false,$only_item=false,$socle_nom=false);
		$arbreXML = exporter_arborescence_to_XML($DB_TAB);
		$reponse = envoyer_arborescence_XML($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$matiere_id,$niveau_id,$arbreXML);
	}
	else
	{
		$reponse = envoyer_arborescence_XML($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$matiere_id,$niveau_id,'');
	}
	// Analyse de la réponse retournée par le serveur de partage
	if($reponse!='ok')
	{
		exit($reponse);
	}
	// Tout s'est bien passé si on arrive jusque là...
	$date_mysql = date("Y-m-d");
	DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel( $matiere_id , $niveau_id , array(':partage_etat'=>$partage,':partage_date'=>$date_mysql) );
	// Retour envoyé
	$tab_partage = array('oui'=>'<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/etat/partage_oui.gif" />','non'=>'<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/etat/partage_non.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/etat/partage_non.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/etat/partage_non.gif" />');
	exit( str_replace('◄DATE►',affich_date($date_mysql),$tab_partage[$partage]) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Mettre à jour sur le serveur de partage la dernière version d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Envoyer') && ($perso==0) && $matiere_id && $niveau_id )
{
	if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
	{
		exit('Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.');
	}
	// Envoyer le référentiel vers le serveur de partage
	$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence(0,$matiere_id,$niveau_id,$only_socle=false,$only_item=false,$socle_nom=false);
	$arbreXML = exporter_arborescence_to_XML($DB_TAB);
	$reponse = envoyer_arborescence_XML($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$matiere_id,$niveau_id,$arbreXML);
	// Analyse de la réponse retournée par le serveur de partage
	if($reponse!='ok')
	{
		exit($reponse);
	}
	// Tout s'est bien passé si on arrive jusque là...
	$date_mysql = date("Y-m-d");
	DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel( $matiere_id , $niveau_id , array(':partage_date'=>$date_mysql) );
	// Retour envoyé
	exit('<img title="Référentiel partagé sur le serveur communautaire (MAJ le '.affich_date($date_mysql).')." alt="" src="./_img/etat/partage_oui.gif" />');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Supprimer un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Retirer') && $matiere_id && $niveau_id && in_array($partage,$tab_partages) )
{
	// S'il était partagé, il faut le retirer du serveur communautaire
	if($partage=='oui')
	{
		if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
		{
			exit('Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.');
		}
		$reponse = envoyer_arborescence_XML($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$matiere_id,$niveau_id,'');
		if($reponse!='ok')
		{
			exit($reponse);
		}
	}
	DB_STRUCTURE_REFERENTIEL::DB_supprimer_referentiel_matiere_niveau($matiere_id,$niveau_id);
	// Log de l'action
	ajouter_log_SACoche('Suppression d\'un référentiel (matière '.$matiere_id.' / niveau '.$niveau_id.').');
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier le mode de calcul d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Calculer') && $matiere_id && $niveau_id && in_array($methode,$tab_methodes) && in_array($limite,$tab_limites[$methode]) )
{
	DB_STRUCTURE_REFERENTIEL::DB_modifier_referentiel( $matiere_id , $niveau_id , array(':calcul_methode'=>$methode,':calcul_limite'=>$limite) );
	if($limite==1)	// si une seule saisie prise en compte
	{
		$retour = 'Seule la dernière saisie compte.';
	}
	elseif($methode=='classique')	// si moyenne classique
	{
		$retour = ($limite==0) ? 'Moyenne de toutes les saisies.' : 'Moyenne des '.$limite.' dernières saisies.';
	}
	elseif(in_array($methode,array('geometrique','arithmetique')))	// si moyenne geometrique | arithmetique
	{
		$seize = (($methode=='geometrique')&&($limite==5)) ? 1 : 0 ;
		$coefs = ($methode=='arithmetique') ? substr('1/2/3/4/5/6/7/8/9/',0,2*$limite-19) : substr('1/2/4/8/16/',0,2*$limite-12+$seize) ;
		$retour = 'Les '.$limite.' dernières saisies &times;'.$coefs.'.';
	}
	elseif($methode=='bestof1')	// si meilleure note
	{
		$retour = ($limite==0) ? 'Seule la meilleure saisie compte.' : 'Meilleure des '.$limite.' dernières saisies.';
	}
	elseif(in_array($methode,array('bestof2','bestof3')))	// si 2 | 3 meilleures notes
	{
		$nb_best = (int)substr($methode,-1);
		$retour = ($limite==0) ? 'Moyenne des '.$nb_best.' meilleures saisies.' : 'Moyenne des '.$nb_best.' meilleures saisies parmi les '.$limite.' dernières.';
	}
	exit('ok'.$retour);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ajouter un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

if( ($action=='Ajouter') && $matiere_id && $niveau_id )
{
	if( DB_STRUCTURE_REFERENTIEL::DB_tester_referentiel($matiere_id,$niveau_id) )
	{
		exit('Ce référentiel existe déjà ! Un autre administrateur de la même matière vient probablement de l\'importer... Actualisez cette page.');
	}
	if($referentiel_id==0)
	{
		// C'est une demande de partir d'un référentiel vierge : on ne peut que créer un nouveau référentiel
		$partage = ($perso==1) ? 'hs' : 'non' ;
		DB_STRUCTURE_REFERENTIEL::DB_ajouter_referentiel($matiere_id,$niveau_id,$partage);
		exit('ok');
	}
	elseif($referentiel_id>0)
	{
		// C'est une demande de récupérer un référentiel provenant du serveur communautaire pour se le dupliquer
		if( (!$_SESSION['SESAMATH_ID']) || (!$_SESSION['SESAMATH_KEY']) )
		{
			exit('Pour échanger avec le serveur communautaire, un administrateur doit identifier l\'établissement dans la base Sésamath.');
		}
		// Récupérer le référentiel
		$arbreXML = recuperer_arborescence_XML($_SESSION['SESAMATH_ID'],$_SESSION['SESAMATH_KEY'],$referentiel_id);
		if(mb_substr($arbreXML,0,6)=='Erreur')
		{
			exit($arbreXML);
		}
		// L'analyser
		$test_XML_valide = verifier_arborescence_XML($arbreXML);
		if($test_XML_valide!='ok')
		{
			exit($test_XML_valide);
		}
		DB_STRUCTURE_REFERENTIEL::DB_importer_arborescence_from_XML($arbreXML,$matiere_id,$niveau_id);
		$partage = ($perso==1) ? 'hs' : 'bof' ;
		DB_STRUCTURE_REFERENTIEL::DB_ajouter_referentiel($matiere_id,$niveau_id,$partage);
		exit('ok');
	}
	else
	{
		// C'est une demande de dupliquer le référentiel d'un autre établissement, mais rien n'est transmis (normalement impossible)
		exit('Erreur avec les données transmises !');
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// On en devrait pas en arriver là
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

exit('Erreur avec les données transmises !');

?>
