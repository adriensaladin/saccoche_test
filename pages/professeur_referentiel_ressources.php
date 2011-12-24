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
$TITRE = "Associer des ressources aux items";
?>

<?php
// Indication des profils ayant accès à cette page
require_once('./_inc/tableau_profils.php'); // Charge $tab_profil_libelle[$profil][court|long][1|2]
$tab_profils = array('professeur','profcoordonnateur','aucunprof');
$texte_profil = $_SESSION['DROIT_GERER_RESSOURCE'];
foreach($tab_profils as $profil)
{
	$texte_profil = str_replace($profil,$tab_profil_libelle[$profil]['long'][2],$texte_profil);
}
?>

<ul class="puce">
	<li><span class="astuce">Profils autorisés par les administrateurs : <span class="u"><?php echo $texte_profil ?></span>.</span></li>
	<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_lier_ressources">DOC : Associer aux items des ressources pour travailler.</a></span></li>
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
	$tab_colonne = array();
	foreach($DB_TAB as $DB_ROW)
	{
		if(!isset($tab_matiere[$DB_ROW['matiere_id']]))
		{
			$matiere_droit = ( (($_SESSION['DROIT_GERER_RESSOURCE']=='profcoordonnateur')&&($DB_ROW['jointure_coord'])) || ($_SESSION['DROIT_GERER_RESSOURCE']=='professeur') ) ? TRUE : FALSE ;
			$icone_action  = ($matiere_droit) ? '<q class="modifier" title="Modifier les ressources de ce référentiel."></q>' : '<q class="modifier_non" title="Accès restreint : '.$texte_profil.'."></q>' ;
			$tab_matiere[$DB_ROW['matiere_id']] = array(
				'matiere_nom' => html($DB_ROW['matiere_nom']) ,
				'matiere_ref' => clean_fichier($DB_ROW['matiere_ref']) ,
				'matiere_act' => $icone_action
			);
		}
		$tab_colonne[$DB_ROW['matiere_id']][$DB_ROW['niveau_id']] = '<td>'.html($DB_ROW['niveau_nom']).'</td><td class="v">Référentiel présent.</td><td class="nu" id="td_'.$DB_ROW['matiere_id'].'_'.$DB_ROW['niveau_id'].'">'.$tab_matiere[$DB_ROW['matiere_id']]['matiere_act'].'</td>';
	}
	// On construit et affiche le tableau résultant
	$affichage = '<table class="vm_nug"><thead><tr><th>Matière</th><th>Niveau</th><th>Référentiel</th><th class="nu"></th></tr></thead><tbody>'."\r\n";
	foreach($tab_matiere as $matiere_id => $tab)
	{
		$rowspan = count($tab_colonne[$matiere_id]);
		foreach($tab_colonne[$matiere_id] as $niveau_id => $cellules)
		{
			if($rowspan)
			{
				$affichage .= '<tr class="tr_'.$tab['matiere_ref'].'"><td rowspan="'.$rowspan.'">'.$tab['matiere_nom'].'</td>'.$cellules.'</tr>'."\r\n";
				$rowspan = 0;
			}
			else
			{
				$affichage .= '<tr class="tr_'.$tab['matiere_ref'].'">'.$cellules.'</tr>'."\r\n";
			}
		}
	}
	$affichage .= '</tbody></table>'."\r\n";
	echo $affichage;
}
?>
</form>

<form action="#" method="post" id="zone_elaboration_referentiel" onsubmit="return false;" class="arbre_dynamique">
</form>

<div id="zone_ressources" class="hide">
	<form action="#" method="post" id="zone_ressources_form">
		<h2>Liens (ressources pour travailler)</h2>
		<p><label class="tab">Item :</label><span class="f_nom i"></span><input type="hidden" id="page_mode" value="" /></p>
		<ul id="sortable">
			<li></li>
		</ul>
		<div><span class="tab"></span><button class="annuler" type="button" id="choisir_ressources_annuler">Annuler / Retour.</button> <button class="valider" type="button" id="choisir_ressources_valider">Valider et enregistrer ces liens.</button> <label id="ajax_ressources_msg">&nbsp;</label></div>
		<hr />
		<h2>Ajouter un paragraphe</h2>
		<div class="sortable"><label class="tab">Sous-titre :</label><input id="paragraphe_nom" value="" size="100" maxlength="256" /><q id="paragraphe_ajouter" class="ajouter" title="Ajouter ce paragraphe"></q></div>
		<h2>Ajouter un lien</h2>
		<div class="sortable"><label class="tab">Intitulé :</label><input id="lien_nom" value="" size="100" maxlength="256" /><br /><label class="tab">Adresse :</label><input id="lien_url" value="" size="100" maxlength="256" /><q id="lien_ajouter" class="ajouter" title="Ajouter ce lien"></q></div>
		<h2>Mettre en ligne une ressource</h2>
		<div>Vous pouvez si besoin <button id="afficher_zone_ressources_upload" type="button" class="fichier_import">déposer une ressource sur le serveur communautaire</button> afin de pouvoir faire pointer un lien vers celle-ci.</div>
		<h2>Recherche de liens existants</h2>
		<div class="sortable"><label class="tab">Mots clefs :</label><input id="chaine_recherche" value="" size="90" maxlength="256" /> <button id="ressources_rechercher" type="button" class="rechercher">Chercher.</button></div>
		<div id="zone_resultat_recherche"></div>
	</form>
	<div id="zone_ressources_upload" class="hide">
		<h2>Mettre en ligne une ressource</h2>
		<ul class="puce">
			<li><span class="danger">Lisez la documentation afin de prendre connaissance des conditions d'utilisation !</span></li>
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=referentiels_socle__referentiel_uploader_ressources">DOC : Mettre en ligne des ressources pour travailler.</a></span></li>
		</ul>
		<p>
			<button id="afficher_zone_ressources_form" type="button" class="retourner">Annuler.</button>
			<button id="bouton_import" type="button" class="fichier_import">Parcourir...</button>
			<label id="ajax_ressources_upload">&nbsp;</label>
			<button id="bouton_report" type="button" class="retourner hide"></button>
		</p>
	</div>
</div>

<script type="text/javascript">
	var etablissement_identifie = <?php echo ( $_SESSION['SESAMATH_ID'] && $_SESSION['SESAMATH_KEY'] ) ? 'true' : 'false' ;?>;
</script>
