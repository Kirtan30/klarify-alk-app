<template>
    <div>
        <PContextualSaveBar
            v-if="contextualSaveBar.open"
            :open-modal="contextualSaveBar.open"
            message="Unsaved Changes"
            :saveAction="contextualSaveBar.save"
            :discardAction="contextualSaveBar.discard"
            class="contextual-save-bar"
        />
        <PSkeletonPage v-if="pageLoading" full-width title="Clinics">
            <PCard sectioned>
                <PLayoutSection>
                    <PSkeletonBodyText :lines="6"/>
                </PLayoutSection>
            </PCard>
        </PSkeletonPage>
        <PPage
            v-else
            full-width
            title="Create Doctor / Clinic"
            :breadcrumbs="breadcrumbs"
        >
            <template slot="primaryAction">
                <PButton
                    primary
                    @click="contextualSaveBar.save.onAction"
                    :loading="contextualSaveBar.save.loading"
                >
                    Save
                </PButton>
            </template>

            <PHorizontalDivider style="margin-bottom: 20px;" />
            <ValidationObserver ref="clinics">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="Entity Type"
                        description="Choose the type of the entity doctor / clinic"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PToggle
                                    id="is_doctor"
                                    label="Is Doctor?"
                                    :checked="form.is_doctor"
                                    :value="form.is_doctor"
                                    @change="form.is_doctor = !form.is_doctor"
                                />
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Doctor / Clinic Details"
                        description="Add the details of doctor / clinics carefully"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PTextField
                                    id="title"
                                    label="Title"
                                    v-model="form.title"
                                />
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="first_name"
                                        label="First name"
                                        v-model="form.first_name"
                                    />
                                    <PTextField
                                        id="last_name"
                                        label="Last name"
                                        v-model="form.last_name"
                                    />
                                </PFormLayoutGroup>
                                <PTextField
                                    id="email"
                                    label="Email"
                                    type="email"
                                    v-model="form.email"
                                />
                                <PTextField
                                    id="phone_number"
                                    label="Phone Number"
                                    v-model="form.phone"
                                />
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="clinic_name"
                                        label="Clinic Name"
                                        v-model="form.clinic_name"
                                        @input="form.clinic_handle = slugify(form.clinic_name)"
                                    />
                                    <PTextField
                                        id="clinic_handle"
                                        label="Clinic handle"
                                        v-model="form.clinic_handle"
                                        @input="form.clinic_handle = slugify(form.clinic_handle)"
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="doctor_name"
                                        label="Doctor Name"
                                        v-model="form.doctor_name"
                                        @input="form.doctor_handle = slugify(form.doctor_name)"
                                    />
                                    <PTextField
                                        id="doctor_handle"
                                        label="Doctor handle"
                                        v-model="form.doctor_handle"
                                        @input="form.doctor_handle = slugify(form.doctor_handle)"
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Address"
                        description="Add the Address Details of Doctor / clinics carefully"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="street"
                                        label="Street"
                                        v-model="form.street"
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="zipcode"
                                        label="Zip Code"
                                        v-model="form.zipcode"
                                    />
                                    <ValidationProvider name="City" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            id="city"
                                            label="City"
                                            v-model="form.city"
                                            :error="errors[0] || formErrors.city"
                                        />
                                    </ValidationProvider>
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="state"
                                        label="State"
                                        v-model="form.state"
                                    />
                                    <PTextField
                                        id="region"
                                        label="Region"
                                        v-model="form.region"
                                    />
                                    <ValidationProvider name="Country" rules="required" v-slot="{ errors }">
                                        <PMultiSelect
                                            id="country"
                                            label="Country"
                                            v-model="form.country"
                                            :options="countries"
                                            text-field="name"
                                            value-field="id"
                                            disabled
                                            :multiple="false"
                                            :error="errors[0] || formErrors.country"
                                        />
                                    </ValidationProvider>
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
<!--                                    <ValidationProvider name="Latitude" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            id="latitude"
                                            label="Latitude"
                                            v-model="form.latitude"
                                            :error="errors[0] || formErrors.latitude"
                                        />
                                    </ValidationProvider>
                                    <ValidationProvider name="Longitude" rules="required" v-slot="{ errors }">
                                        <PTextField
                                            id="longitude"
                                            label="Longitude"
                                            v-model="form.longitude"
                                            :error="errors[0] || formErrors.longitude"
                                        />
                                    </ValidationProvider>-->
                                    <PTextField
                                        id="latitude"
                                        label="Latitude"
                                        v-model="form.latitude"
                                        :error="formErrors.latitude"
                                    />
                                    <PTextField
                                        id="longitude"
                                        label="Longitude"
                                        v-model="form.longitude"
                                        :error="formErrors.longitude"
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Online Urls"
                        description="Add website / online appointment url for clinic"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PTextField
                                    id="website"
                                    label="Website"
                                    v-model="form.website"
                                />
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="online_appointment_url"
                                        label="Online Appointment Url"
                                        v-model="form.online_appointment_url"
                                    />
                                    <PTextField
                                        id="telehealth"
                                        label="Telehealth Url"
                                        v-model="form.telehealth"
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Descriptive info"
                        description="Other descriptive information of clinic / doctor"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="waiting_time"
                                        label="Waiting time"
                                        v-model="form.waiting_time"
                                    />
                                    <PTextField
                                        id="description"
                                        label="Description"
                                        v-model="form.description"
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PTextField
                                        id="other"
                                        label="Other"
                                        v-model="form.other"
                                        multiline
                                        :minHeight="50"
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Other Entity Types"
                        description="Choose the types of entities for doctor / clinic"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PToggle
                                        id="is_allergy_specialist"
                                        label="Is Allergy Specialist?"
                                        :checked="form.is_allergy_specialist"
                                        :value="form.is_allergy_specialist"
                                        @change="form.is_allergy_specialist = !form.is_allergy_specialist"
                                    />
                                    <PToggle
                                        id="is_venom_immunotherapy"
                                        label="Is Venom Immunotherapy?"
                                        :checked="form.is_venom_immunotherapy"
                                        :value="form.is_venom_immunotherapy"
                                        @change="form.is_venom_immunotherapy = !form.is_venom_immunotherapy"
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PToggle
                                        id="is_allergy_specialist"
                                        label="Is Allergy Diagnostic?"
                                        :checked="form.is_allergy_diagnostic"
                                        :value="form.is_allergy_diagnostic"
                                        @change="form.is_allergy_diagnostic = !form.is_allergy_diagnostic"
                                    />
                                    <PToggle
                                        id="is_insect_allergy_diagnostic"
                                        label="Is Insect Allergy Diagnostic?"
                                        :checked="form.is_insect_allergy_diagnostic"
                                        :value="form.is_insect_allergy_diagnostic"
                                        @change="form.is_insect_allergy_diagnostic = !form.is_insect_allergy_diagnostic"
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PToggle
                                        id="is_subcutaneous_immunotherapy"
                                        label="Is Subcutaneous Immunotherapy?"
                                        :checked="form.is_subcutaneous_immunotherapy"
                                        :value="form.is_subcutaneous_immunotherapy"
                                        @change="form.is_subcutaneous_immunotherapy = !form.is_subcutaneous_immunotherapy"
                                    />
                                    <PToggle
                                        id="is_sublingual_immunotherapy"
                                        label="Is Sublingual Immunotherapy?"
                                        :checked="form.is_sublingual_immunotherapy"
                                        :value="form.is_sublingual_immunotherapy"
                                        @change="form.is_sublingual_immunotherapy = !form.is_sublingual_immunotherapy"
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PToggle
                                        id="only_private_patients"
                                        label="Only Private Patients?"
                                        :checked="form.only_private_patients"
                                        :value="form.only_private_patients"
                                        @change="form.only_private_patients = !form.only_private_patients"
                                    />
                                    <PToggle
                                        id="reference_required"
                                        label="Reference Required?"
                                        :checked="form.reference_required"
                                        :value="form.reference_required"
                                        @change="form.reference_required = !form.reference_required"
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Services & Speciality"
                        description="Choose the services and speciality of doctor / clinic"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PFormLayoutGroup>
                                    <PMultiSelect
                                        id="specialist_areas"
                                        v-model="form.specialist_areas"
                                        label="Specialist areas"
                                        :options="specialist_areas"
                                        multiple
                                        taggable
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PMultiSelect
                                        id="other_services"
                                        v-model="form.other_services"
                                        label="Other services"
                                        :options="other_services"
                                        multiple
                                        taggable
                                    />
                                    <PMultiSelect
                                        id="diagnostic_services"
                                        v-model="form.diagnostic_services"
                                        label="Diagnostic services"
                                        :options="diagnostic_services"
                                        multiple
                                        taggable
                                    />
                                </PFormLayoutGroup>
                                <PFormLayoutGroup>
                                    <PMultiSelect
                                        id="clinic_types"
                                        v-model="form.clinic_types"
                                        label="Types"
                                        :options="clinic_types"
                                        multiple
                                        taggable
                                    />
                                    <PMultiSelect
                                        id="insurance_companies"
                                        v-model="form.insurance_companies"
                                        label="Insurance companies"
                                        :options="insurance_companies"
                                        multiple
                                        taggable
                                    />
                                </PFormLayoutGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Timing"
                    >
                        <div slot="description">
                            <div style="margin-bottom: 10px">
                                Fill timing details of doctor / clinic
                            </div>
                            <PBanner
                                status="info"
                            >
                                Kindly use 24 hours format to fill the timing details
                            </PBanner>
                        </div>
                        <PCard sectioned>
                            <PFormLayout v-if="form.timings && form.timings.length">
                                <template v-for="(timing, timingIndex) in form.timings">
                                    <PStack>
                                        <PStackItem style="width: 20%">
                                            <span style="margin-top: 3px">{{ timing.name }}</span>
                                        </PStackItem>
                                        <PStackItem
                                            fill
                                            v-if="form.timings.hasOwnProperty(timingIndex)"
                                        >
                                            <template v-for="(opening_hour, openingHourIndex) in timing.opening_hours">
                                                <div
                                                    v-if="form.timings[timingIndex].opening_hours.hasOwnProperty(openingHourIndex)"
                                                    style="display: flex; justify-content: space-between; margin-bottom: 10px;"
                                                >
                                                    <PTextField
                                                        type="time"
                                                        :id="`day_${timingIndex}_opening_hours_${openingHourIndex}_opening_time`"
                                                        label="Opening hours"
                                                        v-model="form.timings[timingIndex].opening_hours[openingHourIndex].opening_time"
                                                    />
                                                    <PTextField
                                                        type="time"
                                                        :id="`day_${timingIndex}_opening_hours_${openingHourIndex}_closing_time`"
                                                        label="Closing hours"
                                                        v-model="form.timings[timingIndex].opening_hours[openingHourIndex].closing_time"
                                                    />
                                                    <PToggle
                                                        style="display: flex; align-items: center; margin-top: 5px;"
                                                        :id="`day_${timingIndex}_opening_hours_${openingHourIndex}_optional`"
                                                        label="Optional?"
                                                        :checked="form.timings[timingIndex].opening_hours[openingHourIndex].optional"
                                                        :value="form.timings[timingIndex].opening_hours[openingHourIndex].optional"
                                                        @change="form.timings[timingIndex].opening_hours[openingHourIndex].optional = !form.timings[timingIndex].opening_hours[openingHourIndex].optional"
                                                    />
                                                    <PIcon
                                                        source="DeleteMinor"
                                                        color="critical"
                                                        @click="removeOpeningHour(timingIndex, openingHourIndex)"
                                                        style="margin: 12px 10px 0 10px; align-self: center; cursor: pointer;"
                                                    />
                                                </div>
                                            </template>
                                        </PStackItem>
                                        <PStackItem style="display: flex; align-items: center;">
                                            <PIcon
                                                source="PlusMinor"
                                                color="success"
                                                @click="addOpeningHour(timingIndex)"
                                                style="cursor: pointer;"
                                            />
                                        </PStackItem>
                                    </PStack>
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
import axios from "axios";
import {mapGetters} from "vuex";

