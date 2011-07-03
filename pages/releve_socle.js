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

		// Initialisation
		$("#f_eleve").hide();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Enlever le message ajax et le résultat précédent au changement d'un select
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('select').change
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#bilan').html("&nbsp;");
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Afficher masquer des options de la grille
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#f_groupe').change
		(
			function()
			{
				var groupe_val = $("#f_groupe").val();
				if(groupe_val!='0')
				{
					$("#option_groupe").show("slow");
				}
				else
				{
					$("#option_groupe").hide("slow");
				}
			}
		);

		$('#f_mode_auto').click
		(
			function()
			{
				$("#div_matiere").hide();
			}
		);

		$('#f_mode_manuel').click
		(
			function()
			{
				$("#div_matiere").show();
			}
		);

//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
//	Charger le select f_pilier en ajax
//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		var maj_pilier = function()
		{
			$("#f_pilier").html('<option value=""></option>').hide();
			palier_id = $("#f_palier").val();
			if(palier_id)
			{
				$('#ajax_maj_pilier').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page=_maj_select_piliers',
						data : 'f_palier='+palier_id+'&f_first='+'non',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_maj_pilier').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								$('#ajax_maj_pilier').removeAttr("class").html('&nbsp;');
								$('#f_pilier').html(responseHTML).attr('size',$('#f_pilier option').size()).show();
							}
							else
							{
								$('#ajax_maj_pilier').removeAttr("class").addClass("alerte").html(responseHTML);
							}
						}
					}
				);
			}
			else
			{
				$('#ajax_maj_pilier').removeAttr("class").html("&nbsp;");
			}
		};

		$("#f_palier").change( maj_pilier );

		maj_pilier();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger le select f_eleve en ajax
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_eleve(groupe_val,type)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page=_maj_select_eleves',
					data : 'f_groupe='+groupe_val+'&f_type='+type+'&f_statut=1',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_maj').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_maj').removeAttr("class").html("&nbsp;");
							$('#f_eleve').html(responseHTML).show();
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
				$("#f_eleve").html('<option value=""></option>').hide();
				var groupe_val = $("#f_groupe").val();
				if(groupe_val!='0')
				{
					type = $("#f_groupe option:selected").parent().attr('label');
					$('#ajax_maj').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
					maj_eleve(groupe_val,type);
				}
				else
				{
					$('#ajax_maj').removeAttr("class").html("&nbsp;");
				}
			}
		);

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $("#form_select");

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_palier  : { required:true },
					f_pilier  : { required:true },
					f_groupe  : { required:true },
					f_eleve   : { required:true },
					f_mode    : { required:true },
					f_matiere : { required:function(){return $('#f_mode_manuel').is(':checked');} },
					f_coef    : { required:false },
					f_socle   : { required:false },
					f_lien    : { required:false }
				},
				messages :
				{
					f_palier  : { required:"palier manquant" },
					f_pilier  : { required:"compétence(s) manquante(s)" },
					f_groupe  : { required:"groupe manquant" },
					f_eleve   : { required:"élève(s) manquant(s)" },
					f_mode    : { required:"choix manquant" },
					f_matiere : { required:"matiere(s) manquant(e)" },
					f_coef    : { },
					f_socle   : { },
					f_lien    : { }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					if(element.is("select")) {element.after(error);}
					else if(element.attr("type")=="text") {element.next().after(error);}
					else if(element.attr("type")=="radio") {element.parent().next().after(error);}
					else if(element.attr("type")=="checkbox") {element.parent().next().after(error);}
				}
				// success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
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
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai copié le tableau dans un champ hidden...
				var f_eleve = new Array(); $("#f_eleve option:selected").each(function(){f_eleve.push($(this).val());});
				$('#eleves').val(f_eleve);
				var tab_pilier = new Array(); $("#f_pilier option:selected").each(function(){tab_pilier.push($(this).val());});
				$('#piliers').val(tab_pilier);
				if($('#f_mode_manuel').is(':checked'))
				{
					var tab_matiere = new Array(); $("#f_matiere option:selected").each(function(){tab_matiere.push($(this).val());});
					$('#matieres').val(tab_matiere);
				}
				// récupération du nom du palier
				$('#f_palier_nom').val( $("#f_palier option:selected").text() );
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
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Génération du relevé en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('button').prop('disabled',false);
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			initialiser_compteur();
			$('button').prop('disabled',false);
			if(responseHTML.substring(0,17)!='<ul class="puce">')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				$('#bilan').html(responseHTML);
				format_liens('#bilan');
			}
		} 

	}
);
