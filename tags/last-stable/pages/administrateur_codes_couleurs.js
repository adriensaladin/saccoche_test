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

    var mode_note_code = false;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un crayon pour modifier un symbole coloré
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_notes').on
    (
      'click',
      'q.modifier',
      function()
      {
        mode_note_code = $(this).prev('input').attr('id').substring(11); // note_image_
        $.fancybox( { 'href':'#zone_notes' , onStart:function(){$('#zone_notes').css("display","block");} , onClosed:function(){$('#zone_notes').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un lien pour choisir d'un symbole coloré
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('.note_liste').on
    (
      'click',
      'a',
      function()
      {
        var new_note_code = $(this).attr('id').substring(2); // a_
        var input_obj = $('#note_image_'+mode_note_code);
        input_obj.val(new_note_code);
        input_obj.prev('img').attr('src','./_img/note/choix/h/'+new_note_code+'.gif');
        $('#ajax_msg_notes').removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le lien pour annuler le choix d'un symbole coloré
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_note').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Activation du colorpicker pour les 3 champs input.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var f = $.farbtastic('#colorpicker');
    $('div.colorpicker input.stretch').focus
    (
      function()
      {
        $('#colorpicker').removeAttr("class");
        f.linkTo(this);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Reporter dans un input colorpicker une valeur préféfinie lors du clic sur un bouton (couleur de fond).
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('div.colorpicker button').click
    (
      function()
      {
        $( '#acquis_'+$(this).attr('name') ).val( $(this).val() ).focus();
        $('#ajax_msg_acquis').removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Affecter aux div la même couleur de fond que celle du input.
// Utilisation d'un test en boucle car un simple test change() ne fonctionne pas.
// ////////////////////////////////////////////////////////////////////////////////////////////////////

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

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du premier formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire1 = $('#form_notes');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation1 = formulaire1.validate
    (
      {
        rules :
        {
          note_image_RR     : { required:true },
          note_image_R      : { required:true },
          note_image_V      : { required:true },
          note_image_VV     : { required:true },
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
          note_image_RR     : { required:"codes de couleur manquant" },
          note_image_R      : { required:"codes de couleur manquant" },
          note_image_V      : { required:"codes de couleur manquant" },
          note_image_VV     : { required:"codes de couleur manquant" },
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
          $('#ajax_msg_notes').html(error);
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions1 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
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
        readytogo = false;
        var tab_image   = new Array( $('#note_image_RR').val().toUpperCase()   , $('#note_image_R').val().toUpperCase()   , $('#note_image_V').val().toUpperCase()   , $('#note_image_VV').val().toUpperCase()   );
        var tab_texte   = new Array( $('#note_texte_RR').val().toUpperCase()   , $('#note_texte_R').val().toUpperCase()   , $('#note_texte_V').val().toUpperCase()   , $('#note_texte_VV').val().toUpperCase()   );
        var tab_legende = new Array( $('#note_legende_RR').val().toUpperCase() , $('#note_legende_R').val().toUpperCase() , $('#note_legende_V').val().toUpperCase() , $('#note_legende_VV').val().toUpperCase() );
        tab_image.sort();
        tab_texte.sort();
        tab_legende.sort();
        if( (tab_image[0]==tab_image[1]) || (tab_image[1]==tab_image[2]) || (tab_image[2]==tab_image[3]) )
        {
          $('#ajax_msg_notes').addClass("erreur").html("Des symboles colorés sont identiques !").show();
        }
        else if( (tab_texte[0]==tab_texte[1]) || (tab_texte[1]==tab_texte[2]) || (tab_texte[2]==tab_texte[3]) )
        {
          $('#ajax_msg_notes').addClass("erreur").html("Des équivalents textes sont identiques !").show();
        }
        else if( (tab_legende[0]==tab_legende[1]) || (tab_legende[1]==tab_legende[2]) || (tab_legende[2]==tab_legende[3]) )
        {
          $('#ajax_msg_notes').addClass("erreur").html("Des légendes sont identiques !").show();
        }
        else
        {
          readytogo = true;
        }
      }
      if(readytogo)
      {
        $("#bouton_valider_notes").prop('disabled',true);
        $('#ajax_msg_notes').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur1(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_valider_notes").prop('disabled',false);
      $('#ajax_msg_notes').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
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


    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du second formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire2 = $('#form_acquis');

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
          $('#ajax_msg_acquis').html(error);
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions2 =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
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
        readytogo = false;
        var tab_texte   = new Array( $('#acquis_texte_NA').val().toUpperCase()   , $('#acquis_texte_VA').val().toUpperCase()   , $('#acquis_texte_A').val().toUpperCase()   );
        var tab_legende = new Array( $('#acquis_legende_NA').val().toUpperCase() , $('#acquis_legende_VA').val().toUpperCase() , $('#acquis_legende_A').val().toUpperCase() );
        var tab_color   = new Array( $('#acquis_color_NA').val().toUpperCase()   , $('#acquis_color_VA').val().toUpperCase()   , $('#acquis_color_A').val().toUpperCase()   );
        tab_texte.sort();
        tab_legende.sort();
        tab_color.sort();
        if( (tab_texte[0]==tab_texte[1]) || (tab_texte[1]==tab_texte[2]) || (tab_texte[2]==tab_texte[3]) )
        {
          $('#ajax_msg_acquis').addClass("erreur").html("Des équivalents textes sont identiques !").show();
        }
        else if( (tab_legende[0]==tab_legende[1]) || (tab_legende[1]==tab_legende[2]) || (tab_legende[2]==tab_legende[3]) )
        {
          $('#ajax_msg_acquis').addClass("erreur").html("Des légendes sont identiques !").show();
        }
        else if( (tab_color[0]==tab_color[1]) || (tab_color[1]==tab_color[2]) || (tab_color[2]==tab_color[3]) )
        {
          $('#ajax_msg_acquis').addClass("erreur").html("Des couleurs de fond sont identiques !").show();
        }
        else
        {
          readytogo = true;
        }
      }
      if(readytogo)
      {
        $("#bouton_valider_acquis").prop('disabled',true);
        $('#ajax_msg_acquis').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur2(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_valider_acquis").prop('disabled',false);
      $('#ajax_msg_acquis').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
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
