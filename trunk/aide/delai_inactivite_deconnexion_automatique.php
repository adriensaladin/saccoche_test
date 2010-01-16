<?php
/**
 * @version $Id: delai_inactivite_deconnexion_automatique.php 8 2009-10-30 20:56:02Z thomas $
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
$TITRE = "Délai d'inactivité et déconnexion automatique";
?>

<h2>Fonctionnement</h2>
<p>
	Dans chaque espace identifié figure en haut à droite un compteur dynamique <img alt="réveil" width="16" height="16" src="./_img/clock_fixe.png" /> indiquant le temps restant avant une deconnexion automatique pour inactivité.<br />
	Un changement de page, ou une validation quelconque d'un formulaire, est considéré comme une activité et remet le compteur au maximum. Ainsi, par exemple, valider régulièrement une saisie partielle des résultats d'une évaluation permet d'éviter toute déconnexion.<br />
	Lorsque le compteur arrive à moins de 5 minutes, il se met à clignoter <img alt="réveil" width="16" height="16" src="./_img/clock_anim.gif" /> et emet un léger signal sonore chaque minute.
</p>

<h2>Réglage par l'administrateur</h2>
<p>
	Par défaut, le compteur est initialisé pour 30 minutes ; l'administrateur peut paramétrer ce délai dans une fourchette allant de 10min à 90min.
</p>
<ul class="puce">
	<li>Se connecter avec son compte administrateur.</li>
	<li>Menu <em>[Paramétrages]</em> puis <em>[Délai avant déconnexion]</em>.</li>
	<li>Sélectionner le délai souhaité et <em>[Valider]</em>.</li>
</ul>
