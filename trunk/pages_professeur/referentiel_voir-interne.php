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
$TITRE = "Référentiels en place dans l'établissement";
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=referentiel_organisation_competences">DOC : Organisation des compétences dans les référentiels.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=referentiel_structure">DOC : Structure d'un référentiel.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="./aide.php?fichier=calcul_scores_etats_acquisitions">DOC : Calcul des scores et des états d'acquisitions.</a></span></li>
</ul>

<form action="">

<?php
// J'ai séparé en plusieurs requêtes au bout de plusieurs heures sans m'en sortir (entre les matières sans coordonnateurs, sans référentiel, les deux à la fois...).
// La recherche ne s'effectue que sur les matières et niveaux utilisés, sans débusquer des référentiels résiduels.
$tab_matiere = array();
$tab_niveau  = array();
$tab_colonne = array();

// On récupère la liste des matières utilisées par l'établissement
$DB_TAB = DB_lister_matieres_etablissement($_SESSION['MATIERES'],$with_transversal=true);
if(count($DB_TAB))
{
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_matiere[$DB_ROW['matiere_id']]['nom'] = html($DB_ROW['matiere_nom']);
	}
}
$liste_matieres = implode(',',array_keys($tab_matiere));

if(!$liste_matieres)
{
	echo'<p><span class="danger">Aucune matière enregistrée ou associée à l\'établissement !</span></p>';
}
elseif(!$_SESSION['NIVEAUX'])
{
	echo'<p><span class="danger">Aucun niveau n\'est rattaché à l\'établissement !</span></p>';
}
else
{
	echo'<p><span class="astuce">Cliquer sur l\'&oelig;il pour voir le détail d\'un référentiel.</span></p>';
	// On récupère la liste des niveaux utilisés par l'établissement
	$DB_TAB = DB_lister_niveaux_etablissement($_SESSION['NIVEAUX'],$_SESSION['PALIERS']);
	$nb_niveaux = count($DB_TAB);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_niveau[$DB_ROW['niveau_id']] = html($DB_ROW['niveau_nom']);
	}
