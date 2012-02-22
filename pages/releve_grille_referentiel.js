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
		//	Charger le select f_niveau en ajax (au changement de f_matiere et au départ)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_niveau()
		{
			$("#f_niveau").html('<option value=""></option>').hide();
			var matiere_val = $("#f_matiere").val();
			if(!matiere_val)
			{
				$('#ajax_maj_matiere').removeAttr("class").html("&nbsp;");
				return false;
			}
			$('#ajax_maj_matiere').removeAttr("class").addClass("loader").html("Actualisation en cours...");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page=_maj_select_niveaux',
					data : 'f_matiere='+matiere_val,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_maj_matiere').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_maj_matiere').removeAttr("class").html("&nbsp;");
							$('#f_niveau').html(responseHTML).show();
						}
					else
						{
							$('#ajax_maj_matiere').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}
		$("#f_matiere").change
		(
			function()
			{
				maj_niveau();
			}
		);
		maj_niveau();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger le select f_eleve en ajax (au changement de f_groupe)
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
						$('#ajax_maj_groupe').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_maj_groupe').removeAttr("class").html("&nbsp;");
							$('#f_eleve').html(responseHTML).show();
						}
					else
						{
							$('#ajax_maj_groupe').removeAttr("class").addClass("alerte").html(responseHTML);
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
					$('#ajax_maj_groupe').removeAttr("class").addClass("loader").html("Actualisation en cours...");
					maj_eleve(groupe_val,type);
				}
				else
				{
					$('#ajax_maj_groupe').removeAttr("class").html("&nbsp;");
				}
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger toutes les matières ou seulement les matières affectées (pour un prof)
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		var modifier_action = 'ajouter';
		$("#modifier_matiere").click
		(
			function()
			{
				$('button').prop('disabled',true);
				var matiere_id = $("#f_matiere option:selected").val();
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page=_maj_select_matieres_prof',
						data : 'f_matiere='+matiere_id+'&f_action='+modifier_action,
						dataType : "html",
						error : function(msg,string)
						{
							$('button').prop('disabled',false);
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								modifier_action = (modifier_action=='ajouter') ? 'retirer' : 'ajouter' ;
								$('#modifier_matiere').removeAttr("class").addClass("form_"+modifier_action);
								$('#f_matiere').html(responseHTML);
							}
							$('button').prop('disabled',false);
						}
					}
				);
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Soumettre le formulaire principal
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $("#form_select");

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_matiere       : { required:true },
					f_niveau        : { required:true },
					f_groupe        : { required:true },
					'f_eleve[]'     : { required:false },
					f_restriction   : { required:false },
					f_coef          : { required:false },
					f_socle         : { required:false },
					f_lien          : { required:false },
					f_cases_nb      : { required:true },
					f_cases_larg    : { required:true },
					f_remplissage   : { required:true },
					f_colonne_bilan : { required:true },
					f_colonne_vide  : { required:true },
					f_orientation   : { required:true },
					f_couleur       : { required:true },
					f_legende       : { required:true },
					f_marge_min     : { required:true }
				},
				messages :
				{
					f_matiere       : { required:"matière manquante" },
					f_niveau        : { required:"niveau manquant" },
					f_groupe        : { required:"classe/groupe manquant" },
					'f_eleve[]'     : { },
					f_restriction   : { },
					f_coef          : { },
					f_socle         : { },
					f_lien          : { },
					f_cases_nb      : { required:"nombre manquant" },
					f_cases_larg    : { required:"largeur manquante" },
					f_remplissage   : { required:"contenu manquant" },
					f_colonne_bilan : { required:"contenu manquant" },
					f_colonne_vide  : { required:"contenu manquant" },
					f_orientation   : { required:"orientation manquante" },
					f_couleur       : { required:"couleur manquante" },
					f_legende       : { required:"légende manquante" },
					f_marge_min     : { required:"marge mini manquante" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					if(element.attr("id")=='f_matiere') { element.next().after(error); }
					else {element.after(error);}
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
				// récupération du nom de la matière et du nom du niveau
				$('#f_matiere_nom').val( $("#f_matiere option:selected").text() );
				$('#f_niveau_nom').val( $("#f_niveau option:selected").text() );
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
				$('#bilan').html("&nbsp;");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Génération du relevé en cours...");
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
