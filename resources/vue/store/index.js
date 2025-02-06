import Vue from 'vue';
import Vuex from 'vuex';
import shop from "./shop";
import common from "./common";

Vue.use(Vuex);
const state = {
    //
};
const mutations = {
    //
}
export default new Vuex.Store({
    modules: {
        shop,
        common
    },
    state,
    mutations,
    strict: true
});
