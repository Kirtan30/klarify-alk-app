<template>
    <div class="quiz-modify">
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
                   content: 'News',
                   to: {name: 'news'}
               }
           ]"
        >
            <ValidationObserver ref="news">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="News Details"
                        description="Please Enter News Details."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <ValidationProvider name="Title" rules="required" v-slot="{ errors }">
                                    <PTextField
                                        v-model="form.title"
                                        label="Title*"
                                        :error="errors[0] || formErrors.title"
                                    />
                                </ValidationProvider>
                                <div>
                                    <PLabel id="news_description" style="margin-bottom: 5px;">Description</PLabel>
                                    <ckeditor
                                        v-model="form.description"
                                    />
                                </div>
                                <PDatePicker
                                    id="date"
                                    v-model="form.date"
                                    label="Date"
                                    single-date-picker
                                    auto-apply
                                    format="DD-MM-YYYY"
                                />
                                <PTextField
                                    label="Image"
                                    id="image"
                                    :multiple="false"
                                    type="file"
                                    clearable
                                    v-model="form.image"
                                />
                                <PImage
                                    v-if="form.image"
                                    :source="typeof form.image != 'string' ? createImageUrl() : form.image"
                                    alt="Image"
                                    style="max-width:250px"
                                />
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="News CTA"
                        description="Please Enter News CTA details."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <div style="display: flex; gap: 20px;">
                                    <div style="width: 100%;">
                                        <div v-for="(item, index) in form.cta" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                            <PTextField
                                                v-model="form.cta[index].label"
                                                label="CTA Label"
                                            />
                                            <PTextField
                                                v-model="form.cta[index].link"
                                                label="CTA Link"
                                            />
                                            <PIcon
                                                source="DeleteMinor"
                                                color="critical"
                                                @click="removeCta(index)"
                                                style="cursor: pointer;"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <PIcon
                                            source="PlusMinor"
                                            color="primary"
                                            @click="addCta"
                                            style="cursor: pointer;"
                                        />
                                    </div>
                                </div>
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
                ctaObject: {
                    label: '',
                    link: '',
                },
                form: {
                    id: null,
                    title: '',
                    description: '',
                    date: '',
                    image: null,
                    cta: []
                },
                tempForm: {},
                formErrors: {
                    title: '',
                    image: '',
                },
                contextualSaveBar: {
                    open: false,
                    save: {
                        loading: false,
                        onAction: this.handleSaveNews
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
            async handleSaveNews() {
                let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
                if (!isConfirmed) {
                    return;
                }
                let validated = await this.$refs.news.validate();
                if (!validated) {
                    return;
                }
                this.contextualSaveBar.save.loading = true;
                try {
                    let parameters = {...this.form };
                    if (this.form.date?.startDate) {
                        parameters.date = this.form.date.startDate;
                    }
                    let { data } = this.form.id ?
                        await axios.put(`/app/news/${this.form.id}`, parameters) :
                        await axios.post('/app/news', parameters);

                    let config = {
                        headers: {
                            contentType: 'multipart/form-data'
                        }
                    }

                    let formData = new FormData();
                    if (data && data.news && data.news.id &&
                        (this.form.image && this.form.image.length && (this.form.image[0] instanceof File))
                    ) {
                        formData.append('image', this.form.image[0]);
                        await axios.post(`/app/news/${data.news.id}/upload`, formData, config);
                    }

                    this.manageTempForm();
                    this.$pToast.open(data.message || 'News saved successfully');
                    await this.$router.push({name: 'news'});
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
            addCta() {
                if (this.form.cta.length === 2) {
                    this.$pToast.open({
                        error: true,
                        message: 'Maximum 2 cta are allowed'
                    })
                    return;
                }
                this.form.cta.push(this.ctaObject);
            },
            async removeCta(index) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this cta?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                this.form.cta.splice(index, 1);
            },
            manageTempForm() {
                this.tempForm = JSON.parse(JSON.stringify(this.form));
                this.contextualSaveBar.save.loading = false;
                this.contextualSaveBar.open = false;
            },
            async getNews(newsId) {
                try {
                    let {data} = await axios.get(`/app/news/${newsId}`);
                    this.form = data.news || {};

                    if (!this.form.description) {
                        this.form.description = '';
                    }
                } catch ({response}) {
                    this.$pToast.open({
                        error: true,
                        message: response?.data?.message || 'Something went wrong'
                    });
                }
            },
            createImageUrl() {
                return URL.createObjectURL(this.form.image && this.form.image.length && this.form.image[0]);
            }
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
            if (this.$route.params.newsId) {
                await this.getNews(this.$route.params.newsId);
            }

            this.manageTempForm();
        }
    }
</script>

<style scoped>

</style>
