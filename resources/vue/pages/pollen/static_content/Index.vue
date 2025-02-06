<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Static Contents">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Pollen Static Contents">
        <PButton primary slot="primaryAction" @click="handleAdd">Add</PButton>
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'Static Content', plural: 'StaticContents'}"
                :headings="headings"
                :rows="pollenStaticContents"
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
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} StaticContents</PTextStyle>
            </PDataTable>
        </PCard>
    </PPage>
</template>

<script>

export default {
    name: "StaticContents",
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
                    content: 'Actions',
                    value: 'actions',
                    type: 'text',
                    sortable: false,
                    width: '10%'
                },
            ],
            pollenStaticContents: [],
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
            dateRange: null
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

            await this.getPollenStaticContents();
        },
        changeUrl() {
            this.$router.push({ query: this.options })
        },
        async getPollenStaticContents () {
            this.loading = true;
            try {
                let { data } = await axios.get('/app/pollen/static-contents', {
                    params: this.options
                })
                this.pollenStaticContents = data.pollenStaticContents?.data || [];
                this.pagination.hasNext = !!(data.pollenStaticContents?.next_page_url);
                this.pagination.hasPrevious = !!(data.pollenStaticContents?.prev_page_url);
                this.pagination.from = data.pollenStaticContents?.from;
                this.pagination.to = data.pollenStaticContents?.to;
                this.pagination.total = data.pollenStaticContents?.total;
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
            this.$router.push({name: 'pollen.static-contents.create'});
        },
        handleEdit(id) {
            this.$router.push({name: 'pollen.static-contents.edit', params: {staticContentId: id}});
        },
        async handleDelete(item) {
            let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete <b>${item.name}</b> pollen static content?`, {
                primaryAction: {
                    destructive: true,
                },
            });
            if (!isConfirmed) {
                return;
            }
            try {
                await axios.delete(`/app/pollen/static-contents/${item.id}`);
                await this.getPollenStaticContents();
                this.$pToast.open({
                    message: 'Pollen Static Content removed successfully',
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
