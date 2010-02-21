/**
 * @version $Id: eval_demande.js 8 2009-10-30 20:56:02Z thomas $
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

		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[3,1],[2,0]];
		$('table.form').tablesorter({ headers:{0:{sorter:false}} });
		function trier_tableau()
		{
			if($('table.form tbody tr').length)
			{
				$('table.form').trigger('update');
				$('table.form').trigger('sorton',[sorting]);
			}
		}
		trier_tableau();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du premier formulaire pour afficher le tableau avec la liste des demandes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		//	Afficher masquer des options du formulaire

		$('#f_periode').change
		(
			function()
			{
				var periode_val = $("#f_periode").val();
				if(periode_val!=0)
				{
					$("#dates_perso").attr("class","hide");
				}
				else
				{
					$("#dates_perso").attr("class","show");
				}
			}
		);

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire0 = $('#form0');

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
		var validation0 = formulaire0.validate
		(
			{
				rules :
				{
					f_matiere : { required:true },
					f_groupe  : { required:true }
				},
				messages :
				{
					f_matiere : { required:"matière manquante" },
					f_groupe  : { required:"classe / groupe manquant" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					if(element.is("select")) {element.after(error);}
					else if(element.attr("type")=="text") {element.next().after(error);}
				}
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions0 =
		{
			type : 'POST',
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg0",
			beforeSubmit : test_form_avant_envoi0,
			error : retour_form_erreur0,
			success : retour_form_valide0
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire0.submit
		(
			function()
			{
				// Mémoriser le nom de la matière + le type de groupe + le nom du groupe
				$('#f_matiere_nom').val( $("#f_matiere option:selected").text() );
				$("#f_groupe_nom").val(  $("#f_groupe option:selected").text() );
				$("#f_groupe_type").val( $("#f_groupe option:selected").parent().attr('label') );
				$(this).ajaxSubmit(ajaxOptions0);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi0(formData, jqForm, options)
		{
			$('#ajax_msg0').removeAttr("class").html("&nbsp;");
			var readytogo = validation0.form();
			if(readytogo)
			{
				$('#ajax_msg0').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur0(msg,string)
		{
			$('#ajax_msg0').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide0(responseHTML)
		{
			maj_clock(1);
			if( (responseHTML.substring(0,3)!='<tr') && (responseHTML!='') )
			{
				$('#ajax_msg0').removeAttr("class").addClass("alerte").html(responseHTML);
				$("#zone_actions").hide("slow");
			}
			else
			{
				$('#ajax_msg0').removeAttr("class").addClass("valide").html("Demande réalisée !");
				$('table.form tbody').html(responseHTML);
				trier_tableau();
				infobulle();
				$("#f_qui option[value=groupe]").text($("#f_groupe_nom").val());
				$("#zone_actions").show("slow");
			}
		}

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Afficher masquer des éléments du formulaire
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#f_quoi').change
		(
			function()
			{
				quoi = $("#f_quoi option:selected").val();
				if( (quoi=='creer') || (quoi=='completer') ) {$("#step_qui").show("slow");}       else {$("#step_qui").hide("slow");}
				if(quoi=='creer')                            {$("#step_creer").show("slow");}     else {$("#step_creer").hide("slow");}
				if(quoi=='completer')                        {$("#step_completer").show("slow");} else {$("#step_completer").hide("slow");}
				if( (quoi=='creer') || (quoi=='completer') ) {$("#step_suite").show("slow");}     else {$("#step_suite").hide("slow");}
				if(quoi!='')                                 {$("#step_valider").show("slow");}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire principal
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form1');

		// Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
		// méthode dateITA déjà ajoutée

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_date         : { required:true , dateITA:true },
					f_groupe       : { required:true },
					f_info         : { required:false , maxlength:60 },
					f_compet_liste : { required:true }
				},
				messages :
				{
					f_date         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_groupe       : { required:"groupe manquant" },
					f_info         : { maxlength:"60 caractères maximum" },
					f_compet_liste : { required:"item(s) manquant(s)" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { $('#ajax_msg').after(error); }
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
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
				$('#ajax_msg').parent().children('q').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('#ajax_msg').parent().children('q').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$('#ajax_msg').parent().children('q').show();
			if(responseHTML.substring(0,1)!='<')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				action = $('#f_action').val();
				switch (action)
				{
					case 'ajouter':
					case 'dupliquer':
						groupe_id = $("#f_groupe option:selected").val();
						new_td  = responseHTML.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupes[groupe_id]+'</td>');
						new_tr  = '<tr class="new">'+new_td+'</tr>';
						$('table.form tbody').append(new_tr);
						$('q.valider').parent().parent().remove();
						break;
					case 'modifier':
						groupe_id = $("#f_groupe option:selected").val();
						new_td  = responseHTML.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupes[groupe_id]+'</td>');
						$('q.valider').parent().parent().prev().addClass("new").html(new_td).show();
						$('q.valider').parent().parent().remove();
						break;
					case 'supprimer':
						$('q.valider').parent().parent().parent().remove();
						break;
				}
				trier_tableau();
				$('#form0').css('visibility','visible');
				infobulle();
			}
		} 

	}
);

