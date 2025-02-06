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
                <ValidationObserver ref="quiz">
                    <template v-if="tabSelected === 0">
                        <PLayout>
                            <PLayoutAnnotatedSection
                                title="Theme"
                                description="Please select a theme."
                            >
                                <PCard sectioned>
                                    <PFormLayout>
                                        <PSelect
                                            v-model="form.theme"
                                            label="Theme"
                                            :options="themeOptions"
                                            value-field="value"
                                            text-field="label"
                                        />
                                    </PFormLayout>
                                </PCard>
                            </PLayoutAnnotatedSection>
                            <PLayoutAnnotatedSection
                                title="Quiz Details"
                                description="Please Enter Quiz Details."
                            >
                                <PCard sectioned>
                                    <PFormLayout>
                                        <PTextField
                                            v-model="form.label"
                                            label="Label"
                                        />
                                        <ValidationProvider name="Quiz Title" rules="required" v-slot="{ errors }">
                                            <PTextField
                                                v-model="form.title"
                                                label="Title*"
                                                :error="errors[0]"
                                            />
                                        </ValidationProvider>
                                        <PTextField
                                            v-model="form.cta_label"
                                            label="CTA Label"
                                        />
                                        <PStack>
                                            <PStackItem fill>
                                                <PDropZone
                                                    type="image"
                                                    label="Background Image"
                                                    :files="quizImage"
                                                    :handleOnDrop="handleOnDrop"
                                                />
                                            </PStackItem>
                                            <PStackItem style="margin-top: 40px">
                                                <PImage
                                                    v-if="form.image"
                                                    :source="form.image"
                                                    alt="quiz image"
                                                    :width="230"
                                                />
                                            </PStackItem>
                                        </PStack>
                                    </PFormLayout>
                                </PCard>
                            </PLayoutAnnotatedSection>
                        </PLayout>
                    </template>
                    <template v-else-if="tabSelected === 1">
                        <PLayout>
                            <PLayoutAnnotatedSection
                                title="Question Details"
                                description="Please Add question details."
                            >
                                <PAccordion id="question_accordion">
                                    <div v-for="(question, index) in form.questions" :key="index">
                                        <PStack>
                                            <PStackItem fill>
                                                <PAccordionItem
                                                    :themeOptions="accordion.themeOptions"
                                                >
                                                    <div slot="title">
                                                        {{ question.title ? question.title : `Question ${question.order}` }}
                                                    </div>
                                                    <div slot="content">
                                                        <PFormLayout>
                                                            <ValidationProvider name="Question Title" rules="required" v-slot="{ errors }">
                                                                <PTextField
                                                                    v-model="form.questions[index].title"
                                                                    label="Title*"
                                                                    :error="errors[0]"
                                                                />
                                                            </ValidationProvider>
                                                            <PTextField
                                                                v-model="form.questions[index].label"
                                                                label="Label"
                                                                type="text"
                                                            />
                                                            <PTextField
                                                                label="Question Image"
                                                                :id="`questionImage_${index}`"
                                                                :multiple="false"
                                                                type="file"
                                                                clearable
                                                                v-model="form.questions[index].image"
                                                            />
                                                            <br/>
                                                            <PImage
                                                                v-if="form.questions[index].image"
                                                                :source="typeof form.questions[index].image != 'string' ? createQuestionObjectUrl(index) : form.questions[index].image"
                                                                alt="Question Image"
                                                                style="max-width:250px"
                                                            />
                                                        </PFormLayout>
                                                        <PLayoutAnnotatedSection title="Options" style="margin-left: 0;">
                                                            <PAccordion id="question_option_accordion">
                                                                <div v-for="(option, optionIndex) in form.questions[index].options" :key="optionIndex">
                                                                    <PStack>
                                                                        <PStackItem fill style="width: 500px">
                                                                            <PAccordionItem>
                                                                                <div slot="title">
                                                                                    {{ option.title ? option.title : `Option ${option.order}` }}
                                                                                </div>
                                                                                <div slot="content" style="width: 100%">
                                                                                    <PFormLayout>
                                                                                        <PStack>
                                                                                            <PStackItem>
                                                                                                <PRadioButton
                                                                                                    :id="`optionTitle_${index}_${optionIndex}`"
                                                                                                    label="Option with Title"
                                                                                                    :name="`optionImage_${index}_${optionIndex}`"
                                                                                                    value="title"
                                                                                                    :checked="form.questions[index].options[optionIndex].type == 'title'"
                                                                                                    @change="optionTypeChanged(index, optionIndex, $event)"
                                                                                                />
                                                                                            </PStackItem>
                                                                                            <PStackItem>
                                                                                                <PRadioButton
                                                                                                    :id="`optionImage_${index}_${optionIndex}`"
                                                                                                    label="Option with Image"
                                                                                                    :name="`optionImage_${index}_${optionIndex}`"
                                                                                                    value="image"
                                                                                                    :checked="form.questions[index].options[optionIndex].type == 'image'"
                                                                                                    @change="optionTypeChanged(index, optionIndex, $event)"
                                                                                                />
                                                                                            </PStackItem>
                                                                                        </PStack>
                                                                                        <ValidationProvider rules="required" name="Options Title" v-slot="{ errors }" v-if="form.questions[index].options[optionIndex].type == 'title'">
                                                                                            <PTextField
                                                                                                v-model = "form.questions[index].options[optionIndex].title"
                                                                                                label="Title*"
                                                                                                type="text"
                                                                                                :error="errors[0]"
                                                                                            />
                                                                                        </ValidationProvider>
