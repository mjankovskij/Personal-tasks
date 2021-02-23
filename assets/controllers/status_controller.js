import { Controller } from 'stimulus';
const axios = require('axios').default;

export default class extends Controller {

    editor() {
        document.querySelector('.blur').style.display = 'block';
        document.querySelector('.adminBox').classList.add('active');
        document.querySelector('.adminBox form #addstatus_form_id').value = '';
        document.querySelector('.adminBox form #addstatus_form_name').value = '';
    }

    close({ target }) {
        document.querySelector('.blur').style.display = 'none';
        document.querySelector('.adminBox').classList.remove('active');
        if (target.closest('.status')) return;
        this.statussDOM = document.querySelectorAll('.status');
        for (let i = 0; i < this.statussDOM.length; i++)
            this.statussDOM[i].classList.remove('active');
    }

    view({ target }) {
        if (document.querySelector('.blur').style.display == 'block') return;
        let DOM;
        if (target.classList.contains('status')) {
            DOM = target;
        } else if (target.parentNode.classList.contains('status')) {
            DOM = target.parentNode;
        }
        if (target.closest('.options')) return;
        DOM.classList.add('active');
        document.querySelector('.blur').style.display = 'block';
    }

    send(action, target) {
        this.bodyFormData = new FormData();
        this.form = target.parentNode.parentNode.querySelectorAll('input');

        for (let i = 0; i < this.form.length; i++) {
            this.bodyFormData.append(this.form[i].name, this.form[i].value);
        }
        this.bodyFormData.append('id', target.id);

        axios({
                method: 'post',
                data: this.bodyFormData,
                headers: { 'Content-Type': 'multipart/form-data' },
                url: `${window.location.protocol}//${window.location.href.split("/")[2]}/${window.location.href.split("/")[3]}/${action}`,
            })
            .then(function(response) {
                if (action == 'get') {
                    const data = response.data.message;
                    for (const item in data) {
                        document.querySelector(`.adminBox form #addstatus_form_${item}`).value = data[item];
                    }
                    document.querySelector('.blur').style.display = 'block';
                    document.querySelector('.adminBox').classList.add('active');
                } else if (action == 'delete') {
                    target.parentNode.parentNode.remove();
                    document.querySelector('.blur').style.display = 'none';
                } else {
                    location.reload();
                }
            })
            .catch(function(error) {
                console.log(error.response.data)
                if (action == 'delete') {
                    target.parentNode.parentNode.querySelector('.error').innerText = "Tasks have been assigned to this status.";
                } else if (action == 'save') {
                    target.parentNode.parentNode.querySelector('.error').innerText = error.response.data.message;
                }
            });
    }

    save({ target }) {
        this.send('save', target);
    }

    get({ target }) {
        this.statussDOM = document.querySelectorAll('.status');
        for (let i = 0; i < this.statussDOM.length; i++) {
            this.statussDOM[i].classList.remove('active');
        }
        this.send('get', target);
    }

    delete({ target }) {
        if (confirm(`
                Are you sure ? `) == true) {
            this.send('delete', target);
        }
    }
}