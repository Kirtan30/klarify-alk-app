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
        <PPage full-width title="Settings">
            <PHorizontalDivider style="margin-bottom: 20px;" />
            <ValidationObserver ref="settings">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="Google API settings"
                        description="Add google api credential details."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PTextField
                                    label="API key"
                                    v-model="form.settings.google_api_key"
                                />
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Shop Domain Settings"
                        description="Add shop country and domain details."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PStack distribution="fillEvenly">
                                    <PStackItem >
                                        <ValidationProvider name="Country Name" rules="required" v-slot="{ errors }">
                                            <PMultiSelect
                                                label="Country Name*"
                                                :options=countries
                                                :error="errors[0]"
                                                textField="name"
                                                valueField="id"
                                                placeholder="Select Country"
                                                :multiple="false"
                                                v-model="form.country"
                                            />
                                        </ValidationProvider>
                                    </PStackItem>
                                    <PStackItem>
                                        <PTextField
                                            label="Public Domain"
                                            v-model="form.public_domain"
                                        />
                                    </PStackItem>
                                </PStack>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Shop Language Settings"
                        description="Add shop languages."
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PStack distribution="fillEvenly">
                                    <PStackItem>
                                        <ValidationProvider name="Language" rules="required" v-slot="{ errors }">
                                            <PMultiSelect
                                                label="Languages*"
                                                :options=languages
                                                :error="errors[0]"
                                                textField="name"
                                                valueField="code"
                                                placeholder="Select Languages"
                                                v-model="form.languages"
                                                @input="handleLanguage"
                                            />
                                        </ValidationProvider>
                                    </PStackItem>
                                    <PStackItem>
                                        <ValidationProvider name="Default Language" rules="required" v-slot="{ errors }">
                                            <PMultiSelect
                                                label="Default Language*"
                                                :options=form.languages
                                                :error="errors[0]"
                                                textField="name"
                                                valueField="code"
                                                placeholder="Select Default Language"
                                                :multiple="false"
                                                v-model="form.default_language"
                                            />
                                        </ValidationProvider>
                                    </PStackItem>
                                </PStack>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        v-if="form.languages.length"
                        title="Clinic Page Settings"
                        description="Add clinic / doctor page details."
                    >
                        <PCard sectioned>
                            <div v-for="(item, index) in form.languages" :key="index" v-if="form.languages[index].pivot">
                                <PLayoutAnnotatedSection
                                    :title = item.name
                                >
                                    <PFormLayout>
                                        <PFormLayoutGroup>
                                            <PTextField
                                                v-model="form.languages[index].pivot.clinic_page"
                                                label="Clinic Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                            <PTextField
                                                v-model="form.languages[index].pivot.clinic_index_page"
                                                label="Clinic Index Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                        </PFormLayoutGroup>
                                    </PFormLayout>
                                </PLayoutAnnotatedSection>
                            </div>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        v-if="form.languages.length"
                        title="FAD City / Region Page Settings"
                        description="Add find a doctor page details."
                    >
                        <PCard sectioned>
                            <div v-for="(item, index) in form.languages" :key="index" v-if="form.languages[index].pivot">
                                <PLayoutAnnotatedSection
                                    :title = item.name
                                >
                                    <PFormLayout>
                                        <PFormLayoutGroup>
                                            <ValidationProvider name="Page Handle" rules="required" v-slot="{ errors }">
                                                <PTextField
                                                    v-model="form.languages[index].pivot.fad_page"
                                                    :error="errors[0]"
                                                    label="Page Handle*"
                                                    :multiline="false"
                                                >
                                                </PTextField>
                                            </ValidationProvider>
                                            <PTextField
                                                v-model="form.languages[index].pivot.fad_static_page"
                                                label="Static Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                        </PFormLayoutGroup>
                                        <PFormLayoutGroup>
                                            <PTextField
                                                v-model="form.languages[index].pivot.fad_region_page"
                                                label="Region Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                            <PTextField
                                                v-model="form.languages[index].pivot.fad_region_static_page"
                                                label="Region Static Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                        </PFormLayoutGroup>
                                    </PFormLayout>
                                </PLayoutAnnotatedSection>
                            </div>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        v-if="form.languages.length"
                        title="FAD Iframe Settings"
                        description="Add find a doctor iframe page details."
                    >
                        <PCard sectioned>
                            <div v-for="(item, index) in form.languages" :key="index" v-if="form.languages[index].pivot">
                                <PLayoutAnnotatedSection
                                    :title = item.name
                                >
                                    <PFormLayout>
                                        <PFormLayoutGroup>
                                            <ValidationProvider name="Page Handle" rules="required" v-slot="{ errors }">
                                                <PTextField
                                                    v-model="form.languages[index].pivot.fad_iframe_page"
                                                    :error="errors[0]"
                                                    label="Page Handle*"
                                                    :multiline="false"
                                                >
                                                </PTextField>
                                            </ValidationProvider>
                                        </PFormLayoutGroup>
                                    </PFormLayout>
                                </PLayoutAnnotatedSection>
                            </div>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        v-if="form.languages.length"
                        title="Pollen City / Region Page Settings"
                        description="Add pollen page details."
                    >
                        <PCard sectioned>
                            <div v-for="(item, index) in form.languages" :key="index" v-if="form.languages[index].pivot">
                                <PLayoutAnnotatedSection
                                    :title = item.name
                                >
                                    <PFormLayout>
                                        <PFormLayoutGroup>
                                            <PTextField
                                                v-model="form.languages[index].pivot.pollen_page"
                                                label="Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                            <PTextField
                                                v-model="form.languages[index].pivot.pollen_static_page"
                                                label="Static Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                        </PFormLayoutGroup>
                                        <PFormLayoutGroup>
                                            <PTextField
                                                v-model="form.languages[index].pivot.pollen_region_page"
                                                label="Region Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                            <PTextField
                                                v-model="form.languages[index].pivot.pollen_region_static_page"
                                                label="Region Static Page Handle"
                                                :multiline="false"
                                            >
                                            </PTextField>
                                        </PFormLayoutGroup>
                                    </PFormLayout>
                                </PLayoutAnnotatedSection>
                            </div>
                        </PCard>
                    </PLayoutAnnotatedSection>
                </PLayout>
            </ValidationObserver>

            <PHorizontalDivider style="margin-top: 20px; margin-bottom: 20px" />

            <PLayoutAnnotatedSection
                v-if="form.languages.length"
                title="Lexicon Page Settings"
                description="Add Lexicon page details."
            >
                <PCard sectioned>
                    <div v-for="(item, index) in form.languages" :key="index" v-if="form.languages[index].pivot">
                        <PLayoutAnnotatedSection
                            :title = item.name
                        >
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PTextField
                                        v-model="form.languages[index].pivot.lexicon_page"
                                        label="Lexicon Page Handle"
                                        :multiline="false"
                                    >
                                    </PTextField>
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PLayoutAnnotatedSection>
                    </div>
                </PCard>
            </PLayoutAnnotatedSection>

            <PHorizontalDivider style="margin-top: 20px; margin-bottom: 20px" />

            <ValidationObserver ref="pollen_calendar">
                <PLayout>
                    <PLayoutAnnotatedSection
                        v-if="form.languages.length"
                        title="Pollen Calendar"
                        description="Upload Pollen Calendar json file."
                    >
                        <PCard sectioned>
                            <div v-for="(item, index) in form.languages" :key="index" v-if="form.languages[index].pivot">
                                <PLayoutAnnotatedSection
                                    :title = item.name
                                    description="Upload json file for Syncing data."
                                >
                                    <PFormLayout>
                                        <PFormLayoutGroup>
                                            <ValidationProvider name="Type" rules="required" v-slot="{ errors }">
                                                <PMultiSelect
                                                    label="Type"
                                                    v-model="pollenCalendarType[item.code]"
                                                    :options="pollenCalendarOptions"
                                                    :error="errors[0] || formErrors.type"
                                                    :multiple="false"
                                                />
                                            </ValidationProvider>
                                            <PStack>
                                                <PStackItem fill>
                                                    <PTextField
                                                        label="File"
                                                        :multiple="false"
                                                        accept=".json"
                                                        type="file"
                                                        v-model="calendarFiles[item.code]"
                                                    />
                                                </PStackItem>
                                                <PStackItem v-if="calendarFiles[item.code]">
                                                    <PStack distribution="fillEvenly">
                                                        <PStackItem fill>
                                                            <PIcon source="UpdateInventoryMajor" @click="syncMetafields(item.code)" v-p-tooltip="'Sync Metafields'"/>
                                                        </PStackItem>
                                                        <PStackItem fill>
                                                            <PIcon source="MobileCancelMajor" @click="emptyFile(item.code)"/>
                                                        </PStackItem>
                                                    </PStack>
                                                </PStackItem>
                                            </PStack>
                                        </PFormLayoutGroup>
                                    </PFormLayout>
                                </PLayoutAnnotatedSection>
                            </div>
                        </PCard>
                    </PLayoutAnnotatedSection>
                </PLayout>
            </ValidationObserver>

            <PHorizontalDivider style="margin-top: 20px; margin-bottom: 20px" />

            <PLayout>
                <PLayoutAnnotatedSection
                    title="Cache Settings"
                    description="Cache settings for proxy pages"
                >
                    <PCard sectioned>
                        <PFormLayout>
                            <PFormLayoutGroup>
                                <PToggle
                                    id="enabled_proxy_cache"
                                    label="Enable Proxy page cache?"
                                    :checked="$root.$getBoolean(form.settings.enabled_proxy_cache)"
                                    :value="$root.$getBoolean(form.settings.enabled_proxy_cache)"
                                    @change="form.settings.enabled_proxy_cache = !$root.$getBoolean(form.settings.enabled_proxy_cache)"
                                />
                                <PButton
                                    destructive
                                    @click="clearProxyCache"
                                    :loading="loaders.cacheClearLoading"
                                >
                                    Clear Proxy Cache
                                </PButton>
                            </PFormLayoutGroup>
                        </PFormLayout>
                    </PCard>
                </PLayoutAnnotatedSection>
            </PLayout>
        </PPage>
    </div>
