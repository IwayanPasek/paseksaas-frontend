import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'             
import AdminApp from './AdminApp.jsx'   
import MasterApp from './MasterApp.jsx' 
import LoginApp from './LoginApp.jsx'   
import './index.css'

const rootElement = document.getElementById('root');

if (rootElement) {
  // 1. Jika dibuka di login.php
  if (window.LOGIN_DATA) {
    ReactDOM.createRoot(rootElement).render(<React.StrictMode><LoginApp /></React.StrictMode>)
  }
  // 2. Jika dibuka di master.php
  else if (window.MASTER_DATA) {
    ReactDOM.createRoot(rootElement).render(<React.StrictMode><MasterApp /></React.StrictMode>)
  }
  // 3. Jika dibuka di admin.php
  else if (window.ADMIN_DATA) {
    ReactDOM.createRoot(rootElement).render(<React.StrictMode><AdminApp /></React.StrictMode>)
  } 
  // 4. Default / index.php
  else {
    ReactDOM.createRoot(rootElement).render(<React.StrictMode><App /></React.StrictMode>)
  }
}
