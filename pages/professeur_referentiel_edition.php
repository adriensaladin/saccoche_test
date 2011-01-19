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
$TITRE = "Modifier le contenu des référentiels";
$VERSION_JS_FILE += 7;
?>

<ul class="puce">
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_modifier_contenu">DOC : Modifier le contenu des référentiels.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
	<li><span class="astuce">Pour mettre à jour un référentiel modifié sur le serveur communautaire, utiliser la page "<a href="./index.php?page=professeur_referentiel&amp;section=gestion">créer / paramétrer les référentiels</a>".</span></li>
	<li><span class="danger">Retirer des items supprime les résultats associés de tous les élèves !</span></li>
</ul>

<hr />

<form action="" onsubmit="return false;">

<?php
// J'ai séparé en plusieurs requêtes au bout de plusieurs heures sans m'en sortir (entre les matières sans coordonnateurs, sans référentiel, les deux à la fois...).
// La recherche ne s'effectue que sur les matières et niveaux utilisés, sans débusquer des référentiels résiduels.
$tab_matiere = array();
$tab_niveau  = array();
// On récupère la liste des matières où le professeur est rattaché, et s'il en est coordonnateur
$DB_TAB = DB_STRUCTURE_lister_matieres_professeur_infos_referentiel($_SESSION['MATIERES'],$_SESSION['USER_ID']);
if(count($DB_TAB))
{
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_matiere[$DB_ROW['matiere_id']] = array( 'nom'=>html($DB_ROW['matiere_nom']) , 'coord'=>$DB_ROW['jointure_coord'] , 'niveau_nb'=>0 );
	}
}
$listing_matieres_id = implode(',',array_keys($tab_matiere));

if(!$listing_matieres_id)
{
	echo'<p><span class="danger">Vous n\'êtes rattaché à aucune matière de l\'établissement !</span></p>';
}
elseif(!$_SESSION['NIVEAUX']) // normalement impossible
{
	echo'<p><span class="danger">Aucun niveau n\'est rattaché à l\'établissement !</span></p>';
}
elseif(!$_SESSION['CYCLES']) // normalement impossible
{
	echo'<p><span class="danger">Aucun cycle n\'est rattaché à l\'établissement !</span></p>';
}
else
{
	// On récupère la liste des niveaux utilisés par l'établissement
	$DB_TAB = DB_STRUCTURE_lister_niveaux_etablissement($_SESSION['NIVEAUX'],$_SESSION['CYCLES']);
	$nb_niveaux = count($DB_TAB);
	foreach($DB_TAB as $DB_ROW)
	{
		$tab_niveau[$DB_ROW['niveau_id']] = html($DB_ROW['niveau_nom']);
	}
	// On récupère la liste des référentiels par matière avec indication du nombre de niveau
	$tab_partage = array('oui'=>'<img title="Référentiel partagé sur le serveur communautaire (MAJ le ◄DATE►)." alt="" src="./_img/partage1.gif" />','non'=>'<img title="Référentiel non partagé avec la communauté (choix du ◄DATE►)." alt="" src="./_img/partage0.gif" />','bof'=>'<img title="Référentiel dont le partage est sans intérêt (pas novateur)." alt="" src="./_img/partage0.gif" />','hs'=>'<img title="Référentiel dont le partage est sans objet (matière spécifique)." alt="" src="./_img/partage0.gif" />');
	$DB_TAB = DB_STRUCTURE_lister_referentiels_infos_groupement_matieres($listing_matieres_id,$_SESSION['NIVEAUX'],$_SESSION['CYCLES']);
	if(count($DB_TAB))
	{
		foreach($DB_TAB as $DB_ROW)
		{
			$tab_matiere[$DB_ROW['matiere_id']]['niveau_nb'] = $DB_ROW['niveau_nb'];
		}
	}
	// On construit et affiche le tableau résultant
	$affichage = '<table class="comp_view"><thead><tr><th>Matière</th><th>Référentiel</th><th class="nu"></th></tr></thead><tbody>'."\r\n";
	foreach($tab_matiere as $matiere_id => $tab)
	{
		$matiere_nom   = $tab['nom'];
		$matiere_coord = $tab['coord'];
		$affichage .= '<tr lang="'.$matiere_nom.'"><td>'.$matiere_nom.'</td>';
		$id = 'm1_'.$matiere_id;
		if($tab_matiere[$matiere_id]['niveau_nb']>0)
		{
			$x = ($tab_matiere[$matiere_id]['niveau_nb'])>1 ? 'x' : '';
			$affichage .= '<td class="v">Référentiel présent sur '.$tab_matiere[$matiere_id]['niveau_nb'].' niveau'.$x.'.</td>';
			$affichage .= ($matiere_coord) ? '<td class="nu" id="'.$id.'"><q class="modifier" title="Paramétrer les référentiels de cette matière."></q></td>' : '<td class="nu"><q class="modifier_non" title="Action réservée aux coordonnateurs."></q></td>' ;

		}
		else
		{
			$affichage .= '<td class="r">Absence de référentiel.</td><td class="nu"></td>';
		}
		$affichage .= '</tr>'."\r\n";
	}
	$affichage .= '</tbody></table>'."\r\n";
	echo $affichage;
}
?>

<hr />

<div id="zone_compet">
</div>

<div id="zone_socle">
	<h2>Relation au socle commun</h2>
	<label class="tab" for="rien">Item disciplinaire :</label><span class="f_nom i"></span><br />
	<label class="tab" for="f_lien">Socle commun :</label>Cocher ci-dessous.<q class="valider" lang="choisir_compet" title="Valider la modification de la relation au socle commun."></q><q class="annuler" lang="choisir_compet" title="Annuler la modification de la relation au socle commun."></q>
	<p />
	<ul class="ul_n1"><li class="li_n3"><input id="socle_0" name="f_socle" type="radio" value="0" /><label for="socle_0">Hors-socle.</label></li></ul>
	<p />
	<?php
	// Affichage de la liste des items du socle pour chaque palier
	if($_SESSION['PALIERS'])
	{
		$DB_TAB = DB_STRUCTURE_recuperer_arborescence_palier($_SESSION['PALIERS']);
		echo afficher_arborescence_socle_from_SQL($DB_TAB,$dynamique=true,$reference=false,$aff_input=true,$ids=false);
	}
	else
	{
		echo'<p><span class="danger"> Aucun palier du socle n\'est associé à l\'établissement ! L\'administrateur doit préalablement choisir les paliers évalués...</span></p>'."\r\n";
	}
	?>
</div>

</form>

<p />

