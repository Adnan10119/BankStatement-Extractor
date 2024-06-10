require('./bootstrap')

import { createApp } from 'vue'

// window.$ = window.jQuery = require('jquery')
// import * as Vue from 'vue'
import { createWebHistory, createRouter } from "vue-router";

import Index from './components/index'
import Signup from './components/signup'
import Login from './components/login'
import Home from './components/home'
import History from './components/history'
import ProcessDocument from './components/process-Doc'
import OverviewDocument from './components/overview'
import chargify_subscription from './components/chargify_subscription'


const routes = [
    { path: '/', component: Login, name: 'login' },
    { path: '/signup', component: Signup, name: 'Signup'},
    { path: '/subscription', component: chargify_subscription, name: 'Subscription'},
    { path: '/home', component: Home, name: 'home', children : [
        {path : '', component : ProcessDocument, name : 'process-doc'},
        {path : '/history', component : History, name : 'history'},
        // {
        //   path: '/overview/:id',
        //   component: OverviewDocument,
        //   name: 'overview',
        //   props: true
        // },
        {path : '/overview', component : OverviewDocument, name : 'overview'},
    ]},

  ]

  const router = createRouter({
    history: createWebHistory(),
    routes,
    linkActiveClass: "active", // active class for non-exact links.
  linkExactActiveClass: "active" // active class for *exact* links.
  });
createApp(Index).use(router).mount('#app')
