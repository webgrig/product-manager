/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
require('./styles/app.scss');

require('bootstrap');
require('@coreui/coreui');

import './images/coreui.svg';
// start the Stimulus application

import './bootstrap';

const Centrifuge = require('centrifuge');
const toastr = require('toastr');

document.addEventListener('DOMContentLoaded', function () {
    let url = document.querySelector('meta[name=centrifugo-url]').getAttribute('content');
    let user = document.querySelector('meta[name=centrifugo-user]').getAttribute('content');
    let token = document.querySelector('meta[name=centrifugo-token]').getAttribute('content');
    let centrifuge = new Centrifuge(url);
    centrifuge.setToken(token);
    centrifuge.subscribe('alerts#' + user, function (message) {
        toastr.info(message.data.message);
    });
    centrifuge.connect();
});