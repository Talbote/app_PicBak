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


// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';


// ALERT  à chaque message selectionné + récupération du nom du fichier IMAGE
$('.custom-file-input').on('change', function (e) {
    var inputFile = e.currentTarget;
    $(inputFile).parent().find('.custom-file-label').html(inputFile.files[0].name);

});

// Function Like Pic
// récuperation de toute lesclass a js-like


function onClickBtnLike(even) { // tu dois recevoir un évenement even en parametre

    // event ne bouge pas
    even.preventDefault();
    // le href du liens sur lequelle on click.
    const spanLogo = this.querySelector('i');
    const spanCount = this.querySelector('span');
    const url = this.href;
    console.log(spanLogo, url, spanCount);

    // recuperé l'URL avec axios
    // quand il aura une reponse
    // met dans la fonction response ->  console.log(response);
    // response renvois des données -> data qui contient de ce qui est revenu du serveur
    axios.get(url).then(function (response) {

        // chercher les likes dans le data

        spanCount.textContent = response.data.likes;

        /*   if (spanCount.classList.contains('fas')) {

         spanCount.classList.replace('fas', 'far')
         } else {
         spanCount.classList.replace('far', 'fas');
         }
          */

    });
}

document.querySelectorAll('a.js-like').forEach(function (link) {
    link.addEventListener('click', onClickBtnLike);
})
