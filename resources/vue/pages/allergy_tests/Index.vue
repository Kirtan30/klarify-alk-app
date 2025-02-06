<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Allergy Tests">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Allergy Tests">
        <PButton primary slot="primaryAction" @click="addAllergyTest">Add Test</PButton>
<!--        <PVerticalTabs-->
<!--            :tabs="tabs"-->
<!--            :selected="tabSelected"-->
<!--            @select="changeTab"-->
<!--        >-->
<!--            <template>-->
<!--                <PLayout sectioned>-->
<!--                    <PCard sectioned>-->
<!--                        <PDataTable-->
<!--                            :headings="headings"-->
<!--                            :rows="allergyTests.filter(filterAllergyTests)"-->
<!--                            :has-pagination="true"-->
<!--                            :pagination="pagination"-->
<!--                            :loading="loading"-->
<!--                        >-->
<!--                            <template v-slot:item.actions="{ item }">-->
<!--                                <PStack>-->
<!--                                    <PStackItem>-->
<!--                                        <PIcon source="EditMinor" @click="handleEdit(item.id)"/>-->
<!--                                    </PStackItem>-->
<!--                                    <PStackItem>-->
<!--                                        <PIcon source="DeleteMinor" color="critical" @click="handleDelete(item)"/>-->
<!--                                    </PStackItem>-->
<!--                                    <PStackItem>-->
<!--                                        <PIcon source="ClipboardMinor" @click="handleCopy(item.uuid)"/>-->
<!--                                    </PStackItem>-->
<!--                                </PStack>-->
<!--                            </template>-->
<!--                            <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} Quizzes</PTextStyle>-->
<!--                        </PDataTable>-->
<!--                    </PCard>-->
<!--                </PLayout>-->
<!--            </template>-->
<!--        </PVerticalTabs>-->
        <PTabs
            :tabs="tabs"
            :selected="tabSelected"
            navigation
            @select="changeTab"
        />
        <router-view></router-view>
    </PPage>
</template>

<script>
import {mapActions} from "vuex";

export default {
    name: "Index",
    data() {
        return {
            tabs: [
                {
                    id: 'allergyTestSelf',
                    content: 'Allergy Test(self)',
                    to: '/allergy-tests/self'
                },
                {
                    id: 'allergyTestGerman',
                    content: 'Allergy Test(german)',
                    to: '/allergy-tests/german'
                },
                {
                    id: 'allergyTestSwedish',
                    content: 'Allergy Test(swedish)',
                    to: '/allergy-tests/swedish'
                },
            ],
            tabSelected : 0,
            headings: [
                {
                    content: 'Title',
                    value: 'title',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Label',
                    value: 'label',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Actions',
                    value: 'actions',
                    type: 'text',
                    sortable: false
                },
            ],
        }
    },
    methods: {
        ...mapActions('common', ['setClip']),
        changeTab(tab) {
            this.tabSelected = tab;
            if(this.tabs[this.tabSelected].to === this.$route.path) {
                return;
            }
            this.$router.push(this.tabs[this.tabSelected].to);
        },
        async addAllergyTest() {
            await this.$router.push({name: 'allergy-test.create'});
        },
        async setOptions() {
            for (const [key] of Object.entries(this.options)) {
                if (this.$route.query[key]) {
                    this.options[key] = this.$route.query[key];
                } else {
                    this.options[key] = this.defaultOptions[key];
                }
            }

            await this.getAllergyTests();
        },
        changeUrl() {
            this.$router.push({ query: this.options })
        },
        async getAllergyTests () {
            this.loading = true;
            try {
                let { data } = await axios.get('/app/allergy-tests', {
                    params: this.options
                })
                this.allergyTests = data.allergyTests.data || [];
                this.pagination.hasNext = !!(data.allergyTests?.next_page_url);
                this.pagination.hasPrevious = !!(data.allergyTests?.prev_page_url);
                this.pagination.from = data.allergyTests?.from;
                this.pagination.to = data.allergyTests?.to;
                this.pagination.total = data.allergyTests?.total;
            } catch (e) {
                this.$pToast.open({
                    message: e.message || 'Something went wrong',
                    error: true
                });
            }
            this.loading = false;
        },
        filterAllergyTests(value) {
            if (this.tabSelected === 0) {
                if (value.type === 'allergy-test-self') {
                    return value;
                }
            } else if (this.tabSelected === 1) {
                if (value.type === 'allergy-test-german') {
                    return value;
                }
            } else {
                if (value.type === 'allergy-test-swedish') {
                    return value;
                }
            }
        },
        handleNext() {
            this.options.page++;
            this.changeUrl(this.options);
            this.setOptions();
        },
        handlePrevious() {
            this.options.page--;
            this.changeUrl(this.options);
            this.setOptions();
        },
        handleEdit(id) {
            this.$router.push({name: 'allergy-test.edit', params: {allergyTestId: id}});
        },
        async handleDelete(item) {
            let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete <b>${item.title}</b> Test?`, {
                primaryAction: {
                    destructive: true,
                },
            });
            if (!isConfirmed) {
                return;
            }
            try {
                await axios.delete(`app/allergy-tests/${item.id}`);
                await this.getAllergyTests();
                this.$pToast.open({
                    message: 'deleted successfully',
                });
            } catch (e) {
                this.$pToast.open({
                    message: e.message || 'Something went wrong',
                    error: true,
                });
            }
        },
        handleCopy(uuid) {
            this.setClip(uuid);
            this.$pToast.open({
                message: 'Copied Uuid to clipboard',
            })
        }
    },
    async created() {
        this.$pLoading.start();
        this.pageLoading = true;

        // await this.setOptions();

        this.$pLoading.finish();
        this.pageLoading = false;
    },
}
</script>

<style scoped>

</style>
