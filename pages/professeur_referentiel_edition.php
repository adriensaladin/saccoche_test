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
?>

<?php
// Indication des profils ayant accès à cette page
require_once('./_inc/tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_profils = array('professeur','profcoordonnateur','aucunprof');
$texte_profil = $_SESSION['DROIT_GERER_REFERENTIEL'];
foreach($tab_profils as $profil)
{
	$texte_profil = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$texte_profil);
}
?>

<ul class="puce">
	<li><span class="astuce">Profils autorisés par les administrateurs : <span class="u"><?php echo $texte_profil ?></span>.</span></li>
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_modifier_contenu">DOC : Modifier le contenu des référentiels.</a></span></li>
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__liaison_matiere_socle">DOC : Liaison matières &amp; socle commun.</a></span></li>
	<li><span class="astuce">Pour mettre à jour un référentiel modifié sur le serveur communautaire, utiliser la page "<a href="./index.php?page=professeur_referentiel&amp;section=gestion">créer / paramétrer les référentiels</a>".</span></li>
	<li><span class="astuce">Pour ajouter / modifier les ressources de travail associées aux items, utiliser la page "<a href="./index.php?page=professeur_referentiel&amp;section=ressources">associer des ressources aux items</a>".</span></li>
	<li><span class="danger">Retirer des items supprime les résultats associés de tous les élèves !</span></li>
</ul>

<hr />

<form action="#" method="post" id="zone_choix_referentiel" onsubmit="return false;">
<?php
// On récupère la liste des référentiels des matières auxquelles le professeur est rattaché, et s'il en est coordonnateur
$DB_TAB = DB_STRUCTURE_PROFESSEUR::DB_lister_matieres_niveaux_referentiels_professeur($_SESSION['USER_ID']);
if(!count($DB_TAB))
{
	echo'<ul class="puce">';
	echo'<li><span class="danger">Aucun référentiel présent parmi les matières qui vous sont rattachées !</span></li>';
	echo'<li><span class="astuce">Commencer par <a href="./index.php?page=professeur_referentiel&amp;section=gestion">créer ou importer un référentiel</a>.</span></li>';
	echo'</ul>';
}
else
{
	// On récupère les données
	$tab_matiere = array();
	foreach($DB_TAB as $DB_ROW)
	{
		if(!isset($tab_matiere[$DB_ROW['matiere_id']]))
		{
			$matiere_droit = ( (($_SESSION['DROIT_GERER_REFERENTIEL']=='profcoordonnateur')&&($DB_ROW['jointure_coord'])) || ($_SESSION['DROIT_GERER_REFERENTIEL']=='professeur') ) ? TRUE : FALSE ;
			$icone_action  = ($matiere_droit) ? '<q class="modifier" title="Modifier les référentiels de cette matière."></q>' : '<q class="modifier_non" title="Accès restreint : '.$texte_profil.'."></q>' ;
			$tab_matiere[$DB_ROW['matiere_id']] = array( 
				'matiere_nom' => html($DB_ROW['matiere_nom']) ,
				'matiere_ref' => clean_fichier($DB_ROW['matiere_ref']) ,
				'matiere_col' => '<td class="nu" id="td_'.$DB_ROW['matiere_id'].'">'.$icone_action.'</td>' ,
				'niveau_nb'=>1
			);
		}
		else
		{
			$tab_matiere[$DB_ROW['matiere_id']]['niveau_nb']++;
		}
	}
	// On construit et affiche le tableau résultant
	$affichage = '<table class="vm_nug"><thead><tr><th>Matière</th><th>Niveau(x)</th><th class="nu"></th></tr></thead><tbody>'."\r\n";
	foreach($tab_matiere as $matiere_id => $tab)
	{
		$x = ($tab_matiere[$matiere_id]['niveau_nb'])>1 ? 'x' : '';
		$affichage .= '<tr><td>'.$tab['matiere_nom'].'</td><td>'.$tab_matiere[$matiere_id]['niveau_nb'].' niveau'.$x.'</td>'.$tab['matiere_col'].'</tr>'."\r\n";
	}
	$affichage .= '</tbody></table>'."\r\n";
	echo $affichage;
}
?>
</form>

<form action="#" method="post" id="zone_elaboration_referentiel" onsubmit="return false;" class="arbre_dynamique">
</form>

<div id="zone_socle" class="arbre_dynamique hide">
	<h2>Relation au socle commun</h2>
	<form action="#" method="post">
		<p>
			<label class="tab">Item disciplinaire :</label><span class="f_nom i"></span><br />
			<label class="tab">Socle commun :</label>Cocher ci-dessous (<span class="astuce">cliquer sur un intitulé pour déployer son contenu</span>).<br />
			<span class="tab"></span><button id="choisir_socle_valider" type="button" class="valider">Valider le choix effectué.</button> <button id="choisir_socle_annuler" type="button" class="annuler">Annuler.</button>
		</p>
		<ul class="ul_n1 p"><li class="li_n3"><input id="socle_0" name="f_socle" type="radio" value="0" /><label for="socle_0">Hors-socle.</label></li></ul>
		<?php
		// Affichage de la liste des items du socle pour chaque palier
		$DB_TAB = DB_STRUCTURE_COMMUN::DB_recuperer_arborescence_palier();
		if(count($DB_TAB))
		{
			echo afficher_arborescence_socle_from_SQL($DB_TAB,$dynamique=true,$reference=false,$aff_input=true,$ids=false);
		}
		else
		{
			echo'<span class="danger"> Aucun palier du socle n\'est associé à l\'établissement ! L\'administrateur doit préalablement choisir les paliers évalués...</span>';
		}
		?>
	</form>
</div>

