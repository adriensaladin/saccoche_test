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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_GET['action']!='Voir')){exit('Action désactivée pour la démo...');}

$action  = (isset($_POST['action']))  ? $_POST['action'] : '';
$ids     = (isset($_POST['ids']))     ? $_POST['ids']    : '';

$partage        = (isset($_POST['partage']))        ? clean_texte($_POST['partage'])         : '';	// Changer l'état de partage
$methode        = (isset($_POST['methode']))        ? clean_texte($_POST['methode'])         : '';	// Changer le mode de calcul
$limite         = (isset($_POST['limite']))         ? clean_entier($_POST['limite'])         : -1;	// Changer le nb d'items pris en compte
$referentiel_id = (isset($_POST['referentiel_id'])) ? clean_entier($_POST['referentiel_id']) : -1;	// Référence du référentiel importé (0 si vierge)

if(mb_substr_count($ids,'_')!=3)
{
	exit('Erreur avec les données transmises !');
}

list($prefixe,$perso,$matiere_id,$niveau_id) = explode('_',$ids);
$perso      = clean_entier($perso);
$matiere_id = clean_entier($matiere_id);
$niveau_id  = clean_entier($niveau_id);

$tab_partages = array('oui','non','bof','hs');
$tab_methodes = array('geometrique','arithmetique','classique');
$tab_limites['geometrique']  = array(1,2,3,4,5);
$tab_limites['arithmetique'] = array(1,2,3,4,5,6,7,8,9);
$tab_limites['classique']  = array(0,1,2,3,4,5,6,7,8,9,10,15,20,30,40,50);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du détail d'un référentiel pour une matière et un niveau donnés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Voir') && $matiere_id && $niveau_id )
{
	$DB_TAB = DB_recuperer_arborescence($prof_id=0,$matiere_id,$niveau_id,$only_item=false,$socle_nom=true);
	echo afficher_arborescence($DB_TAB,$dynamique=false,$reference=false,$aff_coef='image',$aff_socle='image',$aff_lien='image',$aff_input=false);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier le partage d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Partager') && ($perso==0) && $matiere_id && $niveau_id && in_array($partage,$tab_partages) && ($partage!='hs') )
{
	if( ($partage=='oui') && ( (!$_SESSION['STRUCTURE_ID']) || (!$_SESSION['STRUCTURE_KEY']) ) )
	{
		exit('Pour pouvoir échanger avec le serveur communautaire, un administrateur doit déclarer cette installation de SACoche.');
	}
	// Envoyer le référentiel (éventuellement vide pour l'effacer) vers le serveur de partage
	if($partage=='oui')
	{
		$DB_TAB = DB_recuperer_arborescence(0,$matiere_id,$niveau_id,$only_item=FALSE,$socle_nom=FALSE);
		$arbreXML = exporter_arborescence_to_XML($DB_TAB);
		$reponse = envoyer_arborescence_XML($_SESSION['STRUCTURE_ID'],$_SESSION['STRUCTURE_KEY'],$matiere_id,$niveau_id,$arbreXML);
	}
	else
	{
		$reponse = envoyer_arborescence_XML($_SESSION['STRUCTURE_ID'],$_SESSION['STRUCTURE_KEY'],$matiere_id,$niveau_id,'');
	}
	// Analyse de la réponse retournée par le serveur de partage
	if($reponse!='ok')
	{
		exit($reponse);
	}
	// Tout s'est bien passé si on arrive jusque là...
	$date_mysql = date("Y-m-d");
	$DB_SQL = 'UPDATE sacoche_referentiel ';
	$DB_SQL.= 'SET referentiel_partage_etat=:etat, referentiel_partage_date=:date ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id AND niveau_id=:niveau_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':etat'=>$partage,':date'=>$date_mysql);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Retour envoyé
	$tab_partage = array('oui'=>'<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/partage1.gif" />','non'=>'<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/partage0.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/partage0.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/partage0.gif" />');
	exit( str_replace('◄DATE►',affich_date($date_mysql),$tab_partage[$partage]) );
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Mettre à jour sur le serveur de partage la dernière version d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Envoyer') && ($perso==0) && $matiere_id && $niveau_id )
{
	if( (!$_SESSION['STRUCTURE_ID']) || (!$_SESSION['STRUCTURE_KEY']) )
	{
		exit('Pour pouvoir échanger avec le serveur communautaire, un administrateur doit déclarer cette installation de SACoche.');
	}
	// Envoyer le référentiel vers le serveur de partage
	$DB_TAB = DB_recuperer_arborescence(0,$matiere_id,$niveau_id,$only_item=FALSE,$socle_nom=FALSE);
	$arbreXML = exporter_arborescence_to_XML($DB_TAB);
	$reponse = envoyer_arborescence_XML($_SESSION['STRUCTURE_ID'],$_SESSION['STRUCTURE_KEY'],$matiere_id,$niveau_id,$arbreXML);
	// Analyse de la réponse retournée par le serveur de partage
	if($reponse!='ok')
	{
		exit($reponse);
	}
	// Tout s'est bien passé si on arrive jusque là...
	$date_mysql = date("Y-m-d");
	$DB_SQL = 'UPDATE sacoche_referentiel ';
	$DB_SQL.= 'SET referentiel_partage_date=:date ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id AND niveau_id=:niveau_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':date'=>$date_mysql);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	// Retour envoyé
	exit('<img title="Référentiel partagé sur le serveur communautaire (MAJ le '.affich_date(date("Y-m-d")).')." alt="" src="./_img/partage1.gif" />');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Supprimer un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Retirer') && $matiere_id && $niveau_id && in_array($partage,$tab_partages) )
{
	// S'il était partagé, il faut le retirer du serveur communautaire
	if($partage=='oui')
	{
		if( (!$_SESSION['STRUCTURE_ID']) || (!$_SESSION['STRUCTURE_KEY']) )
		{
			exit('Pour pouvoir échanger avec le serveur communautaire, un administrateur doit déclarer cette installation de SACoche.');
		}
		$reponse = envoyer_arborescence_XML($_SESSION['STRUCTURE_ID'],$_SESSION['STRUCTURE_KEY'],$matiere_id,$niveau_id,'');
		if($reponse!='ok')
		{
			exit($reponse);
		}
	}
	DB_supprimer_referentiel_matiere_niveau($matiere_id,$niveau_id);
	exit('ok');
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier le mode de calcul d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Calculer') && $matiere_id && $niveau_id && in_array($methode,$tab_methodes) && in_array($limite,$tab_limites[$methode]) )
{
	$DB_SQL = 'UPDATE sacoche_referentiel ';
	$DB_SQL.= 'SET referentiel_calcul_methode=:methode,referentiel_calcul_limite=:limite ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id AND niveau_id=:niveau_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':methode'=>$methode,':limite'=>$limite);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if($limite==1)
	{
		$retour = 'Seule la dernière saisie compte.';
	}
	elseif($methode=='classique')
	{
		$retour = ($limite==0) ? 'Moyenne de toutes les saisies.' : 'Moyenne des '.$limite.' dernières saisies.';
	}
	else
	{
		$chaine = '1/2/3/4/5/6/7/8/9.1/2/4/8/16';
		$debut = ($methode=='geometrique') ? 18 : 0 ;
		$long  = 2*($limite-1);
		$long += (($methode=='geometrique')&&($limite==5)) ? 2 : 1 ;
		$retour = 'Les '.$limite.' dernières saisies &times;'.substr($chaine,$debut,$long).'.';
	}
	exit('ok'.$retour);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ajouter un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Ajouter') && $matiere_id && $niveau_id )
{
	if( ($perso==1) || ($referentiel_id==0) )
	{
		// C'est une matière spécifique à l'établissement, ou une demande de partir d'un référentiel vierge : on ne peut que créer un nouveau référentiel
		$partage = ($perso==1) ? 'hs' : 'non' ;
		$date_mysql = date("Y-m-d");
		$DB_SQL = 'INSERT INTO sacoche_referentiel ';
		$DB_SQL.= 'VALUES(:matiere_id,:niveau_id,:etat,:date,:methode,:limite)';
		$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':etat'=>$partage,':date'=>$date_mysql,':methode'=>$_SESSION['CALCUL_METHODE'],':limite'=>$_SESSION['CALCUL_LIMITE']);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		exit('ok');
	}
	elseif($referentiel_id>0)
	{
		// C'est une matière partagée, et une demande de récupérer un référentiel provenant du serveur communautaire pour se le dupliquer
		if( (!$_SESSION['STRUCTURE_ID']) || (!$_SESSION['STRUCTURE_KEY']) )
		{
			exit('Pour pouvoir échanger avec le serveur communautaire, un administrateur doit déclarer cette installation de SACoche.');
		}
		// Récupérer le référentiel
		$arbreXML = recuperer_arborescence_XML($_SESSION['STRUCTURE_ID'],$_SESSION['STRUCTURE_KEY'],$referentiel_id);
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
		DB_importer_arborescence_from_XML($arbreXML,$matiere_id,$niveau_id);

		// XML -> BDD
/*
		// On ajoute l'entrée dans la table des référentiels
		$DB_SQL = 'INSERT INTO sacoche_referentiel ';
		$DB_SQL.= 'VALUES(:matiere_id,:niveau_id,:etat,:date,:methode,:limite)';
		$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':etat'=>'bof',':date'=>$date_mysql,':methode'=>$_SESSION['CALCUL_METHODE'],':limite'=>$_SESSION['CALCUL_LIMITE']);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		// On récupère et recopie le contenu du référentiel
		
		// ***** $DB_TAB = DB_recuperer_arborescence($donneur,$prof_id=0,$donneur_matiere_id,$donneur_niveau_id,$only_item=false,$socle_nom=false);
		$domaine_id = 0;
		$theme_id = 0;
		$competence_id = 0;
		foreach($DB_TAB as $DB_ROW)
		{
			if( (!is_null($DB_ROW['domaine_id'])) && ($DB_ROW['domaine_id']!=$domaine_id) )
			{
				// nouveau domaine
				$domaine_id = $DB_ROW['domaine_id'];
				$competence_id = 0;
				$theme_id = 0;
				$DB_SQL = 'INSERT INTO sacoche_referentiel_domaine(matiere_id,niveau_id,domaine_ordre,domaine_ref,domaine_nom) ';
				$DB_SQL.= 'VALUES(:matiere,:niveau,:ordre,:ref,:nom)';
				$DB_VAR = array(':matiere'=>$matiere_id,':niveau'=>$niveau_id,':ordre'=>$DB_ROW['domaine_ordre'],':ref'=>$DB_ROW['domaine_ref'],':nom'=>$DB_ROW['domaine_nom']);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
				$domaine_id_new = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
			}
			if( (!is_null($DB_ROW['theme_id'])) && ($DB_ROW['theme_id']!=$theme_id) )
			{
				// nouveau thème
				$theme_id = $DB_ROW['theme_id'];
				$competence_id = 0;
				$DB_SQL = 'INSERT INTO sacoche_referentiel_theme(domaine_id,theme_ordre,theme_nom) ';
				$DB_SQL.= 'VALUES(:domaine,:ordre,:nom)';
				$DB_VAR = array(':domaine'=>$domaine_id_new,':ordre'=>$DB_ROW['theme_ordre'],':nom'=>$DB_ROW['theme_nom']);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
				$theme_id_new = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
			}
			if(!is_null($DB_ROW['item_id']))
			{
				// nouvel item
				$competence_id = $DB_ROW['item_id'];
				$DB_SQL = 'INSERT INTO sacoche_referentiel_item(theme_id,entree_id,item_ordre,item_nom,item_coef,item_lien) ';
				$DB_SQL.= 'VALUES(:theme,:entree,:ordre,:nom,:coef,:lien)';
				$DB_VAR = array(':theme'=>$theme_id_new,':entree'=>$DB_ROW['entree_id'],':ordre'=>$DB_ROW['item_ordre'],':nom'=>$DB_ROW['item_nom'],':coef'=>$DB_ROW['item_coef'],':lien'=>$DB_ROW['item_lien']);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
		echo'ok';
*/
	}
	elseif($referentiel_id==-1)
	{
		// C'est une matière partagée, et une demande de dupliquer le référentiel d'un autre établissement, mais rien n'est transmis (normalement impossible)
		exit('Erreur avec les données transmises !');
	}
}

else
{
	exit('Erreur avec les données transmises !');
}
?>
