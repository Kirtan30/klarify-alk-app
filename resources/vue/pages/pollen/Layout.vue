<template>
    <div class="pages-layout">
        <PStack>
            <PStackItem fill>
                <PTabs
                    :tabs="tabs"
                    :selected="tabSelected"
                    navigation
                    @select="changeTab"
                />
            </PStackItem>
            <PStackItem class="Polaris-Tabs__Wrapper Polaris-Tabs__Navigation" style="margin-left: 0; display: flex">
                <PStack alignment="center">
                    <PStackItem>
                        <PButton primary :loading="loading" :disabled="loading" @click="handleFadMetafieldSync">
                            Sync Metafields
                        </PButton>
                    </PStackItem>
                </PStack>
            </PStackItem>
        </PStack>
        <router-view />
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "Layout",
    data() {
        return {
            tabs: [
                {
                    "id": "states",
                    "content": "States",
                    "to": "/pollen/states"
                },
                {
                    "id": "regions",
                    "content": "Regions",
                    "to": "/pollen/regions"
                },
                {
                    "id": "cities",
                    "content": "Cities",
                    "to": "/pollen/cities",
                },
                {
                    "id": "static_content",
                    "content": "Static Content",
                    "to": "/pollen/static-contents",
                },
            ],
            tabSelected: 0,
            loading: false,
        }
    },
    watch: {
        $route: {
            handler() {
                if(this.tabSelected === -1) {
                    this.tabSelected = 0;
                }
            }
        }
    },
    methods: {
        changeTab(tab) {
            this.tabSelected = tab;
            if(this.tabs[this.tabSelected].to === this.$route.path) {
                return;
            }
            this.$router.push(this.tabs[this.tabSelected].to);
        },
        async handleFadMetafieldSync() {
            try {
                this.loading = true;
                let { data } = await axios.post('/app/pollen/cities/sync', {});
                this.$pToast.open(data?.message || 'Metafields synced successfully')
            } catch ({response}) {
                this.$pToast.open({
                    message: response?.message || 'Something went wrong',
                    error: true
                })
            }
            this.loading = false;
        }
    },
    created() {
        this.tabSelected = this.tabs.findIndex(tab => (tab.to === this.$router.currentRoute.path || this.$router.currentRoute.path.includes(tab.to)));
        if (this.tabSelected === -1) {
            this.tabSelected = 0;
        }
    }
}
</script>

<style scoped>

</style>
