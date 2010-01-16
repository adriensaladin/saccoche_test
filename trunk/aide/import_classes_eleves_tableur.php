<?php
/**
 * @version $Id: import_classes_eleves_tableur.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Import classes / élèves avec un tableur";
?>

<h2>Introduction</h2>
<p>
	Cette procédure peut être utilisée pour une initialisation, ou pour une mise à jour ultérieure. Lors de la procédure, il peut être proposé de retirer des utilisateurs : leurs données ne seront pas supprimées, les comptes seront simplement désactivés. Il n'y a pas de risque d'écraser des données existantes.<p />
	<span class="danger">Tous les élèves de l'établissement doivent être présents dans le fichier transmis ; s'il n'y sont pas, SACoche estime qu'ils ont quitté l'établissement.</span>
</p>

<h2>Création du fichier avec un tableur</h2>
<ul class="puce">
	<li>Utiliser un logiciel tableur et présenter les données en respectant cette disposition :</li>
</ul>
<table id="simulation">
	<tbody>
		<tr><th class="nu"></th><th>A</th><th>B</th><th>C</th><th>D</th></tr>
		<tr><th>1</th><td><b>numéro</b></td><td><b>nom</b></td><td><b>prenom</b></td><td><b>classe</b></td></tr>
		<tr><th>2</th><td>ele_1934</td><td>LABROSSE</td><td>Adam</td><td>6E A</td></tr>
		<tr><th>3</th><td>ele_534</td><td>LAFERMETURET</td><td>Claire</td><td>3E C</td></tr>
		<tr><th>4</th><td>...</td><td>...</td><td>...</td><td>...</td></tr>
	</tbody>
</table>
<p />
<ul class="puce">
	<li><em>numéro</em> est un numéro unique qui identifie l'élève tout au long de sa scolarité (il peut être au format texte ou numérique, mais limité à 11 caractères) ; laisser vide s'il n'existe pas mais alors attention : la moindre correction sur une orthographe risque de générer un nouvel élève...</li>
	<li><em>nom</em> est le nom de l'élève.</li>
	<li><em>prénom</em> est le prénom de l'élève.</li>
	<li><em>classe</em> est le nom court de la classe ; dans certains cas l'établissement utilise des noms courts (comme 6°1) et des noms longs (comme 6°Platon) : n'indiquer ici que le nom court.</li>
	<li>La première ligne du fichier, avec les intitulés, sera ignorée, mais ne la retirez pas du fichier !</li>
</ul>
<p />
<ul class="puce">
	<li>Utiliser ensuite le menu <em>[Enregistrer sous...]</em> pour enregistrer le fichier au format <em>csv</em> (peu importe le séparateur).</li>
</ul>
<p><span class="danger">Le format <em>csv</em> n'enregistre qu'une feuille (la première, ou la feuille courante...) ; il faut donc avoir la liste de tous les élèves sur la même feuille avant d'exporter en <em>csv</em>.</span></p>

<h2>Importation du fichier dans SACoche</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer élèves &amp; classes]</em>.</li>
	<li>Cliquer sur <em>[Parcourir...]</em> et indiquer le fichier précédent.</li>
</ul>
<p>Remarque : il ne faut pas compresser ce fichier avant de l'envoyer.</p>

<h2>Récupération des identifiants</h2>
<p>
	A la fin de la dernière étape, ne pas oublier de récupérer les identifiants des nouveaux utilisateurs inscrits (les mots de passe étant cryptés, ils ne sont plus accessibles ultérieurement) : on peut télécharger un fichier <em>zip</em> contenant un fichier <em>csv</em> (lisible avec un tableur), ainsi qu'un fichier <em>pdf</em> d'étiquettes à distribuer.
</p>
