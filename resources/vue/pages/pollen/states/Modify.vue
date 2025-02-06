<template>
    <div class="state-modify">
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
                   content: 'States',
                   to: {name: 'pollen.states'}
               }
           ]"
        >
            <ValidationObserver ref="states">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="Page status details"
                        description="Please select the status of the State."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PToggle
                                    id="popular"
                                    label="Popular?"
                                    :checked="form.is_popular"
                                    :value="form.is_popular"
                                    @change="form.is_popular = !form.is_popular"
                                />
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Language Details"
                        description="Please Select a Language"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PMultiSelect
                                        label="Languages"
                                        :options='pollenLanguages'
                                        textField="name"
                                        valueField="id"
                                        v-model="form.pollen_language"
                                        :multiple="false"
                                        placeholder="Language"
                                    />
                                    <PMultiSelect
                                        label="Parent Id"
                                        :options='defaultStates'
                                        textField="name"
                                        valueField="id"
                                        v-model="form.pollen_parent"
                                        :multiple="false"
                                        placeholder="Parent Id"
                                        :disabled = "handleParentIdVisibility()"
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="State Details"
                        description="Please Enter State Details."
                        v-if="form.pollen_language"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <ValidationProvider name="Name" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            v-model="form.name"
                                            label="Name*"
                                            :error="errors[0] || formErrors.name"
                                            @input="form.handle = $root.$slugify(form.name)"
                                        />
                                    </ValidationProvider>
                                    <ValidationProvider name="Handle" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            id="handle"
                                            label="Handle*"
                                            v-model="form.handle"
                                            @input="form.handle = $root.$slugify(form.handle)"
                                        />
                                    </ValidationProvider>
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <ValidationProvider name="Latitude" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            v-model="form.latitude"
                                            label="Latitude*"
                                            :error="errors[0] || formErrors.latitude"
                                        />
                                    </ValidationProvider>
                                    <ValidationProvider name="Longitude" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            v-model="form.longitude"
                                            label="Longitude*"
                                            :error="errors[0] || formErrors.longitude"
                                        />
                                    </ValidationProvider>
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Static page details"
                        description="Please Enter static page content details for city."
                        v-if="form.pollen_language"
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
                                        :options='pollenStaticContents'
                                        textField="name"
                                        valueField="id"
                                        v-model="form.pollen_page_content"
                                        :multiple="false"
                                        placeholder="Static Content"
                                        :error="errors[0]"
                                    />
                                </ValidationProvider>

                                <template v-if="form.has_static_content && form.pollen_page_content && (form.pollen_page_content.variables || []).length">
                                    <div v-for="(variable) in form.pollen_page_content.variables" :key="variable">
                                        <PLayoutAnnotatedSection
                                            :title = variable
                                        >
                                            <PFormLayout>
                                                <PTextField
                                                    v-model="form.variables[variable]"
                                                    label="Value"
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
            pollenLanguages: [],
            defaultLanguageId: null,
            defaultStates: [],
            pollenStaticContents: [],
            form: {
                id: null,
                pollen_language: null,
                language_id: null,
                name: '',
                handle: '',
                has_static_content: false,
                pollen_page_content: null,
                pollen_parent: null,
                parent_id: null,
                latitude: '',
                longitude: '',
                is_popular: false,
                variables: {}
            },
            tempForm: {},
            formErrors: {
                pollen_language: null,
                name: '',
                handle: '',
                has_static_content: false,
                pollen_page_content: null,
                pollen_parent: null,
                latitude: '',
                longitude: ''
            },
            contextualSaveBar: {
                open: false,
                save: {
                    loading: false,
                    onAction: this.handleSaveState
                },
                discard: {
                    onAction: this.handleDiscardChanges
                }
            },
        }
    },
    methods: {
        async getPollenLanguages() {
            try {
                let {data} = await axios.get('/app/pollen/languages');
                this.pollenLanguages = data.pollenLanguages || [];
                this.defaultLanguageId = data.defaultLanguageId || null;
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                });
            }
        },
        async getPollenStaticContents() {
            try {
                let {data} = await axios.get('/app/pollen/static-contents', { params: { perPage: -1 } });
                this.pollenStaticContents = data.pollenStaticContents?.data || [];
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                });
            }
        },
        async getDefaultStates() {
            try {
                let {data} = await axios.get('/app/pollen/states/default', {
                    params: {
                        pollenStateId: this.form.id
                    }
                });
                this.defaultStates = data.defaultStates || [];
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response?.data?.message || 'Something went wrong'
                });
            }
        },
        handleParentIdVisibility() {
            let disable = this.form.pollen_language === null || this.form.pollen_language?.id === this.defaultLanguageId;
            if (disable) {
                this.form.pollen_parent = this.form.parent_id = null;
            }

            return disable;
        },
        async handleDiscardChanges () {
            let isConfirmed = await this.$root.$confirm('Discard', `Are you sure want to discard all changes?`);
            if (!isConfirmed) {
                return;
            }
            this.form = JSON.parse(JSON.stringify(this.tempForm));
            this.manageTempForm();
        },
        async handleSaveState() {
            let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
            if (!isConfirmed) {
                return;
            }
            let validated = await this.$refs.states.validate();
            if (!validated) {
                return;
            }
            this.contextualSaveBar.save.loading = true;
            try {
                let parameters = {
                    ...this.form,
                    language_id: this.form.pollen_language?.id,
                    pollen_page_content_id: this.form.pollen_page_content?.id,
                    parent_id: (this.form.pollen_language?.id !== null && this.form.pollen_language?.id !== this.defaultLanguageId) ? this.form.pollen_parent?.id : null
                };

                if (!(this.form.pollen_page_content && this.form.pollen_page_content.variables && this.form.pollen_page_content.variables.length)) {
                    this.form.variables = null;
                    parameters.variables = null;
                }

                let { data } = this.form.id ?
                    await axios.put(`/app/pollen/states/${this.form.id}`, parameters) :
                    await axios.post('/app/pollen/states', parameters);

                this.manageTempForm();
                await this.$router.push({name: 'pollen.states'});
                this.$pToast.open(data.message || 'State saved successfully');
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
        async getState(stateId) {
            try {
                let {data} = await axios.get(`/app/pollen/states/${stateId}`);
                this.form = data.pollenState || {};

                if (!this.form.variables) {
                    this.form.variables = {}
                }

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
        await this.getPollenLanguages();
        await this.getPollenStaticContents();
        if (this.$route.params.stateId) {
            await this.getState(this.$route.params.stateId);
        }

        await this.getDefaultStates();
        this.manageTempForm();
    }
}
</script>

<style scoped>

</style>
