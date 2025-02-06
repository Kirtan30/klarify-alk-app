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
        <PPage
            full-width
            title="Add Allergy Test"
            :breadcrumbs="breadcrumbs"
        >
            <PHorizontalDivider style="margin-bottom: 20px;" />
            <ValidationObserver ref="allergyTest">
                <PLayout>
                    <PLayoutAnnotatedSection
                        title="Allergy Test Type"
                        description="Choose the type of the Allergy Test"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <ValidationProvider name="Allergy Test Type" rules="required" v-slot="{ errors }">
                                    <PMultiSelect
                                        label="Allergy Test Type"
                                        :options='testOptions'
                                        textField="label"
                                        valueField="value"
                                        v-model="form.type"
                                        :multiple="false"
                                        placeholder="Select Test Type"
                                        :error="errors[0]"
                                    />
                                </ValidationProvider>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Allergy Test Details"
                        description="Add Allergy Test Details"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PTextField
                                    label="Label"
                                    v-model="form.label"
                                    id="label"
                                />
                                <PTextField
                                    label="Title"
                                    v-model="form.title"
                                    id="title"
                                />
                                <div>
                                    <PLabel id="description" style="margin-bottom: 5px;">Description</PLabel>
                                    <ckeditor
                                        v-model="form.description"
                                    />
                                </div>
                                <PTextField
                                    label="Cta Label"
                                    v-model="form.cta_label"
                                    id="cta_label"
                                />

                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Select Question Type"
                        description="Choose the type of Question to add in Allergy Test"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PButtonGroup spacing="loose" full-width>
                                    <PButton @click="addQuestionWithOption">Add Quesiton With Options</PButton>
                                    <PButton @click="addQuestionWithSubQuestion">Add Quesiton With Sub-Questions</PButton>
                                </PButtonGroup>
                                <PButtonGroup spacing="loose" full-width>
                                    <PButton @click="addQuestionWithRange">Add Quesiton With Range</PButton>
                                    <PButton @click="addQuestionWithRelatedOption">Add Quesiton Related to Option</PButton>
                                </PButtonGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Allergy Test Questions Details"
                        description="Add Allergy Test Questions Details"
                        v-if="form.questions.length > 0"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PAccordion id="allergy_test_question_accordion">
