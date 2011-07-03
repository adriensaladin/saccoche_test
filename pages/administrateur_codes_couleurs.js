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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Reporter dans les input equiv_txt les valeurs préféfinies lors du clic sur un bouton radio (jeu de symboles colorés).
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('table.simulation input[type=radio]').click
		(
			function()
			{
				var note_nom = $(this).val();
				$('#note_texte_RR').val( tab_notes_txt[note_nom]['RR'] );
				$('#note_texte_R').val( tab_notes_txt[note_nom]['R'] );
				$('#note_texte_V').val( tab_notes_txt[note_nom]['V'] );
				$('#note_texte_VV').val( tab_notes_txt[note_nom]['VV'] );
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Activation du colorpicker pour les 3 champs input.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var f = $.farbtastic('#colorpicker');
		$('div.colorpicker input.stretch').focus
		(
			function()
			{
				$('#colorpicker').removeAttr("class");
				f.linkTo(this);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Reporter dans un input colorpicker une valeur préféfinie lors du clic sur un bouton (couleur de fond).
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('div.colorpicker button').click
		(
			function()
			{
				$( '#acquis_'+$(this).attr('name') ).val( $(this).val() ).focus();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Affecter aux div la même couleur de fond que celle du input.
//	Utilisation d'un test en boucle car un simple test change() ne fonctionne pas.
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function reporter_couleur()
		{
			$("body").everyTime
			('1ds', 'report', function()
				{
					$('div.colorpicker input.stretch').each
					(
						function()
						{
							$(this).parent().parent().css('backgroundColor',$(this).val());
						}
					);
				}
			);
		}
		reporter_couleur();

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du premier formulaire
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire1 = $('#form_notes');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation1 = formulaire1.validate
		(
			{
				rules :
				{
					note_image_style  : { required:true },
					note_texte_RR     : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					note_texte_R      : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					note_texte_V      : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					note_texte_VV     : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					note_legende_RR   : { required:true , maxlength:40 },
					note_legende_R    : { required:true , maxlength:40 },
					note_legende_V    : { required:true , maxlength:40 },
					note_legende_VV   : { required:true , maxlength:40 }
				},
				messages :
				{
					note_image_style  : { required:"codes de couleur manquant" },
					note_texte_RR     : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					note_texte_R      : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					note_texte_V      : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					note_texte_VV     : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					note_legende_RR   : { required:"texte manquant" , maxlength:"40 caractères maximum" },
					note_legende_R    : { required:"texte manquant" , maxlength:"40 caractères maximum" },
					note_legende_V    : { required:"texte manquant" , maxlength:"40 caractères maximum" },
					note_legende_VV   : { required:"texte manquant" , maxlength:"40 caractères maximum" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					if(element.attr("type")=="radio") { $('#ajax_msg_note_symbole').html(error); }
					else {element.parent().append(error);}
				}
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions1 =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg_notes",
			beforeSubmit : test_form_avant_envoi1,
			error : retour_form_erreur1,
			success : retour_form_valide1
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire1.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions1);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi1(formData, jqForm, options)
		{
			$('#ajax_msg_notes').removeAttr("class").html("&nbsp;");
			var readytogo = validation1.form();
			if(readytogo)
			{
				$("#bouton_valider_notes").prop('disabled',true);
				$('#ajax_msg_notes').removeAttr("class").addClass("loader").html("Traitement de la demande en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur1(msg,string)
		{
			$("#bouton_valider_notes").prop('disabled',false);
			$('#ajax_msg_notes').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide1(responseHTML)
		{
			initialiser_compteur();
			$("#bouton_valider_notes").prop('disabled',false);
			if(responseHTML=='ok')
			{
				$('#ajax_msg_notes').removeAttr("class").addClass("valide").html("Choix mémorisés !");
			}
			else
			{
				$('#ajax_msg_notes').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 


		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du second formulaire
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire2 = $('#form_acquis');

		// Ajout d'une méthode pour vérifier le format hexadécimal
		jQuery.validator.addMethod
		(
			"hexa_format", function(value, element)
			{
				return this.optional(element) || ( (/^\#[0-9a-f]{3,6}$/i.test(value)) && (value.length!=5) && (value.length!=6) ) ;
			}
			, "format incorrect"
		); 

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation2 = formulaire2.validate
		(
			{
				rules :
				{
					acquis_texte_NA   : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					acquis_texte_VA   : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					acquis_texte_A    : { required:true , maxlength:3 }, // restriction alphabétique non imposée pour permettre - + etc.
					acquis_legende_NA : { required:true , maxlength:40 },
					acquis_legende_VA : { required:true , maxlength:40 },
					acquis_legende_A  : { required:true , maxlength:40 },
					acquis_color_NA   : { required:true , hexa_format:true },
					acquis_color_VA   : { required:true , hexa_format:true },
					acquis_color_A    : { required:true , hexa_format:true }
				},
				messages :
				{
					acquis_texte_NA   : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					acquis_texte_VA   : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					acquis_texte_A    : { required:"texte manquant" , maxlength:"3 caractères maximum" },
					acquis_legende_NA : { required:"texte manquant" , maxlength:"40 caractères maximum" },
					acquis_legende_VA : { required:"texte manquant" , maxlength:"40 caractères maximum" },
					acquis_legende_A  : { required:"texte manquant" , maxlength:"40 caractères maximum" },
					acquis_color_NA   : { required:"couleur manquante" , hexa_format:"format incorrect" },
					acquis_color_VA   : { required:"couleur manquante" , hexa_format:"format incorrect" },
					acquis_color_A    : { required:"couleur manquante" , hexa_format:"format incorrect" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					element.parent().append(error);
				}
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions2 =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg_acquis",
			beforeSubmit : test_form_avant_envoi2,
			error : retour_form_erreur2,
			success : retour_form_valide2
		};

		// Envoi du formulaire (avec jquery.form.js)
    formulaire2.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions2);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi2(formData, jqForm, options)
		{
			$('#ajax_msg_acquis').removeAttr("class").html("&nbsp;");
			var readytogo = validation2.form();
			if(readytogo)
			{
				$("#bouton_valider_acquis").prop('disabled',true);
				$('#ajax_msg_acquis').removeAttr("class").addClass("loader").html("Traitement de la demande en cours... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur2(msg,string)
		{
			$("#bouton_valider_acquis").prop('disabled',false);
			$('#ajax_msg_acquis').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez valider de nouveau.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide2(responseHTML)
		{
			initialiser_compteur();
			$("#bouton_valider_acquis").prop('disabled',false);
			if(responseHTML=='ok')
			{
				$('#ajax_msg_acquis').removeAttr("class").addClass("valide").html("Choix mémorisés !");
			}
			else
			{
				$('#ajax_msg_acquis').removeAttr("class").addClass("alerte").html(responseHTML);
			}
		} 

	}
);
