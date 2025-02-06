<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Cities">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Pollen Cities">
        <PButton primary slot="primaryAction" @click="handleAdd">Add</PButton>
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'City', plural: 'Cities'}"
                :headings="headings"
                :rows="pollenCities"
                :has-pagination="true"
                :pagination="pagination"
                :loading="loading"
                has-filter
                @input-filter-changed="handleSearch"
            >
                <template v-slot:item.pollen_state="{ item }">
                    {{ item.pollen_state ? item.pollen_state.name : '-' }}
                </template>
                <template v-slot:item.pollen_region="{ item }">
                    {{ item.pollen_region ? item.pollen_region.name : '-' }}
                </template>
                <template v-slot:item.language="{ item }">
                    {{ item.language_id ? shopLanguages[item.language_id] : '-' }}
                </template>
                <template v-slot:item.has_static_content="{ item }">
                    <PTag :tag="{value: item.has_static_content ? 'Yes' : 'No', key: item.id}"/>
                </template>
                <template v-slot:item.popular="{ item }">
                    <PTag :tag="{value: item.is_popular ? 'Yes' : 'No', key: item.id}"/>
                </template>
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
                <PButtonGroup slot="filter" segmented>
                    <PPopover
                        id="popover_2"
                        :active="activeLanguage"
                        preferredAlignment="right"
                    >
                        <PButton slot="activator" :disclosure="activeLanguage ? 'up' : 'down'"
                                 @click="activeLanguage = !activeLanguage"
                        >
                            {{ options.languageId ? shopLanguages[options.languageId] : 'Languages' }}
                        </PButton>
                        <POptionList
                            slot="content"
                            :selected="[options.languageId]"
                            :options="languageOptions"
                            @click="selectLanguage"
                        />
                    </PPopover>
                </PButtonGroup>
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} Cities</PTextStyle>
            </PDataTable>
        </PCard>
    </PPage>
</template>

<script>

export default {
    name: "Cities",
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
                    content: 'State',
                    value: 'pollen_state',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Region',
                    value: 'pollen_region',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Language',
                    value: 'language',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Has Static Content',
                    value: 'has_static_content',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Popular',
                    value: 'popular',
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
            pollenCities: [],
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
                languageId: '',
            },
            defaultOptions: {
                page: 1,
                perPage: 10,
                search: null,
                languageId: '',
            },
            languageOptions: [{
                label: 'All',
                value: ''
            }],
            shopLanguages: {},
            activeLanguage: false,
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

            await this.getPollenCities();
        },
        changeUrl() {
            this.$router.push({ query: this.options })
        },
        async getPollenCities () {
            this.loading = true;
            try {
                let { data } = await axios.get('/app/pollen/cities', {
                    params: this.options
                })
                this.shopLanguages = data.shopLanguages || {};
                if (this.languageOptions.length === 1) {
                    for (const [key, value] of Object.entries(this.shopLanguages)) {
                        this.languageOptions.push({
                            label: value,
                            value: key
                        });
                    }
                }
                this.pollenCities = data.pollenCities?.data || [];
                this.pagination.hasNext = !!(data.pollenCities?.next_page_url);
                this.pagination.hasPrevious = !!(data.pollenCities?.prev_page_url);
                this.pagination.from = data.pollenCities?.from;
                this.pagination.to = data.pollenCities?.to;
                this.pagination.total = data.pollenCities?.total;
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
            this.$router.push({name: 'pollen.cities.create'});
        },
        handleEdit(id) {
            this.$router.push({name: 'pollen.cities.edit', params: {cityId: id}});
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
                await axios.delete(`/app/pollen/cities/${item.id}`);
                await this.getPollenCities();
                this.$pToast.open({
                    message: 'POLLEN City removed successfully',
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
        async selectLanguage(value) {
            this.activeLanguage = false;
            if (this.options.languageId === value[0]) return;
            this.options.languageId = value[0];
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
