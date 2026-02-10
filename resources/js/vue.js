import { createApp } from 'vue'
import { VueQueryPlugin } from '@tanstack/vue-query'

import '../css/app.css'

import { registerGlobalComponents } from './registerGlobalComponents'

const app = createApp({})

app.use(VueQueryPlugin)
registerGlobalComponents(app)

app.mount('#vue-app')
