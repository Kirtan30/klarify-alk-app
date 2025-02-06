<template>
    <div class="lexicon-modify">
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
                   content: 'lexicons',
                   to: {name: 'lexicons'}
               }
           ]"
        >
            <ValidationObserver ref="lexicons">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="lexicon Details"
                        description="Please Enter lexicon Details"
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
                                            :error="errors[0] || formErrors.handle"
                                            @input="form.handle = $root.$slugify(form.handle)"
                                        />
                                    </ValidationProvider>
                                </PFormLayoutGroup>

                                <PLabel id="content">Content</PLabel>
                                <ckeditor
                                    v-model="form.content"
                                    :config="configObject"
                                />
                                <PDatePicker
                                    id="date"
                                    v-model="form.date"
                                    label="Date"
                                    single-date-picker
                                    auto-apply
                                    format="DD-MM-YYYY"
                                    placeholder="Select Date"
                                    clearable
                                />
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
            configObject: {
                allowedContent: true,
            },
            lexiconImage: [],
            lexiconStaticContents: [],
            form: {
                id: null,
                name: '',
                handle: '',
                content: '',
                date: ''
            },
            tempForm: {},
            formErrors: {
                name: '',
                handle: '',
            },
            contextualSaveBar: {
                open: false,
                save: {
                    loading: false,
                    onAction: this.handleSave
                },
                discard: {
                    onAction: this.handleDiscardChanges
                }
            },
        }
    },
    methods: {
        async handleDiscardChanges () {
            let isConfirmed = await this.$root.$confirm('Discard', `Are you sure want to discard all changes?`);
            if (!isConfirmed) {
                return;
            }
            this.form = JSON.parse(JSON.stringify(this.tempForm));
            this.manageTempForm();
        },
        async handleSave() {
            let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
            if (!isConfirmed) {
                return;
            }
            let validated = await this.$refs.lexicons.validate();
            if (!validated) {
                return;
            }
            this.contextualSaveBar.save.loading = true;
            try {
                let config = {
                    headers: {
                        contentType: 'multipart/form-data'
                    }
                }
                let parameters = {...this.form, date: typeof this.form.date === 'string' ? this.form.date : this.form.date.startDate}

                let { data } = this.form.id ?
                    await axios.put(`/app/lexicons/${this.form.id}`, parameters) :
                    await axios.post('/app/lexicons', parameters);

                this.manageTempForm();
                this.$pToast.open(data.message || 'lexicon saved successfully');
                await this.$router.push({name: 'lexicons'});
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
        async getLexicon(lexiconId) {
            try {
                let {data} = await axios.get(`/app/lexicons/${lexiconId}`);
                this.form = data?.lexicon || {};
                if (!this.form.variables) {
                    this.form.variables = {}
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
        if (this.$route.params.lexiconId) {
            await this.getLexicon(this.$route.params.lexiconId);
        }

        this.manageTempForm();
    }
}
</script>

<style scoped>

</style>
