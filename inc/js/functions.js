document.addEventListener('DOMContentLoaded', function () {

    if (document.getElementById('photo')) {
        // document.getElementById('photo').addEventListener('change',function(e){
        $('#photo').on('change', function (e) {
            let fichier = e.target.files;
            console.log(fichier);
            let reader = new FileReader();
            reader.readAsDataURL(fichier[0]);
            reader.onload = function (event) {
                document.getElementById('preview').innerHTML = ' <img src="' + event.target.result + '" alt="' + fichier[0].name + '" class="img-fluid vignette" id="placeholder">'
                $('#placeholder').on('drop', updatePhoto);

            }
        });

    }

    let confirmation = document.querySelectorAll('.confirm');
    for (let i = 0; i < confirmation.length; i++) {
        confirmation[i].onclick = function () {
            return (confirm('Êtes vous sûr(e) de vouloir supprimer ce produit/membre ?'));
        }
    }
    if (document.getElementById('modalConfirm')) {
        $('#modalConfirm').modal('show');
    }

    let lignes = document.querySelectorAll('#tablecommandes tr[data-idcmd]');

    const URL = 'http://localhost/Ifocop/PHP/boutique/';

    for (let i = 0; i < lignes.length; i++) {
        console.log(lignes[i].dataset);
        lignes[i].getElementsByClassName.cursor = 'pointer';
        lignes[i].addEventListener('click', function () {
            window.location.href = URL + 'admin/gestion_commande.php?action=details&id_commande=' + this.dataset.idcmd
        });
    }
    let selectetats = document.querySelectorAll('#tablecommandes tr[data-idcmd] td select');
    for (let i = 0; i < selectetats.length; i++) {
        selectetats[i].addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
    if ($('#bandeaucookies').length > 0) {
        $('#bandeaucookies').animate({
            bottom: 0
        }, 1000);
    }
    if ($('#confirmcookies'.length > 0)) {
        $('#bandeaucookies').on('click', function (e) {
            e.preventDefault();
            let maintenant = new Date();
            let expiration = new Date(maintenant.getTime() + 30 * 24 * 60 * 60 * 1000); //30 jours


            document.cookie = "acceptcookies=true; expires=" + expiration.toGMTString() + "; path=/";

            $('#bandeaucookies').animate({
                bottom: -70
            }, 1000);
        });
    }
    if ($('#placeholder').length > 0) {
        $('html')
            .on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $('#placeholder').css('border', '5px dashed orange')
            })
            .on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $('#placeholder').css('border', '')


            })
            .on('dragleave', function (e) {
                if (e.originalEvent.pageX == 0 || e.originalEvent.pageY == 0) {
                    $('#placeholder').css('border', '')

                }
            })

    }
    $('#placeholder').on('drop', updatePhoto);

    function updatePhoto(e) {
        $('#placeholder').css('border', '')
        let fichier = e.originalEvent.dataTransfer.files;
        $('#photo')[0].files = fichier;
        $('#photo').trigger('change');
    }

    //Affichage du prix du range picker
    if (document.getElementById('prix') ){
        let prix = document.getElementById('prix');
        prix.addEventListener('input', affichePrix);
        function affichePrix() {
            document.getElementById('affichePrix').innerHTML = prix.value + '€';
        }
        affichePrix();
    }

});
