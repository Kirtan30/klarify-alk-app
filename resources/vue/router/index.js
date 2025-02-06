import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

const routes = [
    {
        path: '/',
        name: 'master',
        component: require('../layouts/Master').default,
        redirect: '/doctors',
        children: [
            {
                path: 'doctors',
                name: 'doctors.layout',
                component: require('../pages/clinics/Layout').default,
                children: [
                    {
                        path: '',
                        name: 'doctors',
                        component: require('../pages/clinics/Index').default,
                    },
                    {
                        path: 'create',
                        name: 'doctors.create',
                        component: require('../pages/clinics/Modify').default,
                    },
                    {
                        path: ':doctorId/edit',
                        name: 'doctors.edit',
                        component: require('../pages/clinics/Modify').default,
                    }
                ]
            },
            {
                path: 'self-test-results',
                name: 'self-test-results',
                component: require('../pages/self_test_results/Index').default,
            },
            {
                path: 'allergy-test-german',
                name: 'allergy-test-german',
                component: require('../pages/allergy_test_german/Index').default,
            },
            {
                path: 'allergy-test-swedish',
                name: 'allergy-test-swedish',
                component: require('../pages/allergy_test_swedish/Index').default,
            },
            // {
            //     path: 'allergy-tests',
            //     component: require('../pages/allergy_tests/Layout').default,
            //     children: [
            //         {
            //             path: '',
            //             name: 'allergy-tests',
            //             redirect: '/allergy-tests/self',
            //             component: require('../pages/allergy_tests/Index').default,
            //             children: [
            //                 {
            //                     path: 'self',
            //                     name: 'allergy_tests.self',
            //                     component: require('../pages/allergy_tests/self/Index').default
            //                 },
            //                 {
            //                     path: 'german',
            //                     name: 'allergy_test.german',
            //                     component: require('../pages/allergy_tests/german/Index').default
            //                 },
            //                 {
            //                     path: 'swedish',
            //                     name: 'allergy_test.swedish',
            //                     component: require('../pages/allergy_tests/swedish/Index').default
            //                 },
            //             ]
            //         },
            //         {
            //             path: 'create-allergy-test',
            //             name: 'allergy-test.create',
            //             component: require('../pages/allergy_tests/Modify').default,
            //         },
            //         {
            //             path: ':allergyTestId/edit',
            //             name: 'allergy-test.edit',
            //             component: require('../pages/allergy_tests/Modify').default,
            //         },
            //     ]
            // },
            {
                path: 'quizzes',
                component: require('../pages/quiz/QuizLayout').default,
                children: [
                    {
                        path: '',
                        name: 'quizzes',
                        component: require('../pages/quiz/Index').default,
                    },
                    {
                        path: 'create',
                        name: 'quizzes.create',
                        component: require('../pages/quiz/Modify').default,
                    },
                    {
                        path: ':quizId/edit',
                        name: 'quizzes.edit',
                        component: require('../pages/quiz/Modify').default,
                    }
                ]
            },
            {
                path: 'polls',
                component: require('../pages/polls/PollLayout').default,
                children: [
                    {
                        path: '',
                        name: 'polls',
                        component: require('../pages/polls/Index').default,
                    },
                    {
                        path: 'create',
                        name: 'polls.create',
                        component: require('../pages/polls/Modify').default,
                    },
                    {
                        path: ':pollId/edit',
                        name: 'polls.edit',
                        component: require('../pages/polls/Modify').default,
                    }
                ]
            },
            {
                path: 'news',
                component: require('../pages/news/NewsLayout').default,
                children: [
                    {
                        path: '',
                        name: 'news',
                        component: require('../pages/news/Index').default,
                    },
                    {
                        path: 'create',
                        name: 'news.create',
                        component: require('../pages/news/Modify').default,
                    },
                    {
                        path: ':newsId/edit',
                        name: 'news.edit',
                        component: require('../pages/news/Modify').default,
                    }
                ]
            },
            {
                path: 'fad',
                component: require('../pages/fad/Layout').default,
                redirect: 'fad/cities',
                children: [
                    {
                        path: 'cities',
                        name: 'fad.cities.layout',
                        component: require('../pages/fad/cities/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'fad.cities',
                                component: require('../pages/fad/cities/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'fad.cities.create',
                                component: require('../pages/fad/cities/Modify').default,
                            },
                            {
                                path: ':cityId/edit',
                                name: 'fad.cities.edit',
                                component: require('../pages/fad/cities/Modify').default,
                            }
                        ]
                    },
                    {
                        path: 'regions',
                        name: 'fad.regions.layout',
                        component: require('../pages/fad/regions/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'fad.regions',
                                component: require('../pages/fad/regions/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'fad.regions.create',
                                component: require('../pages/fad/regions/Modify').default,
                            },
                            {
                                path: ':regionId/edit',
                                name: 'fad.regions.edit',
                                component: require('../pages/fad/regions/Modify').default,
                            }
                        ]
                    },
                    {
                        path: 'states',
                        name: 'fad.states.layout',
                        component: require('../pages/fad/states/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'fad.states',
                                component: require('../pages/fad/states/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'fad.states.create',
                                component: require('../pages/fad/states/Modify').default,
                            },
                            {
                                path: ':stateId/edit',
                                name: 'fad.states.edit',
                                component: require('../pages/fad/states/Modify').default,
                            }
                        ]
                    },
                    {
                        path: 'static-contents',
                        name: 'fad.static-contents.layout',
                        component: require('../pages/fad/static_content/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'fad.static-contents',
                                component: require('../pages/fad/static_content/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'fad.static-contents.create',
                                component: require('../pages/fad/static_content/Modify').default,
                            },
                            {
                                path: ':staticContentId/edit',
                                name: 'fad.static-contents.edit',
                                component: require('../pages/fad/static_content/Modify').default,
                            }
                        ]
                    },
                ]
            },
            {
                path: 'pollen',
                component: require('../pages/pollen/Layout').default,
                redirect: 'pollen/cities',
                children: [
                    {
                        path: 'cities',
                        name: 'pollen.cities.layout',
                        component: require('../pages/pollen/cities/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'pollen.cities',
                                component: require('../pages/pollen/cities/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'pollen.cities.create',
                                component: require('../pages/pollen/cities/Modify').default,
                            },
                            {
                                path: ':cityId/edit',
                                name: 'pollen.cities.edit',
                                component: require('../pages/pollen/cities/Modify').default,
                            }
                        ]
                    },
                    {
                        path: 'regions',
                        name: 'pollen.regions.layout',
                        component: require('../pages/pollen/regions/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'pollen.regions',
                                component: require('../pages/pollen/regions/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'pollen.regions.create',
                                component: require('../pages/pollen/regions/Modify').default,
                            },
                            {
                                path: ':regionId/edit',
                                name: 'pollen.regions.edit',
                                component: require('../pages/pollen/regions/Modify').default,
                            }
                        ]
                    },
                    {
                        path: 'states',
                        name: 'pollen.states.layout',
                        component: require('../pages/pollen/states/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'pollen.states',
                                component: require('../pages/pollen/states/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'pollen.states.create',
                                component: require('../pages/pollen/states/Modify').default,
                            },
                            {
                                path: ':stateId/edit',
                                name: 'pollen.states.edit',
                                component: require('../pages/pollen/states/Modify').default,
                            }
                        ]
                    },
                    {
                        path: 'static-contents',
                        name: 'pollen.static-contents.layout',
                        component: require('../pages/pollen/static_content/Layout').default,
                        children: [
                            {
                                path: '',
                                name: 'pollen.static-contents',
                                component: require('../pages/pollen/static_content/Index').default,
                            },
                            {
                                path: 'create',
                                name: 'pollen.static-contents.create',
                                component: require('../pages/pollen/static_content/Modify').default,
                            },
                            {
                                path: ':staticContentId/edit',
                                name: 'pollen.static-contents.edit',
                                component: require('../pages/pollen/static_content/Modify').default,
                            }
                        ]
                    },
                ]
            },
            {
                path: 'lexicons',
                component: require('../pages/lexicon/Layout').default,
                children: [
                    {
                        path: '',
                        name: 'lexicons',
                        component: require('../pages/lexicon/Index').default,
                    },
                    {
                        path: 'create',
                        name: 'lexicons.create',
                        component: require('../pages/lexicon/Modify').default
                    },
                    {
                        path: ':lexiconId/edit',
                        name: 'lexicons.edit',
                        component: require('../pages/lexicon/Modify').default
                    },
                ]
            },
            {
                path: 'settings',
                name: 'settings',
                component: require('../pages/settings/Index').default
            }
        ]
    },
    {
        path: '*',
        component: require('../pages/PageNotFound').default,
        name: 'page-not-found'
    }
]

const router = new VueRouter({
    mode: 'history',
    routes
});

export default router;