<!--                                    <draggable-->
<!--                                        class="drag-area list-group"-->
<!--                                        v-bind="dragOptions"-->
<!--                                        :list="this.form.questions"-->
<!--                                    >-->
                                        <div v-for="(question, index) in form.questions" :key="index">
                                            <PStack>
                                                <PStackItem fill>
                                                    <PAccordionItem
                                                        :themeOptions="accordion.themeOptions"
                                                    >
                                                        <div slot="title">
                                                            {{ question.title ? question.title : `Question ${index + 1}` }}
                                                        </div>
                                                        <div slot="content" style="width: 100%">
                                                            <PFormLayout>
                                                                <ValidationProvider name="Question Order" rules="required|numeric" v-slot="{ errors }">
                                                                    <PTextField
                                                                        label="Question Order"
                                                                        v-model="form.questions[index].order"
                                                                        type="number"
                                                                        :error="errors[0]"
                                                                        :id="`question_${index}_order`"
                                                                    />
                                                                </ValidationProvider>
                                                                <PTextField
                                                                    label="Question Title"
                                                                    v-model="form.questions[index].title"
                                                                    :id="`question_${index}_title`"
                                                                />
                                                                <PTextField
                                                                    label="Question Description"
                                                                    v-model="form.questions[index].description"
                                                                    :id="`question_${index}_description`"
                                                                />
                                                                <PFormLayoutGroup v-if="question.type === 'questionWithRange'">
                                                                    <ValidationProvider name="Start Range" rules="required" v-slot="{ errors }">
                                                                        <PTextField
                                                                            label="Start Range"
                                                                            v-model="form.questions[index].question_data.range.startingRangePoint"
                                                                            :error="errors[0]"
                                                                            :id="`question_${index}_start_range`"
                                                                        />
                                                                    </ValidationProvider>
                                                                    <ValidationProvider name="End Range" rules="required" v-slot="{ errors }">
                                                                        <PTextField
                                                                            label="End Range"
                                                                            v-model="form.questions[index].question_data.range.endingRangePoint"
                                                                            :error="errors[0]"
                                                                            :id="`question_${index}_end_range`"
                                                                        />
                                                                    </ValidationProvider>
                                                                </PFormLayoutGroup>
                                                                <ValidationProvider name="Number Of Range Divisions" rules="required|numeric" v-slot="{ errors }" v-if="question.type === 'questionWithRange'">
                                                                    <PTextField
                                                                        label="Number Of Range Divisions"
                                                                        v-model="form.questions[index].question_data.range.rangeDivisions"
                                                                        type="number"
                                                                        :error="errors[0]"
                                                                        :id="`question_${index}_number_of_range_divisions`"
                                                                    />
                                                                </ValidationProvider>
                                                            </PFormLayout>
                                                            <PLayoutAnnotatedSection title="Sub Questions" style="margin-left: 0;" v-if="question.type === 'questionWithSubQuestion'">
                                                                <PAccordion id="allergy_test_sub_question_accordion">
                                                                    <div v-for="(subQuestion, subQuestionIndex) in form.questions[index].sub_questions" :key="subQuestionIndex">
                                                                        <PStack>
                                                                            <PStackItem fill>
                                                                                <PAccordionItem>
                                                                                    <div slot="title">
                                                                                        {{ subQuestion.title ? subQuestion.title : `Sub Question ${subQuestionIndex + 1}` }}
                                                                                    </div>
                                                                                    <div slot="content" style="width: 100%">
                                                                                        <PFormLayout>
                                                                                            <ValidationProvider name="Sub Question Order" rules="required|numeric" v-slot="{ errors }">
                                                                                                <PTextField
                                                                                                    label="Sub Question Order"
                                                                                                    v-model="form.questions[index].sub_questions[subQuestionIndex].order"
                                                                                                    type="number"
                                                                                                    :error="errors[0]"
                                                                                                    :id="`question_${index}_sub_question_${subQuestionIndex}_order`"
                                                                                                />
                                                                                            </ValidationProvider>
                                                                                            <PTextField
                                                                                                label="Sub Question Title"
                                                                                                v-model="form.questions[index].sub_questions[subQuestionIndex].title"
                                                                                                :id="`question_${index}_sub_question_${subQuestionIndex}_title`"
                                                                                            />
                                                                                        </PFormLayout>
                                                                                    </div>
                                                                                </PAccordionItem>
                                                                            </PStackItem>
                                                                            <PStackItem style="display: flex; align-items: center;">
                                                                                <PIcon @click="handleDeleteSubQuestion(index, subQuestionIndex)" color="critical" source="DeleteMajor" />
                                                                            </PStackItem>
                                                                        </PStack>
                                                                    </div>
                                                                </PAccordion>
                                                                <div style="display: flex; margin-top: 20px;">
                                                                    <PButton
                                                                        style="margin-left: auto"
                                                                        @click="addSubQuestion(index)"
                                                                    >
                                                                        Add
                                                                    </PButton>
                                                                </div>
                                                            </PLayoutAnnotatedSection>
                                                            <PLayoutAnnotatedSection title="options" style="margin-left: 0;" v-if="question.type !== 'questionWithRange'">
                                                                <PAccordion id="allergy_test_question_option_accordion">
                                                                    <div v-for="(option, optionIndex) in form.questions[index].options" :key="optionIndex">
                                                                        <PStack>
                                                                            <PStackItem fill>
                                                                                <PAccordionItem>
                                                                                    <div slot="title">
                                                                                        {{ option.title ? option.title : `Option ${optionIndex + 1}` }}
                                                                                    </div>
                                                                                    <div slot="content" style="width: 100%">
                                                                                        <PFormLayout>
                                                                                            <PFormLayoutGroup>
                                                                                                <ValidationProvider name="Option Order" rules="required|numeric" v-slot="{ errors }">
                                                                                                    <PTextField
                                                                                                        label="Option Order"
                                                                                                        v-model="form.questions[index].options[optionIndex].order"
                                                                                                        type="number"
                                                                                                        :error="errors[0]"
                                                                                                        :id="`question_${index}_option_${optionIndex}_order`"
                                                                                                    />
                                                                                                </ValidationProvider>
