<template>
    <div class="city-modify">
        <PContextualSaveBar
            v-if="contextualSaveBar.open"
            :open-modal="contextualSaveBar.open"
            message="Unsaved Changes"
            :saveAction="contextualSaveBar.save"
            :discardAction="contextualSaveBar.discard"
            class="contextual-save-bar"
        />
        <PPage
            full-width
            :breadcrumbs="[
               {
                   content: 'Cities',
                   to: {name: 'fad.cities'}
               }
           ]"
        >
            <ValidationObserver ref="cities">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="Page status details"
                        description="Please select the status of the City."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PToggle
                                        id="enabled"
                                        label="Enabled?"
                                        :checked="form.enabled"
                                        :value="form.enabled"
                                        @change="form.enabled = !form.enabled"
                                    />
                                    <PToggle
                                        id="popular"
                                        label="Popular?"
                                        :checked="form.is_popular"
                                        :value="form.is_popular"
                                        @change="form.is_popular = !form.is_popular"
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="State / Region Details"
                        description="Please Select a State / Region"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PMultiSelect
                                        label="State"
                                        :options='fadStates'
                                        textField="name"
                                        valueField="id"
                                        v-model="form.fad_state"
                                        :multiple="false"
                                        placeholder="State"
                                        disabled
                                    />
                                    <PMultiSelect
                                        label="Region"
                                        :options='fadRegions'
                                        textField="name"
                                        valueField="id"
                                        v-model="form.fad_region"
                                        :multiple="false"
                                        placeholder="Region"
                                        disabled
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="City Details"
                        description="Please Enter City Details."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <ValidationProvider name="Name" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            v-model="form.name"
                                            label="Name"
                                            :error="errors[0] || formErrors.name"
                                            @input="form.handle = $root.$slugify(form.name)"
                                            :disabled="form.parent_id === null"
                                        />
                                    </ValidationProvider>
                                    <ValidationProvider name="Handle" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            id="handle"
                                            label="Handle"
                                            v-model="form.handle"
                                            @input="form.handle = $root.$slugify(form.handle)"
                                            :disabled="form.parent_id === null"
                                        />
                                    </ValidationProvider>
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Static page details"
                        description="Please Enter static page content details for city."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PToggle
                                    id="has_static_content"
                                    label="Has Static Content?"
                                    :checked="form.has_static_content"
                                    :value="form.has_static_content"
                                    @change="form.has_static_content = !form.has_static_content"
                                />
                                <ValidationProvider v-if="form.has_static_content" name="Static Content" :rules="form.has_static_content ? 'required' : ''" v-slot="{ errors }">
                                    <PMultiSelect
                                        label="Select Static Content"
                                        :options='fadStaticContents'
                                        textField="name"
                                        valueField="id"
                                        v-model="form.fad_page_content"
                                        :multiple="false"
                                        placeholder="Static Content"
                                        :error="errors[0]"
                                    />
                                </ValidationProvider>

                                <template v-if="form.has_static_content && form.fad_page_content && (form.fad_page_content.variables || []).length">
                                    <div v-for="(variable) in form.fad_page_content.variables" :key="variable">
                                        <PLayoutAnnotatedSection
                                            :title = variable
                                        >
                                            <PFormLayout>
                                                <PTextField
                                                    v-model="form.variables[variable]"
                                                    label="Value"
                                                    :multiline="false"
                                                >
                                                </PTextField>
                                            </PFormLayout>
                                        </PLayoutAnnotatedSection>
                                    </div>
                                </template>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                </PLayout>
            </ValidationObserver>
        </PPage>
    </div>
</template>

