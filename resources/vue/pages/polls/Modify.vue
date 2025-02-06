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
        <PPage full-width>
            <PVerticalTabs
                :tabs="tabs"
                :selected="tabSelected"
                @select="changeTab"
            >
                <ValidationObserver ref="poll">
                    <template v-if="tabSelected === 0">
                        <PLayout>
                            <PLayoutAnnotatedSection
                                title="Poll Details"
                                description="Please Enter Poll Details."
                            >
                                <PCard sectioned>
                                    <PFormLayout>
                                        <ValidationProvider name="Poll Title" rules="required" v-slot="{ errors }">
                                            <PTextField
                                                v-model="form.title"
                                                label="Title*"
                                                :error="errors[0]"
                                            />
                                        </ValidationProvider>
                                        <ValidationProvider name="Poll Label" rules="required" v-slot="{ errors }">
                                            <PTextField
                                                v-model="form.label"
                                                label="Label*"
                                            />
                                        </ValidationProvider>
                                        <ValidationProvider name="Poll Question" rules="required" v-slot="{ errors }">
                                            <PTextField
                                                v-model="form.question"
                                                label="Question*"
                                            />
                                        </ValidationProvider>
                                        <div>
                                            <PLabel :id="`poll_description`" style="margin-bottom: 5px;">Description</PLabel>
                                            <ckeditor
                                                v-model="form.description"
                                            />
                                        </div>
                                        <div>
                                            <PLabel :id="`poll_disclaimer`" style="margin-bottom: 5px;">Disclaimer</PLabel>
                                            <ckeditor
                                                v-model="form.disclaimer"
                                            />
                                        </div>
                                    </PFormLayout>
                                </PCard>
                            </PLayoutAnnotatedSection>
                        </PLayout>
                    </template>
                    <template v-else>
                        <PLayout>
                            <PLayoutAnnotatedSection
                                title="Answer Details"
                                description="Please Add Answer Details."
                            >
                                <PAccordion id="answer_accordion">
                                    <div v-for="(answer, index) in form.answers" :key="index">
                                        <PStack>
                                            <PStackItem fill>
                                                <PAccordionItem
                                                    :themeOptions="accordion.themeOptions"
                                                >
                                                    <div slot="title">
                                                        {{ answer.title ? answer.title : `Answer ${answer.order}` }}
                                                    </div>
                                                    <div slot="content">
                                                        <PFormLayout>
                                                            <ValidationProvider name="Answer Title" rules="required" v-slot="{ errors }">
                                                                <PTextField
                                                                    v-model="form.answers[index].title"
                                                                    label="Title*"
                                                                    :error="errors[0]"
                                                                />
                                                            </ValidationProvider>
                                                            <PTextField
                                                                v-model="form.answers[index].description"
                                                                label="Description"
                                                                type="text"
                                                            />
                                                            <PTextField
                                                                label="CTA Label"
                                                                v-model="form.answers[index].cta_label"
                                                            />
                                                            <PTextField
                                                                label="CTA Link"
                                                                v-model="form.answers[index].cta_link"
                                                            />
                                                        </PFormLayout>
                                                    </div>
                                                </PAccordionItem>
                                            </PStackItem>
                                            <PStackItem style="display: flex; align-items: center;">
                                                <PIcon @click="handleDeleteAnswer(index)" color="critical" source="DeleteMajor" />
                                            </PStackItem>
                                        </PStack>
                                    </div>
                                </PAccordion>
                                <div style="display: flex; margin-top: 20px;">
                                    <PButton
                                        style="margin-left: auto"
                                        @click="addAnswer"
                                    >
                                        Add
                                    </PButton>
                                </div>
                            </PLayoutAnnotatedSection>
                        </PLayout>
                    </template>
                </ValidationObserver>
            </PVerticalTabs>
        </PPage>
    </div>
</template>

<script>
    export default {
        name: "Modify",
        data() {
            return {
                tabs: [
                    {
                        id: 'intro',
                        content: 'Intro',
                    },
                    {
                        id: 'answers',
                        content: 'Answers',
                    }
                ],
                tabSelected : 0,
                form: {
                    id: null,
                    title: '',
                    label: '',
                    question: '',
                    description: '',
                    disclaimer: '',
                    answers: [],
                },
                tempForm: {},
                contextualSaveBar: {
                    open: false,
                    save: {
                        loading: false,
                        onAction: this.handleSavePoll
                    },
                    discard: {
                        onAction: this.handleDiscardChanges
                    }
                },
                accordion: {
                    themeOptions: {
                        header: {
                            color: '#0e1111',
                            background: '#f1f8f5',
                            backgroundCollapsed: '#d2e7d6'
                        },
                        content: {
                            color: '#0e1111', background: '#e8f4ea'
                        }
                    }
                }
            }
        },
        methods: {
            changeTab(tab) {
                this.tabSelected = tab;
            },
            async handleDiscardChanges () {
                let isConfirmed = await this.$root.$confirm('Discard', `Are you sure want to discard all changes?`);
                if (!isConfirmed) {
                    return;
                }
                this.form = JSON.parse(JSON.stringify(this.tempForm));
                this.manageTempForm();
            },
            async handleSavePoll() {
                let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
                if (!isConfirmed) {
                    return;
                }
                let isValidated = await this.$refs.poll.validate();
                if (!isValidated) {
                    return;
                }
                this.contextualSaveBar.save.loading = true;
                try {
                    let { data } = this.form.id ?
                        await axios.put(`/app/polls/${this.form.id}`, this.form) :
                        await axios.post('/app/polls', this.form);

                    this.manageTempForm();
                    await this.$router.push({name: 'polls'});
                    this.$pToast.open(data.message || 'Poll created successfully');
                } catch ({response}) {
                    this.$pToast.open({
                        error: true,
                        message: response.data.message || 'Something went wrong'
                    })
                }
                this.contextualSaveBar.save.loading = false;
            },
            addAnswer() {
                let length = this.form.answers.length;
                this.form.answers.push({
                    order: length + 1,
                    title: '',
                    description: '',
                    cta_label: '',
                    cta_link: '',
                })
            },
            async handleDeleteAnswer(index) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this question?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                this.form.answers.splice(index, 1);
            },
            manageTempForm() {
                this.tempForm = JSON.parse(JSON.stringify(this.form));
                this.contextualSaveBar.save.loading = false;
                this.contextualSaveBar.open = false;
            },
            async getPoll(pollId) {
                try {
                    let {data} = await axios.get(`/app/polls/${pollId}`);
                    this.form = data.poll || {};

                    if (!this.form.description) {
                        this.form.description = '';
                    }
                    if (!this.form.disclaimer) {
                        this.form.disclaimer = '';
                    }
                } catch ({response}) {
                    this.$pToast.open({
                        error: true,
                        message: response.data.message || 'Something went wrong'
                    });
                }
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
            if (this.$route.params.pollId) {
                await this.getPoll(this.$route.params.pollId);
            }

            this.manageTempForm();
        }
    }
</script>

<style scoped>

</style>