</template>

<script>
    export default {
        name: "Index",
        data() {
            return {
                form: {
                    country: null,
                    public_domain: null,
                    languages: [],
                    default_language: {},
                    settings: {
                        google_api_key: null,
                        enabled_proxy_cache: false
                    },
                },
                calendarFiles: {},
                tempForm: {},
                contextualSaveBar: {
                    open: false,
                    save: {
                        loading: false,
                        onAction: this.handleSaveSettings
                    },
                    discard: {
                        onAction: this.handleDiscardChanges
                    }
                },
                countries: [],
                languages: [],
                pollenCalendarOptions: [
                    {
                        label: 'Allergens',
                        value: 'allergens'
                    },
                    {
                        label: 'Types',
                        value: 'types'
                    },
                ],
                pollenCalendarType: {},
                formErrors: {
                    type: ''
                },
                loaders: {
                    cacheClearLoading: false
                }
            }
        },
        methods: {
            async getLanguages() {
                this.loading = true;
                try {
                    let {data} = await axios.get('/app/languages');
                    this.languages = data.languages;
                } catch (e) {
                    this.$pToast.open({
                        message: e.message || 'Something went wrong',
                        error: true
                    })
                }
            },
            async getSettings() {
                this.loading = true;
                try {
                    let {data} = await axios.get('/app/settings');
                    this.countries = data.countries || [];
                    let country = this.countries.find((country) => country.id === data.shop?.country_id);
                    this.form = {
                        country: country ? country : null,
                        public_domain: data.shop?.public_domain || '' ,
                        languages: data.shop?.languages,
                        default_language: data.shop?.default_language,
                        settings: Array.isArray(data.settings) ? {} : (data.settings || {})
                    }

                    this.manageTempForm();
                } catch (e) {
                    this.$pToast.open({
                        message: e.message || 'Something went wrong',
                        error: true
                    })
                }
                this.loading = false;
            },
            async handleDiscardChanges () {
                let isConfirmed = await this.$root.$confirm('Discard', `Are you sure want to discard all changes?`);
                if (!isConfirmed) {
                    return;
                }
                this.form = JSON.parse(JSON.stringify(this.tempForm));
                this.manageTempForm();
            },
            async handleSaveSettings() {
                let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
                if (!isConfirmed) {
                    return;
                }
                let isValidated = await this.$refs.settings.validate();
                if (!isValidated) {
                    return;
                }
                this.contextualSaveBar.save.loading = true;
                try {
                    let { data } = await axios.post('/app/settings', {...this.form, country_id: this.form.country?.id});
                    this.manageTempForm();
                    this.$pToast.open(data.message || 'Settings created successfully');
                } catch ({response}) {
                    this.$pToast.open({
                        error: true,
                        message: response.data.message || 'Something went wrong'
                    })
                }
                this.contextualSaveBar.save.loading = false;
            },
            manageTempForm() {
                this.tempForm = JSON.parse(JSON.stringify(this.form));
                this.contextualSaveBar.save.loading = false;
                this.contextualSaveBar.open = false;
            },
            handleLanguage(languages) {
                languages.forEach((language, index) => {
                    if (!language.pivot) {
                        this.form.languages[index].pivot = {}
                    }
                });

                if (!languages.find((language) => language.id === this.form.default_language?.id)) {
                    this.form.default_language = null;
                }
            },
            async syncMetafields(language) {

                let validated = await this.$refs.pollen_calendar.validate();
                if (!validated) {
                    return;
                }

                let formData = new FormData();
                formData.append(`calendarFile`, this.calendarFiles[language][0]);
                formData.append(`language`, language);
                formData.append(`type`, this.pollenCalendarType[language] ? this.pollenCalendarType[language].value : null);

                let config = {
                    headers: {
                        contentType: 'multipart/form-data'
                    }
                }
                try {
                    await axios.post('/app/pollen/calendar', formData, config);
                    this.manageTempForm();
                    this.$pToast.open('File Synced Successfully!');
                } catch ({response}) {
                    this.$pToast.open({
                        error: true,
                        message: response.data.message || 'Something went wrong'
                    })
                }
            },
            emptyFile(language) {
                this.calendarFiles[language] = null;
            },
            async clearProxyCache() {
                let isConfirmed = await this.$root.$confirm('Cache Clear', `Are you sure want to clear all proxy pages cache?`);
                if (!isConfirmed) {
                    return;
                }
                this.loaders.cacheClearLoading = true;
                try {
                    let { data } = await axios.post('/app/settings/cache/clear', {
                        type: 'proxy'
                    });
                    this.$pToast.open(data.message || 'Cache removed successfully');
                } catch ({response}) {
                    this.$pToast.open({
                        error: true,
                        message: response.data.message || 'Something went wrong'
                    })
                }
                this.loaders.cacheClearLoading = false;
            }
        },
        watch: {
            form: {
                handler(form) {
                    this.contextualSaveBar.open = JSON.stringify(form) !== JSON.stringify(this.tempForm);
                },
                deep: true
            },
        },
        async created() {
            this.$pLoading.start();

            await this.getLanguages();
            await this.getSettings();
            this.manageTempForm();

            this.$pLoading.finish();
        }
    }
</script>

<style scoped>

</style>
