import { Controller } from 'stimulus';
const axios = require('axios').default;

export default class extends Controller {
    editor() {
        document.querySelector('.blur').style.display = 'block';
        document.querySelector('.adminBox').classList.add('active');

        document.querySelector('.blur').style.display = 'block';
        document.querySelector('.adminBox form #addtask_form_id').value = '';
        document.querySelector('.adminBox form #addtask_form_task_name').value = '';
        document.querySelector('.adminBox form #addtask_form_status').value = '';
        document.querySelector('.adminBox form #addtask_form_completed_date').value = '';
        document.querySelector('.adminBox form img').src =
            `${window.location.protocol}//${window.location.href.split("/")[2]}/build/missing.png`;
        document.querySelector('.adminBox form #addtask_form_img').value = '';
        CKEDITOR.instances["addtask_form_task_description"].setData('');
    }

    close({ target }) {
        document.querySelector('.blur').style.display = 'none';
        document.querySelector('.adminBox').classList.remove('active');
        if (target.closest('.task')) return;
        const tasksDOM = document.querySelectorAll('.task');
        for (let i = 0; i < tasksDOM.length; i++)
            tasksDOM[i].classList.remove('active');
        const imgDOM = document.querySelectorAll('.task img');
        for (let i = 0; i < imgDOM.length; i++)
            imgDOM[i].classList.remove('active');
    }

    view({ target }) {
        if (target.closest('img')) return;
        if (document.querySelector('.blur').style.display == 'block') return;
        let DOM;
        if (target.classList.contains('task')) {
            DOM = target;
        } else if (target.parentNode.classList.contains('task')) {
            DOM = target.parentNode;
        } else if (target.parentNode.parentNode.classList.contains('task')) {
            DOM = target.parentNode.parentNode;
        } else {
            DOM = target.parentNode.parentNode.parentNode;
        }

        if (target.closest('.options')) return;
        DOM.classList.add('active');
        document.querySelector('.blur').style.display = 'block';
    }

    img({ target }) {
        if (document.querySelector('.blur').style.display == 'block') return;

        target.classList.add('active');
        document.querySelector('.blur').style.display = 'block';
    }

    image(e) {
        document.querySelector('.adminBox form img').src = webkitURL.createObjectURL(e.target.files[0]);
    }

    send(action, target) {
        this.bodyFormData = new FormData();

        const formInput = target.parentNode.parentNode.querySelectorAll('input');
        for (let i = 0; i < formInput.length; i++) {
            this.bodyFormData.append(formInput[i].name, formInput[i].value);
        }

        const formSelects = target.parentNode.parentNode.querySelectorAll('select');
        for (let i = 0; i < formSelects.length; i++) {
            this.bodyFormData.append(formSelects[i].name, formSelects[i].value);
        }
        this.bodyFormData.append('addtask_form[task_description]', CKEDITOR.instances["addtask_form_task_description"].getData());
        this.bodyFormData.append('addtask_form[img]', document.querySelector('#addtask_form_img').files[0]);

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
                        if (item !== 'img') {
                            document.querySelector(`.adminBox form #addtask_form_${item}`).value = data[item];
                        } else {
                            document.querySelector(`.adminBox form #addtask_form_${item}`).value = '';
                        }
                    }
                    document.querySelector('.adminBox form img').src = data.img;
                    CKEDITOR.instances["addtask_form_task_description"].setData(data.task_description);

                    window.scrollTo(0, 0);
                    document.querySelector('.blur').style.display = 'block';
                    document.querySelector('.adminBox').classList.add('active');
                } else if (action == 'delete') {
                    target.parentNode.parentNode.parentNode.remove()
                } else if (action == 'save') {
                    location.reload();
                }

            })
            .catch(function(error) {
                if (action == 'save') {
                    target.parentNode.parentNode.querySelector('.error').innerText = error.response.data.message;
                }
            });
    }

    save({ target }) {
        this.send('save', target);
    }

    get({ target }) {
        this.tasksDOM = document.querySelectorAll('.task');
        for (let i = 0; i < this.tasksDOM.length; i++) {
            this.tasksDOM[i].classList.remove('active');
        }
        this.send('get', target);
    }

    delete({ target }) {
        if (confirm(`Are you sure?`) == true) {
            this.send('delete', target, 'reload');
        }
    }

}