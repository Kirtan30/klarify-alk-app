<template>
    <div>
        <Confirm ref="confirm" />
        <Navigation />
        <router-view></router-view>
    </div>

</template>

<script>
import createApp from '@shopify/app-bridge';
import { History, TitleBar } from '@shopify/app-bridge/actions';
import { getSessionToken } from '@shopify/app-bridge-utils';

import Confirm from "../components/Confirm";
import Navigation from "../components/Navigation";
import {mapActions} from "vuex";

export default {
    name: "Master",
    components: {
        Navigation,
        Confirm
    },
    methods: {
        ...mapActions('shop', [
            'setShop'
        ]),
        slugify(text, ampersand = 'and') {
            const a = 'àáäâèéëêìíïîòóöôùúüûñçßÿỳýœæŕśńṕẃǵǹḿǘẍźḧ'
            const b = 'aaaaeeeeiiiioooouuuuncsyyyoarsnpwgnmuxzh'
            const p = new RegExp(a.split('').join('|'), 'g')

            return text.toString().toLowerCase()
                .replace(/[\s_]+/g, '-')
                .replace(p, c =>
                    b.charAt(a.indexOf(c)))
                .replace(/&/g, `-${ampersand}-`)
                .replace(/[^\w-]+/g, '')
                .replace(/--+/g, '-')
                .replace(/^-+|-+$/g, '')
        },
        prepareUrl(url, query = {}) {
            let preparedUrl = url.split('?');
            if (!(preparedUrl && preparedUrl.length)) {
                return;
            }

            preparedUrl = preparedUrl[0];
            let preparedQuery = preparedUrl[1].split('&') || '';
            // preparedQuery = preparedQuery.split('&');

            let queryParams = {};

            preparedQuery.forEach(item => {
                let key = item.split('=')[0] || '';
                let value = item.split('=')[1] || '';

                if (key && value) {
                    queryParams[key] = value;
                }
            })

            for (const [key, value] of Object.entries(query)) {
                if (!['embedded', 'host', 'shop', 'token'].includes(key)) {
                    queryParams[key] = value;
                }
            }

            let queryString = '';
            for (const [key, value] of Object.entries(queryParams)) {
                if (value) {
                    queryString += `${queryString ? '&' : ''}${key}=${value}`;
                }
            }

            preparedUrl += `?${queryString}`;

            return preparedUrl;
        },
        getBoolean(value) {
            let stringValue = value ? value.toString() : '';
            return value && (stringValue === '1' || stringValue === 'true');
        }
    },
    watch: {
        $route: {
            deep: true,
            handler(to, from) {
                const history = History.create(this.$root.$shopifyApp);
                let path = this.prepareUrl(to.path, to.query);
                history.dispatch(History.Action.PUSH, path ? path : to.path);
                this.$root.$titleBar.set({
                    title: to.meta.title,
                });
            }
        }
    },
    async beforeCreate() {
        this.$root.$shopifyApp = createApp({
            apiKey: SHOPIFY_API_KEY || process.env.MIX_SHOPIFY_API_KEY,
            host: new URLSearchParams(location.search).get('host') || this.$route.query.host || btoa(SHOP_DOMAIN),
            forceRedirect: true,
        });

        window.axios.interceptors.request.use((config) => {
            return getSessionToken(this.$root.$shopifyApp)
                .then((token) => {
                    config.headers["Authorization"] = `Bearer ${token}`;
                    return config;
                });
        });
        const titleBarOptions = {
            title: this.$route.meta.title,
        };
        this.$root.$titleBar = TitleBar.create(this.$root.$shopifyApp, titleBarOptions);
    },
    async created() {
        this.$root.$getBoolean = this.getBoolean;
    },
    async mounted() {
        this.$root.$confirm = this.$refs.confirm.open;
        this.$root.$slugify = this.slugify;
        await this.setShop();

        const history = History.create(this.$root.$shopifyApp);
        let path = this.prepareUrl(this.$route.path, this.$route.query);
        history.dispatch(History.Action.PUSH, path ? path : to.path);
    },
}
</script>

<style scoped>

</style>

<style>
    .Polaris-Frame-ContextualSaveBar.contextual-save-bar {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 100;
    }

    .custom_class .Polaris-Modal__BodyWrapper {
        overflow-x: visible;
    }

    .custom_class .Polaris-Modal-Dialog__Modal {
        overflow: visible;
    }

    div.multiselect .multiselect__tags-wrap{
        height: auto !important;
    }

    .custom_class .Polaris-Stack__Item {
        text-align: right;
    }

    .CodeMirror-placeholder {
        color: grey !important;
    }
</style>
