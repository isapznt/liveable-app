import { createRouter, createWebHistory } from 'vue-router'
import { isAuthenticated } from '@/services/auth'

import BaseLayout from '@/shared/layouts/baseLayout.vue'

import HomeView from '@/modules/home/views/homeView.vue'
import PropertyDetails from '@/modules/properties/views/propertyDetails.vue'
import BaseLogin from '@/shared/layouts/baseLogin.vue'
import LoginView from '@/modules/login/views/loginView.vue'
import ImoveisPendentes from '@/modules/properties/views/imoveisPendentes.vue'
import MinhasProps from '@/modules/properties/views/minhasProps.vue'
import CadastroView from '@/modules/login/views/cadastroView.vue'
import ViewProfile from '@/modules/profile/views/viewProfile.vue'
import ViewStrangerProfile from '@/modules/profile/views/viewStrangerProfile.vue'

const routes = [
  {
    path: '/',
    component: BaseLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: HomeView,
      },
      {
        path: '/property-details/:id',
        name: 'propertyDetails',
        component: PropertyDetails,
        meta: { requiresAuth: true },
      },
      {
        path: 'pendencias',
        name: 'pendenciasPage',
        component: ImoveisPendentes,
        meta: { requiresAuth: true },
      },
      {
        path: 'minhasProps',
        name: 'minhasPropspage',
        component: MinhasProps,
        meta: { requiresAuth: true },
      },
      {
        path: 'perfil',
        name: 'perfilPage',
        component: ViewProfile,
        meta: { requiresAuth: true },
      },
      {
        path: 'perfil/:id',
        name: 'verPerfil',
        component: ViewStrangerProfile,
      },
      {
        path: '/favoritos',
        name: 'favorites',
        component: () => import('@/modules/favorites/views/FavoritesView.vue'),
      },
      {
        // ?type=casa|apartamento|chacara  &q=texto
        path: '/imoveis',
        name: 'todasPropriedades',
        component: () => import('@/modules/properties/views/TodasPropriedades.vue'),
      },

      {
        path: '/esqueci-senha',
        name: 'forgotPassword',
        component: () => import('@/modules/login/views/ForgotPasswordView.vue'),
        meta: { guestOnly: true },
      },
      {
        path: '/reset-password',
        name: 'resetPassword',
        component: () => import('@/modules/login/views/ResetPasswordView.vue'),
        meta: { guestOnly: true },
      },
    ],
  },
  {
    path: '/baselogin',
    component: BaseLogin,
    children: [
      {
        path: '',
        name: 'loginPage',
        component: LoginView,
        meta: { guestOnly: true },
      },
      {
        path: 'cadastrar',
        name: 'cadastrarPage',
        component: CadastroView,
        meta: { guestOnly: true },
      },
      {
        path: '/auth/callback',
        component: () => import('@/modules/login/views/AuthCallback.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0, behavior: 'smooth' }
    }
  },
})

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !isAuthenticated()) {
    next('/baselogin')
  } else if (to.meta.guestOnly && isAuthenticated()) {
    next('/')
  } else {
    next()
  }
})

export default router
