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
$TITRE = "L'environnement professeur";
?>
<h2>Connexion</h2>
<p>
	Pour se connecter comme professeur, sélectionner son établissement (si besoin) puis saisir son nom d'utilisateur et son mot de passe (seul l'administrateur de l'établissement peut générer l'inscription du professeur et lui communiquer ses paramètres).
</p>

<h2>Changer son mot de passe</h2>
<p>
	Les professeurs peuvent modifier leur mot de passe (sauf si la connexion est dépendante d'un service extérieur, tel un ENT).<br />
	Les mots de passe sont cryptés et ne peuvent pas être renvoyés. En cas d'oubli du mot de passe professeur, contacter un administrateur qui est seul habilité à en générer un nouveau.
</p>

<h2>Fonctionnalités</h2>
<p>Le professeur peut :</p>
<ul class="puce">
	<li>gérer les référentiels de compétences et leur contenu (<b>coordonnateurs uniquement</b>)</li>
	<li>gérer des groupes de besoin personnalisés à partir de ses élèves (facultatif)</li>
	<li>créer des évaluations par compétences, en saisir les acquisitions, imprimer des cartouches associés</li>
	<li>générer des grilles de compétences</li>
	<li>établir des bilans de compétences</li>
	<li>estimer des attestations de maîtrise du socle</li>
</ul>
<p>
	L'administrateur de <em>SACoche</em> a la charge de gérer les élèves, les professeurs, les classes, les matières, les périodes...<br />
	Il faut le contacter si on constate une anomalie dans ses données.
</p>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=environnement_coordonnateur">DOC : L'environnement professeur coordonnateur.</a></span></li>
</ul>
