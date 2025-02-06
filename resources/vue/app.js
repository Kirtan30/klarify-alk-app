require('../js/bootstrap');

import Vue from 'vue';

import PolarisVue from '@hulkapps/polaris-vue';
import '@hulkapps/polaris-vue/dist/polaris-vue.min.css';
Vue.use(PolarisVue);

import CKEditor from 'ckeditor4-vue';
Vue.use(CKEditor);

import router from './router';
import store from './store';
require('./validations');

import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/xq-light.css';
import 'codemirror/addon/display/placeholder.js';
import 'codemirror/mode/htmlmixed/htmlmixed.js';

import VueCodemirror from 'vue-codemirror'
Vue.use(VueCodemirror, {
    options: {
      theme: 'xq-light',
      line: true,
      lineNumbers: true,
      tabSize: 4,
      mode: 'text/html',
      placeholder: 'Code goes here...',
    },
});

const app = new Vue({
    router,
    store,
    el: '#app',
});
