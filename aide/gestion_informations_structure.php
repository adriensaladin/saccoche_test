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
$TITRE = "Gestion de l'identité de l'établissement";
?>

<h2>Introduction</h2>
<p>
	Les administrateurs peuvent consuler les informations concernant l'établissement et identifier l'établissement dans la base <em>Sésamath</em>.<br />
	<span class="astuce">Si les informations sont déjà renseignées et sont correctes, alors ne les modifiez pas !</span>
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Identité de l'établissement]</em>.</li>
</ul>

<h2>Données saisies par le webmestre</h2>
<p>
	Le webmestre a la charge d'indiquer la dénomination et le code UAI éventuel de l'établissement.<br />Si besoin, contactez-le pour apporter des modifications.
</p>

<h2>Identification de l'établissement dans la base Sésamath</h2>
<p>
	Pour certaines opérations, les serveurs hébergeant <em>SACoche</em> doivent se connecter au serveur communautaire de <em>Sésamath</em> :
</p>
<ul class="puce">
	<li>Consulter, choisir, récupérer un référentiel partagé par d'autres</li>
	<li>Proposer, envoyer, mettre à jour un référentiel que l'on souhaite partager</li>
</ul>
<p>
	Mais ceci nécessite de pouvoir identifier les établissements concernés dans une base unique, afin de pouvoir les reconnaître. Ce menu permet à l'administrateur de procéder à cette identification.
</p>
<p class="astuce">Lors d'une action sur le serveur communaitaire, l'adresse IP de l'utilisateur et l'adresse du serveur sont enregistrées, afin de pouvoir réagir en cas de problème (fausse déclaration, référentiel effacé par autrui...).</p>
<p class="astuce">Une installation de <em>SACoche</em> non déclarée sur le serveur communautaire est utilisable de façon entièrement autonome, mais ne dispose pas des fontionnalités supplémentaires nécessitant un échange avec ce serveur communautaire.</p>
