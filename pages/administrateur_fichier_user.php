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
$TITRE = "Importer des fichiers d'utilisateurs";
$VERSION_JS_FILE += 8;
?>

<?php
$alerte = DB_STRUCTURE_compter_devoirs() ? '<p class="danger">La base actuelle contient des devoirs ; <span class="u">en début d\'année scolaire</span> vous devez <a href="./index.php?page=administrateur_nettoyage">purger la base avant d\'importer les nouveaux utilisateurs</a>.</p>' : '';

$test_UAI = ($_SESSION['UAI']) ? 'oui' : 'non' ;

$annee_scolaire  = (date('n')>7) ? date('Y') : date('Y')-1 ;
$nom_fin_fichier = $_SESSION['UAI'].'_'.$annee_scolaire;
?>

<form action="" method="post" id="form1">

	<ul class="puce">
		<li><span class="astuce">Si la procédure est utilisée en début d'année (initialisation), elle peut ensuite être renouvelée en cours d'année (mise à jour).</span></li>
		<li><span class="astuce">Pour un traitement individuel on peut utiliser les pages de gestion [<a href="./index.php?page=administrateur_eleve&amp;section=gestion">Élèves</a>] [<a href="./index.php?page=administrateur_parent&amp;section=gestion">Parents</a>] [<a href="./index.php?page=administrateur_professeur&amp;section=gestion">Professeurs</a>] [<a href="./index.php?page=administrateur_directeur">Directeurs</a>].</span></li>
		<li><span class="astuce">Les administrateurs ne se gèrent qu'individuellement depuis la page [<a href="./index.php?page=administrateur_administrateur">Administrateurs</a>].</span></li>
	</ul>
	<?php echo $alerte ?>

	<hr />

	<fieldset>
		<label class="tab" for="f_choix_principal">Catégorie :</label>
		<select id="f_choix_principal" name="f_choix_principal">
			<option value=""></option>
			<optgroup label="Fichiers extraits de Sconet / STS-Web (recommandé pour le second degré)">
				<option value="sconet_professeurs_directeurs_<?php echo $test_UAI ?>">Importer professeurs &amp; directeurs (avec leurs affectations).</option>
				<option value="sconet_eleves_<?php echo $test_UAI ?>">Importer les élèves (avec leurs affectations).</option>
				<option value="sconet_parents_<?php echo $test_UAI ?>">Importer les parents (avec adresses et responsabilités).</option>
			</optgroup>
			<optgroup label="Fichier extrait de Base Élèves (recommandé pour le premier degré)">
				<option value="base-eleves_eleves">Importer les élèves (avec leurs affectations).</option>
			</optgroup>
			<optgroup label="Fichiers fabriqués avec un tableur (hors Éducation Nationale française)">
				<option value="tableur_eleves">Importer les élèves (avec leur classe).</option>
				<option value="tableur_professeurs_directeurs">Importer professeurs &amp; directeurs (sans leurs affectations).</option>
			</optgroup>
		</select><br />
	</fieldset>

	<fieldset id="fieldset_sconet_professeurs_directeurs_non" class="hide">
		<hr />
		<label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
		<div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voyez la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
	</fieldset>

	<fieldset id="fieldset_sconet_professeurs_directeurs_oui" class="hide">
		<hr />
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_sconet">DOC : Import d'utilisateurs depuis Sconet / STS-Web</a></span></li>
			<li>Indiquez le fichier <em>sts_emp_<?php echo $nom_fin_fichier ?>.xml</em> (ou <em>sts_emp_<?php echo $nom_fin_fichier ?>.zip</em>) : <button id="sconet_professeurs_directeurs" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
	</fieldset>

	<fieldset id="fieldset_sconet_eleves_non" class="hide">
		<hr />
		<label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
		<div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voyez la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
	</fieldset>

	<fieldset id="fieldset_sconet_eleves_oui" class="hide">
		<hr />
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_sconet">DOC : Import d'utilisateurs depuis Sconet / STS-Web</a></span></li>
			<li>Indiquez le fichier <em>ExportXML_ElevesSansAdresses.zip</em> (ou <em>ElevesSansAdresses.xml</em>) : <button id="sconet_eleves" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
	</fieldset>

	<fieldset id="fieldset_sconet_parents_non" class="hide">
		<hr />
		<label class="alerte">Le numéro UAI de l'établissement n'étant pas renseigné, cette procédure ne peut pas être utilisée.</label>
		<div class="astuce">Vous devez demander au webmestre d'indiquer votre numéro UAI : voyez la page [<a href="./index.php?page=administrateur_etabl_identite">Identité de l'établissement</a>].</div>
	</fieldset>

	<fieldset id="fieldset_sconet_parents_oui" class="hide">
		<hr />
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_sconet">DOC : Import d'utilisateurs depuis Sconet / STS-Web</a></span></li>
			<li>Indiquez le fichier <em>ResponsablesAvecAdresses.zip</em> (ou <em>ResponsablesAvecAdresses.xml</em>) : <button id="sconet_parents" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
	</fieldset>

	<fieldset id="fieldset_base-eleves_eleves" class="hide">
		<hr />
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_base-eleves">DOC : Import d'utilisateurs depuis Base Élèves 1<sup>er</sup> degré</a></span></li>
			<li>Indiquez le fichier <em>CSVExtraction.csv</em> : <button id="base-eleves_eleves" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
	</fieldset>

	<fieldset id="fieldset_tableur_eleves" class="hide">
		<hr />
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_tableur">DOC : Import d'utilisateurs avec un tableur</a></span></li>
			<li>Indiquez le fichier <em>nom-du-fichier-eleves.csv</em> (ou <em>nom-du-fichier-eleves.txt</em>) : <button id="tableur_eleves" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
	</fieldset>

	<fieldset id="fieldset_tableur_professeurs_directeurs" class="hide">
		<hr />
		<ul class="puce">
			<li><span class="manuel"><a class="pop_up" href="<?php echo SERVEUR_DOCUMENTAIRE ?>?fichier=support_administrateur__import_users_tableur">DOC : Import d'utilisateurs avec un tableur</a></span></li>
			<li>Indiquez le fichier <em>nom-du-fichier-profs.csv</em> (ou <em>nom-du-fichier-profs.txt</em>) : <button id="tableur_professeurs_directeurs" type="button"><img alt="" src="./_img/bouton/fichier_import.png" /> Parcourir...</button></li>
		</ul>
	</fieldset>

</form>


<form action="" method="post" id="form2"><fieldset>
	<hr />
	<label id="ajax_msg">&nbsp;</label>
</fieldset></form>
