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

		// tri du 1er tableau (avec jquery.tablesorter.js).
		var sorting = [[0,1]];
		$('table.form').tablesorter({ headers:{3:{sorter:false}} });
		function trier_tableau()
		{
			if($('table.form tbody tr td').length>1)
			{
				$('table.form').trigger('update');
				$('table.form').trigger('sorton',[sorting]);
			}
		}
		trier_tableau();

		// tri du 2nd tableau (avec jquery.tablesorter.js).
		$('#table_voir').tablesorter();
		function trier_tableau2()
		{
			if($('table.form tbody tr td').length>1)
			{
				$('#table_voir').trigger('update');
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form');

		// Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
		jQuery.validator.addMethod
		(
			"dateITA",
			function(value, element)
			{
				var check = false;
				var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/ ;
				if( re.test(value))
				{
					var adata = value.split('/');
					var gg = parseInt(adata[0],10);
					var mm = parseInt(adata[1],10);
					var aaaa = parseInt(adata[2],10);
					var xdata = new Date(aaaa,mm-1,gg);
					if ( ( xdata.getFullYear() == aaaa ) && ( xdata.getMonth () == mm - 1 ) && ( xdata.getDate() == gg ) )
						check = true;
					else
						check = false;
				}
				else
					check = false;
				return this.optional(element) || check;
			}, 
			"Veuillez entrer une date correcte."
		);

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_eleve      : { required:true },
					f_date_debut : { required:true , dateITA:true },
					f_date_fin   : { required:true , dateITA:true }
				},
				messages :
				{
					f_eleve      : { required:"élève manquant" },
					f_date_debut : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_date_fin   : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { $('#ajax_msg').after(error); }
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_avant_envoi,
			error : retour_form_erreur,
			success : retour_form_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				$("#actualiser").prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Soumission du formulaire en cours... Veuillez patienter.");
				$('#zone_eval_choix').hide();
				$('#zone_eval_detail').hide();
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$("#actualiser").prop('disabled',false);
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			initialiser_compteur();
			$("#actualiser").prop('disabled',false);
			if(responseHTML.substring(0,4)=='<tr>')
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				$('table.form tbody').html(responseHTML);
				trier_tableau();
				infobulle();
				$('#zone_eval_choix h2').html($('#f_eleve option:selected').text());
				$('#zone_eval_choix').show();
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Initialisation et chargement au changement d'élève (cas d'un parent responsable de plusieurs élèves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_eleve()
		{
			if($('#f_eleve option:selected').val())
			{
				formulaire.submit();
			}
			else
			{
				$('#ajax_msg').removeAttr("class").html('');
				$('#zone_eval_choix').hide();
				$('#zone_eval_detail').hide();
			}
		}

		$('#f_eleve').change
		(
			function()
			{
				maj_eleve();
			}
		);

		maj_eleve();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Voir les notes saisies à un devoir
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('table.form q.voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				td_id = $(this).parent().attr('id');
				devoir_id = td_id.substr(7);
				texte_info = $(this).parent().prev().html();
				texte_prof = $(this).parent().prev().prev().html();
				texte_date = $(this).parent().prev().prev().prev().html();
				texte_date = texte_date.substring(17,texte_date.length); // enlever la date mysql cachée
				$('#zone_eval_choix q').hide();	// Pas afficher_masquer_images_action() à cause des <q> pour le choix d'une date
				$('#zone_eval_detail').hide();
				new_label = '<label for="'+td_id+'" class="loader">Demande envoyée... Veuillez patienter.</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=Voir_notes&f_eleve='+$('#f_eleve option:selected').val()+'&f_devoir='+devoir_id,
						dataType : "html",
						error : function(msg,string)
						{
							$.fancybox( '<label class="alerte">'+'Echec de la connexion ! Veuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
							$('label[for='+td_id+']').remove();
							$('#zone_eval_choix q').show();
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,4)!='<tr>')
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
							}
							else
							{
								$('#titre_voir').html('Devoir du ' + texte_date + ' par ' + texte_prof + ' [ ' + texte_info + ' ]');
								$('#table_voir tbody').html(responseHTML);
								format_liens('#table_voir');
								trier_tableau2();
								infobulle();
								$.fancybox( { 'href':'#zone_eval_detail' , onStart:function(){$('#zone_eval_detail').css("display","block");} , onClosed:function(){$('#zone_eval_detail').css("display","none");} , 'centerOnScroll':true } );
							}
							$('label[for='+td_id+']').remove();
							$('#zone_eval_choix q').show();
						}
					}
				);
			}
		);

	}
);

