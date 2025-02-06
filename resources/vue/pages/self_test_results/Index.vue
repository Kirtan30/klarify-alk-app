<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Results">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Results">
        <PButton primary slot="primaryAction" @click="aggregateResult = true">Aggregate Results(CSV)</PButton>
        <ValidationObserver ref="aggregateModal">
            <PModal
                :open="aggregateResult"
                sectioned
                title="Enter Email Address"
                @close="handleAggregateModalClose"
                class="custom_class"
            >
                <PFormLayout>
                    <ValidationProvider name="Export Email" rules="required|email" v-slot="{ errors }">
                        <PTextField
                            label="Enter Email"
                            v-model="aggrgateOptions.email"
                            :error="errors[0]"
                        />
                    </ValidationProvider>
                    <PDatePicker
                        id="dateRange"
                        autoApply
                        :singleDatePicker="false"
                        placeholder="Select Date"
                        v-model="dateRange"
                        @updateValues="handleDateFilter"
                        opens="left"
                        clearable
                        @change="handleDateChange"
                    />
                </PFormLayout>
                <template v-slot:footer>
                    <PButton
                        primary
                        :loading = 'modalExportLoading'
                        @click="handleAggregateResults"
                    >
                        Export
                    </PButton>
                </template>
            </PModal>
        </ValidationObserver>
        <PCard sectioned>
        <PDataTable
            :resourceName="{ singular: 'Self Test Result', plural: 'Self Test Results' }"
            :headings="headings"
            :rows="results"
            :has-pagination="true"
            :pagination="pagination"
            :loading="loading"
            :sort="sorting"
            @sort-changed="sortData"
        >
            <template v-slot:item.date="{ item }">
                {{ item.date }}
            </template>
            <template v-slot:item.actions="{item}">
                <PStack>
                    <PStackItem>
                        <PIcon source="ImportMinor" color="primary" @click="generatePdf(item.uuid)" v-p-tooltip="'Download Pdf'"/>
                    </PStackItem>
                    <PStackItem>
                        <PIcon source="ClipboardMinor" @click="handleCopy(item.uuid)"/>
                    </PStackItem>
                </PStack>
            </template>
            <PTextStyle variation="subdued" slot="footer">Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} Records</PTextStyle>
        </PDataTable>
        </PCard>
    </PPage>
</template>

<script>
import {mapActions} from "vuex";

export default {
    name: "SelfTestResults",
    data() {
        return {
            headings: [
                {
                    content: 'Date',
                    value: 'date',
                    type: 'text'
                },
                {
                    content: 'Score',
                    value: 'score',
                    type: 'text',
                },
                {
                    content: 'Actions',
                    value: 'actions',
                    type: 'text',
                    sortable: false
                },
            ],
            results: [],
            pdfLink: [],
            options: {
                sortBy: null,
                sortOrder : null,
                page : null,
                perPage: null,
            },
            defaultOptions: {
                sortBy: 'date',
                sortOrder : null,
                page : 1,
                perPage: 5,
            },
            pagination: {
                hasPrevious : false,
                hasNext : false,
                onNext: this.handleNext,
                onPrevious: this.handlePrevious,
                from: 0,
                to: 0,
                total: 0
            },
            loading: false,
            pageLoading: false,
            sorting: {
                value: 'date',
                direction: 'descending',
            },
            aggregateResult: false,
            aggrgateOptions: {
                email: '',
                startDate: '',
                endDate: '',
            },
            dateRange: '',
            modalExportLoading: false,
        }
    },
    methods: {
        ...mapActions('common', ['setClip']),
        async setOptions() {
            for (const [key] of Object.entries(this.options)) {
                if (this.$route.query[key]) {
                    this.options[key] = this.$route.query[key];

                    if (key === 'sortOrder') {
                        this.sorting.direction = this.options[key];
                    }
                }
                else {
                    this.options[key] = this.defaultOptions[key];
                }
            }

            await this.getResults();
        },
        changeUrl() {
            this.$router.push({ query: this.options })
        },
        async getResults() {
            this.loading = true;
            try {
                let { data } = await axios.get('app/self-test/results', {
                    params: this.options
                });
                this.pagination.hasNext = !!(data.results?.next_page_url);
                this.pagination.hasPrevious = !!(data.results?.prev_page_url);
                this.pagination.from = data.results?.from || 0;
                this.pagination.to = data.results?.to || 0;
                this.pagination.total = data.results?.total || 0;
                this.results = data.results.data || [];
            } catch (exception) {
                this.$pToast.open({
                    message: exception.message ? exception.message : exception,
                    error: true
                });
            }
            this.loading = false;
        },
        async generatePdf(uuid) {
            try {
                let res = await axios.get(`app/self-test/results/${uuid}/download`, {
                    responseType: 'blob'
                })
                this.pdfLink = window.URL.createObjectURL(new Blob([res.data]));
                this.downloadPdf();
            } catch (exception) {
                this.$pToast.open({
                    message: exception.message ? exception.message : exception,
                    error: true
                });
            }
        },
        downloadPdf() {
            let link = document.createElement('a');
            link.href = this.pdfLink;
            link.setAttribute('download', 'result.pdf');
            document.body.appendChild(link);
            link.click();
            link.remove();
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
        async sortData(key, direction) {
            this.sorting.direction = direction;
            this.sorting.value = key;
            this.options.sortOrder = direction;
            this.options.sortBy = key;
            this.changeUrl(this.options);
            await this.setOptions();
        },
        handleCopy(uuid) {
            this.setClip(uuid);
            this.$pToast.open({
                message: 'Copied Uuid to clipboard',
            })
        },
        async handleAggregateResults() {
            let isValidated = await this.$refs.aggregateModal.validate();
            if (!isValidated) {
                return;
            }

            this.modalExportLoading = true;

            try {
                let { data } = await axios.get(`app/self-test/results/exportcsv`, {
                    params: this.aggrgateOptions,
                })
                this.$pToast.open({
                    message: data.message,
                });
            } catch (exception) {
                this.$pToast.open({
                    message: exception.message ? exception.message : exception,
                    error: true
                });
            }

            this.dateRange = '';
            this.aggregateResult = false;
            this.aggrgateOptions.startDate = '';
            this.aggrgateOptions.endDate = '';
            this.aggrgateOptions.email = '';
            this.modalExportLoading = false;
        },
        async handleDateFilter() {
            this.aggrgateOptions.startDate = new Date(this.dateRange.startDate).toDateString();
            this.aggrgateOptions.endDate = new Date(this.dateRange.endDate).toDateString();
        },
        async handleDateChange(oldValue) {
            if(!oldValue.startDate || !oldValue.endDate) {
                this.aggrgateOptions.startDate = this.aggrgateOptions.endDate = this.dateRange = "";
            }
        },
        handleAggregateModalClose() {
            this.dateRange = '';
            this.aggregateResult = false;
            this.aggrgateOptions.startDate = '';
            this.aggrgateOptions.endDate = '';
            this.aggrgateOptions.email = '';
            this.modalExportLoading = false;
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
