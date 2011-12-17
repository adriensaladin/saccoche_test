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

		//	Initialisation

		$("#f_eleve").hide();

		//	Rechercher automatiquement la meilleure période et le niveau du groupe au chargement de la page (uniquement pour un élève, seul cas où la classe est préselectionnée)
		var groupe_id   = $('#f_groupe option:selected').val();
		var groupe_type = $("#f_groupe option:selected").parent().attr('label');
		selectionner_periode_adaptee();
		reporter_niveau_groupe();

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
		//	Afficher masquer des éléments du formulaire
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

		function view_dates_perso()
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

		$('#f_periode').change
		(
			function()
			{
				view_dates_perso();
				autoperiode = false;
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Changement de groupe
		//	-> choisir automatiquement la meilleure période si un changement manuel de période n'a jamais été effectué
		//	-> afficher le formulaire de périodes s'il est masqué
		//	-> choisir automatiquement le niveau du groupe associé
		//	-> afficher le formulaire des options s'il est masqué
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function selectionner_periode_adaptee()
		{
			if(typeof(tab_groupe_periode[groupe_id])!='undefined')
			{
				for(var periode_id in tab_groupe_periode[groupe_id]) // Parcourir un tableau associatif...
				{
					var tab_split = tab_groupe_periode[groupe_id][periode_id].split('_');
					if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
					{
						$("#f_periode option[value="+periode_id+"]").prop('selected',true);
						view_dates_perso();
						break;
					}
				}
			}
		}

		function reporter_niveau_groupe()
		{
			if(typeof(tab_groupe_niveau[groupe_id])!='undefined')
			{
				$('#f_niveau').val(tab_groupe_niveau[groupe_id][0]);
				$('#niveau_nom').html(tab_groupe_niveau[groupe_id][1]);
			}
		}

		$('#f_groupe').change
		(
			function()
			{
				// remettre à jour ces deux valeurs
				groupe_id   = $('#f_groupe option:selected').val();
				groupe_type = $("#f_groupe option:selected").parent().attr('label');
				$("#f_periode option").each
				(
					function()
					{
						periode_id = $(this).val();
						// La période personnalisée est tout le temps accessible
						if(periode_id!=0)
						{
							// classe ou groupe classique -> toutes périodes accessibles
							if(groupe_type!='Besoins')
							{
								$(this).prop('disabled',false);
							}
							// groupe de besoin -> desactiver les périodes prédéfinies
							else
							{
								$(this).prop('disabled',true);
							}
						}
					}
				);
				// Sélectionner si besoin la période personnalisée
				if(groupe_type=='Besoins')
				{
					$("#f_periode option[value=0]").prop('selected',true);
					$("#dates_perso").attr("class","show");
				}
				// Modification automatique du formulaire : périodes
				if(autoperiode)
				{
					if( (typeof(groupe_type)!='undefined') && (groupe_type!='Besoins') )
					{
						// Rechercher automatiquement la meilleure période
						selectionner_periode_adaptee();
					}
					// Afficher la zone de choix des périodes
					if(typeof(groupe_type)!='undefined')
					{
						$('#zone_periodes').removeAttr("class");
					}
					else
					{
						$('#zone_periodes').addClass("hide");
					}
				}
				// Modification automatique du formulaire : niveau du groupe
				if(groupe_type=='Besoins')
				{
					$('#f_restriction_niveau').prop('disabled',true);
				}
				else
				{
					$('#f_restriction_niveau').prop('disabled',false);
				}
				if(groupe_id)
				{
					reporter_niveau_groupe();
					// Afficher la zone des options
					if($('#zone_options').hasClass("hide"))
					{
						$('#zone_options').removeAttr("class");
					}
				}
				else
				{
					$('#zone_options').addClass("hide");
				}
			}
		);

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
					data : 'f_groupe='+groupe_id+'&f_type='+groupe_type+'&f_statut=1',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_maj').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
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
				// Pour un directeur ou un professeur, on met à jour f_eleve
				// Pour un élève cette fonction n'est pas appelée puisque son groupe (masqué) ne peut être changé
				$("#f_eleve").html('<option value=""></option>').hide();
				var groupe_id = $("#f_groupe").val();
				if(groupe_id)
				{
					groupe_type = $("#f_groupe option:selected").parent().attr('label');
					$('#ajax_maj').removeAttr("class").addClass("loader").html("Actualisation en cours...");
					maj_eleve(groupe_id,groupe_type);
				}
				else
				{
					$('#ajax_maj').removeAttr("class").html("&nbsp;");
				}
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Soumettre le formulaire principal
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $("#form_select");

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
					f_groupe             : { required:true },
					'f_eleve[]'          : { required:true },
					f_periode            : { required:true },
					f_date_debut         : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
					f_date_fin           : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
					f_retroactif         : { required:true },
					f_coef               : { required:false },
					f_socle              : { required:false },
					f_lien               : { required:false },
					f_restriction_socle  : { required:false },
					f_restriction_niveau : { required:false },
					f_couleur            : { required:true },
					f_legende            : { required:true }
				},
				messages :
				{
					f_groupe             : { required:"groupe manquant" },
					'f_eleve[]'          : { required:"élève(s) manquant(s)" },
					f_periode            : { required:"période manquante" },
					f_date_debut         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_date_fin           : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_retroactif         : { required:"choix manquant" },
					f_coef               : { },
					f_socle              : { },
					f_lien               : { },
					f_restriction_socle  : { },
					f_restriction_niveau : { },
					f_couleur            : { required:"couleur manquante" },
					f_legende            : { required:"légende manquante" }
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
				// récupération du nom du groupe
				$('#f_groupe_nom').val( $("#f_groupe option:selected").text() );
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
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Génération du relevé en cours...");
				$('#bilan').html('');
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('button').prop('disabled',false);
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			initialiser_compteur();
			$('button').prop('disabled',false);
			if(responseHTML.substring(0,6)=='<hr />')
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Terminé : voir ci-dessous.");
				$('#bilan').html(responseHTML);
				format_liens('#bilan');
				infobulle();
			}
			else if(responseHTML.substring(0,17)=='<ul class="puce">')
			{
				$('#ajax_msg').removeAttr("class").html('');
				// Mis dans le div bilan et pas balancé directement dans le fancybox sinon le format_lien() nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
				// Malgré tout, pour Chrome par exemple, la largeur est mal clculée et provoque des retours à la ligne, d'où le minWidth ajouté.
				$('#bilan').html(responseHTML);
				format_liens('#bilan');
				$.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minWidth':400 } );
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

	}
);
