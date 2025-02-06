<template>
    <div class="region-modify">
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
                   content: 'Regions',
                   to: {name: 'fad.regions'}
               }
           ]"
        >
            <ValidationObserver ref="regions">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="Page status details"
                        description="Please select the status of the Region."
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
                        title="Region Details"
                        description="Please Enter Region Details."
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
                        description="Please Enter static page content details for region."
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
            fadStaticContents: [],
            form: {
                id: null,
                parent_id: null,
                enabled: false,
                name: '',
                handle: '',
                has_static_content: false,
                is_popular: false,
                fad_page_content: null,
                variables: {}
            },
            tempForm: {},
            formErrors: {
                name: '',
                handle: '',
                has_static_content: false,
                is_popular: false,
                fad_page_content: null
            },
            contextualSaveBar: {
                open: false,
                save: {
                    loading: false,
                    onAction: this.handleSaveRegion
                },
                discard: {
                    onAction: this.handleDiscardChanges
                }
            },
        }
    },
    methods: {
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
        async handleSaveRegion() {
            let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
            if (!isConfirmed) {
                return;
            }
            let validated = await this.$refs.regions.validate();
            if (!validated) {
                return;
            }

            this.contextualSaveBar.save.loading = true;
            try {
                let parameters = {
                    ...this.form,
                    fad_page_content_id: this.form.fad_page_content?.id
                };

                if (!(this.form.fad_page_content && this.form.fad_page_content.variables && this.form.fad_page_content.variables.length)) {
                    this.form.variables = null;
                    parameters.variables = null;
                }

                let { data } = this.form.id ?
                    await axios.put(`/app/fad/regions/${this.form.id}`, parameters) :
                    await axios.post('/app/fad/regions', parameters);

                this.manageTempForm();
                await this.$router.push({name: 'fad.regions'});
                this.$pToast.open(data.message || 'Region saved successfully');
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
        async getRegion(regionId) {
            try {
                let {data} = await axios.get(`/app/fad/regions/${regionId}`);
                this.form = data.fadRegion || {};

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
        await this.getFadStaticContents();
        if (this.$route.params.regionId) {
            await this.getRegion(this.$route.params.regionId);
        }

        this.manageTempForm();
    }
}
</script>

<style scoped>

</style>
