function submitComment(event) { // tu dois recevoir un évenement even en parametre

    event.preventDefault();

    const url = this.href

    axios.post(url).then(function (response) {

        console.log(response);

    });
}

document.querySelectorAll('a.js-form-comment').forEach(function (link) {
    link.addEventListener('click', submitComment);
});