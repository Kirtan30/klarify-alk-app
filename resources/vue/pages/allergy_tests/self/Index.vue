<template>
    <PPage full-width>
        <PLayout sectioned>
            <PCard sectioned>
                <PDataTable
                    :headings="headings"
                    :rows="allergyTests"
                    :has-pagination="true"
                    :pagination="pagination"
                    :loading="loading"
                >
                    <template v-slot:item.actions="{ item }">
                        <PStack>
                            <PStackItem>
                                <PIcon source="EditMinor" @click="handleEdit(item.id)"/>
                            </PStackItem>
                            <PStackItem>
                                <PIcon source="DeleteMinor" color="critical" @click="handleDelete(item)"/>
                            </PStackItem>
                            <PStackItem>
                                <PIcon source="ClipboardMinor" @click="handleCopy(item.uuid)"/>
                            </PStackItem>
                        </PStack>
                    </template>
                    <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} Quizzes</PTextStyle>
                </PDataTable>
            </PCard>
        </PLayout>
    </PPage>
</template>

<script>
import {mapActions} from "vuex";

export default {
    name: "SelfIndex",
    data() {
        return {
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
            allergyTests: [],
            pagination: {
                hasPrevious : false,
                hasNext : false,
                onNext: this.handleNext,
                onPrevious: this.handlePrevious,
                from: 0,
                to: 0,
                total: 0
            },
            options: {
                page: null,
                perPage: null,
                type: null,
            },
            defaultOptions: {
                page: 1,
                perPage: 10,
                type: 'allergy-test-self'
            },
            loading: false,
            pageLoading: false,
        }
    },
    methods: {
        ...mapActions('common', ['setClip']),
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

        await this.setOptions();

        this.$pLoading.finish();
        this.pageLoading = false;
    },
}
</script>

<style scoped>

</style>
