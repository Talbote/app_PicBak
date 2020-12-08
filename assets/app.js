/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
const $ = require('jquery');
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

function onClickBtnLike(even) {

    even.preventDefault();

    const url = this.href;
    const spanCount = this.querySelector('span.js-likes');
    const icone = this.querySelector('i');

    axios.get(url).then(function (response) {
        spanCount.textContent = response.data.likes;

        // si tu contiens actuellement la class fas = le pousse est remplit -> l'user à deja liker
        // Alors on remplace la class fas par Far = pousse vide .
        if (icone.classList.constains('fas')) {
            icone.classList.replace('fas', 'far');
        }
        else {
            icone.classList.replace('far', 'fas');
        }

    });

    document.querySelectorAll('a.js-like').forEach(function (link) {

        link.addEventListener('Click', onClickBtnLike);


    })
}

