/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// any CSS you import will output into a single css file (app.css in this case)
const $ = require('jquery');
const axios = require('axios').default;

require('bootstrap');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

import './app.scss';

$(document).ready(function () {
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';


// ALERT  à chaque message selectionné + récupération du nom du fichier IMAGE
    $('.custom-file-input').on('change', function (e) {
        var inputFile = e.currentTarget;
        $(inputFile).parent().find('.custom-file-label').html(inputFile.files[0].name);

    });


    /*###########################################################################
     ############################# JS LIKE / NO LIKE ############################
     ############################################################################ */

// récuperation de toute lesclass a js-like

    function onClickBtnLike(event) { // tu dois recevoir un évenement even en parametre

        // event ne bouge pas
        event.preventDefault();
        // le href du liens sur lequelle on click.


        const spanIcon = this.querySelector('svg');
        const spanCount = this.querySelector('span.js-likes');
        const url = this.href;


        // recuperé l'URL avec axios
        // quand il aura une reponse
        // met dans la fonction response ->  console.log(response);
        // response renvois des données -> data qui contient de ce qui est revenu du serveur
        axios.get(url).then(function (response) {

            console.log(response);
                // chercher les likes dans le data
                spanCount.textContent = response.data.likes;


           const numberId = response.data.picture;

                if (spanIcon.classList.contains('far-svg')) {


                        $('.far-svg').attr('data-prefix', 'fas')

                        spanIcon.classList.replace('far-svg', 'fas-svg')


                } else {

                            $('.fas-svg').attr('data-prefix', 'far')

                            spanIcon.classList.replace('fas-svg', 'far-svg')


                }
            }
        )
    }

    document.querySelectorAll('a.js-like').forEach(function (link) {
        link.addEventListener('click', onClickBtnLike);
    })


    /*###########################################################################
     ########################### SUBMIT COMMENT AJAX ############################
     ############################################################################ */


    function submitComment(event) { // tu dois recevoir un évenement even en parametre


        event.preventDefault();

        const url = this.href;

        axios.post(url).then(function (response) {

            console.log(response);

        });
    }

    document.querySelectorAll('a.js-form-comment').forEach(function (link) {
        link.addEventListener('click', submitComment);
    })

});

