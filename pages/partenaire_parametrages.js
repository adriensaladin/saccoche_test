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

    // Indiquer le nombre de caractères restant autorisés dans le textarea
    $('#f_message').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , 500 );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer un logo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_delete_logo').click
    (
      function()
      {
        $("button").prop('disabled',true);
        $('#ajax_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=delete_logo',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_upload').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              $("button").prop('disabled',false);
              if(responseHTML.substring(0,2)!='ok')
              {
                $('#ajax_upload').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                initialiser_compteur();
                $('#ajax_upload').removeAttr("class").html('');
                $('#image_logo').attr('src',responseHTML.substring(3,responseHTML.length));
                $('#ajax_msg').removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Upload d'un fichier image avec jquery.ajaxupload.js
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    new AjaxUpload
    ('#f_upload_logo',
      {
        action: 'ajax.php?page='+PAGE,
        name: 'userfile',
        data: {'csrf':CSRF,'f_action':'upload_logo'},
        autoSubmit: true,
        responseType: "html",
        onChange: changer_fichier,
        onSubmit: verifier_fichier,
        onComplete: retourner_fichier
      }
    );

    function changer_fichier(fichier_nom,fichier_extension)
    {
      $("button").prop('disabled',true);
      $('#ajax_upload').removeAttr("class").html('&nbsp;');
      return true;
    }

    function verifier_fichier(fichier_nom,fichier_extension)
    {
      if (fichier_nom==null || fichier_nom.length<5)
      {
        $("button").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
        return false;
      }
      else if ('.bmp.gif.jpg.jpeg.png.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
      {
        $("button").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension d\'image autorisée (bmp gif jpg jpeg png).');
        return false;
      }
      else
      {
        $('#ajax_upload').removeAttr("class").addClass("loader").html("En cours&hellip;");
        return true;
      }
    }

    function retourner_fichier(fichier_nom,responseHTML)  // Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
    {
      if(responseHTML.substring(0,2)!='ok')
      {
        $("button").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        initialiser_compteur();
        $("button").prop('disabled',false);
        $('#ajax_upload').removeAttr("class").html('&nbsp;');
        $('#image_logo').attr('src',responseHTML.substring(3,responseHTML.length));
        $('#ajax_msg').removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_upload_logo : { required:false },
          f_adresse_web : { required:false , url:true, maxlength:150 },
          f_message     : { required:false }
        },
        messages :
        {
          f_upload_logo : { },
          f_adresse_web : { url:"adresse invalide (http:// manquant ?)", maxlength:"150 caractères maximum" },
          f_message     : {  }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
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
        $("button").prop('disabled',true);
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $("button").prop('disabled',false);
      $('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      $("button").prop('disabled',false);
      if(responseHTML.substring(0,2)=='ok')
      {
        $('#ajax_msg').removeAttr("class").addClass("valide").html("Données enregistrées !");
        $('#resultat').html(responseHTML.substring(3,responseHTML.length));
        format_liens('#resultat');
      }
      else
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
    }

  }
);