<!--                                                                                                <ValidationProvider name="Option Weightage" rules="required|numeric" v-slot="{ errors }">-->
<!--                                                                                                    <PTextField-->
<!--                                                                                                        label="Option Weightage %"-->
<!--                                                                                                        v-model="form.questions[index].options[optionIndex].weightage"-->
<!--                                                                                                        type="number"-->
<!--                                                                                                        :error="errors[0]"-->
<!--                                                                                                        :id="`question_${index}_option_${optionIndex}_weightage`"-->
<!--                                                                                                    />-->
<!--                                                                                                </ValidationProvider>-->
                                                                                                <ValidationProvider name="Option Value" rules="required|numeric" v-slot="{ errors }">
                                                                                                    <PTextField
                                                                                                        label="Option Value"
                                                                                                        v-model="form.questions[index].options[optionIndex].value"
                                                                                                        type="number"
                                                                                                        :error="errors[0]"
                                                                                                        :id="`question_${index}_option_${optionIndex}_value`"
                                                                                                    />
                                                                                                </ValidationProvider>
                                                                                            </PFormLayoutGroup>
                                                                                            <PTextField
                                                                                                label="Option Name"
                                                                                                v-model="form.questions[index].options[optionIndex].name"
                                                                                                :id="`question_${index}_option_${optionIndex}_name`"
                                                                                            />
                                                                                        </PFormLayout>
                                                                                        <PLayoutAnnotatedSection title="Related Question" style="margin-left: 0;" v-if="question.type === 'questionWithRelatedOption'">
                                                                                            <PAccordion id="allergy_test_related_question_accordion">
                                                                                                <div v-for="(relatedQuestion, relatedQuestionIndex) in form.questions[index].options[optionIndex].related_questions" :key="relatedQuestionIndex">
                                                                                                    <PStack>
                                                                                                        <PStackItem fill>
                                                                                                            <PAccordionItem
                                                                                                                :themeOptions="accordion.themeOptions"
                                                                                                            >
                                                                                                                <div slot="title">
                                                                                                                    {{ relatedQuestion.title ? relatedQuestion.title : `Related Question ${relatedQuestionIndex + 1}` }}
                                                                                                                </div>
                                                                                                                <div slot="content" style="width: 100%">
                                                                                                                    <PFormLayout>
                                                                                                                        <ValidationProvider name="Related Question Order" rules="required|numeric" v-slot="{ errors }">
                                                                                                                            <PTextField
                                                                                                                                label="Related Question Order"
                                                                                                                                v-model="form.questions[index].options[optionIndex].related_questions[relatedQuestionIndex].order"
                                                                                                                                type="number"
                                                                                                                                :error="errors[0]"
                                                                                                                                :id="`question_${index}_option_${optionIndex}_related_question_${relatedQuestionIndex}_order`"
                                                                                                                            />
                                                                                                                        </ValidationProvider>
                                                                                                                        <PTextField
                                                                                                                            label="Question Title"
                                                                                                                            v-model="form.questions[index].options[optionIndex].related_questions[relatedQuestionIndex].title"
                                                                                                                            :id="`question_${index}_option_${optionIndex}_related_question_${relatedQuestionIndex}_title`"
                                                                                                                        />
                                                                                                                        <PTextField
                                                                                                                            label="Question Description"
                                                                                                                            v-model="form.questions[index].options[optionIndex].related_questions[relatedQuestionIndex].description"
                                                                                                                            :id="`question_${index}_option_${optionIndex}_related_question_${relatedQuestionIndex}_description`"
                                                                                                                        />
                                                                                                                        <PFormLayoutGroup>
                                                                                                                            <ValidationProvider name="Start Range" rules="required" v-slot="{ errors }">
                                                                                                                                <PTextField
                                                                                                                                    label="Start Range"
                                                                                                                                    v-model="form.questions[index].options[optionIndex].related_questions[relatedQuestionIndex].question_data.range.startingRangePoint"
                                                                                                                                    :error="errors[0]"
                                                                                                                                    :id="`question_${index}_option_${optionIndex}_related_question_${relatedQuestionIndex}_start_range`"
                                                                                                                                />
                                                                                                                            </ValidationProvider>
                                                                                                                            <ValidationProvider name="End Range" rules="required" v-slot="{ errors }">
                                                                                                                                <PTextField
                                                                                                                                    label="End Range"
                                                                                                                                    v-model="form.questions[index].options[optionIndex].related_questions[relatedQuestionIndex].question_data.range.endingRangePoint"
                                                                                                                                    :error="errors[0]"
                                                                                                                                    :id="`question_${index}_option_${optionIndex}_related_question_${relatedQuestionIndex}_end_range`"
                                                                                                                                />
                                                                                                                            </ValidationProvider>
                                                                                                                        </PFormLayoutGroup>
                                                                                                                        <ValidationProvider name="Number Of Range Divisions" rules="required|numeric" v-slot="{ errors }">
                                                                                                                            <PTextField
                                                                                                                                label="Number Of Range Divisions"
                                                                                                                                v-model="form.questions[index].options[optionIndex].related_questions[relatedQuestionIndex].question_data.range.rangeDivisions"
                                                                                                                                type="number"
                                                                                                                                :error="errors[0]"
                                                                                                                                :id="`question_${index}_option_${optionIndex}_related_question_${relatedQuestionIndex}_no_of_range_divisions`"
                                                                                                                            />
                                                                                                                        </ValidationProvider>
                                                                                                                    </PFormLayout>
                                                                                                                </div>
                                                                                                            </PAccordionItem>
                                                                                                        </PStackItem>
                                                                                                    </PStack>
                                                                                                    <PStackItem style="display: flex; align-items: center;">
                                                                                                        <PIcon @click="handleDeleteRelatedQuestion(index, optionIndex, relatedQuestionIndex)" color="critical" source="DeleteMajor" />
                                                                                                    </PStackItem>
                                                                                                </div>
                                                                                            </PAccordion>
                                                                                            <div style="display: flex; margin-top: 20px;">
                                                                                                <PButton
                                                                                                    style="margin-left: auto"
                                                                                                    @click="addRelatedQuestion(index, optionIndex)"
                                                                                                >
                                                                                                    Add
                                                                                                </PButton>
                                                                                            </div>
                                                                                        </PLayoutAnnotatedSection>
                                                                                    </div>
                                                                                </PAccordionItem>
                                                                            </PStackItem>
                                                                            <PStackItem style="display: flex; align-items: center;">
                                                                                <PIcon @click="handleDeleteOption(index, optionIndex)" color="critical" source="DeleteMajor" />
                                                                            </PStackItem>
                                                                        </PStack>
                                                                    </div>
                                                                </PAccordion>
                                                                <PStack style="margin-top: 20px;">
                                                                    <PStackItem>
                                                                        <PToggle
                                                                            label="Has Redirect Result?"
                                                                            :checked="form.questions[index].has_redirect_result"
                                                                            :value="form.questions[index].has_redirect_result"
                                                                            @change="form.questions[index].has_redirect_result = !form.questions[index].has_redirect_result"
                                                                            :id="`question_${index}_redirect_result`"
                                                                        />
                                                                    </PStackItem>
                                                                    <PStackItem fill>
                                                                        <PToggle
                                                                            label="Has Redirect Question?"
                                                                            :checked="form.questions[index].has_redirect_question"
                                                                            :value="form.questions[index].has_redirect_question"
                                                                            @change="form.questions[index].has_redirect_question = !form.questions[index].has_redirect_question"
                                                                            :id="`question_${index}_redirect_result`"
                                                                        />
                                                                    </PStackItem>
                                                                    <PStackItem>
                                                                        <PButton
                                                                            style="margin-left: auto"
                                                                            @click="addOption(index, question.type)"
                                                                        >
                                                                            Add
                                                                        </PButton>
                                                                    </PStackItem>
                                                                </PStack>
                                                            </PLayoutAnnotatedSection>
                                                        </div>
                                                    </PAccordionItem>
                                                </PStackItem>
                                                <PStackItem style="display: flex; align-items: center;">
                                                    <PIcon @click="handleDeleteQuestion(index)" color="critical" source="DeleteMajor" />
                                                </PStackItem>
                                            </PStack>
                                        </div>
