import { Controller } from 'stimulus';
const axios = require('axios').default;

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
        this.authDOM = document.querySelectorAll('.auth');
    }
    expand() {
        if (this.authDOM[0].classList.contains('active') || this.authDOM[1].classList.contains('active')) {
            this.authDOM[0].classList.remove('active');
            this.authDOM[1].classList.remove('active');
        } else {
            this.authDOM[0].classList.add('active');
        }
    }
    switch () {
        this.authDOM[0].classList.toggle('active');
        this.authDOM[1].classList.toggle('active');
    }

    send(action, target) {
        const bodyFormData = new FormData();
        const form = target.parentNode.parentNode.querySelectorAll('input');

        for (let i = 0; i < form.length; i++) {
            bodyFormData.append(form[i].name, form[i].value);
        }

        axios({
                method: 'post',
                data: bodyFormData,
                headers: { 'Content-Type': 'multipart/form-data' },
                url: `${window.location.protocol}//${window.location.href.split("/")[2]}/${action}`,
            })
            .then(function() {
                location.reload();
            })
            .catch(function(error) {
                target.parentNode.parentNode.querySelector('.error').innerText = error.response.data.message;
            });
    }

    register({ target }) {
        this.send('register', target);
    }

    login({ target }) {
        this.send('login', target);
    }

}