<template>
    <div class="static-content-modify">
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
                   content: 'Static Contents',
                   to: {name: 'pollen.static-contents'}
               }
           ]"
        >
            <ValidationObserver ref="static-contents">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="Static Content details"
                        description="Please Enter static content details."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <ValidationProvider name="Name" rules="required" v-slot="{ errors }">
                                    <PTextField
                                        v-model="form.name"
                                        label="Name"
                                        :error="errors[0] || formErrors.name"
                                    />
                                </ValidationProvider>
                                <PLabel id="static_content">Static Content</PLabel>
                                <ValidationProvider name="Static Content" rules="required" v-slot="{ errors }">
<!--                                    <ckeditor
                                        v-model="form.content"
                                        :config="configObject"
                                    />-->
                                    <codemirror
                                        v-model="form.content"
                                        :autofocus="true"
                                        :indent-with-tab="true"
                                        :tab-size="4"
                                    />
                                    <br>
                                    <span style="color: red" v-if="errors[0] || formErrors.content">{{ errors[0] || formErrors.content }}</span>
                                </ValidationProvider>
                                <PMultiSelect
                                    label="Variables"
                                    :options="[]"
                                    textField="name"
                                    valueField="value"
                                    :taggable="true"
                                    v-model="form.variables"
                                    :multiple="true"
                                    placeholder="variables"
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
            form: {
                id: null,
                name: '',
                content: '',
                variables: []
            },
            tempForm: {},
            formErrors: {
                name: '',
                content: '',
            },
            contextualSaveBar: {
                open: false,
                save: {
                    loading: false,
                    onAction: this.handleSaveStaticContent
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
        async handleSaveStaticContent() {
            let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
            if (!isConfirmed) {
                return;
            }
            let validated = await this.$refs["static-contents"].validate();
            if (!validated) {
                return;
            }
            this.contextualSaveBar.save.loading = true;
            try {
                let parameters = {
                    ...this.form,
                };

                let { data } = this.form.id ?
                    await axios.put(`/app/pollen/static-contents/${this.form.id}`, parameters) :
                    await axios.post('/app/pollen/static-contents', parameters);
                this.manageTempForm();
                this.$pToast.open(data.message || 'Static Content saved successfully');
                await this.$router.push({name: 'pollen.static-contents'});
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
        async getStaticContent(staticContentId) {
            try {
                let {data} = await axios.get(`/app/pollen/static-contents/${staticContentId}`);
                this.form = data?.pollenStaticContent || {};
                let variables = [];
                (this.form.variables || []).forEach((item, index) => {
                    variables.push({name: item, value: item});
                });
                this.form.variables = variables;
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
        if (this.$route.params.staticContentId) {
            await this.getStaticContent(this.$route.params.staticContentId);
        }

        this.manageTempForm();
    }
}
</script>

<style scoped>

</style>
