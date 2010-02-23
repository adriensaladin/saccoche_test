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
$TITRE = "Import classes / élèves depuis Sconet";
?>

<h2>Introduction</h2>
<p>
	Cette procédure peut être utilisée pour une initialisation, ou pour une mise à jour ultérieure. Lors de la procédure, il peut être proposé de retirer des utilisateurs : leurs données ne seront pas supprimées, les comptes seront simplement désactivés. Il n'y a pas de risque d'écraser des données existantes.<p />
	<span class="danger">Tous les élèves de l'établissement doivent être présents dans le fichier transmis ; s'il n'y sont pas, SACoche estime qu'ils ont quitté l'établissement.</span>
</p>

<h2>Extraction du fichier de Sconet</h2>
<ul class="puce">
	<li>Accéder à la web-application Sconet en utilisant le navigateur Firefox.</li>
	<li>Choisir <em>[Accès Base Elèves]</em>, vérifier l'établissement, l'année, et entrer.</li>
	<li>Dans le menu de gauche choisir <em>[Exploitation]</em>.</li>
	<li>Puis <em>[Exports standard]</em>.</li>
	<li>Puis <em>[Exports XML génériques]</em>.</li>
	<li>Puis <em>[Export Elèves sans adresse]</em>.</li>
	<li>Enregistrer le fichier <em>ExportXML_ElevesSansAdresses.zip</em> obtenu.</li>
</ul>
<p><span class="danger">Utiliser le navigateur Internet Explorer pose problème, on risque alors de récupérer un fichier corrompu.</span></p>

<h2>Importation du fichier dans SACoche</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer élèves &amp; classes]</em>.</li>
	<li>Cliquer sur <em>[Parcourir...]</em> et indiquer le fichier précédent.</li>
</ul>
<p>Remarque : il est inutile de décompresser ce fichier (laissez-le avec son extension zip).</p>

<h2>Récupération des identifiants</h2>
<p>
	A la fin de la dernière étape, ne pas oublier de récupérer les identifiants des nouveaux utilisateurs inscrits (les mots de passe étant cryptés, ils ne sont plus accessibles ultérieurement) : on peut télécharger un fichier <em>zip</em> contenant un fichier <em>csv</em> (lisible avec un tableur), ainsi qu'un fichier <em>pdf</em> d'étiquettes à distribuer.
</p>
