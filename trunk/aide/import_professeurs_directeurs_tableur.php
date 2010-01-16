<?php
/**
 * @version $Id: import_professeurs_directeurs_tableur.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Import professeurs / directeurs avec un tableur";
?>

<h2>Introduction</h2>
<p>
	Cette procédure peut être utilisée pour une initialisation, ou pour une mise à jour ultérieure. Lors de la procédure, il peut être proposé de retirer des utilisateurs : leurs données ne seront pas supprimées, les comptes seront simplement désactivés. Il n'y a pas de risque d'écraser des données existantes.<p />
	<span class="danger">Tous les professeurs et directeurs de l'établissement doivent être présents dans le fichier transmis ; s'il n'y sont pas, SACoche estime qu'ils ont quitté l'établissement.</span>
</p>

<h2>Création du fichier avec un tableur</h2>
<ul class="puce">
	<li>Utiliser un logiciel tableur et présenter les données en respectant cette disposition :</li>
</ul>
<table id="simulation">
	<tbody>
		<tr><th class="nu"></th><th>A</th><th>B</th><th>C</th><th>D</th></tr>
		<tr><th>1</th><td><b>numéro</b></td><td><b>nom</b></td><td><b>prenom</b></td><td><b>profil</b></td></tr>
		<tr><th>2</th><td>prof_1934</td><td>LABROSSE</td><td>Adam</td><td>professeur</td></tr>
		<tr><th>3</th><td>dir_534</td><td>LAFERMETURET</td><td>Claire</td><td>directeur</td></tr>
		<tr><th>4</th><td>...</td><td>...</td><td>...</td><td>...</td></tr>
	</tbody>
</table>
<p />
<ul class="puce">
	<li><em>numéro</em> est un numéro unique qui identifie le professeur ou le directeur (il peut être au format texte ou numérique, mais limité à 11 caractères) ; laisser vide s'il n'existe pas mais alors attention : la moindre correction sur une orthographe risque de générer un nouveau professeur / directeur...</li>
	<li><em>nom</em> est le nom de la personne.</li>
	<li><em>prénom</em> est le prénom de la personne.</li>
	<li><em>profil</em> prend soit la valeur <em>«&nbsp;professeur&nbsp;»</em> soit la valeur <em>«&nbsp;directeur&nbsp;»</em></li>
	<li>La première ligne du fichier, avec les intitulés, sera ignorée, mais ne la retirez pas du fichier !</li>
</ul>
<p />
<ul class="puce">
	<li>Utiliser ensuite le menu <em>[Enregistrer sous...]</em> pour enregistrer le fichier au format <em>csv</em> (peu importe le séparateur).</li>
</ul>
<p><span class="danger">Le format <em>csv</em> n'enregistre qu'une feuille (la première, ou la feuille courante...) ; il faut donc avoir la liste de tous les professeurs et directeurs sur la même feuille avant d'exporter en <em>csv</em>.</span></p>

<h2>Importation du fichier dans SACoche</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Importer professeurs &amp; directeurs]</em>.</li>
	<li>Cliquer sur <em>[Parcourir...]</em> et indiquer le fichier précédent.</li>
</ul>
<p>Remarque : il ne faut pas compresser ce fichier avant de l'envoyer.</p>

<h2>Récupération des identifiants</h2>
<p>
	A la fin de la dernière étape, ne pas oublier de récupérer les identifiants des nouveaux utilisateurs inscrits (les mots de passe étant cryptés, ils ne sont plus accessibles ultérieurement) : on peut télécharger un fichier <em>zip</em> contenant un fichier <em>csv</em> (lisible avec un tableur), ainsi qu'un fichier <em>pdf</em> d'étiquettes à distribuer.
</p>
