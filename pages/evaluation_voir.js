/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// jQuery !
$(document).ready
(
  function()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var groupe_id   = 0;
    var groupe_type = '';
    var nb_caracteres_max = 999;

    // tri des tableaux (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:'date_fr'},3:{sorter:false},4:{sorter:false}} });
    $('#table_voir'  ).tablesorter({ headers:{} });
    var tableau_tri_action = function(){ $('#table_action').trigger( 'sorton' , [ [[0,1]] ] ); };
    var tableau_tri_voir   = function(){ $('#table_voir'  ).trigger( 'sorton' ); };
    var tableau_maj_action = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    var tableau_maj_voir   = function(){ $('#table_voir'  ).trigger( 'update' , [ true ] ); };
    tableau_tri_action();
    tableau_tri_voir();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_eleve en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve(groupe_id,groupe_type)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre=alpha'+'&f_statut=1',
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
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
        $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
        $('#ajax_msg').removeAttr("class").html('');
        $('#zone_eval_choix').hide();
        groupe_id = $("#f_groupe option:selected").val();
        if(groupe_id)
        {
          groupe_type = $("#f_groupe option:selected").parent().attr('label');
          $('#ajax_maj').removeAttr("class").addClass("loader").html("En cours&hellip;");
          maj_eleve(groupe_id,groupe_type);
        }
        else
        {
          $('#ajax_maj').removeAttr("class").html("&nbsp;");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
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
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $('#zone_eval_choix').hide();
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $("#actualiser").prop('disabled',false);
      $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
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
        $('#table_action tbody').html( responseHTML.substring(0,position_script) );
        tableau_maj_action();
        if(aff_nom_eleve)
        {
          $('#zone_eval_choix h2').html($('#f_eleve option:selected').text());
        }
        $('#zone_eval_choix').show();
        eval( responseHTML.substring(position_script+8) );
        // Afficher des résultats au chargement
        if(auto_voir_devoir_id)
        {
          if( $('#devoir_'+auto_voir_devoir_id).length )
          {
            $('#devoir_'+auto_voir_devoir_id).children('q.'+auto_mode).click();
          }
          auto_voir_devoir_id = false;
        }
      }
      else
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation et chargement au changement d'élève (cas d'un parent responsable de plusieurs élèves)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Voir les notes saisies à un devoir / Lire un commentaire écrit / Écouter un commentaire audio
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_eval_choix').on
    (
      'click',
      'q.voir , q.texte_consulter , q.audio_ecouter',
      function()
      {
        var objet_tds  = $(this).parent().parent().find('td');
        // Récupérer les informations de la ligne concernée
        var devoir_id  = objet_tds.eq(4).attr('id').substring(7); // "devoir_" + id
        var texte_date = objet_tds.eq(0).html();
        var texte_prof = objet_tds.eq(1).html();
        var texte_info = objet_tds.eq(2).html();
        // Afficher la zone associée après avoir chargé son contenu
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Voir_notes'+'&f_eleve='+$('#f_eleve option:selected').val()+'&f_devoir='+devoir_id,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              var tab_response = responseHTML.split(']¤[');
              if(tab_response[0]!='ok')
              {
                $.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $('#titre_voir').html('Devoir du ' + texte_date + ' par ' + texte_prof + ' [ ' + texte_info + ' ]');
                $('#table_voir tbody').html(tab_response[1]);
                $('#report_legende'  ).html(tab_response[2]);
                $('#report_texte'    ).html(tab_response[3]);
                $('#report_audio'    ).html(tab_response[4]);
                tableau_maj_voir();
                $.fancybox( { 'href':'#zone_eval_voir' , onStart:function(){$('#zone_eval_voir').css("display","block");} , onClosed:function(){$('#zone_eval_voir').css("display","none");} , 'centerOnScroll':true } );
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur l'image pour Saisir les notes d'un devoir (auto-évaluation)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_eval_choix').on
    (
      'click',
      'q.saisir',
      function()
      {
        var objet_tds  = $(this).parent().parent().find('td');
        // Récupérer les informations de la ligne concernée
        var devoir_id  = objet_tds.eq(4).attr('id').substring(7); // "devoir_" + id
        var texte_date = objet_tds.eq(0).html();
        var texte_prof = objet_tds.eq(1).html();
        var texte_info = objet_tds.eq(2).html();
        // Afficher la zone associée après avoir chargé son contenu
        $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=Saisir_notes'+'&f_eleve='+$('#f_eleve option:selected').val()+'&f_devoir='+devoir_id,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+'Échec de la connexion !'+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              var tab_response = responseHTML.split(']¤[');
              if(tab_response[0]!='ok')
              {
                $.fancybox( '<label class="alerte">'+tab_response[0]+'</label>' , {'centerOnScroll':true} );
              }
              else
              {
                $('#titre_saisir').html('Devoir du ' + texte_date + ' par ' + texte_prof + ' [ ' + texte_info + ' ]');
                $('#report_date').html(tab_dates[devoir_id]);
                $('#fermer_zone_saisir').removeAttr("class").addClass("retourner").html('Retour');
                $('#msg_saisir').removeAttr("class").html("");
                $('#f_devoir').val(devoir_id);
                $('#table_saisir tbody').html(tab_response[1]);
                tableau_maj_voir();
                $.fancybox( { 'href':'#zone_eval_saisir' , onStart:function(){$('#zone_eval_saisir').css("display","block");} , onClosed:function(){$('#zone_eval_saisir').css("display","none");} , 'margin':0 , 'modal':true , 'centerOnScroll':true } );
                $('#f_msg_autre').val(tab_response[2]);
                $('#f_msg_url'  ).val(tab_response[3]);
                $('#f_msg_texte').focus().val(tab_response[4]);
                afficher_textarea_reste( $('#f_msg_texte') , nb_caracteres_max );
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Réagir à la modification d'une note ou d'un commentaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var modification = false;

    $('#table_saisir').on
    (
      'click',
      'input[type=radio]',
      function()
      {
        modification = true;
        $('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
        $('#msg_saisir').removeAttr("class").html("");
      }
    );

    $('#f_msg_texte').change
    (
      function()
      {
        modification = true;
        $('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
        $('#msg_saisir').removeAttr("class").html("");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_msg_texte').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le formulaire servant à saisir les acquisitions à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fermer_zone_saisir').click
    (
      function()
      {
        modification = false;
        $.fancybox.close();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le lien pour mettre à jour les acquisitions à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_saisir').click
    (
      function()
      {
        if(modification==false)
        {
          $('#msg_saisir').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
        }
        else
        {
          $('#zone_eval_saisir button').prop('disabled',true);
          $('#msg_saisir').removeAttr("class").addClass("loader").html("En cours&hellip;");
          // On ne risque pas de problème dû à une limitation du module "suhosin" ou à "max input vars" pour un seul élève (nb champs envoyés = nb items + 1).
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=Enregistrer_saisies'+'&'+$('#zone_eval_saisir').serialize(),
              dataType : "html",
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#zone_eval_saisir button').prop('disabled',false);
                $('#msg_saisir').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
                return false;
              },
              success : function(responseHTML)
              {
                initialiser_compteur();
                $('#zone_eval_saisir button').prop('disabled',false);
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

