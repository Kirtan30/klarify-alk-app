<template>
    <PStack>
        <PStackItem>
            <ValidationObserver ref="confirm_form" v-slot="{ invalid }">
                <PModal
                    :open="dialog"
                    sectioned
                    :primaryAction='{
                        ...primaryAction,
                        disabled: invalid
                    }'
                    :secondaryActions='secondaryActions'
                    :title="title"
                    @close="cancel"
                >
                    <span v-html="message"/>

                    <ValidationProvider vid="notification_email" name="Email" rules="required|email" v-slot="{ errors }">
                        <PTextField v-if="options.input.showInput"
                                    :label="'Notification Email'"
                                    v-model="options.input.inputValue"
                                    :error="errors[0]"
                                    style="margin-top: 10px;"
                        />
                    </ValidationProvider>
                </PModal>
            </ValidationObserver>
        </PStackItem>
    </PStack>
</template>

<script>
export default {
    name: 'Confirm',
    data() {
        return {
            dialog: false,
            resolve: null,
            reject: null,
            message: null,
            title: null,
            inputText: '',
            primaryAction: {
                content: 'Yes',
                onAction: this.agree,
            },
            secondaryActions: [ {
                content: 'No',
                onAction: this.cancel,
            } ],
            options: {
                primaryAction: {
                    content: '',
                    destructive: false,
                },
                secondaryAction: {
                    content: '',
                    destructive: false,
                },
                input: {
                    showInput: false,
                    inputValue: '',
                }
            }
        }
    },
    methods: {
        open(title, message, options) {
            this.dialog = true
            this.title = title
            this.message = message
            this.options = Object.assign(this.options, options);
            if(this.options.primaryAction) {
                this.primaryAction.content = this.options.primaryAction.content || 'Yes';
                this.primaryAction.destructive = this.options.primaryAction.destructive || false;
            }
            if(this.options.secondaryAction) {
                this.secondaryActions[0].content = this.options.secondaryAction.content || 'No';
                this.secondaryActions[0].destructive = this.options.secondaryAction.destructive || false;
            }
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        async agree() {
            if(this.options.input.showInput) {
                let validated = await this.$refs.confirm_form.validate();
                if(!validated) {
                    return;
                }
                this.resolve({isConfirmed: true, inputValue: this.options.input.inputValue})
            } else {
                this.resolve(true);
            }
            this.options = {
                primaryAction: {
                    content: '',
                    destructive: false,
                },
                secondaryAction: {
                    content: '',
                    destructive: false,
                },
                input: {
                    showInput: false,
                    inputValue: '',
                },
            }
            this.dialog = false;
        },
        cancel() {
            if(this.options.input.showInput) {
                this.resolve({isConfirmed: false, inputValue: this.options.input.inputValue})
            } else {
                this.resolve(false);
            }
            this.options = {
                primaryAction: {
                    content: '',
                    destructive: false,
                },
                secondaryAction: {
                    content: '',
                    destructive: false,
                },
                input: {
                    showInput: false,
                    inputValue: '',
                },
            }
            this.dialog = false
        }
    }
}
</script>
