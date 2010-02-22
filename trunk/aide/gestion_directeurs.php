<?php
/**
 * @version $Id$
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
$TITRE = "Gestion des directeurs";
?>

<h2>Introduction</h2>
<p>
	Seul l'administrateur gère les directeurs et leurs affectations.<br />
	<span class="astuce">L'ajout d'un directeur peut se faire depuis la procédure d'importation Sconet / tableur ou manuellement.</span>
</p>

<h2>Créer / modifier / enlever des directeurs</h2>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Directeurs]</em>.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_ajouter.png" /> pour créer un nouveau directeur.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_modifier.png" /> pour modifier un directeur présent.</li>
	<li>Cliquer sur <img alt="niveau" src="./_img/action/action_desactiver.png" /> pour enlever un directeur présent.</li>
</ul>
<p>Remarques sur les différents champs :</p>
<ul class="puce">
	<li>Les champs "<b>Id&nbsp;ENT</b>" et "<b>Id&nbsp;GEPI</b>" servent à réaliser des interconnexions (voir documentations correspondantes).</li>
	<li>Il est déconseillé de modifier les champs "<b>n°&nbsp;Sconet</b>" et "<b>Référence</b>", sauf en connaissance de cause (lors d'une inscription manuelle, ces champs peuvent être ignorés).</li>
	<li>Lors de l'ajout d'un nouveau directeur, un nom d'utilisateur et un mot de passe sont générés automatiquement : ne pas oublier de les noter.</li>
	<li>Un nom d'utilisateur peut être modifié sous réserve de disponibilité.</li>
	<li>Retirer un directeur ne le supprime pas de la base : son compte est simplement désactivé.</li>
</ul>

<h2>Documentations associées</h2>
<ul class="puce">
	<li><span class="manuel"><a href="./aide.php?fichier=import_professeurs_directeurs_Sconet">DOC : Import professeurs / directeurs depuis Sconet</a></span></li>
	<li><span class="manuel"><a href="./aide.php?fichier=import_professeurs_directeurs_tableur">DOC : Import professeurs / directeurs avec un tableur</a></span></li>
</ul>
