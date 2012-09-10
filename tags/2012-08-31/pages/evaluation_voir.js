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
		$('table.form').tablesorter({ headers:{3:{sorter:false},4:{sorter:false}} });
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

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger le select f_eleve en ajax
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_eleve(groupe_id,groupe_type)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page=_maj_select_eleves',
					data : 'f_groupe='+groupe_id+'&f_type='+groupe_type+'&f_statut=1&f_multiple=0',
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#ajax_maj').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_maj').removeAttr("class").html("&nbsp;");
							$('#f_eleve').html(responseHTML).parent().show();
						}
						else
						{
							$('#ajax_maj').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

		$("#f_groupe").change
		(
			function()
			{
				// Pour un directeur ou un professeur, on met à jour f_eleve
				// Pour un élève ou un parent cette fonction n'est pas appelée puisque son groupe (masqué) ne peut être changé
				$("#f_eleve").html('<option value=""></option>').parent().hide();
				$('#ajax_msg').removeAttr("class").html('');
				$('#zone_eval_choix').hide();
				var groupe_id = $("#f_groupe").val();
				if(groupe_id)
				{
					groupe_type = $("#f_groupe option:selected").parent().attr('label');
					$('#ajax_maj').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
					maj_eleve(groupe_id,groupe_type);
				}
				else
				{
					$('#ajax_maj').removeAttr("class").html("&nbsp;");
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form');

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
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				$('#zone_eval_choix').hide();
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(jqXHR, textStatus, errorThrown)
		{
			$("#actualiser").prop('disabled',false);
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			initialiser_compteur();
			$("#actualiser").prop('disabled',false);
			if(responseHTML.substring(0,4)=='<tr>')
			{
				var position_script = responseHTML.lastIndexOf('<SCRIPT>');
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				$('table.form tbody').html( responseHTML.substring(0,position_script) );
				trier_tableau();
				infobulle();
				$('#zone_eval_choix h2').html($('#f_eleve option:selected').text());
				$('#zone_eval_choix').show();
				eval( responseHTML.substring(position_script+8) );
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Initialisation et chargement au changement d'élève (cas d'un parent responsable de plusieurs élèves)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_eval()
		{
			if($('#f_eleve option:selected').val())
			{
				formulaire.submit();
			}
			else
			{
				$('#ajax_msg').removeAttr("class").html('');
				$('#zone_eval_choix').hide();
			}
		}

		$('#f_eleve').change
		(
			function()
			{
				maj_eval();
			}
		);

		maj_eval();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Voir les notes saisies à un devoir ; retiré d'un fancybox afin de permettre d'utiliser ce fancybox pour une demande d'évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_eval_choix q.voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var td_id = $(this).parent().attr('id');
				var devoir_id = td_id.substr(7);
				var texte_info = $(this).parent().prev().prev().html();
				var texte_prof = $(this).parent().prev().prev().prev().html();
				var texte_date = $(this).parent().prev().prev().prev().prev().html();
				var date_fr    = texte_date.substring(17,texte_date.length); // enlever la date mysql cachée
				$('#titre_voir').html('Devoir du ' + date_fr + ' par ' + texte_prof + ' [ ' + texte_info + ' ]');
				$('#msg_voir').removeAttr("class").addClass("loader").html('Connexion au serveur&hellip;');
				$('#form , #zone_eval_choix').hide(0);
				$('#zone_eval_voir').show(0);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=Voir_notes&f_eleve='+$('#f_eleve option:selected').val()+'&f_devoir='+devoir_id,
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#msg_voir').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,4)!='<tr>')
							{
								$('#msg_voir').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#msg_voir').removeAttr("class").html('&nbsp;');
								$('#table_voir tbody').html(responseHTML);
								format_liens('#table_voir');
								trier_tableau2();
								infobulle();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Saisir les notes d'un devoir (auto-évaluation)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_eval_choix q.saisir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var td_id = $(this).parent().attr('id');
				var devoir_id = td_id.substr(7);
				var texte_info = $(this).parent().prev().prev().html();
				var texte_prof = $(this).parent().prev().prev().prev().html();
				var texte_date = $(this).parent().prev().prev().prev().prev().html();
				var date_fr    = texte_date.substring(17,texte_date.length); // enlever la date mysql cachée
				$('#zone_eval_choix q').hide();	// Pas afficher_masquer_images_action() à cause des <q> pour le choix d'une date
				$('#zone_eval_saisir').hide();
				new_label = '<label for="'+td_id+'" class="loader">Connexion au serveur&hellip;</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=Saisir_notes&f_eleve='+$('#f_eleve option:selected').val()+'&f_devoir='+devoir_id,
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$.fancybox( '<label class="alerte">'+'Echec de la connexion !'+'</label>' , {'centerOnScroll':true} );
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
								$('#titre_saisir').html('Devoir du ' + date_fr + ' par ' + texte_prof + ' [ ' + texte_info + ' ]');
								$('#report_date').html(tab_dates[devoir_id]);
								$('#fermer_zone_saisir').removeAttr("class").addClass("retourner").html('Retour');
								$('#msg_saisir').removeAttr("class").html("");
								$('#f_devoir').val(devoir_id);
								$('#table_saisir tbody').html(responseHTML);
								format_liens('#table_saisir');
								trier_tableau2();
								infobulle();
								$.fancybox( { 'href':'#zone_eval_saisir' , onStart:function(){$('#zone_eval_saisir').css("display","block");} , onClosed:function(){$('#zone_eval_saisir').css("display","none");} , 'margin':0 , 'modal':true , 'centerOnScroll':true } );
							}
							$('label[for='+td_id+']').remove();
							$('#zone_eval_choix q').show();
						}
					}
				);
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Réagir à la modification d'une note
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var modification = false;

		$("#table_saisir input[type=radio]").live
		('change',
			function()
			{
				modification = true;
				$('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
				$('#msg_saisir').removeAttr("class").html("");
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Clic sur le bouton pour fermer la zone servant à voir les acquisitions à une évaluation
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#fermer_zone_voir').click
		(
			function()
			{
				$('#zone_eval_voir').hide(0);
				$('#form , #zone_eval_choix').show(0);
				$('#table_voir tbody').html('<tr><td class="nu" colspan="4"></td></tr>');
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Clic sur le bouton pour fermer le formulaire servant à saisir les acquisitions à une évaluation
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#fermer_zone_saisir').click
		(
			function()
			{
				modification = false;
				$.fancybox.close();
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Clic sur le lien pour mettre à jour les acquisitions à une évaluation
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#Enregistrer_saisie').click // live est utilisé pour prendre en compte les nouveaux éléments créés
		(
			function()
			{
				if(modification==false)
				{
					$('#msg_saisir').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
				}
				else
				{
					$('button').prop('disabled',true);
					$('#msg_saisir').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
					// On ne risque pas de problème dû à une limitation du module "suhosin" pour un seul élève (nb champs envoyés = nb items + 1).
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'f_action=Enregistrer_saisies'+'&'+$('#zone_eval_saisir').serialize(),
							dataType : "html",
							error : function(jqXHR, textStatus, errorThrown)
							{
								$('button').prop('disabled',false);
								$('#msg_saisir').removeAttr("class").addClass("alerte").html('Echec de la connexion !');
								return false;
							},
							success : function(responseHTML)
							{
								initialiser_compteur();
								$('button').prop('disabled',false);
								if(responseHTML!='ok')
								{
									$('#msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									modification = false;
									$('#msg_saisir').removeAttr("class").addClass("valide").html("Saisies enregistrées !");
									$('#fermer_zone_saisir').removeAttr("class").addClass("retourner").html('Retour');
								}
							}
						}
					);
				}
			}
		);

	}
);

