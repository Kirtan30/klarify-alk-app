<template>
    <div class="navigation">
        <PTabs
            :tabs="tabs"
            :selected="tabSelected"
            navigation
            @select="changeTab"
        />
    </div>
</template>

<script>
export default {
    name: "Navigation",
    data() {
        return {
            tabs: [
                {
                    "id":"doctors",
                    "content":"Doctors / Clinics",
                    "to":"/doctors"
                }
            ],
            tabSelected: 0,
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
    },
    created() {
        let shop = this.$route.query.shop;
        if (!(['ragwitek.myshopify.com', 'grastek.myshopify.com'].includes(shop))) {
            let tabs = [
                {
                    "id":"news",
                    "content":"News",
                    "to":"/news",
                },
                {
                    "id":"polls",
                    "content":"Polls",
                    "to":"/polls"
                },
                {
                    "id":"quizzes",
                    "content":"Quizzes",
                    "to":"/quizzes"
                },
                {
                    "id":"results",
                    "content":"Allergy Tests (Self)",
                    "to":"/self-test-results",
                },
                {
                    "id":"allergy-test",
                    "content":"Allergy Tests (German)",
                    "to":"/allergy-test-german"
                },
                {
                    "id":"allergy-test-swedish",
                    "content":"Allergy Tests (Swedish)",
                    "to":"/allergy-test-swedish"
                },
/*                {
                    "id":"all-allergy-tests",
                    "content":"Allergy Tests",
                    "to":"/allergy-tests"
                },*/
            ];
            tabs.forEach(tab => this.tabs.push(tab));
        }

        this.tabs.push({
            "id":"fad",
            "content":"FaD",
            "to":"/fad/cities"
        });

        this.tabs.push({
            "id":"pollen",
            "content":"Pollen",
            "to":"/pollen/cities"
        });

        if(['allergiecheck.myshopify.com'].includes(shop)) {
            this.tabs.push({
                "id":"lexicon",
                "content":"Lexicon",
                "to":"/lexicons"
            });
        }

        this.tabs.push({
            "id":"settings",
            "content":"Settings",
            "to":"/settings"
        });

        this.tabSelected = this.tabs.findIndex(tab => (tab.to === this.$router.currentRoute.path || this.$router.currentRoute.path.includes(tab.to)));
        if(this.tabSelected === -1) {
            this.tabSelected = 0;
        }
    },
}
</script>

<style scoped>

</style>
