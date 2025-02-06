<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Quizzes">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Quizzes">
        <PButton primary slot="primaryAction" @click="handleAdd">Add</PButton>
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'Quiz', plural: 'Quizzes'}"
                :headings="headings"
                :rows="quizzes"
                :has-pagination="true"
                :pagination="pagination"
                :loading="loading"
            >
                <template v-slot:item.theme="{item}">
                    <PBadge>{{item.theme}}</PBadge>
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
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} Quizzes</PTextStyle>
            </PDataTable>
        </PCard>
    </PPage>
</template>

<script>
    import {mapActions} from 'vuex'
    export default {
        name: "Quizzes",
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
                        content: 'Theme',
                        value: 'theme',
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
                quizzes: [],
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
                },
                defaultOptions: {
                    page: 1,
                    perPage: 20,
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

                await this.getQuizzes();
            },
            changeUrl() {
                this.$router.push({ query: this.options })
            },
            async getQuizzes () {
                this.loading = true;
                try {
                    let { data } = await axios.get('/app/quizzes', {
                        params: this.options
                    })
                    this.quizzes = data.quizzes.data || [];
                    this.pagination.hasNext = !!(data.quizzes?.next_page_url);
                    this.pagination.hasPrevious = !!(data.quizzes?.prev_page_url);
                    this.pagination.from = data.quizzes?.from;
                    this.pagination.to = data.quizzes?.to;
                    this.pagination.total = data.quizzes?.total;
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
                this.$router.push({name: 'quizzes.create'});
            },
            handleEdit(id) {
                this.$router.push({name: 'quizzes.edit', params: {quizId: id}});
            },
            async handleDelete(item) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete <b>${item.title}</b> quiz?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                try {
                    await axios.delete(`app/quizzes/${item.id}`);
                    await this.getQuizzes();
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
