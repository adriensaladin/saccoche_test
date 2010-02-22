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

// jQuery !
$(document).ready
(
	function()
	{

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Voir un référentiel de compétences
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.voir').click
		(
			function()
			{
				ids = $(this).parent().attr('id');
				afficher_masquer_images_action('hide');
				new_label = '<label for="'+ids+'" class="loader">Demande envoyée... Veuillez patienter.</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'ids='+ids,
						dataType : "html",
						error : function(msg,string)
						{
							$('label[for='+ids+']').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.').fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,18)!='<ul class="ul_m1">')
							{
								$('label[for='+ids+']').removeAttr("class").addClass("alerte").html(responseHTML).fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
							}
							else
							{
								$('#referentiel').html(responseHTML+'<p />');
								infobulle();
								$('label[for='+ids+']').removeAttr("class").addClass("valide").html("Contenu affiché ci-dessous !").fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
							}
						}
					}
				);
			}
		);

	}
);
