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

		var f_action = '';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au changement dans le premier formulaire (choix principal)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#f_choix_principal").change
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html('&nbsp;');
				// Masquer tout
				$('#fieldset_sconet_eleves_non , #fieldset_sconet_eleves_oui , #fieldset_sconet_parents_non , #fieldset_sconet_parents_oui , #fieldset_sconet_professeurs_directeurs_non , #fieldset_sconet_professeurs_directeurs_oui , #fieldset_base-eleves_eleves , #fieldset_tableur_eleves , #fieldset_tableur_professeurs_directeurs').hide(0);
				// Puis afficher ce qu'il faut
				f_action = $(this).val();
				if(f_action!='')
				{
					$('#fieldset_'+f_action).show();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le lien pour revenir au formulaire principal
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#bouton_annuler').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#form1').show();
				$('#form2').html('<hr /><label id="ajax_msg">&nbsp;</label>');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Éléments dynamiques du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Tout cocher ou tout décocher
		$('input[name=all_check]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).parent().parent().parent().find('input[type=checkbox]').prop('checked',true);
				return false;
			}
		);
		$('input[name=all_uncheck]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).parent().parent().parent().find('input[type=checkbox]').prop('checked',false);
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// depart -> step10     Réagir au clic sur un bouton pour envoyer un import (quel qu'il soit)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Envoi du fichier avec jquery.ajaxupload.js
		// Attention, la variable f_action n'est pas accessible dans les AjaxUpload
		new AjaxUpload
		('#sconet_eleves',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'f_step':10,'f_action':'sconet_eleves_oui'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_sconet,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#sconet_parents',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'f_step':10,'f_action':'sconet_parents_oui'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_sconet,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#sconet_professeurs_directeurs',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'f_step':10,'f_action':'sconet_professeurs_directeurs_oui'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_sconet,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#base-eleves_eleves',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'f_step':10,'f_action':'base-eleves_eleves'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_tableur,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#tableur_eleves',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'f_step':10,'f_action':'tableur_eleves'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_tableur,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#tableur_professeurs_directeurs',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'f_step':10,'f_action':'tableur_professeurs_directeurs'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_tableur,
				onComplete: retourner_fichier
			}
		);

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('#ajax_msg').removeAttr("class").html('&nbsp;');
			$('#ajax_retour').html("&nbsp;");
			return true;
		}

		function verifier_fichier_sconet(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "xml" ou "zip".');
				return false;
			}
			else
			{
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function verifier_fichier_tableur(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
				return false;
			}
			else
			{
				$('button').prop('disabled',true);
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			$('button').prop('disabled',false);
			if( (responseHTML.substring(0,14)!='<ul id="step">') && (responseHTML.substring(0,12)!='<UL id=step>') )
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
				alert(responseHTML.substring(0,12));
			}
			else
			{
				maj_clock(1);
				var texte1 = $('#f_choix_principal option:selected').parent('optgroup').attr('label');
				var texte2 = $('#f_choix_principal option:selected').text();
				$('#form1').hide();
				$('#form2').html('<p><input name="report_objet" readonly size="80" value="'+texte1.substring(0,texte1.indexOf('(')-1)+' &rarr; '+texte2.substring(0,texte2.indexOf('(')-1)+'" class="b" /> <button id="bouton_annuler"><img alt="" src="./_img/bouton/retourner.png" /> Annuler / Retour</button></p>'+responseHTML);
				$("#step1").addClass("on");
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// step10 -> step20                              Passer à l'extraction des données
// step20 -> step31                              Passer à l'analyse des données des classes
// step32 -> step41                              Passer à l'analyse des données des groupes
// step20 | step32 | step42 -> step51            Passer à l'analyse des données des utilisateurs
// step52 | step53 -> step61                     Passer aux ajouts d'affectations éventuelles (Sconet uniquement)
// step52 | step53 -> step71                     Passer aux adresses des parents
// step72 -> step81                              Passer aux liens de responsabilité des parents
// step52 | step53 | step62 | step82 -> step90   Nettoyage des fichiers temporaires
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#passer_etape_suivante').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var li_step = $(this).attr('href').substring(5,6);
				var f_step  = $(this).attr('href').substring(5);
				$("#step li").removeAttr("class");
				$('#form2 fieldset table').remove();
				$("#step"+li_step).addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step='+f_step+'&f_action='+f_action,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,25)!='<p><label class="valide">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form2 fieldset').html(responseHTML);
								infobulle();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// step31 -> step32     Envoyer les actions sur les classes
// step41 -> step42     Envoyer les actions sur les groupes
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#envoyer_infos_regroupements').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				nb_pb = 0;
				$("select option:selected").each
				(
					function()
					{
						if($(this).val()=="")
						{
							nb_pb++;
						}
					}
				);
				$("input[id^=add_nom_]").each
				(
					function()
					{
						if($(this).val()=="")
						{
							nb_pb++;
						}
					}
				);
				if(nb_pb)
				{
					$s = (nb_pb>1) ? 's' : '';
					$('#ajax_msg').removeAttr("class").addClass("erreur").html('Il reste '+nb_pb+' élément'+$s+' de formulaire à compléter.');
					return false;
				}
				else
				{
					var f_step = $(this).attr('href').substring(5);
					$('#form2 fieldset table').hide(0);
					$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'f_step='+f_step+'&f_action='+f_action+'&'+$("#form2").serialize(),
							data : 'f_step='+f_step+'&f_action='+f_action+'&'+$("#form2").serialize(),
							dataType : "html",
							error : function(msg,string)
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								if(responseHTML.substring(0,25)!='<p><label class="valide">')
								{
									$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									$('#ajax_msg').removeAttr("class").html('&nbsp;');
									$('#form2 fieldset').html(responseHTML);
								}
							}
						}
					);
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// step51 -> step52     Envoyer les actions sur les utilisateurs
// step61 -> step62     Envoyer les actions sur les ajouts d'affectations éventuelles (Sconet uniquement)
// step71 -> step72     Envoyer les actions sur les ajouts d'affectations éventuelles (Sconet uniquement)
// step81 -> step82     Envoyer les modifications éventuelles sur les liens de responsabilité des parents (Sconet uniquement)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#envoyer_infos_utilisateurs').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var f_step = $(this).attr('href').substring(5);
				// Grouper les checkbox dans un champ unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque).
				var f_check = new Array();
				$("#form2 input:checked").each
				(
					function()
					{
						f_check.push($(this).attr('id'));
					}
				);
				$('#form2 fieldset table').hide(0);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step='+f_step+'&f_action='+f_action+'&f_check='+f_check,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,25)!='<p><label class="valide">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form2 fieldset').html(responseHTML);
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// step52 -> step53     Récupérer les identifiants des nouveaux utilisateurs
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('a.step53').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#form2 fieldset table').remove();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_step=53&f_action='+f_action+'&'+$("#form2").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,25)!='<p><label class="alerte">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#form2 fieldset').html(responseHTML);
								format_liens('#form2 fieldset');
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// step90 -> step0
// Retour au départ
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#retourner_depart').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#bouton_annuler').click();
			}
		);

	}
);