<script>
export default {
    name: "Modify",
    data() {
        return {
            fadStates: [],
            fadRegions: [],
            fadStaticContents: [],
            form: {
                id: null,
                parent_id: null,
                enabled: false,
                fad_state: null,
                fad_region: null,
                name: '',
                handle: '',
                variables: {},
                has_static_content: false,
                is_popular: false,
                fad_page_content: null
            },
            tempForm: {},
            formErrors: {
                name: '',
                handle: '',
                has_static_content: false,
                is_popular: false,
                content: '',
                fad_state: '',
                fad_region: '',
                fad_page_content: null
            },
            contextualSaveBar: {
                open: false,
                save: {
                    loading: false,
                    onAction: this.handleSaveCity
                },
                discard: {
                    onAction: this.handleDiscardChanges
                }
            },
        }
    },
    methods: {
        async getFadStates() {
            try {
                let {data} = await axios.get('/app/fad/states', { params: { perPage: -1, enabled: 1 } });
                this.fadStates = data.fadStates?.data || [];
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                });
            }
        },
        async getFadRegions() {
            try {
                let {data} = await axios.get('/app/fad/regions', { params: { perPage: -1, enabled: 1 } });
                this.fadRegions = data.fadRegions?.data || [];
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                });
            }
        },
        async getFadStaticContents() {
            try {
                let {data} = await axios.get('/app/fad/static-contents', { params: { perPage: -1 } });
                this.fadStaticContents = data.fadStaticContents?.data || [];
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                });
            }
        },
        async handleDiscardChanges () {
            let isConfirmed = await this.$root.$confirm('Discard', `Are you sure want to discard all changes?`);
            if (!isConfirmed) {
                return;
            }
            this.form = JSON.parse(JSON.stringify(this.tempForm));
            this.manageTempForm();
        },
        async handleSaveCity() {
            if (!this.form.id) {
                return;
            }
            let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
            if (!isConfirmed) {
                return;
            }
            let validated = await this.$refs.cities.validate();
            if (!validated) {
                return;
            }
            this.contextualSaveBar.save.loading = true;
            try {
                let parameters = {
                    ...this.form,
                    fad_state_id: this.form.fad_state?.id,
                    fad_region_id: this.form.fad_region?.id,
                    fad_page_content_id: this.form.fad_page_content?.id
                };

                if (!(this.form.fad_page_content && this.form.fad_page_content.variables && this.form.fad_page_content.variables.length)) {
                    this.form.variables = null;
                    parameters.variables = null;
                }

                let { data } = await axios.put(`/app/fad/cities/${this.form.id}`, parameters);
                this.manageTempForm();
                this.$pToast.open(data.message || 'City saved successfully');
                await this.$router.push({name: 'fad.cities'});
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                })

                if (response?.data?.errors) {
                    for (const [key, value] of Object.entries(response.data.errors)) {
                        this.formErrors[key] = value[0];
                    }
                }
            }
            this.contextualSaveBar.save.loading = false;
        },
        manageTempForm() {
            this.tempForm = JSON.parse(JSON.stringify(this.form));
            this.contextualSaveBar.save.loading = false;
            this.contextualSaveBar.open = false;
        },
        async getCity(cityId) {
            try {
                let {data} = await axios.get(`/app/fad/cities/${cityId}`);
                this.form = data?.fadCity || {};
                if (!this.form.variables) {
                    this.form.variables = {}
                }
                /*if (this.form.fad_state && !this.form.fad_state.enabled) {
                    this.form.fad_state = null;
                }
                if (this.form.fad_region && !this.form.fad_region.enabled) {
                    this.form.fad_region = null;
                }*/

                if (!this.form.content) {
                    this.form.content = '';
                }
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                });
            }
        },
    },
    watch: {
        form: {
            handler(form) {
                this.contextualSaveBar.open = JSON.stringify(form) !== JSON.stringify(this.tempForm);
            },
            deep: true
        }
    },
    async created() {
        await this.getFadStates();
        await this.getFadRegions();
        await this.getFadStaticContents();
        if (this.$route.params.cityId) {
            await this.getCity(this.$route.params.cityId);
        }

        this.manageTempForm();
    }
}
</script>

<style scoped>

</style>
