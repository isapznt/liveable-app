import './app.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import PhosphorIcons from "@phosphor-icons/vue"

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(PhosphorIcons);

app.mount('#app')
