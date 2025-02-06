<template>
    <PSkeletonPage v-if="pageLoading" full-width title="Clinics">
        <PCard sectioned>
            <PLayoutSection>
                <PSkeletonBodyText :lines="6"/>
            </PLayoutSection>
        </PCard>
    </PSkeletonPage>
    <PPage v-else full-width title="Clinics">
        <PButtonGroup
            slot="primaryAction"
        >
            <PButton
                primary
                @click="handleMetafieldsSync"
                :loading="syncLoading"
            >
                Sync Doctors
            </PButton>
            <PButton
                primary
                @click="addClinic"
            >
                Add Doctor / Clinic
            </PButton>
        </PButtonGroup>
        <PCard sectioned>
            <PDataTable
                :resourceName="{singular: 'Doctor', plural: 'Doctors'}"
                :headings="headings"
                :rows="clinics"
                :has-pagination="true"
                :pagination="pagination"
                :loading="loading"
                has-filter
                @input-filter-changed="handleSearch"
            >
                <template v-slot:item.clinic_name="{item}">
                    {{ item.clinic_name ? item.clinic_name : '-'}}
                </template>
                <template v-slot:item.doctor_name="{item}">
                    {{ item.doctor_name ? item.doctor_name : '-' }}
                </template>
                <template v-slot:item.email="{item}">
                    {{ item.email ? item.email : '-' }}
                </template>
                <template v-slot:item.phone="{item}">
                    {{ item.phone ? item.phone : '-' }}
                </template>
                <PTextStyle variation="subdued" slot="footer">Showing {{pagination.from}} to {{pagination.to}} of {{pagination.total}} Records</PTextStyle>
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
            </PDataTable>
        </PCard>
    </PPage>
</template>

<script>
    export default {
        name: "Doctors",
        data() {
            return {
                headings: [
                    {
                        content: 'Clinic Name',
                        value: 'clinic_name',
                        type: 'text',
                        sortable: false
                    },
                    {
                        content: 'Doctor Name',
                        value: 'doctor_name',
                        type: 'text',
                        sortable: false
                    },
                    {
                        content: 'Email',
                        value: 'email',
                        type: 'text',
                        sortable: false
                    },
                    {
                        content: 'Phone',
                        value: 'phone',
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
                clinics: [],
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
                    search: null
                },
                defaultOptions: {
                    page: 1,
                    perPage: 20,
                },
                loading: false,
                pageLoading: false,
                syncLoading: false,
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

                await this.getDoctors();
            },
            changeUrl() {
                this.$router.push({ query: this.options })
            },
            async getDoctors () {
                this.loading = true;
                try {
                    let { data } = await axios.get('/app/clinics', {
                        params: this.options
                    })
                    this.clinics = data.clinics?.data || [];
                    this.pagination.hasNext = !!(data.clinics?.next_page_url);
                    this.pagination.hasPrevious = !!(data.clinics?.prev_page_url);
                    this.pagination.from = data.clinics?.from || 0;
                    this.pagination.to = data.clinics?.to || 0;
                    this.pagination.total = data.clinics?.total || 0;
                } catch ({response}) {
                    this.$pToast.open({
                        message: response.data?.message || 'Something went wrong',
                        error: true
                    });
                }
                this.loading = false;
            },
            async handleSearch(value) {
                this.options.search = value;
                this.options.page = 1;
                await this.changeUrl(this.options);
                await this.setOptions();
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
            async addClinic() {
                await this.$router.push({name: 'doctors.create'});
            },
            async handleMetafieldsSync() {
                let isConfirmed = await this.$root.$confirm('Sync', `Are you sure you want to sync `+this.pagination.total+` doctors?`);

                if (!isConfirmed) return;

                this.syncLoading = true;

                try {
                    let { data } = await axios.post('/app/clinics/sync', {
                        'countryCode' : this.options.countryCode
                    });

                    this.$pToast.open({
                        message: data.message
                    });
                } catch (e) {
                    this.$pToast.open({
                        message: e.message || 'Something went wrong',
                        error: true
                    })
                }
                this.syncLoading = false;
            },
            handleEdit(id) {
                this.$router.push({name: 'doctors.edit', params: {doctorId: id}});
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
                    await axios.delete(`app/clinics/${item.id}`);
                    await this.getDoctors();
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
