<template>
    <PSkeletonPage v-if="pageLoading" full-width title="lexicons">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Lexicons">
        <PButtonGroup
            slot="primaryAction"
        >
            <PButton
                primary
                @click="handleMetafieldsSync"
                :loading="syncLoading"
            >
                Sync Metafields
            </PButton>
            <PButton primary @click="handleAdd">Add</PButton>

        </PButtonGroup>
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'Lexicons', plural: 'Lexicons'}"
                :headings="headings"
                :rows="lexicons"
                :has-pagination="true"
                :pagination="pagination"
                :loading="loading"
                has-filter
                @input-filter-changed="handleSearch"
            >
                <template v-slot:item.actions="{ item }">
                    <PStack>
                        <PStackItem>
                            <PIcon source="EditMinor" @click="handleEdit(item.id)"/>
                        </PStackItem>
                        <PStackItem>
                            <PIcon source="DeleteMinor" color="critical" @click="handleDelete(item)"/>
                        </PStackItem>
                    </PStack>
                </template>
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} lexicons</PTextStyle>
            </PDataTable>
        </PCard>
    </PPage>
</template>

<script>
export default {
    name: "Index",
    data() {
        return {
            headings: [
                {
                    content: 'Name',
                    value: 'name',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Handle',
                    value: 'handle',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Actions',
                    value: 'actions',
                    type: 'text',
                    sortable: false,
                    width: '10%'
                },
            ],
            lexicons: [],
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
                search: null,
            },
            defaultOptions: {
                page: 1,
                perPage: 10,
                search: null,
            },
            loading: false,
            pageLoading: false,
            dateRange: null,
            syncLoading: false
        }
    },
    methods: {
        async setOptions() {
            for (const [key] of Object.entries(this.options)) {
                if (this.$route.query[key]) {
                    this.options[key] = this.$route.query[key];
                } else {
                    this.options[key] = this.defaultOptions[key];
                }
            }

            await this.getLexicons();
        },
        changeUrl() {
            this.$router.push({ query: this.options })
        },
        async handleMetafieldsSync() {
            this.syncLoading = true;
            try {
                let { data } = await axios.post('/app/lexicons/sync', {})
                this.$pToast.open(data?.message || 'Metafields synced successfully')
            } catch (e) {
                this.$pToast.open({
                    message: e.message || 'Something went wrong',
                    error: true
                });
            }
            this.syncLoading = false;
        },
        async getLexicons () {
            this.loading = true;
            try {
                let { data } = await axios.get('/app/lexicons', {
                    params: this.options
                })
                this.lexicons = data.lexicons?.data || [];
                this.pagination.hasNext = !!(data.lexicons?.next_page_url);
                this.pagination.hasPrevious = !!(data.lexicons?.prev_page_url);
                this.pagination.from = data.lexicons?.from;
                this.pagination.to = data.lexicons?.to;
                this.pagination.total = data.lexicons?.total;
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
        handleAdd() {
            this.$router.push({name: 'lexicons.create'});
        },
        handleEdit(id) {
            this.$router.push({name: 'lexicons.edit', params: {lexiconId: id}});
        },
        async handleDelete(item) {
            let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete <b>${item.name}</b> pollen city?`, {
                primaryAction: {
                    destructive: true,
                },
            });
            if (!isConfirmed) {
                return;
            }
            try {
                await axios.delete(`/app/lexicons/${item.id}`);
                await this.getLexicons();
                this.$pToast.open({
                    message: 'Lexicon removed successfully',
                });
            } catch (e) {
                this.$pToast.open({
                    message: e.message || 'Something went wrong',
                    error: true,
                });
            }
        },
        async handleSearch(value) {
            this.options.search = value;
            this.options.page = 1;
            await this.changeUrl(this.options);
            await this.setOptions();
        },
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
