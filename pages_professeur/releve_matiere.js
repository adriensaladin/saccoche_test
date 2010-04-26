/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://competences.sesamath.net> - Suivi d'Acquisitions de Compétences
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
		//	Afficher masquer des éléments du formulaire
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#f_type_individuel').click
		(
			function()
			{
				$("#options_individuel").toggle("slow");
			}
		);

		$('#f_type_bulletin').click
		(
			function()
			{
				$("#astuce_bulletin").toggle("slow");
			}
		);

		$('#f_bilan_ms , #f_bilan_pv').click
		(
			function()
			{
				if( ($('#f_bilan_ms').is(':checked')) || ($('#f_bilan_pv').is(':checked')) )
				{
					$('label[for=f_conv_sur20]').css('visibility','visible');
				}
				else
				{
					$('label[for=f_conv_sur20]').css('visibility','hidden');
				}
			}
		);

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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changement de groupe -> desactiver les périodes prédéfinies en cas de groue de besoin
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#f_groupe').change
		(
			function()
			{
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
								$(this).removeAttr('disabled');
							}
							// groupe de besoin -> desactiver les périodes prédéfinies
							else
							{
								$(this).attr('disabled',true);
							}
						}
					}
				);
				// Sélectionner si besion la période personnalisée
				if(groupe_type=='Besoins')
				{
					$("#f_periode option[value=0]").attr('selected',true);
					$("#dates_perso").attr("class","show");
				}
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Charger le select f_eleve en ajax
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		function maj_eleve(groupe_val,type)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier=_maj_select_eleves',
					data : 'f_groupe='+groupe_val+'&f_type='+type+'&f_statut=1',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_maj').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_maj').removeAttr("class").html('&nbsp;<span class="astuce">Utiliser "<i>Shift + clic</i>" ou "<i>Ctrl + clic</i>" pour une sélection multiple.</span>');
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
				if(groupe_val)
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
					f_orientation : { required:true },
					f_marge_min   : { required:true },
					f_couleur     : { required:true },
					f_cases_nb    : { required:true },
					f_cases_larg  : { required:true },
					f_cases_haut  : { required:true },
					f_type        : { required:true },
					f_coef        : { required:false },
					f_socle       : { required:false },
					f_lien        : { required:false },
					f_bilan_ms    : { required:false },
					f_bilan_pv    : { required:false },
					f_conv_sur20  : { required:false },
					f_periode     : { required:true },
					f_date_debut  : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
					f_date_fin    : { required:function(){return $("#f_periode").val()==0;} , dateITA:true },
					f_retroactif  : { required:true },
					f_matiere     : { required:true },
					f_groupe      : { required:true },
					f_eleve       : { required:true }
				},
				messages :
				{
					f_orientation : { required:"orientation manquante" },
					f_marge_min   : { required:"marge mini manquante" },
					f_couleur     : { required:"couleur manquante" },
					f_cases_nb    : { required:"nombre manquant" },
					f_cases_larg  : { required:"largeur manquante" },
					f_cases_haut  : { required:"hauteur manquante" },
					f_type        : { required:"type(s) manquant(s)" },
					f_coef        : { },
					f_socle       : { },
					f_lien        : { },
					f_bilan_ms    : { },
					f_bilan_pv    : { },
					f_conv_sur20  : { },
					f_periode     : { required:"période manquante" },
					f_date_debut  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_date_fin    : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_retroactif  : { required:"choix manquant" },
					f_matiere     : { required:"matière manquante" },
					f_groupe      : { required:"groupe manquant" },
					f_eleve       : { required:"élève(s) manquant(s)" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					if(element.is("select")) {element.after(error);}
					else if(element.attr("type")=="text") {element.next().after(error);}
					else if(element.attr("type")=="radio") {element.parent().next().after(error);}
					else if(element.attr("type")=="checkbox") {element.parent().next().next().after(error);}
				}
				// success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
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
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai copié le tableau dans un champ hidden...
				var f_eleve = new Array(); $("#f_eleve option:selected").each(function(){f_eleve.push($(this).val());});
				$('#eleves').val(f_eleve);
				// grouper les checkbox multiples => normalement pas besoin si name de la forme nom[], mais ça pose pb à jquery.validate.js d'avoir un id avec []
				// alors j'ai copié le tableau dans un champ hidden...
				var f_type = new Array(); $("input[name=f_type]:checked").each(function(){f_type.push($(this).val());});
				$('#types').val(f_type);
				// récupération du nom de la matière et du nom du groupe
				$('#f_matiere_nom').val( $("#f_matiere option:selected").text() );
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
				$('#f_submit').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Transmission du fichier en cours... Veuillez patienter.");
				$('#bilan').html('');
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('#f_submit').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$('#f_submit').show();
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
