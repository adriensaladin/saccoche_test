/**
 * @version $Id: recherche_generale.js 8 2009-10-30 20:56:02Z thomas $
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

// jQuery !
$(document).ready
(
	function()
	{

		// Réagir au changement dans un select
		$('select').change
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#choisir_referentiel').hide("fast");
				$('#zone_compet').hide("fast");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Validation du formulaire pour afficher les établissements proposant un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#f_submit').click
		(
			function()
			{
				matiere_id  = $("#f_matiere option:selected").val();
				niveau_id   = $("#f_niveau option:selected").val();
				matiere_txt = $("#f_matiere option:selected").text();
				niveau_txt  = $("#f_niveau option:selected").text();
				if(!matiere_id)
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Il faut choisir une matière !");
					return false;
				}
				else if(!niveau_id)
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Il faut choisir un niveau !");
					return false;
				}
				$('#zone_compet').html("&nbsp;");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Lister&matiere='+matiere_id+'&niveau='+niveau_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,3)!='<li')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").addClass("valide").html("Référentiels disponibles affichés ci-dessous...");
								$('#mat_niv').html(matiere_txt+' - '+niveau_txt);
								$('#choisir_referentiel ul').html(responseHTML);
								$('#choisir_referentiel').show();
								$('#zone_compet').hide("fast");
								infobulle();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Voir un référentiel d'un autre établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				matiere_id  = $("#f_matiere option:selected").val();
				niveau_id   = $("#f_niveau option:selected").val();
				matiere_txt = $("#f_matiere option:selected").text();
				niveau_txt  = $("#f_niveau option:selected").text();
				donneur = $(this).parent().attr('id');
				etabl   = $(this).parent().text();
				new_label = '<label id="temp" class="loader">Demande envoyée... Veuillez patienter.</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Voir&matiere='+matiere_id+'&niveau='+niveau_id+'&donneur='+donneur,
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
								$('#zone_compet').html('<hr /><h2>'+etabl+'</h2><ul class="ul_m1"><li class="li_m1">'+matiere_txt+'<ul class="ul_m2"><li class="li_m2">'+niveau_txt+responseHTML+'</li></ul></li></ul><p />').show();
								infobulle();
								$('label[id=temp]').removeAttr("class").addClass("valide").html("Contenu affiché ci-dessous !").fadeOut(2000,function(){$('label[id=temp]').remove();});
							}
						}
					}
				);
			}
		);

	}
);
