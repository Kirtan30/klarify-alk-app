<template>
    <PSkeletonPage v-if="pageLoading" full-width title="News">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="News">
        <PButton primary slot="primaryAction" @click="handleAdd">Add</PButton>
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'News', plural: 'News'}"
                :headings="headings"
                :rows="news"
                :has-pagination="true"
                :pagination="pagination"
                :loading="loading"
                has-filter
                @input-filter-changed="handleSearch"
            >
                <template v-slot:item.news_date="{ item }">
                    <span>{{ item.news_date ? item.news_date : '-' }}</span>
                </template>
                <template v-slot:item.actions="{ item }">
                    <PStack>
                        <PStackItem>
                            <PIcon source="EditMinor" @click="handleEdit(item.id)"/>
                        </PStackItem>
                        <PStackItem>
                            <PIcon source="DeleteMinor" color="critical" @click="handleDelete(item)"/>
                        </PStackItem>
<!--                        <PStackItem>
                            <PIcon source="ClipboardMinor" @click="handleCopy(item.uuid)"/>
                        </PStackItem>-->
                    </PStack>
                </template>
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} News</PTextStyle>
            </PDataTable>
        </PCard>
    </PPage>
</template>

<script>
    import {mapActions} from "vuex";

    export default {
        name: "News",
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
                        content: 'Date',
                        value: 'news_date',
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
                news: [],
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
                    startDate: "",
                    endDate: "",
                    search: null,
                },
                defaultOptions: {
                    page: 1,
                    perPage: 10,
                    startDate: "",
                    endDate: "",
                    search: null,
                },
                loading: false,
                pageLoading: false,
                dateRange: null
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

                await this.getNews();
            },
            changeUrl() {
                this.$router.push({ query: this.options })
            },
            async getNews () {
                this.loading = true;
                try {
                    let { data } = await axios.get('/app/news', {
                        params: this.options
                    })
                    this.news = data.news?.data || [];
                    this.pagination.hasNext = !!(data.news?.next_page_url);
                    this.pagination.hasPrevious = !!(data.news?.prev_page_url);
                    this.pagination.from = data.news?.from;
                    this.pagination.to = data.news?.to;
                    this.pagination.total = data.news?.total;
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
                this.$router.push({name: 'news.create'});
            },
            handleEdit(id) {
                this.$router.push({name: 'news.edit', params: {newsId: id}});
            },
            async handleDelete(item) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete <b>${item.title}</b> news?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                try {
                    await axios.delete(`app/news/${item.id}`);
                    await this.getNews();
                    this.$pToast.open({
                        message: 'News removed successfully',
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
            },
            async handleDateFilter() {
                this.options.startDate = new Date(this.dateRange.startDate).toDateString();
                this.options.endDate = new Date(this.dateRange.endDate).toDateString();
                await this.changeUrl(this.options);
                await this.setOptions();
            },
            async handleDateChange(oldValue) {
                if(!oldValue.startDate || !oldValue.endDate) {
                    this.options.startDate = this.options.endDate = this.dateRange = "";
                    await this.changeUrl(this.options);
                    await this.setOptions();
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
