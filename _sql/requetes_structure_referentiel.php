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
 
// Extension de classe qui étend DB (pour permettre l'autoload)

// Ces méthodes ne concernent qu'une base STRUCTURE.
// Ces méthodes ne concernent que la gestion des référentiels par les profs coordonnateurs.

class DB_STRUCTURE_REFERENTIEL extends DB
{

/**
 * Tester la présence d'un référentiel
 *
 * @param int    $matiere_id
 * @param int    $niveau_id
 * @return int
 */
public function DB_tester_referentiel($matiere_id,$niveau_id)
{
	$DB_SQL = 'SELECT matiere_id ';
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id AND niveau_id=:niveau_id ';
	// LIMIT 1 inutile
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id);
	return DB::queryOne(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter un référentiel (juste la coquille, sans son contenu)
 *
 * @param int    $matiere_id
 * @param int    $niveau_id
 * @param string $partage_etat
 * @return void
 */
public function DB_ajouter_referentiel($matiere_id,$niveau_id,$partage_etat)
{
	$DB_SQL = 'INSERT INTO sacoche_referentiel ';
	$DB_SQL.= 'VALUES(:matiere_id,:niveau_id,:partage_etat,:partage_date,:calcul_methode,:calcul_limite,:mode_synthese)';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':partage_etat'=>$partage_etat,':partage_date'=>date("Y-m-d"),':calcul_methode'=>$_SESSION['CALCUL_METHODE'],':calcul_limite'=>$_SESSION['CALCUL_LIMITE'],':mode_synthese'=>'inconnu');
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Ajouter un domaine dans un référentiel (le numéro d'ordre des autres domaines impactés est modifié ailleurs)
 *
 * @param int    $matiere_id
 * @param int    $niveau_id
 * @param int    $domaine_ordre
 * @param string $domaine_ref
 * @param string $domaine_nom
 * @return int
 */
public function DB_ajouter_referentiel_domaine($matiere_id,$niveau_id,$domaine_ordre,$domaine_ref,$domaine_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_referentiel_domaine(matiere_id,niveau_id,domaine_ordre,domaine_ref,domaine_nom) ';
	$DB_SQL.= 'VALUES(:matiere_id,:niveau_id,:domaine_ordre,:domaine_ref,:domaine_nom)';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':domaine_ordre'=>$domaine_ordre,':domaine_ref'=>$domaine_ref,':domaine_nom'=>$domaine_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Ajouter un thème dans un référentiel (le numéro d'ordre des autres thèmes impactés est modifié ailleurs)
 *
 * @param int    $domaine_id
 * @param int    $theme_ordre
 * @param string $theme_nom
 * @return int
 */
public function DB_ajouter_referentiel_theme($domaine_id,$theme_ordre,$theme_nom)
{
	$DB_SQL = 'INSERT INTO sacoche_referentiel_theme(domaine_id,theme_ordre,theme_nom) ';
	$DB_SQL.= 'VALUES(:domaine_id,:theme_ordre,:theme_nom)';
	$DB_VAR = array(':domaine_id'=>$domaine_id,':theme_ordre'=>$theme_ordre,':theme_nom'=>$theme_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Ajouter un item dans un référentiel (le lien de ressources est géré ultérieurement ; le numéro d'ordre des autres items impactés est modifié ailleurs)
 *
 * @param int    $theme_id
 * @param int    $socle_id
 * @param int    $item_ordre
 * @param string $item_nom
 * @param int    $item_coef
 * @param int    $item_cart
 * @return int
 */
public function DB_ajouter_referentiel_item($theme_id,$socle_id,$item_ordre,$item_nom,$item_coef,$item_cart)
{
	$DB_SQL = 'INSERT INTO sacoche_referentiel_item(theme_id,entree_id,item_ordre,item_nom,item_coef,item_cart) ';
	$DB_SQL.= 'VALUES(:theme_id,:socle_id,:item_ordre,:item_nom,:item_coef,:item_cart)';
	$DB_VAR = array(':theme_id'=>$theme_id,':socle_id'=>$socle_id,':item_ordre'=>$item_ordre,':item_nom'=>$item_nom,':item_coef'=>$item_coef,':item_cart'=>$item_cart);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Déplacer un domaine d'un référentiel (le numéro d'ordre des autres domaines impactés est modifié ailleurs)
 *
 * @param int    $domaine_id
 * @param int    $niveau_id
 * @param int    $domaine_ordre
 * @return int   test si déplacement effectué (0|1)
 */
public function DB_deplacer_referentiel_domaine($domaine_id,$niveau_id,$domaine_ordre)
{
	$DB_SQL = 'UPDATE sacoche_referentiel_domaine ';
	$DB_SQL.= 'SET niveau_id=:niveau_id, domaine_ordre=:domaine_ordre ';
	$DB_SQL.= 'WHERE domaine_id=:domaine_id ';
	$DB_VAR = array(':domaine_id'=>$domaine_id,':niveau_id'=>$niveau_id,':domaine_ordre'=>$domaine_ordre);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Déplacer un thème d'un référentiel (le numéro d'ordre des autres thèmes impactés est modifié ailleurs)
 *
 * @param int    $theme_id
 * @param int    $domaine_id
 * @param int    $theme_ordre
 * @return int   test si déplacement effectué (0|1)
 */
public function DB_deplacer_referentiel_theme($theme_id,$domaine_id,$theme_ordre)
{
	$DB_SQL = 'UPDATE sacoche_referentiel_theme ';
	$DB_SQL.= 'SET domaine_id=:domaine_id, theme_ordre=:theme_ordre ';
	$DB_SQL.= 'WHERE theme_id=:theme_id ';
	$DB_VAR = array(':theme_id'=>$theme_id,':domaine_id'=>$domaine_id,':theme_ordre'=>$theme_ordre);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Déplacer un item d'un référentiel (le numéro d'ordre des autres items impactés est modifié ailleurs)
 *
 * @param int    $item_id
 * @param int    $theme_id
 * @param int    $item_ordre
 * @return int   test si déplacement effectué (0|1)
 */
public function DB_deplacer_referentiel_item($item_id,$theme_id,$item_ordre)
{
	$DB_SQL = 'UPDATE sacoche_referentiel_item ';
	$DB_SQL.= 'SET theme_id=:theme_id, item_ordre=:item_ordre ';
	$DB_SQL.= 'WHERE item_id=:item_id ';
	$DB_VAR = array(':item_id'=>$item_id,':theme_id'=>$theme_id,':item_ordre'=>$item_ordre);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Importer dans la base l'arborescence d'un référentiel à partir d'un XML récupéré sur le serveur communautaire
 *
 * Remarque : les ordres des domaines / thèmes / items ne sont pas dans le XML car il sont générés par leur position dans l'arborescence.
 *
 * @param string $arbreXML
 * @param int    $matiere_id
 * @param int    $niveau_id
 * @return void
 */
public function DB_importer_arborescence_from_XML($arbreXML,$matiere_id,$niveau_id)
{
	// décortiquer l'arbre XML
	$xml = new DOMDocument;
	$xml -> loadXML($arbreXML);
	// On passe en revue les domaines...
	$domaine_liste = $xml -> getElementsByTagName('domaine');
	$domaine_nb = $domaine_liste -> length;
	for($domaine_ordre=0; $domaine_ordre<$domaine_nb; $domaine_ordre++)
	{
		$domaine_xml = $domaine_liste -> item($domaine_ordre);
		$domaine_ref = $domaine_xml -> getAttribute('ref');
		$domaine_nom = $domaine_xml -> getAttribute('nom');
		$DB_SQL = 'INSERT INTO sacoche_referentiel_domaine(matiere_id,niveau_id,domaine_ordre,domaine_ref,domaine_nom) ';
		$DB_SQL.= 'VALUES(:matiere_id,:niveau_id,:ordre,:ref,:nom)';
		$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id,':ordre'=>$domaine_ordre+1,':ref'=>$domaine_ref,':nom'=>$domaine_nom);
		DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		$domaine_id = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
		// On passe en revue les thèmes du domaine...
		$theme_liste = $domaine_xml -> getElementsByTagName('theme');
		$theme_nb = $theme_liste -> length;
		for($theme_ordre=0; $theme_ordre<$theme_nb; $theme_ordre++)
		{
			$theme_xml = $theme_liste -> item($theme_ordre);
			$theme_nom = $theme_xml -> getAttribute('nom');
			$DB_SQL = 'INSERT INTO sacoche_referentiel_theme(domaine_id,theme_ordre,theme_nom) ';
			$DB_SQL.= 'VALUES(:domaine_id,:ordre,:nom)';
			$DB_VAR = array(':domaine_id'=>$domaine_id,':ordre'=>$theme_ordre+1,':nom'=>$theme_nom);
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			$theme_id = DB::getLastOid(SACOCHE_STRUCTURE_BD_NAME);
			// On passe en revue les items du thème...
			$item_liste = $theme_xml -> getElementsByTagName('item');
			$item_nb = $item_liste -> length;
			for($item_ordre=0; $item_ordre<$item_nb; $item_ordre++)
			{
				$item_xml   = $item_liste -> item($item_ordre);
				$item_socle = $item_xml -> getAttribute('socle');
				$item_nom   = $item_xml -> getAttribute('nom');
				$item_coef  = $item_xml -> getAttribute('coef');
				$item_cart  = $item_xml -> getAttribute('cart');
				$item_lien  = $item_xml -> getAttribute('lien');
				$DB_SQL = 'INSERT INTO sacoche_referentiel_item(theme_id,entree_id,item_ordre,item_nom,item_coef,item_cart,item_lien) ';
				$DB_SQL.= 'VALUES(:theme,:socle,:ordre,:nom,:coef,:cart,:lien)';
				$DB_VAR = array(':theme'=>$theme_id,':socle'=>$item_socle,':ordre'=>$item_ordre,':nom'=>$item_nom,':coef'=>$item_coef,':cart'=>$item_cart,':lien'=>$item_lien);
				DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
			}
		}
	}
}

/**
 * Modifier les caractéristiques d'un référentiel (juste ses paramètres, pas le contenu de son arborescence)
 *
 * @param int     $matiere_id
 * @param int     $niveau_id
 * @param array   array(':partage_etat'=>$val, ':partage_date'=>$val , ':calcul_methode'=>$val , ':calcul_limite'=>$val , ':mode_synthese'=>$val );
 * @return void
 */
public function DB_modifier_referentiel($matiere_id,$niveau_id,$DB_VAR)
{
	$tab_set = array();
	foreach($DB_VAR as $key => $val)
	{
		switch($key)
		{
			case ':partage_etat'   : $tab_set[] = 'referentiel_partage_etat='.$key;   break;
			case ':partage_date'   : $tab_set[] = 'referentiel_partage_date='.$key;   break;
			case ':calcul_methode' : $tab_set[] = 'referentiel_calcul_methode='.$key; break;
			case ':calcul_limite'  : $tab_set[] = 'referentiel_calcul_limite='.$key;  break;
			case ':mode_synthese'  : $tab_set[] = 'referentiel_mode_synthese='.$key;  break;
		}
	}
	$DB_SQL = 'UPDATE sacoche_referentiel ';
	$DB_SQL.= 'SET '.implode(', ',$tab_set).' ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id AND niveau_id=:niveau_id ';
	$DB_VAR[':matiere_id'] = $matiere_id;
	$DB_VAR[':niveau_id'] = $niveau_id;
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier les caractéristiques d'un domaine d'un référentiel
 *
 * @param int     $domaine_id
 * @param string  $domaine_ref
 * @param string  $domaine_nom
 * @return int   nb de lignes modifiées (0|1)
 */
public function DB_modifier_referentiel_domaine($domaine_id,$domaine_ref,$domaine_nom)
{
	$DB_SQL = 'UPDATE sacoche_referentiel_domaine ';
	$DB_SQL.= 'SET domaine_ref=:domaine_ref, domaine_nom=:domaine_nom ';
	$DB_SQL.= 'WHERE domaine_id=:domaine_id ';
	$DB_VAR = array(':domaine_id'=>$domaine_id,':domaine_ref'=>$domaine_ref,':domaine_nom'=>$domaine_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Modifier les caractéristiques d'un thème d'un référentiel
 *
 * @param int     $theme_id
 * @param string  $theme_ref
 * @param string  $theme_nom
 * @return int   nb de lignes modifiées (0|1)
 */
public function DB_modifier_referentiel_theme($theme_id,$theme_nom)
{
	$DB_SQL = 'UPDATE sacoche_referentiel_theme ';
	$DB_SQL.= 'SET theme_nom=:theme_nom ';
	$DB_SQL.= 'WHERE theme_id=:theme_id ';
	$DB_VAR = array(':theme_id'=>$theme_id,':theme_nom'=>$theme_nom);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Modifier les caractéristiques d'un item d'un référentiel (hors déplacement ;le lien de ressources est modifié ailleurs)
 *
 * @param int     $item_id
 * @param int     $socle_id
 * @param string  $item_nom
 * @param int     $item_coef
 * @param int     $item_cart
 * @return int   nb de lignes modifiées (0|1)
 */
public function DB_modifier_referentiel_item($item_id,$socle_id,$item_nom,$item_coef,$item_cart)
{
	$DB_SQL = 'UPDATE sacoche_referentiel_item ';
	$DB_SQL.= 'SET entree_id=:socle_id, item_nom=:item_nom, item_coef=:item_coef, item_cart=:item_cart ';
	$DB_SQL.= 'WHERE item_id=:item_id ';
	$DB_VAR = array(':item_id'=>$item_id,':socle_id'=>$socle_id,':item_nom'=>$item_nom,':item_coef'=>$item_coef,':item_cart'=>$item_cart);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);
}

/**
 * Modifier le lien de vers des ressources d'un item d'un référentiel
 *
 * @param int     $item_id
 * @param string  $item_lien
 * @return void
 */
public function DB_modifier_referentiel_lien_ressources($item_id,$item_lien)
{
	$DB_SQL = 'UPDATE sacoche_referentiel_item ';
	$DB_SQL.= 'SET item_lien=:item_lien ';
	$DB_SQL.= 'WHERE item_id=:item_id ';
	$DB_VAR = array(':item_id'=>$item_id,':item_lien'=>$item_lien);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Modifier le nb de demandes d'évaluations autorisées pour les référentiels d'une matière donnée
 *
 * @param int   $matiere_id
 * @param int   $matiere_nb_demandes
 * @return void
 */
public function DB_modifier_matiere_nb_demandes($matiere_id,$matiere_nb_demandes)
{
	$DB_SQL = 'UPDATE sacoche_matiere ';
	$DB_SQL.= 'SET matiere_nb_demandes=:matiere_nb_demandes ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':matiere_nb_demandes'=>$matiere_nb_demandes);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer un référentiel (avec son contenu et ce qui en dépend)
 *
 * @param int $matiere_id
 * @param int $niveau_id
 * @return void
 */
public function DB_supprimer_referentiel_matiere_niveau($matiere_id,$niveau_id)
{
	$DB_SQL = 'DELETE sacoche_referentiel, sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie ';
	$DB_SQL.= 'FROM sacoche_referentiel ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_domaine USING (matiere_id,niveau_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (matiere_id,item_id) ';
	$DB_SQL.= 'WHERE matiere_id=:matiere_id AND niveau_id=:niveau_id ';
	$DB_VAR = array(':matiere_id'=>$matiere_id,':niveau_id'=>$niveau_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Supprimer un domaine d'un référentiel (avec son contenu et ce qui en dépend)
 *
 * @param int    $domaine_id
 * @return int
 */
public function DB_supprimer_referentiel_domaine($domaine_id)
{
	$DB_SQL = 'DELETE sacoche_referentiel_domaine, sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
	$DB_SQL.= 'FROM sacoche_referentiel_domaine ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_theme USING (domaine_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
	$DB_SQL.= 'WHERE domaine_id=:domaine_id';
	$DB_VAR = array(':domaine_id'=>$domaine_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);	// Est censé renvoyé le nb de lignes supprimées ; à cause du multi-tables curieusement ça renvoie 2, même pour un élément non lié
}

/**
 * Supprimer un thème d'un référentiel (avec son contenu et ce qui en dépend)
 *
 * @param int    $theme_id
 * @return int
 */
public function DB_supprimer_referentiel_theme($theme_id)
{
	$DB_SQL = 'DELETE sacoche_referentiel_theme, sacoche_referentiel_item, sacoche_jointure_devoir_item, sacoche_saisie, sacoche_demande ';
	$DB_SQL.= 'FROM sacoche_referentiel_theme ';
	$DB_SQL.= 'LEFT JOIN sacoche_referentiel_item USING (theme_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_saisie USING (item_id) ';
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
	$DB_SQL.= 'WHERE theme_id=:theme_id';
	$DB_VAR = array(':theme_id'=>$theme_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);	// Est censé renvoyé le nb de lignes supprimées ; à cause du multi-tables curieusement ça renvoie 2, même pour un élément non lié
}

/**
 * Supprimer un item et les demandes d'évaluations associées
 *
 * On ne supprime aussi les jointures aux devoirs ni les saisies que s'il ne s'agit pas d'une fusion.
 *
 * @param int    $item_id
 * @param bool   $with_notes   TRUE par défaut, FALSE dans le cas d'une fusion d'items (étudié ensuite par une autre fonction)
 * @return int
 */
public function DB_supprimer_referentiel_item($item_id,$with_notes=TRUE)
{
	// Supprimer l'item à fusionner et les demandes d'évaluations associées
	$DB_SQL = 'DELETE sacoche_referentiel_item, sacoche_demande';
	// Dans le cas d'une fusion, PAS ENCORE les jointures aux devoirs ni les saisies
	$DB_SQL.= ($with_notes) ? ', sacoche_jointure_devoir_item, sacoche_saisie ' : ' ' ;
	$DB_SQL.= 'FROM sacoche_referentiel_item ';
	$DB_SQL.= ($with_notes) ? 'LEFT JOIN sacoche_jointure_devoir_item USING (item_id) ' : '' ;
	$DB_SQL.= ($with_notes) ? 'LEFT JOIN sacoche_saisie USING (item_id) ' : '' ;
	$DB_SQL.= 'LEFT JOIN sacoche_demande USING (item_id) ';
	$DB_SQL.= 'WHERE item_id=:item_id';
	$DB_VAR = array(':item_id'=>$item_id);
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	return DB::rowCount(SACOCHE_STRUCTURE_BD_NAME);	// Est censé renvoyé le nb de lignes supprimées ; à cause du multi-tables curieusement ça renvoie 2, même pour un élément non lié
}

/**
 * Fusionner deux items
 *
 * L'item à fusionner a déjà été supprimé, et la partie concernée du référentiel a déjà été renumérotée.
 * Il reste à étudier les jointures aux devoirs et les saisies.
 *
 * @param int    $item_id_degageant
 * @param int    $item_id_absorbant
 * @return void
 */
public function DB_fusionner_referentiel_items($item_id_degageant,$item_id_absorbant)
{
	$DB_VAR = array(':item_id_degageant'=>$item_id_degageant,':item_id_absorbant'=>$item_id_absorbant);
	// Dans le cas où les deux items ont été évalués dans une même évaluation, on est obligé de supprimer l'un des scores
	// On doit donc commencer par chercher les conflits possibles de clefs multiples pour éviter un erreur lors de l'UPDATE
	//
	// TABLE sacoche_jointure_devoir_item
	//
	$DB_SQL = 'SELECT devoir_id ';
	$DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
	$DB_SQL.= 'WHERE item_id=:item_id_degageant';
	$COL1 = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	$DB_SQL = 'SELECT devoir_id ';
	$DB_SQL.= 'FROM sacoche_jointure_devoir_item ';
	$DB_SQL.= 'WHERE item_id=:item_id_absorbant';
	$COL2 = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	$tab_conflit = array_intersect($COL1,$COL2);
	if(count($tab_conflit))
	{
		$DB_SQL = 'DELETE FROM sacoche_jointure_devoir_item ';
		$DB_SQL.= 'WHERE devoir_id=:devoir_id AND item_id=:item_id_degageant ';
		foreach($tab_conflit as $devoir_id)
		{
			$DB_VAR[':devoir_id'] = $devoir_id;
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	$DB_SQL = 'UPDATE sacoche_jointure_devoir_item ';
	$DB_SQL.= 'SET item_id=:item_id_absorbant ';
	$DB_SQL.= 'WHERE item_id=:item_id_degageant';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	//
	// TABLE sacoche_saisie
	//
	$DB_SQL = 'SELECT CONCAT(eleve_id,"x",devoir_id) AS clefs ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE item_id=:item_id_degageant';
	$COL1 = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	$DB_SQL = 'SELECT CONCAT(eleve_id,"x",devoir_id) AS clefs ';
	$DB_SQL.= 'FROM sacoche_saisie ';
	$DB_SQL.= 'WHERE item_id=:item_id_absorbant';
	$COL2 = DB::queryCol(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	$tab_conflit = array_intersect($COL1,$COL2);
	if(count($tab_conflit))
	{
		$DB_SQL = 'DELETE FROM sacoche_saisie ';
		$DB_SQL.= 'WHERE eleve_id=:eleve_id AND devoir_id=:devoir_id AND item_id=:item_id_degageant ';
		foreach($tab_conflit as $ids)
		{
			list($eleve_id,$devoir_id) = explode('x',$ids);
			$DB_VAR[':eleve_id']  = $eleve_id;
			$DB_VAR[':devoir_id'] = $devoir_id;
			DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
		}
	}
	$DB_SQL = 'UPDATE sacoche_saisie ';
	$DB_SQL.= 'SET item_id=:item_id_absorbant ';
	$DB_SQL.= 'WHERE item_id=:item_id_degageant';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
}

/**
 * Incrémenter ou décrémenter dans un référentiel le numéro d'ordre d'une liste d'éléments précis
 *
 * @param string   $element_champ 'domaine' | 'theme' | 'item'
 * @param array    $tab_elements_id
 * @param string   $operation     '+1' | '-1' 
 * @return void
 */
public function DB_renumeroter_referentiel_elements($element_champ,$tab_elements_id,$operation)
{
	$listing_elements_id = implode(',',$tab_elements_id);
	$DB_SQL = 'UPDATE sacoche_referentiel_'.$element_champ.' ';
	$DB_SQL.= 'SET '.$element_champ.'_ordre='.$element_champ.'_ordre'.$operation.' ';
	$DB_SQL.= 'WHERE '.$element_champ.'_id IN('.$listing_elements_id.') ';
	DB::query(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , NULL);
}

}
?>