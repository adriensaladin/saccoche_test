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
// Réagir au clic dans un select multiple
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('select[multiple]').click
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#ajax_retour').html("&nbsp;");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('input').click
		(
			function()
			{
				id = $(this).attr('id');
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai remplacé le $("form").serialize() par les tableaux maison et mis un explode dans le fichier ajax
				if( $("#select_professeurs_directeurs option:selected").length==0 )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez au moins un professeur ou un directeur !");
					return(false);
				}
				else
				{
					var select_users = new Array(); $("#select_professeurs_directeurs option:selected").each(function(){select_users.push($(this).val());});
				}
				// On demande confirmation
				quoi = (id=='prof_login') ? "nom d'utilisateur" : "mot de passe" ;
				continuer = (confirm("Attention : vous devrez communiquer les nouveaux codes !\nConfirmez-vous l'initialisation du "+quoi+" des professeurs et/ou directeurs sélectionnés ?")) ? true : false ;
				if(continuer)
				{
					$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER+'&action='+id,
							data : 'select_users=' + select_users,
							dataType : "html",
							error : function(msg,string)
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								if(responseHTML.substring(0,6)!='<hr />')
								{
									$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
									$('#ajax_retour').html(responseHTML);
									format_liens('#ajax_retour');
								}
							}
						}
					);
				}
			}
		);

	}
);
