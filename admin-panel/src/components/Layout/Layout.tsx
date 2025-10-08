import React from 'react';
import type { ReactNode } from 'react';
import { Box, CssBaseline, Toolbar } from '@mui/material';
import Sidebar from './Sidebar.js';
import Header from './Header.js';

interface LayoutProps {
  children: ReactNode;
}

const DRAWER_WIDTH = 280;

const Layout: React.FC<LayoutProps> = ({ children }) => {
  return (
    <Box sx={{ display: 'flex' }}>
      <CssBaseline />
      {/* Sidebar */}
      <Sidebar />
      
      {/* Main content */}
      <Box
        component="main"
        sx={{
          flexGrow: 1,
          ml: `${DRAWER_WIDTH}px`,
          minHeight: '100vh',
          backgroundColor: 'background.default',
        }}
      >
        <Header />
        <Toolbar /> {/* Space for fixed header */}
        <Box sx={{ p: 3 }}>
          {children}
        </Box>
      </Box>
    </Box>
  );
};

export default Layout;