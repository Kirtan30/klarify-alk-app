/*
|--------------------------------------------------------------------------
| Mutation Types
|--------------------------------------------------------------------------
*/

export const SET_SHOP = 'SET_SHOP';

/*
|--------------------------------------------------------------------------
| States
|--------------------------------------------------------------------------
*/
const states = {
    shop: null
};

/*
|--------------------------------------------------------------------------
| Mutations
|--------------------------------------------------------------------------
*/
const mutations = {
    [SET_SHOP](state, payload) {
        state.shop = payload;
    }
};

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/
const actions = {
    setShop: async ({state, commit}) => {
        if (state.shop && state.shop.id) {
            return;
        }
        let shop = null;
        try {
            let { data } = await axios.get('/app/shops/auth');
            shop = data.shop || null;
        } catch (error) {
            //
        }
        commit(SET_SHOP, shop);
    }
};

/*
|--------------------------------------------------------------------------
| Getters
|--------------------------------------------------------------------------
*/
const getters = {
    getShop: (state) => {
        return state.shop;
    }
};

/*
|--------------------------------------------------------------------------
| Export the module
|--------------------------------------------------------------------------
*/
export default {
    state: states,
    mutations,
    actions,
    getters,
    namespaced: true
}