<!--                                    </draggable>-->
                                </PAccordion>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Select Result Type"
                        description="Choose the type of Result to add in Allergy Test"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PButtonGroup spacing="loose" full-width>
                                    <PButton @click="addResult('resultWithRange')">Add Result With Range</PButton>
                                    <PButton @click="addResult('resultWithRangeAndInstruction')">Add Result With Range And Instruction</PButton>
                                </PButtonGroup>
                                <PButtonGroup spacing="loose" full-width>
                                    <PButton @click="addResult('resultSimple')">Add Simple Result</PButton>
                                </PButtonGroup>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Allergy Test Results Details"
                        description="Add Allergy Test Results Details"
                        v-if="form.results.length > 0"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PAccordion id="allergy_test_result_accordion">
                                    <div v-for="(result, resultIndex) in form.results" :key="resultIndex">
                                        <PStack>
                                            <PStackItem fill>
                                                <PAccordionItem
                                                    :themeOptions="accordion.themeOptions"
                                                >
                                                    <div slot="title">
                                                        {{ result.title ? result.title : `Result ${resultIndex + 1}` }}
                                                    </div>
                                                    <div slot="content" style="width: 100%">
                                                        <PFormLayout>
<!--                                                            <PFormLayoutGroup help-text="Percentage Range">-->
<!--                                                                <ValidationProvider name="From" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                                    <PTextField-->
<!--                                                                        label="From%"-->
<!--                                                                        v-model="form.results[resultIndex].percentage_range.from"-->
<!--                                                                        type="number"-->
<!--                                                                        :error="errors[0]"-->
<!--                                                                        :id="`result_${resultIndex}_percentage_from`"-->
<!--                                                                    />-->
<!--                                                                </ValidationProvider>-->
<!--                                                                <ValidationProvider name="To" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                                    <PTextField-->
<!--                                                                        label="To%"-->
<!--                                                                        v-model="form.results[resultIndex].percentage_range.to"-->
<!--                                                                        type="number"-->
<!--                                                                        :error="errors[0]"-->
<!--                                                                        :id="`result_${resultIndex}_percentage_to`"-->
<!--                                                                    />-->
<!--                                                                </ValidationProvider>-->
<!--                                                            </PFormLayoutGroup>-->
                                                            <ValidationProvider name="Order" rules="required|numeric" v-slot="{ errors }">
                                                                <PTextField
                                                                    label="Order"
                                                                    v-model="form.results[resultIndex].order"
                                                                    type="number"
                                                                    :error="errors[0]"
                                                                    :id="`result_${resultIndex}_order`"
                                                                />
                                                            </ValidationProvider>
                                                            <PTextField
                                                                label="Result Label"
                                                                v-model="form.results[resultIndex].label"
                                                                :id="`result_${resultIndex}_label`"
                                                            />
                                                            <PTextField
                                                                label="Result Title"
                                                                v-model="form.results[resultIndex].title"
                                                                :id="`result_${resultIndex}_title`"
                                                            />
                                                            <div>
                                                                <PLabel :id="`result_${resultIndex}_description`" style="margin-bottom: 5px;">Result Description</PLabel>
                                                                <ckeditor
                                                                    v-model="form.results[resultIndex].description"
                                                                />
                                                            </div>
                                                            <div v-if="result.type === 'resultWithRangeAndInstruction'">
                                                                <PLabel :id="`result_${resultIndex}_instruction`" style="margin-bottom: 5px;">Result Instruction</PLabel>
                                                                <ckeditor
                                                                    v-model="form.results[resultIndex].instruction"
                                                                />
                                                            </div>
                                                            <PFormLayoutGroup help-text="Range Details" v-if="result.type !== 'resultSimple'">
                                                                <ValidationProvider name="Start Range" rules="required" v-slot="{ errors }">
                                                                    <PTextField
                                                                        label="Start Range"
                                                                        v-model="form.results[resultIndex].result_data.range.startingRangePoint"
                                                                        :error="errors[0]"
                                                                        :id="`result_${resultIndex}_result_range_start_point`"
                                                                    />
                                                                </ValidationProvider>
                                                                <ValidationProvider name="End Range" rules="required" v-slot="{ errors }">
                                                                    <PTextField
                                                                        label="End Range"
                                                                        v-model="form.results[resultIndex].result_data.range.endingRangePoint"
                                                                        :error="errors[0]"
                                                                        :id="`result_${resultIndex}_result_range_end_point`"
                                                                    />
                                                                </ValidationProvider>
                                                                <ValidationProvider name="Number of Range Divisions" rules="required|numeric" v-slot="{ errors }">
                                                                    <PTextField
                                                                        label="Number of Range Divisions"
                                                                        v-model="form.results[resultIndex].result_data.range.rangeDivisions"
                                                                        type="number"
                                                                        :error="errors[0]"
                                                                        :id="`result_${resultIndex}_result_range_no`"
                                                                    />
                                                                </ValidationProvider>
                                                            </PFormLayoutGroup>
                                                        </PFormLayout>
                                                        <PLayoutAnnotatedSection title="Result CTAs" style="margin-left: 0;">
                                                            <PAccordion id="allergy_test_result_ctas_accordion">
                                                                <div v-for="(resultCta, resultCtaIndex) in form.results[resultIndex].result_ctas" :key="resultCtaIndex">
                                                                    <PStack>
                                                                        <PStackItem fill>
                                                                            <PAccordionItem
                                                                                :themeOptions="accordion.themeOptions"
                                                                            >
                                                                                <div slot="title">
                                                                                    {{ resultCta.text ? resultCta.text : `Result CTA ${resultCtaIndex + 1}` }}
                                                                                </div>
                                                                                <div slot="content" style="width: 100%">
                                                                                    <PFormLayout>
                                                                                        <PToggle
                                                                                            label="Open In New Tab?"
                                                                                            :checked="form.results[resultIndex].result_ctas[resultCtaIndex].target_blank"
                                                                                            :value="form.results[resultIndex].result_ctas[resultCtaIndex].target_blank"
                                                                                            @change="form.results[resultIndex].result_ctas[resultCtaIndex].target_blank = !form.results[resultIndex].result_ctas[resultCtaIndex].target_blank"
                                                                                            :id="`result_${resultIndex}_result_cta_${resultCtaIndex}_target_blank`"
                                                                                        />
                                                                                        <PTextField
                                                                                            label="CTA Text"
                                                                                            v-model="form.results[resultIndex].result_ctas[resultCtaIndex].text"
                                                                                            :id="`result_${resultIndex}_result_cta_${resultCtaIndex}_text`"
                                                                                        />
                                                                                        <PTextField
                                                                                            label="CTA link"
                                                                                            v-model="form.results[resultIndex].result_ctas[resultCtaIndex].link"
                                                                                            :id="`result_${resultIndex}_result_cta_${resultCtaIndex}_link`"
                                                                                        />
                                                                                    </PFormLayout>
                                                                                </div>
                                                                            </PAccordionItem>
                                                                        </PStackItem>
                                                                        <PStackItem style="display: flex; align-items: center;">
                                                                            <PIcon @click="handleDeleteResultCta(resultIndex, resultCtaIndex)" color="critical" source="DeleteMajor" />
                                                                        </PStackItem>
                                                                    </PStack>
                                                                </div>
                                                            </PAccordion>
                                                            <div style="display: flex; margin-top: 20px;">
                                                                <PButton
                                                                    style="margin-left: auto"
                                                                    @click="addResultCta(resultIndex)"
                                                                >
                                                                    Add
                                                                </PButton>
                                                            </div>
                                                        </PLayoutAnnotatedSection>
                                                    </div>
                                                </PAccordionItem>
                                            </PStackItem>
                                            <PStackItem style="display: flex; align-items: center;">
                                                <PIcon @click="handleDeleteResult(resultIndex)" color="critical" source="DeleteMajor" />
                                            </PStackItem>
                                        </PStack>
                                    </div>
                                </PAccordion>
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Select Default Result type"
                        description="Select Default Result Type from the created Results (not required)."
                        v-if="form.results.length > 0"
                    >
                        <PCard sectioned>
                            <PFormLayout>
                                <PMultiSelect
                                    label="Default Result"
                                    :options='form.results'
                                    textField="title"
                                    valueField="order"
                                    v-model="defaultResult"
                                    :multiple="false"
                                    placeholder="Select Default Result"
                                />
                            </PFormLayout>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Add Redirect Question Details"
                        description="Add Redirect Question Details. Please Add all the questions required for the test before filling this."
                        v-if="form.questions.some(item => item.has_redirect_question === true)"
                    >
                        <PCard sectioned>
                            <div v-for="(item, index) in form.questions.filter(item => item.has_redirect_question === true)" :key="index">
                                <PLayoutAnnotatedSection
                                    :title = "item.title ? item.title : `Question ${item.order}`"
                                >
                                    <PFormLayout>
