import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { ThemeWrapper } from './theme/theme';
import Layout from './components/Layout/Layout';
import Login from './pages/Login.js';
import Dashboard from './pages/Dashboard.js';
import PromptTemplates from './pages/PromptTemplates.tsx';
import PromptSets from './pages/PromptSets.js';
import ContentGenerator from './pages/ContentGenerator.js';
import VersionManagement from './pages/VersionManagement.tsx';
import LoadingSpinner from './components/UI/LoadingSpinner.js';

const AppRoutes: React.FC = () => {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return <LoadingSpinner />;
  }

  if (!isAuthenticated) {
    return (
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    );
  }

  return (
    <Layout>
      <Routes>
        <Route path="/" element={<Navigate to="/dashboard" replace />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/templates" element={<PromptTemplates />} />
        <Route path="/templates/:templateId/versions" element={<VersionManagement />} />
        <Route path="/sets" element={<PromptSets />} />
        <Route path="/generator" element={<ContentGenerator />} />
        <Route path="/login" element={<Navigate to="/dashboard" replace />} />
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </Layout>
  );
};

const App: React.FC = () => {
  return (
    <ThemeWrapper>
      <AuthProvider>
        <Router>
          <div style={{ minHeight: '100vh', backgroundColor: '#f5f5f5' }}>
            <AppRoutes />
          </div>
        </Router>
      </AuthProvider>
    </ThemeWrapper>
  );
};

export default App;