<!--                                                                                        <ValidationProvider rules="required" name="Options Image" v-slot="{ errors }" v-if="form.questions[index].options[optionIndex].type == 'image'">-->
                                                                                            <PTextField
                                                                                                label="Upload Image"
                                                                                                id="optionImage"
                                                                                                :multiple="false"
                                                                                                type="file"
                                                                                                clearable
                                                                                                v-model="form.questions[index].options[optionIndex].image"
                                                                                            />
                                                                                            <br/>
                                                                                            <PImage
                                                                                                v-if="form.questions[index].options[optionIndex].image"
                                                                                                :source="typeof form.questions[index].options[optionIndex].image != 'string' ? createObjectUrl(index, optionIndex) : form.questions[index].options[optionIndex].image"
                                                                                                alt="Option Image"
                                                                                                style="max-width:250px"
                                                                                            />
<!--                                                                                        </ValidationProvider>-->
                                                                                        <div>
                                                                                            <PLabel :id="`question_${index}_option_${optionIndex}_description`" style="margin-bottom: 5px;">Description</PLabel>
                                                                                            <ckeditor
                                                                                                v-model="form.questions[index].options[optionIndex].description"
                                                                                            />
                                                                                        </div>
                                                                                        <PCheckbox
                                                                                            v-model="form.questions[index].options[optionIndex].correct"
                                                                                            :value="form.questions[index].options[optionIndex].correct"
                                                                                            :checked="form.questions[index].options[optionIndex].correct"
                                                                                            label="Correct"
                                                                                            :id="`correct_${index}_${optionIndex}`"
                                                                                            @change="handleOptionCorrectChange(index, optionIndex, $event)"
                                                                                        />
                                                                                    </PFormLayout>
                                                                                </div>
                                                                            </PAccordionItem>
                                                                        </PStackItem>
                                                                        <PStackItem style="display: flex; align-items: center;">
                                                                            <PIcon @click="handleDeleteOption(index, optionIndex)" color="critical" source="DeleteMajor" />
                                                                        </PStackItem>
                                                                    </PStack>
                                                                </div>
                                                            </PAccordion>
                                                            <div style="display: flex; margin-top: 20px;">
                                                                <PButton
                                                                    style="margin-left: auto"
                                                                    @click="addOption(index)"
                                                                >
                                                                    Add
                                                                </PButton>
                                                            </div>
                                                        </PLayoutAnnotatedSection>
                                                    </div>
                                                </PAccordionItem>
                                            </PStackItem>
                                            <PStackItem style="display: flex; align-items: center;">
                                                <PIcon @click="handleDeleteQuestion(index)" color="critical" source="DeleteMajor" />
                                            </PStackItem>
                                        </PStack>
                                    </div>
                                </PAccordion>
                                <div style="display: flex; margin-top: 20px;">
                                    <PButton
                                        style="margin-left: auto"
                                        @click="addQuestion"
                                    >
                                        Add
                                    </PButton>
                                </div>
                            </PLayoutAnnotatedSection>
                        </PLayout>
                    </template>
                    <template v-else-if="tabSelected === 2">
                        <PLayout>
                            <PLayoutAnnotatedSection
                                title="Result Details"
                                description="Please Add Result details."
                            >
                                <PAccordion id="result_accordion">
                                    <div v-for="(result, resultIndex) in form.results" :key="resultIndex">
                                        <PStack>
                                            <PStackItem fill>
                                                <PAccordionItem
                                                    :themeOptions="accordion.themeOptions"
                                                >
                                                    <div slot="title">
                                                        {{ result.title ? result.title : `Result ${result.order}` }}
                                                    </div>
                                                    <div slot="content">
                                                        <PFormLayout>
                                                            <PStack>
                                                                <PStackItem>
                                                                    <PRadioButton
                                                                        :id="`resultTitle_${resultIndex}`"
                                                                        label="Result with Title"
                                                                        :name="`resultTitle_${resultIndex}`"
                                                                        value="title"
                                                                        :checked="form.results[resultIndex].type == 'title'"
                                                                        @change="resultTypeChanged(resultIndex, $event)"
                                                                    />
                                                                </PStackItem>
                                                                <PStackItem>
                                                                    <PRadioButton
                                                                        :id="`resultDescription_${resultIndex}`"
                                                                        label="Result with Description"
                                                                        :name="`resultDescription_${resultIndex}`"
                                                                        value="description"
                                                                        :checked="form.results[resultIndex].type == 'description'"
                                                                        @change="resultTypeChanged(resultIndex, $event)"
                                                                    />
                                                                </PStackItem>
                                                                <PStackItem>
                                                                    <PRadioButton
                                                                        :id="`resultImage_${resultIndex}`"
                                                                        label="Result with Image"
                                                                        :name="`resultImage_${resultIndex}`"
                                                                        value="image"
                                                                        :checked="form.results[resultIndex].type == 'image'"
                                                                        @change="resultTypeChanged(resultIndex, $event)"
                                                                    />
                                                                </PStackItem>
                                                                <PStackItem>
                                                                    <PRadioButton
                                                                        :id="`resultSmiley_${resultIndex}`"
                                                                        label="Result with Smiley"
                                                                        :name="`resultSmiley_${resultIndex}`"
                                                                        value="smiley"
                                                                        :checked="form.results[resultIndex].type == 'smiley'"
                                                                        @change="resultTypeChanged(resultIndex, $event)"
                                                                    />
                                                                </PStackItem>
                                                            </PStack>
                                                            <PTextField
                                                                label="Percentage*"
                                                                type="number"
                                                                v-model="form.results[resultIndex].percentage"
                                                            >
                                                                <div slot="suffix">%</div>
                                                            </PTextField>
                                                            <ValidationProvider name="Result Title" :rules="form.results[resultIndex].type === 'title' ? 'required' : ''" v-slot="{ errors }">
                                                                <PTextField
                                                                    :label="form.results[resultIndex].type === 'title' ? 'Title*' : 'Title'"
                                                                    v-model="form.results[resultIndex].title"
                                                                    :error="errors[0]"
                                                                />
                                                            </ValidationProvider>
                                                            <ValidationProvider name="Result Smiley" rules="required" v-slot="{ errors }" v-if="form.results[resultIndex].type === 'smiley'">
                                                                <PMultiSelect
                                                                    class="resultMultiSelect"
                                                                    label="Smiley Options*"
                                                                    :options="smileyOptions"
                                                                    textField="label"
                                                                    valueField="value"
                                                                    :value="smileyOptions.find(item => item.value === form.results[resultIndex].smiley)"
                                                                    placeholder="Select Smiley"
                                                                    :multiple="false"
                                                                    @change="selectStatus(resultIndex, $event)"
                                                                    :error="errors[0]"
                                                                />
                                                            </ValidationProvider>
                                                            <validation-provider rules="required" name="Result Image" v-slot="{ errors }" v-if="form.results[resultIndex].type == 'image'">
                                                                <PTextField
                                                                    label="Upload Image*"
                                                                    :id="`resultImage_${resultIndex}`"
                                                                    :multiple="false"
                                                                    type="file"
                                                                    clearable
                                                                    v-model="form.results[resultIndex].image"
                                                                    :error="errors[0]"
                                                                />
                                                                <br/>
                                                                <PImage
                                                                    v-if="form.results[resultIndex].image"
                                                                    :source="typeof form.results[resultIndex].image != 'string' ? createResultObjectUrl(resultIndex) : form.results[resultIndex].image"
                                                                    alt="Result Image"
                                                                    style="max-width:250px"
                                                                />
                                                            </validation-provider>
                                                            <div>
                                                                <validation-provider :rules="form.results[resultIndex].type === 'description' ? 'required' : ''" name="Result Description" v-slot="{ errors }" v-if="form.results[resultIndex].type === 'title' || form.results[resultIndex].type === 'description'">
                                                                    <PLabel :id="`result_${resultIndex}_description`" style="margin-bottom: 5px;">{{form.results[resultIndex].type === 'description' ? 'Description*' : 'Description'}}</PLabel>
                                                                    <ckeditor
                                                                        v-model="form.results[resultIndex].description"
                                                                    />
                                                                    <PFieldError :error="errors[0]" v-if="errors.length"/>
                                                                </validation-provider>
                                                            </div>
                                                            <PTextField
                                                                label="CTA Link"
                                                                v-model="form.results[resultIndex].cta_link"
                                                            />
                                                            <PTextField
                                                                label="CTA Label"
                                                                v-model="form.results[resultIndex].cta_label"
                                                            />
                                                            <PTextField
                                                                label="Text Link"
                                                                v-model="form.results[resultIndex].text_link"
                                                            />
                                                            <PTextField
                                                                label="Text Label"
                                                                v-model="form.results[resultIndex].text_label"
                                                            />
                                                        </PFormLayout>
                                                    </div>
                                                </PAccordionItem>
                                            </PStackItem>
                                            <PStackItem style="display: flex; align-items: center;">
                                                <PIcon @click="handleDeleteResult(resultIndex)" color="critical" source="DeleteMajor" />
                                            </PStackItem>
                                        </PStack>
                                    </div>
                                </PAccordion>
                                <div style="display: flex; margin-top: 20px;">
                                    <PButton
                                        style="margin-left: auto"
                                        @click="addResult"
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
import {mapGetters} from "vuex";
    export default {
        name: "Index",
        data() {
            return {
                tabs: [
                    {
                        id: 'intro',
                        content: 'Intro',
                    },
                    {
                        id: 'questions',
                        content: 'Questions',
                    },
                    {
                        id: 'results',
                        content: 'Results',
                    },
                ],
                tabSelected : 0,
                themeOptions: [
                    {
                        label: 'Green',
                        value: 'green'
                    },
                    {
                        label: 'Red',
                        value: 'red'
                    }
                ],
                form: {
                    id: null,
                    theme: 'green',
                    label: '',
                    title: '',
                    cta_label: '',
                    image: null,
                    questions: [],
                    results: []
                },
                tempForm: {},
                quizImage: [],
                contextualSaveBar: {
                    open: false,
                    save: {
                        loading: false,
                        onAction: this.handleSaveQuiz
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
                },
                smileyOptions: [
                    {
                        label: 'Very Positive',
                        value: 'very_positive'
                    },
                    {
                        label: 'Positive',
                        value: 'positive'
                    },
                    {
                        label: 'Neutral',
                        value: 'neutral'
                    },
                    {
                        label: 'Negative',
                        value: 'negative'
                    },
                    {
                        label: 'Very Negative',
                        value: 'very_negative'
                    },
                ]
            }
        },
        computed: {
            ...mapGetters('shop', {
                domain: 'getShop'
            })
        },
        methods: {
            changeTab(tab) {
                this.tabSelected = tab;
            },
            optionTypeChanged(questionIndex, optionIndex, value) {
                this.form.questions[questionIndex].options[optionIndex].type = value.value;
            },
            resultTypeChanged(resultIndex, value) {
                this.form.results[resultIndex].type = value.value;
            },
            handleOptionCorrectChange(index, optionIndex, value) {
                this.form.questions[index].options[optionIndex].correct = value && value.checked;
            },
            async handleDiscardChanges () {
                let isConfirmed = await this.$root.$confirm('Discard', `Are you sure want to discard all changes?`);
                if (!isConfirmed) {
                    return;
                }
                this.form = JSON.parse(JSON.stringify(this.tempForm));
                await this.setQuizImage();
                this.manageTempForm();
            },
            async handleSaveQuiz() {
                let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
                if (!isConfirmed) {
                    return;
                }
                let isQuizValidated = await this.$refs.quiz.validate();
                if (!isQuizValidated) {
                    return;
                }
                this.contextualSaveBar.save.loading = true;
                    try {
                        let config = {
                            headers: {
                                contentType: 'multipart/form-data'
                            }
                        }
                        let { data } = this.form.id ?
                            await axios.put(`/app/quizzes/${this.form.id}`, this.form) :
                            await axios.post('/app/quizzes', this.form);

                        let formData = new FormData();
                        if (data && data.quiz && data.quiz.id &&
                            (this.tempForm.image !== this.form.image) &&
                            (this.quizImage && this.quizImage.length && (this.quizImage[0] instanceof File))) {
                            formData.append('image', this.quizImage[0]);
                        }

                        this.form.questions.forEach((question) => {
                            let dataQuestion = data?.quiz?.questions?.find(item => item?.order === question?.order);
                            if (question?.image?.length && question?.image[0] instanceof File) {
                                formData.append(`questions[${dataQuestion?.id}][image]`, question?.image[0]);
                            }
                            question?.options.forEach((option) => {
                                let dataOption = dataQuestion?.options.find(optionItem => optionItem?.order === option?.order);
                                if (option?.image?.length && option?.image[0] instanceof File) {
                                    formData.append(`questions[${dataQuestion?.id}][options][${dataOption?.id}]`, option?.image[0]);
                                }
                            })
                        })

                        this.form.results.forEach((result) => {
                            let dataResult = data?.quiz?.results.find(item => item?.order === result?.order);
                            if (result?.image?.length && result?.image[0] instanceof File) {
                                formData.append(`results[${dataResult?.id}]`, result?.image[0]);
                            }
                        })

                        await axios.post(`/app/quizzes/${data.quiz.id}/upload`, formData, config);

                        this.manageTempForm();
                        await this.$router.push({name: 'quizzes'});
                        this.$pToast.open(data.message || 'Quiz created successfully');
                    } catch ({response}) {
                        this.$pToast.open({
                            error: true,
                            message: response.data.message || 'Something went wrong'
                        })
                    }
                this.contextualSaveBar.save.loading = false;
            },
            addQuestion() {
                let length = this.form.questions.length;
                this.form.questions.push({
                    order: length + 1,
                    title: '',
                    label: '',
                    options: []
                })
                this.addOption(length);
            },
            addOption(index) {
                this.form.questions[index].options.push({
                    order: this.form.questions[index].options.length + 1,
                    id: '',
                    title: '',
                    description: '',
                    correct: false,
                    image: '',
                    type: 'title'
                })
            },
            addResult() {
                this.form.results.push({
                    order: this.form.results.length + 1,
                    percentage: 0,
                    title: '',
                    description: '',
                    cta_label: '',
                    cta_link: '',
                    text_label: '',
                    text_link: '',
                    type: 'title'
                })
            },
            async handleDeleteQuestion(index) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this question?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                this.form.questions.splice(index, 1);
            },
            async handleDeleteOption(index, optionIndex) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this option?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                this.form.questions[index].options.splice(optionIndex, 1);
            },
            async handleDeleteResult(resultIndex) {
                let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this result?`, {
                    primaryAction: {
                        destructive: true,
                    },
                });
                if (!isConfirmed) {
                    return;
                }
                this.form.results.splice(resultIndex, 1);
            },
            manageTempForm() {
                this.tempForm = JSON.parse(JSON.stringify(this.form));
                this.contextualSaveBar.save.loading = false;
                this.contextualSaveBar.open = false;
            },
            async getQuiz(quizId) {
                try {
                    let {data} = await axios.get('/app/quizzes/'+quizId);
                    this.form = data.quiz || {};

                    // converting rich text value null to empty string
                    this.form.questions.forEach((question, index) => {
                        question.options.forEach((option, optionIndex) => {
                            if (!option.description) {
                                this.form.questions[index].options[optionIndex].description = '';
                            }
                        });
                    });

                    this.form.results.forEach((result, index) => {
                       if (!result.description) {
                            this.form.results[index].description = '';
                       }
                    });

                    await this.setQuizImage();
                } catch ({response}) {
                    this.$pToast.open({
                        error: true,
                        message: response.data.message || 'Something went wrong'
                    });
                }
            },
            handleOnDrop(files, acceptedFiles) {
                this.quizImage = acceptedFiles;
                this.form.image = URL.createObjectURL(this.quizImage[0]);
            },
            createQuestionObjectUrl(index) {
                return URL.createObjectURL(this.form.questions[index].image[0]);
            },
            createObjectUrl(index, optionIndex) {
                return URL.createObjectURL(this.form.questions[index].options[optionIndex].image[0]);
            },
            createResultObjectUrl(resultIndex) {
                return URL.createObjectURL(this.form.results[resultIndex].image[0]);
            },
            async setQuizImage() {
                try {
                    if (this.form.image) {
                        let response = await fetch(this.form.image);
                        let data = await response.blob();
                        let name = this.form.image.split('/');
                        name = name[name.length - 1];
                        let file = new File([data], name, {
                            type: 'image/jpeg'
                        });
                        this.quizImage = [file];
                    }
                } catch (e) {
                    this.$pToast.open({
                        message: e.message || 'Something went wrong',
                        error: true,
                    });
                }
            },
            async selectStatus(index, value) {
                this.form.results[index].smiley = value?.value ?? value;
            },
        },
        watch: {
            form: {
                handler(form) {
                    this.contextualSaveBar.open = JSON.stringify(form) !== JSON.stringify(this.tempForm);
                },
                deep: true
            },
            quizImage: {
                handler(image) {
                    if (!(image && image.length)) {
                        this.form.image = null;
                    }
                },
                deep: true
            }
        },
        async created() {
            if (this.$route.params.quizId) {
                await this.getQuiz(this.$route.params.quizId);
            }

            this.manageTempForm();
        }
    }
</script>

<style scoped>
.resultMultiSelect {
    width: 30%;
}
</style>
