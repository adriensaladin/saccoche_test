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

    $('select').change
    (
      function()
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
      }
    );

    var requis = '';

    $('#f_type').change
    (
      function()
      {
        var type = $(this).val();
        if( (type=='listing_eleves') || (type.substring(0,6)=='infos_') ) {requis='groupe';  $('#div_groupe').slideDown();}  else {$('#div_groupe').slideUp();}
        if( (type=='listing_matiere') || (type=='arbre_matiere') )        {requis='matiere'; $('#div_matiere').slideDown();} else {$('#div_matiere').slideUp();}
        if( (type=='arbre_socle') || (type=='jointure_socle_matiere') )   {requis='palier';  $('#div_palier').slideDown();}  else {$('#div_palier').slideUp();}
        if(type=='')                                                      {requis='';        $('#p_submit').hide(0);}        else {$('#p_submit').show(0);}
        $('#bilan').html("&nbsp;");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumettre le formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_export");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_type    : { required:true },
          f_groupe  : { required:function(){return requis=='groupe';} },
          f_matiere : { required:function(){return requis=='matiere';} },
          f_palier  : { required:function(){return requis=='palier';} }
        },
        messages :
        {
          f_type :    { required:"type manquant" },
          f_groupe :  { required:"regroupement manquant" },
          f_matiere : { required:"matière manquante" },
          f_palier :  { required:"palier manquant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element){element.after(error);}
        // success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
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
        // récupération du type et du nom du groupe
        var groupe_val = $("#f_groupe").val();
        if(groupe_val)
        {
          // Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
          if(isNaN(parseInt(groupe_val,10)))
          {
            groupe_type = groupe_val.substring(0,1);
            groupe_id   = groupe_val.substring(1);
          }
          // Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
          else
          {
            groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
            groupe_id   = groupe_val;
          }
          groupe_nom = $("#f_groupe option:selected").text();
          $('#f_groupe_type').val( groupe_type );
          $('#f_groupe_nom').val( groupe_nom );
          $('#f_groupe_id').val( groupe_id );
        }
        // récupération du nom de la matière
        var matiere_val = $("#f_matiere").val();
        if(matiere_val)
        {
          nom  = $("#f_matiere option:selected").text();
          $('#f_matiere_nom').val( nom );
        }
        // récupération du nom du palier
        var palier_val = $("#f_palier").val();
        if(palier_val)
        {
          nom  = $("#f_palier option:selected").text();
          $('#f_palier_nom').val( nom );
        }
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
        $("#bouton_exporter").prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $('#bilan').html('');
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_exporter").prop('disabled',false);
      $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      $("#bouton_exporter").prop('disabled',false);
      if(responseHTML.substring(0,17)!='<ul class="puce">')
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        $('#ajax_msg').removeAttr("class").html('');
        $.fancybox( responseHTML , {'centerOnScroll':true} );
        format_liens('#fancybox_contenu');
      }
    }

  }
);