<!--                                        <PFormLayoutGroup>-->
<!--                                            <ValidationProvider name="From" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                <PTextField-->
<!--                                                    label="From %"-->
<!--                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_question_data.questionWeightageRange.from"-->
<!--                                                    :error="errors[0]"-->
<!--                                                    type="number"-->
<!--                                                    :id="`question_${form.questions.findIndex(value => value === item)}_redirect_question_weightage_from`"-->
<!--                                                />-->
<!--                                            </ValidationProvider>-->
<!--                                            <ValidationProvider name="To" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                <PTextField-->
<!--                                                    label="To %"-->
<!--                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_question_data.questionWeightageRange.to"-->
<!--                                                    :error="errors[0]"-->
<!--                                                    type="number"-->
<!--                                                    :id="`question_${form.questions.findIndex(value => value === item)}_redirect_question_weightage_to`"-->
<!--                                                />-->
<!--                                            </ValidationProvider>-->
<!--                                        </PFormLayoutGroup>-->
                                        <PStack>
                                            <PStackItem>
<!--                                                <ValidationProvider name="Question Option" rules="required" v-slot="{ errors }">-->
                                                    <PMultiSelect
                                                        label="Select Question Option to redirect from"
                                                        :options="item.options"
                                                        textField="name"
                                                        valueField="value"
                                                        v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_question_data.optionValue"
                                                        :multiple="false"
                                                        placeholder="Select Question Option"
                                                    />
