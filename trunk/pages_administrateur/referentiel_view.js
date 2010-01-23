/**
 * @version $Id: referentiel_view.js 8 2009-10-30 20:56:02Z thomas $
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
				ref  = $(this).attr('id');
				niveau = $(this).parent().prev().prev().html();
				matiere = $(this).parent().parent().attr('lang');
				$('label').removeAttr("class").html('&nbsp;');
				$('label[for='+ref+']').removeAttr("class").addClass("loader").html('Demande envoyée... Veuillez patienter.');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'ref='+ref,
						dataType : "html",
						error : function(msg,string)
						{
							$('label[for='+ref+']').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,18)!='<ul class="ul_m1">')
							{
								$('label[for='+ref+']').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('label[for='+ref+']').removeAttr("class").addClass("valide").html("Contenu affiché ci-dessous !");
								$('#referentiel').html(responseHTML+'<p />');
								infobulle();
							}
						}
					}
				);
			}
		);

	}
);
