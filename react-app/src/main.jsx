/* eslint-disable react-refresh/only-export-components */
import React, { lazy, Suspense } from 'react'
import ReactDOM from 'react-dom/client'
import './index.css'

// Lazy-loaded page components — only the active page is downloaded
const StorefrontPage = lazy(() => import('./pages/storefront/StorefrontPage'))
const AdminPage      = lazy(() => import('./pages/admin/AdminPage'))
const MasterPage     = lazy(() => import('./pages/master/MasterPage'))
const LoginPage      = lazy(() => import('./pages/login/LoginPage'))

// Minimal loading fallback
function Loading() {
  return (
    <div style={{ display:'flex', alignItems:'center', justifyContent:'center', height:'100vh', fontFamily:'Inter,sans-serif', color:'#a3a3a3' }}>
      <div style={{ textAlign:'center' }}>
        <div style={{ width:32, height:32, border:'3px solid #e5e5e5', borderTop:'3px solid #171717', borderRadius:'50%', animation:'spin 0.6s linear infinite', margin:'0 auto 12px' }} />
        <p style={{ fontSize:13 }}>Memuat...</p>
      </div>
      <style>{`@keyframes spin { to { transform: rotate(360deg) } }`}</style>
    </div>
  )
}

const root = document.getElementById('root')

if (root) {
  let App

  if (window.LOGIN_DATA)       App = LoginPage
  else if (window.MASTER_DATA) App = MasterPage
  else if (window.ADMIN_DATA)  App = AdminPage
  else                         App = StorefrontPage

  ReactDOM.createRoot(root).render(
    <React.StrictMode>
      <Suspense fallback={<Loading />}>
        <App />
      </Suspense>
    </React.StrictMode>
  )
}