<!--                                                </ValidationProvider>-->
                                            </PStackItem>
                                            <PStackItem fill>
                                                <ValidationProvider name="Redirect Question" rules="required" v-slot="{ errors }">
                                                    <PMultiSelect
                                                        label="Select Redirect Question"
                                                        :options='form.questions.filter(value => value !== item)'
                                                        textField="title"
                                                        valueField="order"
                                                        v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_question_data.questionOrder"
                                                        :multiple="false"
                                                        placeholder="Select Redirect Question"
                                                        :error="errors[0]"
                                                    />
                                                </ValidationProvider>
                                            </PStackItem>
                                        </PStack>
                                    </PFormLayout>
                                </PLayoutAnnotatedSection>
                            </div>
                        </PCard>
                    </PLayoutAnnotatedSection>
                    <PLayoutAnnotatedSection
                        title="Add Redirect Result Details"
                        description="Add Redirect Result Details. Please Add all the Results required for the test before filling this."
                        v-if="form.questions.some(item => item.has_redirect_result === true)"
                    >
                        <PCard sectioned>
                            <div v-for="(item, index) in form.questions.filter(item => item.has_redirect_result === true)" :key="index">
                                <PLayoutAnnotatedSection
                                    :title = "item.title ? item.title : `Question ${item.order}`"
                                >
                                    <PFormLayout>
<!--                                        <PFormLayoutGroup helpText="Question Range Weightage">-->
<!--                                            <ValidationProvider name="From" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                <PTextField-->
<!--                                                    label="From %"-->
<!--                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_result_data.questionWeightageRange.from"-->
<!--                                                    :error="errors[0]"-->
<!--                                                    type="number"-->
<!--                                                    :id="`question_${form.questions.findIndex(value => value === item)}_redirect_result_weightage_question_from`"-->
<!--                                                />-->
<!--                                            </ValidationProvider>-->
<!--                                            <ValidationProvider name="To" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                <PTextField-->
<!--                                                    label="To %"-->
<!--                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_result_data.questionWeightageRange.to"-->
<!--                                                    :error="errors[0]"-->
<!--                                                    type="number"-->
<!--                                                    :id="`question_${form.questions.findIndex(value => value === item)}_redirect_result_weightage_question_to`"-->
<!--                                                />-->
<!--                                            </ValidationProvider>-->
<!--                                        </PFormLayoutGroup>-->
<!--                                        <PFormLayoutGroup helpText="Result Range Weightage">-->
<!--                                            <ValidationProvider name="From" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                <PTextField-->
<!--                                                    label="From %"-->
<!--                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_result_data.resultWeightageRange.from"-->
<!--                                                    :error="errors[0]"-->
<!--                                                    type="number"-->
<!--                                                    :id="`question_${form.questions.findIndex(value => value === item)}_redirect_result_weightage_result_from`"-->
<!--                                                />-->
<!--                                            </ValidationProvider>-->
<!--                                            <ValidationProvider name="To" rules="required|numeric|min_value:0|max_value:100" v-slot="{ errors }">-->
<!--                                                <PTextField-->
<!--                                                    label="To %"-->
<!--                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_result_data.resultWeightageRange.to"-->
<!--                                                    :error="errors[0]"-->
<!--                                                    type="number"-->
<!--                                                    :id="`question_${form.questions.findIndex(value => value === item)}_redirect_result_weightage_result_to`"-->
<!--                                                />-->
<!--                                            </ValidationProvider>-->
<!--                                        </PFormLayoutGroup>-->
                                        <PStack>
                                            <PStackItem>
                                                <!--                                                <ValidationProvider name="Question Option" rules="required" v-slot="{ errors }">-->
                                                <PMultiSelect
                                                    label="Select Question Option to redirect from"
                                                    :options="item.options"
                                                    textField="name"
                                                    valueField="value"
                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_result_data.optionValue"
                                                    :multiple="false"
                                                    placeholder="Select Question Option"
                                                />
                                            </PStackItem>
                                            <PStackItem fill>
                                                <PMultiSelect
                                                    label="Select Redirect Result"
                                                    :options='form.results'
                                                    textField="title"
                                                    valueField="order"
                                                    v-model="form.questions[form.questions.findIndex(value => value === item)].redirect_result_data.resultOrder"
                                                    :multiple="false"
                                                    placeholder="Select Redirect Result"
                                                />
                                            </PStackItem>
                                        </PStack>
                                        <PToggle
                                            label="Terminate Test?"
                                            :checked="form.questions[index].redirect_result_data.terminateTest"
                                            :value="form.questions[index].redirect_result_data.terminateTest"
                                            @change="form.questions[index].redirect_result_data.terminateTest = !form.questions[index].redirect_result_data.terminateTest"
                                            :id="`question_${index}_terminate_test_toggle`"
                                        />
                                    </PFormLayout>
                                </PLayoutAnnotatedSection>
                            </div>
                        </PCard>
                    </PLayoutAnnotatedSection>
                </PLayout>
            </ValidationObserver>
        </PPage>
    </div>
