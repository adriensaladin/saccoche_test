<?php
/**
 * @version $Id: referentiel_global.ajax.php 8 2009-10-30 20:56:02Z thomas $
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
if(($_SESSION['STRUCTURE_ID']==ID_DEMO)&&($_GET['action']!='Voir')){exit('Action désactivée pour la démo...');}

$action  = (isset($_POST['action']))  ? $_POST['action'] : '';
$ids     = (isset($_POST['ids']))     ? $_POST['ids']    : '';

$partage = (isset($_POST['partage'])) ? clean_texte($_POST['partage'])  : '';	// Changer l'état de partage
$methode = (isset($_POST['methode'])) ? clean_entier($_POST['methode']) : -1;	// Changer le mode de calcul
$limite  = (isset($_POST['limite']))  ? clean_entier($_POST['limite'])  : -1;	// Changer le nb d'items pris en compte
$donneur = (isset($_POST['donneur'])) ? clean_entier($_POST['donneur']) : -1;	// Etablissement donneur d'un référentiel

if(mb_substr_count($ids,'_')!=3)
{
	exit('Erreur avec les données transmises !');
}

list($prefixe,$perso,$matiere_id,$niveau_id) = explode('_',$ids);
$perso      = clean_entier($perso);
$matiere_id = clean_entier($matiere_id);
$niveau_id  = clean_entier($niveau_id);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Affichage du détail d'un référentiel pour une matière et un niveau donnés (pour son bahut ou un bahut donneur)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
if( ($action=='Voir') && $matiere_id && $niveau_id )
{
	// La matière et le niveau donneurs peuvent ne pas correspondre avec la matière et le niveau de l'établissement.
	$donneur_matiere_id = (isset($_POST['matiere_id'])) ? $_POST['matiere_id'] : $matiere_id ;
	$donneur_niveau_id  = (isset($_POST['niveau_id']))  ? $_POST['niveau_id']  : $niveau_id  ;
	$structure_id = ($donneur==-1) ? $_SESSION['STRUCTURE_ID'] : $donneur ;
	$DB_TAB = DB_select_arborescence($structure_id,$prof_id=0,$donneur_matiere_id,$donneur_niveau_id,$socle_nom=true);
	echo afficher_arborescence($DB_TAB,$dynamique=false,$reference=false,$aff_coef='image',$aff_socle='image',$aff_lien='image',$aff_input=false);
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier le partage d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Partager') && ($perso==0) && $matiere_id && $niveau_id && in_array($partage,array('oui','non','bof')) )
{
	$DB_SQL = 'UPDATE livret_referentiel ';
	$DB_SQL.= 'SET livret_referentiel_partage=:partage ';
	$DB_SQL.= 'WHERE livret_matiere_id=:matiere_id AND livret_niveau_id=:niveau_id AND livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':structure_id'=>$_SESSION['STRUCTURE_ID'],':partage'=>$partage);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$tab_partage = array('oui'=>'<img title="Référentiel accessible aux autres établissements." alt="" src="./_img/partage1.gif" />','non'=>'<img title="Référentiel caché aux autres établissements." alt="" src="./_img/partage0.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/partage0.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/partage0.gif" />');
	echo $tab_partage[$partage];
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Modifier le mode de calcul d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Calculer') && $matiere_id && $niveau_id && in_array($methode,array(0,1)) && in_array($limite,array(0,1,2,3,4,5,6,7,8,9,10,15,20,30,40,50)) )
{
	$DB_SQL = 'UPDATE livret_referentiel ';
	$DB_SQL.= 'SET livret_referentiel_calcul_methode=:methode,livret_referentiel_calcul_limite=:limite ';
	$DB_SQL.= 'WHERE livret_matiere_id=:matiere_id AND livret_niveau_id=:niveau_id AND livret_structure_id=:structure_id ';
	$DB_SQL.= 'LIMIT 1';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':structure_id'=>$_SESSION['STRUCTURE_ID'],':methode'=>$methode,':limite'=>$limite);
	DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	$retour = ($methode) ? 'Coefficients progressifs' : 'Moyenne classique' ;
	if($limite==0)
	{
		$retour .= ' sur toutes les évaluations.';
	}
	elseif($limite==1)
	{
		$retour = 'Seule la dernière évaluation est prise en compte.';
	}
	else
	{
		$retour .= ' des '.$limite.' dernières évaluations.';
	}
	echo 'ok'.$retour;
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Supprimer un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Retirer') && $matiere_id && $niveau_id )
{
	DB_supprimer_referentiel_matiere_niveau($_SESSION['STRUCTURE_ID'],$matiere_id,$niveau_id);
	echo'ok';
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Ajouter un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Ajouter') && $matiere_id && $niveau_id )
{
	if( ($perso==1) || ($donneur==0) )
	{
		// C'est une matière spécifique à l'établissement, ou une demande de partir d'un référentiel vierge : on ne peut que créer un nouveau référentiel
		$partage = ($perso==1) ? 'hs' : 'non' ;
		$DB_SQL = 'INSERT INTO livret_referentiel ';
		$DB_SQL.= 'VALUES(:matiere_id,:niveau_id,:structure_id,:partage,:succes,:methode,:limite)';
		$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':structure_id'=>$_SESSION['STRUCTURE_ID'],':partage'=>$partage,':succes'=>0,':methode'=>$_SESSION['CALCUL_METHODE'],':limite'=>$_SESSION['CALCUL_LIMITE']);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		echo'ok';
	}
	elseif($donneur>0)
	{
		// La matière et le niveau donneurs peuvent ne pas correspondre avec la matière et le niveau de l'établissement.
		$donneur_matiere_id = (isset($_POST['matiere_id'])) ? $_POST['matiere_id'] : $matiere_id ;
		$donneur_niveau_id  = (isset($_POST['niveau_id']))  ? $_POST['niveau_id']  : $niveau_id  ;
		// C'est une matière partagée, et une demande de dupliquer le référentiel d'un autre établissement
		// On ajoute l'entrée dans la table des référentiels
		$DB_SQL = 'INSERT INTO livret_referentiel ';
		$DB_SQL.= 'VALUES(:matiere_id,:niveau_id,:structure_id,:partage,:succes,:methode,:limite)';
		$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':structure_id'=>$_SESSION['STRUCTURE_ID'],':partage'=>'bof',':succes'=>0,':methode'=>$_SESSION['CALCUL_METHODE'],':limite'=>$_SESSION['CALCUL_LIMITE']);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		// On récupère et recopie le contenu du référentiel
		$DB_TAB = DB_select_arborescence($donneur,$prof_id=0,$donneur_matiere_id,$donneur_niveau_id,$socle_nom=false);
		$domaine_id = 0;
		$theme_id = 0;
		$competence_id = 0;
		foreach($DB_TAB as $key => $DB_ROW)
		{
			if( (!is_null($DB_ROW['livret_domaine_id'])) && ($DB_ROW['livret_domaine_id']!=$domaine_id) )
			{
				// nouveau domaine
				$domaine_id = $DB_ROW['livret_domaine_id'];
				$competence_id = 0;
				$theme_id = 0;
				$DB_SQL = 'INSERT INTO livret_competence_domaine(livret_structure_id,livret_matiere_id,livret_niveau_id,livret_domaine_ref,livret_domaine_nom,livret_domaine_ordre) ';
				$DB_SQL.= 'VALUES(:structure_id,:matiere,:niveau,:ref,:nom,:ordre)';
				$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':matiere'=>$matiere_id,':niveau'=>$niveau_id,':ref'=>$DB_ROW['livret_domaine_ref'],':nom'=>$DB_ROW['livret_domaine_nom'],':ordre'=>$DB_ROW['livret_domaine_ordre']);
				DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
				$domaine_id_new = DB::getLastOid(SACOCHE_BD_NAME);
			}
			if( (!is_null($DB_ROW['livret_theme_id'])) && ($DB_ROW['livret_theme_id']!=$theme_id) )
			{
				// nouveau thème
				$theme_id = $DB_ROW['livret_theme_id'];
				$competence_id = 0;
				$DB_SQL = 'INSERT INTO livret_competence_theme(livret_structure_id,livret_domaine_id,livret_theme_nom,livret_theme_ordre) ';
				$DB_SQL.= 'VALUES(:structure_id,:domaine,:nom,:ordre)';
				$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':domaine'=>$domaine_id_new,':nom'=>$DB_ROW['livret_theme_nom'],':ordre'=>$DB_ROW['livret_theme_ordre']);
				DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
				$theme_id_new = DB::getLastOid(SACOCHE_BD_NAME);
			}
			if(!is_null($DB_ROW['livret_competence_id']))
			{
				// nouvel item
				$competence_id = $DB_ROW['livret_competence_id'];
				$DB_SQL = 'INSERT INTO livret_competence_item(livret_structure_id,livret_theme_id,livret_socle_id,livret_competence_nom,livret_competence_ordre,livret_competence_coef,livret_competence_lien) ';
				$DB_SQL.= 'VALUES(:structure_id,:theme,:socle,:nom,:ordre,:coef,:lien)';
				$DB_VAR = array(':structure_id'=>$_SESSION['STRUCTURE_ID'],':theme'=>$theme_id_new,':socle'=>$DB_ROW['livret_socle_id'],':nom'=>$DB_ROW['livret_competence_nom'],':ordre'=>$DB_ROW['livret_competence_ordre'],':coef'=>$DB_ROW['livret_competence_coef'],':lien'=>$DB_ROW['livret_competence_lien']);
				DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
		// On valorise le référentiel dupliqué
		$DB_SQL = 'UPDATE livret_referentiel ';
		$DB_SQL.= 'SET livret_referentiel_succes=livret_referentiel_succes+1 ';
		$DB_SQL.= 'WHERE livret_matiere_id=:matiere_id AND livret_niveau_id=:niveau_id AND livret_structure_id=:structure_id ';
		$DB_SQL.= 'LIMIT 1';
		$DB_VAR = array(':matiere_id'=>$donneur_matiere_id,':niveau_id'=>$donneur_niveau_id,':structure_id'=>$donneur);
		DB::query(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
		echo'ok';
	}
	elseif($donneur==-1)
	{
		// C'est une matière partagée, et une demande de dupliquer le référentiel d'un autre établissement, mais rien n'est transmis (normalement impossible)
		echo'Erreur avec les données transmises !';
	}
}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Lister les établissements partageant leur référentiel pour une matière et un niveau donné
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
elseif( ($action=='Lister') && $matiere_id && $niveau_id )
{
	// La matière et le niveau donneurs peuvent ne pas correspondre avec la matière et le niveau de l'établissement.
	$donneur_matiere_id = (isset($_POST['matiere_id'])) ? $_POST['matiere_id'] : $matiere_id ;
	$donneur_niveau_id  = (isset($_POST['niveau_id']))  ? $_POST['niveau_id']  : $niveau_id  ;
	$DB_SQL = 'SELECT * FROM livret_referentiel ';
	$DB_SQL.= 'LEFT JOIN livret_structure USING (livret_structure_id) ';
	$DB_SQL.= 'WHERE livret_structure_id!='.ID_DEMO.' AND livret_matiere_id=:matiere_id AND livret_niveau_id=:niveau_id AND livret_referentiel_partage=:partage ';
	$DB_SQL.= 'ORDER BY livret_referentiel_succes DESC, geo_continent_ordre ASC, geo_pays_nom ASC, geo_departement_numero ASC, geo_commune_nom ASC';
	$DB_VAR = array(':matiere_id'=>$donneur_matiere_id,':niveau_id'=>$donneur_niveau_id,':partage'=>'oui');
	$DB_TAB = DB::queryTab(SACOCHE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $key => $DB_ROW)
		{
			$texte = ($DB_ROW['geo_continent_ordre']>2) ? $DB_ROW['geo_pays_nom'].' | ' : $DB_ROW['geo_departement_numero'].' '.$DB_ROW['geo_departement_nom'].' | ';
			$texte.= $DB_ROW['geo_commune_nom'].' | ';
			$texte.= $DB_ROW['structure_type_ref'].' '.$DB_ROW['structure_nom'];
			echo'<li><input id="etabl_'.$DB_ROW['livret_structure_id'].'" name="donneur" type="radio" value="'.$DB_ROW['livret_structure_id'].'" /><label for="etabl_'.$DB_ROW['livret_structure_id'].'"> '.$texte.'</label> <img alt="star" src="./_img/star_bullet.png" /><sup>'.$DB_ROW['livret_referentiel_succes'].'</sup><q class="voir" title="Voir le détail de ce référentiel."></q></li>'."\r\n";
		}
	}
	else
	{
		echo'<li>Aucun référentiel partagé pour cette matière et ce niveau... Soyez le premier à créer et partager le votre !</li>'."\r\n";
	}
}

else
{
	echo'Erreur avec les données transmises !';
}
?>
