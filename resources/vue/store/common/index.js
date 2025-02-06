/*
|--------------------------------------------------------------------------
| Mutation Types
|--------------------------------------------------------------------------
*/

export const SET_CLIP = 'SET_CLIP';

/*
|--------------------------------------------------------------------------
| States
|--------------------------------------------------------------------------
*/
const states = {
    clip: null
};

/*
|--------------------------------------------------------------------------
| Mutations
|--------------------------------------------------------------------------
*/
const mutations = {
    [SET_CLIP](state, payload) {
        state.clip = payload;
    }
};

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/
const actions = {
    setClip: async ({state, commit}, payload) => {
        const element = document.createElement('textarea');
        element.innerText = payload;
        document.body.appendChild(element);
        element.select();
        document.execCommand('copy');
        document.body.removeChild(element);
        commit(SET_CLIP, payload);
    }
};

/*
|--------------------------------------------------------------------------
| Getters
|--------------------------------------------------------------------------
*/
const getters = {
    getClip: (state) => {
        return state.clip;
    }
};

/*
|--------------------------------------------------------------------------
| Export the module
|--------------------------------------------------------------------------
*/
export default {
    states,
    mutations,
    actions,
    getters,
    namespaced: true
}