/*
	// On récupère la liste des coordonnateurs responsables par matières
	$DB_SQL = 'SELECT matiere_id, GROUP_CONCAT(CONCAT(user_nom," ",user_prenom) SEPARATOR ";") AS coord_liste FROM sacoche_jointure_user_matiere ';
	$DB_SQL.= 'LEFT JOIN sacoche_user USING (user_id) ';
	$DB_SQL.= 'WHERE matiere_id IN('.$liste_matieres.') AND jointure_coord=:coord AND user_statut=:statut ';
	$DB_SQL.= 'GROUP BY matiere_id';
	$DB_VAR = array(':coord'=>1,':statut'=>1);
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , $DB_VAR);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_matiere[$DB_ROW['matiere_id']]['coord'] = str_replace(';','<br />',html($DB_ROW['coord_liste']));
		}
	}
*/
	// On récupère la liste des référentiels par matière et niveau
	$tab_partage = array('oui'=>'<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/partage1.gif" />','non'=>'<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/partage0.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/partage0.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/partage0.gif" />');
	$DB_SQL = 'SELECT matiere_id,niveau_id,niveau_nom,referentiel_partage_etat,referentiel_partage_date,referentiel_calcul_methode,referentiel_calcul_limite FROM sacoche_referentiel ';
	$DB_SQL.= 'LEFT JOIN sacoche_niveau USING (niveau_id) ';
	$DB_SQL.= 'WHERE matiere_id IN('.$liste_matieres.') AND ( niveau_id IN('.$_SESSION['NIVEAUX'].') OR palier_id IN('.$_SESSION['PALIERS'].') ) ';
	$DB_SQL.= 'ORDER BY matiere_id ASC, niveau_ordre ASC';
	$DB_TAB = DB::queryTab(SACOCHE_STRUCTURE_BD_NAME , $DB_SQL , null);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			if($DB_ROW['referentiel_calcul_limite']==1)
			{
				$methode_calcul_texte = 'Seule la dernière saisie compte.';
			}
			elseif($DB_ROW['referentiel_calcul_methode']=='classique')
			{
				$methode_calcul_texte = ($DB_ROW['referentiel_calcul_limite']==0) ? 'Moyenne de toutes les saisies.' : 'Moyenne des '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies.';
			}
			else
			{
				$chaine = '1/2/3/4/5/6/7/8/9.1/2/4/8/16';
				$debut = ($DB_ROW['referentiel_calcul_methode']=='geometrique') ? 18 : 0 ;
				$long  = 2*($DB_ROW['referentiel_calcul_limite']-1);
				$long += (($DB_ROW['referentiel_calcul_methode']=='geometrique')&&($DB_ROW['referentiel_calcul_limite']==5)) ? 2 : 1 ;
				$methode_calcul_texte = 'Les '.$DB_ROW['referentiel_calcul_limite'].' dernières saisies &times;'.substr($chaine,$debut,$long).'.';
			}
			$tab_colonne[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']] = '<td class="v">Référentiel présent. '.str_replace('◄DATE►',affich_date($DB_ROW['referentiel_partage_date']),$tab_partage[$DB_ROW['referentiel_partage_etat']]).'</td>'.'<td class="v">'.$methode_calcul_texte.'</td>';
		}
	}
	// On construit et affiche le tableau résultant
	$tab_paliers = explode( '.' , substr(LISTING_ID_NIVEAUX_PALIERS,1,-1) );
	$affichage = '<table class="comp_view"><thead><tr><th>Matière</th><th>Niveau</th><th>Référentiel</th><th>Méthode de calcul</th><th class="nu"></th></tr></thead><tbody>'."\r\n"; // <th>Coordonnateur(s)</th>
	foreach($tab_matiere as $matiere_id => $tab)
	{
		$rowspan = ($matiere_id!=ID_MATIERE_TRANSVERSALE) ? $nb_niveaux : mb_substr_count($_SESSION['PALIERS'],',','UTF-8')+1 ;
		$matiere_nom   = $tab['nom'];
		// $matiere_coord = (isset($tab['coord'])) ? '>'.$tab['coord'] : ' class="r">Absence de coordonnateur.' ;
		$affichage .= '<tr><td colspan="5" class="nu">&nbsp;</td></tr>'."\r\n"; // colspan="6"
		$affichage .= '<tr><td rowspan="'.$rowspan.'">'.$matiere_nom.'</td>'; // <td rowspan="'.$rowspan.'"'.$matiere_coord.'</td>
		$affichage_suite = false;
		foreach($tab_niveau as $niveau_id => $niveau_nom)
		{
			if( ($matiere_id!=ID_MATIERE_TRANSVERSALE) || (in_array($niveau_id,$tab_paliers)) )
			{
				$ids = 'ids_'.$matiere_id.'_'.$niveau_id;
				$colonnes = (isset($tab_colonne[$matiere_id][$niveau_id])) ? $tab_colonne[$matiere_id][$niveau_id].'<td class="nu" id="'.$ids.'"><q class="voir" title="Voir le détail de ce référentiel."></q></td>' : '<td class="r">Absence de référentiel.</td><td class="r">Sans objet.</td><td class="nu"></td></td>' ;
				if($affichage_suite===false)
				{
					$affichage .= '<td>'.$niveau_nom.'</td>'.$colonnes;
					$affichage_suite = '';
				}
				else
				{
					$affichage_suite .= '<tr><td>'.$niveau_nom.'</td>'.$colonnes.'</tr>'."\r\n";
				}
			}
		}
		$affichage .= '</tr>'."\r\n".$affichage_suite;
	}
	$affichage .= '</tbody></table>'."\r\n";
	echo $affichage;
}
?>

</form>

<hr />

<div id="referentiel">
</div>


