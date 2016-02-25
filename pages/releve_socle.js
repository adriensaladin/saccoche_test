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

    var groupe_id    = 0;
    var groupe_type  = $("#f_groupe option:selected").parent().attr('label'); // Il faut indiquer une valeur initiale au moins pour le profil élève
    var eleves_ordre = '';

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enlever le message ajax et le résultat précédent au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr('class').html("");
        $('#bilan').html("");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / masquer des options de la grille
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_groupe').change
    (
      function()
      {
        var groupe_val = $("#f_groupe option:selected").val();
        if(groupe_val!='0')
        {
          $("#option_groupe").show();
        }
        else
        {
          $("#option_groupe").hide();
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Charger le select f_pilier en ajax
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var maj_pilier = function()
    {
      $("#f_pilier").html('');
      palier_id = $("#f_palier").val();
      if(palier_id)
      {
        $('#ajax_maj_pilier').removeAttr('class').addClass('loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_piliers',
            data : 'f_palier='+palier_id+'&f_multiple=1',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_maj_pilier').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#ajax_maj_pilier').removeAttr('class').html('&nbsp;');
                $('#f_pilier').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_maj_pilier').removeAttr('class').addClass('alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
      else
      {
        $('#ajax_maj_pilier').removeAttr('class').html("");
      }
    };

    $("#f_palier").change( maj_pilier );

    maj_pilier();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_eleve en ajax
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve(groupe_id,groupe_type,eleves_ordre)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre='+eleves_ordre+'&f_statut=1'+'&f_multiple='+is_multiple+'&f_selection=1',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj').removeAttr('class').addClass('alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(groupe_type=='Classes')
            {
              $("#bloc_ordre").hide();
            }
            else
            {
              $("#bloc_ordre").show();
            }
            if(responseJSON['statut']==true)
            {
              $('#ajax_maj').removeAttr('class').html("");
              $('#f_eleve').html(responseJSON['value']).parent().show();
              if( !is_multiple && ($('#f_eleve option').length==2) )
              {
                // Cas d'un seul élève retourné dans le regroupement (en particulier pour un parent de plusieurs enfants)
                $('#f_eleve option').eq(1).prop('selected',true);
              }
            }
          else
            {
              $('#ajax_maj').removeAttr('class').addClass('alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }
    $("#f_groupe").change
    (
      function()
      {
        $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
        groupe_id = parseInt( $("#f_groupe option:selected").val() , 10 );
        if(groupe_id)
        {
          groupe_type  = $("#f_groupe option:selected").parent().attr('label');
          eleves_ordre = $("#f_eleves_ordre option:selected").val();
          $('#ajax_maj').removeAttr('class').addClass('loader').html("En cours&hellip;");
          maj_eleve(groupe_id,groupe_type,eleves_ordre);
        }
        else
        {
          $("#bloc_ordre").hide();
          $('#ajax_maj').removeAttr('class').html("");
        }
      }
    );

    $("#f_eleves_ordre").change
    (
      function()
      {
        groupe_id    = $("#f_groupe option:selected").val();
        groupe_type  = $("#f_groupe option:selected").parent().attr('label');
        eleves_ordre = $("#f_eleves_ordre option:selected").val();
        $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
        $('#ajax_maj').removeAttr('class').addClass('loader').html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type,eleves_ordre);
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
          f_palier        : { required:true },
          'f_pilier[]'    : { required:true },
          f_groupe        : { required:true },
          'f_eleve[]'     : { required:true },
          f_eleves_ordre  : { required:true },
          f_mode          : { required:true },
          'f_matiere[]'   : { required:function(){return $('#f_mode_manuel').is(':checked');} },
          f_only_presence : { required:false },
          f_coef          : { required:false },
          f_socle         : { required:false },
          f_lien          : { required:false },
          f_start         : { required:false },
          f_couleur       : { required:true },
          f_fond          : { required:true },
          f_legende       : { required:true },
          f_marge_min     : { required:true }
        },
        messages :
        {
          f_palier        : { required:"palier manquant" },
          'f_pilier[]'    : { required:"compétence(s) manquante(s)" },
          f_groupe        : { required:"groupe manquant" },
          'f_eleve[]'     : { required:"élève(s) manquant(s)" },
          f_eleves_ordre  : { required:"ordre manquant" },
          f_mode          : { required:"choix manquant" },
          'f_matiere[]'   : { required:"matière(s) manquante(s)" },
          f_only_presence : { },
          f_coef          : { },
          f_socle         : { },
          f_lien          : { },
          f_start         : { },
          f_couleur       : { required:"couleur manquante" },
          f_fond          : { required:"fond manquant" },
          f_legende       : { required:"légende manquante" },
          f_marge_min     : { required:"marge mini manquante" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
          else if(element.attr("type")=="radio") {element.parent().next().after(error);}
          else if(element.attr("type")=="checkbox") {
            if(element.parent().parent().hasClass('select_multiple')) {element.parent().parent().next().after(error);}
            else {element.parent().next().after(error);}
          }
        }
        // success: function(label) {label.text("ok").removeAttr('class').addClass('valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
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
        // récupération d'éléments
        $('#f_palier_nom' ).val( $("#f_palier option:selected").text() );
        $('#f_groupe_nom' ).val( $("#f_groupe option:selected").text() );
        $('#f_groupe_type').val( groupe_type );
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#bouton_valider').prop('disabled',true);
        $('#ajax_msg').removeAttr('class').addClass('loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#bouton_valider').prop('disabled',false);
      var message = (jqXHR.status!=500) ? afficher_json_message_erreur(jqXHR,textStatus) : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
      $('#ajax_msg').removeAttr('class').addClass('alerte').html(message);
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('#bouton_valider').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').removeAttr('class').addClass('alerte').html(responseJSON['value']);
      }
      else if(responseJSON['direct']==true)
      {
        $('#ajax_msg').removeAttr('class').addClass('valide').html("Résultat ci-dessous.");
        $('#bilan').html(responseJSON['bilan']);
      }
      else if(responseJSON['direct']==false)
      {
        $('#ajax_msg').removeAttr('class').html('');
        // Mis dans le div bilan et pas balancé directement dans le fancybox sinon la mise en forme des liens nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
        // Malgré tout, pour Chrome par exemple, la largeur est mal clculée et provoque des retours à la ligne, d'où le minWidth ajouté.
        $('#bilan').html('<p class="noprint">Afin de préserver l\'environnement, n\'imprimer que si nécessaire !</p>'+responseJSON['bilan']);
        $.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minWidth':450 } );
      }
    }

  }
);
