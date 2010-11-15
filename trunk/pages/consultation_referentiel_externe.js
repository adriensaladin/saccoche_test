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

// jQuery !
$(document).ready
(
	function()
	{

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Charger le formulaire listant les structures ayant partagées un référentiel (appel au serveur communautaire)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var charger_formulaire_structures = function()
		{
			$('#ajax_msg').removeAttr("class").addClass("loader").html('Chargement du formulaire... Veuillez patienter.');
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'action=Afficher_structures',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! <a href="#" id="charger_formulaire_structures">Veuillez essayer de nouveau.</a>');
						return false;
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,7)!='<option')
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML+' <a href="#" id="charger_formulaire_structures">Veuillez essayer de nouveau.</a>');
						}
						else
						{
							modification = false;
							$('#ajax_msg').removeAttr("class").html('&nbsp;');
							$('#f_structure').html(responseHTML);
							$('#rechercher').removeAttr("class").show("fast"); // Pour IE7 le show() ne suffit pas
						}
					}
				}
			);
		};

		// Charger au démarrage et au clic sur le lien obtenu si échec
		charger_formulaire_structures();
		$('#charger_formulaire_structures').live(  'click' , charger_formulaire_structures );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Réagir au changement dans un select
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('select').change
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#choisir_referentiel_communautaire').hide("fast");
				$('#voir_referentiel_communautaire').hide("fast");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changement de matière -> desactiver les niveaux classiques en cas de matière transversale
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#f_matiere').change
		(
			function()
			{
				modif_niveau_selected = 0; // 0 = pas besoin modifier / 1 = à modifier / 2 = déjà modifié
				matiere_id = $('#f_matiere').val();
				$("#f_niveau option").each
				(
					function()
					{
						niveau_id = $(this).val();
						findme = '.'+niveau_id+'.';
						// Les niveaux "cycles" sont tout le temps accessibles
						if(listing_id_niveaux_cycles.indexOf(findme) == -1)
						{
							// matière classique -> tous niveaux actifs
							if(matiere_id != id_matiere_transversale)
							{
								$(this).removeAttr('disabled');
							}
							// matière transversale -> desactiver les autres niveaux
							else
							{
								$(this).attr('disabled','disabled');
								modif_niveau_selected = Math.max(modif_niveau_selected,1);
							}
						}
						// C'est un niveau cycle ; le sélectionner si besoin
						else if(modif_niveau_selected==1)
						{
							$(this).attr('selected','selected');
							modif_niveau_selected = 2;
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour chercher des référentiels partagés sur d'autres niveaux ou matières
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#rechercher').click
		(
			function()
			{
				$('#voir_referentiel_communautaire').hide();
				matiere_id   = $('#f_matiere').val();
				niveau_id    = $('#f_niveau').val();
				structure_id = $('#f_structure').val();
				if( (matiere_id==0) && (niveau_id==0) && (structure_id==0) )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Il faut préciser au moins un critère !");
					return false;
				}
				$('#rechercher').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Demande envoyée... Veuillez patienter.');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Lister_referentiels&matiere_id='+matiere_id+'&niveau_id='+niveau_id+'&structure_id='+structure_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('#rechercher').removeAttr('disabled');
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							$('#rechercher').removeAttr('disabled');
							if(responseHTML.substring(0,3)!='<li')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								maj_clock(1);
								$('#ajax_msg').removeAttr("class").html("&nbsp;");
								$('#choisir_referentiel_communautaire ul').html(responseHTML).parent().show();
								infobulle();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Voir le détail d'un référentiel partagé
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#choisir_referentiel_communautaire q.voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				referentiel_id = $(this).parent().attr('id').substr(3);
				description    = $(this).parent().text(); // Pb : il prend le contenu du <sup> avec
				longueur_sup   = $(this).prev().text().length;
				description    = description.substring(0,description.length-longueur_sup);
				new_label = '<label id="temp" class="loader">Demande envoyée... Veuillez patienter.</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=Voir_referentiel&referentiel_id='+referentiel_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('label[id=temp]').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.').fadeOut(2000,function(){$('label[id=temp]').remove();});
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,18)!='<ul class="ul_n1">')
							{
								$('label[id=temp]').removeAttr("class").addClass("alerte").html(responseHTML).fadeOut(2000,function(){$('label[id=temp]').remove();});
							}
							else
							{
								$('#voir_referentiel_communautaire ul li.li_m1').html('<b>'+description+'</b><q class="imprimer" title="Imprimer le référentiel."></q>'+responseHTML).parent().parent().show();
								infobulle();
								$('label[id=temp]').removeAttr("class").addClass("valide").html("Contenu affiché ci-dessous !").fadeOut(2000,function(){$('label[id=temp]').remove();});
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une image pour Imprimer un referentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#voir_referentiel_communautaire q.imprimer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				window.print();
			}
		);

	}
);