export default {
    name: "Modify",
    computed: {
      ...mapGetters('shop', {
          shop: 'getShop'
      })
    },
    data() {
        return {
            pageLoading: false,
            breadcrumbs: [{
                content: 'Doctors',
                to: {
                    name: 'doctors'
                },
                accessibilityLabel: 'doctors'
            }],
            contextualSaveBar: {
                open: false,
                save: {
                    loading: false,
                    onAction: this.saveClinic
                },
                discard: {
                    onAction: this.handleDiscardChanges
                }
            },
            form: {
                id: null,
                is_doctor: false,
                title: '',
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                clinic_name: '',
                clinic_handle: '',
                doctor_name: '',
                doctor_handle: '',
                street : '',
                zipcode : '',
                city : '',
                region : '',
                state : '',
                country : null,
                latitude: '',
                longitude: '',
                website : '',
                waiting_time: '',
                description: '',
                other: '',
                online_appointment_url: '',
                telehealth: '',
                only_private_patients: false,
                reference_required: false,
                is_allergy_specialist: false,
                is_allergy_diagnostic: false,
                is_subcutaneous_immunotherapy: false,
                is_sublingual_immunotherapy: false,
                is_venom_immunotherapy: false,
                is_insect_allergy_diagnostic: false,
                manual_inserted: false,
                specialist_areas: [],
                insurance_companies: [],
                other_services: [],
                diagnostic_services: [],
                clinic_types: [],
                timings: []
            },
            specialist_areas: [],
            insurance_companies: [],
            other_services: [],
            diagnostic_services: [],
            clinic_types: [],
            week_days: [],
            countries: [],
            tempForm: {},
            relations: [
                'specialist_areas',
                'insurance_companies',
                'other_services',
                'diagnostic_services',
                'clinic_types',
            ],
            formErrors: {
                latitude: '',
                longitude: '',
                city: '',
                country: '',
            }
        }
    },
    methods: {
        async fetchClinic() {
            if (!this.form.id) {
                return;
            }
            this.pageLoading = true;
            try {
                let { data } = await axios.get(`/app/clinics/${this.form.id}`);
                let clinic = data.clinic || {};

                this.relations.forEach((relation) => {
                    (clinic[relation] || []).forEach((item) => {
                        this.form[relation].push({
                            label: item,
                            value: item
                        });
                    })
                });

                (this.form.timings || []).forEach((timing, timingIndex) => {
                    let clinicTiming = (clinic.timings || []).find((clinicTimingItem) => clinicTimingItem.index === timingIndex);
                    if (clinicTiming) {
                        this.form.timings[timingIndex] = clinicTiming;
                    }
                });

                Object.keys(this.form).forEach((key) => {
                    if (!this.relations.includes(key) && clinic.hasOwnProperty(key) && !['country', 'timings'].includes(key)) {
                        this.form[key] = clinic[key];
                    }
                });
                this.manageTempForm();
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response.data.message || 'Something went wrong'
                })
            }
            this.pageLoading = false;
        },
        async saveClinic() {
            let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
            if (!isConfirmed) {
                return;
            }

            let validated = await this.$refs.clinics.validate();
            if (!validated) {
                return;
            }

            this.formErrors = {};

            this.contextualSaveBar.save.loading = true;

            try {
                let data = await axios.post('/app/clinics', this.form);
                this.manageTempForm();
                this.$pToast.open(data?.message || 'Clinic saved successfully');
                await this.$router.push({name: 'doctors'});
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response.data.message || 'Something went wrong'
                })

                if (response?.data?.errors) {
                    for (const [key, value] of Object.entries(response.data.errors)) {
                        this.formErrors[key] = value[0];
                    }
                }
            }
            this.contextualSaveBar.save.loading = false;
        },
        async handleDiscardChanges() {
            let isConfirmed = await this.$root.$confirm('Discard', `Are you sure want to discard all changes?`);
            if (!isConfirmed) {
                return;
            }
            this.form = JSON.parse(JSON.stringify(this.tempForm));
            this.manageTempForm();
        },
        addOpeningHour(timingIndex) {
            this.form.timings[timingIndex].opening_hours.push({});
        },
        removeOpeningHour(timingIndex, openingHourIndex) {
            this.form.timings[timingIndex].opening_hours.splice(openingHourIndex);
        },
        manageTempForm() {
            this.tempForm = JSON.parse(JSON.stringify(this.form));
            this.contextualSaveBar.save.loading = false;
            this.contextualSaveBar.open = false;
        },
        slugify(text, ampersand = 'and') {
            const a = 'àáäâèéëêìíïîòóöôùúüûñçßÿỳýœæŕśńṕẃǵǹḿǘẍźḧ'
            const b = 'aaaaeeeeiiiioooouuuuncsyyyoarsnpwgnmuxzh'
            const p = new RegExp(a.split('').join('|'), 'g')

            return text.toString().toLowerCase()
                .replace(/[\s_]+/g, '-')
                .replace(p, c =>
                    b.charAt(a.indexOf(c)))
                .replace(/&/g, `-${ampersand}-`)
                .replace(/[^\w-]+/g, '')
                .replace(/--+/g, '-')
                .replace(/^-+|-+$/g, '')
        },
        async getEntities() {
            try {
                let { data } = await axios.get('/app/clinics/entities');
                this.specialist_areas = data.specialist_areas || [];
                this.other_services = data.other_services || [];
                this.insurance_companies = data.insurance_companies || [];
                this.diagnostic_services = data.diagnostic_services || [];
                this.clinic_types = data.clinic_types || [];
                this.week_days = data.week_days || [];
                await this.formatTimings();
            } catch ({response}) {
                this.$pToast.open({
                    message: response?.data?.message || 'Something went wrong',
                    error: true
                })
            }
        },
        async formatTimings() {
            this.week_days.forEach(day => {
                this.form.timings.push({...day, opening_hours: [{}]})
            });
        }
    },
    watch: {
        form: {
            handler(form) {
                this.contextualSaveBar.open = JSON.stringify(form) !== JSON.stringify(this.tempForm);
            },
            deep: true
        },
        shop: {
            handler() {
                this.form.country = this.shop?.country;
            }
        }
    },
    async created() {
        this.form.country = this.shop?.country;
        this.form.id = this.$route.params?.doctorId || null;
        await this.getEntities();
        await this.fetchClinic();
        this.manageTempForm();
    }
}
</script>

<style scoped>

</style>
