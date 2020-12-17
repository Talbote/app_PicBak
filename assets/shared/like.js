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