</template>

<script>
// import draggable from 'vuedraggable';
export default {
    name: "Modify",
    // components: {
    //     draggable,
    // },
    data () {
        return {
            pageLoading: false,
            breadcrumbs: [{
                content: 'Allergy Tests',
                to: {
                    name: 'allergy_tests.self'
                },
                accessibilityLabel: 'allergy_test.self'
            }],
            contextualSaveBar: {
                open: false,
                save: {
                    loading: false,
                    onAction: this.saveTests
                },
                discard: {
                    onAction: this.handleDiscardChanges
                }
            },
            form: {
                id: null,
                type: '',
                label: '',
                title: '',
                description: '',
                cta_label: '',
                questions: [],
                results: [],
            },
            tempForm: {},
            defaultResult: '',
            // dragOptions: {
            //     animation: 200,
            //     group: "draggable",
            //     disabled: false,
            //     ghostClass: "ghost"
            // },
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
            testOptions: [
                {
                    label: 'Allergy Test(Self)',
                    value: 'allergy-test-self'
                },
                {
                    label: 'Allergy Test(German)',
                    value: 'allergy-test-german'
                },
                {
                    label: 'Allergy Test(Swedish)',
                    value: 'allergy-test-swedish'
                },
            ]
        }
    },
    methods: {
        async getAllergyTest(allergyTestId) {
            try {
                let {data} = await axios.get('/app/allergy-tests/'+allergyTestId);
                let allergyTestType = this.testOptions.find(item => item.value === data.allergyTest.type);
                this.form = data.allergyTest || {};
                this.form.type = allergyTestType ? allergyTestType : null;
                this.form.questions.forEach((item, index) => {
                    if (item.has_redirect_question) {
                        this.form.questions[index].redirect_question_data.questionOrder = this.form.questions.find(value => value.order === item.redirect_question_data.questionOrder);
                        this.form.questions[index].redirect_question_data.optionValue = this.form.questions[index].options.find(optionValue => optionValue.value == item.redirect_question_data.optionValue);
                    } else {
                        this.form.questions[index].redirect_question_data = {};
                    }

                    if (item.has_redirect_result) {
                        this.form.questions[index].redirect_result_data.resultOrder = this.form.results.find(value => value.order === item.redirect_result_data.resultOrder);
                        this.form.questions[index].redirect_result_data.optionValue = this.form.questions[index].options.find(optionValue => optionValue.value == item.redirect_result_data.optionValue);
                    } else {
                        this.form.questions[index].redirect_result_data = {};
                    }
                })

                let defaultResult = this.form.results.find(result => result.default === true);
                this.defaultResult = defaultResult ? defaultResult : '';

            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response.data.message || 'Something went wrong'
                });
            }
        },
        async saveTests() {
            let isConfirmed = await this.$root.$confirm('Save', `Are you sure want to save all changes?`);
            if (!isConfirmed) {
                return;
            }
            let isTestValidated = await this.$refs.allergyTest.validate();
            if (!isTestValidated) {
                return;
            }

            this.contextualSaveBar.save.loading = true;
            try {
                this.form.questions.forEach((item, index) => {
                    this.form.questions[index].redirect_question_data.questionOrder = item?.redirect_question_data?.questionOrder?.order;
                    this.form.questions[index].redirect_question_data.optionValue = item?.redirect_question_data?.optionValue?.value;
                    this.form.questions[index].redirect_result_data.resultOrder = item?.redirect_result_data?.resultOrder?.order;
                    this.form.questions[index].redirect_result_data.optionValue = item?.redirect_result_data?.optionValue?.value;
                });

                if (this.defaultResult) {
                    let index = this.form.results.indexOf(this.defaultResult);
                    this.form.results[index].default = true;
                } else {
                    this.form.results.forEach((item, index) => {
                       this.form.results[index].default = false;
                    });
                }

                let data = this.form.id ?
                    await axios.put(`/app/allergy-tests/${this.form.id}`, {...this.form, type: this.form.type?.value}) :
                    await axios.post('/app/allergy-tests', {...this.form, type: this.form.type?.value});

                await this.$router.push({name: 'allergy_tests.self'});
                this.$pToast.open(data.message || 'Test Added successfully');
            } catch ({response}) {
                this.$pToast.open({
                    error: true,
                    message: response.data.message || 'Something went wrong'
                })
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
        manageTempForm() {
            this.tempForm = JSON.parse(JSON.stringify(this.form));
            this.contextualSaveBar.save.loading = false;
            this.contextualSaveBar.open = false;
        },
        addQuestionWithOption() {
            let length = this.form.questions.length;
            this.form.questions.push({
                type: 'questionWithOption',
                has_redirect_result: false,
                has_redirect_question: false,
                order: length + 1,
                title: '',
                description: '',
                options: [],
                redirect_question_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    optionValue: '',
                    questionOrder: ''
                },
                redirect_result_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    optionValue: '',
                    resultOrder: '',
                    terminateTest: false,
                    // resultWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // }
                },
            });

            this.addOption(length);
        },
        addQuestionWithSubQuestion() {
            let length = this.form.questions.length;
            this.form.questions.push({
                type: 'questionWithSubQuestion',
                has_redirect_result: false,
                has_redirect_question: false,
                order: length + 1,
                title: '',
                description: '',
                sub_questions: [],
                options: [],
                redirect_question_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    optionValue: '',
                    questionOrder: ''
                },
                redirect_result_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    // resultWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // }
                    optionValue: '',
                    resultOrder: '',
                    terminateTest: false,
                },
            });

            this.addSubQuestion(length);
            this.addOption(length);
        },
        addQuestionWithRange() {
            let length = this.form.questions.length;
            this.form.questions.push({
                type: 'questionWithRange',
                has_redirect_result: false,
                has_redirect_question: false,
                order: length + 1,
                title: '',
                description: '',
                question_data: {
                    range: {
                        startingRangePoint: '',
                        endingRangePoint: '',
                        rangeDivisions: 0
                    },
                },
                redirect_question_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    // questionOrder: ''
                    optionValue: '',
                    questionOrder: ''
                },
                redirect_result_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    // resultWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // }
                    optionValue: '',
                    resultOrder: '',
                    terminateTest: false,
                },
            });
        },
        addQuestionWithRelatedOption() {
            let length = this.form.questions.length;
            this.form.questions.push({
                type: 'questionWithRelatedOption',
                has_redirect_result: false,
                has_redirect_question: false,
                order: length + 1,
                title: '',
                description: '',
                options: [],
                redirect_question_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    // questionOrder: ''
                    optionValue: '',
                    questionOrder: ''
                },
                redirect_result_data: {
                    // questionWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // },
                    // resultWeightageRange: {
                    //     from: 0,
                    //     to: 0
                    // }
                    optionValue: '',
                    resultOrder: '',
                    terminateTest: false,
                },
            });

            this.addOption(length, 'questionWithRelatedOption');
        },
        addSubQuestion(questionIndex) {
            let length = this.form.questions[questionIndex].sub_questions.length;
            this.form.questions[questionIndex].sub_questions.push({
                order: length + 1,
                title: '',
            })
        },
        addOption(questionIndex, questionType=null) {
            let length = this.form.questions[questionIndex].options.length;
            this.form.questions[questionIndex].options.push({
                order: length + 1,
                name: '',
                weightage: 0,
                value: 0,
                related_questions: []
            });

            if (questionType == 'questionWithRelatedOption') {
               this.addRelatedQuestion(questionIndex, length);
            }
        },
        addRelatedQuestion(questionIndex, optionIndex) {
            let length = this.form.questions[questionIndex].options[optionIndex].related_questions.length;
            this.form.questions[questionIndex].options[optionIndex].related_questions.push({
                type: 'questionWithRange',
                order: length + 1,
                title: '',
                description: '',
                question_data: {
                    range: {
                        startingRangePoint: '',
                        endingRangePoint: '',
                        rangeDivisions: 0,
                    },
                },
            });
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
        async handleDeleteSubQuestion(index, subQuestionIndex) {
            let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this question?`, {
                primaryAction: {
                    destructive: true,
                },
            });
            if (!isConfirmed) {
                return;
            }
            this.form.questions[index].sub_questions.splice(subQuestionIndex, 1);
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
        async handleDeleteRelatedQuestion(index, optionIndex, relatedQuestionIndex) {
            let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this option?`, {
                primaryAction: {
                    destructive: true,
                },
            });
            if (!isConfirmed) {
                return;
            }
            this.form.questions[index].options[optionIndex].related_questions.splice(relatedQuestionIndex, 1);
        },
        addResult(resultType) {
            let length = this.form.results.length;
            this.form.results.push({
                type: resultType,
                default: false,
                percentage_range: {
                    from: 0,
                    to: 100,
                },
                order: length + 1,
                label: '',
                title: '',
                description: '',
                instruction: '',
                result_data: {
                    range: {
                        startingRangePoint: '',
                        endingRangePoint: '',
                        rangeDivisions: 0
                    },
                },
                result_ctas: [],
            });

            this.addResultCta(length);
        },
        addResultCta(resultIndex) {
            this.form.results[resultIndex].result_ctas.push({
                text: '',
                link: '',
                target_blank: false
            });
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
        async handleDeleteResultCta(resultIndex, resultCtaIndex) {
            let isConfirmed = await this.$root.$confirm('Delete', `Are you sure want to delete this result?`, {
                primaryAction: {
                    destructive: true,
                },
            });
            if (!isConfirmed) {
                return;
            }
            this.form.results[resultIndex].result_ctas.splice(resultCtaIndex, 1);
        },
        // handleDragQuestion(value) {
        //     console.log(this.form.questions);
        //     // value.relatedContext.list.forEach((item, index) => {
        //     //     item.order = index + 1;
        //     // });
        //     // console.log(value);
        //     // this.form.questions.forEach((item, index) => {
        //     //     this.form.questions[index].order = value;
        //     // });
        // }
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
        if (this.$route.params.allergyTestId) {
            await this.getAllergyTest(this.$route.params.allergyTestId);
        }

        this.manageTempForm();
    }
}
</script>

<style scoped>

</style>
