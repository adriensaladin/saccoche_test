<?php
/**
 * @version $Id: force_login_mdp_tableur.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Imposer identifiants SACoche avec un tableur";
?>

<h2>Introduction</h2>
<p>
	Au départ, lors de l'import des élèves et des personnels, <em>SACoche</em> génère des noms d'utilisateurs selon le format choisi par l'administrateur, et des mots de passe aléatoires. Mais si ces utilisateurs disposent déjà d'identifiants par ailleurs (serveur ou autre application utilisée par l'établissement...), il est aussi possible de les modifier ensuite dans <em>SACoche</em> à l'aide d'un fichier tableur.<p />
	<span class="danger">Cette procédure ne sert pas à faire le lien avec un ENT !</span><br />
	<span class="danger">Cette procédure ne sert pas à inscrire des utilisateurs !</span>
</p>

<h2>Récupération d'un fichier csv pour démarrer</h2>
<p>
	Il est possible de télécharger sur le site un fichier tableur au format <em>csv</em> (avec comme séparateur la tabulation), contenant la liste des noms / prénoms / logins des utilisateurs enregistrés dans <em>SACoche</em>. Ce fichier peut servir de base pour être réimporté avec des informations supplémentaires (autres logins, nouveaux mots de passe). En procédant de la sorte, on s'assure de la concordance exacte des noms et des prénoms (qui serviront de comparateurs).
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Imposer identifiants SACoche]</em>.</li>
	<li>Cliquer sur <em>[récupérer un fichier csv avec les noms / prénoms / logins actuels]</em> et enregistrer le fichier.</li>
</ul>

<h2>Création / modification du fichier avec un tableur</h2>
<ul class="puce">
	<li>Utiliser un logiciel tableur et présenter les données en respectant cette disposition :</li>
</ul>
<table id="simulation">
	<tbody>
		<tr><th class="nu"></th><th>A</th><th>B</th><th>C</th><th>D</th></tr>
		<tr><th>1</th><td><b>login</b></td><td><b>mot de passe</b></td><td><b>nom</b></td><td><b>prenom</b></td></tr>
		<tr><th>2</th><td>ada.labrosse</td><td>dentifrice</td><td>LABROSSE</td><td>Adam</td></tr>
		<tr><th>3</th><td>c_lafermeturet</td><td>&nbsp;</td><td>LAFERMETURET</td><td>Claire</td></tr>
		<tr><th>4</th><td>...</td><td>...</td><td>...</td><td>...</td></tr>
	</tbody>
</table>
<p />
<ul class="puce">
	<li><em>login</em> ne doit être utilisé que si on veut imposer un nom d'utilisateur (sous réserve de disponibilité) ; laisser le champ vide pour conserver la valeur actuelle.</li>
	<li><em>mot de passe</em> ne doit être utilisé que si on veut imposer un mot de passe ; laisser le champ vide pour conserver la valeur actuelle.</li>
	<li><em>nom</em> est le nom de l'utilisateur.</li>
	<li><em>prénom</em> est le prénom de l'utilisateur.</li>
	<li>La première ligne du fichier, avec les intitulés, sera ignorée, mais ne la retirez pas du fichier !</li>
</ul>
<p />
Remarque : il n'est pas obligatoire d'indiquer tous les utilisateurs de l'établissement ; seules les personnes présentes dans le fichier, concordants avec le nom et le prénom indiqués dans la base, et dont un nouveau nom d'utilisateur ou un nouveau mot de passe est indiqué, verront leurs données mises à jour.
<p />
<ul class="puce">
	<li>Utiliser ensuite le menu <em>[Enregistrer sous...]</em> pour enregistrer le fichier au format <em>csv</em> (peu importe le séparateur).</li>
</ul>
<p><span class="danger">Le format <em>csv</em> n'enregistre qu'une feuille (la première, ou la feuille courante...) ; il faut donc avoir la liste de tous les élèves sur la même feuille avant d'exporter en <em>csv</em>.</span></p>

<h2>Importation du fichier dans SACoche</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Import / Export]</em> puis <em>[Imposer identifiants SACoche]</em>.</li>
	<li>Cliquer sur <em>[Parcourir...]</em> et indiquer le fichier précédent.</li>
</ul>
<p>Remarque : il ne faut pas compresser ce fichier avant de l'envoyer.</p>
