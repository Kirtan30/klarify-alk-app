import {extend, ValidationObserver, ValidationProvider} from "vee-validate";
import {alpha, confirmed, email, is_not, max, min, required, numeric, min_value, max_value} from "vee-validate/dist/rules.umd";
import Vue from "vue";

extend('required', required);
extend('numeric', numeric);
extend('min', min);
extend('min_value', min_value);
extend('max', max);
extend('max_value', max_value);
extend('alpha', alpha);
extend('confirmed', confirmed);
extend('is_not', is_not);
extend('email', email);
extend('url', {
    validate: (str) => {
        let pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
            '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
        return !!pattern.test(str);
    },
});

Vue.component('ValidationProvider', ValidationProvider);
Vue.component('ValidationObserver', ValidationObserver);
