<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Regions">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="FAD Regions">
<!--        <PButton primary slot="primaryAction" @click="handleAdd">Add</PButton>-->
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'Region', plural: 'Regions'}"
                :headings="headings"
                :rows="fadRegions"
                :has-pagination="true"
                :pagination="pagination"
                :loading="loading"
                has-filter
                @input-filter-changed="handleSearch"
            >
                <template v-slot:item.language="{ item }">
                    {{ item.language_id ? shopLanguages[item.language_id] : '-' }}
                </template>
                <template v-slot:item.hasStaticContent="{ item }">
                    <PTag :tag="{'value' : item.has_static_content ? 'Yes' : 'No', 'key' : item.id}"/>
                </template>
                <template v-slot:item.enabled="{ item }">
                    <PBadge
                        :status="item.enabled ? 'success' : 'new'"
                        class="badge"
                    >
                        {{ item.enabled ? 'Enabled' : 'Disabled' }}
                    </PBadge>
                </template>
                <template v-slot:item.popular="{ item }">
                    <PTag :tag="{value: item.is_popular ? 'Yes' : 'No', key: item.id}"/>
                </template>
                <template v-slot:item.actions="{ item }">
                    <PStack>
                        <PStackItem>
                            <PIcon source="EditMinor" @click="handleEdit(item.id)"/>
                        </PStackItem>
<!--                        <PStackItem>
                            <PIcon source="DeleteMinor" color="critical" @click="handleDelete(item)"/>
                        </PStackItem>-->
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
                            {{ options.language ? shopLanguages[options.language] : 'Languages' }}
                        </PButton>
                        <POptionList
                            slot="content"
                            :selected="[options.language]"
                            :options="languageOptions"
                            @click="selectLanguage"
                        />
                    </PPopover>
                </PButtonGroup>
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} Regions</PTextStyle>
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
                    content: 'Language',
                    value: 'language',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Has Static Content',
                    value: 'hasStaticContent',
                    type: 'text',
                    sortable: false
                },
                {
                    content: 'Enabled',
                    value: 'enabled',
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
            fadRegions: [],
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
                language: '',
            },
            defaultOptions: {
                page: 1,
                perPage: 10,
                search: null,
                language: '',
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

            await this.getFadRegions();
        },
        changeUrl() {
            this.$router.push({ query: this.options })
        },
        async getFadRegions () {
            this.loading = true;
            try {
                let { data } = await axios.get('/app/fad/regions', {
                    params: this.options
                })
                this.fadRegions = data.fadRegions?.data || [];
                this.shopLanguages = data.shopLanguages || {};
                if (this.languageOptions.length === 1) {
                    for (const [key, value] of Object.entries(this.shopLanguages)) {
                        this.languageOptions.push({
                            label: value,
                            value: key
                        });
                    }
                }
                this.pagination.hasNext = !!(data.fadRegions?.next_page_url);
                this.pagination.hasPrevious = !!(data.fadRegions?.prev_page_url);
                this.pagination.from = data.fadRegions?.from;
                this.pagination.to = data.fadRegions?.to;
                this.pagination.total = data.fadRegions?.total;
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
        // handleAdd() {
        //     this.$router.push({name: 'fad.regions.create'});
        // },
        handleEdit(id) {
            this.$router.push({name: 'fad.regions.edit', params: {regionId: id}});
        },
        /*async handleDelete(item) {
            let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete <b>${item.name}</b> fad region?`, {
                primaryAction: {
                    destructive: true,
                },
            });
            if (!isConfirmed) {
                return;
            }
            try {
                await axios.delete(`/app/fad/regions/${item.id}`);
                await this.getFadRegions();
                this.$pToast.open({
                    message: 'FAD Region removed successfully',
                });
            } catch (e) {
                this.$pToast.open({
                    message: e.message || 'Something went wrong',
                    error: true,
                });
            }
        },*/
        async handleSearch(value) {
            this.options.search = value;
            this.options.page = 1;
            await this.changeUrl(this.options);
            await this.setOptions();
        },
        async selectLanguage(value) {
            this.activeLanguage = false;
            if (this.options.language === value[0]) return;
            this.options.language = value[0];
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
