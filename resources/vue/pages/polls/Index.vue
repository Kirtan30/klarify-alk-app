<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Polls">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Polls">
        <PButton primary slot="primaryAction" @click="handleAdd">Add</PButton>
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'Polls', plural: 'Polls'}"
                :headings="headings"
                :rows="polls"
                :has-pagination="true"
                :pagination="pagination"
                :loading="loading"
                @input-filter-changed="handleSearch"
            >
                <PDatePicker
                    id="dateRange"
                    autoApply
                    :singleDatePicker="false"
                    placeholder="Select Date"
                    slot="filter"
                    v-model="dateRange"
                    @updateValues="handleDateFilter"
                    opens="left"
                    clearable
                    @change="handleDateChange"
                />
                <template v-slot:item.answers="{item}">
                    <div v-for="(answer, index) in item.answers" :key="index">
                        <div>
                            <PTag :tag="{'value':answer.title, 'key':answer.title}"/>
                            <PTag :tag="{'value':'count:'+answer.response_count, 'key':answer.response_count}"/>
                            <PTag :tag="{'value':answer.percentage+'%', 'key':answer.percentage}"/>
                        </div>
                        <br/>
                    </div>
                </template>
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
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} Polls</PTextStyle>
            </PDataTable>
        </PCard>
    </PPage>
</template>

<script>
    import {mapActions} from "vuex";

    export default {
        name: "Polls",
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
                        content: 'Answers',
                        value: 'answers',
                        type: 'text',
                        sortable: false
                    },
                    {
                        content: 'Total Count',
                        value: 'totalCount',
                        type: 'text',
                        sortable: false,
                        width: '10%'
                    },
                    {
                        content: 'Actions',
                        value: 'actions',
                        type: 'text',
                        sortable: false,
                        width: '10%'
                    },
                ],
                polls: [],
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
                    searchValue: null,
                },
                defaultOptions: {
                    page: 1,
                    perPage: 10,
                    startDate: "",
                    endDate: "",
                    searchValue: null,
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

                await this.getPolls();
            },
            changeUrl() {
                this.$router.push({ query: this.options })
            },
            async getPolls () {
                this.loading = true;
                try {
                    let { data } = await axios.get('/app/polls', {
                        params: this.options
                    })
                    this.polls = data.polls.data || [];
                    this.pagination.hasNext = !!(data.polls?.next_page_url);
                    this.pagination.hasPrevious = !!(data.polls?.prev_page_url);
                    this.pagination.from = data.polls?.from;
                    this.pagination.to = data.polls?.to;
                    this.pagination.total = data.polls?.total;
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
                this.$router.push({name: 'polls.create'});
            },
            handleEdit(id) {
                this.$router.push({name: 'polls.edit', params: {pollId: id}});
            },
            async handleDelete(item) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete <b>${item.title}</b> poll?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                try {
                    await axios.delete(`app/polls/${item.id}`);
                    await this.getPolls();
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
            },
            async handleDateFilter() {
                this.options.page = 1;
                this.options.startDate = new Date(this.dateRange.startDate).toDateString();
                this.options.endDate = new Date(this.dateRange.endDate).toDateString();
                await this.changeUrl(this.options);
                await this.setOptions();
            },
            async handleDateChange(oldValue) {
                if(!oldValue.startDate || !oldValue.endDate) {
                    this.options.page = 1;
                    this.options.startDate = this.options.endDate = this.dateRange = "";
                    await this.changeUrl(this.options);
                    await this.setOptions();
                }
            },
            async handleSearch(value) {
                this.options.page = 1;
                this.options.searchValue = value;
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
